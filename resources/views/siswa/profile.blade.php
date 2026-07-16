@extends('layouts.app') 

@section('title', 'Profil')

@section('content')
<div class="max-w-5xl mx-auto py-8 px-4">

    {{-- TOMBOL KEMBALI DI LUAR CARD --}}
    <div class="mb-5">
        <a href="{{ route('siswa.dashboard') }}"
           class="group inline-flex items-center gap-2 px-5 py-3 bg-white border border-slate-200 rounded-2xl text-slate-600 hover:text-blue-600 hover:border-blue-100 hover:bg-blue-50 transition-all duration-300 shadow-sm">

            <svg xmlns="http://www.w3.org/2000/svg"
                 class="h-5 w-5 group-hover:-translate-x-1 transition-transform"
                 fill="none"
                 viewBox="0 0 24 24"
                 stroke="currentColor">
                <path stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>

            <span class="font-bold text-sm">Kembali</span>
        </a>
    </div>

    <div class="bg-white rounded-3xl border border-slate-200 overflow-hidden shadow-sm">
        
        {{-- HEADER --}}
        <div class="p-8 border-b border-slate-100 bg-slate-50/50">
            <div>
                <h3 class="text-xl font-extrabold text-slate-800">Profil Saya</h3>
                <p class="text-sm text-slate-500">Kelola informasi pribadi, data orang tua, dan keamanan akun Anda</p>
            </div>

        </div>

        <div class="p-8">
            @if ($errors->any())
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-2xl">
                    <ul class="text-sm text-red-600 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>• {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(session('success'))
                <div class="mb-6 p-4 bg-emerald-50 text-emerald-600 rounded-2xl border border-emerald-100 font-bold text-sm flex items-center gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    {{ session('success') }}
                </div>
            @endif
            

            <form action="{{ route('siswa.profile.update') }}" method="POST" class="space-y-10">
                @csrf
                @method('PUT')

                <div>
                    <h4 class="text-xs font-bold text-blue-600 uppercase tracking-widest mb-6 flex items-center gap-2">
                        <span class="h-1 w-1 bg-blue-600 rounded-full"></span> Informasi Akun
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700">Nama Lengkap</label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" 
                                class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none transition">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700">Email</label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}" 
                                class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none transition">
                        </div>
                    </div>
                </div>

                <div>
                    <h4 class="text-xs font-bold text-blue-600 uppercase tracking-widest mb-6 flex items-center gap-2">
                        <span class="h-1 w-1 bg-blue-600 rounded-full"></span> Data Personal
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700">NIS</label>
                            <input type="text" name="nis" value="{{ old('nis', $siswa->nis) }}" 
                                class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none transition">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700">NISN</label>
                            <input type="text" name="nisn" value="{{ old('nisn', $siswa->nisn) }}" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none transition">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700">
                                Tahun Angkatan
                            </label>

                            <input type="text"
                                value="{{ $siswa->tahun_masuk }}"
                                readonly
                                class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 text-slate-600">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700">
                                Tahun Ajaran
                            </label>

                            <input type="text"
                                value="{{ $tahunAjaran }}"
                                readonly
                                class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 text-slate-600">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700">
                                Kelas Saat Ini
                            </label>

                            <input type="text"
                                value="{{ $kelasDisplay }}"
                                readonly
                                class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 text-slate-600 font-bold">
                        </div>
                        <div class="space-y-2">

                            <label class="text-sm font-bold text-slate-700">
                                Tingkatan
                            </label>

                            @php
                                $tingkatanColor = match($siswa->tingkatan) {
                                    'balonpas' => 'bg-blue-50 text-blue-600',
                                    'instruktur' => 'bg-emerald-50 text-emerald-600',
                                    default => 'bg-slate-100 text-slate-500',
                                };
                            @endphp

                            <div class="px-4 py-3 rounded-xl border font-bold capitalize {{ $tingkatanColor }}">
                                {{ $siswa->tingkatan ?? '-' }}
                            </div>

                        </div>
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-500 uppercase">
                                No. Telp Siswa
                            </label>

                            <input
                                type="text"
                                name="no_telp_siswa"
                                value="{{ old('no_telp_siswa', $siswa->no_telp_siswa) }}"
                                class="w-full px-4 py-2.5 rounded-lg border border-slate-200 bg-white outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700">Tempat Lahir</label>
                            <input type="text" name="tempat_lahir" value="{{ old('tempat_lahir', $siswa->tempat_lahir) }}" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none transition">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700">
                                Tanggal Lahir
                            </label>

                            <input
                                type="date"
                                name="tanggal_lahir"
                                value="{{ old('tanggal_lahir', optional($siswa->tanggal_lahir)->format('Y-m-d')) }}"
                                class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none transition">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700">Jenis Kelamin</label>
                            <select name="jenis_kelamin" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none transition">
                                <option value="L" {{ old('jenis_kelamin', $siswa->jenis_kelamin) == 'L' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="P" {{ old('jenis_kelamin', $siswa->jenis_kelamin) == 'P' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-6 space-y-2">
                        <label class="text-sm font-bold text-slate-700">Alamat Lengkap</label>
                        <textarea name="alamat" rows="3" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none transition">{{ old('alamat', $siswa->alamat) }}</textarea>
                    </div>
                </div>

                <div>
                    <h4 class="text-xs font-bold text-blue-600 uppercase tracking-widest mb-6 flex items-center gap-2">
                        <span class="h-1 w-1 bg-blue-600 rounded-full"></span> Kontak Orang Tua
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

                        <div class="p-6 bg-slate-50 rounded-2xl space-y-4 border border-slate-100">
                            <p class="font-bold text-slate-800 text-sm">Data Ayah</p>
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-500 uppercase">Nama Ayah</label>
                                <input type="text" name="nama_ayah" value="{{ old('nama_ayah', $siswa->nama_ayah) }}" class="w-full px-4 py-2.5 rounded-lg border border-slate-200 bg-white outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-500 uppercase">No. Telp Ayah</label>
                                <input type="text" name="no_telp_ayah" value="{{ old('no_telp_ayah', $siswa->no_telp_ayah) }}" class="w-full px-4 py-2.5 rounded-lg border border-slate-200 bg-white outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                        <div class="p-6 bg-slate-50 rounded-2xl space-y-4 border border-slate-100">
                            <p class="font-bold text-slate-800 text-sm">Data Ibu</p>
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-500 uppercase">Nama Ibu</label>
                                <input type="text" name="nama_ibu" value="{{ old('nama_ibu', $siswa->nama_ibu) }}" class="w-full px-4 py-2.5 rounded-lg border border-slate-200 bg-white outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-500 uppercase">No. Telp Ibu</label>
                                <input type="text" name="no_telp_ibu" value="{{ old('no_telp_ibu', $siswa->no_telp_ibu) }}" class="w-full px-4 py-2.5 rounded-lg border border-slate-200 bg-white outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pt-6 border-t border-slate-100">
                    <h4 class="text-xs font-bold text-red-600 uppercase tracking-widest mb-6">Ganti Password</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700">Password Baru</label>
                            <input type="password" name="password" placeholder="Kosongkan jika tidak ingin ganti" 
                                class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none transition placeholder:text-slate-300">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-700">Konfirmasi Password</label>
                            <input type="password" name="password_confirmation" 
                                class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none transition">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-4">
                    <button type="submit" 
                        class="w-full md:w-auto px-10 py-4 bg-blue-600 text-white rounded-2xl font-bold hover:bg-blue-700 transition shadow-xl shadow-blue-200 flex items-center justify-center gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M7.707 10.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V4a1 1 0 10-2 0v7.586l-1.293-1.293z" />
                            <path d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" />
                        </svg>
                        Perbarui Profil
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection