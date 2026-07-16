<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ekstrakurikuler;
use App\Models\Jadwal;
use App\Models\HariLibur;
use Illuminate\Http\Request;

class JadwalController extends Controller
{
    public function index()
    {
        $ekskuls = Ekstrakurikuler::latest()->get();

        return view('admin.jadwal.index', compact('ekskuls'));
    }

    public function show($id)
    {
        $ekskul = Ekstrakurikuler::findOrFail($id);

        $jadwals = Jadwal::where('ekstrakurikuler_id', $id)->get();
        $liburs = HariLibur::where('ekstrakurikuler_id', $id)->get();

        return view('admin.jadwal.show', compact('ekskul', 'jadwals', 'liburs'));
    }
}