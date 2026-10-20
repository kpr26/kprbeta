@php
    $n = fn ($v, $d = 2) => ($v !== null && $v !== '') ? number_format((float)$v, $d, ',', '.') : '';
    $periodeLabel = $startDate->translatedFormat('d M Y') . ' s/d ' . $endDate->translatedFormat('d M Y');
    $judul = 'LOGSHEET MVSB INCOMING FROM PLN — ' . strtoupper($periodeLabel);

    // Hitung Metrik Statistik Interval
    $voltList = [];
    $currList = [];
    foreach ($rows as $r) {
        if ($r->voltage_20kv !== null && $r->voltage_20kv !== '') $voltList[] = (float)$r->voltage_20kv;
        if ($r->current !== null && $r->current !== '') $currList[] = (float)$r->current;
    }

    $avgVolt = count($voltList) > 0 ? round(array_sum($voltList) / count($voltList), 1) : null;
    $maxVolt = count($voltList) > 0 ? max($voltList) : null;
    $minVolt = count($voltList) > 0 ? min($voltList) : null;

    $avgCurr = count($currList) > 0 ? round(array_sum($currList) / count($currList), 2) : null;
    $maxCurr = count($currList) > 0 ? max($currList) : null;
    $minCurr = count($currList) > 0 ? min($currList) : null;
@endphp
<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel">
<head>
<meta charset="UTF-8">
<style>
    th, td { border: 1px solid #000; padding: 6px 8px; font-family: Calibri, Arial; font-size: 10pt; }
    th { background: #581c87; color: #fff; font-weight: bold; text-align: center; }
    .kpi-hdr { background: #7c3aed; color: #ffffff; font-weight: bold; }
    .num { mso-number-format: "\#\,\#\#0\.00"; text-align: right; }
    .num1 { mso-number-format: "\#\,\#\#0\.0"; text-align: right; }
</style>
</head>
<body>
<table>
    <tr><td colspan="7" style="border:0;font-size:14pt;font-weight:bold">{{ $judul }}</td></tr>
    <tr><td colspan="7" style="border:0;font-size:10pt;">Rentang Interval: {{ $periodeLabel }} (Maks. 12 Bulan)</td></tr>
    <tr><td colspan="7" style="border:0;font-size:10pt;">Dicetak: {{ now()->translatedFormat('d F Y H:i') }} oleh {{ auth()->user()->name }}</td></tr>
    <tr><td colspan="7" style="border:0"></td></tr>

    {{-- TABEL 1: RANGKUMAN STATISTIK MVSB --}}
    <tr><td colspan="7" class="kpi-hdr" style="font-size:11pt;">I. TABEL RANGKUMAN STATISTIK MVSB (INTERVAL {{ $periodeLabel }})</td></tr>
    <tr>
        <th>No</th>
        <th colspan="2">Parameter MVSB PLN</th>
        <th>Satuan</th>
        <th>Rata-rata</th>
        <th>Nilai Tertinggi (Max)</th>
        <th>Nilai Terendah (Min)</th>
    </tr>
    <tr>
        <td style="text-align:center;">1</td>
        <td colspan="2" style="font-weight:bold;">Voltage 20kV (Tegangan Menengah)</td>
        <td style="text-align:center;">Volt</td>
        <td class="num1" style="font-weight:bold;">{{ $avgVolt }}</td>
        <td class="num1">{{ $maxVolt }}</td>
        <td class="num1">{{ $minVolt }}</td>
    </tr>
    <tr>
        <td style="text-align:center;">2</td>
        <td colspan="2" style="font-weight:bold;">Current (Arus Beban Incoming)</td>
        <td style="text-align:center;">Ampere</td>
        <td class="num" style="font-weight:bold;">{{ $avgCurr }}</td>
        <td class="num">{{ $maxCurr }}</td>
        <td class="num">{{ $minCurr }}</td>
    </tr>
    <tr style="background:#fef3c7;font-weight:bold;">
        <td colspan="3" style="text-align:center;">TOTAL HARI PENCATATAN TERCATAT</td>
        <td colspan="4" style="text-align:left;padding-left:12px;">{{ $rows->count() }} hari logsheet tercatat</td>
    </tr>

    <tr><td colspan="7" style="border:0"></td></tr>
    <tr><td colspan="7" style="border:0"></td></tr>

    {{-- TABEL 2: RINCIAN PENCATATAN HARIAN --}}
    <tr><td colspan="7" class="kpi-hdr" style="font-size:11pt;">II. RINCIAN PENCATATAN HARIAN MVSB 20kV</td></tr>
    <tr>
        <th style="width:40px;">No</th>
        <th style="width:110px;">Tanggal</th>
        <th style="width:140px;">Voltage 20kV (Volt)</th>
        <th style="width:140px;">Current (Ampere)</th>
        <th style="width:200px;">Catatan Kondisi</th>
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
