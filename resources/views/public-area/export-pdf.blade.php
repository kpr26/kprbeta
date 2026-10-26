@php
    $n = fn ($v, $d = 2) => ($v !== null && $v !== '') ? number_format((float)$v, $d, ',', '.') : '—';
    $catLabel = $category === 'water' ? 'WATER METER PUBLIC AREA' : 'KWH METER PUBLIC AREA';
    $periodeLabel = $startDate->translatedFormat('d M Y') . ' s/d ' . $endDate->translatedFormat('d M Y');
    $unit = $category === 'water' ? 'm³' : 'kWh';
    $grandTotalUsage = round($rows->sum(fn ($r) => $r->pemakaian ?? 0), 2);

    // Siapkan data grafik untuk visualisasi PDF
    $chartDates = [];
    $chartCT = [];
    $chartSL = [];
    $chartPO = [];
    $chartTotal = [];
    $dates = $rows->pluck('tanggal')->map(fn($d) => $d->format('Y-m-d'))->unique()->sort()->values();
    foreach ($dates as $d) {
        $dayRows = $rows->filter(fn($r) => $r->tanggal->format('Y-m-d') === $d);
        $uCT = (float) ($dayRows->firstWhere('meter_key', 'cooling_tower')?->pemakaian ?? 0);
        $uSL = (float) ($dayRows->firstWhere('meter_key', 'toilet_sky_lobby')?->pemakaian ?? 0);
        $uPO = (float) ($dayRows->firstWhere('meter_key', 'pantry_office')?->pemakaian ?? 0);
        $dayTotal = round($uCT + $uSL + $uPO, 2);

        $chartDates[] = \Carbon\Carbon::parse($d)->translatedFormat('d M');
        $chartCT[] = $uCT;
        $chartSL[] = $uSL;
        $chartPO[] = $uPO;
        $chartTotal[] = $dayTotal;
    }
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Logsheet_{{ ucfirst($category) }}_{{ $startDate->format('Ymd') }}_{{ $endDate->format('Ymd') }}</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<style>
    @page { size: A4 portrait; margin: 10mm; }
    * { box-sizing: border-box; }
    body { font-family: 'Segoe UI', Arial, sans-serif; color: #111; font-size: 8.5pt; margin: 0; line-height: 1.3; }
    .bar { position: sticky; top: 0; background: #0f172a; padding: 10px 18px; display: flex; gap: 10px; justify-content: flex-end; z-index: 99; }
    .bar button { background: #0284c7; color: #fff; border: 0; padding: 8px 16px; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 9pt; }
    .page { padding: 12px; }
    h1 { font-size: 13pt; margin: 0; color: #0369a1; }
    .sub { color: #555; margin: 2px 0 12px; font-size: 8.5pt; }
    
    .section-title { font-size: 9.5pt; font-weight: 700; color: #0f172a; margin: 12px 0 6px; border-left: 3px solid #0284c7; padding-left: 8px; text-transform: uppercase; }

    /* Summary cards */
    .sum-grid { display: flex; gap: 10px; margin-bottom: 12px; }
    .sum-card { flex: 1; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px 10px; background: #f8fafc; }
    .sum-card small { display: block; font-size: 7.5pt; color: #64748b; text-transform: uppercase; font-weight: 600; }
    .sum-card b { display: block; font-size: 12pt; color: #0f172a; margin-top: 2px; }

    /* Tables */
    table { width: 100%; border-collapse: collapse; font-size: 8pt; margin-bottom: 12px; }
    th, td { border: 1px solid #94a3b8; padding: 4px 6px; }
    th { background: #1e293b; color: #fff; text-align: center; }
    .th-sub { background: #334155; color: #f1f5f9; }
    td.n { text-align: right; font-variant-numeric: tabular-nums; }
    td.c { text-align: center; }
    tfoot td { font-weight: bold; background: #fef3c7; }

    /* Chart Box */
    .chart-container { border: 1px solid #cbd5e1; border-radius: 6px; padding: 10px; background: #ffffff; margin-bottom: 14px; page-break-inside: avoid; }

    .sign { display: flex; justify-content: space-between; margin-top: 25px; text-align: center; font-size: 8.5pt; page-break-inside: avoid; }
    .sign div { width: 200px; }
    .sign .line { margin-top: 50px; border-top: 1px solid #000; padding-top: 4px; }

    @media print { 
        .bar { display: none; } 
        .page { padding: 0; } 
        th { -webkit-print-color-adjust: exact; print-color-adjust: exact; } 
        tfoot td { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
        .chart-container { border-color: #94a3b8; }
    }
</style>
</head>
<body>
<div class="bar">
    <button onclick="window.print()">⬇ Simpan sebagai PDF / Cetak Laporan</button>
</div>
<div class="page">
    <div style="display:flex; justify-content:space-between; align-items:flex-start; border-bottom:2px solid #0284c7; padding-bottom:8px; margin-bottom:10px;">
        <div>
            <h1>LAPORAN LOGSHEET REKAPITULASI {{ $catLabel }}</h1>
            <p class="sub">Rentang Interval: <b>{{ $periodeLabel }}</b> (Maks. 12 Bulan)</p>
        </div>
        <div style="text-align:right; font-size:8pt; color:#64748b;">
            <div>Dicetak: {{ now()->translatedFormat('d F Y H:i') }}</div>
            <div>Petugas: <b>{{ auth()->user()->name }}</b></div>
        </div>
    </div>

    {{-- HIGHLIGHT CARDS --}}
    <div class="sum-grid">
        <div class="sum-card" style="border-left:4px solid #0284c7;">
            <small>Total Pemakaian Periode Ini</small>
            <b>{{ $n($grandTotalUsage) }} {{ $unit }}</b>
        </div>
        <div class="sum-card" style="border-left:4px solid #10b981;">
            <small>Jumlah Titik Meteran</small>
            <b>3 Titik Area Publik</b>
        </div>
        <div class="sum-card" style="border-left:4px solid #f59e0b;">
            <small>Total Data Tercatat</small>
            <b>{{ $rows->count() }} pencatatan</b>
        </div>
        <div class="sum-card" style="border-left:4px solid #8b5cf6;">
            <small>Rata-rata Pemakaian Harian</small>
            <b>{{ $rows->count() > 0 ? $n($grandTotalUsage / max(count($dates), 1)) : 0 }} {{ $unit }}/hari</b>
        </div>
    </div>

    {{-- BAGIAN 1: RANGKUMAN DALAM BENTUK TABEL --}}
    <div class="section-title">I. Tabel Rangkuman Statistik per Titik Meteran Air</div>
    <table>
        <thead>
            <tr>
                <th style="width:30px;">No</th>
                <th style="text-align:left;">Nama Titik Meteran</th>
                <th style="text-align:left;">Lokasi Fisik</th>
                <th>Total Pemakaian ({{ $unit }})</th>
                <th>Rata-rata ({{ $unit }}/hari)</th>
                <th>Min ({{ $unit }})</th>
                <th>Max ({{ $unit }})</th>
                <th>Hari Tercatat</th>
            </tr>
        </thead>
        <tbody>
            @php $idx = 1; @endphp
            @foreach ($meters as $key => $meta)
                @php $s = $summaryPerMeter[$key] ?? null; @endphp
                <tr>
                    <td class="c">{{ $idx++ }}</td>
                    <td style="font-weight:600; text-align:left;">{{ $meta['name'] }}</td>
                    <td style="text-align:left; color:#475569;">{{ $meta['location'] }}</td>
                    <td class="n" style="font-weight:700; color:#0369a1;">{{ $n($s['totalUsage'] ?? 0) }}</td>
                    <td class="n">{{ $n($s['avgUsage'] ?? 0) }}</td>
                    <td class="n">{{ $n($s['minUsage'] ?? 0) }}</td>
                    <td class="n">{{ $n($s['maxUsage'] ?? 0) }}</td>
                    <td class="c">{{ $rows->where('meter_key', $key)->count() }} hari</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="c">GRAND TOTAL SELURUH METERAN AIR</td>
                <td class="n">{{ $n($grandTotalUsage) }} {{ $unit }}</td>
                <td colspan="4" class="c">{{ $rows->count() }} data logsheet tercatat</td>
            </tr>
        </tfoot>
    </table>

    {{-- BAGIAN 2: RANGKUMAN DALAM BENTUK GRAFIK --}}
    <div class="section-title">II. Grafik Tren Pemakaian Air Harian ({{ $periodeLabel }})</div>
    <div class="chart-container">
        <div style="height:190px; width:100%;">
            <canvas id="chartWaterPdf"></canvas>
        </div>
    </div>

    {{-- BAGIAN 3: RINCIAN TABEL LENGKAP --}}
    <div class="section-title">III. Rincian Seluruh Pencatatan Stand Meter</div>
    <table>
        <thead>
            <tr>
                <th style="width:25px;">No</th>
                <th style="width:75px;">Tanggal</th>
                <th>Nama Meteran</th>
                <th>Lokasi</th>
                <th>Stand ({{ $unit }})</th>
                <th>Pemakaian ({{ $unit }})</th>
                <th>Petugas</th>
                <th style="width:65px;">Status</th>
            </tr>
        </thead>
        <tbody>
        @forelse ($rows as $i => $r)
            <tr>
                <td class="c">{{ $i + 1 }}</td>
                <td class="c">{{ $r->tanggal->format('d/m/Y') }}</td>
                <td style="font-weight:600; text-align:left;">{{ $r->meter_name }}</td>
                <td style="text-align:left; color:#475569;">{{ $meters[$r->meter_key]['location'] ?? '—' }}</td>
                <td class="n">{{ $n($r->stand_meter) }}</td>
                <td class="n" style="font-weight:600; color:#0284c7;">{{ $n($r->pemakaian) }}</td>
                <td style="text-align:left;">{{ $r->user?->name ?? 'User Terhapus' }}</td>
                <td class="c">{{ $r->isVerified() ? '✓ Terverifikasi' : 'Menunggu' }}</td>
            </tr>
        @empty
            <tr><td colspan="8" class="c">Tidak ada data pencatatan pada interval ini.</td></tr>
        @endforelse
        </tbody>
        @if ($rows->count())
        <tfoot>
            <tr>
                <td colspan="5" class="c">TOTAL PEMAKAIAN</td>
                <td class="n">{{ $n($grandTotalUsage) }} {{ $unit }}</td>
                <td colspan="2" class="c">{{ $rows->count() }} data</td>
            </tr>
        </tfoot>
        @endif
    </table>

    <div class="sign">
        <div>Dibuat oleh,<div class="line">Teknisi Pengawas</div></div>
        <div>Diperiksa &amp; Disetujui,<div class="line">{{ auth()->user()->name }} (Supervisor / Admin)</div></div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const labels = @json($chartDates);
        const dataCT = @json($chartCT);
        const dataSL = @json($chartSL);
        const dataPO = @json($chartPO);
        const dataTotal = @json($chartTotal);

        const ctx = document.getElementById('chartWaterPdf');
        if (ctx && labels.length > 0) {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Cooling Tower',
                            data: dataCT,
                            backgroundColor: '#0284c7',
                            stack: 'stack0',
                        },
                        {
                            label: 'Toilet & Mushola',
                            data: dataSL,
                            backgroundColor: '#06b6d4',
                            stack: 'stack0',
                        },
                        {
                            label: 'Pantry Office',
                            data: dataPO,
                            backgroundColor: '#38bdf8',
                            stack: 'stack0',
                        },
                        {
                            label: 'Total Harian',
                            data: dataTotal,
                            type: 'line',
                            borderColor: '#f59e0b',
                            backgroundColor: '#f59e0b',
                            borderWidth: 2,
                            pointRadius: labels.length > 30 ? 0 : 3,
                            fill: false,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: false,
                    plugins: {
                        legend: { position: 'top', labels: { boxWidth: 10, font: { size: 9 } } },
                        title: { display: false }
                    },
                    scales: {
                        x: { stacked: true, ticks: { font: { size: 8 }, maxRotation: 45 } },
                        y: { stacked: true, beginAtZero: true, ticks: { font: { size: 8 } } }
                    }
                }
            });
        }

        setTimeout(() => window.print(), 500);
    });
</script>
</body>
</html>
