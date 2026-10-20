@php
    $n = fn ($v, $d = 2) => ($v !== null && $v !== '') ? number_format((float)$v, $d, ',', '.') : '';
    $judul = 'LOGSHEET MVSB INCOMING FROM PLN — ' . strtoupper(\Carbon\Carbon::create($year, $month)->translatedFormat('F Y'));
@endphp
<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel">
<head>
<meta charset="UTF-8">
<style>
    th, td { border: 1px solid #000; padding: 6px 8px; font-family: Calibri, Arial; font-size: 10pt; }
    th { background: #581c87; color: #fff; font-weight: bold; text-align: center; }
    .num { mso-number-format: "\#\,\#\#0\.00"; text-align: right; }
    .num1 { mso-number-format: "\#\,\#\#0\.0"; text-align: right; }
</style>
</head>
<body>
<table>
    <tr><td colspan="7" style="border:0;font-size:14pt;font-weight:bold">{{ $judul }}</td></tr>
    <tr><td colspan="7" style="border:0;font-size:10pt;">Dicetak: {{ now()->format('d/m/Y H:i') }} oleh {{ auth()->user()->name }}</td></tr>
    <tr><td colspan="7" style="border:0"></td></tr>

    <tr>
        <th style="width:40px;">No</th>
        <th style="width:110px;">Tanggal</th>
        <th style="width:140px;">Voltage 20kV (Volt)</th>
        <th style="width:140px;">Current (Ampere)</th>
        <th style="width:180px;">Catatan</th>
        <th style="width:130px;">Petugas</th>
        <th style="width:120px;">Status</th>
    </tr>

    @foreach($rows as $idx => $r)
        <tr>
            <td style="text-align:center;">{{ $idx + 1 }}</td>
            <td style="text-align:center;">{{ $r->tanggal->format('d/m/Y') }}</td>
            <td class="num1">{{ $r->voltage_20kv !== null ? number_format($r->voltage_20kv, 1, '.', '') : '' }}</td>
            <td class="num">{{ $r->current !== null ? number_format($r->current, 2, '.', '') : '' }}</td>
            <td>{{ $r->catatan ?? '-' }}</td>
            <td>{{ $r->user?->name ?? 'Teknisi' }}</td>
            <td style="text-align:center;">{{ $r->isVerified() ? 'Verified' : 'Pending' }}</td>
        </tr>
    @endforeach
</table>
</body>
</html>
