@extends('layouts.panel')

@php
    $n = fn ($v) => number_format($v ?? 0, 2, ',', '.');
    $hasElecToday  = $today && $today->foto_kwh;
    $hasWaterToday = $today && $today->foto_air;
@endphp

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('panel')

@if ($hasElecToday && $hasWaterToday)
    <div class="status-banner ok">
        <span><span class="dot"></span>Logsheet hari ini (Listrik ⚡ &amp; Air 💧) sudah lengkap dicatat oleh <b>{{ $today->user?->name ?? 'Teknisi' }}</b>.</span>
        <a href="{{ route('logsheet.show', $today) }}" class="btn btn-sm">Lihat Detail</a>
    </div>
@elseif ($hasElecToday && !$hasWaterToday)
    <div class="status-banner warn">
        <span><span class="dot"></span>Listrik hari ini sudah dicatat. <b>Water Meter Air 💧 belum dicatat.</b></span>
        <a href="{{ route('logsheet.create.air') }}" class="btn btn-sm btn-primary">+ Input Stand Air</a>
    </div>
@elseif (!$hasElecToday && $hasWaterToday)
    <div class="status-banner warn">
        <span><span class="dot"></span>Air hari ini sudah dicatat. <b>kWh Meter Listrik ⚡ belum dicatat.</b></span>
        <a href="{{ route('logsheet.create.listrik') }}" class="btn btn-sm btn-primary">+ Input Stand Listrik</a>
    </div>
@else
    <div class="status-banner warn">
        <span><span class="dot"></span>Logsheet hari ini belum dicatat. Teknisi harap melakukan pencatatan stand meter.</span>
        <div style="display:flex; gap:8px; margin-left:auto; flex-wrap:wrap;">
            <a href="{{ route('logsheet.create.listrik') }}" class="btn btn-sm btn-primary">+ Input Listrik</a>
            <a href="{{ route('logsheet.create.air') }}" class="btn btn-sm" style="background:rgba(6,182,212,0.18); border-color:rgba(6,182,212,0.4); color:#38bdf8; font-weight:600;">+ Input Air</a>
        </div>
    </div>
@endif

<div class="meter-grid">
    <div class="meter-card mc-lwbp">
        <div class="mc-label">kWh LWBP · Bulan ini</div>
        <div class="mc-value">{{ $n($summary['lwbp']) }}<small>kWh</small></div>
        <div class="mc-sub">Hari ini: {{ $today && $today->pakai_lwbp !== null ? $n($today->pakai_lwbp).' kWh' : '—' }}</div>
    </div>
    <div class="meter-card mc-wbp">
        <div class="mc-label">kWh WBP · Bulan ini</div>
        <div class="mc-value">{{ $n($summary['wbp']) }}<small>kWh</small></div>
        <div class="mc-sub">Hari ini: {{ $today && $today->pakai_wbp !== null ? $n($today->pakai_wbp).' kWh' : '—' }}</div>
    </div>
    <div class="meter-card mc-air">
        <div class="mc-label">Air · Bulan ini</div>
        <div class="mc-value">{{ $n($summary['air']) }}<small>m³</small></div>
        <div class="mc-sub">Hari ini: {{ $today && $today->pakai_air !== null ? $n($today->pakai_air).' m³' : '—' }}</div>
    </div>
    <div class="meter-card mc-total">
        <div class="mc-label">Menunggu Verifikasi</div>
        <div class="mc-value">{{ $summary['pending'] }}<small>data</small></div>
        <div class="mc-sub">Perlu crosscheck foto oleh admin</div>
    </div>
</div>

<div class="form-grid">
    <section class="panel">
        <div class="panel-head"><div><h2>Pemakaian Listrik Harian</h2><p>{{ now()->translatedFormat('F Y') }} (kWh)</p></div></div>
        <div class="chart-box"><canvas id="chartListrik"></canvas></div>
    </section>
    <section class="panel">
        <div class="panel-head"><div><h2>Pemakaian Air Harian</h2><p>{{ now()->translatedFormat('F Y') }} (m³)</p></div></div>
        <div class="chart-box"><canvas id="chartAir"></canvas></div>
    </section>
</div>

