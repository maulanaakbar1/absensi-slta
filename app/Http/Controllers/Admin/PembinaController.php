<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ekstrakurikuler;
use App\Models\Pembina;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PembinaController extends Controller
{
    public function index()
    {
        $pembinas = Pembina::with(['user', 'ekstrakurikuler'])->latest()->get();
        $ekskuls = Ekstrakurikuler::all();

        return view('admin.pembina', compact('pembinas', 'ekskuls'));
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'ekstrakurikuler_id' => 'required|exists:ekstrakurikulers,id',
        ],
    [
        'password.min' => 'Password minimal 6 karakter',
        'email.unique' => 'Email sudah terdaftar',
        'ekstrakurikuler_id.exists' => 'Ekstrakurikuler tidak ditemukan',
        'ekstrakurikuler_id.required' => 'Ekstrakurikuler wajib diisi',
        'password.required' => 'Password wajib diisi',
        'email.required' => 'Email wajib diisi',
        'name.required' => 'Nama wajib diisi',
    ]
    
    );

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'pembina',
        ]);

        if ($user->id) {
            Pembina::create([
                'user_id' => $user->id,
                'nip' => $request->nip,
                'no_telp' => $request->no_telp,
                'ekstrakurikuler_id' => $request->ekstrakurikuler_id,
            ]);
        }

        return back()->with('success', 'Pembina berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        $pembina = Pembina::findOrFail($id);
        $user = $pembina->user;

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'password' => 'nullable|min:6',
            'ekstrakurikuler_id' => 'required|exists:ekstrakurikulers,id',
        ]);

        // SIMPAN DATA LAMA
        $oldName = $user->name;
        $oldEmail = $user->email;
        $oldNip = $pembina->nip;
        $oldTelp = $pembina->no_telp;

        $oldEkskul = $pembina->ekstrakurikuler
            ? $pembina->ekstrakurikuler->nama
            : '-';

        // UPDATE USER
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        // UPDATE PASSWORD JIKA DIISI
        if ($request->filled('password')) {
            $user->update([
                'password' => Hash::make($request->password),
            ]);
        }

        // UPDATE PEMBINA
        $pembina->update([
            'nip' => $request->nip,
            'no_telp' => $request->no_telp,
            'ekstrakurikuler_id' => $request->ekstrakurikuler_id,
        ]);

        // AMBIL DATA EKSKUL BARU
        $newEkskul = Ekstrakurikuler::find($request->ekstrakurikuler_id)?->nama ?? '-';

        // DETEKSI PERUBAHAN
        $changes = [];

        if ($oldName != $request->name) {
            $changes[] = "Nama: {$oldName} → {$request->name}";
        }

        if ($oldEmail != $request->email) {
            $changes[] = "Email: {$oldEmail} → {$request->email}";
        }

        if ($oldNip != $request->nip) {
            $changes[] = "NIP: {$oldNip} → {$request->nip}";
        }

        if ($oldTelp != $request->no_telp) {
            $changes[] = "No WA: {$oldTelp} → {$request->no_telp}";
        }

        if ($oldEkskul != $newEkskul) {
            $changes[] = "Ekskul: {$oldEkskul} → {$newEkskul}";
        }

        if ($request->filled('password')) {
            $changes[] = 'Password berhasil diperbarui';
        }

        // FORMAT PESAN
        if (count($changes) > 0) {
            $message = "Data pembina berhasil diperbarui:\n- ".implode("\n- ", $changes);
        } else {
            $message = 'Tidak ada data yang diubah';
        }

        return back()->with('success', $message);
    }

    public function destroy($id)
    {
        $pembina = Pembina::findOrFail($id);
        $pembina->user->delete();

        return back()->with('success', 'Pembina dihapus');
    }
}
