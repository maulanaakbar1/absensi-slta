<?php

namespace App\Providers;

use App\Models\Ekstrakurikuler;
use App\Models\Siswa;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useTailwind();

        View::composer('*', function ($view) {
            if (Auth::check() && Auth::user()->role == 'siswa') {
                $siswa = Siswa::where('user_id', auth()->id())->first();
                $id_eskul = json_decode($siswa->ekstrakurikuler_id, true);
                $eskul = Ekstrakurikuler::whereIn('id', $id_eskul)->get();
                $view->with('eskul', $eskul);
            }
        });
    }
}