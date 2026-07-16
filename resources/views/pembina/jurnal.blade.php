@extends('layouts.app')

@section('title', 'Jurnal Pembina')

@section('content')

<div class="space-y-6">

    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-xl md:text-2xl font-bold text-slate-800">
                Jurnal Pembina
            </h1>
            <p class="text-sm text-slate-500">
                Jurnal otomatis dari jadwal latihan dan absensi
            </p>
        </div>

        <div class="w-full lg:w-auto">
            <form method="GET" id="filterForm" class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-end">
                
                <div class="grid grid-cols-2 gap-3 sm:flex sm:items-end w-full sm:w-auto">
                    <div class="flex items-end">
                        <a href="{{ route('pembina.jurnal.pdf', request()->query()) }}"
                           class="w-full text-center px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-xl font-medium text-sm transition h-[42px] flex items-center justify-center">
                            Download PDF
                        </a>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">
                            Tanggal
                        </label>
                        <input 
                            type="date" 
                            name="tanggal" 
                            value="{{ request('tanggal') }}"
                            onchange="document.getElementById('filterForm').submit()"
                            class="w-full border border-slate-200 rounded-xl px-3 py-2 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 h-[42px]"
                        >
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 sm:flex sm:items-end w-full sm:w-auto">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">
                            Bulan
                        </label>
                        <select 
                            name="bulan" 
                            onchange="document.getElementById('filterForm').submit()"
                            class="w-full border border-slate-200 rounded-xl px-3 py-2 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 h-[42px]">
                            @foreach(range(1,12) as $b)
                                <option value="{{ $b }}" {{ $bulan == $b ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create()->month($b)->translatedFormat('F') }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">
                            Tahun Ajaran
                        </label>
                        <select 
                            name="tahun_ajaran" 
                            onchange="document.getElementById('filterForm').submit()"
                            class="w-full border border-slate-200 rounded-xl px-3 py-2 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 h-[42px]">
                            @foreach($tahunAjaranList as $ta)
                                <option value="{{ $ta }}" {{ $tahunAjaran == $ta ? 'selected' : '' }}>
                                    {{ $ta }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                @if(
                    request('tanggal')
                    || $bulan != now()->month
                    || $tahunAjaran != ($tahunAjaranList[0] ?? $tahunAjaran)
                )
                    <div class="w-full sm:w-auto">
                        <a href="{{ route('pembina.jurnal.index') }}"
                           class="block w-full text-center px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium text-sm transition h-[42px] flex items-center justify-center">
                            Reset
                        </a>
                    </div>
                @endif

            </form>
        </div>

    </div>

    {{-- ... bagian atas / form filter tetap sama ... --}}

    <div class="bg-white rounded-2xl shadow-sm border overflow-hidden">
        <div class="w-full overflow-x-auto scrollbar-thin">
            <table class="w-full min-w-[800px] border-collapse whitespace-nowrap">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="p-3 md:p-4 text-left text-xs md:text-sm font-semibold text-slate-600">No</th>
                        <th class="p-3 md:p-4 text-left text-xs md:text-sm font-semibold text-slate-600">Hari / Tanggal</th>
                        <th class="p-3 md:p-4 text-left text-xs md:text-sm font-semibold text-slate-600">Jadwal</th>
                        <th class="p-3 md:p-4 text-left text-xs md:text-sm font-semibold text-slate-600">Jam</th>
                        <th class="p-3 md:p-4 text-left text-xs md:text-sm font-semibold text-slate-600">Lokasi</th>
                        <th class="p-3 md:p-4 text-left text-xs md:text-sm font-semibold text-slate-600">Keterangan</th>
                        <th class="p-3 md:p-4 text-center text-xs md:text-sm font-semibold text-slate-600">Kehadiran</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($events as $index => $event)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="p-3 md:p-4 text-sm text-slate-600">
                                {{ ($events->currentPage() - 1) * $events->perPage() + $index + 1 }}
                            </td>
                            <td class="p-3 md:p-4 text-sm font-medium text-slate-800">
                                {{ $event['tanggal']->translatedFormat('l, d F Y') }}
                            </td>
                            <td class="p-3 md:p-4 text-sm">
                                @if($event['libur'])
                                    <span class="inline-block px-2.5 py-1 rounded-full bg-slate-100 text-slate-500 text-xs font-semibold">
                                        -
                                    </span>
                                @elseif($event['jadwal'] === 'Dadakan')
                                    <span class="inline-block px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-semibold">
                                        Dadakan
                                    </span>
                                @else
                                    <span class="inline-block px-2.5 py-1 rounded-full bg-blue-100 text-blue-700 text-xs font-semibold">
                                        Rutin
                                    </span>
                                @endif
                            </td>
                            <td class="p-3 md:p-4 text-sm text-slate-600">
                                {{ $event['jam'] }}
                            </td>
                            <td class="p-3 md:p-4 text-sm text-slate-600">
                                {{ $event['lokasi'] }}
                            </td>
                            <td class="p-3 md:p-4 text-sm">
                                @if($event['libur'])
                                    <span class="text-red-600 font-medium bg-red-50/50 px-2 py-1 rounded">
                                        {{ $event['keterangan_libur'] }}
                                    </span>
                                @else
                                    <span class="text-slate-700">
                                        {{ $event['keterangan'] ?: '-' }}
                                    </span>
                                @endif
                            </td>
                            <td class="p-3 md:p-4 text-center text-sm">
                                @if($event['libur'])
                                    <span class="inline-block px-2.5 py-1 rounded-full bg-red-100 text-red-700 text-xs font-semibold">
                                        Libur
                                    </span>
                                @else
                                    <span class="inline-block px-2.5 py-1 rounded-full bg-green-100 text-green-700 text-xs font-semibold">
                                        {{ $event['hadir'] }}/{{ $event['total'] }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-10 text-center text-slate-400 text-sm">
                                Belum ada jurnal
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- TAMPILKAN TOMBOL NAVIGASI PAGINATION DI SINI --}}
        @if($events->hasPages())
            <div class="px-4 py-3 bg-slate-50 border-t flex items-center justify-between system-pagination">
                <div class="w-full link-pagination">
                    {{ $events->links() }}
                </div>
            </div>
        @endif
    </div>

</div>

@endsection