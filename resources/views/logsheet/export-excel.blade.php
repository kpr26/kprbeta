@php
    $n = fn ($v) => $v === null ? '' : number_format($v, 2, ',', '.');
    $judul = 'LOGSHEET PEMAKAIAN LISTRIK & AIR — ' . strtoupper(\Carbon\Carbon::create($year, $month)->translatedFormat('F Y'));
@endphp
<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel">
<head>
<meta charset="UTF-8">
<style>
    th, td { border: 1px solid #000; padding: 4px 6px; font-family: Calibri, Arial; font-size: 11pt; }
    th { background: #1e293b; color: #fff; }
    .num { mso-number-format: "\#\,\#\#0\.00"; text-align: right; }
    .tot td { font-weight: bold; background: #e0e7ff; }
</style>
</head>
<body>
<table>
    <tr><td colspan="11" style="border:0;font-size:14pt;font-weight:bold">{{ $judul }}</td></tr>
    <tr><td colspan="11" style="border:0">Dicetak: {{ now()->format('d/m/Y H:i') }} oleh {{ auth()->user()->name }}</td></tr>
    <tr><td colspan="11" style="border:0"></td></tr>
    <tr>
        <th rowspan="2">No</th><th rowspan="2">Tanggal</th>
        <th colspan="5">Listrik (kWh)</th>
        <th colspan="2">Air (m³)</th>
        <th rowspan="2">Teknisi</th><th rowspan="2">Status</th>
    </tr>
    <tr>
        <th>Stand LWBP</th><th>Pakai LWBP</th><th>Stand WBP</th><th>Pakai WBP</th><th>Total kWh</th>
        <th>Stand Air</th><th>Pakai Air</th>
    </tr>
    @foreach ($rows as $i => $r)
    <tr>
        <td>{{ $i + 1 }}</td>
        <td>{{ $r->tanggal->format('d/m/Y') }}</td>
        <td class="num">{{ $r->stand_lwbp }}</td>
        <td class="num">{{ $r->pakai_lwbp }}</td>
        <td class="num">{{ $r->stand_wbp }}</td>
        <td class="num">{{ $r->pakai_wbp }}</td>
        <td class="num">{{ $r->pakai_kwh }}</td>
        <td class="num">{{ $r->stand_air }}</td>
        <td class="num">{{ $r->pakai_air }}</td>
        <td>{{ $r->user->name }}</td>
        <td>{{ $r->verified_at ? 'Terverifikasi' : 'Menunggu' }}</td>
    </tr>
    @endforeach
    <tr class="tot">
        <td colspan="2">TOTAL</td>
        <td></td><td class="num">{{ $summary['lwbp'] }}</td>
        <td></td><td class="num">{{ $summary['wbp'] }}</td>
        <td class="num">{{ $summary['kwh'] }}</td>
        <td></td><td class="num">{{ $summary['air'] }}</td>
        <td colspan="2">{{ $summary['jumlah'] }} hari tercatat</td>
    </tr>
</table>
</body>
</html>
