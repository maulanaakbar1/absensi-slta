<?php

namespace App\Http\Controllers\Pembina;

use App\Http\Controllers\Controller;
use App\Models\Pembina;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $pembina = Pembina::where('user_id', $user->id)->first();
        return view('pembina.profile', compact('user', 'pembina'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $pembina = Pembina::firstOrCreate([
            'user_id' => $user->id
        ]);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'nip' => 'nullable|unique:pembinas,nip,' . $pembina->id,
            'no_telp' => 'nullable|max:15',
            'password' => 'nullable|min:6|confirmed',
        ]);

        $changes = [];

        // Cek perubahan user
        if ($user->name !== $request->name) {
            $changes[] = 'Nama';
        }

        if ($user->email !== $request->email) {
            $changes[] = 'Email';
        }

        if (!empty($request->password)) {
            $changes[] = 'Password';
        }

        // Cek perubahan pembina
        if (($pembina->nip ?? '') !== $request->nip) {
            $changes[] = 'NIP';
        }

        if (($pembina->no_telp ?? '') !== $request->no_telp) {
            $changes[] = 'Nomor Telepon';
        }

        // Update user
        $user->name = $request->name;
        $user->email = $request->email;

        if (!empty($request->password)) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        $user->save();

        // Update pembina
        $pembina->update([
            'nip' => $request->nip,
            'no_telp' => $request->no_telp,
        ]);

        // Pesan alert
        if (count($changes)) {
            $message = implode(', ', $changes) . ' berhasil diperbarui!';
        } else {
            $message = 'Tidak ada perubahan data.';
        }

        return back()->with('success', $message);
    }
}