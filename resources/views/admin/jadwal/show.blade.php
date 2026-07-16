@extends('layouts.app')

@section('title', 'Detail Jadwal')

@section('content')

<div class="space-y-6 px-4 sm:px-0">

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between border-b border-slate-100 pb-4">
        <div>
            <h1 class="text-xl md:text-2xl font-bold text-slate-800 tracking-tight">
                {{ $ekskul->nama }}
            </h1>
            <p class="text-xs md:text-sm text-slate-500 mt-0.5">Daftar jadwal latihan rutin & hari libur</p>
        </div>

        <div>
            <a href="{{ route('admin.jadwal.index') }}"
               class="inline-flex items-center justify-center gap-2 w-full sm:w-auto px-4 py-2 bg-slate-100 hover:bg-slate-200 active:bg-slate-300 text-slate-700 rounded-xl font-medium text-sm transition-colors duration-150">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                </svg>
                Kembali
            </a>
        </div>
    </div>

    {{-- JADWAL LATIHAN --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-4 font-bold text-slate-800 border-b bg-slate-50 flex items-center gap-2">
            <span class="w-2 h-4 bg-blue-500 rounded-full"></span>
            Jadwal Latihan
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[700px] text-sm">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="p-3 text-left">Tipe</th>
                        <th class="p-3 text-left">Hari / Tanggal</th>
                        <th class="p-3 text-left">Jam</th>
                        <th class="p-3 text-left">Lokasi</th>
                        <th class="p-3 text-left">Keterangan</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($jadwals as $j)
                    <tr class="border-t hover:bg-slate-50">

                        {{-- TIPE --}}
                        <td class="p-3">
                            @if($j->tipe == 'dadakan')
                                <span class="px-2 py-1 text-xs rounded bg-orange-100 text-orange-700">
                                    Dadakan
                                </span>
                            @else
                                <span class="px-2 py-1 text-xs rounded bg-blue-100 text-blue-700">
                                    Rutin
                                </span>
                            @endif
                        </td>

                        {{-- HARI / TANGGAL --}}
                        <td class="p-3 font-medium">
                            @if($j->tipe == 'dadakan')
                                {{ \Carbon\Carbon::parse($j->tanggal)->locale('id')->isoFormat('DD MMMM YYYY') }}
                            @else
                                Setiap {{ $j->hari }}
                            @endif
                        </td>

                        {{-- JAM --}}
                        <td class="p-3">
                            <span class="px-2 py-1 bg-blue-50 text-blue-700 rounded text-xs">
                                {{ $j->jam_mulai }} - {{ $j->jam_selesai }}
                            </span>
                        </td>

                        {{-- LOKASI --}}
                        <td class="p-3 text-slate-600">
                            {{ $j->lokasi }}
                        </td>

                        {{-- KETERANGAN --}}
                        <td class="p-3 text-slate-500">
                            {{ $j->keterangan ?? '-' }}
                        </td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-6 text-center text-slate-400">
                            Belum ada jadwal latihan
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- LIBUR --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-4 font-bold text-red-600 border-b bg-slate-50 flex items-center gap-2">
            <span class="w-2 h-4 bg-red-500 rounded-full"></span>
            Hari Libur
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="p-3 text-left">Tipe</th>
                        <th class="p-3 text-left">Tanggal / Hari</th>
                        <th class="p-3 text-left">Keterangan</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($liburs as $l)
                    <tr class="border-t hover:bg-red-50/20">

                        {{-- TIPE --}}
                        <td class="p-3">
                            @if($l->tipe == 'dadakan')
                                <span class="px-2 py-1 text-xs rounded bg-orange-100 text-orange-700">
                                    Dadakan
                                </span>
                            @else
                                <span class="px-2 py-1 text-xs rounded bg-red-100 text-red-700">
                                    Rutin
                                </span>
                            @endif
                        </td>

                        {{-- TANGGAL / HARI --}}
                        <td class="p-3 font-medium">
                            @if($l->tipe == 'dadakan')
                                {{ \Carbon\Carbon::parse($j->tanggal)->locale('id')->isoFormat('DD MMMM YYYY') }}
                            @else
                                Setiap {{ $l->hari }}
                            @endif
                        </td>

                        {{-- KETERANGAN --}}
                        <td class="p-3">
                            <span class="px-2 py-1 bg-red-50 text-red-700 text-xs rounded">
                                {{ $l->keterangan }}
                            </span>
                        </td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="p-6 text-center text-slate-400">
                            Tidak ada data libur
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@endsection