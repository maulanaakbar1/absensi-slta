@extends('layouts.app')
@section('title', 'Dashboard Pembina')

@section('content')
<div class="space-y-8">
    {{-- Header Section --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        {{-- Sisi Kiri: Ucapan Selamat Datang --}}
        <div>
            <h3 class="text-2xl font-bold text-slate-800">Halo, {{ Auth::user()->name }}!</h3>
            <p class="text-slate-500 text-sm mt-1">Selamat datang kembali di panel pembina ekskul.</p>
        </div>

        {{-- Sisi Kanan: Status & Tanggal --}}
        <div class="flex flex-col md:flex-row items-end md:items-center gap-3">
            {{-- Kartu Tanggal --}}
            <div class="bg-white px-5 py-2.5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-3">
                <div class="h-8 w-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <div class="flex flex-col">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider leading-none mb-1">Hari Ini</span>
                    <span class="text-sm font-bold text-slate-700 leading-none">
                        {{ \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM YYYY') }}
                    </span>
                </div>
            </div>

            {{-- Badge Status --}}
            <div class="bg-white px-4 py-2.5 rounded-2xl border border-slate-200 flex items-center gap-3 shadow-sm">
                <div class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></div>
                <span class="text-xs font-bold text-slate-600 uppercase tracking-wider">Status: Pembina Aktif</span>
            </div>
        </div>
    </div>

    {{-- Main Info Card --}}
    <div class="bg-white p-8 rounded-[2.5rem] border border-slate-200 shadow-sm">
        <div class="grid lg:grid-cols-3 gap-8 items-center">
            {{-- Logo Ekskul --}}
            <div class="flex justify-center lg:justify-start">
                <div class="h-32 w-32 rounded-[2rem] bg-blue-600 text-white font-black text-4xl flex items-center justify-center overflow-hidden shadow-xl">
                    @if($pembina && $pembina->ekstrakurikuler && $pembina->ekstrakurikuler->foto)
                        <img src="{{ asset('storage/' . $pembina->ekstrakurikuler->foto) }}" class="w-full h-full object-cover">
                    @else
                        {{ substr($pembina->ekstrakurikuler->nama ?? '?', 0, 1) }}
                    @endif
                </div>
            </div>

            {{-- Detail Ekskul --}}
            <div class="text-center lg:text-left">
                <span class="text-xs font-bold text-blue-600 uppercase tracking-[0.2em]">
                    Ekskul yang Dibina
                </span>
                <h2 class="text-3xl font-black text-slate-800 mt-1">
                    {{ $pembina->ekstrakurikuler->nama ?? 'Belum Ditugaskan' }}
                </h2>
                <p class="text-slate-500 mt-2 text-sm leading-relaxed">
                    {{ $pembina->ekstrakurikuler->deskripsi ?? 'Silahkan hubungi admin untuk menetapkan tugas pembina.' }}
                </p>
            </div>

            {{-- Total Anggota --}}
            <div class="w-full">
                <div class="bg-slate-900 text-white p-6 rounded-3xl text-center lg:text-left shadow-lg">
                    <p class="text-xs uppercase tracking-widest text-slate-400 font-medium">
                        Total Anggota
                    </p>
                    <h3 class="text-5xl font-black mt-2">
                        {{ $jumlahSiswa }}
                    </h3>
                    <p class="text-slate-400 text-xs mt-1">
                        Siswa Terdaftar
                    </p>
                </div>
            </div>
        </div>

        {{-- Tingkatan Anggota (Sub-Grid) --}}
        <div class="grid md:grid-cols-2 gap-4 mt-8">
            {{-- Balonpas --}}
            <div class="bg-blue-50/60 border border-blue-100 p-6 rounded-3xl transition-all hover:bg-blue-50">
                <p class="text-xs uppercase tracking-widest text-blue-600 font-bold">
                    Balonpas
                </p>
                <h3 class="text-4xl font-black text-blue-700 mt-2">
                    {{ $jumlahBalonpas }}
                </h3>
                <p class="text-blue-500 text-xs mt-1">
                    Anggota Tingkat Balonpas
                </p>
            </div>

            {{-- Instruktur --}}
            <div class="bg-emerald-50/60 border border-emerald-100 p-6 rounded-3xl transition-all hover:bg-emerald-50">
                <p class="text-xs uppercase tracking-widest text-emerald-600 font-bold">
                    Instruktur
                </p>
                <h3 class="text-4xl font-black text-emerald-700 mt-2">
                    {{ $jumlahInstruktur }}
                </h3>
                <p class="text-emerald-500 text-xs mt-1">
                    Anggota Tingkat Instruktur
                </p>
            </div>
        </div>
    </div>

    {{-- Bottom Section (Aktivitas & Jadwal) --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- Absensi Hari Ini --}}
        <div class="bg-white border border-slate-200 p-6 rounded-3xl flex items-start gap-4 shadow-sm">
            <div class="h-12 w-12 rounded-2xl bg-emerald-500 text-white flex items-center justify-center shadow-lg shadow-emerald-100 flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <h5 class="font-bold text-slate-800 text-sm">Kehadiran Hari Ini</h5>
                <p class="text-2xl font-black text-emerald-600 mt-1">
                    {{ $absensiHariIni }} 
                    <span class="text-xs font-medium text-slate-400 ml-1">Siswa Hadir</span>
                </p>
            </div>
        </div>

        {{-- Jadwal Terdekat --}}
        <div class="bg-white border border-slate-200 p-6 rounded-3xl flex items-start gap-4 shadow-sm">
            <div class="h-12 w-12 rounded-2xl bg-blue-500 text-white flex items-center justify-center shadow-lg shadow-blue-100 flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
            <div class="space-y-1.5 flex-1">
                <div class="flex items-center gap-2 flex-wrap">
                    <h5 class="font-bold text-slate-800 text-sm">Jadwal Latihan</h5>
                    @if($labelJadwal === 'Hari Ini Libur')
                        <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-md bg-red-100 text-red-700 uppercase tracking-wide">
                            LIBUR HARI INI
                        </span>
                    @elseif($labelJadwal)
                        <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-md bg-blue-100 text-blue-700 uppercase tracking-wide">
                            {{ $labelJadwal }}
                        </span>
                    @endif
                </div>

                @if($jadwalTerdekat)
                    <p class="text-sm text-slate-700 font-semibold">
                        @if($jadwalTerdekat->tipe === 'dadakan')
                            {{ \Carbon\Carbon::parse($jadwalTerdekat->tanggal)->translatedFormat('l, d F Y') }}
                        @else
                            {{ $jadwalTerdekat->hari }}
                        @endif
                        <span class="text-blue-600 ml-1">
                            ({{ date('H:i', strtotime($jadwalTerdekat->jam_mulai)) }} - {{ date('H:i', strtotime($jadwalTerdekat->jam_selesai)) }} WIB)
                        </span>
                    </p>
                    
                    <div class="flex flex-col gap-1.5 pt-1 border-t border-slate-100">
                        <span class="text-xs text-slate-500 flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            {{ $jadwalTerdekat->lokasi }}
                        </span>
                        
                        @if($jadwalTerdekat->keterangan)
                            <span class="text-xs text-amber-600 font-medium flex items-start gap-1 bg-amber-50 p-2 rounded-lg border border-amber-100/70">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-amber-500 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="italic">"{{ $jadwalTerdekat->keterangan }}"</span>
                            </span>
                        @endif
                    </div>
                @else
                    <p class="text-xs text-slate-400 italic">Tidak ada jadwal terdekat.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection