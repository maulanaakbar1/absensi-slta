<?php

namespace App\Http\Controllers\Pembina;

use App\Http\Controllers\Controller;
use App\Models\Jadwal;
use App\Models\HariLibur;
use Carbon\Carbon;
use Illuminate\Http\Request;

class JadwalController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $ekskulId = $user->pembina->ekstrakurikuler_id;

        $jadwals = Jadwal::where('ekstrakurikuler_id', $ekskulId)
        ->where(function ($q) {
            $q->whereNull('tanggal')              
            ->orWhereDate('tanggal', '>=', today()); 
        })
        ->orderByRaw('tanggal IS NULL DESC')
        ->orderBy('tanggal')
        ->get();
        $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

        return view('pembina.jadwal_latihan', compact('jadwals', 'hariList'));
    }

    public function store(Request $request)
    {
        // dd($request->all());
        Carbon::setLocale('id');

        $hari = Carbon::parse($request->tanggal)->translatedFormat('l');

        if ($request->tipe == 'dadakan') {
            $request->validate([
                'tanggal' => 'required',
                // 'hari' => 'required',
                'jam_mulai' => 'required',
                'jam_selesai' => 'required',
                'lokasi' => 'required',
                'keterangan' => 'nullable|string'
            ]);

            Jadwal::create([
                'ekstrakurikuler_id' => auth()->user()->pembina->ekstrakurikuler_id,
                'hari' => $hari,
                'tipe' => $request->tipe,
                'tanggal' => $request->tanggal,
                'jam_mulai' => $request->jam_mulai,
                'jam_selesai' => $request->jam_selesai,
                'lokasi' => $request->lokasi,
                'keterangan' => $request->keterangan,
            ]);
        } elseif ($request->tipe == 'rutin') {
            $request->validate([
                'hari' => 'required',
                'jam_mulai' => 'required',
                'jam_selesai' => 'required',
                'lokasi' => 'required',
                'keterangan' => 'nullable|string'
            ]);

            Jadwal::create([
                'ekstrakurikuler_id' => auth()->user()->pembina->ekstrakurikuler_id,
                'tipe' => $request->tipe,
                'hari' => $request->hari,
                'jam_mulai' => $request->jam_mulai,
                'jam_selesai' => $request->jam_selesai,
                'lokasi' => $request->lokasi,
                'keterangan' => $request->keterangan,
            ]);
        }

        return back()->with('success', 'Jadwal berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        $jadwal = Jadwal::findOrFail($id);
        $jadwal->update($request->all());
        return back()->with('success', 'Jadwal berhasil diperbarui');
    }

    public function destroy($id)
    {
        Jadwal::destroy($id);
        return back()->with('success', 'Jadwal berhasil dihapus');
    }

    public function liburIndex()
    {
        $ekskulId = auth()->user()->pembina->ekstrakurikuler_id;
        $hariLibur = HariLibur::where('ekstrakurikuler_id', $ekskulId)
        ->where(function ($q) {
            $q->whereNull('tanggal')
            ->orWhereDate('tanggal', '>=', today());
        })
        ->orderBy('tanggal')
        ->get();

        return view('pembina.hari_libur', compact('hariLibur'));
    }

    public function liburStore(Request $request)
    {

        if ($request->tipe == 'dadakan') {
            $request->validate([
                'tanggal' => 'required|date',
                'keterangan' => 'required|string|max:255'
            ]);

            HariLibur::create([
                'ekstrakurikuler_id' => auth()->user()->pembina->ekstrakurikuler_id,
                'tipe' => $request->tipe,
                'tanggal' => $request->tanggal,
                'hari' => null,
                'keterangan' => $request->keterangan,
            ]);
        } elseif ($request->tipe == 'rutin') {
            // dd($request->all());
            $request->validate([
                'hari' => 'required',
                'keterangan' => 'required|string|max:255'
            ]);

            HariLibur::create([
                'ekstrakurikuler_id' => auth()->user()->pembina->ekstrakurikuler_id,
                'tipe' => $request->tipe,
                'tanggal' => null,
                'hari' => $request->hari,
                'keterangan' => $request->keterangan,
            ]);
        }


        return back()->with('success', 'Hari libur berhasil ditambahkan');
    }

    public function liburUpdate(Request $request, $id)
    {
        $libur = HariLibur::findOrFail($id);
        $libur->update($request->all());
        return back()->with('success', 'Data libur berhasil diperbarui');
    }

    public function liburDestroy($id)
    {
        HariLibur::destroy($id);
        return back()->with('success', 'Hari libur berhasil dihapus');
    }
}
