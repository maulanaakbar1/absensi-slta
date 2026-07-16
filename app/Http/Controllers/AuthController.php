<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login()
    {
        // Mengarah ke folder views/auth/login.blade.php
        return view('auth.login');
    }

    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {

            $request->session()->regenerate();

            $user = Auth::user();

            // =========================
            // CEK SISWA PUNYA PEMBINA?
            // =========================
            if ($user->role === 'siswa') {

                // dd($request->all());
                $siswa = $user->siswa;
                $ekskulIds = json_decode($siswa->ekstrakurikuler_id, true) ?? [];

                sort($ekskulIds);

                $firstEkskul = $ekskulIds[0] ?? null;

                if ($firstEkskul) {
                    session()->put('ekskul_aktif', $firstEkskul);
                }

                // kalau belum punya ekskul
                if (!$siswa || !$siswa->ekstrakurikuler_id) {

                    Auth::logout();

                    return back()->with(
                        'loginError',
                        'Anda belum terdaftar ke ekstrakurikuler.'
                    );
                }

                // cek pembina berdasarkan ekskul siswa
                $pembinaAda = \App\Models\Pembina::where(
                    'ekstrakurikuler_id',
                    $siswa->ekstrakurikuler_id
                )->exists();

                // kalau belum ada pembina
                // if (!$pembinaAda) {

                //     Auth::logout();

                //     return back()->with(
                //         'loginError',
                //         'Ekstrakurikuler Anda belum memiliki pembina.'
                //     );
                // }
            }

            // =========================
            // LOGIN BERHASIL
            // =========================
            $pesanSukses = 'Selamat datang kembali, ' . $user->name . '!';

            if ($user->role === 'admin') {
                return redirect()
                    ->intended('/admin/dashboard')
                    ->with('loginSuccess', $pesanSukses);
            }

            if ($user->role === 'pembina') {
                return redirect()
                    ->intended('/pembina/dashboard')
                    ->with('loginSuccess', $pesanSukses);
            }

            return redirect()
                ->intended('/siswa/dashboard')
                ->with('loginSuccess', $pesanSukses);
        }

        return back()->with('loginError', 'Email atau password salah!');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('logoutSuccess', 'Anda telah berhasil keluar dari sistem.');
    }
}
