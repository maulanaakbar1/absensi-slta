<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Siswa;
use App\Models\Ekstrakurikuler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Exports\SiswaExport;
use App\Imports\SiswaImport;
use Maatwebsite\Excel\Facades\Excel;

class SiswaController extends Controller
{
    public function index(Request $request)
    {
        $selectedTahun = $request->get(
            'tahun_ajaran',
            $this->getCurrentTahunAjaran()
        );

        $selectedKelas = $request->get('kelas');
        $selectedJurusan = $request->get('jurusan');
        $selectedEkskul = $request->get('ekskul');

        $selectedTahunStart = $selectedTahun !== 'semua'
            ? $this->parseTahunAjaranStart($selectedTahun)
            : $this->parseTahunAjaranStart($this->getCurrentTahunAjaran());

        $query = Siswa::with('user');

        // filter tahun ajaran
        if ($selectedTahun !== 'semua') {
            $currentStart = $this->parseTahunAjaranStart($this->getCurrentTahunAjaran());

            $query->where(function ($q) use ($selectedTahunStart, $currentStart) {

                $q->whereNull('tahun_masuk');

                if ($selectedTahunStart == $currentStart) {

                    $q->orWhereRaw(
                        '(? - tahun_masuk) + tingkat_awal BETWEEN 7 AND 9',
                        [$selectedTahunStart]
                    );

                } else {

                    $q->orWhereRaw(
                        '? BETWEEN tahun_masuk AND (tahun_masuk + (12 - tingkat_awal))',
                        [$selectedTahunStart]
                    );

                }
            });
        }

        // filter jurusan
        if ($selectedJurusan) {
            $query->where('jurusan', $selectedJurusan);
        }

        // filter eskul
        if ($selectedEkskul && $selectedEkskul !== 'all') {
            $query->whereJsonContains(
                'ekstrakurikuler_id',
                (int) $selectedEkskul
            );
        }

        // search nama
        if ($request->filled('search')) {
            $search = $request->search;

            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%');
            });
        }

        // filter kelas
        if ($selectedKelas) {
            $query->where(function ($q) use ($selectedTahunStart, $selectedKelas) {
                $q->whereRaw(
                    '(? - tahun_masuk) + tingkat_awal = ?',
                    [$selectedTahunStart, $selectedKelas]
                );
            });
        }

        $anggota = $query
            ->join('users', 'siswas.user_id', '=', 'users.id')
            ->orderBy('siswas.jurusan', 'asc')
            ->orderBy('siswas.tingkat_awal', 'asc')
            ->orderBy('users.name', 'asc')
            ->select('siswas.*')
            ->paginate(10)
            ->withQueryString();

        
        $allEkskul = Ekstrakurikuler::all()->keyBy('id');
        $ekskul = Ekstrakurikuler::all(); 

        $anggota->getCollection()->transform(function ($siswa) use ($selectedTahunStart, $allEkskul) {

            $tingkat = $this->getTingkat($siswa, $selectedTahunStart);
            $siswa->kelas_display = $this->getKelasDisplay($siswa, $selectedTahunStart);
            $siswa->tingkat_display = $tingkat;

            $ids = is_string($siswa->ekstrakurikuler_id) 
                ? json_decode($siswa->ekstrakurikuler_id, true) 
                : $siswa->ekstrakurikuler_id;
                
            $ids = $ids ?: [];

            $siswa->ekskul_nama = collect($ids)
                ->map(fn($id) => $allEkskul[$id]->nama ?? '-')
                ->implode(', ');

            return $siswa;
        });

        $tahunAjaranList = $this->getTahunAjaranList();

        if (
            $selectedTahun !== 'semua'
            && !in_array($selectedTahun, $tahunAjaranList)
        ) {
            $tahunAjaranList[] = $selectedTahun;
        }

        return view('admin.siswa', compact(
            'anggota',
            'ekskul',
            'tahunAjaranList',
            'selectedTahun',
            'selectedTahunStart',
            'selectedKelas',
            'selectedJurusan',
            'selectedEkskul'
        ));
    }

    public function show($id)
    {
        $siswa = Siswa::with('user')
            ->findOrFail($id);

        $ids = is_string($siswa->ekstrakurikuler_id) 
            ? json_decode($siswa->ekstrakurikuler_id, true) 
            : $siswa->ekstrakurikuler_id;
            
        $ids = $ids ?: [];

        $siswa->ekskul_nama = Ekstrakurikuler::whereIn('id', $ids)
            ->pluck('nama')
            ->implode(', ');

        $tahunAjaran = $this->getCurrentTahunAjaran();

        $tahunStart = $this->parseTahunAjaranStart($tahunAjaran);

        $kelasDisplay = $this->getKelasDisplay(
            $siswa,
            $tahunStart
        );

        return view('admin.siswa-show', compact(
            'siswa',
            'tahunAjaran',
            'kelasDisplay'
        ));
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'nis' => 'required|unique:siswas,nis',
            'nisn' => 'required|unique:siswas,nisn',
            'tahun_masuk' => 'required|integer|min:2000|max:2100',
            'tingkat_awal' => 'required|in:7,8,9',
            'jurusan' => 'required|string|max:50',
            'jenis_kelamin' => 'required|in:L,P',

            'ekstrakurikuler_id' => 'required|array',
            'ekstrakurikuler_id.*' => 'exists:ekstrakurikulers,id',

            'no_telp_siswa' => 'nullable|string|max:15',
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'alamat' => 'nullable|string',
            'nama_ayah' => 'nullable|string|max:100',
            'no_telp_ayah' => 'nullable|string|max:15',
            'nama_ibu' => 'nullable|string|max:100',
            'no_telp_ibu' => 'nullable|string|max:15',
            'tingkatan' => 'required|in:balonpas,instruktur',
        ]);

        DB::transaction(function () use ($request) {

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'siswa',
            ]);
            $ekskul = array_map('intval', $request->ekstrakurikuler_id ?? []);

            Siswa::create([
                'user_id' => $user->id,

                'ekstrakurikuler_id' => $ekskul,

                'nis' => $request->nis,
                'nisn' => $request->nisn,
                'tahun_masuk' => $request->tahun_masuk,
                'tingkat_awal' => $request->tingkat_awal,
                'jurusan' => $request->jurusan,
                'jenis_kelamin' => $request->jenis_kelamin,
                'no_telp_siswa' => $request->no_telp_siswa,
                'tempat_lahir' => $request->tempat_lahir,
                'tanggal_lahir' => $request->tanggal_lahir,
                'alamat' => $request->alamat,
                'nama_ayah' => $request->nama_ayah,
                'no_telp_ayah' => $request->no_telp_ayah,
                'nama_ibu' => $request->nama_ibu,
                'no_telp_ibu' => $request->no_telp_ibu,
                'tingkatan' => $request->tingkatan,
            ]);
        });

        return back()->with(
            'success',
            'Siswa baru berhasil ditambahkan.'
        );
    }

    public function update(Request $request, $id)
    {
        $siswa = Siswa::findOrFail($id);
        $user = $siswa->user;

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|min:6',
            'nis' => 'required|unique:siswas,nis,' . $siswa->id,
            'nisn' => 'required|unique:siswas,nisn,' . $siswa->id,
            'tahun_masuk' => 'required|integer|min:2000|max:2100',
            'tingkat_awal' => 'required|in:7,8,9',
            'jurusan' => 'required|string|max:50',
            'jenis_kelamin' => 'required|in:L,P',

            'ekstrakurikuler_id' => 'required|array',
            'ekstrakurikuler_id.*' => 'exists:ekstrakurikulers,id',

            'no_telp_siswa' => 'nullable|string|max:15',
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'alamat' => 'nullable|string',
            'nama_ayah' => 'nullable|string|max:100',
            'no_telp_ayah' => 'nullable|string|max:15',
            'nama_ibu' => 'nullable|string|max:100',
            'no_telp_ibu' => 'nullable|string|max:15',
            'tingkatan' => 'required|in:balonpas,instruktur',
        ]);

        // =========================
        // OLD DATA
        // =========================
        $oldEkskulIds = $siswa->ekstrakurikuler_id;

        if (!is_array($oldEkskulIds)) {
            $oldEkskulIds = json_decode($oldEkskulIds, true) ?: [$oldEkskulIds];
        }

        $oldEkskul = Ekstrakurikuler::whereIn('id', $oldEkskulIds)
            ->pluck('nama')
            ->implode(', ');
        $oldEkskul = $oldEkskul ?: '-';

        $oldData = [
            'Nama' => $user->name,
            'Email' => $user->email,
            'NIS' => $siswa->nis,
            'NISN' => $siswa->nisn,
            'Tahun Masuk' => $siswa->tahun_masuk,
            'Tingkat Awal' => $siswa->tingkat_awal,
            'Jurusan' => $siswa->jurusan,
            'Jenis Kelamin' => $siswa->jenis_kelamin,
            'No Telp Siswa' => $siswa->no_telp_siswa,
            'Tempat Lahir' => $siswa->tempat_lahir,
            'Tanggal Lahir' => $siswa->tanggal_lahir,
            'Alamat' => $siswa->alamat,
            'Nama Ayah' => $siswa->nama_ayah,
            'No Telp Ayah' => $siswa->no_telp_ayah,
            'Nama Ibu' => $siswa->nama_ibu,
            'No Telp Ibu' => $siswa->no_telp_ibu,
            'Tingkatan' => $siswa->tingkatan,
            'Ekskul' => $oldEkskul,
        ];

        DB::transaction(function () use ($request, $user, $siswa) {

            $ekskul = array_map('intval', $request->ekstrakurikuler_id ?? []);

            $user->update([
                'name' => $request->name,
                'email' => $request->email,
            ]);

            if ($request->filled('password')) {
                $user->update([
                    'password' => Hash::make($request->password)
                ]);
            }

            $siswa->update([
                'nis' => $request->nis,
                'nisn' => $request->nisn,
                'tahun_masuk' => $request->tahun_masuk,
                'tingkat_awal' => $request->tingkat_awal,
                'jurusan' => $request->jurusan,
                'jenis_kelamin' => $request->jenis_kelamin,
                'ekstrakurikuler_id' => $ekskul,
                'no_telp_siswa' => $request->no_telp_siswa,
                'tempat_lahir' => $request->tempat_lahir,
                'tanggal_lahir' => $request->tanggal_lahir,
                'alamat' => $request->alamat,
                'nama_ayah' => $request->nama_ayah,
                'no_telp_ayah' => $request->no_telp_ayah,
                'nama_ibu' => $request->nama_ibu,
                'no_telp_ibu' => $request->no_telp_ibu,
                'tingkatan' => $request->tingkatan,
            ]);
        });

        // =========================
        // NEW DATA
        // =========================

        $newEkskul = Ekstrakurikuler::whereIn('id', $request->ekstrakurikuler_id ?? [])
            ->pluck('nama')
            ->implode(', ') ?: '-';

        $newData = [
            'Nama' => $request->name,
            'Email' => $request->email,
            'NIS' => $request->nis,
            'NISN' => $request->nisn,
            'Tahun Masuk' => $request->tahun_masuk,
            'Tingkat Awal' => $request->tingkat_awal,
            'Jurusan' => $request->jurusan,
            'Jenis Kelamin' => $request->jenis_kelamin,
            'No Telp Siswa' => $request->no_telp_siswa,
            'Tempat Lahir' => $request->tempat_lahir,
            'Tanggal Lahir' => $request->tanggal_lahir,
            'Alamat' => $request->alamat,
            'Nama Ayah' => $request->nama_ayah,
            'No Telp Ayah' => $request->no_telp_ayah,
            'Nama Ibu' => $request->nama_ibu,
            'No Telp Ibu' => $request->no_telp_ibu,
            'Tingkatan' => $request->tingkatan,
            'Ekskul' => $newEkskul,
        ];

        // =========================
        // DETEKSI PERUBAHAN
        // =========================
        $changes = [];

        foreach ($oldData as $field => $oldValue) {

            $newValue = $newData[$field] ?? null;

            if ($oldValue != $newValue) {
                $changes[] = "{$field}: {$oldValue} → {$newValue}";
            }
        }

        if ($request->filled('password')) {
            $changes[] = "Password berhasil diperbarui";
        }

        $message = count($changes) > 0
            ? "Data siswa berhasil diperbarui:\n- " . implode("\n- ", $changes)
            : "Tidak ada data yang diubah";

        return back()->with('success', $message);
    }
    public function destroy($id)
    {
        $siswa = Siswa::findOrFail($id);

        $user = $siswa->user;

        $siswa->delete();

        if ($user) {
            $user->delete();
        }

        return back()->with(
            'success',
            'Data siswa berhasil dihapus.'
        );
    }

    public function export(Request $request)
    {
        return Excel::download(
            new SiswaExport(
                $request->ekskul,
                $request->get('tahun_ajaran', $this->getCurrentTahunAjaran()),
                $request->kelas,
                $request->jurusan,
                $request->search
            ),
            'data-siswa.xlsx'
        );
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        Excel::import(
            new SiswaImport,
            $request->file('file')
        );

        return back()->with(
            'success',
            'Import data siswa berhasil.'
        );
    }

    private function parseTahunAjaranStart(string $tahunAjaran): int
    {
        return (int) explode('/', $tahunAjaran)[0];
    }

    private function getCurrentTahunAjaran(): string
    {
        $year = now()->month >= 7
            ? now()->year
            : now()->year - 1;

        return $year . '/' . ($year + 1);
    }

    private function getTahunAjaranList(): array
    {
        $range = Siswa::whereNotNull('tahun_masuk')
            ->selectRaw('MIN(tahun_masuk) as min_tahun, MAX(tahun_masuk) as max_tahun')
            ->first();

        if (!$range || !$range->min_tahun) {
            return [];
        }

        $currentYear = now()->month >= 7
            ? now()->year
            : now()->year - 1;

        $maxLimit = max($currentYear, $range->max_tahun);

        $list = [];

        for ($y = $range->min_tahun; $y <= $maxLimit; $y++) {
            $list[] = $y . '/' . ($y + 1);
        }

        return array_reverse($list);
    }

    private function getTingkat($siswa, int $tahunAjaranStart): ?int
    {
        if (!$siswa->tahun_masuk) {
            return null;
        }

        $tingkat = ($tahunAjaranStart - $siswa->tahun_masuk)
            + $siswa->tingkat_awal;

        return ($tingkat >= 7 && $tingkat <= 9)
            ? $tingkat
            : null;
    }

    private function getKelasDisplay($siswa, int $tahunAjaranStart): string
    {
        $tingkat = $this->getTingkat($siswa, $tahunAjaranStart);

        if ($tingkat === null) {

            $kelasAsli = ($tahunAjaranStart - $siswa->tahun_masuk) + $siswa->tingkat_awal;

            if ($kelasAsli > 9) {
                return 'Lulus';
            }

            return '-';
        }

        $label = match ($tingkat) {
            7 => 'VII',
            8 => 'VIII',
            9 => 'IX',
            default => '?',
        };

         $jurusan = preg_replace(
            '/^(VII|VIII|IX)\s+/i',
            '',
            $siswa->jurusan ?? ''
        );

        return trim($label.' '.$jurusan);
    }
}
