<table border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse; width: 100%;">
    <thead>
        <tr>
            <td colspan="{{ 5 + $jumlahHari + 4 }}" align="left" style="font-weight: bold; font-size: 12px; border: none;">
                Tahun Ajaran : 
                @if(isset($selectedTahun) && $selectedTahun !== 'semua')
                    {{ $selectedTahun }}
                @else
                    Semua
                @endif
            </td>
        </tr>

        @if($isAdmin ?? false)
        <tr>
            <td colspan="{{ 5 + $jumlahHari + 4 }}" align="left" style="font-weight: bold; font-size: 12px; border: none;">
                Ekstrakurikuler : {{ $namaEkskul ?? '-' }}
            </td>
        </tr>
        @endif

        <tr>
            <td colspan="{{ 5 + $jumlahHari + 4 }}" style="border: none;"></td>
        </tr>

        <tr>
            <td colspan="{{ 5 + $jumlahHari + 4 }}" align="center" style="font-weight: bold; font-size: 14px;">
                Rekap Absensi Bulan {{ $namaBulan[$bulan] }} {{ $tahun }}
            </td>
        </tr>
        
        <tr>
            <td colspan="{{ 5 + $jumlahHari + 4 }}"></td>
        </tr>

        {{-- Header Data Utama --}}
        <tr>
            <th rowspan="2" align="center">No</th>
            <th rowspan="2" align="center">NISN</th>
            <th rowspan="2" align="left" style="width: 250px; min-width: 250px;">Nama Siswa</th>
            <th rowspan="2" align="center">Kelas</th>
            <th rowspan="2" align="center">Angkatan</th>

            <th colspan="{{ $jumlahHari }}" align="center">
                Tanggal
            </th>

            <th colspan="4" align="center">
                Total
            </th>
        </tr>

        <tr>
            {{-- Angka Tanggal (1, 2, 3...) -> Center --}}
            @for($i = 1; $i <= $jumlahHari; $i++)
                <th align="center">{{ $i }}</th>
            @endfor

            <th align="center">H</th>
            <th align="center">S</th>
            <th align="center">I</th>
            <th align="center">A</th>
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
            <td align="center">{{ $index + 1 }}</td>
            <td align="center">{{ $siswa->nisn }}</td>
            {{-- PERUBAHAN: Ditambahkan juga di level data td agar konsisten --}}
            <td align="left" style="width: 250px;">{{ $siswa->user->name ?? '-' }}</td>
            <td align="center">{{ $siswa->kelas_display }}</td>

            {{-- Kolom Angkatan --}}
            <td align="center">
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

                {{-- Status Absen (H/S/I/A/L/-) -> Center --}}
                <td align="center">{{ $status }}</td>

            @endfor

            {{-- Total Rekap Angka -> Center --}}
            <td align="center">{{ $totalHadir }}</td>
            <td align="center">{{ $totalSakit }}</td>
            <td align="center">{{ $totalIzin }}</td>
            <td align="center">{{ $totalAlpa }}</td>
        </tr>

    @endforeach

    </tbody>
</table>    