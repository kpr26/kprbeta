@php
    $n = fn ($v, $d = 2) => ($v !== null && $v !== '') ? number_format((float)$v, $d, ',', '.') : '—';
    $periodeLabel = $startDate->translatedFormat('d M Y') . ' s/d ' . $endDate->translatedFormat('d M Y');

    // Hitung Metrik Statistik Interval
    $vList = [];
    $pList = [];
    $cList = [];
    $pfList = [];
    $chartLabels = [];
    $chartVolt = [];
    $chartPower = [];
    $chartCurrent = [];
    $chartPF = [];

    foreach ($rows as $r) {
        $v = $r->val('incoming_trafo', 'voltage_3p');
        $p = $r->val('incoming_trafo', 'power');
        $c = $r->val('incoming_trafo', 'current');
        $pf = $r->val('incoming_capacitor', 'power_factor');

        if ($v !== null && $v !== '') $vList[] = (float)$v;
        if ($p !== null && $p !== '') $pList[] = (float)$p;
        if ($c !== null && $c !== '') $cList[] = (float)$c;
        if ($pf !== null && $pf !== '') $pfList[] = (float)$pf;

        $chartLabels[] = $r->tanggal->translatedFormat('d M');
        $chartVolt[] = $v !== null && $v !== '' ? (float)$v : null;
        $chartPower[] = $p !== null && $p !== '' ? (float)$p : null;
        $chartCurrent[] = $c !== null && $c !== '' ? (float)$c : null;
        $chartPF[] = $pf !== null && $pf !== '' ? (float)$pf : null;
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
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Logsheet_LVSB_Public_Area_{{ $startDate->format('Ymd') }}_{{ $endDate->format('Ymd') }}</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<style>
    @page { size: A4 landscape; margin: 8mm; }
    * { box-sizing: border-box; }
    body { font-family: 'Segoe UI', Arial, sans-serif; color: #111; font-size: 7pt; margin: 0; line-height: 1.25; }
    .bar { position: sticky; top: 0; background: #0f172a; padding: 8px 16px; display: flex; gap: 10px; justify-content: flex-end; z-index: 99; }
    .bar button { background: #d97706; color: #fff; border: 0; padding: 6px 14px; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 8pt; }
    .page { padding: 10px; }
    h1 { font-size: 12pt; margin: 0; color: #b45309; }
    .sub { color: #555; margin: 2px 0 8px; font-size: 7.5pt; }

    .section-title { font-size: 8.5pt; font-weight: 700; color: #0f172a; margin: 8px 0 4px; border-left: 3px solid #d97706; padding-left: 6px; text-transform: uppercase; }

    /* Summary cards */
    .sum-grid { display: flex; gap: 8px; margin-bottom: 8px; }
    .sum-card { flex: 1; border: 1px solid #cbd5e1; border-radius: 5px; padding: 6px 8px; background: #f8fafc; }
    .sum-card small { display: block; font-size: 6.5pt; color: #64748b; text-transform: uppercase; font-weight: 600; }
    .sum-card b { display: block; font-size: 10pt; color: #0f172a; margin-top: 1px; }

    table { width: 100%; border-collapse: collapse; font-size: 6.5pt; margin-bottom: 8px; }
    th, td { border: 1px solid #64748b; padding: 3px 4px; text-align: center; }
    th { background: #1e293b; color: #fff; }
    .th-sub { background: #334155; font-size: 6pt; }
    td.n { text-align: right; font-variant-numeric: tabular-nums; }
    td.c { text-align: center; }
    tfoot td { font-weight: bold; background: #fef3c7; }

    .chart-container { border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px; background: #ffffff; margin-bottom: 10px; page-break-inside: avoid; }

    .sign { display: flex; justify-content: space-between; margin-top: 20px; text-align: center; font-size: 8pt; page-break-inside: avoid; }
    .sign div { width: 220px; }
    .sign .line { margin-top: 45px; border-top: 1px solid #000; padding-top: 3px; }

    @media print { 
        .bar { display: none; } 
        .page { padding: 0; } 
        th { -webkit-print-color-adjust: exact; print-color-adjust: exact; } 
        tfoot td { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
    }
</style>
</head>
<body>
<div class="bar">
    <button onclick="window.print()">⬇ Simpan sebagai PDF / Cetak Laporan</button>
</div>
<div class="page">
    <div style="display:flex; justify-content:space-between; align-items:flex-start; border-bottom:2px solid #d97706; padding-bottom:6px; margin-bottom:8px;">
        <div>
            <h1>LAPORAN LOGSHEET LVSB (LOW VOLTAGE SWITCHBOARD) — PUBLIC AREA</h1>
            <p class="sub">Rentang Interval: <b>{{ $periodeLabel }}</b> (Maksimal 12 Bulan)</p>
        </div>
        <div style="text-align:right; font-size:7.5pt; color:#64748b;">
            <div>Dicetak: {{ now()->translatedFormat('d F Y H:i') }}</div>
            <div>Petugas: <b>{{ auth()->user()->name }}</b></div>
        </div>
    </div>

    {{-- HIGHLIGHT CARDS --}}
    <div class="sum-grid">
        <div class="sum-card" style="border-left:3px solid #f59e0b;">
            <small>Avg Voltage Trafo</small>
            <b>{{ $n($avgVolt, 1) }} V</b>
        </div>
        <div class="sum-card" style="border-left:3px solid #ef4444;">
            <small>Beban Puncak (Max kW)</small>
            <b>{{ $n($maxPower, 2) }} kW</b>
        </div>
        <div class="sum-card" style="border-left:3px solid #3b82f6;">
            <small>Rata-rata Daya (Avg kW)</small>
            <b>{{ $n($avgPower, 2) }} kW</b>
        </div>
        <div class="sum-card" style="border-left:3px solid #8b5cf6;">
            <small>Arus Maksimum (Max A)</small>
            <b>{{ $n($maxCurr, 2) }} A</b>
        </div>
        <div class="sum-card" style="border-left:3px solid #10b981;">
            <small>Avg Power Factor Kapasitor</small>
            <b>{{ $n($avgPf, 2) }}</b>
        </div>
        <div class="sum-card" style="border-left:3px solid #64748b;">
            <small>Total Hari Pencatatan</small>
            <b>{{ $rows->count() }} hari</b>
        </div>
    </div>

    {{-- BAGIAN 1: RANGKUMAN DALAM BENTUK TABEL --}}
    <div class="section-title">I. Tabel Rangkuman Statistik Panel LVSB (Interval {{ $periodeLabel }})</div>
    <table style="width:100%; margin-bottom:10px;">
        <thead>
            <tr>
                <th style="width:30px;">No</th>
                <th style="text-align:left;">Parameter Kunci</th>
                <th>Satuan</th>
                <th>Rata-rata (Average)</th>
                <th>Tertinggi (Maximum)</th>
                <th>Terendah (Minimum)</th>
                <th style="text-align:left;">Keterangan Parameter</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="c">1</td>
                <td style="font-weight:600; text-align:left;">Incoming Trafo Voltage 3-P</td>
                <td class="c">Volt</td>
                <td class="n" style="font-weight:700; color:#b45309;">{{ $n($avgVolt, 1) }}</td>
                <td class="n">{{ $n($maxVolt, 1) }}</td>
                <td class="n">{{ $n($minVolt, 1) }}</td>
                <td style="text-align:left; color:#475569;">Tegangan sumber Incoming Trafo Apart A/B</td>
            </tr>
            <tr>
                <td class="c">2</td>
                <td style="font-weight:600; text-align:left;">Incoming Trafo Power (kW Total)</td>
                <td class="c">kW</td>
                <td class="n" style="font-weight:700; color:#0284c7;">{{ $n($avgPower, 2) }}</td>
                <td class="n" style="font-weight:700; color:#ef4444;">{{ $n($maxPower, 2) }}</td>
                <td class="n">{{ $n($minPower, 2) }}</td>
                <td style="text-align:left; color:#475569;">Beban daya aktif trafo gedung</td>
            </tr>
            <tr>
                <td class="c">3</td>
                <td style="font-weight:600; text-align:left;">Incoming Trafo Current (Arus)</td>
                <td class="c">Ampere</td>
                <td class="n">{{ $n($avgCurr, 2) }}</td>
                <td class="n" style="font-weight:700;">{{ $n($maxCurr, 2) }}</td>
                <td class="n">{{ $n($minCurr, 2) }}</td>
                <td style="text-align:left; color:#475569;">Arus listrik incoming trafo</td>
            </tr>
            <tr>
                <td class="c">4</td>
                <td style="font-weight:600; text-align:left;">Capacitor Bank Power Factor (PF)</td>
                <td class="c">Cos φ</td>
                <td class="n" style="font-weight:700; color:#059669;">{{ $n($avgPf, 2) }}</td>
                <td class="n">{{ $n($maxPf, 2) }}</td>
                <td class="n">{{ $n($minPf, 2) }}</td>
                <td style="text-align:left; color:#475569;">Efisiensi kompensasi daya reaktif kapasitor bank</td>
            </tr>
        </tbody>
    </table>

    {{-- BAGIAN 2: RANGKUMAN DALAM BENTUK GRAFIK --}}
    <div class="section-title">II. Grafik Tren Daya (kW), Tegangan (V), & PF Kapasitor</div>
    <div class="chart-container">
        <div style="height:150px; width:100%;">
            <canvas id="chartLvsbPdf"></canvas>
        </div>
    </div>

    {{-- BAGIAN 3: TABEL RINCIAN PENCATATAN HARIAN LENGKAP --}}
    <div class="section-title">III. Rincian Seluruh Pencatatan Harian Feeder LVSB</div>
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
                {{-- Out 1.2 AC Panel --}}
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
                <td class="c" style="white-space:nowrap;">{{ $r->tanggal->format('d/m') }}</td>

                {{-- Trafo --}}
                <td class="n">{{ $n($r->val('incoming_trafo', 'voltage_3p'), 0) }}</td>
                <td class="n">{{ $n($r->val('incoming_trafo', 'current'), 1) }}</td>
                <td class="n" style="font-weight:600;">{{ $n($r->val('incoming_trafo', 'power'), 1) }}</td>
                <td class="n">{{ $n($r->val('incoming_trafo', 'energy'), 4) }}</td>
                <td class="n">{{ $n($r->val('incoming_trafo', 'power_factor'), 2) }}</td>
                <td class="n">{{ $n($r->val('incoming_trafo', 'frequency'), 1) }}</td>

                {{-- Out 1.2 Riser 3 --}}
                <td class="n">{{ $n($r->val('outgoing_1_2', 'riser_3', 'voltage_3p'), 0) }}</td>
                <td class="n">{{ $n($r->val('outgoing_1_2', 'riser_3', 'current'), 1) }}</td>
                <td class="n">{{ $n($r->val('outgoing_1_2', 'riser_3', 'power'), 1) }}</td>
                <td class="n">{{ $n($r->val('outgoing_1_2', 'riser_3', 'energy'), 4) }}</td>
                <td class="n">{{ $n($r->val('outgoing_1_2', 'riser_3', 'power_factor'), 2) }}</td>
                <td class="n">{{ $n($r->val('outgoing_1_2', 'riser_3', 'frequency'), 1) }}</td>

                {{-- Out 1.2 OV AC --}}
                <td class="n">{{ $n($r->val('outgoing_1_2', 'ov_ac_sky_lobby', 'voltage_3p'), 0) }}</td>
                <td class="n">{{ $n($r->val('outgoing_1_2', 'ov_ac_sky_lobby', 'current'), 1) }}</td>
                <td class="n">{{ $n($r->val('outgoing_1_2', 'ov_ac_sky_lobby', 'power'), 1) }}</td>
                <td class="n">{{ $n($r->val('outgoing_1_2', 'ov_ac_sky_lobby', 'energy'), 4) }}</td>
                <td class="n">{{ $n($r->val('outgoing_1_2', 'ov_ac_sky_lobby', 'power_factor'), 2) }}</td>
                <td class="n">{{ $n($r->val('outgoing_1_2', 'ov_ac_sky_lobby', 'frequency'), 1) }}</td>

                {{-- Out 1.2 AC Panel --}}
                <td class="n">{{ $n($r->val('outgoing_1_2', 'ac_r_panel_trafo', 'voltage_3p'), 0) }}</td>
                <td class="n">{{ $n($r->val('outgoing_1_2', 'ac_r_panel_trafo', 'current'), 1) }}</td>
                <td class="n">{{ $n($r->val('outgoing_1_2', 'ac_r_panel_trafo', 'power'), 1) }}</td>
                <td class="n">{{ $n($r->val('outgoing_1_2', 'ac_r_panel_trafo', 'energy'), 4) }}</td>
                <td class="n">{{ $n($r->val('outgoing_1_2', 'ac_r_panel_trafo', 'power_factor'), 2) }}</td>
                <td class="n">{{ $n($r->val('outgoing_1_2', 'ac_r_panel_trafo', 'frequency'), 1) }}</td>

                {{-- Out 1.1 Riser 4 --}}
                <td class="n">{{ $n($r->val('outgoing_1_1', 'riser_4', 'voltage_3p'), 0) }}</td>
                <td class="n">{{ $n($r->val('outgoing_1_1', 'riser_4', 'current'), 1) }}</td>
                <td class="n">{{ $n($r->val('outgoing_1_1', 'riser_4', 'power'), 1) }}</td>
                <td class="n">{{ $n($r->val('outgoing_1_1', 'riser_4', 'energy'), 4) }}</td>
                <td class="n">{{ $n($r->val('outgoing_1_1', 'riser_4', 'power_factor'), 2) }}</td>
                <td class="n">{{ $n($r->val('outgoing_1_1', 'riser_4', 'frequency'), 1) }}</td>

                {{-- Out 2.1 Riser 2 --}}
                <td class="n">{{ $n($r->val('outgoing_2_1', 'riser_2', 'voltage_3p'), 0) }}</td>
                <td class="n">{{ $n($r->val('outgoing_2_1', 'riser_2', 'current'), 1) }}</td>
                <td class="n">{{ $n($r->val('outgoing_2_1', 'riser_2', 'power'), 1) }}</td>
                <td class="n">{{ $n($r->val('outgoing_2_1', 'riser_2', 'energy'), 4) }}</td>

                {{-- Out 2.1 DB Fire 2 --}}
                <td class="n">{{ $n($r->val('outgoing_2_1', 'db_fire_2_lvl_12', 'voltage_3p'), 0) }}</td>
                <td class="n">{{ $n($r->val('outgoing_2_1', 'db_fire_2_lvl_12', 'current'), 1) }}</td>
                <td class="n">{{ $n($r->val('outgoing_2_1', 'db_fire_2_lvl_12', 'power'), 1) }}</td>
                <td class="n">{{ $n($r->val('outgoing_2_1', 'db_fire_2_lvl_12', 'energy'), 4) }}</td>

                {{-- Out 2.1 Spare --}}
                <td class="n">{{ $n($r->val('outgoing_2_1', 'spare', 'voltage_3p'), 0) }}</td>
                <td class="n">{{ $n($r->val('outgoing_2_1', 'spare', 'current'), 1) }}</td>
                <td class="n">{{ $n($r->val('outgoing_2_1', 'spare', 'power'), 1) }}</td>
                <td class="n">{{ $n($r->val('outgoing_2_1', 'spare', 'energy'), 4) }}</td>

                {{-- Out 2.2 Riser 1 --}}
                <td class="n">{{ $n($r->val('outgoing_2_2', 'riser_1', 'voltage_3p'), 0) }}</td>
                <td class="n">{{ $n($r->val('outgoing_2_2', 'riser_1', 'current'), 1) }}</td>
                <td class="n">{{ $n($r->val('outgoing_2_2', 'riser_1', 'power'), 1) }}</td>
                <td class="n">{{ $n($r->val('outgoing_2_2', 'riser_1', 'energy'), 4) }}</td>
                <td class="n">{{ $n($r->val('outgoing_2_2', 'riser_1', 'power_factor'), 2) }}</td>
                <td class="n">{{ $n($r->val('outgoing_2_2', 'riser_1', 'frequency'), 1) }}</td>

                {{-- Out 2.2 Riser 5 --}}
                <td class="n">{{ $n($r->val('outgoing_2_2', 'riser_5', 'voltage_3p'), 0) }}</td>
                <td class="n">{{ $n($r->val('outgoing_2_2', 'riser_5', 'current'), 1) }}</td>
                <td class="n">{{ $n($r->val('outgoing_2_2', 'riser_5', 'power'), 1) }}</td>
                <td class="n">{{ $n($r->val('outgoing_2_2', 'riser_5', 'energy'), 4) }}</td>
                <td class="n">{{ $n($r->val('outgoing_2_2', 'riser_5', 'power_factor'), 2) }}</td>
                <td class="n">{{ $n($r->val('outgoing_2_2', 'riser_5', 'frequency'), 1) }}</td>

                {{-- Cap --}}
                <td class="n" style="font-weight:600; color:#059669;">{{ $n($r->val('incoming_capacitor', 'power_factor'), 2) }}</td>

                <td class="c" style="white-space:nowrap;">{{ $r->user?->name ?? '—' }}</td>
            </tr>
        @empty
            <tr><td colspan="46" class="c">Tidak ada data logsheet LVSB pada interval ini.</td></tr>
        @endforelse
        </tbody>
    </table>

    <div class="sign">
        <div>Dibuat oleh,<div class="line">Teknisi Pengawas</div></div>
        <div>Diperiksa &amp; Disetujui,<div class="line">{{ auth()->user()->name }} (Supervisor / Admin)</div></div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const labels = @json($chartLabels);
        const dataPower = @json($chartPower);
        const dataPF = @json($chartPF);

        const ctx = document.getElementById('chartLvsbPdf');
        if (ctx && labels.length > 0) {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Incoming Power (kW)',
                            data: dataPower,
                            borderColor: '#d97706',
                            backgroundColor: 'rgba(217,119,6,0.1)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.2,
                            yAxisID: 'y',
                            pointRadius: labels.length > 35 ? 0 : 2,
                        },
                        {
                            label: 'Capacitor PF',
                            data: dataPF,
                            borderColor: '#10b981',
                            borderWidth: 2,
                            fill: false,
                            tension: 0.2,
                            yAxisID: 'y1',
                            pointRadius: labels.length > 35 ? 0 : 2,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: false,
                    plugins: {
                        legend: { position: 'top', labels: { boxWidth: 10, font: { size: 8 } } }
                    },
                    scales: {
                        x: { ticks: { font: { size: 7 }, maxRotation: 45 } },
                        y: { 
                            type: 'linear', 
                            position: 'left', 
                            title: { display: true, text: 'Power (kW)', font: { size: 7 } },
                            ticks: { font: { size: 7 } } 
                        },
                        y1: { 
                            type: 'linear', 
                            position: 'right', 
                            min: 0.7, 
                            max: 1.0, 
                            title: { display: true, text: 'PF', font: { size: 7 } },
                            grid: { drawOnChartArea: false },
                            ticks: { font: { size: 7 } } 
                        }
                    }
                }
            });
        }

        setTimeout(() => window.print(), 500);
    });
</script>
</body>
</html>
