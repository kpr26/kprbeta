@php
    $n = fn ($v) => ($v !== null && $v !== '') ? number_format((float)$v, 2, ',', '.') : '—';
    $catLabel = $category === 'water' ? 'WATER METER PUBLIC AREA' : 'KWH METER PUBLIC AREA';
    $periodeLabel = $startDate->translatedFormat('d M Y') . ' s/d ' . $endDate->translatedFormat('d M Y');
    $judul = 'REKAPITULASI LOGSHEET ' . $catLabel . ' (' . $periodeLabel . ')';
    $grandTotalUsage = round($rows->sum(fn ($r) => $r->pemakaian ?? 0), 2);
@endphp
<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel">
<head>
<meta charset="UTF-8">
<style>
    th, td { border: 1px solid #000; padding: 5px 8px; font-family: Calibri, Arial; font-size: 10pt; }
    th { background: #1e293b; color: #fff; font-weight: bold; text-align: center; }
    .th-sub { background: #334155; color: #fff; font-weight: bold; }
    .num { mso-number-format: "\#\,\#\#0\.00"; text-align: right; }
    .tot td { font-weight: bold; background: #fef3c7; }
    .kpi-hdr { background: #0284c7; color: #ffffff; font-weight: bold; }
</style>
</head>
<body>
<table>
    <tr><td colspan="8" style="border:0;font-size:14pt;font-weight:bold">{{ $judul }}</td></tr>
    <tr><td colspan="8" style="border:0;font-size:10pt;">Rentang Interval: {{ $periodeLabel }} (Maks. 12 Bulan)</td></tr>
    <tr><td colspan="8" style="border:0;font-size:10pt;">Dicetak: {{ now()->translatedFormat('d F Y H:i') }} oleh {{ auth()->user()->name }}</td></tr>
    <tr><td colspan="8" style="border:0"></td></tr>

    {{-- TABEL 1: RANGKUMAN STATISTIK INTERVAL --}}
    <tr><td colspan="8" class="kpi-hdr" style="font-size:11pt;">I. TABEL RANGKUMAN STATISTIK PEMAKAIAN (INTERVAL {{ $periodeLabel }})</td></tr>
    <tr>
        <th>No</th>
        <th colspan="2">Titik Meteran Air</th>
        <th>Lokasi</th>
        <th>Total Pemakaian (m³)</th>
        <th>Rata-rata (m³/hari)</th>
        <th>Min (m³)</th>
        <th>Max (m³)</th>
    </tr>
    @php $mIndex = 1; @endphp
    @foreach ($meters as $key => $meta)
        @php $s = $summaryPerMeter[$key] ?? null; @endphp
        <tr>
            <td style="text-align:center;">{{ $mIndex++ }}</td>
            <td colspan="2" style="font-weight:bold;">{{ $meta['name'] }}</td>
            <td>{{ $meta['location'] }}</td>
            <td class="num" style="font-weight:bold;">{{ $s['totalUsage'] ?? 0 }}</td>
            <td class="num">{{ $s['avgUsage'] ?? 0 }}</td>
            <td class="num">{{ $s['minUsage'] ?? 0 }}</td>
            <td class="num">{{ $s['maxUsage'] ?? 0 }}</td>
        </tr>
    @endforeach
    <tr class="tot">
        <td colspan="4" style="text-align:center;">GRAND TOTAL PEMAKAIAN AIR PUBLIC AREA</td>
        <td class="num">{{ $grandTotalUsage }}</td>
        <td colspan="3" style="text-align:center;">{{ $rows->count() }} data pencatatan</td>
    </tr>

    <tr><td colspan="8" style="border:0"></td></tr>
    <tr><td colspan="8" style="border:0"></td></tr>

    {{-- TABEL 2: RINCIAN SEMUA PENCATATAN --}}
    <tr><td colspan="8" class="kpi-hdr" style="font-size:11pt;">II. RINCIAN PENCATATAN STAND METER HARIAN</td></tr>
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
    @foreach ($rows as $i => $r)
    <tr>
        <td style="text-align:center;">{{ $i + 1 }}</td>
        <td style="text-align:center;">{{ $r->tanggal->format('d/m/Y') }}</td>
        <td>{{ $r->meter_name }}</td>
        <td>{{ $meters[$r->meter_key]['location'] ?? '—' }}</td>
        <td class="num">{{ $r->stand_meter }}</td>
        <td class="num">{{ $r->pemakaian }}</td>
        <td>{{ $r->user?->name ?? 'User Terhapus' }}</td>
        <td style="text-align:center;">{{ $r->isVerified() ? 'Terverifikasi' : 'Menunggu' }}</td>
    </tr>
    @endforeach
    <tr class="tot">
        <td colspan="5" style="text-align:center;">TOTAL PEMAKAIAN</td>
        <td class="num">{{ $grandTotalUsage }}</td>
        <td colspan="2" style="text-align:center;">{{ $rows->count() }} data tercatat</td>
    </tr>
</table>
</body>
</html>
