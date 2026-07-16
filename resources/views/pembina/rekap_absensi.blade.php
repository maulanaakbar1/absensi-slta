@extends('layouts.app')

@section('content')
    <div class="p-6">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">

            {{-- Header & Filter --}}
            <div class="p-6 border-b border-slate-100">
                <form action="{{ route('pembina.rekap.index') }}" method="GET" class="flex flex-wrap items-end gap-4">

                    {{-- Tahun Ajaran --}}
                    <div class="flex-1 min-w-[220px]">
                        <label class="block text-sm font-semibold text-slate-600 mb-2">
                            Tahun Ajaran12
                        </label>

                        <select name="tahun_ajaran" onchange="this.form.submit()"
                            class="w-full border-slate-200 rounded-xl focus:ring-cyan-500">
                            <option value="semua">
                                Semua Tahun Ajaran
                            </option>

                            @foreach ($tahunAjaranList as $tahunAjaran)
                                <option value="{{ $tahunAjaran }}" {{ $selectedTahun == $tahunAjaran ? 'selected' : '' }}>
                                    {{ $tahunAjaran }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Filter Kelas --}}
                    <div class="flex-1 min-w-[180px]">
                        <label class="block text-sm font-semibold text-slate-600 mb-2">
                            Kelas
                        </label>

                        <select name="kelas" onchange="this.form.submit()"
                            class="w-full border-slate-200 rounded-xl focus:ring-cyan-500">
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
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-sm font-semibold text-slate-600 mb-2">
                            Kode Kelas
                        </label>

                        <select name="jurusan" onchange="this.form.submit()"
                            class="w-full border-slate-200 rounded-xl focus:ring-cyan-500">
                            <option value="">Semua Kode Kelas</option>

                            @foreach ($jurusanList as $jurusan)
                                <option value="{{ $jurusan }}" {{ $selectedJurusan == $jurusan ? 'selected' : '' }}>
                                    {{ $jurusan }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Bulan --}}
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-sm font-semibold text-slate-600 mb-2">
                            Pilih Bulan
                        </label>

                        <select name="bulan" onchange="this.form.submit()"
                            class="w-full border-slate-200 rounded-xl focus:ring-emerald-500">
                            @foreach ($namaBulan as $key => $name)
                                <option value="{{ $key }}" {{ $bulan == $key ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @php
                        $isFiltered =
                            request('tahun_ajaran') || request('kelas') || request('bulan') || request('jurusan');
                    @endphp

                    @if ($isFiltered)
                        <a href="{{ route('pembina.rekap.index') }}"
                            class="bg-slate-200 hover:bg-slate-300 text-slate-700 px-6 py-2.5 rounded-xl font-bold">
                            Reset
                        </a>
                    @endif

                </form>
            </div>

            <div
                class="px-6 py-5 flex flex-wrap items-center justify-between gap-8 bg-slate-50/80 border-b border-slate-100 w-full">
                {{-- Sisi Kiri: Item Legenda --}}
                <div class="flex flex-wrap items-center gap-8">
                    <div class="flex items-center gap-3">
                        <div class="w-6 h-6 bg-emerald-100 border border-emerald-200 rounded-md"></div>
                        <span class="text-sm font-bold text-slate-700">Hadir</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-6 h-6 bg-amber-100 border border-amber-200 rounded-md"></div>
                        <span class="text-sm font-bold text-slate-700">Sakit</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-6 h-6 bg-blue-100 border border-blue-200 rounded-md"></div>
                        <span class="text-sm font-bold text-slate-700">Izin</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-6 h-6 bg-slate-400 border border-slate-500 rounded-md"></div>
                        <span class="text-sm font-bold text-slate-700">Alpa</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-6 h-6 bg-red-500 border border-red-600 rounded-md"></div>
                        <span class="text-sm font-bold text-slate-700">Libur</span>
                    </div>
                </div>

                {{-- Sisi Kanan: Tombol Aksi (Dengan Efek Warna Pas di Hover) --}}
                <div class="flex items-center gap-2">
                    {{-- PDF --}}
                    <a href="{{ route('pembina.rekap.pdf', request()->query()) }}"
                        class="inline-flex items-center gap-1.5 bg-white hover:bg-red-600 text-red-600 hover:text-white border border-red-200 hover:border-red-600 text-xs font-semibold px-2.5 py-1.5 rounded-md shadow-sm transition-all duration-200 active:scale-95">

                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>

                        <span>PDF</span>
                    </a>

                    {{-- EXCEL --}}
                    <a href="{{ route('pembina.rekap.excel', request()->query()) }}"
                        class="inline-flex items-center gap-1.5 bg-white hover:bg-emerald-600 text-emerald-600 hover:text-white border border-emerald-200 hover:border-emerald-600 text-xs font-semibold px-2.5 py-1.5 rounded-md shadow-sm transition-all duration-200 active:scale-95">

                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 00-2-2V5a2 2 0 002-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span>Excel</span>
                    </a>
                </div>
            </div>

            <div class="p-6">
                <h2 class="text-center text-xl font-bold text-slate-700 mb-6">
                    Rekap Absensi Bulan {{ $namaBulan[$bulan] }} {{ $tahun }}
                </h2>

                <div class="overflow-x-auto border rounded-xl">
                    <table class="min-w-max table-fixed text-sm border-collapse">

                        <thead class="bg-slate-50 text-slate-600">
                            <tr>
                                <th rowspan="2" class="w-12 border px-2 text-center">No</th>
                                <th rowspan="2" class="w-32 border px-2">NISN</th>
                                <th rowspan="2" class="w-64 border px-2">Nama Siswa</th>
                                <th rowspan="2" class="w-32 border px-2">Kelas</th>
                                <th rowspan="2" class="w-24 border px-2 text-center">Angkatan</th>
                                <th colspan="{{ $jumlahHari }}" class="border text-center text-xs">Tanggal</th>
                                <th colspan="4" class="border text-center text-xs">Total</th>
                            </tr>
                            <tr>
                                @for ($i = 1; $i <= $jumlahHari; $i++)
                                    <th class="w-10 border text-center text-xs">{{ $i }}</th>
                                @endfor
                                <th class="w-10 border text-center">H</th>
                                <th class="w-10 border text-center">S</th>
                                <th class="w-10 border text-center">I</th>
                                <th class="w-10 border text-center">A</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($siswas as $index => $siswa)
                                @php
                                    $totalHadir = 0;
                                    $totalSakit = 0;
                                    $totalIzin = 0;
                                    $totalAlpa = 0;
                                @endphp

                                <tr class="hover:bg-slate-50">
                                    <td class="border text-center">{{ $index + 1 }}</td>
                                    <td class="border px-2">{{ $siswa->nisn }}</td>
                                    <td class="border px-2">{{ $siswa->user->name ?? '-' }}</td>
                                    <td class="border px-2 text-center">
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

                                    <td class="border px-2 text-center">
                                        {{ $siswa->tahun_masuk ?? '-' }}
                                    </td>

                                    @for ($i = 1; $i <= $jumlahHari; $i++)
                                        @php
                                            $tgl = sprintf('%02d', $i);
                                            $fullDate = "$tahun-$bulan-$tgl";

                                            $ekskulIds = json_decode($siswa->ekstrakurikuler_id, true);

                                            \Carbon\Carbon::setLocale('id');
                                            $tanggalCarbon = \Carbon\Carbon::parse($fullDate);
                                            $hari = $tanggalCarbon->locale('id')->translatedFormat('l');

                                            $ekskulSiswaIds = is_array($ekskulIds)
                                                ? $ekskulIds
                                                : [$siswa->ekstrakurikuler_id];

                                            $isLiburRutin = \App\Models\HariLibur::where('tipe', 'rutin')
                                                ->where('hari', $hari)
                                                ->whereIn('ekstrakurikuler_id', $ekskulSiswaIds)
                                                ->exists();

                                            $isLiburDadakan = \App\Models\HariLibur::where('tipe', 'dadakan')
                                                ->whereDate('tanggal', $fullDate)
                                                ->whereIn('ekstrakurikuler_id', $ekskulSiswaIds)
                                                ->exists();

                                            $isLibur = $isLiburRutin || $isLiburDadakan;

                                            $adaJadwalRutin = \App\Models\Jadwal::where('tipe', 'rutin')
                                                ->where('hari', $hari)
                                                ->whereIn('ekstrakurikuler_id', $ekskulSiswaIds)
                                                ->exists();

                                            $adaJadwalDadakan = \App\Models\Jadwal::where('tipe', 'dadakan')
                                                ->whereDate('tanggal', $fullDate)
                                                ->whereIn('ekstrakurikuler_id', $ekskulSiswaIds)
                                                ->exists();

                                            $adaJadwal = $adaJadwalRutin || $adaJadwalDadakan;

                                            $absen = $siswa->absensis->firstWhere('tanggal', $fullDate);

                                            $statusColor = 'bg-slate-50';
                                            $tooltip = 'Belum ada absensi';

                                            // =========================
                                            // LIBUR
                                            // =========================
                                            if ($isLibur) {

                                                $statusColor = 'bg-red-500 border-red-600';
                                                $tooltip = 'Hari Libur';

                                            }

                                            elseif ($adaJadwal) {

                                                if ($absen) {

                                                    if ($absen->status === 'hadir') {

                                                        $statusColor = 'bg-emerald-100 border-emerald-200';
                                                        $tooltip = 'Hadir';
                                                        $totalHadir++;

                                                    } elseif ($absen->status === 'sakit') {

                                                        $statusColor = 'bg-amber-100 border-amber-200';
                                                        $tooltip = 'Sakit';
                                                        $totalSakit++;

                                                    } elseif ($absen->status === 'izin') {

                                                        $statusColor = 'bg-blue-100 border-blue-200';
                                                        $tooltip = 'Izin';
                                                        $totalIzin++;

                                                    } elseif ($absen->status === 'alpa') {

                                                        $statusColor = 'bg-slate-400 border-slate-500';
                                                        $tooltip = 'Alpa';
                                                        $totalAlpa++;

                                                    }

                                                }

                                                elseif (\Carbon\Carbon::parse($fullDate)->lt(now()->startOfDay())) {

                                                    $statusColor = 'bg-slate-400 border-slate-500';
                                                    $tooltip = 'Alpa';
                                                    $totalAlpa++;

                                                }

                                                else {

                                                    $statusColor = 'bg-slate-50';
                                                    $tooltip = 'Belum ada absensi';

                                                }

                                            }

                                            else {

                                                $statusColor = 'bg-slate-100';
                                                $tooltip = 'Tidak ada jadwal';

                                            }
                                        @endphp

                                        <td class="border p-0 w-10 h-10">
                                            <div class="relative group w-full h-full">

                                                {{-- Kotak Absensi --}}
                                                <div
                                                    class="w-full h-full flex items-center justify-center cursor-pointer transition duration-200 hover:scale-105 rounded-sm {{ $statusColor }}">
                                                </div>

                                                {{-- Tooltip --}}
                                                <div
                                                    class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2
                                                opacity-0 group-hover:opacity-100
                                                pointer-events-none
                                                transition-all duration-200
                                                scale-95 group-hover:scale-100
                                                z-50">

                                                    <div
                                                        class="px-3 py-1.5 rounded-lg bg-slate-900 text-white text-xs font-semibold shadow-xl whitespace-nowrap">
                                                        {{ $tooltip }}

                                                        {{-- Arrow bawah --}}
                                                        <div
                                                            class="absolute left-1/2 top-full -translate-x-1/2
                                                            border-4 border-transparent border-t-slate-900">
                                                        </div>
                                                    </div>

                                                </div>

                                            </div>
                                        </td>
                                    @endfor

                                    <td class="border text-center font-bold">{{ $totalHadir }}</td>
                                    <td class="border text-center font-bold">{{ $totalSakit }}</td>
                                    <td class="border text-center font-bold">{{ $totalIzin }}</td>
                                    <td class="border text-center font-bold">{{ $totalAlpa }}</td>
                                </tr>
                            @endforeach
                        </tbody>

                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
