<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $siswa = $user->siswa;

        $tahunAjaran = $this->getCurrentTahunAjaran();
        $tahunAjaranStart = (int) explode('/', $tahunAjaran)[0];

        $kelasDisplay = $this->getKelasDisplay(
            $siswa,
            $tahunAjaranStart
        );

        $isProfileComplete =
            !empty($siswa->no_telp_siswa) &&
            !empty($siswa->tempat_lahir) &&
            !empty($siswa->tanggal_lahir) &&
            !empty($siswa->alamat) &&
            !empty($siswa->nama_ayah) &&
            !empty($siswa->no_telp_ayah) &&
            !empty($siswa->nama_ibu) &&
            !empty($siswa->no_telp_ibu);

        return view('siswa.profile', compact(
            'user',
            'siswa',
            'tahunAjaran',
            'kelasDisplay',
            'isProfileComplete'
        ));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $siswa = $user->siswa;

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'nis' => 'required|string|unique:siswas,nis,' . $siswa->id,
            'jenis_kelamin' => 'required|in:L,P',
            'nisn' => 'required|string|unique:siswas,nisn,' . $siswa->id,
            'alamat' => 'nullable|string',
            'tempat_lahir' => 'nullable|string',
            'tanggal_lahir' => 'nullable|date',
            'nama_ayah' => 'nullable|string',
            'nama_ibu' => 'nullable|string',
            'no_telp_ayah' => 'nullable|string|max:15',
            'no_telp_ibu' => 'nullable|string|max:15',
            'no_telp_siswa' => 'nullable|string|max:15',
            'password' => 'nullable|min:6|confirmed',
        ]);

        $changes = [];

        // USER
        if ($user->name !== $request->name) {
            $changes[] = 'Nama';
        }

        if ($user->email !== $request->email) {
            $changes[] = 'Email';
        }

        if (!empty($request->password)) {
            $changes[] = 'Password';
        }

        // SISWA
        if ($siswa->nis !== $request->nis) {
            $changes[] = 'NIS';
        }

        if ($siswa->nisn !== $request->nisn) {
            $changes[] = 'NISN';
        }

        if (($siswa->alamat ?? '') !== $request->alamat) {
            $changes[] = 'Alamat';
        }

        if (($siswa->tempat_lahir ?? '') !== $request->tempat_lahir) {
            $changes[] = 'Tempat Lahir';
        }

        if (
            optional($siswa->tanggal_lahir)->format('Y-m-d')
            !== $request->tanggal_lahir
        ) {
            $changes[] = 'Tanggal Lahir';
        }

        if (($siswa->jenis_kelamin ?? '') !== $request->jenis_kelamin) {
            $changes[] = 'Jenis Kelamin';
        }

        if (($siswa->nama_ayah ?? '') !== $request->nama_ayah) {
            $changes[] = 'Nama Ayah';
        }

        if (($siswa->nama_ibu ?? '') !== $request->nama_ibu) {
            $changes[] = 'Nama Ibu';
        }

        if (($siswa->no_telp_ayah ?? '') !== $request->no_telp_ayah) {
            $changes[] = 'No. Telp Ayah';
        }

        if (($siswa->no_telp_ibu ?? '') !== $request->no_telp_ibu) {
            $changes[] = 'No. Telp Ibu';
        }

        if (($siswa->no_telp_siswa ?? '') !== $request->no_telp_siswa) {
            $changes[] = 'No. Telp Siswa';
        }

        // UPDATE USER
        $user->name = $request->name;
        $user->email = $request->email;

        if ($request->password) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        // UPDATE SISWA
        $siswa->update([
            'nis' => $request->nis,
            'jenis_kelamin' => $request->jenis_kelamin,
            'nisn' => $request->nisn,
            'alamat' => $request->alamat,
            'tempat_lahir' => $request->tempat_lahir,
            'tanggal_lahir' => $request->tanggal_lahir,
            'nama_ayah' => $request->nama_ayah,
            'nama_ibu' => $request->nama_ibu,
            'no_telp_ayah' => $request->no_telp_ayah,
            'no_telp_ibu' => $request->no_telp_ibu,
            'no_telp_siswa' => $request->no_telp_siswa,
        ]);

        // ALERT MESSAGE
        if (count($changes)) {
            $message = implode(', ', $changes) . ' berhasil diperbarui!';
        } else {
            $message = 'Tidak ada perubahan data.';
        }

        return redirect()
            ->route('siswa.profile')
            ->with('success', $message);
    }

    private function getCurrentTahunAjaran(): string
    {
        $year = now()->month >= 7
            ? now()->year
            : now()->year - 1;

        return $year . '/' . ($year + 1);
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

        if (!$tingkat) {
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
}