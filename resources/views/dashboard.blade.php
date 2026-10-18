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
        <span><span class="dot"></span>Logsheet hari ini (Listrik ⚡ &amp; Air 💧) sudah lengkap dicatat oleh <b>{{ $today->user->name }}</b>.</span>
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
            <a href="{{ route('logsheet.create.listrik') }}" class="btn btn-sm" style="background:#fbbf24; color:#0f172a; font-weight:600;">+ Input Listrik</a>
            <a href="{{ route('logsheet.create.air') }}" class="btn btn-sm" style="background:#38bdf8; color:#0f172a; font-weight:600;">+ Input Air</a>
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
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    const data = @json($chart);
    Chart.defaults.color = '#94a3b8';
    Chart.defaults.font.family = 'Inter';
    const grid = { color: 'rgba(255,255,255,.06)' };
    const opts = { responsive: true, maintainAspectRatio: false, interaction: { mode: 'index', intersect: false },
        scales: { x: { grid, stacked: true }, y: { grid, stacked: true, beginAtZero: true } },
        plugins: { legend: { labels: { usePointStyle: true } } } };

    new Chart(document.getElementById('chartListrik'), {
        type: 'bar',
        data: { labels: data.labels, datasets: [
            { label: 'LWBP', data: data.lwbp, backgroundColor: '#f59e0b', borderRadius: 4 },
            { label: 'WBP',  data: data.wbp,  backgroundColor: '#f43f5e', borderRadius: 4 },
        ]},
        options: opts,
    });

    const ctx = document.getElementById('chartAir').getContext('2d');
    const g = ctx.createLinearGradient(0, 0, 0, 300);
    g.addColorStop(0, 'rgba(6,182,212,.45)'); g.addColorStop(1, 'rgba(6,182,212,0)');
    new Chart(ctx, {
        type: 'line',
        data: { labels: data.labels, datasets: [
            { label: 'Air (m³)', data: data.air, borderColor: '#22d3ee', backgroundColor: g, fill: true, tension: .35, pointRadius: 3 },
        ]},
        options: { ...opts, scales: { x: { grid }, y: { grid, beginAtZero: true } } },
    });
</script>
@endpush
