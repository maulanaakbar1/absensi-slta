<?php

namespace App\Http\Controllers\Siswa;

use Carbon\Carbon;
use App\Models\Jadwal;
use GuzzleHttp\Client;
use App\Models\Absensi;
use App\Jobs\KirimWaJob;
use App\Models\HariLibur;
use Illuminate\Http\Request;
use App\Models\Ekstrakurikuler;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class AbsenController extends Controller
{

    private $ekskulId;

    public function __construct()
    {
        Carbon::setlocale('id');
        $this->ekskulId = Auth::user()->ekskul_aktif;
    }

    public function index()
    {

        $user = Auth::user();
        $siswa = $user->siswa;

        // Cek biodata
        $isComplete = $siswa->nisn && $siswa->alamat && $siswa->nama_ayah;
        $today = Carbon::today();
        $hariIni = $today->translatedFormat('l');
        // dd($hariIni);


        // AMBIL JADWAL HARI INI
        $jadwalHariIni = $this->getJadwalHariIni($this->ekskulId);

        $adaJadwal = $jadwalHariIni ? true : false;

        // CEK APAKAH SUDAH LEWAT JAM SELESAI
        $sudahMulai = false;
        $sudahTutup = false;

        if ($jadwalHariIni) {

            $jamSekarang = Carbon::now();

            $jamMulai = Carbon::today()->setTimeFromTimeString($jadwalHariIni->jam_mulai);
            $jamSelesai = Carbon::today()->setTimeFromTimeString($jadwalHariIni->jam_selesai);

            $sudahMulai = $jamSekarang->greaterThanOrEqualTo($jamMulai);
            $sudahTutup = $jamSekarang->greaterThan($jamSelesai);
        }

        // CEK LIBUR
        $isLibur = $this->isHariLibur($this->ekskulId);
        if ($isLibur) {
            $adaJadwal = false;
        }

        // CEK SUDAH ABSEN
        $absenHariIni = Absensi::with('ekstrakurikuler')
            ->where('siswa_id', $siswa->id)
            ->where('ekstrakurikuler_id', $this->ekskulId)
            ->whereDate('tanggal', $today)
            ->first();

        return view('siswa.absen', compact(
            'absenHariIni',
            'isComplete',
            'adaJadwal',
            'isLibur',
            'sudahMulai',
            'sudahTutup',
            'jadwalHariIni'
        ));
    }

    public function store(Request $request)
    {
        $siswa = Auth::user()->siswa;

        // PROTEK BIODATA
        if (!$siswa->nisn || !$siswa->alamat || !$siswa->nama_ayah) {
            return redirect()->route('siswa.profile')
                ->with('error', 'Lengkapi biodata dulu sebelum absen!');
        }

        $today = Carbon::today();
        $hariIni = $today->translatedFormat('l');

        // CEK JADWAL HARI INI
        $jadwalHariIni = $this->getJadwalHariIni($this->ekskulId);

        if (!$jadwalHariIni) {
            return back()->with('error', 'Tidak ada jadwal latihan hari ini!');
        }

        // CEK APAKAH ABSENSI SUDAH DITUTUP
        $jamSekarang = Carbon::now();

        $jamMulai = Carbon::today()->setTimeFromTimeString($jadwalHariIni->jam_mulai);
        $jamSelesai = Carbon::today()->setTimeFromTimeString($jadwalHariIni->jam_selesai);

        if ($jamSekarang->lt($jamMulai)) {
            return back()->with(
                'error',
                'Absensi belum dibuka. Mulai pukul ' .
                Carbon::parse($jadwalHariIni->jam_mulai)->format('H:i') .
                ' WIB.'
            );
        }

        if ($jamSekarang->gt($jamSelesai)) {
            return back()->with(
                'error',
                'Absensi sudah ditutup karena jadwal latihan telah selesai.'
            );
        }

        // CEK LIBUR
        $isLibur = $this->isHariLibur($this->ekskulId);

        if ($isLibur) {
            return back()->with('error', 'Hari ini adalah hari libur!');
        }

        // CEK SUDAH ABSEN
        $sudahAbsen = Absensi::where('siswa_id', $siswa->id)
            ->whereDate('tanggal', $today)
            ->exists();

        if ($sudahAbsen) {
            return redirect()->route('siswa.absen.riwayat')
                ->with('error', 'Anda sudah absen hari ini!');
        }

        // VALIDASI
        $request->validate([
            'foto' => 'required',
            'lokasi' => 'required',
            'status' => 'required|in:hadir,izin,sakit',
            'keterangan' => 'required|string|max:255'
        ]);

        // ==========================
        // SIMPAN FOTO BASE64
        // ==========================

        $fotoPath = null;

        if ($request->foto) {

            $folderPath = public_path('storage/absensi/');

            if (!file_exists($folderPath)) {
                mkdir($folderPath, 0777, true);
            }

            $image_parts = explode(";base64,", $request->foto);

            if (count($image_parts) > 1) {

                $image_base64 = base64_decode($image_parts[1]);

                $fileName = 'absen_' . time() . '_' . uniqid() . '.jpg';

                file_put_contents(
                    $folderPath . $fileName,
                    $image_base64
                );

                $fotoPath = 'absensi/' . $fileName;
            }
        }

        // ==========================
        // STATUS & KETERANGAN
        // ==========================

        $status = strtolower($request->status);

        $keteranganDefault = match ($status) {
            'hadir' => 'Hadir mengikuti kegiatan ekstrakurikuler.',
            'izin'  => 'Izin tidak mengikuti kegiatan ekstrakurikuler.',
            'sakit' => 'Sakit dan tidak dapat mengikuti kegiatan ekstrakurikuler.',
            default => 'Absensi ekstrakurikuler'
        };

        $keterangan = $request->keterangan ?: $keteranganDefault;

        // ==========================
        // FORMAT KELAS
        // ==========================

        $kelas = '-';

        if (!empty($siswa->kelas)) {

            $kelas = $siswa->kelas;
        } elseif (!empty($siswa->jurusan)) {

            $kelas = $siswa->jurusan;
        }

        // ==========================
        // SIMPAN ABSENSI
        // ==========================

        $absensi = Absensi::create([
            'siswa_id' => $siswa->id,
            'ekstrakurikuler_id' => $this->ekskulId,
            'tanggal' => $today,
            'jam_masuk' => Carbon::now()->toTimeString(),
            'foto' => $fotoPath,
            'lokasi' => $request->lokasi,
            'status' => $status,
            'keterangan' => $keterangan,
        ]);

        // ==========================
        // TEMPLATE PESAN WA
        // ==========================

        $pembina = \App\Models\Pembina::where(
            'ekstrakurikuler_id',
            $this->ekskulId
        )->first();

        $namaPembina = $pembina?->user?->name ?? 'Tidak diketahui';

        $noPembina = $pembina?->no_telp
            ? $this->formatNomor($pembina->no_telp)
            : '-';

        $statusText = strtoupper($status);

        $emojiStatus = match ($status) {
            'hadir' => '✅',
            'izin'  => '🟡',
            'sakit' => '🤒',
            default => '📌'
        };

        $namaEkskul = Ekstrakurikuler::where('id', $this->ekskulId)->first()->nama ?? 'Ekstrakurikuler';

        $pesan =
            "📢 *ABSENSI {$namaEkskul}*\n\n" .

            "👤 Nama: {$siswa->user->name}\n" .
            "🏫 Kelas: {$kelas}\n" .
            "{$emojiStatus} Status: {$statusText}\n" .
            "📅 Tanggal: " . now()->format('d-m-Y') . "\n" .
            "⏰ Jam: " . now()->format('H:i') . " WIB\n\n" .

            "👨‍🏫 Pembina:\n" .
            "{$namaPembina}\n" .
            "📞 {$noPembina}\n\n" .

            "📝 Keterangan:\n" .
            ($keterangan ?: '-') . "\n\n" .

            "📍 Lokasi:\n" .
            "https://maps.google.com/?q={$request->lokasi}";

        $delay = 0;

        // ==========================
        // KIRIM KE IBU
        // ==========================
        if ($siswa->no_telp_ibu) {

            KirimWaJob::dispatch(
                $this->formatNomor($siswa->no_telp_ibu),
                $pesan
            )->delay(now()->addSeconds($delay));

            $delay += 10;
        }

        // ==========================
        // KIRIM KE AYAH
        // ==========================
        if ($siswa->no_telp_ayah) {

            KirimWaJob::dispatch(
                $this->formatNomor($siswa->no_telp_ayah),
                $pesan
            )->delay(now()->addSeconds($delay));
        }

        return redirect()->route('siswa.absen.riwayat')
            ->with('success', 'Berhasil melakukan absensi!');
    }

    public function riwayat()
    {
        $siswa = Auth::user()->siswa;

        $semuaRiwayat = Absensi::where('siswa_id', $siswa->id)
            ->where('ekstrakurikuler_id', $this->ekskulId)
            ->orderBy('tanggal', 'desc')
            ->paginate(10);

        return view('siswa.riwayat', compact('semuaRiwayat'));
    }

    // ==========================
    // FORMAT NOMOR INDONESIA
    // ==========================

    private function formatNomor($nomor)
    {
        $nomor = preg_replace('/[^0-9]/', '', $nomor);

        if (substr($nomor, 0, 1) == '0') {
            $nomor = '62' . substr($nomor, 1);
        }

        return $nomor;
    }

    // ==========================
    // KIRIM WHATSAPP
    // ==========================

    private function kirimWhatsapp($nomor, $pesan)
    {
        $token = env('WA_API_TOKEN');

        $client = new Client();

        try {

            $response = $client->post(env('WA_API_URL'), [

                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],

                'json' => [
                    'phone' => $nomor,
                    'message' => $pesan,
                ]

            ]);

            Log::info('WA BERHASIL', [
                'nomor' => $nomor,
                'response' => $response->getBody()->getContents()
            ]);
        } catch (\Exception $e) {

            Log::error('WA GAGAL', [
                'nomor' => $nomor,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function getJadwalHariIni($ekskulId)
    {
        $today = Carbon::today();
        $hariIni = $today->translatedFormat('l');

        return Jadwal::where('ekstrakurikuler_id', $ekskulId)
            ->where(function ($q) use ($hariIni, $today) {

                $q->where(function ($r) use ($hariIni) {

                    $r->where('tipe', 'rutin')
                        ->where('hari', $hariIni);

                });

                $q->orWhere(function ($r) use ($today) {

                    $r->where('tipe', 'dadakan')
                        ->whereDate('tanggal', $today);

                });

            })
            ->orderBy('jam_mulai')
            ->first();
    }

    private function isHariLibur($ekskulId)
    {
        $today = Carbon::today();
        $hariIni = $today->translatedFormat('l');

        return HariLibur::where('ekstrakurikuler_id', $ekskulId)
            ->where(function ($q) use ($hariIni, $today) {

                $q->where(function ($r) use ($today) {

                    $r->where('tipe', 'dadakan')
                        ->whereDate('tanggal', $today);

                });

                $q->orWhere(function ($r) use ($hariIni) {

                    $r->where('tipe', 'rutin')
                        ->where('hari', $hariIni);

                });

            })
            ->exists();
    }
}
