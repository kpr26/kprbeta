@php
    $n = fn ($v, $d = 2) => ($v !== null && $v !== '') ? number_format((float)$v, $d, ',', '.') : '';
    $periodeLabel = $startDate->translatedFormat('d M Y') . ' s/d ' . $endDate->translatedFormat('d M Y');
    $judul = 'LOGSHEET LVSB PUBLIC AREA — ' . strtoupper($periodeLabel);

    // Hitung Metrik Statistik Interval
    $vList = [];
    $pList = [];
    $cList = [];
    $pfList = [];
    foreach ($rows as $r) {
        $v = $r->val('incoming_trafo', 'voltage_3p');
        $p = $r->val('incoming_trafo', 'power');
        $c = $r->val('incoming_trafo', 'current');
        $pf = $r->val('incoming_capacitor', 'power_factor');

        if ($v !== null && $v !== '') $vList[] = (float)$v;
        if ($p !== null && $p !== '') $pList[] = (float)$p;
        if ($c !== null && $c !== '') $cList[] = (float)$c;
        if ($pf !== null && $pf !== '') $pfList[] = (float)$pf;
    }

    $avgVolt = count($vList) > 0 ? round(array_sum($vList) / count($vList), 1) : null;
    $maxVolt = count($vList) > 0 ? max($vList) : null;
    $minVolt = count($vList) > 0 ? min($vList) : null;

    $avgPower = count($pList) > 0 ? round(array_sum($pList) / count($pList), 2) : null;
    $maxPower = count($pList) > 0 ? max($pList) : null;
    $minPower = count($pList) > 0 ? min($pList) : null;

    $avgCurr = count($cList) > 0 ? round(array_sum($cList) / count($cList), 2) : null;
    $maxCurr = count($cList) > 0 ? max($cList) : null;
    $minCurr = count($cList) > 0 ? min($cList) : null;

    $avgPf = count($pfList) > 0 ? round(array_sum($pfList) / count($pfList), 2) : null;
    $minPf = count($pfList) > 0 ? min($pfList) : null;
    $maxPf = count($pfList) > 0 ? max($pfList) : null;
