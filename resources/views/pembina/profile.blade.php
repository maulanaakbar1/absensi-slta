@extends('layouts.app')
@section('title', 'Profil Saya')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h3 class="text-2xl font-bold text-slate-800">Pengaturan Profil</h3>
            <p class="text-slate-500 text-xs font-semibold uppercase tracking-wider">Kelola informasi akun Anda</p>
        </div>

        {{-- Tombol Kembali di Pojok Kanan --}}
        <a href="{{ route('pembina.dashboard') }}" class="group flex items-center gap-2 px-5 py-2.5 bg-white border border-slate-200 rounded-2xl text-slate-600 hover:text-emerald-600 hover:border-emerald-100 hover:bg-emerald-50 transition-all duration-300 shadow-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 group-hover:-translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span class="font-bold text-sm">Kembali</span>
        </a>
    </div>

    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-600 px-6 py-4 rounded-2xl font-bold">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('pembina.profile.update') }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @csrf
        @method('PUT')
        
        <div class="bg-white p-8 rounded-[2.5rem] border border-slate-200 shadow-sm text-center flex flex-col items-center justify-center">
            <div class="h-24 w-24 rounded-3xl bg-emerald-500 text-white flex items-center justify-center text-4xl font-black shadow-xl shadow-emerald-100 mb-4">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <h4 class="font-bold text-slate-800 text-lg leading-tight">{{ $user->name }}</h4>
            <p class="text-slate-400 text-xs font-bold uppercase mt-1 tracking-widest">{{ $user->role }}</p>
        </div>

        <div class="md:col-span-2 bg-white p-8 rounded-[2.5rem] border border-slate-200 shadow-sm space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-400 uppercase ml-1">Nama Lengkap</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-emerald-500 focus:ring-0 transition">
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-400 uppercase ml-1">Email</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-emerald-500 focus:ring-0 transition">
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-400 uppercase ml-1">NIP (Opsional)</label>
                    <input type="text" name="nip" value="{{ old('nip', $pembina->nip ?? '') }}" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-emerald-500 focus:ring-0 transition">
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-400 uppercase ml-1">No. Telepon</label>
                    <input type="text" name="no_telp" value="{{ old('no_telp', $pembina->no_telp ?? '') }}" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-emerald-500 focus:ring-0 transition">
                </div>
            </div>

            <hr class="my-6 border-slate-100">
            
            <p class="text-xs font-bold text-amber-500 uppercase ml-1">Ganti Password (Kosongkan jika tidak diganti)</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
                <input 
                    type="password" 
                    name="password" 
                    placeholder="Password Baru"
                    autocomplete="new-password"
                    class="w-full px-4 py-3 rounded-xl border border-slate-200"/>

                <input 
                    type="password" 
                    name="password_confirmation" 
                    placeholder="Konfirmasi Password"
                    autocomplete="new-password"
                    class="w-full px-4 py-3 rounded-xl border border-slate-200"/>
            </div>

            <div class="pt-4">
                <button type="submit" class="w-full bg-emerald-600 text-white font-bold py-4 rounded-2xl shadow-lg shadow-emerald-100 hover:bg-emerald-700 transition">
                    Simpan Perubahan
                </button>
            </div>
        </div>
    </form>
</div>
@endsection