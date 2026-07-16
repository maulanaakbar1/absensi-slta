@extends('layouts.app')

@section('title', 'Dashboard Siswa')

@section('content')



    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h3 class="text-2xl font-bold text-slate-800">
                Halo, {{ $user->name }}!
            </h3>

            <p class="text-slate-500 text-sm">
                Selamat datang di panel siswa
                <span class="font-semibold text-blue-600">
                    EskulMate
                </span>
            </p>
        </div>

        <div class="bg-white px-5 py-3 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-3">
            <div class="h-10 w-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                <i class="fas fa-calendar-day"></i>
            </div>

            <div class="flex flex-col">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider leading-none mb-1">
                    Hari Ini
                </span>

                <span class="text-sm font-bold text-slate-700 leading-none">
                    {{ \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM YYYY') }}
                </span>
            </div>
        </div>
    </div>

    {{-- Statistik --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">

        {{-- Total Hadir --}}
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
            <div class="flex items-center gap-4">

                <div
                    class="h-14 w-14 bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center text-xl shadow-inner">
                    <i class="fas fa-clipboard-check"></i>
                </div>

                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">
                        Total Kehadiran
                    </p>

                    <h4 class="text-3xl font-bold text-slate-800">
                        {{ $totalHadir }}
                    </h4>
                </div>
            </div>
        </div>

        {{-- Ekskul --}}
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
            <div class="flex items-center gap-4">

                <div
                    class="h-14 w-14 bg-emerald-100 text-emerald-600 rounded-2xl flex items-center justify-center text-xl shadow-inner">
                    <i class="fas fa-running"></i>
                </div>

                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">
                        Ekstrakurikuler
                    </p>

                    <h4 class="text-xl font-bold text-slate-800">
                        {{ $namaEkskul }}
                    </h4>
                </div>
            </div>
        </div>

        {{-- Status --}}
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
            <div class="flex items-center gap-4">

                <div
                    class="h-14 w-14 bg-orange-100 text-orange-600 rounded-2xl flex items-center justify-center text-xl shadow-inner">
                    <i class="fas fa-user-shield"></i>
                </div>

                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">
                        Status Akun
                    </p>

                    <h4 class="text-xl font-bold text-slate-800">
                        Aktif
                    </h4>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        {{-- Riwayat Absensi --}}
        <div class="lg:col-span-2 bg-white rounded-[2rem] border border-slate-200 shadow-sm overflow-hidden">

            <div class="p-6 border-b border-slate-100 flex justify-between items-center">
                <h4 class="font-bold text-slate-800 text-lg">
                    Riwayat Absensi Terakhir
                </h4>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">

                    <thead class="bg-slate-50 text-slate-500 uppercase text-xs font-bold">
                        <tr>
                            <th class="px-6 py-4">Tanggal</th>
                            <th class="px-6 py-4">Jam</th>
                            <th class="px-6 py-4 text-center">Status</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">

                        @forelse($riwayatAbsensi as $absensi)
                            <tr class="hover:bg-slate-50 transition-colors">

                                <td class="px-6 py-4 text-sm font-medium text-slate-700">
                                    {{ \Carbon\Carbon::parse($absensi->tanggal)->isoFormat('D MMM YYYY') }}
                                </td>

                                <td class="px-6 py-4 text-sm text-slate-500">
                                    {{ $absensi->jam_masuk }}
                                </td>

                                <td class="px-6 py-4 text-center">

                                    @if ($absensi->status == 'hadir')
                                        <span
                                            class="bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full text-xs font-bold">
                                            Hadir
                                        </span>
                                    @else
                                        <span class="bg-amber-100 text-amber-700 px-3 py-1 rounded-full text-xs font-bold">
                                            {{ ucfirst($absensi->status) }}
                                        </span>
                                    @endif

                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="3" class="px-6 py-8 text-center text-slate-400 italic">
                                    Belum ada data absensi.
                                </td>
                            </tr>
                        @endforelse

                    </tbody>

                </table>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="flex flex-col gap-6">

            {{-- Pembina --}}
            @if ($pembinas->count())

                <div class="bg-white p-6 rounded-[2rem] border border-slate-200 shadow-sm relative overflow-hidden">

                    <div class="flex items-center justify-between mb-5 relative z-10">

                        <div>
                            <p class="text-[10px] font-black text-emerald-600 uppercase tracking-widest leading-none mb-1">
                                Pembina Ekskul
                            </p>

                            <h4 class="text-sm font-bold text-slate-800">
                                Total {{ $pembinas->count() }} Pembina
                            </h4>
                        </div>

                        <div
                            class="h-12 w-12 rounded-2xl bg-gradient-to-tr from-emerald-500 to-teal-400 text-white flex items-center justify-center shadow-lg shadow-emerald-100">
                            <i class="fas fa-user-tie"></i>
                        </div>

                    </div>

                    <div class="space-y-3 relative z-10">

                        @foreach ($pembinas as $pembina)
                            <div class="flex items-center gap-3 p-3 rounded-2xl bg-emerald-50 border border-emerald-100">

                                <div
                                    class="h-10 w-10 rounded-xl bg-white text-emerald-600 flex items-center justify-center shadow-sm">
                                    <i class="fas fa-user"></i>
                                </div>

                                <div>
                                    <h5 class="text-sm font-bold text-slate-700 leading-none mb-1">
                                        {{ $pembina->nama ?? $pembina->user->name }}
                                    </h5>

                                    <p
                                        class="text-[10px] font-black text-emerald-600 uppercase tracking-widest leading-none mb-1">
                                        Pembina {{ $namaEkskul }}
                                    </p>
                                </div>

                            </div>
                        @endforeach

                    </div>

                    <div class="absolute -right-4 -bottom-4 opacity-5">
                        <i class="fas fa-users text-8xl transform -rotate-12"></i>
                    </div>

                </div>

            @endif

            {{-- Jadwal --}}
            <div class="bg-white rounded-[2rem] border border-slate-200 shadow-sm overflow-hidden">

                <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                    <h4 class="font-bold text-slate-800 text-base flex items-center gap-2">
                        <i class="fas fa-calendar-alt text-blue-600"></i>
                        Jadwal Ekstrakurikuler
                    </h4>
                </div>

                <div class="p-6 space-y-4">

                    @forelse($jadwalEkskul as $jadwal)
                        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200">

                            <div class="flex flex-col gap-3 mb-2">

                                <div>

                                    <p class="text-sm font-black text-slate-700 mb-2">
                                        @if ($jadwal->tipe === 'dadakan')
                                            <span class="bg-emerald-500 text-white text-[10px] px-3 py-1 rounded-full font-bold">
                                                Jadwal Dadakan
                                            </span>
                                        @else
                                            <span class="bg-blue-500 text-white text-[10px] px-3 py-1 rounded-full font-bold">
                                                Jadwal Rutin
                                            </span>
                                        @endif
                                    </p>
                                    <p class="text-sm font-black text-slate-700">

                                        {{ $jadwal->hari }}
                                    </p>

                                    <p class="text-[10px] font-bold text-emerald-600 uppercase tracking-widest">
                                        {{ date('H:i', strtotime($jadwal->jam_mulai)) }}
                                        -
                                        {{ date('H:i', strtotime($jadwal->jam_selesai)) }} WIB
                                    </p>
                                    <div class="mt-1">
                                        @if ($jadwal->tipe === 'dadakan')
                                            <p class="text-sm font-black text-slate-700">
                                                @php
                                                    Carbon\Carbon::setLocale('id');
                                                    $tanggal = Carbon\Carbon::parse($jadwal->tanggal);

                                                @endphp
                                                {{ $tanggal->translatedFormat('d F Y') }}
                                            </p>
                                        @endif
                                    </div>
                                </div>


                                @if ($statusJadwal == 'Sedang Berlangsung')
                                    <span class="bg-emerald-500 text-white text-[10px] px-3 py-1 rounded-full font-bold">
                                        Sedang Berlangsung
                                    </span>
                                @elseif($statusJadwal == 'Segera Hadir')
                                    <span class="bg-amber-500 text-white text-[10px] px-3 py-1 rounded-full font-bold">
                                        Segera Hadir
                                    </span>
                                @else
                                    <span class="bg-blue-500 text-white text-[10px] px-3 py-1 rounded-full font-bold">
                                        Akan Datang
                                    </span>
                                @endif

                            </div>

                            <div class="space-y-1 border-t border-emerald-200 pt-2 mt-2">

                                <p class="text-[11px] text-slate-600">
                                    <span class="font-bold text-slate-400">
                                        Lokasi:
                                    </span>

                                    {{ $jadwal->lokasi }}
                                </p>

                                @if ($jadwal->keterangan)
                                    <p class="text-[11px] text-slate-500 italic">
                                        "{{ $jadwal->keterangan }}"
                                    </p>
                                @endif

                            </div>

                        </div>

                    @empty

                        <div class="py-8 text-center">

                            <div class="h-12 w-12 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                <i class="fas fa-calendar-times text-slate-300"></i>
                            </div>

                            <p class="text-slate-400 italic text-xs">
                                Tidak ada latihan yang sedang berlangsung saat ini.
                            </p>

                        </div>
                    @endforelse

                </div>
            </div>

        </div>
    </div>

@endsection
