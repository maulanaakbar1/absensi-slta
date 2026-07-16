<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ekstrakurikuler;
use App\Models\Pembina;
use App\Models\Siswa;
use App\Models\Absensi;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Statistik Ringkasan
        $totalEkskul = Ekstrakurikuler::count();
        $totalSiswa = Siswa::count();
        $totalPembina = Pembina::count();
        
        // Menghitung kehadiran rata-rata hari ini
        $kehadiranHariIni = Absensi::whereDate('tanggal', Carbon::today())->count();
        $persentaseHadir = $totalSiswa > 0 ? round(($kehadiranHariIni / $totalSiswa) * 100) : 0;

        // Data untuk Chart (7 Hari Terakhir)
        $chartData = Absensi::select(DB::raw('DATE(tanggal) as date'), DB::raw('count(*) as total'))
            ->where('tanggal', '>=', Carbon::now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get();

        $labels = $chartData->map(fn($d) => Carbon::parse($d->date)->isoFormat('D MMM'));
        $values = $chartData->pluck('total');

        return view('admin.dashboard', compact(
            'totalEkskul', 
            'totalSiswa', 
            'totalPembina', 
            'persentaseHadir',
            'labels',
            'values'
        ));
    }
}