<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Siswa;
use App\Models\Ekstrakurikuler;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class SiswaImport implements ToModel, WithHeadingRow, SkipsEmptyRows
{
    public function model(array $row)
    {
        if (
            !empty($row['email']) &&
            User::where('email', $row['email'])->exists()
        ) {
            return null;
        }

        if (
            !empty($row['nis']) &&
            Siswa::where('nis', $row['nis'])->exists()
        ) {
            return null;
        }

        if (
            !empty($row['nisn']) &&
            Siswa::where('nisn', $row['nisn'])->exists()
        ) {
            return null;
        }

        $namaEkskulList = collect(explode(',', $row['ekstrakurikuler'] ?? ''))
            ->map(fn($nama) => trim($nama))
            ->filter()
            ->filter(fn($nama) => strtolower($nama) !== '-');

        $ekskulIds = Ekstrakurikuler::whereIn('nama', $namaEkskulList)
            ->pluck('id')
            ->map(fn($id) => (int) $id)
            ->values()
            ->all();

        $tingkatAwal = isset($row['tingkat_awal']) && $row['tingkat_awal'] !== ''
            ? (int) $row['tingkat_awal']
            : null;

        $jurusan = trim(
            $row['kode_kelas']
                ?? $row['jurusan'] 
                ?? ''
        );

        $user = User::create([
            'name' => $row['nama'] ?? '-',
            'email' => $row['email'],
            'password' => Hash::make('123456'),
            'role' => 'siswa',
        ]);

        return new Siswa([
            'user_id'             => $user->id,
            'ekstrakurikuler_id'  => !empty($ekskulIds) ? json_encode($ekskulIds) : null,

            'nis'                 => $row['nis'] ?? null,
            'nisn'                => $row['nisn'] ?? null,

            'jurusan'             => $jurusan,
            'tingkat_awal'        => $tingkatAwal,
            'tahun_masuk'         => $row['tahun_masuk'] ?? null,

            'jenis_kelamin'       => $row['jenis_kelamin'] ?? 'L',

            'alamat'              => $row['alamat'] ?? null,
            'tempat_lahir'        => $row['tempat_lahir'] ?? null,
            'tanggal_lahir'       => $this->parseTanggal($row['tanggal_lahir'] ?? null),

            'nama_ayah'           => $row['nama_ayah'] ?? null,
            'nama_ibu'            => $row['nama_ibu'] ?? null,

            'no_telp_ayah'        => $row['no_telp_ayah'] ?? null,
            'no_telp_ibu'         => $row['no_telp_ibu'] ?? null,
            'no_telp_siswa'       => $row['no_telp_siswa'] ?? null,

            'tingkatan'           => $row['tingkatan'] ?? 'balonpas',
        ]);
    }

    private function parseTanggal($value)
    {
        if (empty($value)) {
            return null;
        }

        if (is_numeric($value)) {
            try {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)
                    ->format('Y-m-d');
            } catch (\Throwable $e) {
                return null;
            }
        }

        try {
            return \Carbon\Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }
}