@extends('layouts.app')

@section('content')
<div class="p-6 bg-slate-50 min-h-screen">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800">Manajemen Kehadiran</h1>
    </div>

    {{-- LOGIKA PENGECEKAN JADWAL --}}
    @php
        $hariMap = [
            'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
        ];
        $hariIni = $hariMap[date('l', strtotime($tanggal))];
        $isJadwal = \App\Models\Jadwal::where(
            'ekstrakurikuler_id',
            auth()->user()->pembina->ekstrakurikuler_id
        )
        ->where(function ($q) use ($hariIni, $tanggal) {

            $q->where(function ($rutin) use ($hariIni) {
                $rutin->where('tipe', 'rutin')
                    ->where('hari', $hariIni);
            });

            $q->orWhere(function ($dadakan) use ($tanggal) {
                $dadakan->where('tipe', 'dadakan')
                        ->whereDate('tanggal', $tanggal);
            });

        })
        ->exists();
    @endphp

    <div class="bg-white p-6 rounded-[2rem] border border-slate-200 shadow-sm mb-6">
        <form action="{{ route('pembina.absensi.manage') }}" method="GET"
            class="flex flex-col md:flex-row gap-4 items-end">

            {{-- Tahun Ajaran --}}
            <div class="w-full md:w-56">
                <label class="text-xs font-bold text-slate-400 uppercase ml-1">
                    Tahun Ajaran
                </label>

                <select
                    name="tahun_ajaran"
                    onchange="this.form.submit()"
                    class="w-full mt-1 px-4 py-2.5 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-0 transition text-sm font-bold text-slate-700">
                    <option value="semua">Semua Tahun</option>

                    @foreach($tahunAjaranList as $tahun)
                        <option
                            value="{{ $tahun }}"
                            {{ $selectedTahun == $tahun ? 'selected' : '' }}>
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
                    class="w-full mt-1 px-4 py-2.5 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-0 transition text-sm font-bold text-slate-700">
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

            {{-- Filter Jurusan --}}
            <div class="w-full md:w-56">
                <label class="text-xs font-bold text-slate-400 uppercase ml-1">
                    Jurusan
                </label>

                <select
                    name="jurusan"
                    onchange="this.form.submit()"
                    class="w-full mt-1 px-4 py-2.5 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-0 transition text-sm font-bold text-slate-700">

                    <option value="">Semua Jurusan</option>

                    @foreach($jurusanList as $jurusan)
                        <option
                            value="{{ $jurusan }}"
                            {{ $selectedJurusan == $jurusan ? 'selected' : '' }}>
                            {{ $jurusan }}
                        </option>
                    @endforeach

                </select>
            </div>

            {{-- Tanggal --}}
            <div class="w-full md:w-56">
                <label class="text-xs font-bold text-slate-400 uppercase ml-1">
                    Tanggal
                </label>

                <input
                    type="date"
                    name="tanggal"
                    value="{{ $tanggal }}"
                    onchange="this.form.submit()"
                    class="w-full mt-1 px-4 py-2.5 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-0 transition text-sm">
            </div>

            {{-- Cari Nama --}}
            <div class="w-full md:flex-1 min-w-[220px]">
                <label class="text-xs font-bold text-slate-400 uppercase ml-1">
                    Cari Nama
                </label>

                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Masukkan nama siswa..."
                    oninput="debounceSearch(this)"
                    class="w-full mt-1 px-4 py-2.5 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-0 transition text-sm"
                >
            </div>

            {{-- Reset Filter --}}
            <div class="w-full md:w-auto">
                @if(
                    request('kelas') ||
                    request('jurusan') ||
                        (request('tahun_ajaran') && request('tahun_ajaran') !== 'semua') ||
                        request('tanggal')
                    )
                    <a href="{{ route('pembina.absensi.manage') }}"
                        class="block bg-slate-100 text-slate-600 px-6 py-2.5 rounded-xl font-bold hover:bg-slate-200 transition text-sm text-center whitespace-nowrap">
                        Reset Filter
                    </a>
                @endif
            </div>

        </form>
    </div>

    {{-- ALERT JIKA BUKAN HARI JADWAL --}}
    @if(!$isJadwal)
    <div class="mb-6 p-4 bg-amber-50 border-l-4 border-amber-500 text-amber-700">
        <div class="flex items-center">
            <span class="mr-2 text-lg">⚠️</span>
            <p class="text-sm font-medium">
                Hari <strong>{{ $hariIni }}</strong> bukan jadwal rutin ekskul kamu. 
                Tombol absen dinonaktifkan secara otomatis untuk mencegah manipulasi data.
            </p>
        </div>
    </div>
    @endif

    <div class="bg-white rounded-[2rem] border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1100px] text-sm text-left border-collapse">
                
                {{-- HEADER --}}
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-5 text-center text-xs font-bold uppercase tracking-wider text-slate-500 w-16">
                            No
                        </th>

                        <th class="px-6 py-5 text-xs font-bold uppercase tracking-wider text-slate-500 whitespace-nowrap">
                            NISN
                        </th>

                        <th class="px-6 py-5 text-xs font-bold uppercase tracking-wider text-slate-500 whitespace-nowrap">
                            Nama Siswa
                        </th>

                        <th class="px-6 py-5 text-xs font-bold uppercase tracking-wider text-slate-500 whitespace-nowrap">
                            Kelas
                        </th>

                        <th class="px-6 py-5 text-xs font-bold uppercase tracking-wider text-slate-500 whitespace-nowrap">
                            Angkatan
                        </th>

                        <th class="px-6 py-5 text-xs font-bold uppercase tracking-wider text-slate-500 whitespace-nowrap">
                            Status Kehadiran
                        </th>

                        <th class="px-6 py-5 text-xs font-bold uppercase tracking-wider text-slate-500 whitespace-nowrap">
                            Keterangan
                        </th>
                    </tr>
                </thead>

                {{-- BODY --}}
                <tbody class="divide-y divide-slate-100">

                    @forelse($siswas as $index => $siswa)

                        @php
                            $absen = $siswa->absensis->first();

                            $hariMap = [
                                'Sunday' => 'Minggu',
                                'Monday' => 'Senin',
                                'Tuesday' => 'Selasa',
                                'Wednesday' => 'Rabu',
                                'Thursday' => 'Kamis',
                                'Friday' => 'Jumat',
                                'Saturday' => 'Sabtu'
                            ];

                            $hari = $hariMap[\Carbon\Carbon::parse($tanggal)->format('l')];

                            $ekskulIds = is_array($siswa->ekstrakurikuler_id)
                                ? $siswa->ekstrakurikuler_id
                                : json_decode($siswa->ekstrakurikuler_id, true);

                            $ekskulIds = $ekskulIds ?: [];

                            $isLiburRutin = \App\Models\HariLibur::where('tipe', 'rutin')
                                ->where('hari', $hari)
                                ->whereIn('ekstrakurikuler_id', $ekskulIds)
                                ->exists();

                            $isLiburDadakan = \App\Models\HariLibur::where('tipe', 'dadakan')
                                ->whereDate('tanggal', $tanggal)
                                ->whereIn('ekstrakurikuler_id', $ekskulIds)
                                ->exists();

                            $isLibur = $isLiburRutin || $isLiburDadakan;

                            $adaJadwalRutin = \App\Models\Jadwal::where('tipe', 'rutin')
                                ->where('hari', $hari)
                                ->whereIn('ekstrakurikuler_id', $ekskulIds)
                                ->exists();

                            $adaJadwalDadakan = \App\Models\Jadwal::where('tipe', 'dadakan')
                                ->whereDate('tanggal', $tanggal)
                                ->whereIn('ekstrakurikuler_id', $ekskulIds)
                                ->exists();

                            $adaJadwal = $adaJadwalRutin || $adaJadwalDadakan;

                            if ($absen) {

                                $status = $absen->status;

                            } elseif (
                                !$isLibur &&
                                $adaJadwal &&
                                $tanggal < now()->toDateString()
                            ) {

                                // otomatis alpa
                                $status = 'alpa';

                            } else {

                                $status = 'belum ada';

                            }

                            $statusColor = match($status) {
                                'hadir' => 'bg-emerald-100 text-emerald-700',
                                'sakit' => 'bg-amber-100 text-amber-700',
                                'izin'  => 'bg-blue-100 text-blue-700',
                                'alpa'  => 'bg-red-100 text-red-700',
                                default => 'bg-slate-100 text-slate-500',
                            };
                        @endphp

                        <tr class="hover:bg-slate-50/70 transition">

                            {{-- NO --}}
                            <td class="px-6 py-5 text-center font-medium text-slate-500">
                                {{ ($siswas->currentPage() - 1) * $siswas->perPage() + $index + 1 }}
                            </td>

                            {{-- NISN --}}
                            <td class="px-6 py-5 text-slate-600 font-medium whitespace-nowrap">
                                {{ $siswa->nisn }}
                            </td>

                            {{-- NAMA --}}
                            <td class="px-6 py-5">
                                <div class="flex items-center gap-3">

                                    <div class="h-11 w-11 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center font-bold shrink-0">
                                        {{ strtoupper(substr($siswa->user?->name ?? 'S', 0, 1)) }}
                                    </div>

                                    <div>
                                        <p class="font-bold text-slate-800 uppercase leading-tight">
                                            {{ $siswa->user?->name ?? '-' }}
                                        </p>

                                        <p class="text-xs text-slate-400">
                                            {{ $siswa->user?->email ?? '-' }}
                                        </p>
                                    </div>

                                </div>
                            </td>

                            {{-- KELAS --}}
                            <td class="px-6 py-5">
                                @php
                                    $kelasColor = match ($siswa->tingkat_display) {
                                        7 => 'bg-blue-50 text-blue-600',
                                        8 => 'bg-emerald-50 text-emerald-600',
                                        9 => 'bg-purple-50 text-purple-600',
                                        default => 'bg-slate-100 text-slate-600',
                                    };
                                @endphp

                                <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-bold {{ $kelasColor }}">
                                    {{ $siswa->kelas_display }}
                                </span>
                            </td>

                            {{-- ANGKATAN --}}
                            <td class="px-6 py-5">
                                @if($siswa->tahun_masuk)
                                    <span class="inline-flex px-3 py-1 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold">
                                        {{ $siswa->tahun_masuk }}
                                    </span>
                                @else
                                    <span class="text-slate-400 italic">
                                        -
                                    </span>
                                @endif
                            </td>
                            

                            {{-- SELECT STATUS --}}
                            <td class="px-6 py-5">
                                <form action="{{ route('pembina.absensi.update') }}" method="POST">
                                    @csrf

                                    <input type="hidden" name="siswa_id" value="{{ $siswa->id }}">
                                    <input type="hidden" name="tanggal" value="{{ $tanggal }}">

                                    <select
                                        name="status"
                                        onchange="this.form.submit()"
                                        @if($status == 'hadir' || !$isJadwal) disabled @endif
                                        class="w-44 rounded-xl border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700 focus:border-blue-500 focus:ring-blue-500
                                        {{ ($status == 'hadir' || !$isJadwal)
                                            ? 'bg-slate-100 text-slate-400 cursor-not-allowed'
                                            : 'bg-white'
                                        }}"
                                    >
                                        <option value="" disabled {{ $status == 'belum ada' ? 'selected' : '' }}>
                                            -- Pilih Status --
                                        </option>

                                        <option value="hadir" {{ $status == 'hadir' ? 'selected' : '' }}>
                                            Hadir
                                        </option>

                                        <option value="sakit" {{ $status == 'sakit' ? 'selected' : '' }}>
                                            Sakit
                                        </option>

                                        <option value="izin" {{ $status == 'izin' ? 'selected' : '' }}>
                                            Izin
                                        </option>

                                        <option value="alpa" {{ $status == 'alpa' ? 'selected' : '' }}>
                                            Alpa
                                        </option>
                                    </select>
                                </form>
                            </td>

                            {{-- BADGE STATUS --}}
                            <td class="px-6 py-5">
                                <div class="flex items-center">
                                    <span class="inline-flex items-center justify-center min-w-[110px] px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wide {{ $statusColor }}">
                                        {{ $status == 'belum ada' ? 'Belum Ada' : $status }}
                                    </span>
                                </div>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="7" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center gap-2 text-slate-400">

                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="h-12 w-12 opacity-40"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="1.5"
                                            d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 13V7a2 2 0 012-2h10a2 2 0 012 2v6" />
                                    </svg>

                                    <p class="text-sm italic">
                                        Tidak ada data siswa ditemukan.
                                    </p>

                                </div>
                            </td>
                        </tr>

                    @endforelse

                </tbody>
            </table>
        </div>
        {{-- Pagination --}}
        @if($siswas->hasPages())
            <div class="bg-white px-6 py-5 rounded-[2rem] border border-slate-200 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4 mt-6">
                
                {{-- Info Text --}}
                <div class="text-sm text-slate-500 text-center sm:text-left">
                    Menampilkan 
                    <span class="font-bold text-slate-800">{{ $siswas->firstItem() }}</span> 
                    sampai 
                    <span class="font-bold text-slate-800">{{ $siswas->lastItem() }}</span> 
                    dari 
                    <span class="font-bold text-slate-800">{{ $siswas->total() }}</span> siswa
                </div>

                {{-- Pagination Buttons --}}
                <nav class="inline-flex flex-wrap items-center gap-1.5 justify-center" aria-label="Pagination">
                    
                    {{-- Previous Page Link --}}
                    @if ($siswas->onFirstPage())
                        <span class="p-2.5 rounded-xl border border-slate-100 bg-slate-50/50 text-slate-300 cursor-not-allowed">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                            </svg>
                        </span>
                    @else
                        <a href="{{ $siswas->previousPageUrl() }}" class="p-2.5 rounded-xl border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 hover:text-blue-600 transition shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                            </svg>
                        </a>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($siswas->linkCollection() as $link)

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
                    @if ($siswas->hasMorePages())
                        <a href="{{ $siswas->nextPageUrl() }}" class="p-2.5 rounded-xl border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 hover:text-blue-600 transition shadow-sm">
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
    </div>
</div>
@endsection

@push('scripts')
<script>
    let searchTimer;

    function debounceSearch(element) {

        clearTimeout(searchTimer);

        searchTimer = setTimeout(() => {

            element.form.submit();

        }, 500);

    }
</script>
@endpush