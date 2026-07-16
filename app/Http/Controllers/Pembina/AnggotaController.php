<?php

namespace App\Http\Controllers\Pembina;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Models\User;
use App\Models\Pembina;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Exports\SiswaExport;
use App\Imports\SiswaImport;
use Maatwebsite\Excel\Facades\Excel;

class AnggotaController extends Controller
{
    public function index(Request $request)
    {
        $pembina = Pembina::where('user_id', Auth::id())->firstOrFail();
        $ekskulId = $pembina->ekstrakurikuler_id;

        // Default: tampilkan tahun ajaran sekarang
        $selectedTahun = $request->get('tahun_ajaran', $this->getCurrentTahunAjaran());

        // Filter
        $selectedKelas = $request->get('kelas');
        $selectedJurusan = $request->get('jurusan');

        $selectedTahunStart = $selectedTahun !== 'semua'
            ? $this->parseTahunAjaranStart($selectedTahun)
            : $this->parseTahunAjaranStart($this->getCurrentTahunAjaran());

        $query = Siswa::with(['user'])
            ->whereJsonContains('ekstrakurikuler_id', $ekskulId);

        // Filter berdasarkan tahun ajaran (jika bukan 'semua')
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

        // PERBAIKAN FILTER KELAS: Dihitung langsung di query database
        if ($selectedKelas) {
            $query->where(function ($q) use ($selectedTahunStart, $selectedKelas) {
                $q->whereRaw('(? - tahun_masuk) + tingkat_awal = ?', [$selectedTahunStart, $selectedKelas]);
            });
        }

        // Search nama siswa - SUDAH DIPERBAIKI (menggunakan 'use')
        if ($request->filled('search')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            });
        }

        // Filter Jurusan
        if ($selectedJurusan) {
            $query->where('jurusan', $selectedJurusan);
        }

        // Urutkan dan Paginate
        $anggota = $query
            ->join('users', 'siswas.user_id', '=', 'users.id')
            ->orderBy('siswas.tingkat_awal', 'asc')
            ->orderBy('siswas.jurusan', 'asc')
            ->orderBy('users.name', 'asc')
            ->select('siswas.*')
            ->paginate(10)
            ->withQueryString();

        // Transform data display teks kelas
        $anggota->getCollection()->transform(function ($siswa) use ($selectedTahunStart) {
            $tingkat = $this->getTingkat($siswa, $selectedTahunStart);
            $siswa->kelas_display = $this->getKelasDisplay($siswa, $selectedTahunStart);
            $siswa->tingkat_display = $tingkat;

            return $siswa;
        });

        // dd($anggota);



        // Dropdown data pendukung
        $tahunAjaranList = $this->getTahunAjaranList($ekskulId);
        $jurusanList = Siswa::whereJsonContains('ekstrakurikuler_id', $ekskulId)
            ->whereNotNull('jurusan')
            ->select('jurusan')
            ->distinct()
            ->orderBy('jurusan')
            ->pluck('jurusan')
            ->toArray();

        if ($selectedTahun !== 'semua' && !in_array($selectedTahun, $tahunAjaranList)) {
            $tahunAjaranList[] = $selectedTahun;
        }

        return view('pembina.anggota', compact(
            'anggota',
            'tahunAjaranList',
            'selectedTahun',
            'selectedKelas',
            'selectedJurusan',
            'jurusanList'
        ));
    }

    public function store(Request $request)
    {
        $pembina = Pembina::where('user_id', Auth::id())->firstOrFail();
        $ekskulId = $pembina->ekstrakurikuler_id;

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'nis' => 'required|unique:siswas,nis',
            'nisn' => 'required|unique:siswas,nisn',
            'tahun_masuk' => 'required|integer|min:2000|max:2100',
            'tingkat_awal' => 'required|in:7,8,9',
            'jurusan' => 'required|string|max:50',
            'jenis_kelamin' => 'required|in:L,P',
            'tingkatan' => 'required|in:balonpas,instruktur',
        ], [
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'siswa',
        ]);

        Siswa::create([
            'user_id' => $user->id,
            'ekstrakurikuler_id' => json_encode([$ekskulId]),
            'tahun_masuk' => $request->tahun_masuk,
            'tingkat_awal' => $request->tingkat_awal,
            'jurusan' => $request->jurusan,

            'kelas' => $this->generateKelas(
                $request->tingkat_awal,
                $request->jurusan
            ),

            'nis' => $request->nis,
            'nisn' => $request->nisn,
            'jenis_kelamin' => $request->jenis_kelamin,
            'tingkatan' => $request->tingkatan,
        ]);

        return back()->with('success', 'Anggota berhasil ditambahkan ke ekstrakurikuler Anda!');
    }

    public function update(Request $request, $id)
    {
        $siswa = Siswa::findOrFail($id);
        $user  = $siswa->user;

        // Simpan data lama
        $oldData = [
            'Nama'            => $user->name,
            'Email'           => $user->email,
            'NIS'             => $siswa->nis,
            'NISN'            => $siswa->nisn,
            'Tahun Masuk'     => $siswa->tahun_masuk,
            'Kelas Awal'      => $siswa->tingkat_awal,
            'Jurusan'         => $siswa->jurusan,
            'Jenis Kelamin'   => $siswa->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan',
            'Tingkatan'       => ucfirst($siswa->tingkatan),
        ];

        $request->validate([
            'name'           => 'required|string|max:255',
            'email'          => 'required|email|unique:users,email,' . $user->id,
            'nis'            => 'required|unique:siswas,nis,' . $siswa->id,
            'nisn'           => 'required|unique:siswas,nisn,' . $siswa->id,
            'tahun_masuk'    => 'required|integer|min:2000|max:2100',
            'tingkat_awal'   => 'required|in:7,8,9',
            'jurusan'        => 'required|string|max:50',
            'jenis_kelamin'  => 'required|in:L,P',
            'tingkatan'      => 'required|in:balonpas,instruktur',
        ]);

        $user->update([
            'name'  => $request->name,
            'email' => $request->email,
        ]);

        if ($request->filled('password')) {
            $user->update([
                'password' => Hash::make($request->password)
            ]);
        }

        $siswa->update([
            'tahun_masuk'   => $request->tahun_masuk,
            'tingkat_awal'  => $request->tingkat_awal,
            'jurusan'       => $request->jurusan,

            'kelas' => $this->generateKelas(
                $request->tingkat_awal,
                $request->jurusan
            ),

            'nis'            => $request->nis,
            'nisn'           => $request->nisn,
            'jenis_kelamin'  => $request->jenis_kelamin,
            'tingkatan'      => $request->tingkatan,
        ]);

        // Data baru
        $newData = [
            'Nama'            => $request->name,
            'Email'           => $request->email,
            'NIS'             => $request->nis,
            'NISN'            => $request->nisn,
            'Tahun Masuk'     => $request->tahun_masuk,
            'Kelas Awal'      => $request->tingkat_awal,
            'Jurusan'         => $request->jurusan,
            'Jenis Kelamin'   => $request->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan',
            'Tingkatan'       => ucfirst($request->tingkatan),
        ];

        $changes = [];

        foreach ($oldData as $field => $oldValue) {
            $newValue = $newData[$field];

            if ((string)$oldValue !== (string)$newValue) {
                $changes[] = "$field: $oldValue → $newValue";
            }
        }

        return back()->with([
            'success' => 'Data anggota berhasil diperbarui!',
            'changes' => $changes
        ]);
    }

    public function show($id)
    {
        $pembina = Pembina::where('user_id', Auth::id())->firstOrFail();

        $siswa = Siswa::with(['user'])
            ->whereJsonContains(
                'ekstrakurikuler_id',
                $pembina->ekstrakurikuler_id
            )
            ->findOrFail($id);

        $tahunAjaran = $this->getCurrentTahunAjaran();
        $tahunStart = $this->parseTahunAjaranStart($tahunAjaran);

        $kelasDisplay = $this->getKelasDisplay(
            $siswa,
            $tahunStart
        );


        return view('pembina.anggota-show', compact(
            'siswa',
            'tahunAjaran',
            'kelasDisplay'
        ));
    }

    public function destroy($id)
    {
        $siswa = Siswa::findOrFail($id);
        $siswa->user->delete();

        return back()->with('success', 'Anggota berhasil dihapus!');
    }

    public function export(Request $request)
    {
        $pembina = Pembina::where('user_id', Auth::id())->firstOrFail();

        return Excel::download(
            new SiswaExport(
                $pembina->ekstrakurikuler_id,
                $request->get('tahun_ajaran', $this->getCurrentTahunAjaran()),
                $request->kelas,
                $request->jurusan,
                $request->search
            ),
            'anggota-ekskul.xlsx'
        );
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);
        // dd($request->file('file'));

        $pembina = Pembina::where('user_id', Auth::id())->firstOrFail();
        // dd($pembina);

        Excel::import(
            new SiswaImport($pembina->ekstrakurikuler_id),
            $request->file('file')
        );

        return back()->with(
            'success',
            'Data anggota berhasil diimport!'
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
        $pembina = Pembina::where('user_id', Auth::id())->firstOrFail();

        $range = Siswa::whereJsonContains('ekstrakurikuler_id', $pembina->ekstrakurikuler_id)
            ->whereNotNull('tahun_masuk')
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

        return trim($label . ' ' . $jurusan);
    }

    private function generateKelas($tingkat, $jurusan)
    {
        $label = match ((int)$tingkat) {
            7 => 'VII',
            8 => 'VIII',
            9 => 'IX',
            default => '',
        };

        return trim($label . ' ' . $jurusan);
    }
}