@endphp
<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel">
<head>
<meta charset="UTF-8">
<style>
    th, td { border: 1px solid #000; padding: 4px 6px; font-family: Calibri, Arial; font-size: 9pt; }
    th { background: #1e293b; color: #fff; font-weight: bold; text-align: center; }
    .th-sub { background: #334155; color: #fff; font-size: 8pt; }
    .kpi-hdr { background: #d97706; color: #ffffff; font-weight: bold; }
    .num { mso-number-format: "\#\,\#\#0\.00"; text-align: right; }
    .num1 { mso-number-format: "\#\,\#\#0\.0"; text-align: right; }
    .num4 { mso-number-format: "\#\,\#\#0\.0000"; text-align: right; }
    .num8 { mso-number-format: "\#\,\#\#0\.00000000"; text-align: right; }
</style>
</head>
<body>
<table>
    <tr><td colspan="15" style="border:0;font-size:14pt;font-weight:bold">{{ $judul }}</td></tr>
    <tr><td colspan="15" style="border:0;font-size:10pt;">Rentang Interval: {{ $periodeLabel }} (Maks. 12 Bulan)</td></tr>
    <tr><td colspan="15" style="border:0;font-size:10pt;">Dicetak: {{ now()->translatedFormat('d F Y H:i') }} oleh {{ auth()->user()->name }}</td></tr>
    <tr><td colspan="15" style="border:0"></td></tr>

    {{-- TABEL 1: RANGKUMAN STATISTIK LVSB --}}
    <tr><td colspan="15" class="kpi-hdr" style="font-size:11pt;">I. TABEL RANGKUMAN STATISTIK LVSB (INTERVAL {{ $periodeLabel }})</td></tr>
    <tr>
        <th>No</th>
        <th colspan="3">Parameter LVSB</th>
        <th colspan="2">Satuan</th>
        <th colspan="2">Rata-rata</th>
        <th colspan="2">Nilai Tertinggi (Max)</th>
        <th colspan="2">Nilai Terendah (Min)</th>
        <th colspan="3">Keterangan</th>
    </tr>
    <tr>
        <td style="text-align:center;">1</td>
        <td colspan="3" style="font-weight:bold;">Tegangan Incoming Trafo (3-Phase)</td>
        <td colspan="2" style="text-align:center;">Volt</td>
        <td colspan="2" class="num1" style="font-weight:bold;">{{ $avgVolt }}</td>
        <td colspan="2" class="num1">{{ $maxVolt }}</td>
        <td colspan="2" class="num1">{{ $minVolt }}</td>
        <td colspan="3">Tegangan incoming trafo Apart A/B</td>
    </tr>
    <tr>
        <td style="text-align:center;">2</td>
        <td colspan="3" style="font-weight:bold;">Beban Daya Incoming Trafo (Power)</td>
        <td colspan="2" style="text-align:center;">kW Total</td>
        <td colspan="2" class="num" style="font-weight:bold;">{{ $avgPower }}</td>
        <td colspan="2" class="num" style="color:#d97706;font-weight:bold;">{{ $maxPower }}</td>
        <td colspan="2" class="num">{{ $minPower }}</td>
        <td colspan="3">Beban puncak (kW) incoming trafo</td>
    </tr>
    <tr>
        <td style="text-align:center;">3</td>
        <td colspan="3" style="font-weight:bold;">Arus Incoming Trafo (Current)</td>
        <td colspan="2" style="text-align:center;">Ampere</td>
        <td colspan="2" class="num">{{ $avgCurr }}</td>
        <td colspan="2" class="num" style="font-weight:bold;">{{ $maxCurr }}</td>
        <td colspan="2" class="num">{{ $minCurr }}</td>
        <td colspan="3">Arus operasional trafo</td>
    </tr>
    <tr>
        <td style="text-align:center;">4</td>
        <td colspan="3" style="font-weight:bold;">Power Factor (PF) Kapasitor Bank</td>
        <td colspan="2" style="text-align:center;">Cos φ</td>
        <td colspan="2" class="num" style="font-weight:bold;">{{ $avgPf }}</td>
        <td colspan="2" class="num">{{ $maxPf }}</td>
        <td colspan="2" class="num">{{ $minPf }}</td>
        <td colspan="3">Efisiensi cos φ kapasitor bank</td>
    </tr>
    <tr style="background:#fef3c7;font-weight:bold;">
        <td colspan="4" style="text-align:center;">TOTAL HARI PENCATATAN TERCATAT</td>
        <td colspan="11" style="text-align:left;padding-left:12px;">{{ $rows->count() }} hari logsheet tercatat</td>
    </tr>

    <tr><td colspan="15" style="border:0"></td></tr>
    <tr><td colspan="15" style="border:0"></td></tr>

    {{-- TABEL 2: RINCIAN LOGSHEET SEMUA FEEDER --}}
    <tr><td colspan="15" class="kpi-hdr" style="font-size:11pt;">II. RINCIAN PENCATATAN LOGSHEET HARIAN LVSB (SEMUA FEEDER)</td></tr>
    {{-- Header Baris 1: Grup --}}
    <tr>
        <th rowspan="2">No</th>
        <th rowspan="2">Tanggal</th>
        <th colspan="6">1. INCOMING FROM TRAFO APART A/B</th>
        <th colspan="6">2. OUTGOING 1.2 - RISER 3</th>
        <th colspan="6">2. OUTGOING 1.2 - OV AC SKY LOBBY</th>
        <th colspan="6">2. OUTGOING 1.2 - AC R.PANEL & R.TRAFO</th>
        <th colspan="6">3. OUTGOING 1.1 - RISER 4</th>
        <th colspan="4">4. OUTGOING 2.1 - RISER 2</th>
        <th colspan="4">4. OUTGOING 2.1 - DB FIRE 2 LVL 12</th>
        <th colspan="4">4. OUTGOING 2.1 - SPARE</th>
        <th colspan="6">5. OUTGOING 2.2 - RISER 1</th>
        <th colspan="6">5. OUTGOING 2.2 - RISER 5</th>
        <th>6. CAPACITOR</th>
        <th rowspan="2">Petugas</th>
        <th rowspan="2">Status</th>
    </tr>

    {{-- Header Baris 2: Parameter Tiap Feeder --}}
    <tr>
        {{-- Incoming Trafo --}}
        <th class="th-sub">Volt</th><th class="th-sub">Amp</th><th class="th-sub">kW</th><th class="th-sub">GWh</th><th class="th-sub">PF</th><th class="th-sub">Hz</th>
        {{-- Outgoing 1.2 Riser 3 --}}
        <th class="th-sub">Volt</th><th class="th-sub">Amp</th><th class="th-sub">kW</th><th class="th-sub">GWh</th><th class="th-sub">PF</th><th class="th-sub">Hz</th>
        {{-- Outgoing 1.2 OV AC --}}
        <th class="th-sub">Volt</th><th class="th-sub">Amp</th><th class="th-sub">kW</th><th class="th-sub">GWh</th><th class="th-sub">PF</th><th class="th-sub">Hz</th>
        {{-- Outgoing 1.2 Ac R. Panel --}}
        <th class="th-sub">Volt</th><th class="th-sub">Amp</th><th class="th-sub">kW</th><th class="th-sub">GWh</th><th class="th-sub">PF</th><th class="th-sub">Hz</th>
        {{-- Outgoing 1.1 Riser 4 --}}
        <th class="th-sub">Volt</th><th class="th-sub">Amp</th><th class="th-sub">kW</th><th class="th-sub">GWh</th><th class="th-sub">PF</th><th class="th-sub">Hz</th>
        {{-- Outgoing 2.1 Riser 2 --}}
        <th class="th-sub">Volt</th><th class="th-sub">Amp</th><th class="th-sub">kW</th><th class="th-sub">GWh</th>
        {{-- Outgoing 2.1 DB Fire 2 --}}
        <th class="th-sub">Volt</th><th class="th-sub">Amp</th><th class="th-sub">kW</th><th class="th-sub">GWh</th>
        {{-- Outgoing 2.1 Spare --}}
        <th class="th-sub">Volt</th><th class="th-sub">Amp</th><th class="th-sub">kW</th><th class="th-sub">GWh</th>
        {{-- Outgoing 2.2 Riser 1 --}}
        <th class="th-sub">Volt</th><th class="th-sub">Amp</th><th class="th-sub">kW</th><th class="th-sub">GWh</th><th class="th-sub">PF</th><th class="th-sub">Hz</th>
        {{-- Outgoing 2.2 Riser 5 --}}
        <th class="th-sub">Volt</th><th class="th-sub">Amp</th><th class="th-sub">kW</th><th class="th-sub">GWh</th><th class="th-sub">PF</th><th class="th-sub">Hz</th>
        {{-- Capacitor --}}
        <th class="th-sub">PF</th>
    </tr>

    @foreach($rows as $i => $r)
    <tr>
        <td style="text-align:center;">{{ $i + 1 }}</td>
        <td style="text-align:center;">{{ $r->tanggal->format('d/m/Y') }}</td>

        {{-- 1. Incoming Trafo --}}
        <td class="num">{{ $r->val('incoming_trafo', 'voltage_3p') }}</td>
        <td class="num">{{ $r->val('incoming_trafo', 'current') }}</td>
        <td class="num">{{ $r->val('incoming_trafo', 'power') }}</td>
        <td class="num8">{{ $r->val('incoming_trafo', 'energy') }}</td>
        <td class="num">{{ $r->val('incoming_trafo', 'power_factor') }}</td>
        <td class="num">{{ $r->val('incoming_trafo', 'frequency') }}</td>

        {{-- 2. Outgoing 1.2 Riser 3 --}}
        <td class="num">{{ $r->val('outgoing_1_2', 'riser_3', 'voltage_3p') }}</td>
        <td class="num">{{ $r->val('outgoing_1_2', 'riser_3', 'current') }}</td>
        <td class="num">{{ $r->val('outgoing_1_2', 'riser_3', 'power') }}</td>
        <td class="num8">{{ $r->val('outgoing_1_2', 'riser_3', 'energy') }}</td>
        <td class="num">{{ $r->val('outgoing_1_2', 'riser_3', 'power_factor') }}</td>
        <td class="num">{{ $r->val('outgoing_1_2', 'riser_3', 'frequency') }}</td>

        {{-- 2. Outgoing 1.2 OV AC Sky Lobby --}}
        <td class="num">{{ $r->val('outgoing_1_2', 'ov_ac_sky_lobby', 'voltage_3p') }}</td>
        <td class="num">{{ $r->val('outgoing_1_2', 'ov_ac_sky_lobby', 'current') }}</td>
        <td class="num">{{ $r->val('outgoing_1_2', 'ov_ac_sky_lobby', 'power') }}</td>
        <td class="num8">{{ $r->val('outgoing_1_2', 'ov_ac_sky_lobby', 'energy') }}</td>
        <td class="num">{{ $r->val('outgoing_1_2', 'ov_ac_sky_lobby', 'power_factor') }}</td>
        <td class="num">{{ $r->val('outgoing_1_2', 'ov_ac_sky_lobby', 'frequency') }}</td>

        {{-- 2. Outgoing 1.2 Ac R. Panel & Trafo --}}
        <td class="num">{{ $r->val('outgoing_1_2', 'ac_r_panel_trafo', 'voltage_3p') }}</td>
        <td class="num">{{ $r->val('outgoing_1_2', 'ac_r_panel_trafo', 'current') }}</td>
        <td class="num">{{ $r->val('outgoing_1_2', 'ac_r_panel_trafo', 'power') }}</td>
        <td class="num8">{{ $r->val('outgoing_1_2', 'ac_r_panel_trafo', 'energy') }}</td>
        <td class="num">{{ $r->val('outgoing_1_2', 'ac_r_panel_trafo', 'power_factor') }}</td>
        <td class="num">{{ $r->val('outgoing_1_2', 'ac_r_panel_trafo', 'frequency') }}</td>

        {{-- 3. Outgoing 1.1 Riser 4 --}}
        <td class="num">{{ $r->val('outgoing_1_1', 'riser_4', 'voltage_3p') }}</td>
        <td class="num">{{ $r->val('outgoing_1_1', 'riser_4', 'current') }}</td>
        <td class="num">{{ $r->val('outgoing_1_1', 'riser_4', 'power') }}</td>
        <td class="num8">{{ $r->val('outgoing_1_1', 'riser_4', 'energy') }}</td>
        <td class="num">{{ $r->val('outgoing_1_1', 'riser_4', 'power_factor') }}</td>
        <td class="num">{{ $r->val('outgoing_1_1', 'riser_4', 'frequency') }}</td>

        {{-- 4. Outgoing 2.1 Riser 2 --}}
        <td class="num">{{ $r->val('outgoing_2_1', 'riser_2', 'voltage_3p') }}</td>
        <td class="num">{{ $r->val('outgoing_2_1', 'riser_2', 'current') }}</td>
        <td class="num">{{ $r->val('outgoing_2_1', 'riser_2', 'power') }}</td>
        <td class="num8">{{ $r->val('outgoing_2_1', 'riser_2', 'energy') }}</td>

        {{-- 4. Outgoing 2.1 DB Fire 2 --}}
        <td class="num">{{ $r->val('outgoing_2_1', 'db_fire_2_lvl_12', 'voltage_3p') }}</td>
        <td class="num">{{ $r->val('outgoing_2_1', 'db_fire_2_lvl_12', 'current') }}</td>
        <td class="num">{{ $r->val('outgoing_2_1', 'db_fire_2_lvl_12', 'power') }}</td>
        <td class="num8">{{ $r->val('outgoing_2_1', 'db_fire_2_lvl_12', 'energy') }}</td>

        {{-- 4. Outgoing 2.1 Spare --}}
        <td class="num">{{ $r->val('outgoing_2_1', 'spare', 'voltage_3p') }}</td>
        <td class="num">{{ $r->val('outgoing_2_1', 'spare', 'current') }}</td>
        <td class="num">{{ $r->val('outgoing_2_1', 'spare', 'power') }}</td>
        <td class="num8">{{ $r->val('outgoing_2_1', 'spare', 'energy') }}</td>

        {{-- 5. Outgoing 2.2 Riser 1 --}}
        <td class="num">{{ $r->val('outgoing_2_2', 'riser_1', 'voltage_3p') }}</td>
        <td class="num">{{ $r->val('outgoing_2_2', 'riser_1', 'current') }}</td>
        <td class="num">{{ $r->val('outgoing_2_2', 'riser_1', 'power') }}</td>
        <td class="num8">{{ $r->val('outgoing_2_2', 'riser_1', 'energy') }}</td>
        <td class="num">{{ $r->val('outgoing_2_2', 'riser_1', 'power_factor') }}</td>
        <td class="num">{{ $r->val('outgoing_2_2', 'riser_1', 'frequency') }}</td>

        {{-- 5. Outgoing 2.2 Riser 5 --}}
        <td class="num">{{ $r->val('outgoing_2_2', 'riser_5', 'voltage_3p') }}</td>
        <td class="num">{{ $r->val('outgoing_2_2', 'riser_5', 'current') }}</td>
        <td class="num">{{ $r->val('outgoing_2_2', 'riser_5', 'power') }}</td>
        <td class="num8">{{ $r->val('outgoing_2_2', 'riser_5', 'energy') }}</td>
        <td class="num">{{ $r->val('outgoing_2_2', 'riser_5', 'power_factor') }}</td>
        <td class="num">{{ $r->val('outgoing_2_2', 'riser_5', 'frequency') }}</td>

        {{-- 6. Capacitor --}}
        <td class="num">{{ $r->val('incoming_capacitor', 'power_factor') }}</td>

        <td>{{ $r->user?->name ?? 'User Terhapus' }}</td>
        <td style="text-align:center;">{{ $r->isVerified() ? 'Terverifikasi' : 'Menunggu' }}</td>
    </tr>
    @endforeach
</table>
</body>
</html>
