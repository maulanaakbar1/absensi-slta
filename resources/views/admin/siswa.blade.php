@extends('layouts.app')
@section('title', 'Manajemen Siswa')

@section('content')

    <div x-data="{
        openModal: false,
        editMode: false,
        search: '',
    
        currentData: {
            id: '',
            name: '',
            email: '',
            nis: '',
            nisn: '',
            tahun_masuk: '',
            tingkat_awal: '',
            jurusan: '',
            jk: 'L',
            ekskul: [],
            tingkatan: '',
        }
    }" class="space-y-6">

        {{-- HEADER --}}
        <div class="flex flex-col gap-4 mb-8">

            <div>
                <h3 class="text-2xl font-bold text-slate-800">
                    Data Siswa Seluruh Ekskul
                </h3>

                <p class="text-slate-500 text-sm">
                    Kelola seluruh data siswa.
                </p>
            </div>

            <div class="flex justify-start md:justify-end">

                <button
                    @click="
                        openModal = true;
                        editMode = false;

                        currentData = {
                            id: '',
                            name: '',
                            email: '',
                            nis: '',
                            nisn: '',
                            tahun_masuk: '',
                            tingkat_awal: '',
                            jurusan: '',
                            jk: 'L',
                            ekskul: [],
                            tingkatan: '',
                        }
                    "
                    class="bg-blue-600 text-white px-4 py-2 rounded-xl text-sm font-bold shadow-md shadow-blue-100 hover:bg-blue-700 transition flex items-center gap-2 w-fit">

                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">

                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4" />
                    </svg>

                    Tambah Siswa
                </button>

            </div>
        </div>

        {{-- FILTER --}}
        <div class="bg-white p-6 rounded-[2rem] border border-slate-200 shadow-sm">

            <form method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">

                {{-- Tahun Ajaran --}}
                <div class="md:col-span-3">
                    <label class="text-xs font-bold text-slate-400 uppercase ml-1">
                        Tahun Ajaran
                    </label>

                    <select name="tahun_ajaran" onchange="this.form.submit()"
                        class="w-full mt-1 px-4 py-2.5 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-0 transition text-sm font-bold text-slate-700">
                        <option value="semua">Semua Tahun Ajaran</option>

                        @foreach ($tahunAjaranList as $tahun)
                            <option value="{{ $tahun }}" {{ $selectedTahun == $tahun ? 'selected' : '' }}>
                                {{ $tahun }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Kelas --}}
                <div class="md:col-span-2">
                    <label class="text-xs font-bold text-slate-400 uppercase ml-1">
                        Kelas
                    </label>

                    <select name="kelas" onchange="this.form.submit()"
                        class="w-full mt-1 px-4 py-2.5 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-0 transition text-sm font-bold text-slate-700">
                        <option value="">Semua Kelas</option>
                        <option value="7" {{ $selectedKelas == '7' ? 'selected' : '' }}>VII</option>
                        <option value="8" {{ $selectedKelas == '8' ? 'selected' : '' }}>VIII</option>
                        <option value="9" {{ $selectedKelas == '9' ? 'selected' : '' }}>IX</option>
                    </select>
                </div>

                {{-- Ekskul --}}
                <div class="md:col-span-2">
                    <label class="text-xs font-bold text-slate-400 uppercase ml-1">
                        Ekskul
                    </label>

                    <select
                        name="ekskul"
                        onchange="this.form.submit()"
                        class="w-full mt-1 px-4 py-2.5 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-0 transition text-sm font-bold text-slate-700">

                        <option value="all">Semua Ekskul</option>

                        @foreach($ekskul as $e)
                            <option
                                value="{{ $e->id }}"
                                {{ ($selectedEkskul ?? 'all') == $e->id ? 'selected' : '' }}>
                                {{ $e->nama }}
                            </option>
                        @endforeach

                    </select>
                </div>

                {{-- Jurusan --}}
                <div class="md:col-span-3">
                    <label class="text-xs font-bold text-slate-400 uppercase ml-1">
                        Kode Kelas
                    </label>

                    <select name="jurusan" onchange="this.form.submit()"
                        class="w-full mt-1 px-4 py-2.5 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-0 transition text-sm font-bold text-slate-700">
                        <option value="">Semua Kode Kelas</option>

                        @foreach ($anggota->pluck('jurusan')->unique()->filter() as $jur)
                            <option value="{{ $jur }}" {{ $selectedJurusan == $jur ? 'selected' : '' }}>
                                {{ $jur }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Search --}}
                <div class="md:col-span-3">
                    <label class="text-xs font-bold text-slate-400 uppercase ml-1">
                        Cari Nama
                    </label>

                    <div class="relative mt-1">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </span>

                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Masukkan nama siswa..."
                            oninput="clearTimeout(this.delay); this.delay = setTimeout(() => this.form.submit(), 500)"
                            class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-0 transition text-sm">
                    </div>
                </div>

                {{-- Reset --}}
                <div class="md:col-span-1 flex justify-end">

                    @if (
                            request('search') ||
                            request('kelas') ||
                            request('jurusan') ||
                            request('ekskul') ||
                            (request('tahun_ajaran') && request('tahun_ajaran') !== 'semua')
                        )
                        <a href="{{ route('admin.siswa.index') }}"
                            class="w-full md:w-auto bg-slate-100 text-slate-600 px-4 py-2.5 rounded-xl font-bold hover:bg-slate-200 transition text-sm text-center whitespace-nowrap">
                            Reset
                        </a>
                    @endif

                </div>

            </form>
        </div>

        {{-- Info Tahun Ajaran + Export Import --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 px-1">

            {{-- INFO --}}
            <div class="flex items-center gap-2 text-sm text-slate-500">

                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-500 flex-shrink-0" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>

                <span class="leading-relaxed">

                    @if ($selectedTahun === 'semua')
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
                <form action="{{ route('admin.siswa.import') }}" method="POST" enctype="multipart/form-data"
                    class="flex items-center gap-2">
                    @csrf

                    <input type="file" name="file" id="importFile" required class="hidden"
                        onchange="this.form.submit()">

                    <label for="importFile"
                        class="cursor-pointer bg-indigo-600 text-white px-3 py-1.5 rounded-lg text-xs font-bold hover:bg-indigo-700 transition flex items-center gap-1">

                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">

                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4-4m0 0l-4 4m4-4v12" />

                        </svg>

                        Import
                    </label>
                </form>

                {{-- EXPORT --}}
                <a href="{{ route('admin.siswa.export', request()->query()) }}"
                    class="bg-emerald-600 text-white px-3 py-1.5 rounded-lg text-xs font-bold hover:bg-emerald-700 transition flex items-center gap-1">

                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4v12m0 0l-4-4m4 4l4-4m6 4v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2" />
                    </svg>

                    Export
                </a>

            </div>

        </div>

        {{-- TABLE --}}
        <div class="bg-white rounded-[2.5rem] border border-slate-200 shadow-sm overflow-hidden">

            <div class="overflow-x-auto">

                <table class="w-full text-left border-collapse">

                    <thead>
                        <tr class="bg-slate-50/70 border-b border-slate-100">
                            <th
                                class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider whitespace-nowrap">
                                Siswa</th>
                            <th
                                class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider whitespace-nowrap">
                                NISN</th>
                            <th
                                class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider whitespace-nowrap">
                                Kelas
                                @if ($selectedTahun !== 'semua')
                                    <span class="text-blue-500 normal-case font-medium">({{ $selectedTahun }})</span>
                                @endif
                            </th>
                            <th
                                class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider whitespace-nowrap">
                                Tahun Ajaran</th>
                            <th
                                class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider whitespace-nowrap">
                                Angkatan</th>
                            <th
                                class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider whitespace-nowrap">
                                Ekstrakurikuler</th>
                            <th
                                class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider whitespace-nowrap">
                                Jenis Kelamin</th>
                            <th
                                class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider whitespace-nowrap">
                                Tingkatan</th>
                            <th
                                class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider whitespace-nowrap text-center w-28">
                                Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">

                        @forelse($anggota as $s)
                            @php
                                $tingkat = $s->tingkat_display;

                                $tingkatColor = match (true) {
                                    $s->kelas_display === 'Lulus' => 'text-slate-600 bg-slate-200',
                                    $tingkat === 7 => 'text-blue-600 bg-blue-50',
                                    $tingkat === 8 => 'text-emerald-600 bg-emerald-50',
                                    $tingkat === 9 => 'text-purple-600 bg-purple-50',
                                    default => 'text-slate-600 bg-slate-50',
                                };
                            @endphp

                            <tr class="hover:bg-slate-50/40 transition">

                                {{-- SISWA --}}
                                <td class="px-6 py-4 whitespace-nowrap">

                                    <div class="flex items-center gap-3">

                                        <div
                                            class="h-10 w-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center font-bold shrink-0">

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

                                {{-- NISN --}}
                                <td class="px-6 py-4 text-sm font-semibold text-slate-600 whitespace-nowrap">
                                    {{ $s->nisn }}
                                </td>

                                {{-- KELAS --}}
                                <td class="px-6 py-4 whitespace-nowrap">

                                    <span
                                        class="px-2.5 py-1 rounded-lg text-xs font-bold uppercase tracking-wide inline-block {{ $tingkatColor }}">

                                        {{ $s->kelas_display }}

                                    </span>

                                </td>

                                <td class="px-6 py-4 text-sm whitespace-nowrap">
                                    @if ($selectedTahun === 'semua')
                                        <span
                                            class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-600 text-xs font-semibold">
                                            Semua
                                        </span>
                                    @else
                                        <span
                                            class="px-2.5 py-1 rounded-lg bg-blue-50 text-blue-600 text-xs font-semibold">
                                            {{ $selectedTahun }}
                                        </span>
                                    @endif
                                </td>

                                {{-- ANGKATAN --}}
                                <td class="px-6 py-4 text-sm text-slate-600 whitespace-nowrap font-medium">

                                    @if ($s->tahun_masuk)
                                        {{ $s->tahun_masuk }}/{{ $s->tahun_masuk + 1 }}
                                    @else
                                        -
                                    @endif

                                </td>

                                {{-- EKSKUL --}}
                                <td class="px-6 py-4 whitespace-nowrap">

                                    <span class="px-3 py-1 rounded-lg bg-indigo-50 text-indigo-600 text-xs font-bold">

                                        {{ $s->ekskul_nama ?? 'Belum Pilih' }}

                                    </span>

                                </td>

                                {{-- JENIS KELAMIN --}}
                                <td class="px-6 py-4 whitespace-nowrap">

                                    <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-600 text-xs font-semibold">

                                        {{ $s->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}

                                    </span>

                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $tingkatanColor = match ($s->tingkatan) {
                                            'balonpas' => 'bg-blue-50 text-blue-600',
                                            'instruktur' => 'bg-emerald-50 text-emerald-600',
                                            default => 'bg-slate-50 text-slate-500',
                                        };
                                    @endphp

                                    <span
                                        class="px-2.5 py-1 rounded-lg text-xs font-bold capitalize {{ $tingkatanColor }}">
                                        {{ $s->tingkatan }}
                                    </span>
                                </td>

                                {{-- AKSI --}}
                                <td class="px-6 py-4">

                                    <div class="flex justify-center gap-2">

                                        {{-- SHOW --}}
                                        <a href="{{ route('admin.siswa.show', $s->id) }}"
                                            class="p-2 text-blue-500 hover:bg-blue-50 rounded-xl transition"
                                            title="Detail siswa">

                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">

                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />

                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />

                                            </svg>

                                        </a>

                                        {{-- EDIT --}}
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
                                                    ekskul: @js(is_array($s->ekstrakurikuler_id) ? $s->ekstrakurikuler_id : json_decode($s->ekstrakurikuler_id, true) ?? []),
                                                    tingkatan: @js($s->tingkatan),
                                                }
                                            "
                                            class="p-2 text-amber-500 hover:bg-amber-50 rounded-xl transition">

                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">

                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />

                                            </svg>

                                        </button>

                                        {{-- DELETE --}}
                                        <form action="{{ route('admin.siswa.destroy', $s->id) }}" method="POST"
                                            class="form-delete">

                                            @csrf
                                            @method('DELETE')

                                            <button type="button"
                                                class="btn-delete p-2 text-red-500 hover:bg-red-50 rounded-xl transition"
                                                data-nama="{{ $s->user?->name }}" data-id="{{ $s->id }}">

                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">

                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />

                                                </svg>

                                            </button>

                                        </form>

                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="7" class="px-6 py-16 text-center">

                                    <p class="text-slate-400 italic">
                                        Belum ada data siswa.
                                    </p>

                                </td>

                            </tr>
                        @endforelse

                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination Elemen Kustom --}}
        @if ($anggota->hasPages())
            <div
                class="mt-6 bg-white px-6 py-5 rounded-[2rem] border border-slate-200 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">

                {{-- Teks Informasi Data --}}
                <div class="text-sm text-slate-500 text-center sm:text-left">
                    Menampilkan
                    <span class="font-bold text-slate-800">{{ $anggota->firstItem() }}</span>
                    sampai
                    <span class="font-bold text-slate-800">{{ $anggota->lastItem() }}</span>
                    dari
                    <span class="font-bold text-slate-800">{{ $anggota->total() }}</span> siswa
                </div>

                {{-- Tombol Navigasi Angka --}}
                <nav class="inline-flex flex-wrap items-center gap-1.5 justify-center" aria-label="Pagination">

                    {{-- Tombol Halaman Sebelumnya --}}
                    @if ($anggota->onFirstPage())
                        <span
                            class="p-2.5 rounded-xl border border-slate-100 bg-slate-50/50 text-slate-300 cursor-not-allowed">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M15 19l-7-7 7-7" />
                            </svg>
                        </span>
                    @else
                        <a href="{{ $anggota->previousPageUrl() }}"
                            class="p-2.5 rounded-xl border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 hover:text-blue-600 transition shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M15 19l-7-7 7-7" />
                            </svg>
                        </a>
                    @endif

                    {{-- Render Angka Halaman --}}
                    @foreach ($anggota->links()->elements as $element)
                        {{-- Separator Titik Tiga (...) Jika Halaman Terlalu Banyak --}}
                        @if (is_string($element))
                            <span class="px-3 py-1.5 text-sm font-bold text-slate-400 cursor-default">
                                {{ $element }}
                            </span>
                        @endif

                        {{-- Link Angka --}}
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $anggota->currentPage())
                                    <span
                                        class="px-4 py-2 rounded-xl bg-blue-600 text-white text-sm font-bold shadow-md shadow-blue-100 cursor-default">
                                        {{ $page }}
                                    </span>
                                @else
                                    <a href="{{ $url }}"
                                        class="px-4 py-2 rounded-xl border border-slate-200 bg-white text-slate-600 hover:border-blue-500 hover:text-blue-600 text-sm font-bold transition shadow-sm">
                                        {{ $page }}
                                    </a>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Tombol Halaman Selanjutnya --}}
                    @if ($anggota->hasMorePages())
                        <a href="{{ $anggota->nextPageUrl() }}"
                            class="p-2.5 rounded-xl border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 hover:text-blue-600 transition shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    @else
                        <span
                            class="p-2.5 rounded-xl border border-slate-100 bg-slate-50/50 text-slate-300 cursor-not-allowed">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M9 5l7 7-7 7" />
                            </svg>
                        </span>
                    @endif

                </nav>
            </div>
        @endif

        {{-- ═══════════════════════════════════════════ MODAL ══════════════════════════════════════════ --}}
        <div x-show="openModal" class="fixed inset-0 z-[99] overflow-y-auto" x-cloak>
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">

                <div @click="openModal = false" class="fixed inset-0 transition-opacity bg-slate-900/40 backdrop-blur-sm">
                </div>

                <div
                    class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-[2.5rem] shadow-2xl sm:my-8 sm:align-middle sm:max-w-xl sm:w-full p-8">

                    <h3 class="text-xl font-bold text-slate-800 mb-1"
                        x-text="editMode ? 'Edit Data Siswa' : 'Tambah Siswa Baru'"></h3>

                    <p class="text-slate-400 text-sm mb-6">
                        Kelas siswa akan otomatis berubah sesuai tahun ajaran.
                    </p>

                    <form
                        :action="editMode
                            ?
                            `/admin/siswa/${currentData.id}` :
                            '{{ route('admin.siswa.store') }}'"
                        method="POST">
                        @csrf

                        <template x-if="editMode">
                            <input type="hidden" name="_method" value="PUT">
                        </template>

                        <div class="space-y-4">

                            {{-- Nama --}}
                            <div>
                                <label class="text-xs font-bold text-slate-400 uppercase ml-1">
                                    Nama Lengkap
                                </label>

                                <input type="text" name="name" x-model="currentData.name"
                                    class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-0 transition"
                                    required>
                            </div>

                            {{-- Email & Password --}}
                            <div class="grid grid-cols-2 gap-4">

                                <div>
                                    <label class="text-xs font-bold text-slate-400 uppercase ml-1">
                                        Email
                                    </label>

                                    <input type="email" name="email" x-model="currentData.email"
                                        class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-0 transition"
                                        required>
                                </div>

                                <div x-data="{ password: '' }">

                                    <label class="text-xs font-bold text-slate-400 uppercase ml-1">
                                        Password
                                    </label>

                                    <input type="password" name="password" x-model="password" minlength="6"
                                        :placeholder="editMode
                                            ?
                                            'Kosongkan jika tidak diubah' :
                                            'Minimal 6 karakter'"
                                        :required="!editMode"
                                        :class="{
                                            'border-red-400': password.length > 0 && password.length < 6,
                                            'border-emerald-400': password.length >= 6
                                        }"
                                        class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-0 transition">

                                    <p x-show="password.length > 0 && password.length < 6"
                                        class="mt-1 text-xs text-red-500">
                                        Password minimal 6 karakter
                                    </p>

                                    <p x-show="password.length >= 6" class="mt-1 text-xs text-emerald-500">
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
                                    <label class="text-xs font-bold text-slate-400 uppercase ml-1">
                                        NIS
                                    </label>

                                    <input type="text" name="nis" x-model="currentData.nis"
                                        class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-0 transition"
                                        required>
                                </div>

                                <div>
                                    <label class="text-xs font-bold text-slate-400 uppercase ml-1">
                                        NISN
                                    </label>

                                    <input type="text" name="nisn" x-model="currentData.nisn"
                                        class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-0 transition"
                                        required>
                                </div>

                            </div>

                            {{-- Tahun Masuk, Tingkat, Jurusan --}}
                            <div class="grid grid-cols-3 gap-4">

                                {{-- Tahun Masuk --}}
                                <div>
                                    <label class="text-xs font-bold text-slate-400 uppercase ml-1">
                                        Tahun Masuk
                                    </label>

                                    <input type="number" name="tahun_masuk" x-model="currentData.tahun_masuk"
                                        min="2000" max="2100" placeholder="2025"
                                        class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-0 transition"
                                        required>

                                    <p class="text-[10px] text-slate-400 mt-1 ml-1">
                                        Tahun pertama siswa masuk
                                    </p>
                                </div>

                                {{-- Tingkat Awal --}}
                                <div>
                                    <label class="text-xs font-bold text-slate-400 uppercase ml-1">
                                        Kelas Saat Masuk
                                    </label>

                                    <select name="tingkat_awal" x-model="currentData.tingkat_awal"
                                        class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-0 transition"
                                        required>
                                        <option value="">Pilih Tingkat</option>
                                            <option value="7">VII</option>
                                            <option value="8">VIII</option>
                                            <option value="9">IX</option>
                                    </select>

                                    <p class="text-[10px] text-slate-400 mt-1 ml-1">
                                        Tingkat awal saat masuk sekolah
                                    </p>
                                </div>

                                {{-- Jurusan --}}
                                <div>
                                    <label class="text-xs font-bold text-slate-400 uppercase ml-1">
                                        Kode Kelas
                                    </label>

                                    <input type="text" name="jurusan" x-model="currentData.jurusan"
                                        placeholder="Contoh: 07 / 08"
                                        class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-0 transition"
                                        required>

                                    <p class="text-[10px] text-slate-400 mt-1 ml-1">
                                        Tanpa 7 / 8 / 9
                                    </p>
                                </div>

                            </div>

                            {{-- Tingkatan & Ekskul --}}
                            <div class="grid grid-cols-2 gap-4">

                                {{-- Tingkatan --}}
                                <div>
                                    <label class="text-xs font-bold text-slate-400 uppercase ml-1">
                                        Tingkatan
                                    </label>

                                    <select name="tingkatan" x-model="currentData.tingkatan"
                                        class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-0 transition"
                                        required>
                                        <option value="" disabled>Pilih Tingkatan</option>

                                        <option value="balonpas">
                                            Balonpas
                                        </option>

                                        <option value="instruktur">
                                            Instruktur
                                        </option>
                                    </select>
                                </div>

                                {{-- Ekskul --}}
                                <div>
                                    <label class="text-xs font-bold text-slate-400 uppercase ml-1">
                                        Ekstrakurikuler
                                    </label>

                                        <div class="grid grid-cols-2 gap-2 mt-2">
                                            @foreach ($ekskul as $e)
                                                <label
                                                    class="flex items-center gap-2 p-2 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer">

                                                    <input type="checkbox" name="ekstrakurikuler_id[]" value="{{ $e->id }}"
                                                        x-model="currentData.ekskul"
                                                        class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">

                                                    <span class="text-sm text-slate-700">
                                                        {{ $e->nama }}
                                                    </span>

                                                </label>
                                            @endforeach
                                        </div>
                                </div>

                            </div>

                            {{-- Jenis Kelamin --}}
                            <div>
                                <label class="text-xs font-bold text-slate-400 uppercase ml-1">
                                    Jenis Kelamin
                                </label>

                                <select
                                    name="jenis_kelamin"
                                    x-model="currentData.jk"
                                    class="w-full rounded-xl border border-slate-300 px-4 py-3"
                                    required>

                                    <option value="" disabled>Pilih Jenis Kelamin</option>
                                    <option value="L">Laki-laki</option>
                                    <option value="P">Perempuan</option>

                                </select>
                            </div>

                        </div>

                        {{-- BUTTON --}}
                        <div class="mt-8 flex gap-3">

                            <button type="button" @click="openModal = false"
                                class="flex-1 px-4 py-3.5 rounded-2xl border border-slate-200 font-bold text-slate-500 hover:bg-slate-50 transition">
                                Batal
                            </button>

                            <button type="submit"
                                class="flex-1 px-4 py-3.5 rounded-2xl bg-blue-600 text-white font-bold hover:bg-blue-700 shadow-lg shadow-blue-100 transition">
                                Simpan Data
                            </button>

                        </div>

                    </form>

                </div>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // =========================
            // SUCCESS ALERT
            // =========================
            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: @json(session('success')),
                    showConfirmButton: false,
                    timer: 2000,
                    customClass: {
                        popup: 'rounded-[1.5rem]',
                    }
                });
            @endif


            // =========================
            // DELETE CONFIRM
            // =========================
            const deleteButtons = document.querySelectorAll('.btn-delete');

            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {

                    const form = this.closest('.form-delete');
                    const nama = this.getAttribute('data-nama');

                    Swal.fire({
                        title: 'Hapus Siswa?',
                        text: `Data "${nama}" akan dihapus permanen!`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#ef4444',
                        cancelButtonColor: '#64748b',
                        confirmButtonText: 'Ya, Hapus',
                        cancelButtonText: 'Batal',
                        reverseButtons: true,
                        customClass: {
                            popup: 'rounded-[1.5rem]',
                            confirmButton: 'rounded-xl px-4 py-2 font-bold',
                            cancelButton: 'rounded-xl px-4 py-2 font-bold'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();

                            Swal.fire({
                                icon: 'success',
                                title: 'Terhapus!',
                                text: 'Data siswa berhasil dihapus.',
                                timer: 1500,
                                showConfirmButton: false
                            });
                        }
                    });

                });
            });

        });
    </script>

@endsection
