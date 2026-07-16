<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // 1. Cek apakah user sudah login dan apakah rolenya diizinkan
        if (Auth::check() && in_array(Auth::user()->role, $roles)) {
            
            $user = Auth::user();

            // 2. LOGIKA TAMBAHAN: Cek biodata jika user adalah siswa
            if ($user->role === 'siswa' && $user->siswa) {
                $siswa = $user->siswa;

                // Tentukan field mana yang wajib (contoh: nisn, alamat, foto_profil)
                $isComplete = $siswa->nisn && $siswa->alamat && $siswa->nama_ayah;

                if (!$isComplete) {
                    // Kirim pesan peringatan ke session tanpa memblokir akses ke dashboard
                    session()->flash('warning_data', 'Biodata Anda belum lengkap! Silahkan lengkapi profil agar bisa melakukan absensi.');
                }
            }

            return $next($request);
        }

        return redirect('/login')->with('loginError', 'Anda tidak memiliki akses ke halaman tersebut.');
    }
}