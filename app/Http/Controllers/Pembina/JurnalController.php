<?php

namespace App\Http\Controllers\Pembina;

use Carbon\Carbon;
use App\Models\Siswa;
use App\Models\Jadwal;
use App\Models\Absensi;
use App\Models\Pembina;
use App\Models\HariLibur;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;

class JurnalController extends Controller
{
    public function index(Request $request)
    {
        Carbon::setLocale('id');

        $tanggalFilter = $request->tanggal;
        $bulan = $request->bulan ?? now()->month;
        $tahunAjaranList = $this->getTahunAjaranList();

        $tahunAjaran = $request->tahun_ajaran
            ?? ($tahunAjaranList[0] ?? $this->getCurrentTahunAjaran());

        $tahunMulai = (int) explode('/', $tahunAjaran)[0];
        $tahunSelesai = $tahunMulai + 1;

        $tahun = $bulan >= 7 ? $tahunMulai : $tahunSelesai;
        $tahunAjaranList = $this->getTahunAjaranList();

        $ekskulId = auth()->user()->pembina->ekstrakurikuler_id;

        $jadwals = Jadwal::where('ekstrakurikuler_id', $ekskulId)->get();
        $liburs = HariLibur::where('ekstrakurikuler_id', $ekskulId)->get();
        $totalAnggota = Siswa::where('ekstrakurikuler_id', $ekskulId)->count();

        $events = collect();
        $start = Carbon::create($tahun, $bulan, 1)->startOfMonth();
        $end = Carbon::create($tahun, $bulan, 1)->endOfMonth();

        $hariMap = [
            'Minggu' => 0,
            'Senin' => 1,
            'Selasa' => 2,
            'Rabu' => 3,
            'Kamis'  => 4,
            'Jumat' => 5,
            'Sabtu' => 6,
        ];

        foreach ($jadwals->whereNull('tanggal') as $jadwal) {
            if (!isset($hariMap[$jadwal->hari])) continue;
            $targetDay = $hariMap[$jadwal->hari];
            $tanggalLoop = $start->copy();

            while ($tanggalLoop <= $end) {
                if ($tanggalLoop->dayOfWeek == $targetDay) {
                    $libur = $this->isLibur($tanggalLoop, $liburs);
                    $events->push(
                        $this->buildEvent($jadwal, $tanggalLoop->copy(), $totalAnggota, $libur ? true : false, $libur?->keterangan)
                    );
                }
                $tanggalLoop->addDay();
            }
        }

        foreach ($jadwals->whereNotNull('tanggal') as $jadwal) {
            $tanggal = Carbon::parse($jadwal->tanggal);
            if ($tanggal->month == $bulan && $tanggal->year == $tahun) {
                $libur = $this->isLibur($tanggal, $liburs);
                $events->push(
                    $this->buildEvent($jadwal, $tanggal->copy(), $totalAnggota, $libur ? true : false, $libur?->keterangan)
                );
            }
        }

        foreach ($liburs->whereNotNull('tanggal') as $libur) {
            $tanggal = Carbon::parse($libur->tanggal);
            if ($tanggal->month == $bulan && $tanggal->year == $tahun) {
                $events->push([
                    'tanggal' => $tanggal,
                    'jadwal' => '-',
                    'jam' => '-',
                    'lokasi' => '-',
                    'keterangan' => null,
                    'hadir' => 0,
                    'total' => $totalAnggota,
                    'libur' => true,
                    'keterangan_libur' => $libur->keterangan,
                ]);
            }
        }

        foreach ($liburs->whereNull('tanggal') as $libur) {
            if (!isset($hariMap[$libur->hari])) continue;
            $targetDay = $hariMap[$libur->hari];
            $tanggalLoop = $start->copy();

            while ($tanggalLoop <= $end) {
                if ($tanggalLoop->dayOfWeek == $targetDay) {
                    $events->push([
                        'tanggal' => $tanggalLoop->copy(),
                        'jam' => '-',
                        'lokasi' => '-',
                        'keterangan' => null,
                        'hadir' => 0,
                        'total' => $totalAnggota,
                        'libur' => true,
                        'keterangan_libur' => $libur->keterangan,
                    ]);
                }
                $tanggalLoop->addDay();
            }
        }

        if ($tanggalFilter) {
            $events = $events->filter(function ($event) use ($tanggalFilter) {
                return Carbon::parse($event['tanggal'])->isSameDay(Carbon::parse($tanggalFilter));
            });
        }

        $events = $events->sortBy([
            ['tanggal', 'asc'],
            ['jam', 'asc']
        ])->values();

        $currentPage = LengthAwarePaginator::resolveCurrentPage();

        $perPage = 10;

        $currentItems = $events->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $paginatedEvents = new LengthAwarePaginator(
            $currentItems,
            $events->count(),
            $perPage,
            $currentPage,
            [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'query' => $request->query()
            ]
        );

        return view(
            'pembina.jurnal',
            [
                'events' => $paginatedEvents,
                'bulan' => $bulan,
                'tahun' => $tahun,
                'tahunAjaran' => $tahunAjaran,
                'tahunAjaranList' => $tahunAjaranList
            ]
        );
    }

