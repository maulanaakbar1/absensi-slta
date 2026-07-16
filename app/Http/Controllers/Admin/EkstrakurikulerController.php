<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ekstrakurikuler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EkstrakurikulerController extends Controller {
    
    public function index() {
        $ekskuls = Ekstrakurikuler::latest()->get();
        return view('admin.ekstrakurikuler', compact('ekskuls'));
    }

    public function store(Request $request) {
        $request->validate([
            'nama' => 'required|unique:ekstrakurikulers,nama',
            'nama_satuan' => 'nullable|string|max:255',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048' 
        ]);
        
        $path = $request->file('foto') ? $request->file('foto')->store('ekskul', 'public') : null;

        Ekstrakurikuler::create([
            'nama' => $request->nama,
            'nama_satuan' => $request->nama_satuan,
            'deskripsi' => $request->deskripsi,
            'foto' => $path
        ]);

        return back()->with('success', 'Ekskul berhasil dibuat');
    }

    public function update(Request $request, $id) {
        $ekskul = Ekstrakurikuler::findOrFail($id);
        
        $request->validate([
            'nama' => 'required|unique:ekstrakurikulers,nama,' . $id, 
            'nama_satuan' => 'nullable|string|max:255',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);
        
        // Ambil path foto lama sebagai default
        $path = $ekskul->foto;

        if ($request->hasFile('foto')) {
            if ($ekskul->foto && Storage::disk('public')->exists($ekskul->foto)) {
                Storage::disk('public')->delete($ekskul->foto);
            }
            // Simpan foto baru
            $path = $request->file('foto')->store('ekskul', 'public');
        }

        $ekskul->update([
            'nama' => $request->nama,
            'nama_satuan' => $request->nama_satuan,
            'deskripsi' => $request->deskripsi,
            'foto' => $path, 
        ]);

        return back()->with('success', 'Ekskul diperbarui');
    }

    public function destroy($id) {
        $ekskul = Ekstrakurikuler::findOrFail($id);
        
        if ($ekskul->foto && Storage::disk('public')->exists($ekskul->foto)) {
            Storage::disk('public')->delete($ekskul->foto);
        }
        
        $ekskul->delete();
        return back()->with('success', 'Ekskul dihapus');
    }
}