<!DOCTYPE html>
<html>
<head>
    <title>Jurnal Pembina</title>
    <style>
        body { font-family: Arial; font-size: 12px; }
        h2 { text-align: center; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 6px; }
        th { background: #f2f2f2; }
    </style>
</head>
<body>

<h2>
    Jurnal Pembina - {{ \Carbon\Carbon::create()->month($bulan)->translatedFormat('F') }}
</h2>

<table style="border:0; border-collapse:collapse; margin-bottom:15px;">
    <tbody>
        <tr>
            <td style="border:0; width:120px; padding:2px 0;"><strong>Tahun Ajaran</strong></td>
            <td style="border:0; width:15px; text-align:center;">:</td>
            <td style="border:0;">{{ $tahunAjaran }}</td>
        </tr>
        <tr>
            <td style="border:0; padding:2px 0;"><strong>Ekstrakurikuler</strong></td>
            <td style="border:0; text-align:center;">:</td>
            <td style="border:0;">{{ $ekstrakurikuler->nama ?? '-' }}</td>
        </tr>
    </tbody>
</table>

<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Hari / Tanggal</th>
            <th>Jadwal</th>
            <th>Jam</th>
            <th>Lokasi</th>
            <th>Keterangan</th>
            <th>Kehadiran</th>
        </tr>
    </thead>

    <tbody>
        @foreach($events as $i => $event)
        <tr>
            <td>{{ $i+1 }}</td>

            <td>
                {{ $event['tanggal']->translatedFormat('l, d F Y') }}
            </td>

            <td>
                @if($event['libur'])
                    -
                @else
                    {{ $event['jadwal'] }}
                @endif
            </td>

            <td>{{ $event['jam'] }}</td>

            <td>{{ $event['lokasi'] }}</td>

            <td>
                @if($event['libur'])
                    <strong style="color:red">
                        {{ $event['keterangan_libur'] }}
                    </strong>
                @else
                    {{ $event['keterangan'] ?: '-' }}
                @endif
            </td>

            <td style="text-align: center;">
                @if($event['libur'])
                    <strong style="color: #dc2626;">
                        LIBUR
                    </strong>
                @else
                    {{ $event['hadir'] }}/{{ $event['total'] }}
                @endif
            </td>

        </tr>
        @endforeach
    </tbody>
</table>

</body>
</html>