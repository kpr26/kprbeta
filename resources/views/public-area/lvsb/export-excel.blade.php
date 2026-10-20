@php
    $n = fn ($v, $d = 2) => ($v !== null && $v !== '') ? number_format((float)$v, $d, ',', '.') : '';
    $judul = 'LOGSHEET LVSB PUBLIC AREA — ' . strtoupper(\Carbon\Carbon::create($year, $month)->translatedFormat('F Y'));
@endphp
<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel">
<head>
<meta charset="UTF-8">
<style>
    th, td { border: 1px solid #000; padding: 4px 6px; font-family: Calibri, Arial; font-size: 9pt; }
    th { background: #1e293b; color: #fff; font-weight: bold; text-align: center; }
    .th-sub { background: #334155; color: #fff; font-size: 8pt; }
    .num { mso-number-format: "\#\,\#\#0\.00"; text-align: right; }
    .num4 { mso-number-format: "\#\,\#\#0\.0000"; text-align: right; }
</style>
</head>
<body>
<table>
    <tr><td colspan="20" style="border:0;font-size:14pt;font-weight:bold">{{ $judul }}</td></tr>
    <tr><td colspan="20" style="border:0;font-size:10pt;">Dicetak: {{ now()->format('d/m/Y H:i') }} oleh {{ auth()->user()->name }}</td></tr>
    <tr><td colspan="20" style="border:0"></td></tr>

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
        <td class="num4">{{ $r->val('incoming_trafo', 'energy') }}</td>
        <td class="num">{{ $r->val('incoming_trafo', 'power_factor') }}</td>
        <td class="num">{{ $r->val('incoming_trafo', 'frequency') }}</td>

        {{-- 2. Outgoing 1.2 Riser 3 --}}
        <td class="num">{{ $r->val('outgoing_1_2', 'riser_3', 'voltage_3p') }}</td>
        <td class="num">{{ $r->val('outgoing_1_2', 'riser_3', 'current') }}</td>
        <td class="num">{{ $r->val('outgoing_1_2', 'riser_3', 'power') }}</td>
        <td class="num4">{{ $r->val('outgoing_1_2', 'riser_3', 'energy') }}</td>
        <td class="num">{{ $r->val('outgoing_1_2', 'riser_3', 'power_factor') }}</td>
        <td class="num">{{ $r->val('outgoing_1_2', 'riser_3', 'frequency') }}</td>

        {{-- 2. Outgoing 1.2 OV AC Sky Lobby --}}
        <td class="num">{{ $r->val('outgoing_1_2', 'ov_ac_sky_lobby', 'voltage_3p') }}</td>
        <td class="num">{{ $r->val('outgoing_1_2', 'ov_ac_sky_lobby', 'current') }}</td>
        <td class="num">{{ $r->val('outgoing_1_2', 'ov_ac_sky_lobby', 'power') }}</td>
        <td class="num4">{{ $r->val('outgoing_1_2', 'ov_ac_sky_lobby', 'energy') }}</td>
        <td class="num">{{ $r->val('outgoing_1_2', 'ov_ac_sky_lobby', 'power_factor') }}</td>
        <td class="num">{{ $r->val('outgoing_1_2', 'ov_ac_sky_lobby', 'frequency') }}</td>

        {{-- 2. Outgoing 1.2 Ac R. Panel & Trafo --}}
        <td class="num">{{ $r->val('outgoing_1_2', 'ac_r_panel_trafo', 'voltage_3p') }}</td>
        <td class="num">{{ $r->val('outgoing_1_2', 'ac_r_panel_trafo', 'current') }}</td>
        <td class="num">{{ $r->val('outgoing_1_2', 'ac_r_panel_trafo', 'power') }}</td>
        <td class="num4">{{ $r->val('outgoing_1_2', 'ac_r_panel_trafo', 'energy') }}</td>
        <td class="num">{{ $r->val('outgoing_1_2', 'ac_r_panel_trafo', 'power_factor') }}</td>
        <td class="num">{{ $r->val('outgoing_1_2', 'ac_r_panel_trafo', 'frequency') }}</td>

        {{-- 3. Outgoing 1.1 Riser 4 --}}
        <td class="num">{{ $r->val('outgoing_1_1', 'riser_4', 'voltage_3p') }}</td>
        <td class="num">{{ $r->val('outgoing_1_1', 'riser_4', 'current') }}</td>
        <td class="num">{{ $r->val('outgoing_1_1', 'riser_4', 'power') }}</td>
        <td class="num4">{{ $r->val('outgoing_1_1', 'riser_4', 'energy') }}</td>
        <td class="num">{{ $r->val('outgoing_1_1', 'riser_4', 'power_factor') }}</td>
        <td class="num">{{ $r->val('outgoing_1_1', 'riser_4', 'frequency') }}</td>

        {{-- 4. Outgoing 2.1 Riser 2 --}}
        <td class="num">{{ $r->val('outgoing_2_1', 'riser_2', 'voltage_3p') }}</td>
        <td class="num">{{ $r->val('outgoing_2_1', 'riser_2', 'current') }}</td>
        <td class="num">{{ $r->val('outgoing_2_1', 'riser_2', 'power') }}</td>
        <td class="num4">{{ $r->val('outgoing_2_1', 'riser_2', 'energy') }}</td>

        {{-- 4. Outgoing 2.1 DB Fire 2 --}}
        <td class="num">{{ $r->val('outgoing_2_1', 'db_fire_2_lvl_12', 'voltage_3p') }}</td>
        <td class="num">{{ $r->val('outgoing_2_1', 'db_fire_2_lvl_12', 'current') }}</td>
        <td class="num">{{ $r->val('outgoing_2_1', 'db_fire_2_lvl_12', 'power') }}</td>
        <td class="num4">{{ $r->val('outgoing_2_1', 'db_fire_2_lvl_12', 'energy') }}</td>

        {{-- 4. Outgoing 2.1 Spare --}}
        <td class="num">{{ $r->val('outgoing_2_1', 'spare', 'voltage_3p') }}</td>
        <td class="num">{{ $r->val('outgoing_2_1', 'spare', 'current') }}</td>
        <td class="num">{{ $r->val('outgoing_2_1', 'spare', 'power') }}</td>
        <td class="num4">{{ $r->val('outgoing_2_1', 'spare', 'energy') }}</td>

        {{-- 5. Outgoing 2.2 Riser 1 --}}
        <td class="num">{{ $r->val('outgoing_2_2', 'riser_1', 'voltage_3p') }}</td>
        <td class="num">{{ $r->val('outgoing_2_2', 'riser_1', 'current') }}</td>
        <td class="num">{{ $r->val('outgoing_2_2', 'riser_1', 'power') }}</td>
        <td class="num4">{{ $r->val('outgoing_2_2', 'riser_1', 'energy') }}</td>
        <td class="num">{{ $r->val('outgoing_2_2', 'riser_1', 'power_factor') }}</td>
        <td class="num">{{ $r->val('outgoing_2_2', 'riser_1', 'frequency') }}</td>

        {{-- 5. Outgoing 2.2 Riser 5 --}}
        <td class="num">{{ $r->val('outgoing_2_2', 'riser_5', 'voltage_3p') }}</td>
        <td class="num">{{ $r->val('outgoing_2_2', 'riser_5', 'current') }}</td>
        <td class="num">{{ $r->val('outgoing_2_2', 'riser_5', 'power') }}</td>
        <td class="num4">{{ $r->val('outgoing_2_2', 'riser_5', 'energy') }}</td>
        <td class="num">{{ $r->val('outgoing_2_2', 'riser_5', 'power_factor') }}</td>
        <td class="num">{{ $r->val('outgoing_2_2', 'riser_5', 'frequency') }}</td>

        {{-- 6. Capacitor --}}
        <td class="num">{{ $r->val('incoming_capacitor', 'power_factor') }}</td>

        <td>{{ $r->user?->name ?? 'User Terhapus' }}</td>
        <td style="text-align:center;">{{ $r->verified_at ? 'Terverifikasi' : 'Menunggu' }}</td>
    </tr>
    @endforeach
</table>
</body>
</html>
