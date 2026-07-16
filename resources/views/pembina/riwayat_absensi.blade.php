@extends('layouts.app')

@section('title_page', 'Riwayat Absensi Anggota')

@section('content')
<div class="max-w-6xl mx-auto py-8 px-4">
    <div class="bg-white rounded-3xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="p-6 border-b border-slate-100 bg-slate-50/50">

            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">

                {{-- TITLE --}}
                <div>
                    <h3 class="font-bold text-slate-800 text-lg">
                        Riwayat Absensi Anggota
                    </h3>

                    <p class="text-xs text-slate-500 mt-1">
                        Memantau seluruh kehadiran siswa di ekstrakurikuler Anda
                    </p>
                </div>

                {{-- BUTTON --}}
                <a href="{{ route('pembina.rekap.index') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 rounded-xl text-sm font-bold text-slate-600 hover:bg-slate-50 transition">

                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="h-4 w-4"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor">

                        <path stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>

                    Lihat Rekap Bulanan
                </a>

            </div>

            {{-- FILTER --}}
            <form 
                id="filterForm"
                method="GET"
                action="{{ route('pembina.riwayat.index') }}"
                class="mt-5 flex flex-col md:flex-row gap-4">

                {{-- TAHUN AJARAN --}}
                <div class="w-full md:w-56">

                    <label class="text-xs font-bold text-slate-400 uppercase ml-1">
                        Tahun Ajaran
                    </label>

                    <select
                        name="tahun_ajaran"
                        onchange="this.form.submit()"
                        class="w-full mt-1 px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm font-semibold text-slate-700 focus:border-blue-500 focus:ring-0">

                        <option value="semua">
                            Semua Tahun
                        </option>

                        @foreach($tahunAjaranList as $tahun)

                            <option
                                value="{{ $tahun }}"
                                {{ $selectedTahun == $tahun ? 'selected' : '' }}>

                                {{ $tahun }}

                            </option>

                        @endforeach

                    </select>

                </div>

                {{-- KELAS --}}
                <div class="w-full md:w-44">

                    <label class="text-xs font-bold text-slate-400 uppercase ml-1">
                        Kelas
                    </label>

                    <select
                        name="kelas"
                        onchange="this.form.submit()"
                        class="w-full mt-1 px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm font-semibold text-slate-700 focus:border-blue-500 focus:ring-0">

                        <option value="">
                            Semua Kelas
                        </option>

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
                <div class="w-full md:w-56">

                    <label class="text-xs font-bold text-slate-400 uppercase ml-1">
                        Kode Kelas
                    </label>

                    <select
                        name="jurusan"
                        onchange="this.form.submit()"
                        class="w-full mt-1 px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm font-semibold text-slate-700 focus:border-blue-500 focus:ring-0">

                        <option value="">Semua Kode Kelas</option>

                        @foreach($jurusanList as $jurusan)
                            <option value="{{ $jurusan }}" 
                                {{ $selectedJurusan == $jurusan ? 'selected' : '' }}>
                                {{ $jurusan }}
                            </option>
                        @endforeach

                    </select>
                </div>

                {{-- TANGGAL --}}
                <div class="w-full md:w-52">

                    <label class="text-xs font-bold text-slate-400 uppercase ml-1">
                        Tanggal
                    </label>

                    <input
                        type="date"
                        name="tanggal"
                        value="{{ request('tanggal') }}"
                        onchange="this.form.submit()"
                        class="w-full mt-1 px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm text-slate-700 focus:border-blue-500 focus:ring-0"
                    >

                </div>

                {{-- SEARCH NAMA --}}
                <div class="w-full md:w-64">

                    <label class="text-xs font-bold text-slate-400 uppercase ml-1">
                        Cari Nama
                    </label>

                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Cari..."
                        oninput="debounceSearch()"
                        class="w-full mt-1 px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm text-slate-700 focus:border-blue-500 focus:ring-0"
                    >

                </div>

                {{-- RESET --}}
                @if(
                    request('kelas') ||
                    request('jurusan') ||
                    request('search') ||
                    request('tanggal') ||
                    (request('tahun_ajaran') && request('tahun_ajaran') !== 'semua')
                )

                <div class="flex items-end">

                    <a href="{{ route('pembina.riwayat.index') }}"
                        class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-bold transition">

                        Reset

                    </a>

                </div>

                @endif

            </form>

        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[950px] text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/70">

                        <th class="px-4 py-4 text-xs font-bold text-slate-400 uppercase border-b text-center w-14">
                            No
                        </th>

                        <th class="px-5 py-4 text-xs font-bold text-slate-400 uppercase border-b min-w-[220px]">
                            Nama
                        </th>

                        <th class="px-5 py-4 text-xs font-bold text-slate-400 uppercase border-b text-center w-[150px]">
                            Kelas
                        </th>

                        <th class="px-5 py-4 text-xs font-bold text-slate-400 uppercase border-b text-center w-[150px]">
                            Tanggal
                        </th>

                        <th class="px-5 py-4 text-xs font-bold text-slate-400 uppercase border-b text-center w-[110px]">
                            Jam
                        </th>

                        <th class="px-5 py-4 text-xs font-bold text-slate-400 uppercase border-b text-center w-[120px]">
                            Status
                        </th>

                        <th class="px-5 py-4 text-xs font-bold text-slate-400 uppercase border-b text-center w-[140px]">
                            Foto
                        </th>

                        <th class="px-5 py-4 text-xs font-bold text-slate-400 uppercase border-b text-center w-[100px]">
                            Lokasi
                        </th>

                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                @forelse($riwayat as $index => $row)
                <tr class="hover:bg-slate-50/50 transition">

                    {{-- NO --}}
                    <td class="px-6 py-4 text-sm text-slate-400 text-center font-medium">
                        {{ $riwayat->firstItem() + $index }}
                    </td>

                    {{-- NAMA --}}
                    <td class="px-6 py-4 text-sm font-bold text-slate-700">
                        {{ $row->siswa->user->name }}
                    </td>

                    {{-- KELAS (DIPISAH) --}}
                    <td class="px-5 py-4 text-center">

                        @php
                            $kelasColor = match ($row->siswa->tingkat_display) {
                                7 => 'bg-blue-50 text-blue-600',
                                8 => 'bg-emerald-50 text-emerald-600',
                                9 => 'bg-purple-50 text-purple-600',
                                default => 'bg-slate-100 text-slate-600',
                                };
                            @endphp

                        <span class="inline-flex items-center justify-center min-w-[90px] px-3 py-1.5 rounded-xl {{ $kelasColor }}">
                            {{ $row->siswa->kelas_display ?? '-' }}
                        </span>
                    </td>

                    {{-- TANGGAL --}}
                    <td class="px-5 py-4 text-sm text-slate-600 text-center whitespace-nowrap font-medium">
                        {{ \Carbon\Carbon::parse($row->tanggal)->translatedFormat('d M Y') }}
                    </td>

                    {{-- JAM --}}
                    <td class="px-5 py-4 text-sm text-slate-500 text-center whitespace-nowrap font-semibold">
                        {{ $row->jam_masuk ?? '--:--' }}
                    </td>

                    {{-- STATUS --}}
                    <td class="px-6 py-4">
                        @php
                            $statusColor = [
                                'hadir' => 'bg-emerald-50 text-emerald-600',
                                'sakit' => 'bg-amber-50 text-amber-600',
                                'izin'  => 'bg-blue-50 text-blue-600',
                                'alpa'  => 'bg-red-50 text-red-600',
                            ][$row->status] ?? 'bg-slate-50 text-slate-600';
                        @endphp

                        <span class="px-3 py-1 rounded-lg text-[10px] font-extrabold uppercase {{ $statusColor }}">
                            {{ $row->status }}
                        </span>
                    </td>

                    {{-- FOTO (DIBESARKAN) --}}
                    <td class="px-4 py-4 text-center">
                        @if($row->foto)
                            <button onclick="openImage('{{ asset('storage/' . $row->foto) }}')">
                                <img src="{{ asset('storage/' . $row->foto) }}" 
                                    class="w-16 md:w-20 h-auto aspect-[3/4] md:aspect-square object-cover rounded-xl border shadow hover:scale-105 transition mx-auto">
                            </button>
                        @else
                            <span class="text-slate-300 text-xs">-</span>
                        @endif
                    </td>

                    {{-- LOKASI (DIPISAH) --}}
                    <td class="px-6 py-4 text-center">
                        @if($row->lokasi)
                            <a href="https://www.google.com/maps?q={{ $row->lokasi }}" target="_blank"
                                class="group inline-flex items-center justify-center w-10 h-10 rounded-xl bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition shadow-sm">

                                <svg xmlns="http://www.w3.org/2000/svg" 
                                    class="h-5 w-5 group-hover:scale-110 transition" 
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </a>
                        @else
                            <span class="text-slate-300 text-xs">-</span>
                        @endif
                    </td>

                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-6 py-12 text-center text-slate-400">
                        Belum ada data
                    </td>
                </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        
        @if($riwayat->hasPages())
        <div class="p-6 bg-slate-50 border-t">
            {{ $riwayat->links() }}
        </div>
        @endif
    </div>
</div>

{{-- MODAL FOTO --}}
<div id="imageModal" class="fixed inset-0 bg-slate-900/80 hidden items-center justify-center z-[100] backdrop-blur-sm p-4" onclick="closeImage()">
    <div class="relative max-w-3xl w-full bg-white rounded-3xl overflow-hidden shadow-2xl" onclick="event.stopPropagation()">
        <div class="p-4 border-b border-slate-100 flex justify-between items-center">
            <h4 class="font-bold text-slate-800">Bukti Foto Absensi</h4>
            <button onclick="closeImage()" class="text-slate-400 hover:text-red-500 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div class="p-2 bg-slate-100">
            <img id="modalImage" class="w-full max-h-[70vh] object-contain rounded-xl">
        </div>
    </div>
</div>

<script>
function openImage(src) {
    document.getElementById('modalImage').src = src;
    const modal = document.getElementById('imageModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeImage() {
    const modal = document.getElementById('imageModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

let searchTimer;

function debounceSearch() {

    clearTimeout(searchTimer);

    searchTimer = setTimeout(() => {

        document.getElementById('filterForm').submit();

    }, 500);

}
</script>
@endsection