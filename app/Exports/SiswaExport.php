<?php

namespace App\Exports;

use App\Models\Siswa;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SiswaExport implements FromCollection, WithHeadings
{
    protected $ekskulId;
    protected $tahunAjaran;
    protected $kelas;
    protected $jurusan;
    protected $search;

    public function __construct(
        $ekskulId = null,
        $tahunAjaran = null,
        $kelas = null,
        $jurusan = null,
        $search = null
    ) {
        $this->ekskulId = $ekskulId;
        $this->tahunAjaran = $tahunAjaran;
        $this->kelas = $kelas;
        $this->jurusan = $jurusan;
        $this->search = $search;
    }

    public function collection()
    {
        $query = Siswa::with(['user']);

        $tahunStart = null;

        if ($this->tahunAjaran && $this->tahunAjaran !== 'semua') {

            $tahunStart = (int) explode('/', $this->tahunAjaran)[0];

            $currentStart = now()->month >= 7
                ? now()->year
                : now()->year - 1;

            $query->where(function ($q) use ($tahunStart, $currentStart) {

                $q->whereNull('tahun_masuk');

                if ($tahunStart == $currentStart) {

                    $q->orWhereRaw(
                        '(? - tahun_masuk) + tingkat_awal BETWEEN 7 AND 9',
                        [$tahunStart]
                    );

                } else {

                    $q->orWhereRaw(
                        '? BETWEEN tahun_masuk AND (tahun_masuk + (12 - tingkat_awal))',
                        [$tahunStart]
                    );

                }
            });
        }

        // filter jurusan / kode kelas
        if ($this->jurusan) {
            $query->where('jurusan', $this->jurusan);
        }

        // filter ekskul
        if ($this->ekskulId && $this->ekskulId !== 'all') {
            $query->whereJsonContains('ekstrakurikuler_id', (int) $this->ekskulId);
        }

        // filter kelas (tingkat 7/8/9) — dihitung relatif terhadap tahun ajaran yang dipilih
        if ($this->kelas) {

            $tahunUntukKelas = $tahunStart ?? (now()->month >= 7
                ? now()->year
                : now()->year - 1);

            $query->whereRaw(
                '(? - tahun_masuk) + tingkat_awal = ?',
                [$tahunUntukKelas, $this->kelas]
            );
        }

        // search nama
        if ($this->search) {
            $search = $this->search;

            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%');
            });
        }

        $ekskuls = \App\Models\Ekstrakurikuler::all()->keyBy('id');

        return $query->get()
            ->map(function ($siswa) use ($ekskuls) {
                $siswaEkskulIds = is_array($siswa->ekstrakurikuler_id)
                    ? $siswa->ekstrakurikuler_id
                    : json_decode($siswa->ekstrakurikuler_id, true) ?? [];

                $ekskulNames = collect($siswaEkskulIds)
                    ->map(fn($id) => $ekskuls->get($id)->nama ?? null)
                    ->filter()
                    ->implode(', ');

                return [
                    'nama'                => $siswa->user->name ?? '-',
                    'email'               => $siswa->user->email ?? '-',
                    'nis'                 => $siswa->nis,
                    'nisn'                => $siswa->nisn,

                    'kelas'               => $this->generateKelas($siswa),

                    'jurusan'             => $siswa->jurusan,
                    'tingkat_awal'        => $siswa->tingkat_awal,
                    'tahun_masuk'         => $siswa->tahun_masuk,
                    'jenis_kelamin'       => $siswa->jenis_kelamin,
                    'alamat'              => $siswa->alamat,
                    'tempat_lahir'        => $siswa->tempat_lahir,
                    'tanggal_lahir'       => $siswa->tanggal_lahir,
                    'nama_ayah'           => $siswa->nama_ayah,
                    'nama_ibu'            => $siswa->nama_ibu,
                    'no_telp_ayah'        => $siswa->no_telp_ayah,
                    'no_telp_ibu'         => $siswa->no_telp_ibu,
                    'no_telp_siswa'       => $siswa->no_telp_siswa,
                    'ekstrakurikuler'     => $ekskulNames ?: '-',
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Nama',
            'Email',
            'NIS',
            'NISN',
            'Kelas',
            'Kode Kelas',
            'Tingkat Awal',
            'Tahun Masuk',
            'Jenis Kelamin',
            'Alamat',
            'Tempat Lahir',
            'Tanggal Lahir',
            'Nama Ayah',
            'Nama Ibu',
            'No Telp Ayah',
            'No Telp Ibu',
            'No Telp Siswa',
            'Ekstrakurikuler',
        ];
    }

    private function generateKelas($siswa)
    {
        if (!$siswa->tahun_masuk || !$siswa->tingkat_awal) {
            return '-';
        }

        if ($this->tahunAjaran && $this->tahunAjaran !== 'semua') {
            $tahun = (int) explode('/', $this->tahunAjaran)[0];
        } else {
            $tahun = now()->month >= 7
                ? now()->year
                : now()->year - 1;
        }

        $tingkat = ($tahun - $siswa->tahun_masuk)
            + $siswa->tingkat_awal;

        if ($tingkat > 9) {
            return 'Lulus';
        }

        return match ($tingkat) {
            7 => 'VII',
            8 => 'VIII',
            9 => 'IX',
            default => (string) $tingkat,
        };
    }
}