    private function buildEvent($jadwal, $tanggal, $totalAnggota, $isLibur = false, $keteranganLibur = null)
    {
        $hadir = 0;

        if (!$isLibur) {
            $hadir = Absensi::whereDate('tanggal', $tanggal->toDateString())
                ->where('status', 'hadir')
                ->count();
        }

        return [
            'tanggal' => $tanggal->copy(),
            'jadwal' => ucfirst($jadwal->tipe), // Tambahkan ini
            'jam' => $jadwal->jam_mulai . ' - ' . $jadwal->jam_selesai,
            'lokasi' => $jadwal->lokasi,
            'keterangan' => $jadwal->keterangan,
            'hadir' => $hadir,
            'total' => $totalAnggota,
            'libur' => $isLibur,
            'keterangan_libur' => $keteranganLibur,
        ];
    }

    private function isLibur(Carbon $tanggal, Collection $liburs)
    {
        foreach ($liburs as $libur) {
            if ($libur->tanggal && Carbon::parse($libur->tanggal)->isSameDay($tanggal)) {
                return $libur;
            }

            if ($libur->hari && strtolower(trim($libur->hari)) == strtolower(trim($tanggal->translatedFormat('l')))) {
                return $libur;
            }
        }

        return null;
    }

    private function getCurrentTahunAjaran(): string
    {
        $year = now()->month >= 7 ? now()->year : now()->year - 1;
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

    public function exportPdf(Request $request)
    {
        Carbon::setLocale('id');

        $tanggalFilter = $request->tanggal;
        $bulan = $request->bulan ?? now()->month;

        $tahunAjaranList = $this->getTahunAjaranList();
        $tahunAjaran = $request->tahun_ajaran ?? ($tahunAjaranList[0] ?? $this->getCurrentTahunAjaran());

        $tahunMulai = (int) explode('/', $tahunAjaran)[0];
        $tahunSelesai = $tahunMulai + 1;
        $tahun = $bulan >= 7 ? $tahunMulai : $tahunSelesai;

        $ekskulId = auth()->user()->pembina->ekstrakurikuler_id;

        $jadwals = Jadwal::where('ekstrakurikuler_id', $ekskulId)->get();
        $liburs = HariLibur::where('ekstrakurikuler_id', $ekskulId)->get();
        $totalAnggota = Siswa::where('ekstrakurikuler_id', $ekskulId)->count();

        $events = collect();
        $start = Carbon::create($tahun, $bulan, 1)->startOfMonth();
        $end = Carbon::create($tahun, $bulan, 1)->endOfMonth();

        $hariMap = [
            'Minggu' => 0,
            'Senin' => 1,
            'Selasa' => 2,
            'Rabu' => 3,
            'Kamis' => 4,
            'Jumat' => 5,
            'Sabtu' => 6,
        ];

        foreach ($jadwals->whereNull('tanggal') as $jadwal) {
            if (!isset($hariMap[$jadwal->hari])) continue;
            $targetDay = $hariMap[$jadwal->hari];
            $tanggalLoop = $start->copy();

            while ($tanggalLoop <= $end) {
                if ($tanggalLoop->dayOfWeek == $targetDay) {
                    $libur = $this->isLibur($tanggalLoop, $liburs);
                    $events->push($this->buildEvent($jadwal, $tanggalLoop->copy(), $totalAnggota, $libur ? true : false, $libur?->keterangan));
                }
                $tanggalLoop->addDay();
            }
        }

        foreach ($jadwals->whereNotNull('tanggal') as $jadwal) {
            $tanggal = Carbon::parse($jadwal->tanggal);
            if ($tanggal->month == $bulan && $tanggal->year == $tahun) {
                $libur = $this->isLibur($tanggal, $liburs);
                $events->push($this->buildEvent($jadwal, $tanggal->copy(), $totalAnggota, $libur ? true : false, $libur?->keterangan));
            }
        }

        foreach ($liburs->whereNotNull('tanggal') as $libur) {
            $tanggal = Carbon::parse($libur->tanggal);
            if ($tanggal->month == $bulan && $tanggal->year == $tahun) {
                $events->push([
                    'tanggal' => $tanggal,
                    'jadwal' => '-',
                    'jam' => '-',
                    'lokasi' => '-',
                    'keterangan' => null,
                    'hadir' => 0,
                    'total' => $totalAnggota,
                    'libur' => true,
                    'keterangan_libur' => $libur->keterangan,
                ]);
            }
        }

        foreach ($liburs->whereNull('tanggal') as $libur) {
            if (!isset($hariMap[$libur->hari])) continue;
            $targetDay = $hariMap[$libur->hari];
            $tanggalLoop = $start->copy();

            while ($tanggalLoop <= $end) {
                if ($tanggalLoop->dayOfWeek == $targetDay) {
                    $events->push([
                        'tanggal' => $tanggalLoop->copy(),
                        'jam' => '-',
                        'lokasi' => '-',
                        'keterangan' => null,
                        'hadir' => 0,
                        'total' => $totalAnggota,
                        'libur' => true,
                        'keterangan_libur' => $libur->keterangan,
                    ]);
                }
                $tanggalLoop->addDay();
            }
        }

        if ($tanggalFilter) {
            $events = $events->filter(
                fn($e) =>
                Carbon::parse($e['tanggal'])->isSameDay(Carbon::parse($tanggalFilter))
            );
        }

        $events = $events->sortBy('tanggal')->values();

        $pembina = auth()->user()->pembina;

        $ekstrakurikuler = $pembina->ekstrakurikuler;

        $pdf = Pdf::loadView('pembina.jurnal_pdf', [
            'events' => $events,
            'bulan' => $bulan,
            'tahunAjaran' => $tahunAjaran,
            'ekstrakurikuler' => $ekstrakurikuler,
        ]);

        $namaBulan = Carbon::create()->month($bulan)->translatedFormat('F');

        return $pdf->download("Jurnal_Pembina_{$namaBulan}.pdf");
    }
}
