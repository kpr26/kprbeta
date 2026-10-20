@php
    $n = fn ($v) => $v === null ? '' : number_format($v, 2, ',', '.');
    $catLabel = $category === 'water' ? 'WATER METER PUBLIC AREA' : 'KWH METER PUBLIC AREA';
    $judul = 'REKAPITULASI LOGSHEET ' . $catLabel . ' — ' . strtoupper(\Carbon\Carbon::create($year, $month)->translatedFormat('F Y'));
@endphp
<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel">
<head>
<meta charset="UTF-8">
<style>
    th, td { border: 1px solid #000; padding: 5px 8px; font-family: Calibri, Arial; font-size: 10pt; }
    th { background: #1e293b; color: #fff; font-weight: bold; text-align: center; }
    .num { mso-number-format: "\#\,\#\#0\.00"; text-align: right; }
    .tot td { font-weight: bold; background: #fef3c7; }
</style>
</head>
<body>
<table>
    <tr><td colspan="8" style="border:0;font-size:14pt;font-weight:bold">{{ $judul }}</td></tr>
    <tr><td colspan="8" style="border:0;font-size:10pt;">Dicetak: {{ now()->format('d/m/Y H:i') }} oleh {{ auth()->user()->name }}</td></tr>
    <tr><td colspan="8" style="border:0"></td></tr>
    <tr>
        <th>No</th>
        <th>Tanggal</th>
        <th>Nama Meteran</th>
        <th>Lokasi</th>
        <th>Stand Meter ({{ $category === 'water' ? 'm³' : 'kWh' }})</th>
        <th>Pemakaian ({{ $category === 'water' ? 'm³' : 'kWh' }})</th>
        <th>Petugas</th>
        <th>Status</th>
    </tr>
    @php $totalPemakaian = 0; @endphp
    @foreach ($rows as $i => $r)
    @php $totalPemakaian += ($r->pemakaian ?? 0); @endphp
    <tr>
        <td style="text-align:center;">{{ $i + 1 }}</td>
        <td>{{ $r->tanggal->format('d/m/Y') }}</td>
        <td>{{ $r->meter_name }}</td>
        <td>{{ $meters[$r->meter_key]['location'] ?? '—' }}</td>
        <td class="num">{{ $r->stand_meter }}</td>
        <td class="num">{{ $r->pemakaian }}</td>
        <td>{{ $r->user?->name ?? 'User Terhapus' }}</td>
        <td style="text-align:center;">{{ $r->verified_at ? 'Terverifikasi' : 'Menunggu' }}</td>
    </tr>
    @endforeach
    <tr class="tot">
        <td colspan="5" style="text-align:center;">TOTAL PEMAKAIAN</td>
        <td class="num">{{ round($totalPemakaian, 2) }}</td>
        <td colspan="2" style="text-align:center;">{{ $rows->count() }} data tercatat</td>
    </tr>
</table>
</body>
</html>
