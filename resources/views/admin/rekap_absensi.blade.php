@extends('layouts.app')

@section('content')
    <div class="p-6">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">

            {{-- Header & Filter --}}
            <div class="p-6 border-b border-slate-100">

                @php
                    $isFiltered = request('ekskul') || request('tahun_ajaran') || request('kelas') || request('bulan');
                @endphp

                <form action="{{ route('admin.rekap.index') }}" method="GET" class="flex flex-wrap items-end gap-4">

                    {{-- Ekskul --}}
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-sm font-semibold text-slate-600 mb-2">
                            Pilih Ekskul
                        </label>

                        <select name="ekskul" onchange="this.form.submit()"
                            class="w-full border-slate-200 rounded-xl focus:ring-emerald-500">
                            <option value="all">Semua Ekskul</option>

                            @foreach ($listEkskul as $item)
                                <option value="{{ $item->id }}" {{ $ekskul == $item->id ? 'selected' : '' }}>
                                    {{ $item->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Tahun Ajaran --}}
                    <div class="flex-1 min-w-[220px]">
                        <label class="block text-sm font-semibold text-slate-600 mb-2">
                            Tahun Ajaran
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

                    {{-- Kelas --}}
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

                    {{-- Jurusan --}}
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-sm font-semibold text-slate-600 mb-2">
                            Kode Kelas
                        </label>

                        <select name="jurusan" onchange="this.form.submit()"
                            class="w-full border-slate-200 rounded-xl focus:ring-cyan-500">

                            <option value="">Semua Kode Kelas</option>

                            @foreach ($jurusanList as $jur)
                                <option value="{{ $jur }}" {{ $selectedJurusan == $jur ? 'selected' : '' }}>
                                    {{ $jur }}
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

                    {{-- Reset --}}
                    @if ($isFiltered)
                        <a href="{{ route('admin.rekap.index') }}"
                            class="bg-slate-200 hover:bg-slate-300 text-slate-700 px-6 py-2.5 rounded-xl font-bold">
                            Reset
                        </a>
                    @endif

                </form>
            </div>

            {{-- Legend + Action Buttons --}}
            <div
                class="px-6 py-5 flex flex-wrap items-center justify-between gap-8 bg-slate-50/80 border-b border-slate-100 w-full">
                {{-- Sisi Kiri: Legenda --}}
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

                {{-- Sisi Kanan: Tombol Download (SUDAH DIPERBAIKI) --}}
                <div class="flex items-center gap-2">
                    {{-- PDF --}}
                    <a href="{{ request()->is('admin/*') ? route('admin.rekap.pdf', request()->all()) : route('pembina.rekap.pdf', request()->all()) }}"
                        class="inline-flex items-center gap-1.5 bg-white hover:bg-red-600 text-red-600 hover:text-white border border-red-200 hover:border-red-600 text-xs font-semibold px-3 py-2 rounded-xl shadow-sm transition-all duration-200 active:scale-95">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        <span>PDF</span>
                    </a>

                    {{-- Excel --}}
                    <a href="{{ request()->is('admin/*') ? route('admin.rekap.excel', request()->all()) : route('pembina.rekap.excel', request()->all()) }}"
                        class="inline-flex items-center gap-1.5 bg-white hover:bg-emerald-600 text-emerald-600 hover:text-white border border-emerald-200 hover:border-emerald-600 text-xs font-semibold px-3 py-2 rounded-xl shadow-sm transition-all duration-200 active:scale-95">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
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

                        @php
                            $selectedEkskul = request('ekskul');
                        @endphp

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
                                            $kelasColor = match (true) {
                                                $siswa->kelas_display === 'Lulus'
                                                    => 'bg-slate-200 text-slate-700',

                                                $siswa->tingkat_display == 7
                                                    => 'bg-blue-50 text-blue-600',

                                                $siswa->tingkat_display == 8
                                                    => 'bg-emerald-50 text-emerald-600',

                                                $siswa->tingkat_display == 9
                                                    => 'bg-purple-50 text-purple-600',

                                                default
                                                    => 'bg-slate-100 text-slate-600',
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
                                            $fullDate = "{$tahun}-{$bulan}-{$tgl}";
                                            // dump($i, $fullDate);
                                            $tanggalCarbon = \Carbon\Carbon::parse($fullDate);
                                            $hari = $tanggalCarbon->locale('id')->translatedFormat('l');

                                            $jadwalFiltered = $jadwals;
                                            $liburFiltered = $hariLibur;
                                            $absensiFiltered = $siswa->absensis;

                                            if (!empty($selectedEkskul) && $selectedEkskul !== 'all') {
                                                $jadwalFiltered = $jadwalFiltered->where(
                                                    'ekstrakurikuler_id',
                                                    (int) $selectedEkskul,
                                                );

                                                $liburFiltered = $liburFiltered->where(
                                                    'ekstrakurikuler_id',
                                                    (int) $selectedEkskul,
                                                );

                                                $absensiFiltered = $absensiFiltered->where(
                                                    'ekstrakurikuler_id',
                                                    (int) $selectedEkskul,
                                                );
                                            }

                                            // Ambil ekskul siswa dalam bentuk array untuk memfilter jadwal & libur
                                            $ekskulSiswaIds = json_decode($siswa->ekstrakurikuler_id, true);
                                            if (!is_array($ekskulSiswaIds)) {
                                                $ekskulSiswaIds = [$siswa->ekstrakurikuler_id];
                                            }

                                            // Hanya gunakan jadwal dan libur yang sesuai dengan ekskul siswa
                                            $jadwalSiswa = $jadwalFiltered->whereIn('ekstrakurikuler_id', $ekskulSiswaIds);
                                            $liburSiswa = $liburFiltered->whereIn('ekstrakurikuler_id', $ekskulSiswaIds);

                                            $jadwalRutin =
                                                $jadwalSiswa->where('tipe', 'rutin')->where('hari', $hari)->count() >
                                                0;

                                            $jadwalDadakan =
                                                $jadwalSiswa
                                                    ->where('tipe', 'dadakan')
                                                    ->filter(function ($item) use ($fullDate) {
                                                        return !empty($item->tanggal) && \Carbon\Carbon::parse($item->tanggal)->format(
                                                            'Y-m-d',
                                                        ) === $fullDate;
                                                    })
                                                    ->count() > 0;

                                            $adaJadwal = $jadwalRutin || $jadwalDadakan;

                                            $liburRutin =
                                                $liburSiswa->where('tipe', 'rutin')->where('hari', $hari)->count() >
                                                0;

                                            $liburDadakan =
                                                $liburSiswa
                                                    ->where('tipe', 'dadakan')
                                                    ->filter(function ($item) use ($fullDate) {
                                                        return !empty($item->tanggal) && \Carbon\Carbon::parse($item->tanggal)->format(
                                                            'Y-m-d',
                                                        ) === $fullDate;
                                                    })
                                                    ->count() > 0;

                                            $isLibur = $liburRutin || $liburDadakan;

                                            $absen = $absensiFiltered->first(function ($item) use ($fullDate) {
                                                return \Carbon\Carbon::parse($item->tanggal)->format('Y-m-d') ===
                                                    $fullDate;
                                            });

                                            $statusColor = '';

                                            if ($isLibur) {
                                                $statusColor = 'bg-red-500 border-red-600';
                                            } elseif (!$adaJadwal) {
                                                $statusColor = 'bg-slate-100';
                                            } elseif ($absen) {

                                                switch ($absen->status) {

                                                    case 'hadir':
                                                        $statusColor = 'bg-emerald-100 border-emerald-200';
                                                        $totalHadir++;
                                                        break;

                                                    case 'sakit':
                                                        $statusColor = 'bg-amber-100 border-amber-200';
                                                        $totalSakit++;
                                                        break;

                                                    case 'izin':
                                                        $statusColor = 'bg-blue-100 border-blue-200';
                                                        $totalIzin++;
                                                        break;

                                                    case 'alpa':
                                                        $statusColor = 'bg-slate-400 border-slate-500';
                                                        $totalAlpa++;
                                                        break;
                                                }

                                            }
                                            elseif ($adaJadwal && \Carbon\Carbon::parse($fullDate)->lt(now()->startOfDay())) {

                                                $statusColor = 'bg-slate-400 border-slate-500';
                                                $totalAlpa++;

                                            }
                                        @endphp

                                        <td class="border p-0 w-10 h-10 overflow-visible">
                                            @php
                                                $tooltip = 'Belum ada absensi';

                                                    if ($isLibur) {

                                                        $tooltip = 'Hari Libur';

                                                    } elseif (!$adaJadwal) {

                                                        $tooltip = 'Tidak ada jadwal';

                                                    } elseif ($absen) {

                                                        if ($absen->status == 'hadir') {
                                                            $tooltip = 'Hadir';
                                                        } elseif ($absen->status == 'sakit') {
                                                            $tooltip = 'Sakit';
                                                        } elseif ($absen->status == 'izin') {
                                                            $tooltip = 'Izin';
                                                        } elseif ($absen->status == 'alpa') {
                                                            $tooltip = 'Alpa';
                                                        }

                                                    } elseif (\Carbon\Carbon::parse($fullDate)->lt(now()->startOfDay())) {

                                                        $tooltip = 'Alpa';

                                                    }
                                            @endphp

                                            <div class="relative group w-full h-full flex items-center justify-center">

                                                {{-- Kotak Absensi --}}
                                                <div
                                                    class="w-full h-full rounded-sm border transition-all duration-200
                                                hover:scale-110 hover:shadow-md cursor-pointer
                                                {{ $statusColor }}">
                                                </div>

                                                {{-- Tooltip --}}
                                                <div
                                                    class="absolute bottom-full left-1/2 -translate-x-1/2 mb-3
                                                opacity-0 invisible
                                                group-hover:opacity-100 group-hover:visible
                                                transition-all duration-200 ease-out
                                                z-[9999]">

                                                    <div
                                                        class="relative px-3 py-1.5 rounded-xl
                                                        bg-slate-900 text-white text-[11px]
                                                        font-semibold shadow-2xl whitespace-nowrap">

                                                        {{ $tooltip }}

                                                        {{-- Arrow --}}
                                                        <div
                                                            class="absolute left-1/2 top-full -translate-x-1/2
                                                            border-[6px] border-transparent
                                                            border-t-slate-900">
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