<section class="panel" style="margin-top:24px;">
    <div class="panel-head">
        <div>
            <h2><span style="color:var(--accent);">📋</span> Pekerjaan Harian Teknisi (Daily Log Book)</h2>
            <p>Pekerjaan hari ini: <b style="color:#34d399;">{{ $dailyLogStats['today_done'] }} Done</b> &middot; <b style="color:var(--accent-light);">{{ $dailyLogStats['today_continue'] }} Continue</b> (Total: {{ $dailyLogStats['today_total'] }} pekerjaan)</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('daily-log.index') }}" class="btn btn-sm">
                Lihat Semua Daily Log →
            </a>
            <a href="{{ route('daily-log.create') }}" class="btn btn-sm btn-primary">
                + Input Daily Log
            </a>
        </div>
    </div>

    @if ($todayLogs->count() > 0)
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(260px, 1fr)); gap:16px;">
            @foreach ($todayLogs as $l)
                <div style="background:var(--bg-card-subtle); border:1px solid var(--border); border-radius:var(--radius-md); padding:16px; display:flex; flex-direction:column; justify-content:space-between; gap:12px; box-shadow:var(--shadow-sm);">
                    <div>
                        <div style="display:flex; align-items:center; justify-content:space-between; gap:8px;">
                            <span style="font-size:0.84rem; color:var(--accent); font-weight:600;">📍 {{ $l->lokasi }}</span>
                            <span class="badge" style="font-size:0.7rem; padding:2px 8px; font-weight:700; {{ $l->status === 'Done' ? 'background:var(--success-bg); color:var(--success); border:1px solid var(--success-border);' : 'background:var(--warning-bg); color:var(--warning); border:1px solid var(--warning-border);' }}">
                                {{ $l->status }}
                            </span>
                        </div>
                        <p style="font-size:0.85rem; color:var(--text-primary); margin-top:8px; line-height:1.45; max-height:44px; overflow:hidden;">
                            {{ $l->deskripsi }}
                        </p>
                    </div>

                    <div style="display:flex; align-items:center; justify-content:space-between; border-top:1px solid var(--border); padding-top:10px; font-size:0.78rem; color:var(--text-secondary);">
                        <span>👤 <b>{{ $l->user?->name ?? 'Teknisi' }}</b> &middot; {{ $l->jam_mulai }} WIB</span>
                        <a href="{{ route('daily-log.show', $l) }}" style="color:var(--accent); text-decoration:none; font-weight:600;">Detail →</a>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div style="text-align:center; padding:28px 16px; color:var(--text-muted); font-size:0.85rem;">
            Belum ada catatan pekerjaan teknisi yang diinput hari ini.
            <div style="margin-top:10px;">
                <a href="{{ route('daily-log.index') }}" class="btn btn-sm" style="display:inline-flex;">Lihat Rekap Log Book</a>
            </div>
        </div>
    @endif
</section>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    const data = @json($chart);

    function getChartThemeColors() {
        const isLight = document.documentElement.getAttribute('data-theme') === 'light';
        return {
            textColor: isLight ? '#475569' : 'rgba(248, 250, 252, 0.75)',
            gridColor: isLight ? 'rgba(0, 0, 0, 0.06)' : 'rgba(255, 255, 255, 0.06)',
            lwbpColor: isLight ? '#b8860b' : '#d4af37',
            airBg: isLight ? 'rgba(2, 132, 199, 0.12)' : 'rgba(56, 189, 248, 0.25)',
            airBorder: isLight ? '#0284c7' : '#38bdf8'
        };
    }

    let colors = getChartThemeColors();
    Chart.defaults.font.family = "'Inter', system-ui, sans-serif";
    Chart.defaults.color = colors.textColor;

    const opts = {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        scales: {
            x: { grid: { color: colors.gridColor }, ticks: { color: colors.textColor }, stacked: true },
            y: { grid: { color: colors.gridColor }, ticks: { color: colors.textColor }, stacked: true, beginAtZero: true }
        },
        plugins: {
            legend: { labels: { color: colors.textColor, usePointStyle: true } }
        }
    };

    const chartListrik = new Chart(document.getElementById('chartListrik'), {
        type: 'bar',
        data: {
            labels: data.labels,
            datasets: [
                { label: 'LWBP', data: data.lwbp, backgroundColor: colors.lwbpColor, borderRadius: 4 },
                { label: 'WBP',  data: data.wbp,  backgroundColor: '#f43f5e', borderRadius: 4 },
            ]
        },
        options: opts,
    });

    const ctx = document.getElementById('chartAir').getContext('2d');
    const chartAir = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [
                {
                    label: 'Air (m³)',
                    data: data.air,
                    borderColor: colors.airBorder,
                    backgroundColor: colors.airBg,
                    fill: true,
                    tension: 0.35,
                    pointRadius: 3
                },
            ]
        },
        options: {
            ...opts,
            scales: {
                x: { grid: { color: colors.gridColor }, ticks: { color: colors.textColor } },
                y: { grid: { color: colors.gridColor }, ticks: { color: colors.textColor }, beginAtZero: true }
            }
        },
    });

    // Theme switch reactivity
    window.addEventListener('kpr-theme-changed', () => {
        const c = getChartThemeColors();
        [chartListrik, chartAir].forEach(chart => {
            if (!chart) return;
            chart.options.scales.x.grid.color = c.gridColor;
            chart.options.scales.x.ticks.color = c.textColor;
            chart.options.scales.y.grid.color = c.gridColor;
            chart.options.scales.y.ticks.color = c.textColor;
            if (chart.options.plugins && chart.options.plugins.legend) {
                chart.options.plugins.legend.labels.color = c.textColor;
            }
        });
        chartListrik.data.datasets[0].backgroundColor = c.lwbpColor;
        chartAir.data.datasets[0].borderColor = c.airBorder;
        chartAir.data.datasets[0].backgroundColor = c.airBg;
        chartListrik.update();
        chartAir.update();
    });
</script>
@endpush
