<?php

namespace App\Http\Controllers\Pembina;

use App\Http\Controllers\Controller;
use App\Models\Pembina;
use App\Models\Siswa;
use App\Models\Jadwal;
use App\Models\Absensi;
use App\Models\HariLibur;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // dd('dsd');
        // 1. Ambil data pembina dan ekskulnya
        $pembina = Pembina::where('user_id', Auth::id())
            ->with('ekstrakurikuler')
            ->first();

        // dd($pembina);

        if (!$pembina || !$pembina->ekstrakurikuler) {
            return view('pembina.dashboard', [
                'pembina' => $pembina,
                'jumlahSiswa' => 0,
                'jumlahBalonpas' => 0,
                'jumlahInstruktur' => 0,
                'jadwalTerdekat' => null,
                'absensiHariIni' => 0,
                'labelJadwal' => null,
            ]);
        }

        $ekskulId = $pembina->ekstrakurikuler_id;

        // 2. Hitung jumlah siswa
        $jumlahSiswa = Siswa::whereJsonContains('ekstrakurikuler_id', $ekskulId)->count();

        $jumlahBalonpas = Siswa::whereJsonContains('ekstrakurikuler_id', $ekskulId)
            ->where('tingkatan', 'balonpas')
            ->count();

        $jumlahInstruktur = Siswa::whereJsonContains('ekstrakurikuler_id', $ekskulId)
            ->where('tingkatan', 'instruktur')
            ->count();

        // =========================
        // 3. LOGIC JADWAL 
        // =========================

        $now = Carbon::now();
        $today = $now->toDateString();

        $daftarHari = [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu',
        ];

        $hariSekarang = $daftarHari[$now->dayOfWeekIso];

        // =========================
        // LIBUR HARI INI
        // =========================

        $liburHariIni = HariLibur::where('ekstrakurikuler_id', $ekskulId)
            ->where(function ($q) use ($today, $hariSekarang) {

                $q->where(function ($r) use ($today) {
                    $r->where('tipe', 'dadakan')
                        ->whereDate('tanggal', $today);
                });

                $q->orWhere(function ($r) use ($hariSekarang) {
                    $r->where('tipe', 'rutin')
                        ->where('hari', $hariSekarang);
                });
            })
            ->first();

        $jadwalTerdekat = null;
        $labelJadwal = null;

        // kalau hari ini libur
        if ($liburHariIni) {

            $labelJadwal = 'Hari Ini Libur';
        } else {

            // =========================
            // AMBIL SEMUA JADWAL AKTIF
            // =========================

            $jadwalList = Jadwal::where('ekstrakurikuler_id', $ekskulId)
                ->get()
                ->filter(function ($jadwal) use ($today) {

                    // jadwal dadakan
                    if ($jadwal->tipe === 'dadakan') {

                        return $jadwal->tanggal >= $today;
                    }

                    return true;
                })
                ->map(function ($jadwal) use ($now, $daftarHari) {

                    if ($jadwal->tipe === 'dadakan') {

                        $jadwal->tanggal_event =
                            Carbon::parse($jadwal->tanggal . ' ' . $jadwal->jam_mulai);
                    } else {

                        $hariIndex = array_search(
                            $jadwal->hari,
                            $daftarHari
                        );

                        $eventDate = now()->startOfDay();

                        while ($eventDate->dayOfWeekIso != $hariIndex) {
                            $eventDate->addDay();
                        }

                        if (
                            $eventDate->isToday() &&
                            $jadwal->jam_mulai < $now->format('H:i:s')
                        ) {
                            $eventDate->addWeek();
                        }

                        $jadwal->tanggal_event =
                            Carbon::parse(
                                $eventDate->format('Y-m-d')
                                    . ' '
                                    . $jadwal->jam_mulai
                            );
                    }

                    return $jadwal;
                })
                ->sortBy('tanggal_event');

            $jadwalTerdekat = $jadwalList->first();

            if ($jadwalTerdekat) {

                if ($jadwalTerdekat->tipe === 'dadakan') {

                    if (
                        Carbon::parse($jadwalTerdekat->tanggal)
                        ->isToday()
                    ) {

                        $labelJadwal = 'Latihan Dadakan Hari Ini';
                    } elseif (
                        Carbon::parse($jadwalTerdekat->tanggal)
                        ->isTomorrow()
                    ) {

                        $labelJadwal = 'Latihan Dadakan Besok';
                    } else {

                        $labelJadwal = 'Latihan Dadakan';
                    }
                } else {

                    if ($jadwalTerdekat->hari === $hariSekarang) {

                        $labelJadwal = 'Jadwal Latihan Hari Ini';
                    } else {

                        $labelJadwal =
                            'Jadwal Latihan Hari ' .
                            $jadwalTerdekat->hari;
                    }
                }
            }
        }

        // 4. Absensi hari ini
        $absensiHariIni = Absensi::whereHas('siswa', function ($q) use ($ekskulId) {
            $q->whereJsonContains('ekstrakurikuler_id', $ekskulId);
        })
            ->whereDate('tanggal', Carbon::today())
            ->count();

        return view('pembina.dashboard', compact(
            'pembina',
            'jumlahSiswa',
            'jumlahBalonpas',
            'jumlahInstruktur',
            'jadwalTerdekat',
            'absensiHariIni',
            'labelJadwal'
        ));
    }

    public function kirimWa()
    {
        $pembina = Pembina::where('user_id', Auth::id())
            ->with('ekstrakurikuler')
            ->first();

        if (!$pembina || !$pembina->ekstrakurikuler) {
            return back()->with('error', 'Ekskul tidak ditemukan.');
        }

        $siswaList = Siswa::with('user')
            ->whereJsonContains('ekstrakurikuler_id', $pembina->ekstrakurikuler_id)
            ->get();

        $daftarHari = [
            'Senin',
            'Selasa',
            'Rabu',
            'Kamis',
            'Jumat',
            'Sabtu',
            'Minggu'
        ];

        $urutanHari = [
            'Senin' => 1,
            'Selasa' => 2,
            'Rabu' => 3,
            'Kamis' => 4,
            'Jumat' => 5,
            'Sabtu' => 6,
            'Minggu' => 7,
        ];

        $now = Carbon::now();
        $hariSekarangIndex = $now->dayOfWeekIso; // 1-7

        $jadwal = Jadwal::where('ekstrakurikuler_id', $pembina->ekstrakurikuler_id)
            ->get()
            ->filter(function ($item) use ($now) {

                if ($item->tipe === 'dadakan') {
                    return Carbon::parse($item->tanggal)->gte($now->copy()->startOfDay());
                }

                return true;
            })
            ->map(function ($item) use ($urutanHari, $now) {

                if ($item->tipe === 'dadakan') {

                    $item->tanggal_event = Carbon::parse(
                        $item->tanggal.' '.$item->jam_mulai
                    );

                } else {

                    $hariIndex = $urutanHari[$item->hari];

                    $eventDate = $now->copy()->startOfDay();

                    while ($eventDate->dayOfWeekIso != $hariIndex) {
                        $eventDate->addDay();
                    }

                    // kalau hari ini tapi jamnya sudah lewat,
                    // pindahkan ke minggu depan
                    if (
                        $eventDate->isToday() &&
                        $item->jam_mulai <= $now->format('H:i:s')
                    ) {
                        $eventDate->addWeek();
                    }

                    $eventDate->setTimeFromTimeString($item->jam_mulai);

                    $item->tanggal_event = $eventDate;
                }

                return $item;
            })
            ->sortBy('tanggal_event')
            ->first();

        $delay = 0;

        \Log::info($jadwal);
        // ======================
        // SISWA
        // ======================
        foreach ($siswaList as $siswa) {

            $pesan = "📢 *INFORMASI EKSKUL*\n\n";
            $pesan .= "🏫 Ekskul: " . $pembina->ekstrakurikuler->nama . "\n";
            $pesan .= "👤 Siswa: " . $siswa->user->name . "\n\n";

            if ($jadwal) {

                if ($jadwal->tipe === 'dadakan') {

                    $pesan .= "📅 *Jadwal Dadakan*\n";
                    $pesan .= "Hari : " .
                        Carbon::parse($jadwal->tanggal)
                        ->locale('id')
                        ->translatedFormat('l') . "\n";
                    $pesan .= "Tanggal : " .
                        Carbon::parse($jadwal->tanggal)
                        ->locale('id')
                        ->translatedFormat('d F Y') . "\n";

                } else {

                    $pesan .= "📅 *Jadwal Rutin*\n";
                    $pesan .= "Hari : {$jadwal->hari}\n";

                }

                $pesan .= "Jam : "
                    . date('H:i', strtotime($jadwal->jam_mulai))
                    . " - "
                    . date('H:i', strtotime($jadwal->jam_selesai))
                    . " WIB\n";

                $pesan .= "📍 Lokasi : {$jadwal->lokasi}\n";

                if ($jadwal->keterangan) {
                    $pesan .= "📝 Keterangan : {$jadwal->keterangan}\n";
                }

                $pesan .= "\n";
            }

            $pesan .= "Jangan lupa mengikuti kegiatan ekskul sesuai jadwal.\n\n";
            $pesan .= "Terima kasih.";

            if ($siswa->no_telp_siswa) {

                \App\Jobs\KirimWaJob::dispatch(
                    $this->formatNomor($siswa->no_telp_siswa),
                    $pesan
                )->delay(now()->addSeconds($delay));

                $delay += 15;
            }
        }

        // jeda siswa -> ibu
        $delay += 10;

        // ======================
        // IBU
        // ======================
        foreach ($siswaList as $siswa) {

            $pesan = "📢 *INFORMASI EKSKUL*\n\n";
            $pesan .= "🏫 Ekskul: " . $pembina->ekstrakurikuler->nama . "\n";
            $pesan .= "👤 Siswa: " . $siswa->user->name . "\n\n";

            if ($jadwal) {

                if ($jadwal->tipe === 'dadakan') {

                    $pesan .= "📅 *Jadwal Dadakan*\n";
                    $pesan .= "Hari : " .
                        Carbon::parse($jadwal->tanggal)
                        ->locale('id')
                        ->translatedFormat('l') . "\n";
                    $pesan .= "Tanggal : " .
                        Carbon::parse($jadwal->tanggal)
                        ->locale('id')
                        ->translatedFormat('d F Y') . "\n";

                } else {

                    $pesan .= "📅 *Jadwal Rutin*\n";
                    $pesan .= "Hari : {$jadwal->hari}\n";

                }

                $pesan .= "Jam : "
                    . date('H:i', strtotime($jadwal->jam_mulai))
                    . " - "
                    . date('H:i', strtotime($jadwal->jam_selesai))
                    . " WIB\n";

                $pesan .= "📍 Lokasi : {$jadwal->lokasi}\n";

                if ($jadwal->keterangan) {
                    $pesan .= "📝 Keterangan : {$jadwal->keterangan}\n";
                }

                $pesan .= "\n";
            }

            $pesan .= "Jangan lupa mengikuti kegiatan ekskul sesuai jadwal.\n\n";
            $pesan .= "Terima kasih.";

            if ($siswa->no_telp_ibu) {

                \App\Jobs\KirimWaJob::dispatch(
                    $this->formatNomor($siswa->no_telp_ibu),
                    $pesan
                )->delay(now()->addSeconds($delay));

                $delay += 15;
            }
        }

        // jeda ibu -> ayah
        $delay += 10;

        // ======================
        // AYAH
        // ======================
        foreach ($siswaList as $siswa) {

            $pesan = "📢 *INFORMASI EKSKUL*\n\n";
            $pesan .= "🏫 Ekskul: " . $pembina->ekstrakurikuler->nama . "\n";
            $pesan .= "👤 Siswa: " . $siswa->user->name . "\n\n";

            if ($jadwal) {

                if ($jadwal->tipe === 'dadakan') {

                    $pesan .= "📅 *Jadwal Dadakan*\n";
                    $pesan .= "Hari : " .
                        Carbon::parse($jadwal->tanggal)
                        ->locale('id')
                        ->translatedFormat('l') . "\n";
                    $pesan .= "Tanggal : " .
                        Carbon::parse($jadwal->tanggal)
                        ->locale('id')
                        ->translatedFormat('d F Y') . "\n";

                } else {

                    $pesan .= "📅 *Jadwal Rutin*\n";
                    $pesan .= "Hari : {$jadwal->hari}\n";

                }

                $pesan .= "Jam : "
                    . date('H:i', strtotime($jadwal->jam_mulai))
                    . " - "
                    . date('H:i', strtotime($jadwal->jam_selesai))
                    . " WIB\n";

                $pesan .= "📍 Lokasi : {$jadwal->lokasi}\n";

                if ($jadwal->keterangan) {
                    $pesan .= "📝 Keterangan : {$jadwal->keterangan}\n";
                }

                $pesan .= "\n";
            }

            $pesan .= "Jangan lupa mengikuti kegiatan ekskul sesuai jadwal.\n\n";
            $pesan .= "Terima kasih.";

            if ($siswa->no_telp_ayah) {

                \App\Jobs\KirimWaJob::dispatch(
                    $this->formatNomor($siswa->no_telp_ayah),
                    $pesan
                )->delay(now()->addSeconds($delay));

                $delay += 15;
            }
        }

        return back()->with('success', 'Pesan WA berhasil dikirim.');
    }

    private function formatNomor($nomor)
    {
        $nomor = preg_replace('/[^0-9]/', '', $nomor);

        if (substr($nomor, 0, 1) == '0') {
            $nomor = '62' . substr($nomor, 1);
        }

        return $nomor;
    }
}
