@php
    $n = fn ($v, $d = 2) => ($v !== null && $v !== '') ? number_format((float)$v, $d, ',', '.') : '—';
    $bulan = \Carbon\Carbon::create($year, $month)->translatedFormat('F Y');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Logsheet_LVSB_Public_Area_{{ sprintf('%04d-%02d', $year, $month) }}</title>
<style>
    @page { size: A4 landscape; margin: 8mm; }
    * { box-sizing: border-box; }
    body { font-family: 'Segoe UI', Arial, sans-serif; color: #111; font-size: 7pt; margin: 0; }
    .bar { position: sticky; top: 0; background: #0f172a; padding: 10px 18px; display: flex; gap: 10px; justify-content: flex-end; }
    .bar button { background: #6366f1; color: #fff; border: 0; padding: 6px 14px; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 8pt; }
    .page { padding: 10px; }
    h1 { font-size: 12pt; margin: 0; }
    .sub { color: #555; margin: 2px 0 10px; font-size: 7.5pt; }
    table { width: 100%; border-collapse: collapse; font-size: 6.5pt; }
    th, td { border: 1px solid #64748b; padding: 3px 4px; text-align: center; }
    th { background: #1e293b; color: #fff; }
    .th-sub { background: #334155; font-size: 6pt; }
    td.n { text-align: right; font-variant-numeric: tabular-nums; }
    td.c { text-align: center; }
    .sign { display: flex; justify-content: space-between; margin-top: 25px; text-align: center; font-size: 8pt; }
    .sign div { width: 220px; }
    .sign .line { margin-top: 50px; border-top: 1px solid #000; padding-top: 3px; }
    @media print { .bar { display: none; } .page { padding: 0; } th { -webkit-print-color-adjust: exact; print-color-adjust: exact; } }
</style>
</head>
<body>
<div class="bar">
    <button onclick="window.print()">⬇ Simpan sebagai PDF / Cetak</button>
</div>
<div class="page">
    <h1>LOGSHEET LVSB (LOW VOLTAGE SWITCHBOARD) — PUBLIC AREA</h1>
    <p class="sub">Periode: {{ $bulan }} · Dicetak: {{ now()->format('d/m/Y H:i') }} oleh {{ auth()->user()->name }}</p>

    <table>
        <thead>
            <tr>
                <th rowspan="2" style="width:20px;">No</th>
                <th rowspan="2" style="width:45px;">Tanggal</th>
                <th colspan="6">INCOMING TRAFO</th>
                <th colspan="6">OUT 1.2 RISER 3</th>
                <th colspan="6">OUT 1.2 OV AC</th>
                <th colspan="6">OUT 1.2 AC PANEL</th>
                <th colspan="6">OUT 1.1 RISER 4</th>
                <th colspan="4">OUT 2.1 RISER 2</th>
                <th colspan="4">OUT 2.1 DB FIRE 2</th>
                <th colspan="4">OUT 2.1 SPARE</th>
                <th colspan="6">OUT 2.2 RISER 1</th>
                <th colspan="6">OUT 2.2 RISER 5</th>
                <th>CAP</th>
                <th rowspan="2">Petugas</th>
            </tr>
            <tr>
                {{-- Trafo --}}
                <th class="th-sub">V</th><th class="th-sub">A</th><th class="th-sub">kW</th><th class="th-sub">GWh</th><th class="th-sub">PF</th><th class="th-sub">Hz</th>
                {{-- Out 1.2 Riser 3 --}}
                <th class="th-sub">V</th><th class="th-sub">A</th><th class="th-sub">kW</th><th class="th-sub">GWh</th><th class="th-sub">PF</th><th class="th-sub">Hz</th>
                {{-- Out 1.2 OV AC --}}
                <th class="th-sub">V</th><th class="th-sub">A</th><th class="th-sub">kW</th><th class="th-sub">GWh</th><th class="th-sub">PF</th><th class="th-sub">Hz</th>
                {{-- Out 1.2 Ac Panel --}}
                <th class="th-sub">V</th><th class="th-sub">A</th><th class="th-sub">kW</th><th class="th-sub">GWh</th><th class="th-sub">PF</th><th class="th-sub">Hz</th>
                {{-- Out 1.1 Riser 4 --}}
                <th class="th-sub">V</th><th class="th-sub">A</th><th class="th-sub">kW</th><th class="th-sub">GWh</th><th class="th-sub">PF</th><th class="th-sub">Hz</th>
                {{-- Out 2.1 Riser 2 --}}
                <th class="th-sub">V</th><th class="th-sub">A</th><th class="th-sub">kW</th><th class="th-sub">GWh</th>
                {{-- Out 2.1 DB Fire 2 --}}
                <th class="th-sub">V</th><th class="th-sub">A</th><th class="th-sub">kW</th><th class="th-sub">GWh</th>
                {{-- Out 2.1 Spare --}}
                <th class="th-sub">V</th><th class="th-sub">A</th><th class="th-sub">kW</th><th class="th-sub">GWh</th>
                {{-- Out 2.2 Riser 1 --}}
                <th class="th-sub">V</th><th class="th-sub">A</th><th class="th-sub">kW</th><th class="th-sub">GWh</th><th class="th-sub">PF</th><th class="th-sub">Hz</th>
                {{-- Out 2.2 Riser 5 --}}
                <th class="th-sub">V</th><th class="th-sub">A</th><th class="th-sub">kW</th><th class="th-sub">GWh</th><th class="th-sub">PF</th><th class="th-sub">Hz</th>
                {{-- Cap --}}
                <th class="th-sub">PF</th>
            </tr>
        </thead>
        <tbody>
        @forelse($rows as $i => $r)
            <tr>
                <td class="c">{{ $i + 1 }}</td>
                <td class="c">{{ $r->tanggal->format('d/m') }}</td>

                {{-- Trafo --}}
                <td class="n">{{ $n($r->val('incoming_trafo', 'voltage_3p'), 1) }}</td>
                <td class="n">{{ $n($r->val('incoming_trafo', 'current')) }}</td>
                <td class="n">{{ $n($r->val('incoming_trafo', 'power')) }}</td>
                <td class="n">{{ $n($r->val('incoming_trafo', 'energy'), 4) }}</td>
                <td class="n">{{ $n($r->val('incoming_trafo', 'power_factor')) }}</td>
                <td class="n">{{ $n($r->val('incoming_trafo', 'frequency'), 1) }}</td>

                {{-- Out 1.2 Riser 3 --}}
                <td class="n">{{ $n($r->val('outgoing_1_2', 'riser_3', 'voltage_3p'), 1) }}</td>
                <td class="n">{{ $n($r->val('outgoing_1_2', 'riser_3', 'current')) }}</td>
                <td class="n">{{ $n($r->val('outgoing_1_2', 'riser_3', 'power')) }}</td>
                <td class="n">{{ $n($r->val('outgoing_1_2', 'riser_3', 'energy'), 4) }}</td>
                <td class="n">{{ $n($r->val('outgoing_1_2', 'riser_3', 'power_factor')) }}</td>
                <td class="n">{{ $n($r->val('outgoing_1_2', 'riser_3', 'frequency'), 1) }}</td>

                {{-- Out 1.2 OV AC --}}
                <td class="n">{{ $n($r->val('outgoing_1_2', 'ov_ac_sky_lobby', 'voltage_3p'), 1) }}</td>
                <td class="n">{{ $n($r->val('outgoing_1_2', 'ov_ac_sky_lobby', 'current')) }}</td>
                <td class="n">{{ $n($r->val('outgoing_1_2', 'ov_ac_sky_lobby', 'power')) }}</td>
                <td class="n">{{ $n($r->val('outgoing_1_2', 'ov_ac_sky_lobby', 'energy'), 4) }}</td>
                <td class="n">{{ $n($r->val('outgoing_1_2', 'ov_ac_sky_lobby', 'power_factor')) }}</td>
                <td class="n">{{ $n($r->val('outgoing_1_2', 'ov_ac_sky_lobby', 'frequency'), 1) }}</td>

                {{-- Out 1.2 Ac Panel --}}
                <td class="n">{{ $n($r->val('outgoing_1_2', 'ac_r_panel_trafo', 'voltage_3p'), 1) }}</td>
                <td class="n">{{ $n($r->val('outgoing_1_2', 'ac_r_panel_trafo', 'current')) }}</td>
                <td class="n">{{ $n($r->val('outgoing_1_2', 'ac_r_panel_trafo', 'power')) }}</td>
                <td class="n">{{ $n($r->val('outgoing_1_2', 'ac_r_panel_trafo', 'energy'), 4) }}</td>
                <td class="n">{{ $n($r->val('outgoing_1_2', 'ac_r_panel_trafo', 'power_factor')) }}</td>
                <td class="n">{{ $n($r->val('outgoing_1_2', 'ac_r_panel_trafo', 'frequency'), 1) }}</td>

                {{-- Out 1.1 Riser 4 --}}
                <td class="n">{{ $n($r->val('outgoing_1_1', 'riser_4', 'voltage_3p'), 1) }}</td>
                <td class="n">{{ $n($r->val('outgoing_1_1', 'riser_4', 'current')) }}</td>
                <td class="n">{{ $n($r->val('outgoing_1_1', 'riser_4', 'power')) }}</td>
                <td class="n">{{ $n($r->val('outgoing_1_1', 'riser_4', 'energy'), 4) }}</td>
                <td class="n">{{ $n($r->val('outgoing_1_1', 'riser_4', 'power_factor')) }}</td>
                <td class="n">{{ $n($r->val('outgoing_1_1', 'riser_4', 'frequency'), 1) }}</td>

                {{-- Out 2.1 Riser 2 --}}
                <td class="n">{{ $n($r->val('outgoing_2_1', 'riser_2', 'voltage_3p'), 1) }}</td>
                <td class="n">{{ $n($r->val('outgoing_2_1', 'riser_2', 'current')) }}</td>
                <td class="n">{{ $n($r->val('outgoing_2_1', 'riser_2', 'power')) }}</td>
                <td class="n">{{ $n($r->val('outgoing_2_1', 'riser_2', 'energy'), 4) }}</td>

                {{-- Out 2.1 DB Fire 2 --}}
                <td class="n">{{ $n($r->val('outgoing_2_1', 'db_fire_2_lvl_12', 'voltage_3p'), 1) }}</td>
                <td class="n">{{ $n($r->val('outgoing_2_1', 'db_fire_2_lvl_12', 'current')) }}</td>
                <td class="n">{{ $n($r->val('outgoing_2_1', 'db_fire_2_lvl_12', 'power')) }}</td>
                <td class="n">{{ $n($r->val('outgoing_2_1', 'db_fire_2_lvl_12', 'energy'), 4) }}</td>

                {{-- Out 2.1 Spare --}}
                <td class="n">{{ $n($r->val('outgoing_2_1', 'spare', 'voltage_3p'), 1) }}</td>
                <td class="n">{{ $n($r->val('outgoing_2_1', 'spare', 'current')) }}</td>
                <td class="n">{{ $n($r->val('outgoing_2_1', 'spare', 'power')) }}</td>
                <td class="n">{{ $n($r->val('outgoing_2_1', 'spare', 'energy'), 4) }}</td>

                {{-- Out 2.2 Riser 1 --}}
                <td class="n">{{ $n($r->val('outgoing_2_2', 'riser_1', 'voltage_3p'), 1) }}</td>
                <td class="n">{{ $n($r->val('outgoing_2_2', 'riser_1', 'current')) }}</td>
                <td class="n">{{ $n($r->val('outgoing_2_2', 'riser_1', 'power')) }}</td>
                <td class="n">{{ $n($r->val('outgoing_2_2', 'riser_1', 'energy'), 4) }}</td>
                <td class="n">{{ $n($r->val('outgoing_2_2', 'riser_1', 'power_factor')) }}</td>
                <td class="n">{{ $n($r->val('outgoing_2_2', 'riser_1', 'frequency'), 1) }}</td>

                {{-- Out 2.2 Riser 5 --}}
                <td class="n">{{ $n($r->val('outgoing_2_2', 'riser_5', 'voltage_3p'), 1) }}</td>
                <td class="n">{{ $n($r->val('outgoing_2_2', 'riser_5', 'current')) }}</td>
                <td class="n">{{ $n($r->val('outgoing_2_2', 'riser_5', 'power')) }}</td>
                <td class="n">{{ $n($r->val('outgoing_2_2', 'riser_5', 'energy'), 4) }}</td>
                <td class="n">{{ $n($r->val('outgoing_2_2', 'riser_5', 'power_factor')) }}</td>
                <td class="n">{{ $n($r->val('outgoing_2_2', 'riser_5', 'frequency'), 1) }}</td>

                {{-- Cap --}}
                <td class="n" style="font-weight:bold;">{{ $n($r->val('incoming_capacitor', 'power_factor')) }}</td>

                <td class="c">{{ $r->user?->name ?? '—' }}</td>
            </tr>
        @empty
            <tr><td colspan="60" style="text-align:center">Tidak ada data pencatatan LVSB pada periode ini.</td></tr>
        @endforelse
        </tbody>
    </table>

    <div class="sign">
        <div>Dibuat oleh,<div class="line">Teknisi Pengawas</div></div>
        <div>Diperiksa &amp; Disetujui,<div class="line">{{ auth()->user()->name }} (Supervisor / Admin)</div></div>
    </div>
</div>
<script>window.addEventListener('load', () => setTimeout(() => window.print(), 400));</script>
</body>
</html>
