<?php

namespace App\Http\Controllers\Admin;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RekapAbsensiExport;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Models\Ekstrakurikuler;
use App\Models\HariLibur;
use App\Models\Jadwal;
use Illuminate\Http\Request;
use Carbon\Carbon;

class RekapAbsensiController extends Controller
{
    public function index(Request $request)
    {
        $bulan = $request->get('bulan', date('m'));
        $ekskul = $request->get('ekskul', 'all');

        // =========================
        // FILTER TAHUN AJARAN
        // =========================
        $selectedTahun = $request->get(
            'tahun_ajaran',
            $this->getCurrentTahunAjaran()
        );

        $selectedKelas = $request->get('kelas');

        // TAMBAHAN FILTER JURUSAN
        $selectedJurusan = $request->get('jurusan');

        // =========================
        // TAHUN AWAL TA
        // =========================
        $selectedTahunStart = $selectedTahun !== 'semua'
            ? $this->parseTahunAjaranStart($selectedTahun)
            : null;

        // =========================
        // TENTUKAN TAHUN
        // =========================
        if ((int) $bulan >= 7) {
            $tahun = $selectedTahunStart;
        } else {
            $tahun = $selectedTahunStart + 1;
        }

        $jumlahHari = Carbon::createFromDate(
            $tahun,
            $bulan,
            1
        )->daysInMonth;

        $query = Siswa::with([
            'user',
            'absensis' => function ($q) use ($bulan, $tahun) {
                $q->whereMonth('tanggal', $bulan)
                ->whereYear('tanggal', $tahun);
            }
        ]);

        // =========================
        // FILTER EKSKUL
        // =========================
        if ($ekskul != 'all') {
            $query->whereJsonContains(
                'ekstrakurikuler_id',
                (int) $ekskul
            );
        }

        // =========================
        // FILTER TAHUN AJARAN
        // =========================
        $currentStart = $this->parseTahunAjaranStart(
            $this->getCurrentTahunAjaran()
        );

        if ($selectedTahunStart) {

            $query->where(function ($q) use ($selectedTahunStart, $currentStart) {

                $q->whereNull('tahun_masuk');

                if ($selectedTahunStart == $currentStart) {

                    // Tahun ajaran terbaru → hanya siswa aktif
                    $q->orWhereRaw(
                        '(? - tahun_masuk) + tingkat_awal BETWEEN 7 AND 9',
                        [$selectedTahunStart]
                    );

                } else {

                    // Tahun ajaran lama → tampilkan semua termasuk yang lulus
                    $q->orWhereRaw(
                        '? >= tahun_masuk',
                        [$selectedTahunStart]
                    );

                }

            });

        }

        if ($selectedJurusan) {
            $query->where('jurusan', $selectedJurusan);
        }

        $siswas = $query
            ->orderBy('tingkat_awal', 'asc')
            ->orderBy('jurusan', 'asc')
            ->orderBy('nis', 'asc')
            ->get();

        $currentStart = $this->parseTahunAjaranStart($this->getCurrentTahunAjaran());

        $siswas->transform(function ($siswa) use ($selectedTahunStart, $selectedTahun, $currentStart) {

            $tahunDisplay = $selectedTahun === 'semua'
                ? $currentStart
                : $selectedTahunStart;

            $kelasAsli = ($tahunDisplay - $siswa->tahun_masuk) + $siswa->tingkat_awal;

            if ($kelasAsli > 9) {
                $siswa->kelas_display = 'Lulus';
                $siswa->tingkat_display = 'lulus';
            } else {
                $siswa->tingkat_display = $this->getTingkat($siswa, $tahunDisplay);
                $siswa->kelas_display = $this->getKelasDisplay($siswa, $tahunDisplay);
            }

            return $siswa;
        });

        if ($selectedTahun !== 'semua') {

            $query->where(function ($q) use ($selectedTahunStart, $currentStart) {

                $q->whereNull('tahun_masuk');

                if ($selectedTahunStart == $currentStart) {

                    // Tahun ajaran terbaru
                    $q->orWhereRaw(
                        '(? - tahun_masuk) + tingkat_awal BETWEEN 7 AND 9',
                        [$selectedTahunStart]
                    );

                } else {

                    // Tahun ajaran lama
                    $q->orWhereRaw(
                        '? >= tahun_masuk',
                        [$selectedTahunStart]
                    );

                }

            });

        }

        // =========================
        // FILTER KELAS
        // =========================
        if ($selectedKelas) {

            $siswas = $siswas->filter(function ($siswa) use ($selectedKelas) {
                return $siswa->tingkat_display == $selectedKelas;
            })->values();

        }

        $listEkskul = Ekstrakurikuler::all();

        $hariLibur = HariLibur::whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->get();

        $jadwals = Jadwal::all();

        // =========================
        // LIST TAHUN AJARAN
        // =========================
        $tahunAjaranList = $this->getTahunAjaranList();

        // =========================
        // LIST JURUSAN (BARU)
        // =========================
        $jurusanList = Siswa::whereNotNull('jurusan')
            ->select('jurusan')
            ->distinct()
            ->orderBy('jurusan')
            ->pluck('jurusan')
            ->toArray();

        $namaBulan = [
            '01'=>'Januari',
            '02'=>'Februari',
            '03'=>'Maret',
            '04'=>'April',
            '05'=>'Mei',
            '06'=>'Juni',
            '07'=>'Juli',
            '08'=>'Agustus',
            '09'=>'September',
            '10'=>'Oktober',
            '11'=>'November',
            '12'=>'Desember'
        ];

        return view('admin.rekap_absensi', compact(
            'siswas',
            'bulan',
            'tahun',
            'jumlahHari',
            'namaBulan',
            'listEkskul',
            'ekskul',
            'hariLibur',
            'jadwals',
            'tahunAjaranList',
            'selectedTahun',
            'selectedKelas',
            'selectedJurusan',
            'jurusanList'
        ));
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

        $kelasAsli = ($tahunAjaranStart - $siswa->tahun_masuk) + $siswa->tingkat_awal;

        if ($kelasAsli > 9) {
            return 'Lulus';
        }

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

    public function downloadPdf(Request $request)
    {
        $data = $this->getRekapData($request);

        $pdf = Pdf::loadView(
            'exports.rekap_absensi_pdf',
            $data
        )->setPaper('a4', 'landscape');

        return $pdf->download(
            'rekap-absensi-' . now()->format('YmdHis') . '.pdf'
        );
    }

    public function downloadExcel(Request $request)
    {
        $data = $this->getRekapData($request);

        return Excel::download(
            new RekapAbsensiExport($data),
            'rekap-absensi-' . now()->format('YmdHis') . '.xlsx'
        );
    }

    private function getRekapData(Request $request): array
    {
        $bulan = $request->get('bulan', date('m'));
        $selectedKelas = $request->get('kelas');

        $isAdmin = $request->is('admin/*') || auth()->user()->role === 'admin';

        if ($isAdmin) {
            $ekskul = $request->get('ekskul', 'all');
        } else {
            $ekskul = auth()->user()->pembina->ekstrakurikuler_id ?? $request->get('ekskul');
        }

        $selectedTahun = $request->get('tahun_ajaran', $this->getCurrentTahunAjaran());
        $currentStart  = $this->parseTahunAjaranStart($this->getCurrentTahunAjaran());

        $selectedTahunStart = $selectedTahun !== 'semua'
            ? $this->parseTahunAjaranStart($selectedTahun)
            : null;

        $tahunDasar = $selectedTahunStart ?? $currentStart;
        $tahun = ((int) $bulan >= 7) ? $tahunDasar : $tahunDasar + 1;

        $jumlahHari = Carbon::createFromDate($tahun, $bulan, 1)->daysInMonth;

        $query = Siswa::with([
            'user',
            'absensis' => function ($q) use ($bulan, $tahun) {
                $q->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun);
            }
        ]);

        if ($ekskul && $ekskul !== 'all') {
            $query->whereJsonContains('ekstrakurikuler_id', (int) $ekskul);
        }

        if ($selectedTahunStart) {
            $query->where(function ($q) use ($selectedTahunStart) {
                $q->whereNull('tahun_masuk')
                ->orWhere(function ($q2) use ($selectedTahunStart) {
                    $q2->whereRaw(
                        '? BETWEEN tahun_masuk AND (tahun_masuk + (9 - tingkat_awal))',
                        [$selectedTahunStart]
                    );
                });
            });
        }

        $siswas = $query->get();

        $siswas->transform(function ($siswa) use ($selectedTahunStart, $currentStart) {
            $tahunDisplay = $selectedTahunStart ?? $currentStart;
            $siswa->kelas_display   = $this->getKelasDisplay($siswa, $tahunDisplay);
            $siswa->tingkat_display = $this->getTingkat($siswa, $tahunDisplay);
            return $siswa;
        });

        if ($selectedKelas) {
            $siswas = $siswas->filter(fn($siswa) => $siswa->tingkat_display == $selectedKelas)->values();
        }

        $namaEkskul = 'Semua Ekskul';
        if ($ekskul && $ekskul !== 'all') {
            $ekskulModel = Ekstrakurikuler::find($ekskul);
            $namaEkskul = $ekskulModel ? $ekskulModel->nama : 'Semua Ekskul';
        }

        $namaBulan = [
            '01'=>'Januari', '02'=>'Februari', '03'=>'Maret',
            '04'=>'April',   '05'=>'Mei',      '06'=>'Juni',
            '07'=>'Juli',    '08'=>'Agustus',  '09'=>'September',
            '10'=>'Oktober', '11'=>'November', '12'=>'Desember'
        ];

        return compact(
            'siswas', 'bulan', 'tahun', 'jumlahHari',
            'namaBulan', 'selectedTahun', 'selectedTahunStart',
            'selectedKelas', 'namaEkskul', 'isAdmin'
        );
    }
}