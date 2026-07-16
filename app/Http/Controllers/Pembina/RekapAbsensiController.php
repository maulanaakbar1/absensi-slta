<?php

namespace App\Http\Controllers\Pembina;

use Carbon\Carbon;
use App\Models\Siswa;
use App\Models\Absensi;

use App\Models\Pembina;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\RekapAbsensiExport;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class RekapAbsensiController extends Controller
{
    public function index(Request $request)
    {
        $bulan = $request->get('bulan', date('m'));

        $user = auth()->user();
        $ekskulId = $user->pembina->ekstrakurikuler_id;

        // =========================
        // FILTER INPUT
        // =========================
        $selectedTahun = $request->get(
            'tahun_ajaran',
            $this->getCurrentTahunAjaran()
        );

        $selectedKelas = $request->get('kelas');
        $selectedJurusan = $request->get('jurusan');

        // =========================
        // TAHUN AJARAN START
        // =========================
        $selectedTahunStart = $selectedTahun !== 'semua'
            ? $this->parseTahunAjaranStart($selectedTahun)
            : now()->year;

        // =========================
        // PENENTUAN TAHUN ABSENSI
        // =========================
        $tahun = ((int) $bulan >= 7)
            ? $selectedTahunStart
            : $selectedTahunStart + 1;

        $jumlahHari = Carbon::createFromDate(
            $tahun,
            $bulan,
            1
        )->daysInMonth;

        // =========================
        // QUERY DASAR
        // =========================
        $query = Siswa::with([
            'user',
            'absensis' => function ($q) use ($bulan, $tahun, $ekskulId) {
                $q->where('ekstrakurikuler_id', $ekskulId)
                    ->whereMonth('tanggal', $bulan)
                    ->whereYear('tanggal', $tahun);
            }
        ])->whereJsonContains('ekstrakurikuler_id', $ekskulId);
        // $query = Siswa::whereJsonContains('ekstrakurikuler_id', $ekskulId);


        // =========================
        // FILTER TAHUN AJARAN
        // =========================
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

        // =========================
        // FILTER JURUSAN
        // =========================
        if ($selectedJurusan) {
            $query->where('jurusan', $selectedJurusan);
        }

        $siswas = $query
            ->orderBy('tingkat_awal', 'asc')
            ->orderBy('jurusan', 'asc')
            ->orderBy('nis', 'asc')
            ->get();

        // =========================
        // LIST JURUSAN
        // =========================
        $jurusanList = Siswa::whereJsonContains('ekstrakurikuler_id', $ekskulId)
            ->whereNotNull('jurusan')
            ->select('jurusan')
            ->distinct()
            ->orderBy('jurusan')
            ->pluck('jurusan')
            ->toArray();

        // =========================
        // TRANSFORM KELAS DISPLAY
        // =========================
        $siswas->transform(function ($siswa) use ($selectedTahunStart) {

            $tahunDisplay = $selectedTahunStart
                ?? $this->parseTahunAjaranStart($this->getCurrentTahunAjaran());

            $tingkat = $this->getTingkat($siswa, $tahunDisplay);
            $kelasDisplay = $this->getKelasDisplay($siswa, $tahunDisplay);

            $siswa->kelas_display = $kelasDisplay;
            $siswa->tingkat_display = $tingkat;

            return $siswa;
        });

        // =========================
        // FILTER KELAS
        // =========================
        if ($selectedKelas) {
            $siswas = $siswas->filter(function ($siswa) use ($selectedKelas) {
                return $siswa->tingkat_display == $selectedKelas;
            })->values();
        }

        // =========================
        // LIST TAHUN AJARAN
        // =========================
        $tahunAjaranList = $this->getTahunAjaranList($ekskulId);

        if (
            $selectedTahun !== 'semua'
            && !in_array($selectedTahun, $tahunAjaranList)
        ) {
            $tahunAjaranList[] = $selectedTahun;
        }

        // =========================
        // NAMA BULAN
        // =========================
        $namaBulan = [
            '01' => 'Januari',
            '02' => 'Februari',
            '03' => 'Maret',
            '04' => 'April',
            '05' => 'Mei',
            '06' => 'Juni',
            '07' => 'Juli',
            '08' => 'Agustus',
            '09' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'Desember'
        ];



        return view('pembina.rekap_absensi', compact(
            'siswas',
            'bulan',
            'tahun',
            'jumlahHari',
            'namaBulan',
            'tahunAjaranList',
            'selectedTahun',
            'selectedTahunStart',
            'selectedKelas',
            'selectedJurusan',
            'jurusanList'
        ));
    }

    public function updateStatus(Request $request)
    {
        $request->validate([
            'siswa_id' => 'required|exists:siswas,id',
            'tanggal'  => 'required|date',
            'status'   => 'required|in:hadir,izin,sakit,alpa'
        ]);

        $user = auth()->user();
        $ekskulId = $user->pembina->ekstrakurikuler_id;

        $hariMap = [
            'Sunday'    => 'Minggu',
            'Monday'    => 'Senin',
            'Tuesday'   => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday'  => 'Kamis',
            'Friday'    => 'Jumat',
            'Saturday'  => 'Sabtu'
        ];

        $hariEnglish = Carbon::parse($request->tanggal)->format('l');
        $hariIndo = $hariMap[$hariEnglish];

        $tanggal = Carbon::parse($request->tanggal)->toDateString();

        $cekJadwal = \App\Models\Jadwal::where('ekstrakurikuler_id', $ekskulId)
            ->where(function ($q) use ($hariIndo, $tanggal) {

                $q->where(function ($rutin) use ($hariIndo) {
                    $rutin->where('tipe', 'rutin')
                        ->where('hari', $hariIndo);
                });

                $q->orWhere(function ($dadakan) use ($tanggal) {
                    $dadakan->where('tipe', 'dadakan')
                            ->whereDate('tanggal', $tanggal);
                });

            })
            ->exists();

        if (!$cekJadwal) {

            return back()->with(
                'error',
                'Tidak ada jadwal latihan pada tanggal tersebut.'
            );

        }

        $cekLibur = \App\Models\HariLibur::where('ekstrakurikuler_id', $ekskulId)
            ->where(function ($q) use ($request, $hariIndo) {

                $q->where(function ($rutin) use ($hariIndo) {
                    $rutin->where('tipe', 'rutin')
                        ->where('hari', $hariIndo);
                });

                $q->orWhere(function ($dadakan) use ($request) {
                    $dadakan->where('tipe', 'dadakan')
                            ->whereDate('tanggal', $request->tanggal);
                });

            })
            ->exists();

        if ($cekLibur) {

            return back()->with(
                'error',
                'Gagal absen! Tanggal tersebut ditandai sebagai hari libur ekskul.'
            );
        }

        $absensi = Absensi::where('siswa_id', $request->siswa_id)
            ->whereDate('tanggal', $request->tanggal)
            ->first();

        if ($absensi && $absensi->status === 'hadir') {

            return back()->with(
                'error',
                'Status hadir tidak bisa diubah!'
            );
        }



        if ($absensi) {

            $absensi->update([
                'status' => $request->status,

                // isi jam masuk jika hadir
                'jam_masuk' => $request->status === 'hadir'
                    ? now()->format('H:i:s')
                    : $absensi->jam_masuk
            ]);
        } else {

            Absensi::create([
                'siswa_id'  => $request->siswa_id,
                'ekstrakurikuler_id'   => $ekskulId,
                'tanggal'   => $request->tanggal,

                // FIX ERROR DISINI
                'jam_masuk' => now()->format('H:i:s'),

                'status'    => $request->status,
            ]);
        }

        return redirect()->back()->with(
            'success',
            'Status absensi berhasil diperbarui'
        );
    }

    public function manage(Request $request)
    {
        $tanggal = $request->tanggal ?? now()->toDateString();

        $pembina = auth()->user()->pembina;

        $selectedTahun = $request->get(
            'tahun_ajaran',
            $this->getCurrentTahunAjaran()
        );

        $selectedKelas = $request->get('kelas');

        $selectedJurusan = $request->get('jurusan');

        $search = $request->get('search');

        $selectedTahunStart = $selectedTahun !== 'semua'
            ? $this->parseTahunAjaranStart($selectedTahun)
            : null;

        $query = Siswa::with([
            'user',
            'absensis' => function ($q) use ($tanggal) {
                $q->whereDate('tanggal', $tanggal);
            }
        ])->whereJsonContains(
            'ekstrakurikuler_id',
            $pembina->ekstrakurikuler_id
        );

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

        // =========================
        // FILTER JURUSAN
        // =========================
        if ($selectedJurusan) {

            $query->where(
                'jurusan',
                $selectedJurusan
            );
        }

        // =========================
        // SEARCH NAMA
        // =========================
        if ($search) {

            $query->whereHas('user', function ($q) use ($search) {

                $q->where(
                    'name',
                    'like',
                    '%' . $search . '%'
                );
            });
        }

        // =========================
        // FILTER KELAS
        // =========================
        if ($selectedKelas && $selectedTahunStart) {

            $query->whereRaw(
                '(? - tahun_masuk) + tingkat_awal = ?',
                [
                    $selectedTahunStart,
                    $selectedKelas
                ]
            );
        }

        // =========================
        // PAGINATE
        // =========================
        $siswas = $query
            ->paginate(15)
            ->withQueryString();

        // =========================
        // LIST JURUSAN
        // =========================
        $jurusanList = Siswa::whereJsonContains(
            'ekstrakurikuler_id',
            $pembina->ekstrakurikuler_id
        )
            ->whereNotNull('jurusan')
            ->select('jurusan')
            ->distinct()
            ->orderBy('jurusan')
            ->pluck('jurusan')
            ->toArray();

        // =========================
        // TRANSFORM DISPLAY
        // =========================
        $siswas->getCollection()->transform(function ($siswa) use ($selectedTahunStart) {

            $tahunDisplay = $selectedTahunStart
                ?? $this->parseTahunAjaranStart(
                    $this->getCurrentTahunAjaran()
                );

            $tingkat = $this->getTingkat(
                $siswa,
                $tahunDisplay
            );

            $kelasDisplay = $this->getKelasDisplay(
                $siswa,
                $tahunDisplay
            );

            $siswa->kelas_display = $kelasDisplay;
            $siswa->tingkat_display = $tingkat;

            return $siswa;
        });

        // =========================
        // LIST TAHUN AJARAN
        // =========================
        $tahunAjaranList = $this->getTahunAjaranList(
            $pembina->ekstrakurikuler_id
        );

        return view('pembina.absensi_manage', compact(
            'siswas',
            'tanggal',
            'tahunAjaranList',
            'selectedTahun',
            'selectedKelas',
            'selectedJurusan',
            'jurusanList'
        ));
    }

    public function riwayat(Request $request)
    {
        $user = auth()->user();
        $ekskulId = $user->pembina->ekstrakurikuler_id;

        // =========================
        // FILTER INPUT
        // =========================
        $selectedTahun = $request->get(
            'tahun_ajaran',
            $this->getCurrentTahunAjaran()
        );

        $selectedKelas = $request->get('kelas');
        $selectedJurusan = $request->get('jurusan');
        $search = $request->get('search');
        $tanggal = $request->get('tanggal');

        $selectedTahunStart = $selectedTahun !== 'semua'
            ? $this->parseTahunAjaranStart($selectedTahun)
            : null;

        // =========================
        // QUERY DASAR
        // =========================
        $query = Absensi::whereHas('siswa', function ($q) use (
            $ekskulId,
            $selectedTahunStart,
            $selectedJurusan,
            $search
        ) {

            $q->whereJsonContains('ekstrakurikuler_id', $ekskulId);

            // FILTER SEARCH NAMA
            if ($search) {

                $q->whereHas('user', function ($userQuery) use ($search) {

                    $userQuery->where(
                        'name',
                        'like',
                        '%' . $search . '%'
                    );
                });
            }

            // FILTER TAHUN AJARAN
            if ($selectedTahunStart) {

                $q->where(function ($q2) use ($selectedTahunStart) {

                    $q2->whereNull('tahun_masuk')
                        ->orWhere(function ($q3) use ($selectedTahunStart) {

                            $q3->whereRaw(
                                '? BETWEEN tahun_masuk AND (tahun_masuk + (12 - tingkat_awal))',
                                [$selectedTahunStart]
                            );
                        });
                });
            }

            // FILTER JURUSAN
            if ($selectedJurusan) {

                $q->where('jurusan', $selectedJurusan);
            }
        })
            ->with(['siswa.user']);

        if ($tanggal) {
            $query->whereDate('tanggal', $tanggal);
        }

        $query->latest('tanggal')
            ->latest('jam_masuk');

        $riwayat = $query->paginate(15);

        // =========================
        // TRANSFORM KELAS DISPLAY
        // =========================
        $riwayat->getCollection()->transform(function ($row) use ($selectedTahunStart) {

            $tahunDisplay = $selectedTahunStart
                ?? $this->parseTahunAjaranStart($this->getCurrentTahunAjaran());

            $siswa = $row->siswa;

            $tingkat = $this->getTingkat($siswa, $tahunDisplay);

            $kelasDisplay = $this->getKelasDisplay($siswa, $tahunDisplay);

            $siswa->kelas_display = $kelasDisplay;
            $siswa->tingkat_display = $tingkat;

            return $row;
        });

        // =========================
        // FILTER KELAS (AFTER QUERY)
        // =========================
        if ($selectedKelas) {

            $filtered = $riwayat->getCollection()->filter(function ($row) use ($selectedKelas) {

                return $row->siswa->tingkat_display == $selectedKelas;
            })->values();

            $riwayat->setCollection($filtered);
        }

        // =========================
        // LIST TAHUN AJARAN
        // =========================
        $tahunAjaranList = $this->getTahunAjaranList($ekskulId);

        if (
            $selectedTahun !== 'semua'
            && !in_array($selectedTahun, $tahunAjaranList)
        ) {
            $tahunAjaranList[] = $selectedTahun;
        }

        // =========================
        // LIST JURUSAN
        // =========================
        $jurusanList = \App\Models\Siswa::whereJsonContains('ekstrakurikuler_id', $ekskulId)
            ->whereNotNull('jurusan')
            ->select('jurusan')
            ->distinct()
            ->orderBy('jurusan')
            ->pluck('jurusan')
            ->toArray();

        return view('pembina.riwayat_absensi', compact(
            'riwayat',
            'tahunAjaranList',
            'selectedTahun',
            'selectedKelas',
            'selectedJurusan',
            'jurusanList',
            'tanggal'
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

        $user = auth()->user();
        $ekskulId = $user->pembina->ekstrakurikuler_id;

        $selectedTahun = $request->get('tahun_ajaran', $this->getCurrentTahunAjaran());
        $selectedKelas = $request->get('kelas');

        $currentStart = $this->parseTahunAjaranStart($this->getCurrentTahunAjaran());

        $selectedTahunStart = $selectedTahun !== 'semua'
            ? $this->parseTahunAjaranStart($selectedTahun)
            : null;

        $tahunDasar = $selectedTahunStart ?? $currentStart;
        $tahun = ((int) $bulan >= 7) ? $tahunDasar : $tahunDasar + 1;

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
        ])->whereJsonContains('ekstrakurikuler_id', $ekskulId);

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

            $siswas = $siswas->filter(function ($siswa) use ($selectedKelas) {

                return $siswa->tingkat_display == $selectedKelas;
            })->values();
        }

        $namaBulan = [
            '01' => 'Januari',
            '02' => 'Februari',
            '03' => 'Maret',
            '04' => 'April',
            '05' => 'Mei',
            '06' => 'Juni',
            '07' => 'Juli',
            '08' => 'Agustus',
            '09' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'Desember'
        ];

        $isAdmin = auth()->user()->role === 'admin';

        return compact(
            'siswas',
            'bulan',
            'tahun',
            'jumlahHari',
            'namaBulan',
            'selectedTahun',
            'selectedTahunStart',
            'selectedKelas',
            'isAdmin'
        );
    }
}
