@php
    $n = fn ($v, $d = 2) => ($v !== null && $v !== '') ? number_format((float)$v, $d, ',', '.') : '—';
    $periodeLabel = $startDate->translatedFormat('d M Y') . ' s/d ' . $endDate->translatedFormat('d M Y');

    // Hitung Metrik Statistik Interval
    $voltList = [];
    $currList = [];
    $chartLabels = [];
    $chartVolt = [];
    $chartCurr = [];

    foreach ($rows as $r) {
        if ($r->voltage_20kv !== null && $r->voltage_20kv !== '') $voltList[] = (float)$r->voltage_20kv;
        if ($r->current !== null && $r->current !== '') $currList[] = (float)$r->current;

        $chartLabels[] = $r->tanggal->translatedFormat('d M');
        $chartVolt[] = $r->voltage_20kv !== null && $r->voltage_20kv !== '' ? (float)$r->voltage_20kv : null;
        $chartCurr[] = $r->current !== null && $r->current !== '' ? (float)$r->current : null;
    }

    $avgVolt = count($voltList) > 0 ? round(array_sum($voltList) / count($voltList), 1) : null;
    $maxVolt = count($voltList) > 0 ? max($voltList) : null;
    $minVolt = count($voltList) > 0 ? min($voltList) : null;

    $avgCurr = count($currList) > 0 ? round(array_sum($currList) / count($currList), 2) : null;
    $maxCurr = count($currList) > 0 ? max($currList) : null;
    $minCurr = count($currList) > 0 ? min($currList) : null;
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Logsheet_MVSB_Incoming_PLN_{{ $startDate->format('Ymd') }}_{{ $endDate->format('Ymd') }}</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<style>
    @page { size: A4 portrait; margin: 12mm; }
    * { box-sizing: border-box; }
    body { font-family: 'Segoe UI', Arial, sans-serif; color: #111; font-size: 8.5pt; margin: 0; line-height: 1.3; }
    .bar { position: sticky; top: 0; background: #0f172a; padding: 10px 18px; display: flex; gap: 10px; justify-content: flex-end; z-index: 99; }
    .bar button { background: #7c3aed; color: #fff; border: 0; padding: 7px 16px; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 9pt; }
    .page { padding: 14px; }
    h1 { font-size: 13pt; margin: 0; color: #581c87; }
    .sub { color: #555; margin: 3px 0 12px; font-size: 8.5pt; }

    .section-title { font-size: 9.5pt; font-weight: 700; color: #0f172a; margin: 12px 0 6px; border-left: 3px solid #7c3aed; padding-left: 8px; text-transform: uppercase; }

    /* Summary cards */
    .sum-grid { display: flex; gap: 10px; margin-bottom: 12px; }
    .sum-card { flex: 1; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px 10px; background: #f8fafc; }
    .sum-card small { display: block; font-size: 7.5pt; color: #64748b; text-transform: uppercase; font-weight: 600; }
    .sum-card b { display: block; font-size: 12pt; color: #0f172a; margin-top: 2px; }

    table { width: 100%; border-collapse: collapse; font-size: 8pt; margin-top: 6px; margin-bottom: 12px; }
    th, td { border: 1px solid #cbd5e1; padding: 5px 8px; }
    th { background: #581c87; color: #fff; font-weight: 600; text-align: center; }
    td.n { text-align: right; font-variant-numeric: tabular-nums; }
    td.c { text-align: center; }
    tfoot td { font-weight: bold; background: #fef3c7; }

    .chart-container { border: 1px solid #cbd5e1; border-radius: 6px; padding: 10px; background: #ffffff; margin-bottom: 14px; page-break-inside: avoid; }

    .sign { display: flex; justify-content: space-between; margin-top: 30px; text-align: center; font-size: 8.5pt; page-break-inside: avoid; }
    .sign div { width: 200px; }
    .sign .line { margin-top: 55px; border-top: 1px solid #000; padding-top: 4px; }

    @media print { 
        .bar { display: none; } 
        .page { padding: 0; } 
        th { -webkit-print-color-adjust: exact; print-color-adjust: exact; } 
    }
</style>
</head>
<body>
<div class="bar">
    <button onclick="window.print()">⬇ Simpan sebagai PDF / Cetak Laporan</button>
</div>
<div class="page">
    <div style="display:flex; justify-content:space-between; align-items:flex-start; border-bottom:2px solid #7c3aed; padding-bottom:8px; margin-bottom:10px;">
        <div>
            <h1>LAPORAN LOGSHEET MVSB INCOMING FROM PLN — PUBLIC AREA</h1>
            <p class="sub">Rentang Interval: <b>{{ $periodeLabel }}</b> (Maksimal 12 Bulan)</p>
        </div>
        <div style="text-align:right; font-size:8pt; color:#64748b;">
            <div>Dicetak: {{ now()->translatedFormat('d F Y H:i') }}</div>
            <div>Petugas: <b>{{ auth()->user()->name }}</b></div>
        </div>
    </div>

    {{-- HIGHLIGHT CARDS --}}
    <div class="sum-grid">
        <div class="sum-card" style="border-left:4px solid #7c3aed;">
            <small>Avg Voltage 20kV</small>
            <b>{{ $n($avgVolt, 1) }} Volt</b>
        </div>
        <div class="sum-card" style="border-left:4px solid #ef4444;">
            <small>Max / Min Voltage</small>
            <b>{{ $n($maxVolt, 1) }} / {{ $n($minVolt, 1) }} V</b>
        </div>
        <div class="sum-card" style="border-left:4px solid #0284c7;">
            <small>Rata-rata Arus (Current)</small>
            <b>{{ $n($avgCurr, 2) }} Ampere</b>
        </div>
        <div class="sum-card" style="border-left:4px solid #10b981;">
            <small>Total Hari Pencatatan</small>
            <b>{{ $rows->count() }} hari tercatat</b>
        </div>
    </div>

    {{-- BAGIAN 1: RANGKUMAN DALAM BENTUK TABEL --}}
    <div class="section-title">I. Tabel Rangkuman Statistik Panel MVSB (Interval {{ $periodeLabel }})</div>
    <table>
        <thead>
            <tr>
                <th style="width:35px;">No</th>
                <th style="text-align:left;">Parameter MVSB PLN</th>
                <th>Satuan</th>
                <th>Rata-rata (Avg)</th>
                <th>Nilai Maksimum (Max)</th>
                <th>Nilai Minimum (Min)</th>
                <th style="text-align:left;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="c">1</td>
                <td style="font-weight:600; text-align:left;">Voltage 20kV Incoming</td>
                <td class="c">Volt</td>
                <td class="n" style="font-weight:700; color:#7c3aed;">{{ $n($avgVolt, 1) }}</td>
                <td class="n">{{ $n($maxVolt, 1) }}</td>
                <td class="n">{{ $n($minVolt, 1) }}</td>
                <td style="text-align:left; color:#475569;">Tegangan suplai menengah dari gardu PLN</td>
            </tr>
            <tr>
                <td class="c">2</td>
                <td style="font-weight:600; text-align:left;">Current (Arus Beban Incoming)</td>
                <td class="c">Ampere</td>
                <td class="n" style="font-weight:700; color:#0284c7;">{{ $n($avgCurr, 2) }}</td>
                <td class="n" style="font-weight:700;">{{ $n($maxCurr, 2) }}</td>
                <td class="n">{{ $n($minCurr, 2) }}</td>
                <td style="text-align:left; color:#475569;">Arus listrik incoming beban PLN</td>
            </tr>
        </tbody>
    </table>

    {{-- BAGIAN 2: RANGKUMAN DALAM BENTUK GRAFIK (INDIVIDU PER NILAI INPUTAN) --}}
    <div class="section-title">II. Grafik Tren Parameter MVSB (Individu: Tegangan 20kV & Arus Beban)</div>
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:12px;">
        <div class="chart-container" style="margin:0; padding:6px 8px;">
            <div style="font-size:8px; font-weight:bold; color:#7c3aed; margin-bottom:4px;">1. Tegangan Menengah 20kV Incoming (Volt)</div>
            <div style="height:120px; width:100%;">
                <canvas id="chartMvsbPdfVolt"></canvas>
            </div>
        </div>
        <div class="chart-container" style="margin:0; padding:6px 8px;">
            <div style="font-size:8px; font-weight:bold; color:#0284c7; margin-bottom:4px;">2. Arus Listrik Beban Operasional (Ampere)</div>
            <div style="height:120px; width:100%;">
                <canvas id="chartMvsbPdfCurr"></canvas>
            </div>
        </div>
    </div>

    {{-- BAGIAN 3: TABEL RINCIAN PENCATATAN HARIAN --}}
    <div class="section-title">III. Rincian Seluruh Pencatatan Harian MVSB</div>
    <table>
        <thead>
            <tr>
                <th style="width:30px;">No</th>
                <th style="width:80px;">Tanggal</th>
                <th style="width:120px;">Voltage 20kV (Volt)</th>
                <th style="width:110px;">Current (Ampere)</th>
                <th>Catatan Kondisi</th>
                <th style="width:110px;">Petugas</th>
                <th style="width:75px;">Status</th>
            </tr>
        </thead>
        <tbody>
        @forelse($rows as $idx => $r)
            <tr>
                <td class="c">{{ $idx + 1 }}</td>
                <td class="c">{{ $r->tanggal->format('d/m/Y') }}</td>
                <td class="n" style="font-weight:600; color:#7c3aed;">{{ $n($r->voltage_20kv, 1) }}</td>
                <td class="n" style="font-weight:600; color:#0284c7;">{{ $n($r->current, 2) }}</td>
                <td style="text-align:left;">{{ $r->catatan ?? '-' }}</td>
                <td style="text-align:left;">{{ $r->user?->name ?? 'Teknisi' }}</td>
                <td class="c">{{ $r->isVerified() ? '✓ Verified' : 'Pending' }}</td>
            </tr>
        @empty
            <tr><td colspan="7" class="c">Tidak ada data pencatatan MVSB pada interval ini.</td></tr>
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
        const dataVolt = @json($chartVolt);
        const dataCurr = @json($chartCurr);

        function makeMvsbPdfChart(id, label, data, color, unit) {
            const el = document.getElementById(id);
            if (!el || !labels.length) return null;
            return new Chart(el, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: label,
                        data: data,
                        borderColor: color,
                        backgroundColor: color.replace(')', ', 0.1)').replace('rgb', 'rgba'),
                        borderWidth: 1.5,
                        fill: true,
                        tension: 0.2,
                        pointRadius: labels.length > 35 ? 0 : 2,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { ticks: { font: { size: 7.5 }, maxRotation: 45 } },
                        y: {
                            type: 'linear',
                            title: { display: true, text: unit, font: { size: 7.5 } },
                            ticks: { font: { size: 7.5 } },
                            grace: '5%'
                        }
                    }
                }
            });
        }

        makeMvsbPdfChart('chartMvsbPdfVolt', 'Voltage 20kV (Volt)', dataVolt, 'rgb(124, 58, 237)', 'V');
        makeMvsbPdfChart('chartMvsbPdfCurr', 'Current (Ampere)', dataCurr, 'rgb(2, 132, 199)', 'A');

        setTimeout(() => window.print(), 500);
    });
</script>
</body>
</html>
