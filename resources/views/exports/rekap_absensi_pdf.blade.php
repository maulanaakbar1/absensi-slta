<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rekap Absensi</title>

    <style>
        body {
            font-family: sans-serif;
            font-size: 9px;
            margin: 0;
            padding: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }

        th, td {
            border: 1px solid #000;
            padding: 4px 3px;
            text-align: center;
            vertical-align: middle;
        }

        /* Mengatur teks nama agar rata kiri saat di PDF */
        .text-left {
            text-align: left;
        }

        /* Wrapper untuk info Tahun Ajaran di paling atas */
        .info-tahun-ajaran {
            font-weight: bold;
            font-size: 11px;
            text-align: left;
            margin-bottom: 10px;
        }

        h2 {
            margin-top: 10px;
            margin-bottom: 15px;
            font-size: 14px;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="info-tahun-ajaran">
    Tahun Ajaran : 
    @if(isset($selectedTahun) && $selectedTahun !== 'semua')
        {{ $selectedTahun }}
    @else
        Semua
    @endif

    @if($isAdmin ?? false)
        <br>
        Ekstrakurikuler : {{ $namaEkskul ?? '-' }}
    @endif
</div>

<h2>
    Rekap Absensi Bulan {{ $namaBulan[$bulan] }} {{ $tahun }}
</h2>

<table>
    <thead>
        <tr>
            <th rowspan="2" style="width: 30px;">No</th>
            <th rowspan="2" style="width: 70px;">NISN</th>
            <th rowspan="2" class="text-left" style="width: 180px;">Nama Siswa</th>
            <th rowspan="2" style="width: 60px;">Kelas</th>
            <th rowspan="2" style="width: 55px;">Angkatan</th>

            <th colspan="{{ $jumlahHari }}">
                Tanggal
            </th>

            <th colspan="4">
                Total
            </th>
        </tr>

        <tr>
            @for($i = 1; $i <= $jumlahHari; $i++)
                <th>{{ $i }}</th>
            @endfor

            <th>H</th>
            <th>S</th>
            <th>I</th>
            <th>A</th>
        </tr>
    </thead>

    <tbody>

    @foreach($siswas as $index => $siswa)

        @php
            $totalHadir = 0;
            $totalSakit = 0;
            $totalIzin = 0;
            $totalAlpa = 0;
        @endphp

        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $siswa->nisn }}</td>
            <td class="text-left">{{ $siswa->user->name ?? '-' }}</td>
            <td>{{ $siswa->kelas_display }}</td>
            
            {{-- TAMBAHAN: Mengisi Data Kolom Angkatan sesuai format Excel --}}
            <td>
                @if($siswa->tahun_masuk)
                    {{ $siswa->tahun_masuk }}
                @else
                    -
                @endif
            </td>

            @for($i = 1; $i <= $jumlahHari; $i++)

                @php
                    $tgl = sprintf('%02d', $i);
                    $fullDate = "$tahun-$bulan-$tgl";

                    $tanggalCarbon = \Carbon\Carbon::parse($fullDate);
                    $hari = $tanggalCarbon->translatedFormat('l');

                    $isLibur = \App\Models\HariLibur::where(
                        'ekstrakurikuler_id',
                        $siswa->ekstrakurikuler_id
                    )
                    ->whereDate('tanggal', $fullDate)
                    ->exists();

                    $adaJadwal = \App\Models\Jadwal::where(
                        'ekstrakurikuler_id',
                        $siswa->ekstrakurikuler_id
                    )
                    ->where('hari', $hari)
                    ->exists();

                    $absen = $siswa->absensis
                        ->firstWhere('tanggal', $fullDate);

                    $status = '-';

                    if ($isLibur) {
                        $status = 'L';
                    } elseif (!$adaJadwal) {
                        $status = '-';
                    } elseif ($absen) {
                        if ($absen->status == 'hadir') {
                            $status = 'H';
                            $totalHadir++;
                        } elseif ($absen->status == 'sakit') {
                            $status = 'S';
                            $totalSakit++;
                        } elseif ($absen->status == 'izin') {
                            $status = 'I';
                            $totalIzin++;
                        } elseif ($absen->status == 'alpa') {
                            $status = 'A';
                            $totalAlpa++;
                        }
                    }
                @endphp

                <td>{{ $status }}</td>

            @endfor

            <td>{{ $totalHadir }}</td>
            <td>{{ $totalSakit }}</td>
            <td>{{ $totalIzin }}</td>
            <td>{{ $totalAlpa }}</td>
        </tr>

    @endforeach

    </tbody>
</table>

</body>
</html>