@extends('layouts.app')
@section('title', 'Data Anggota')

@section('content')
<div x-data="{ openModal: false, editMode: false, currentData: {} }" class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h3 class="text-2xl font-bold text-slate-800">Data Anggota Siswa</h3>
            <p class="text-slate-500 text-sm">Kelola daftar siswa ekstrakurikuler Anda.</p>
        </div>

        <div class="flex justify-start sm:justify-end">
            <button
                @click="openModal = true;editMode = false;currentData = {};"
                class="bg-blue-600 text-white px-5 py-2.5 rounded-xl text-sm font-bold shadow-md shadow-blue-100 hover:bg-blue-700 transition flex items-center gap-2 w-fit">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Siswa
            </button>
        </div>
    </div>

    {{-- Filter --}}
    <div class="bg-white p-6 rounded-[2rem] border border-slate-200 shadow-sm">
        <form 
                id="filterForm"
                action="{{ route('pembina.anggota.index') }}"
                method="GET"
                class="flex flex-col md:flex-row gap-4 items-end"
            >

            {{-- Filter Tahun Ajaran --}}
            <div class="w-full md:w-56">
                <label class="text-xs font-bold text-slate-400 uppercase ml-1">Tahun Ajaran</label>
                <select
                    name="tahun_ajaran"
                    onchange="this.form.submit()"
                    class="w-full mt-1 px-4 py-2.5 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-0 transition text-sm font-bold text-slate-700"
                >
                    <option value="semua" {{ $selectedTahun == 'semua' ? 'selected' : '' }}>
                        Semua Tahun Ajaran
                    </option>

                    @foreach($tahunAjaranList as $tahun)
                        <option value="{{ $tahun }}" {{ $selectedTahun == $tahun ? 'selected' : '' }}>
                            {{ $tahun }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Filter Kelas --}}
            <div class="w-full md:w-44">
                <label class="text-xs font-bold text-slate-400 uppercase ml-1">
                    Kelas
                </label>

                <select
                    name="kelas"
                    onchange="this.form.submit()"
                    class="w-full mt-1 px-4 py-2.5 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-0 transition text-sm font-bold text-slate-700"
                >
                    <option value="">Semua Kelas</option>

                    <option value="7" {{ $selectedKelas == '7' ? 'selected' : '' }}>
                        VII
                    </option>

                    <option value="8" {{ $selectedKelas == '8' ? 'selected' : '' }}>
                        VIII
                    </option>

                    <option value="9" {{ $selectedKelas == '9' ? 'selected' : '' }}>
                        IX
                    </option>
                </select>
            </div>

            {{-- JURUSAN --}}
            <div class="w-full md:w-44 flex-shrink-0">
                <label class="text-xs font-bold text-slate-400 uppercase ml-1">
                    Kode Kelas
                </label>
                <select
                    name="jurusan"
                    onchange="this.form.submit()"
                    class="w-full mt-1 px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm font-semibold text-slate-700 focus:border-blue-500 focus:ring-0">
                    <option value="">Semua Kode Kelas</option>
                    @foreach($jurusanList as $jurusan)
                        <option value="{{ $jurusan }}" {{ $selectedJurusan == $jurusan ? 'selected' : '' }}>
                            {{ $jurusan }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Cari Nama --}}
            <div class="w-full md:flex-1 min-w-[200px]">
                <label class="text-xs font-bold text-slate-400 uppercase ml-1 whitespace-nowrap">
                    Cari Nama
                </label>

                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Masukkan nama siswa..."
                    oninput="debounceSearch()"
                    class="w-full mt-1 px-4 py-2.5 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-0 transition text-sm"
                >
            </div>

            {{-- Reset --}}
            <div class="w-full md:w-auto flex-shrink-0">
                @if(
                    request('search') ||
                    (request('tahun_ajaran') && request('tahun_ajaran') !== 'semua') ||
                    request('kelas') ||
                    request('jurusan')
                )
                    <a href="{{ route('pembina.anggota.index') }}"
                        class="block bg-slate-100 text-slate-600 px-6 py-2.5 rounded-xl font-bold hover:bg-slate-200 transition text-sm text-center whitespace-nowrap">
                        Reset Filter
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Info tahun ajaran aktif + Import Export --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 px-1">

        {{-- INFO --}}
        <div class="flex items-center gap-2 text-sm text-slate-500">

            <svg xmlns="http://www.w3.org/2000/svg"
                class="h-4 w-4 text-blue-500 flex-shrink-0"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor">

                <path stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />

            </svg>

            <span class="leading-relaxed">

                @if($selectedTahun === 'semua')

                    Menampilkan
                    <span class="font-bold text-blue-700 bg-blue-50/80 px-2 py-0.5 rounded-lg">
                        semua siswa
                    </span>
                    dari seluruh tahun ajaran.

                @else

                    Menampilkan siswa aktif di tahun ajaran

                    <span class="font-bold text-blue-700 bg-blue-50/80 px-2 py-0.5 rounded-lg">
                        {{ $selectedTahun }}
                    </span>

                    — tingkat kelas disesuaikan otomatis.

                @endif

            </span>

        </div>

        {{-- IMPORT EXPORT --}}
        <div class="flex items-center gap-2 flex-wrap">

            {{-- IMPORT --}}
            <form 
                id="filterForm"
                action="{{ route('pembina.anggota.import') }}"
                method="POST"
                enctype="multipart/form-data"
                class="flex flex-col md:flex-row gap-4 items-end">
                @csrf

                <input
                    type="file"
                    name="file"
                    id="importFile"
                    required
                    class="hidden"
                    onchange="this.form.submit()"
                >

                <label
                    for="importFile"
                    class="cursor-pointer bg-indigo-600 text-white px-3 py-1.5 rounded-lg text-xs font-bold hover:bg-indigo-700 transition flex items-center gap-1"
                >

                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="h-3.5 w-3.5"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor">

                        <path stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4-4m0 0l-4 4m4-4v12" />

                    </svg>

                    Import
                </label>
            </form>

            {{-- EXPORT --}}
           <a href="{{ route('pembina.anggota.export', request()->query()) }}" class="bg-emerald-600 text-white px-3 py-1.5 rounded-lg text-xs font-bold hover:bg-emerald-700 transition flex items-center gap-1">

                <svg xmlns="http://www.w3.org/2000/svg"
                    class="h-3.5 w-3.5"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor">

                    <path stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 4v12m0 0l-4-4m4 4l4-4m6 4v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2" />

                </svg>

                Export
            </a>

        </div>

    </div>

    {{-- Table Container --}}
    <div class="bg-white rounded-[2rem] border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto w-full">
            <table class="w-full text-left border-collapse min-w-[900px]">
                <thead>
                    <tr class="bg-slate-50/70 border-b border-slate-100">
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider whitespace-nowrap">Siswa</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider whitespace-nowrap">NISN</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider whitespace-nowrap">
                            Kelas
                            @if($selectedTahun !== 'semua')
                                <span class="text-blue-500 normal-case font-medium">({{ $selectedTahun }})</span>
                            @endif
                        </th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider whitespace-nowrap">Tahun Ajaran</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider whitespace-nowrap">Angkatan</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider whitespace-nowrap">Jenis Kelamin</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider whitespace-nowrap">Tingkatan</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider whitespace-nowrap text-center w-28">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @forelse($anggota as $s)
                    @php
                        $tingkatColor = match($s->tingkat_display) {
                            7 => 'text-blue-600 bg-blue-50',
                            8 => 'text-emerald-600 bg-emerald-50',
                            9 => 'text-purple-600 bg-purple-50',
                            default => 'text-slate-600 bg-slate-50',
                        };
                    @endphp
                    <tr class="hover:bg-slate-50/40 transition">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center font-bold shrink-0">
                                    {{ strtoupper(substr($s->user?->name ?? 'S', 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-bold text-slate-700">
                                        {{ $s->user?->name ?? '-' }}
                                    </p>
                                    <p class="text-xs text-slate-400">
                                        {{ $s->user?->email ?? '-' }}
                                    </p>
                                </div>
                            </div>
                        </td>
                        
                        <td class="px-6 py-4 text-sm font-semibold text-slate-600 whitespace-nowrap">
                            {{ $s->nisn ?? '-' }}
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2.5 py-1 rounded-lg text-xs font-bold uppercase tracking-wide inline-block {{ $tingkatColor }}">
                                {{ $s->kelas_display ?? '-'}}
                            </span>
                        </td>

                        <td class="px-6 py-4 text-sm whitespace-nowrap">
                            @if($selectedTahun === 'semua')
                                <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-600 text-xs font-semibold">
                                    Semua
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-lg bg-blue-50 text-blue-600 text-xs font-semibold">
                                    {{ $selectedTahun }}
                                </span>
                            @endif
                        </td>

                        <td class="px-6 py-4 text-sm text-slate-600 whitespace-nowrap font-medium">
                            @if($s->tahun_masuk)
                                <span>{{ $s->tahun_masuk }}/{{ $s->tahun_masuk + 1 }}</span>
                            @else
                                <span class="italic text-slate-400">—</span>
                            @endif
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-600 text-xs font-semibold">
                                {{ $s->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}
                            </span>
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap">

                                @php
                                    $tingkatanColor = match($s->tingkatan) {
                                        'balonpas' => 'bg-blue-50 text-blue-600',
                                        'instruktur' => 'bg-emerald-50 text-emerald-600',
                                        default => 'bg-slate-100 text-slate-600',
                                    };
                                @endphp

                                <span class="px-2.5 py-1 rounded-lg text-xs font-bold uppercase {{ $tingkatanColor }}">
                                    {{ ucfirst($s->tingkatan ?? '-') }}
                                </span>

                            </td>

                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex justify-center items-center gap-1">

                                <a href="{{ route('pembina.anggota.show', $s->id) }}"
                                class="p-2 text-blue-500 hover:bg-blue-50 rounded-lg transition"
                                title="Detail siswa">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="h-5 w-5"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </a>
                                {{-- Edit --}}
                                <button
                                    @click="
                                        openModal = true;
                                        editMode = true;
                                        currentData = {
                                            id: '{{ $s->id }}',
                                            name: @js($s->user?->name ?? ''),
                                            email: @js($s->user?->email ?? ''),
                                            nis: @js($s->nis),
                                            nisn: @js($s->nisn),
                                            tahun_masuk: @js($s->tahun_masuk),
                                            tingkat_awal: @js($s->tingkat_awal),
                                            jurusan: @js($s->jurusan),
                                            jk: @js($s->jenis_kelamin),
                                            tingkatan: @js($s->tingkatan)
                                        };
                                    "
                                    class="p-2 text-amber-500 hover:bg-amber-50 rounded-lg transition"
                                    title="Edit data siswa"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>

                                {{-- Delete --}}
                                <form action="{{ route('pembina.anggota.destroy', $s->id) }}" method="POST" class="inline form-delete">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" 
                                            data-nama="{{ $s->user?->name }}" 
                                            class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition btn-delete" 
                                            title="Hapus siswa">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center gap-2 text-slate-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 opacity-40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                @if($selectedTahun === 'semua')
                                    <p class="italic text-sm">Belum ada anggota di ekstrakurikuler ini.</p>
                                @else
                                    <p class="italic text-sm">Tidak ada anggota di tahun ajaran <strong>{{ $selectedTahun }}</strong>.</p>
                                    <p class="text-xs">Coba pilih tahun ajaran lain atau tambahkan siswa baru.</p>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    @if($anggota->hasPages())
        <div class="bg-white px-6 py-5 rounded-[2rem] border border-slate-200 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
            
            {{-- Info Text --}}
            <div class="text-sm text-slate-500 text-center sm:text-left">
                Menampilkan 
                <span class="font-bold text-slate-800">{{ $anggota->firstItem() }}</span> 
                sampai 
                <span class="font-bold text-slate-800">{{ $anggota->lastItem() }}</span> 
                dari 
                <span class="font-bold text-slate-800">{{ $anggota->total() }}</span> siswa
            </div>

            {{-- Pagination Buttons --}}
            <nav class="inline-flex flex-wrap items-center gap-1.5 justify-center" aria-label="Pagination">
                
                {{-- Previous Page Link --}}
                @if ($anggota->onFirstPage())
                    <span class="p-2.5 rounded-xl border border-slate-100 bg-slate-50/50 text-slate-300 cursor-not-allowed">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                        </svg>
                    </span>
                @else
                    <a href="{{ $anggota->previousPageUrl() }}" class="p-2.5 rounded-xl border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 hover:text-blue-600 transition shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>
                @endif

                {{-- Pagination Elements --}}
                @foreach ($anggota->linkCollection() as $link)

                    @if ($link['url'] === null)
                        <span class="px-3 py-1.5 text-sm font-bold text-slate-400 cursor-default">
                            {!! $link['label'] !!}
                        </span>
                    @elseif ($link['active'])
                        <span class="px-4 py-2 rounded-xl bg-blue-600 text-white text-sm font-bold shadow-md shadow-blue-100 cursor-default">
                            {!! $link['label'] !!}
                        </span>
                    @else
                        <a href="{{ $link['url'] }}"
                        class="px-4 py-2 rounded-xl border border-slate-200 bg-white text-slate-600 hover:border-blue-500 hover:text-blue-600 text-sm font-bold transition shadow-sm">
                            {!! $link['label'] !!}
                        </a>
                    @endif

                @endforeach

                {{-- Next Page Link --}}
                @if ($anggota->hasMorePages())
                    <a href="{{ $anggota->nextPageUrl() }}" class="p-2.5 rounded-xl border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 hover:text-blue-600 transition shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                @else
                    <span class="p-2.5 rounded-xl border border-slate-100 bg-slate-50/50 text-slate-300 cursor-not-allowed">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                        </svg>
                    </span>
                @endif

            </nav>
        </div>
    @endif

    {{-- ═══════════════════════════════════════════ MODAL ══════════════════════════════════════════ --}}
    <div x-show="openModal" class="fixed inset-0 z-[99] overflow-y-auto" x-cloak>
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">

            <div
                @click="openModal = false"
                class="fixed inset-0 transition-opacity bg-slate-900/40 backdrop-blur-sm"
            ></div>

            <div class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-[2.5rem] shadow-2xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full p-8">

                <h3 class="text-xl font-bold text-slate-800 mb-1" x-text="editMode ? 'Edit Data Siswa' : 'Tambah Siswa Baru'"></h3>
                <p class="text-slate-400 text-sm mb-6">Kelas akan otomatis berubah setiap tahun ajaran berdasarkan tahun masuk.</p>

                <form :action="editMode ? `/pembina/anggota/${currentData.id}` : '{{ route('pembina.anggota.store') }}'" method="POST">
                    @csrf

                    <template x-if="editMode">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <div class="space-y-4">

                        {{-- Nama --}}
                        <div>
                            <label class="text-xs font-bold text-slate-400 uppercase ml-1">Nama Lengkap</label>
                            <input
                                type="text"
                                name="name"
                                x-model="currentData.name"
                                class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-0 transition"
                                required
                            >
                        </div>

                        {{-- Email & Password --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-bold text-slate-400 uppercase ml-1">Email</label>
                                <input
                                    type="email"
                                    name="email"
                                    x-model="currentData.email"
                                    class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-0 transition"
                                    required
                                >
                            </div>
                            <div x-data="{ password: '' }">

                                <label class="text-xs font-bold text-slate-400 uppercase ml-1">
                                    Password
                                </label>

                                <input
                                    type="password"
                                    name="password"
                                    x-model="password"
                                    minlength="6"
                                    :placeholder="editMode
                                        ? 'Kosongkan jika tidak diubah'
                                        : 'Minimal 6 karakter'"
                                    :required="!editMode"
                                    :class="{
                                        'border-red-400': password.length > 0 && password.length < 6,
                                        'border-emerald-400': password.length >= 6
                                    }"
                                    class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-0 transition"
                                >

                                <p
                                    x-show="password.length > 0 && password.length < 6"
                                    class="mt-1 text-xs text-red-500"
                                >
                                    Password minimal 6 karakter
                                </p>

                                <p
                                    x-show="password.length >= 6"
                                    class="mt-1 text-xs text-emerald-500"
                                >
                                    ✓ Password sudah valid
                                </p>

                                @error('password')
                                    <p class="mt-1 text-xs text-red-500">
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>
                        </div>

                        {{-- NIS & NISN --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-bold text-slate-400 uppercase ml-1">NIS</label>
                                <input
                                    type="text"
                                    name="nis"
                                    x-model="currentData.nis"
                                    class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-0 transition"
                                    required
                                >
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-400 uppercase ml-1">NISN</label>
                                <input
                                    type="text"
                                    name="nisn"
                                    x-model="currentData.nisn"
                                    class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-0 transition"
                                    required
                                >
                            </div>
                        </div>

                        {{-- Tahun Masuk, Tingkat Awal, Jurusan & Tingkatan --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                            {{-- Tahun Masuk --}}
                            <div class="space-y-1">
                                <label class="text-xs font-bold text-slate-400 uppercase ml-1">
                                    Tahun Masuk
                                </label>

                                <input
                                    type="number"
                                    name="tahun_masuk"
                                    x-model="currentData.tahun_masuk"
                                    min="2000"
                                    max="2100"
                                    placeholder="2025"
                                    class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-0 transition"
                                    required
                                >

                                <p class="text-[11px] text-slate-400 ml-1">
                                    Tahun pertama siswa terdaftar
                                </p>
                            </div>

                            {{-- Kelas Saat Masuk --}}
                            <div class="space-y-1">
                                <label class="text-xs font-bold text-slate-400 uppercase ml-1">
                                    Kelas Saat Masuk
                                </label>

                                <select
                                    name="tingkat_awal"
                                    x-model="currentData.tingkat_awal"
                                    class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-0 transition text-sm text-slate-700"
                                    required
                                >
                                    <option value="">Pilih Kelas</option>
                                    <option value="7">VII</option>
                                    <option value="8">VIII</option>
                                    <option value="9">IX</option>
                                </select>

                                <p class="text-[11px] text-slate-400 ml-1">
                                    Kelas awal saat siswa masuk
                                </p>
                            </div>

                            {{-- Jurusan --}}
                            <div class="space-y-1">
                                <label class="text-xs font-bold text-slate-400 uppercase ml-1">
                                    Kode kelas
                                </label>

                                <input
                                    type="text"
                                    name="jurusan"
                                    x-model="currentData.jurusan"
                                    placeholder="Contoh: 07 / 08 / 09"
                                    class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-0 transition"
                                    required
                                >

                                <p class="text-[11px] text-slate-400 ml-1">
                                    Tidak perlu menulis VII / VIII / IX
                                </p>
                            </div>

                            {{-- Tingkatan --}}
                            <div class="space-y-1">
                                <label class="text-xs font-bold text-slate-400 uppercase ml-1">
                                    Tingkatan
                                </label>

                                <select
                                    name="tingkatan"
                                    x-model="currentData.tingkatan"
                                    class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-0 transition text-sm text-slate-700"
                                    required
                                >
                                    <option value="">Pilih Tingkatan</option>
                                    <option value="balonpas">Balonpas</option>
                                    <option value="instruktur">Instruktur</option>
                                </select>

                                <p class="text-[11px] text-slate-400 ml-1">
                                    Status keanggotaan siswa
                                </p>
                            </div>

                        </div>

                        {{-- Jenis Kelamin --}}
                        <div>
                            <label class="text-xs font-bold text-slate-400 uppercase ml-1">Jenis Kelamin</label>
                            <select
                                name="jenis_kelamin"
                                x-model="currentData.jk"
                                class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-0 transition text-sm text-slate-700"
                                required
                            >
                                <option value="">Pilih Jenis Kelamin</option>
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                        </div>

                    </div>

                    <div class="mt-8 flex gap-3">
                        <button
                            type="button"
                            @click="openModal = false"
                            class="flex-1 px-4 py-3.5 rounded-2xl border border-slate-200 font-bold text-slate-500 hover:bg-slate-50 transition"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            class="flex-1 px-4 py-3.5 rounded-2xl bg-blue-600 text-white font-bold hover:bg-blue-700 shadow-lg shadow-blue-100 transition"
                        >
                            Simpan Data
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let searchTimer;

    function debounceSearch() {

        clearTimeout(searchTimer);

        searchTimer = setTimeout(() => {

            document.getElementById('filterForm').submit();

        }, 500);

    }

    document.addEventListener('DOMContentLoaded', function() {
        
        @if(session('success'))
            Swal.fire({
                title: 'Berhasil!',
                text: "{{ session('success') }}",
                icon: 'success',
                showConfirmButton: false,
                timer: 2500,
                timerProgressBar: true,
                customClass: {
                    popup: 'rounded-[2rem]'
                }
            });
        @endif

        const deleteButtons = document.querySelectorAll('.btn-delete');
        
        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const form = this.closest('.form-delete');
                const namaSiswa = this.getAttribute('data-nama') || 'Siswa ini';

                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: `Data dari ${namaSiswa} beserta seluruh riwayatnya akan dihapus permanen!`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal',
                    customClass: {
                        popup: 'rounded-[2.5rem]',
                        confirmButton: 'rounded-xl font-bold px-5 py-2.5 text-sm',
                        cancelButton: 'rounded-xl font-bold px-5 py-2.5 text-sm'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });

    });
</script>
@endpush