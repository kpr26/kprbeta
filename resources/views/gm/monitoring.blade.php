@extends('layouts.panel')

@php
    $n = fn ($v) => $v !== null ? number_format($v, 2, ',', '.') : '—';
    $selectedMonthName = \Carbon\Carbon::create($year, $month, 1)->translatedFormat('F Y');
@endphp

@section('title', 'Executive Monitoring — General Manager')
@section('page-title', 'Executive Monitoring')
@section('page-subtitle', 'Pusat Pemantauan & Analisis Seluruh Logsheet Utilitas Gedung · Periode ' . $selectedMonthName)

@section('panel')
<div class="gm-monitoring-container">

    {{-- ── 1. Top Executive Banner & Controls ── --}}
    <div class="panel executive-hero-banner" style="margin-bottom:24px;">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px;">
            <div>
                <div style="display:flex; align-items:center; gap:10px; margin-bottom:6px;">
                    <span class="badge" style="background:linear-gradient(135deg, #0284c7, #0369a1); color:#fff; border:none; padding:4px 10px; font-weight:700; letter-spacing:0.04em;">
                        👔 ROLE: GENERAL MANAGER
                    </span>
                    <span style="font-size:0.8rem; color:var(--accent); font-weight:600;">
                        Kempinski Private Residences Jakarta
                    </span>
                </div>
                <h2 style="font-size:1.45rem; font-weight:700; color:var(--text-primary); margin:0; font-family:var(--font-serif, 'Cormorant Garamond', Georgia, serif);">
                    Ringkasan Pemantauan Operasional Terpadu
                </h2>
                <p style="font-size:0.85rem; color:var(--text-muted); margin:4px 0 0;">
                    Monitoring terintegrasi: AMR PLN T33N, Water Meter Gedung, Public Area (Water, LVSB, MVSB 20kV) dan Pekerjaan Teknisi.
                </p>
            </div>

            {{-- Filter Periode & Export Dropdown --}}
            <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                <form method="GET" action="{{ route('gm.monitoring') }}" class="filter-form" style="margin:0;">
                    <div style="display:flex; align-items:center; gap:8px;">
                        <label for="periode" style="font-size:0.8rem; color:var(--text-secondary); font-weight:600;">Periode:</label>
                        <input type="month" id="periode" name="periode" value="{{ $periode }}" max="{{ now()->format('Y-m') }}" onchange="this.form.submit()" class="input" style="padding:7px 12px; font-size:0.85rem; cursor:pointer;">
                    </div>
                </form>

                <div class="btn-group">
                    <a href="{{ route('logsheet.excel', ['periode' => $periode]) }}" class="btn btn-sm btn-excel" title="Download Excel Logsheet Gedung">
                        <svg viewBox="0 0 20 20" fill="currentColor" style="width:15px; height:15px;"><path d="M10.75 2.75a.75.75 0 0 0-1.5 0v8.614L6.295 8.235a.75.75 0 1 0-1.09 1.03l4.25 4.5a.75.75 0 0 0 1.09 0l4.25-4.5a.75.75 0 0 0-1.09-1.03l-2.955 3.129V2.75Z"/><path d="M3.5 12.75a.75.75 0 0 0-1.5 0v2.5A2.75 2.75 0 0 0 4.75 18h10.5A2.75 2.75 0 0 0 18 15.25v-2.5a.75.75 0 0 0-1.5 0v2.5c0 .69-.56 1.25-1.25 1.25H4.75c-.69 0-1.25-.56-1.25-1.25v-2.5Z"/></svg>
                        Excel Gedung
                    </a>
                    <a href="{{ route('logsheet.pdf', ['periode' => $periode]) }}" target="_blank" class="btn btn-sm btn-pdf" title="Cetak PDF Logsheet Gedung">
                        <svg viewBox="0 0 20 20" fill="currentColor" style="width:15px; height:15px;"><path fill-rule="evenodd" d="M5 2.75C5 1.784 5.784 1 6.75 1h6.5c.966 0 1.75.784 1.75 1.75v3.552c.377.046.752.097 1.126.153A2.212 2.212 0 0 1 18 8.653v4.097A2.25 2.25 0 0 1 15.75 15h-.241l.305 1.984A1.75 1.75 0 0 1 14.084 19H5.915a1.75 1.75 0 0 1-1.73-2.016L4.492 15H4.25A2.25 2.25 0 0 1 2 12.75V8.653c0-1.082.775-2.034 1.874-2.198.374-.056.75-.107 1.127-.153L5 6.25v-3.5Zm8.5 3.397a41.533 41.533 0 0 0-7 0V2.75a.25.25 0 0 1 .25-.25h6.5a.25.25 0 0 1 .25.25v3.397ZM6.608 12.5a.25.25 0 0 0-.247.212l-.693 4.5a.25.25 0 0 0 .247.288h8.17a.25.25 0 0 0 .246-.288l-.692-4.5a.25.25 0 0 0-.247-.212H6.608Z" clip-rule="evenodd"/></svg>
                        PDF Gedung
                    </a>
                    <a href="{{ route('public-area.excel', ['category' => 'water', 'periode' => $periode]) }}" class="btn btn-sm" style="background:rgba(6,182,212,0.12); color:#38bdf8; border-color:rgba(6,182,212,0.3);" title="Download Excel Water Public Area">
                        Excel Water PA
                    </a>
                    @if(auth()->user()?->isSuperAdmin())
                    <a href="{{ route('admin.whatsapp.settings') }}" class="btn btn-sm" style="background:#15803d; color:#ffffff; font-weight:600; border:1px solid #16a34a; display:inline-flex; align-items:center; gap:6px; box-shadow:0 2px 8px rgba(22,163,74,0.3);" title="Pengaturan Notifikasi WhatsApp (Khusus Super Admin)">
                        <svg viewBox="0 0 24 24" fill="currentColor" style="width:15px; height:15px;"><path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0 0 12.04 2m.01 1.67c2.2 0 4.26.86 5.82 2.42a8.225 8.225 0 0 1 2.41 5.83c0 4.54-3.7 8.24-8.24 8.24-1.48 0-2.93-.4-4.2-1.15l-.3-.18-3.12.82.83-3.04-.2-.31a8.196 8.196 0 0 1-1.26-4.38c0-4.54 3.7-8.24 8.24-8.24m4.52 11.66c-.25-.13-1.47-.72-1.7-.81-.23-.08-.39-.13-.56.13-.17.25-.64.81-.79.97-.14.17-.29.19-.54.06-.25-.13-1.06-.39-2.02-1.24-.75-.67-1.26-1.5-1.4-1.75-.15-.25-.02-.39.11-.51.11-.11.25-.29.38-.44.13-.14.17-.25.25-.42.08-.17.04-.31-.02-.44-.06-.13-.56-1.35-.77-1.85-.2-.49-.41-.42-.56-.43h-.48c-.17 0-.44.06-.67.31-.23.25-.88.86-.88 2.1 0 1.24.9 2.44 1.03 2.61.13.17 1.77 2.7 4.29 3.79.6.26 1.07.41 1.43.53.6.19 1.15.16 1.58.1.48-.07 1.47-.6 1.68-1.18.21-.58.21-1.07.15-1.18-.07-.1-.23-.17-.48-.29"/></svg>
                        Pengaturan WhatsApp
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ── 2. Real-Time Pulse Hari Ini (Status Input Tanggal {{ now()->translatedFormat('d F Y') }}) ── --}}
    <div class="panel" style="margin-bottom:24px; padding:20px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px; flex-wrap:wrap; gap:8px;">
            <div style="display:flex; align-items:center; gap:8px;">
                <span class="dot" style="background:#10b981; box-shadow:0 0 8px #10b981;"></span>
                <h3 style="font-size:1rem; font-weight:700; color:var(--text-primary); margin:0;">
                    Status Pencatatan Hari Ini ({{ now()->translatedFormat('l, d F Y') }})
                </h3>
            </div>
            <span style="font-size:0.75rem; color:var(--text-muted);">
                Update real-time dari seluruh gardu &amp; pos teknisi
            </span>
        </div>

        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:12px;">
            {{-- Pulse Listrik PLN --}}
            <div style="background:var(--bg-card-subtle); border:1px solid {{ $todayPulse['elec_pln']['recorded'] ? 'rgba(16,185,129,0.3)' : 'rgba(239,68,68,0.3)' }}; border-radius:var(--radius-sm); padding:12px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                    <span style="font-size:0.75rem; font-weight:700; color:#fbbf24; text-transform:uppercase;">⚡ AMR PLN T33N</span>
                    <span class="badge" style="font-size:0.65rem; padding:2px 6px; {{ $todayPulse['elec_pln']['recorded'] ? 'background:rgba(16,185,129,0.2); color:#34d399;' : 'background:rgba(239,68,68,0.2); color:#f87171;' }}">
                        {{ $todayPulse['elec_pln']['recorded'] ? '✓ Sudah Dicatat' : '✗ Belum' }}
                    </span>
                </div>
                <div style="font-size:0.82rem; color:var(--text-secondary);">
                    @if($todayPulse['elec_pln']['recorded'])
                        Oleh <b>{{ $todayPulse['elec_pln']['user'] ?? 'Teknisi' }}</b> ({{ $todayPulse['elec_pln']['time'] ?? '-' }})
                    @else
                        Menunggu pencatatan gardu PLN
                    @endif
                </div>
            </div>

            {{-- Pulse Air Gedung --}}
            <div style="background:var(--bg-card-subtle); border:1px solid {{ $todayPulse['water_gedung']['recorded'] ? 'rgba(16,185,129,0.3)' : 'rgba(239,68,68,0.3)' }}; border-radius:var(--radius-sm); padding:12px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                    <span style="font-size:0.75rem; font-weight:700; color:#38bdf8; text-transform:uppercase;">💧 Air Gedung</span>
                    <span class="badge" style="font-size:0.65rem; padding:2px 6px; {{ $todayPulse['water_gedung']['recorded'] ? 'background:rgba(16,185,129,0.2); color:#34d399;' : 'background:rgba(239,68,68,0.2); color:#f87171;' }}">
                        {{ $todayPulse['water_gedung']['recorded'] ? '✓ Sudah Dicatat' : '✗ Belum' }}
                    </span>
                </div>
                <div style="font-size:0.82rem; color:var(--text-secondary);">
                    @if($todayPulse['water_gedung']['recorded'])
                        Stand: <b>{{ $todayPulse['water_gedung']['stand'] }}</b>
                    @else
                        Menunggu input water meter utama
                    @endif
                </div>
            </div>

            {{-- Pulse Water Public Area --}}
            @php
                $paRecordedCount = ($todayPulse['water_cooling_tower']['recorded'] ? 1 : 0) +
                                   ($todayPulse['water_toilet_sky']['recorded'] ? 1 : 0) +
                                   ($todayPulse['water_pantry']['recorded'] ? 1 : 0);
            @endphp
            <div style="background:var(--bg-card-subtle); border:1px solid {{ $paRecordedCount === 3 ? 'rgba(16,185,129,0.3)' : 'rgba(245,158,11,0.3)' }}; border-radius:var(--radius-sm); padding:12px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                    <span style="font-size:0.75rem; font-weight:700; color:#06b6d4; text-transform:uppercase;">🏢 Water Public Area</span>
                    <span class="badge" style="font-size:0.65rem; padding:2px 6px; {{ $paRecordedCount === 3 ? 'background:rgba(16,185,129,0.2); color:#34d399;' : 'background:rgba(245,158,11,0.2); color:#fbbf24;' }}">
                        {{ $paRecordedCount }}/3 Meter
                    </span>
                </div>
                <div style="font-size:0.75rem; color:var(--text-muted); display:flex; gap:6px;">
                    <span style="{{ $todayPulse['water_cooling_tower']['recorded'] ? 'color:#34d399;' : 'color:#f87171;' }}">CT: {{ $todayPulse['water_cooling_tower']['recorded'] ? '✓' : '✗' }}</span> &middot;
                    <span style="{{ $todayPulse['water_toilet_sky']['recorded'] ? 'color:#34d399;' : 'color:#f87171;' }}">Toilet: {{ $todayPulse['water_toilet_sky']['recorded'] ? '✓' : '✗' }}</span> &middot;
                    <span style="{{ $todayPulse['water_pantry']['recorded'] ? 'color:#34d399;' : 'color:#f87171;' }}">Pantry: {{ $todayPulse['water_pantry']['recorded'] ? '✓' : '✗' }}</span>
                </div>
            </div>

            {{-- Pulse LVSB & MVSB --}}
            <div style="background:var(--bg-card-subtle); border:1px solid {{ ($todayPulse['lvsb']['recorded'] && $todayPulse['mvsb']['recorded']) ? 'rgba(16,185,129,0.3)' : 'rgba(245,158,11,0.3)' }}; border-radius:var(--radius-sm); padding:12px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                    <span style="font-size:0.75rem; font-weight:700; color:#a855f7; text-transform:uppercase;">⚡ LVSB &amp; MVSB 20kV</span>
                    <span class="badge" style="font-size:0.65rem; padding:2px 6px; background:rgba(168,85,247,0.15); color:#c084fc;">
                        Panel Tegangan
                    </span>
                </div>
                <div style="font-size:0.75rem; color:var(--text-muted);">
                    LVSB: <b style="{{ $todayPulse['lvsb']['recorded'] ? 'color:#34d399;' : 'color:#f87171;' }}">{{ $todayPulse['lvsb']['recorded'] ? 'Sudah ✓' : 'Belum ✗' }}</b> &middot; 
                    MVSB: <b style="{{ $todayPulse['mvsb']['recorded'] ? 'color:#34d399;' : 'color:#f87171;' }}">{{ $todayPulse['mvsb']['recorded'] ? 'Sudah ✓' : 'Belum ✗' }}</b>
                </div>
            </div>

            {{-- Pulse Daily Log Book --}}
            <div style="background:var(--bg-card-subtle); border:1px solid rgba(16,185,129,0.3); border-radius:var(--radius-sm); padding:12px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                    <span style="font-size:0.75rem; font-weight:700; color:#34d399; text-transform:uppercase;">📋 Daily Log Book</span>
                    <span class="badge" style="font-size:0.65rem; padding:2px 6px; background:rgba(16,185,129,0.15); color:#34d399;">
                        Hari Ini
                    </span>
                </div>
                <div style="font-size:0.82rem; color:var(--text-secondary);">
                    <b>{{ $todayPulse['daily_logs_count'] }}</b> pekerjaan teknisi dilaporkan
                </div>
            </div>
        </div>
    </div>

    {{-- ── 3. High-Level KPI Summary Cards (Bulan Terpilih) ── --}}
    <div style="font-size:0.85rem; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:var(--accent-light); margin-bottom:12px; display:flex; align-items:center; gap:6px;">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width:16px; height:16px;"><path d="M15.5 2A1.5 1.5 0 0 0 14 3.5v13a1.5 1.5 0 0 0 1.5 1.5h1a1.5 1.5 0 0 0 1.5-1.5v-13A1.5 1.5 0 0 0 16.5 2h-1ZM9.5 6A1.5 1.5 0 0 0 8 7.5v9A1.5 1.5 0 0 0 9.5 18h1a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 10.5 6h-1ZM3.5 10A1.5 1.5 0 0 0 2 11.5v5A1.5 1.5 0 0 0 3.5 18h1A1.5 1.5 0 0 0 6 16.5v-5A1.5 1.5 0 0 0 4.5 10h-1Z"/></svg>
        <span>Executive KPI Metrics · Bulan {{ $selectedMonthName }}</span>
    </div>

    <div class="meter-grid" style="margin-bottom:24px;">
        {{-- Card 1: Listrik PLN Total --}}
        <div class="meter-card mc-total">
            <div class="mc-label">
                <svg viewBox="0 0 20 20" fill="currentColor"><path d="M11.983 1.907a.75.75 0 0 0-1.292-.657l-8.5 9.5A.75.75 0 0 0 2.75 12h6.572l-1.305 6.093a.75.75 0 0 0 1.292.657l8.5-9.5A.75.75 0 0 0 17.25 8h-6.572l1.305-6.093Z"/></svg>
                Total Listrik PLN (Gedung)
            </div>
            <div class="mc-value">{{ $n($summaryGedung['kwh']) }}<small>kWh</small></div>
            <div class="mc-sub">
                LWBP: {{ $n($summaryGedung['lwbp']) }} · WBP: {{ $n($summaryGedung['wbp']) }} kWh
            </div>
            <div style="font-size:0.75rem; color:var(--accent-light); margin-top:6px;">
                Rata-rata: <b>{{ $n($summaryGedung['rata_kwh']) }} kWh / hari</b> ({{ $summaryGedung['jumlah_hari'] }} hari)
            </div>
        </div>

        {{-- Card 2: Air Gedung Utama --}}
        <div class="meter-card mc-air">
            <div class="mc-label">
                <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 2.5c.3 0 .58.14.77.37C12.4 4.87 16 9.45 16 12.5a6 6 0 0 1-12 0c0-3.05 3.6-7.63 5.23-9.63A1 1 0 0 1 10 2.5Z" clip-rule="evenodd"/></svg>
                Total Air Gedung (Utama)
            </div>
            <div class="mc-value">{{ $n($summaryGedung['air']) }}<small>m³</small></div>
            <div class="mc-sub">
                Konsumsi air bersih meteran utama
            </div>
            <div style="font-size:0.75rem; color:var(--water); margin-top:6px;">
                Rata-rata: <b>{{ $n($summaryGedung['rata_air']) }} m³ / hari</b>
            </div>
        </div>

        {{-- Card 3: Air Public Area --}}
        <div class="meter-card" style="--c:#06b6d4;">
            <div class="mc-label">
                <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v12a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V4Zm3 1h6v2H7V5Zm6 4H7v2h6V9Zm-6 4h6v2H7v-2Z" clip-rule="evenodd"/></svg>
                Total Air Public Area
            </div>
            <div class="mc-value">{{ $n($totalWaterPa) }}<small>m³</small></div>
            <div class="mc-sub">
                Cooling Tower: {{ $n($waterPaByMeter['cooling_tower']['total_pakai'] ?? 0) }} m³
            </div>
            <div style="font-size:0.75rem; color:#06b6d4; margin-top:6px;">
                Toilet: {{ $n($waterPaByMeter['toilet_sky_lobby']['total_pakai'] ?? 0) }} · Pantry: {{ $n($waterPaByMeter['pantry_office']['total_pakai'] ?? 0) }} m³
            </div>
        </div>

        {{-- Card 4: Daily Log Book & Verifikasi --}}
        <div class="meter-card" style="--c:#10b981;">
            <div class="mc-label">
                <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd"/></svg>
                Pekerjaan Teknisi &amp; Integritas
            </div>
            <div class="mc-value">{{ $dailyLogStats['total'] }}<small>tugas</small></div>
            <div class="mc-sub">
                <b style="color:#34d399;">{{ $dailyLogStats['done'] }} Done ({{ $dailyLogStats['completion_rate'] }}%)</b> &middot; {{ $dailyLogStats['continue'] }} Continue
            </div>
            <div style="font-size:0.75rem; color:var(--text-muted); margin-top:6px;">
                Verifikasi Logsheet: <b style="color:#34d399;">{{ $summaryGedung['terverifikasi'] }}</b> terverifikasi · <b style="color:#fbbf24;">{{ $summaryGedung['pending'] }}</b> pending
            </div>
        </div>
    </div>

    {{-- ── 4. Grafik Tren Pemakaian ── --}}
    <div class="form-grid" style="margin-bottom:24px;">
        <section class="panel">
            <div class="panel-head">
                <div>
                    <h2>Tren Pemakaian Listrik Harian (kWh)</h2>
                    <p>AMR PLN T33N &middot; Bulan {{ $selectedMonthName }}</p>
                </div>
            </div>
            <div class="chart-box"><canvas id="gmChartListrik"></canvas></div>
        </section>

        <section class="panel">
            <div class="panel-head">
                <div>
                    <h2>Tren Pemakaian Air Harian (m³)</h2>
                    <p>Water Meter Gedung &middot; Bulan {{ $selectedMonthName }}</p>
                </div>
            </div>
            <div class="chart-box"><canvas id="gmChartAir"></canvas></div>
        </section>
    </div>

    {{-- ── 5. Rincian Multi-Utilitas Terpadu ── --}}
    <div class="panel">
        <div class="panel-head">
            <div>
                <h2>Rincian Akumulasi Pemakaian Per Kategori</h2>
                <p>Data rekapitulasi lengkap seluruh utilitas pada periode {{ $selectedMonthName }}</p>
            </div>
            <div class="btn-group">
                <a href="{{ route('logsheet.index', ['periode' => $periode]) }}" class="btn btn-sm">
                    Detail Logsheet Gedung →
                </a>
                <a href="{{ route('public-area.index') }}" class="btn btn-sm" style="color:#38bdf8; border-color:rgba(56,189,248,0.3);">
                    Detail Public Area →
                </a>
                <a href="{{ route('daily-log.index') }}" class="btn btn-sm" style="color:#34d399; border-color:rgba(16,185,129,0.3);">
                    Detail Daily Log Book →
                </a>
            </div>
        </div>

        {{-- Tabel Ringkasan Komparasi Utilitas --}}
        <div class="table-wrap">
            <table class="log-table">
                <thead>
                    <tr>
                        <th class="l">Utilitas / Fasilitas</th>
                        <th class="l">Lokasi / Keterangan</th>
                        <th class="r">Total Pemakaian Bulan Ini</th>
                        <th class="r">Rata-rata / Hari</th>
                        <th class="c">Jumlah Data</th>
                        <th class="c">Status Integritas</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- 1. Listrik PLN AMR --}}
                    <tr>
                        <td class="l"><b>⚡ AMR PLN T33N (LWBP &amp; WBP)</b></td>
                        <td class="l">Main Incoming Gardu PLN Gedung</td>
                        <td class="r" style="color:#fbbf24; font-weight:700;">{{ $n($summaryGedung['kwh']) }} kWh</td>
                        <td class="r">{{ $n($summaryGedung['rata_kwh']) }} kWh</td>
                        <td class="c">{{ $summaryGedung['jumlah_hari'] }} hari</td>
                        <td class="c">
                            @if($summaryGedung['pending'] === 0 && $summaryGedung['jumlah_hari'] > 0)
                                <span class="badge badge-ok">100% Terverifikasi</span>
                            @else
                                <span class="badge badge-wait">{{ $summaryGedung['terverifikasi'] }}/{{ $summaryGedung['jumlah_hari'] }} Verif</span>
                            @endif
                        </td>
                    </tr>

                    {{-- 2. Air Gedung Utama --}}
                    <tr>
                        <td class="l"><b>💧 Water Meter Gedung Utama</b></td>
                        <td class="l">Suplai Air Bersih Utama Gedung</td>
                        <td class="r" style="color:#38bdf8; font-weight:700;">{{ $n($summaryGedung['air']) }} m³</td>
                        <td class="r">{{ $n($summaryGedung['rata_air']) }} m³</td>
                        <td class="c">{{ $summaryGedung['jumlah_hari'] }} hari</td>
                        <td class="c">
                            <span class="badge badge-ok">Aktif</span>
                        </td>
                    </tr>

                    {{-- 3. Cooling Tower 12A --}}
                    <tr>
                        <td class="l"><b>🏢 Water Meter Cooling Tower</b></td>
                        <td class="l">Operasional Chiller Lantai 12A</td>
                        <td class="r" style="color:#06b6d4; font-weight:700;">{{ $n($waterPaByMeter['cooling_tower']['total_pakai'] ?? 0) }} m³</td>
                        <td class="r">{{ ($waterPaByMeter['cooling_tower']['count'] ?? 0) > 0 ? $n(($waterPaByMeter['cooling_tower']['total_pakai'] ?? 0) / $waterPaByMeter['cooling_tower']['count']) : '—' }} m³</td>
                        <td class="c">{{ $waterPaByMeter['cooling_tower']['count'] ?? 0 }} data</td>
                        <td class="c"><span class="badge badge-ok">Public Area</span></td>
                    </tr>

                    {{-- 4. Toilet Sky Lobby & Mushola --}}
                    <tr>
                        <td class="l"><b>🏢 Water Meter Toilet &amp; Mushola</b></td>
                        <td class="l">Sky Lobby Lt. 11 &amp; Mushola Lt. 12</td>
                        <td class="r" style="color:#3b82f6; font-weight:700;">{{ $n($waterPaByMeter['toilet_sky_lobby']['total_pakai'] ?? 0) }} m³</td>
                        <td class="r">{{ ($waterPaByMeter['toilet_sky_lobby']['count'] ?? 0) > 0 ? $n(($waterPaByMeter['toilet_sky_lobby']['total_pakai'] ?? 0) / $waterPaByMeter['toilet_sky_lobby']['count']) : '—' }} m³</td>
                        <td class="c">{{ $waterPaByMeter['toilet_sky_lobby']['count'] ?? 0 }} data</td>
                        <td class="c"><span class="badge badge-ok">Public Area</span></td>
                    </tr>

                    {{-- 5. Pantry Office --}}
                    <tr>
                        <td class="l"><b>🏢 Water Meter Pantry Office</b></td>
                        <td class="l">Area Pantry Office</td>
                        <td class="r" style="color:#10b981; font-weight:700;">{{ $n($waterPaByMeter['pantry_office']['total_pakai'] ?? 0) }} m³</td>
                        <td class="r">{{ ($waterPaByMeter['pantry_office']['count'] ?? 0) > 0 ? $n(($waterPaByMeter['pantry_office']['total_pakai'] ?? 0) / $waterPaByMeter['pantry_office']['count']) : '—' }} m³</td>
                        <td class="c">{{ $waterPaByMeter['pantry_office']['count'] ?? 0 }} data</td>
                        <td class="c"><span class="badge badge-ok">Public Area</span></td>
                    </tr>

                    {{-- 6. LVSB --}}
                    <tr>
                        <td class="l"><b>⚡ LVSB (Low Voltage Switchboard)</b></td>
                        <td class="l">Incoming Trafo &amp; 4 Riser Distribusi</td>
                        <td class="r" style="color:#fbbf24; font-weight:700;">Panel Aktif</td>
                        <td class="r">—</td>
                        <td class="c">{{ $lvsbSummary['count'] }} hari</td>
                        <td class="c">
                            <span class="badge badge-ok">{{ $lvsbSummary['terverifikasi'] }} Verif</span>
                        </td>
                    </tr>

                    {{-- 7. MVSB --}}
                    <tr>
                        <td class="l"><b>⚡ MVSB Incoming From PLN (20kV)</b></td>
                        <td class="l">Tegangan Menengah 20 Kilovolt</td>
                        <td class="r" style="color:#a855f7; font-weight:700;">Rata-rata: {{ $n($mvsbSummary['avg_voltage']) }} kV</td>
                        <td class="r">Arus: {{ $n($mvsbSummary['avg_current']) }} A</td>
                        <td class="c">{{ $mvsbSummary['count'] }} hari</td>
                        <td class="c">
                            <span class="badge badge-ok">{{ $mvsbSummary['terverifikasi'] }} Verif</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── 6. Log Pekerjaan Teknisi Terbaru (Daily Log Book) ── --}}
    <section class="panel" style="margin-top:24px;">
        <div class="panel-head">
            <div>
                <h2>Aktivitas Pemeliharaan Teknisi Terbaru (Bulan Ini)</h2>
                <p>Menampilkan 6 pekerjaan terakhir dari total {{ $dailyLogStats['total'] }} tugas yang tercatat</p>
            </div>
            <a href="{{ route('daily-log.index') }}" class="btn btn-sm">
                Buka Seluruh Daily Log Book →
            </a>
        </div>

        @if($dailyLogs->count() > 0)
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:16px;">
                @foreach($dailyLogs->take(6) as $log)
                    <div style="background:var(--bg-card-subtle); border:1px solid var(--border); border-radius:var(--radius-md); padding:16px; display:flex; flex-direction:column; justify-content:space-between; gap:12px;">
                        <div>
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                                <span style="font-size:0.8rem; color:var(--text-muted);">
                                    {{ $log->tanggal ? $log->tanggal->translatedFormat('d M Y') : '-' }} &middot; {{ $log->jam_mulai ?? '-' }} - {{ $log->jam_selesai ?? '-' }}
                                </span>
                                <span class="badge" style="font-size:0.7rem; padding:2px 8px; font-weight:700; {{ $log->status === 'Done' ? 'background:var(--success-bg); color:var(--success); border:1px solid var(--success-border);' : 'background:var(--warning-bg); color:var(--warning); border:1px solid var(--warning-border);' }}">
                                    {{ $log->status }}
                                </span>
                            </div>
                            <div style="font-size:0.88rem; font-weight:600; color:var(--accent); margin-bottom:4px;">
                                📍 {{ $log->lokasi }}
                            </div>
                            <p style="font-size:0.82rem; color:var(--text-primary); line-height:1.45; margin:0; max-height:48px; overflow:hidden;">
                                {{ $log->uraian_pekerjaan }}
                            </p>
                        </div>
                        <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid var(--border); padding-top:10px;">
                            <span style="font-size:0.75rem; color:var(--text-muted);">
                                Teknisi: <b>{{ $log->user?->name ?? 'Teknisi' }}</b>
                            </span>
                            <a href="{{ route('daily-log.show', $log) }}" class="btn btn-sm" style="font-size:0.75rem; padding:4px 8px;">
                                Lihat Foto →
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div style="text-align:center; padding:32px; color:var(--text-muted);">
                Belum ada catatan pekerjaan teknisi pada periode {{ $selectedMonthName }}.
            </div>
        @endif
    </section>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const labels = @json($chart['labels']);
    const dataLwbp = @json($chart['lwbp']);
    const dataWbp = @json($chart['wbp']);
    const dataAir = @json($chart['air']);

    function getGmChartTheme() {
        const isLight = document.documentElement.getAttribute('data-theme') === 'light';
        return {
            textColor: isLight ? '#475569' : '#94a3b8',
            tickColor: isLight ? '#64748b' : '#94a3b8',
            gridColor: isLight ? 'rgba(0,0,0,0.06)' : 'rgba(255,255,255,0.04)',
            lwbpBg: isLight ? 'rgba(184, 134, 11, 0.85)' : 'rgba(212, 163, 89, 0.75)',
            lwbpBorder: isLight ? '#996515' : '#d4a359',
            airBg: isLight ? 'rgba(2, 132, 199, 0.15)' : 'rgba(56, 189, 248, 0.15)',
            airBorder: isLight ? '#0284c7' : '#38bdf8'
        };
    }

    let th = getGmChartTheme();

    const commonOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                labels: { color: th.textColor, font: { family: 'Inter', size: 11 } }
            }
        },
        scales: {
            x: {
                ticks: { color: th.tickColor, font: { size: 10 } },
                grid: { color: th.gridColor }
            },
            y: {
                ticks: { color: th.tickColor, font: { size: 10 } },
                grid: { color: th.gridColor }
            }
        }
    };

    let chartL = null;
    let chartA = null;

    // Chart Listrik
    const ctxListrik = document.getElementById('gmChartListrik');
    if (ctxListrik) {
        chartL = new Chart(ctxListrik, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'LWBP (kWh)',
                        data: dataLwbp,
                        backgroundColor: th.lwbpBg,
                        borderColor: th.lwbpBorder,
                        borderWidth: 1,
                        borderRadius: 4
                    },
                    {
                        label: 'WBP (kWh)',
                        data: dataWbp,
                        backgroundColor: 'rgba(244, 63, 94, 0.75)',
                        borderColor: '#f43f5e',
                        borderWidth: 1,
                        borderRadius: 4
                    }
                ]
            },
            options: commonOptions
        });
    }

    // Chart Air
    const ctxAir = document.getElementById('gmChartAir');
    if (ctxAir) {
        chartA = new Chart(ctxAir, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Pemakaian Air (m³)',
                        data: dataAir,
                        borderColor: th.airBorder,
                        backgroundColor: th.airBg,
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true,
                        pointBackgroundColor: th.airBorder,
                        pointRadius: 3
                    }
                ]
            },
            options: commonOptions
        });
    }

    // Theme reactivity
    window.addEventListener('kpr-theme-changed', function () {
        const next = getGmChartTheme();
        [chartL, chartA].forEach(ch => {
            if (!ch) return;
            ch.options.scales.x.grid.color = next.gridColor;
            ch.options.scales.x.ticks.color = next.tickColor;
            ch.options.scales.y.grid.color = next.gridColor;
            ch.options.scales.y.ticks.color = next.tickColor;
            if (ch.options.plugins && ch.options.plugins.legend) {
                ch.options.plugins.legend.labels.color = next.textColor;
            }
        });
        if (chartL) {
            chartL.data.datasets[0].backgroundColor = next.lwbpBg;
            chartL.data.datasets[0].borderColor = next.lwbpBorder;
            chartL.update();
        }
        if (chartA) {
            chartA.data.datasets[0].borderColor = next.airBorder;
            chartA.data.datasets[0].backgroundColor = next.airBg;
            chartA.data.datasets[0].pointBackgroundColor = next.airBorder;
            chartA.update();
        }
    });
});
</script>
@endpush
