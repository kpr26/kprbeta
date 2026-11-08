@extends('layouts.panel')

@php
    $n = fn ($v) => $v !== null ? number_format($v, 2, ',', '.') : '—';
    $rp = fn ($v) => $v !== null ? 'Rp ' . number_format($v, 2, ',', '.') : 'Rp 0';
    $rp0 = fn ($v) => $v !== null ? 'Rp ' . number_format($v, 0, ',', '.') : 'Rp 0';
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
        <div class="meter-card mc-total" style="border-top:3px solid #fbbf24;">
            <div class="mc-label" style="display:flex; justify-content:space-between; align-items:center;">
                <span style="display:flex; align-items:center; gap:8px;">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path d="M11.983 1.907a.75.75 0 0 0-1.292-.657l-8.5 9.5A.75.75 0 0 0 2.75 12h6.572l-1.305 6.093a.75.75 0 0 0 1.292.657l8.5-9.5A.75.75 0 0 0 17.25 8h-6.572l1.305-6.093Z"/></svg>
                    Total Listrik PLN (Gedung)
                </span>
                <span class="badge" style="background:rgba(251,191,36,0.15); color:#fbbf24; font-size:0.65rem; border:1px solid rgba(251,191,36,0.3); padding:2px 6px;">
                    Faktor 5.000x
                </span>
            </div>
            <div class="mc-value" style="color:#fbbf24;">{{ $n($summaryGedung['kwh']) }}<small>kWh</small></div>
            <div style="font-size:1.05rem; font-weight:700; color:var(--text-primary); margin:4px 0 6px; font-variant-numeric:tabular-nums;">
                {{ $rp($summaryGedung['total_rupiah']) }}
                <span style="font-size:0.7rem; font-weight:500; color:var(--text-muted);">(incl. PPJ 2,4%)</span>
            </div>
            <div class="mc-sub" style="font-size:0.78rem;">
                LWBP: <b>{{ $n($summaryGedung['lwbp']) }}</b> · WBP: <b>{{ $n($summaryGedung['wbp']) }}</b> kWh
            </div>
            <div style="font-size:0.75rem; color:var(--accent-light); margin-top:8px; padding-top:6px; border-top:1px dashed rgba(251,191,36,0.2);">
                Rata-rata: <b>{{ $n($summaryGedung['rata_kwh']) }} kWh / hari</b> ({{ $rp($summaryGedung['rata_rupiah']) }}/hari)
            </div>
        </div>

        {{-- Card 2: Air Gedung Utama --}}
        <div class="meter-card mc-air" style="border-top:3px solid #38bdf8;">
            <div class="mc-label" style="display:flex; justify-content:space-between; align-items:center;">
                <span style="display:flex; align-items:center; gap:8px;">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 2.5c.3 0 .58.14.77.37C12.4 4.87 16 9.45 16 12.5a6 6 0 0 1-12 0c0-3.05 3.6-7.63 5.23-9.63A1 1 0 0 1 10 2.5Z" clip-rule="evenodd"/></svg>
                    Total Air Gedung (Utama)
                </span>
                <span class="badge" style="background:rgba(56,189,248,0.15); color:#38bdf8; font-size:0.65rem; border:1px solid rgba(56,189,248,0.3); padding:2px 6px;">
                    Rp 21.500/m³
                </span>
            </div>
            <div class="mc-value" style="color:#38bdf8;">{{ $n($summaryGedung['air']) }}<small>m³</small></div>
            <div style="font-size:1.05rem; font-weight:700; color:var(--text-primary); margin:4px 0 6px; font-variant-numeric:tabular-nums;">
                {{ $rp($summaryGedung['biaya_air']) }}
            </div>
            <div class="mc-sub">
                Konsumsi air bersih meteran utama
            </div>
            <div style="font-size:0.75rem; color:var(--water); margin-top:8px; padding-top:6px; border-top:1px dashed rgba(56,189,248,0.2);">
                Rata-rata: <b>{{ $n($summaryGedung['rata_air']) }} m³ / hari</b> ({{ $rp($summaryGedung['rata_biaya_air']) }}/hari)
            </div>
        </div>

        {{-- Card 3: Air Public Area --}}
        <div class="meter-card" style="--c:#06b6d4; border-top:3px solid #06b6d4;">
            <div class="mc-label" style="display:flex; justify-content:space-between; align-items:center;">
                <span style="display:flex; align-items:center; gap:8px;">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v12a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V4Zm3 1h6v2H7V5Zm6 4H7v2h6V9Zm-6 4h6v2H7v-2Z" clip-rule="evenodd"/></svg>
                    Total Air Public Area
                </span>
                <span class="badge" style="background:rgba(6,182,212,0.15); color:#06b6d4; font-size:0.65rem; border:1px solid rgba(6,182,212,0.3); padding:2px 6px;">
                    Rp 21.500/m³
                </span>
            </div>
            <div class="mc-value" style="color:#06b6d4;">{{ $n($totalWaterPa) }}<small>m³</small></div>
            <div style="font-size:1.05rem; font-weight:700; color:var(--text-primary); margin:4px 0 6px; font-variant-numeric:tabular-nums;">
                {{ $rp($biayaWaterPa) }}
            </div>
            <div class="mc-sub">
                Cooling Tower: {{ $n($waterPaByMeter['cooling_tower']['total_pakai'] ?? 0) }} m³ ({{ $rp($waterPaByMeter['cooling_tower']['biaya'] ?? 0) }})
            </div>
            <div style="font-size:0.75rem; color:#06b6d4; margin-top:8px; padding-top:6px; border-top:1px dashed rgba(6,182,212,0.2);">
                Toilet: {{ $rp($waterPaByMeter['toilet_sky_lobby']['biaya'] ?? 0) }} &middot; Pantry: {{ $rp($waterPaByMeter['pantry_office']['biaya'] ?? 0) }}
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

    {{-- ── 3.A. Grand Total Executive Utility OpEx (Listrik + Air) ── --}}
    <div class="panel" style="margin-bottom:24px; padding:22px; border:1px solid rgba(16,185,129,0.35); background:linear-gradient(135deg, rgba(16,185,129,0.06) 0%, rgba(2,132,199,0.05) 50%, var(--bg-card) 100%); box-shadow:0 8px 24px rgba(0,0,0,0.15);">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:16px; margin-bottom:18px; padding-bottom:16px; border-bottom:1px solid var(--border);">
            <div>
                <div style="display:flex; align-items:center; gap:8px; margin-bottom:4px;">
                    <span class="badge" style="background:linear-gradient(135deg, #10b981, #0284c7); color:#fff; border:none; padding:4px 10px; font-weight:700; letter-spacing:0.04em;">
                        ⚡ + 💧 GRAND TOTAL OPEX UTILITAS
                    </span>
                    <span style="font-size:0.8rem; color:var(--accent); font-weight:600;">
                        Periode {{ $selectedMonthName }}
                    </span>
                </div>
                <h3 style="font-size:1.25rem; font-weight:700; color:var(--text-primary); margin:2px 0 0; font-family:var(--font-serif, 'Cormorant Garamond', Georgia, serif);">
                    Ikhtisar Eksekutif Biaya Utilitas Gedung (Listrik PLN &amp; Air PDAM)
                </h3>
                <p style="font-size:0.82rem; color:var(--text-muted); margin:4px 0 0;">
                    Ringkasan terpadu biaya operasional energi &amp; air bersih untuk kebutuhan analisis strategis General Manager.
                </p>
            </div>

            {{-- Grand Total Metric Callout --}}
            <div style="background:var(--bg-card); border:1.5px solid rgba(16,185,129,0.4); border-radius:var(--radius-md); padding:12px 20px; text-align:right; box-shadow:0 4px 14px rgba(16,185,129,0.15);">
                <div style="font-size:0.7rem; text-transform:uppercase; font-weight:800; color:var(--text-muted); letter-spacing:0.06em;">
                    Total Biaya Utilitas (Listrik + Air)
                </div>
                <div style="font-size:1.85rem; font-weight:800; color:#10b981; font-family:var(--font-serif, 'Cormorant Garamond', Georgia, serif); font-variant-numeric:tabular-nums; margin:2px 0;">
                    {{ $rp($summaryGedung['grand_total_utilitas']) }}
                </div>
                <div style="font-size:0.74rem; color:var(--text-secondary); font-weight:600;">
                    Rata-rata: <b>{{ $rp($summaryGedung['rata_grand_total']) }} / hari</b> ({{ $summaryGedung['jumlah_hari'] }} hari)
                </div>
            </div>
        </div>

        {{-- 2 Core Pillars: Listrik vs Air --}}
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:16px; margin-bottom:18px;">
            {{-- Biaya Listrik PLN --}}
            <div style="background:var(--bg-card-subtle); border:1px solid rgba(251,191,36,0.3); border-radius:var(--radius-md); padding:16px; position:relative; overflow:hidden;">
                <div style="position:absolute; top:0; left:0; width:4px; height:100%; background:#fbbf24;"></div>
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                    <span style="font-size:0.85rem; font-weight:700; color:#fbbf24;">
                        ⚡ Biaya Listrik PLN (AMR T33N)
                    </span>
                    <span class="badge" style="background:rgba(251,191,36,0.15); color:#fbbf24; font-size:0.72rem; font-weight:700;">
                        {{ $summaryGedung['persen_biaya_listrik'] }}% dari OpEx Utilitas
                    </span>
                </div>
                <div style="font-size:1.5rem; font-weight:700; color:var(--text-primary); font-variant-numeric:tabular-nums; font-family:var(--font-serif, 'Cormorant Garamond', Georgia, serif);">
                    {{ $rp($summaryGedung['total_rupiah']) }}
                </div>
                <div style="font-size:0.76rem; color:var(--text-secondary); margin-top:4px;">
                    Konsumsi: <b>{{ $n($summaryGedung['kwh']) }} kWh</b> (Faktor 5.000x &middot; incl. PPJ 2,4%)
                </div>
                <div style="font-size:0.73rem; color:var(--accent-light); margin-top:6px;">
                    Run-rate: <b>{{ $rp($summaryGedung['rata_rupiah']) }} / hari</b>
                </div>
            </div>

            {{-- Biaya Air Gedung Utama --}}
            <div style="background:var(--bg-card-subtle); border:1px solid rgba(56,189,248,0.3); border-radius:var(--radius-md); padding:16px; position:relative; overflow:hidden;">
                <div style="position:absolute; top:0; left:0; width:4px; height:100%; background:#38bdf8;"></div>
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                    <span style="font-size:0.85rem; font-weight:700; color:#38bdf8;">
                        💧 Biaya Air Bersih (Gedung Utama)
                    </span>
                    <span class="badge" style="background:rgba(56,189,248,0.15); color:#38bdf8; font-size:0.72rem; font-weight:700;">
                        {{ $summaryGedung['persen_biaya_air'] }}% dari OpEx Utilitas
                    </span>
                </div>
                <div style="font-size:1.5rem; font-weight:700; color:var(--text-primary); font-variant-numeric:tabular-nums; font-family:var(--font-serif, 'Cormorant Garamond', Georgia, serif);">
                    {{ $rp($summaryGedung['biaya_air']) }}
                </div>
                <div style="font-size:0.76rem; color:var(--text-secondary); margin-top:4px;">
                    Konsumsi: <b>{{ $n($summaryGedung['air']) }} m³</b> (Tarif Rp 21.500 / m³)
                </div>
                <div style="font-size:0.73rem; color:#38bdf8; margin-top:6px;">
                    Run-rate: <b>{{ $rp($summaryGedung['rata_biaya_air']) }} / hari</b>
                </div>
            </div>
        </div>

        {{-- Visual Utility Proportion Bar --}}
        <div>
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px; font-size:0.76rem; color:var(--text-muted);">
                <span>
                    ⚡ Listrik PLN: <b>{{ $summaryGedung['persen_biaya_listrik'] }}%</b> ({{ $rp($summaryGedung['total_rupiah']) }})
                </span>
                <span>
                    💧 Air Bersih: <b>{{ $summaryGedung['persen_biaya_air'] }}%</b> ({{ $rp($summaryGedung['biaya_air']) }})
                </span>
            </div>
            <div style="height:10px; border-radius:999px; overflow:hidden; display:flex; background:rgba(255,255,255,0.06);">
                <div style="width:{{ $summaryGedung['persen_biaya_listrik'] }}%; background:linear-gradient(90deg, #d97706, #fbbf24);" title="Listrik PLN: {{ $summaryGedung['persen_biaya_listrik'] }}%"></div>
                <div style="width:{{ $summaryGedung['persen_biaya_air'] }}%; background:linear-gradient(90deg, #0284c7, #38bdf8);" title="Air Gedung: {{ $summaryGedung['persen_biaya_air'] }}%"></div>
            </div>
        </div>
    </div>

    {{-- ── 3.B. Strategic Executive Energy & Financial Analysis (Listrik PLN AMR T33N) ── --}}
    <div class="panel" style="margin-bottom:24px; padding:22px; border:1px solid rgba(251,191,36,0.35); background:linear-gradient(180deg, rgba(251,191,36,0.05) 0%, var(--bg-card) 100%); box-shadow:0 8px 24px rgba(0,0,0,0.15);">
        {{-- Header Section --}}
        <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:14px; margin-bottom:20px; padding-bottom:16px; border-bottom:1px solid var(--border);">
            <div>
                <div style="display:flex; align-items:center; gap:8px; margin-bottom:4px;">
                    <span style="display:inline-flex; align-items:center; justify-content:center; width:28px; height:28px; border-radius:8px; background:rgba(251,191,36,0.15); color:#fbbf24; font-size:0.95rem; border:1px solid rgba(251,191,36,0.3);">
                        ⚡
                    </span>
                    <h3 style="font-size:1.15rem; font-weight:700; color:var(--text-primary); margin:0; font-family:var(--font-serif, 'Cormorant Garamond', Georgia, serif);">
                        Ringkasan Strategis Biaya &amp; Konsumsi Listrik PLN (AMR T33N)
                    </h3>
                </div>
                <p style="font-size:0.82rem; color:var(--text-muted); margin:0; line-height:1.4;">
                    Monitoring terintegrasi konsumsi energi aktual dengan <b>Faktor Kali Meter 5.000x (CT/PT)</b> dan kalkulasi tarif resmi operasional (OpEx) periode <b>{{ $selectedMonthName }}</b>.
                </p>
            </div>

            {{-- Strategic Parameter Badges --}}
            <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                <span class="badge" style="background:rgba(251,191,36,0.12); color:#fbbf24; border:1px solid rgba(251,191,36,0.3); font-size:0.72rem; padding:4px 9px; font-weight:600;">
                    ⚙️ Faktor Meter: <b>5.000x</b>
                </span>
                <span class="badge" style="background:rgba(56,189,248,0.12); color:#38bdf8; border:1px solid rgba(56,189,248,0.3); font-size:0.72rem; padding:4px 9px; font-weight:600;">
                    💡 Tarif LWBP: <b>Rp 1.035,78</b>/kWh
                </span>
                <span class="badge" style="background:rgba(244,63,94,0.12); color:#fb7185; border:1px solid rgba(244,63,94,0.3); font-size:0.72rem; padding:4px 9px; font-weight:600;">
                    🔥 Tarif WBP: <b>Rp 1.553,67</b>/kWh
                </span>
                <span class="badge" style="background:rgba(16,185,129,0.12); color:#34d399; border:1px solid rgba(16,185,129,0.3); font-size:0.72rem; padding:4px 9px; font-weight:600;">
                    🏛️ Pajak Penerangan: <b>2,4%</b>
                </span>
            </div>
        </div>

        {{-- 4 Executive Metric Pillars --}}
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:16px; margin-bottom:22px;">
            {{-- Pillar 1: Total Energi Aktual --}}
            <div style="background:var(--bg-card-subtle); border:1px solid var(--border); border-radius:var(--radius-md); padding:16px;">
                <div style="font-size:0.72rem; text-transform:uppercase; font-weight:700; color:var(--text-muted); letter-spacing:0.05em; margin-bottom:6px;">
                    1. Konsumsi Total Aktual
                </div>
                <div style="font-size:1.6rem; font-weight:700; color:#fbbf24; font-variant-numeric:tabular-nums; font-family:var(--font-serif, 'Cormorant Garamond', Georgia, serif);">
                    {{ $n($summaryGedung['kwh']) }} <span style="font-size:0.85rem; font-family:var(--font-sans, sans-serif); color:var(--text-secondary); font-weight:500;">kWh</span>
                </div>
                <div style="font-size:0.76rem; color:var(--text-secondary); margin-top:4px;">
                    Rumus: <code>LWBP + WBP</code> (Faktor 5.000)
                </div>
                <div style="font-size:0.74rem; color:var(--accent-light); margin-top:6px; font-weight:600;">
                    Rata-rata: {{ $n($summaryGedung['rata_kwh']) }} kWh / hari
                </div>
            </div>

            {{-- Pillar 2: Subtotal Biaya Pemakaian --}}
            <div style="background:var(--bg-card-subtle); border:1px solid var(--border); border-radius:var(--radius-md); padding:16px;">
                <div style="font-size:0.72rem; text-transform:uppercase; font-weight:700; color:var(--text-muted); letter-spacing:0.05em; margin-bottom:6px;">
                    2. Subtotal Pemakaian Listrik
                </div>
                <div style="font-size:1.6rem; font-weight:700; color:var(--text-primary); font-variant-numeric:tabular-nums; font-family:var(--font-serif, 'Cormorant Garamond', Georgia, serif);">
                    {{ $rp($summaryGedung['subtotal_rupiah']) }}
                </div>
                <div style="font-size:0.76rem; color:var(--text-secondary); margin-top:4px;">
                    Biaya LWBP + Biaya WBP (Sebelum Pajak)
                </div>
                <div style="font-size:0.74rem; color:var(--text-muted); margin-top:6px;">
                    Beban harian: {{ $rp($summaryGedung['rata_subtotal_rp']) }} / hari
                </div>
            </div>

            {{-- Pillar 3: Pajak Penerangan Jalan --}}
            <div style="background:var(--bg-card-subtle); border:1px solid var(--border); border-radius:var(--radius-md); padding:16px;">
                <div style="font-size:0.72rem; text-transform:uppercase; font-weight:700; color:var(--text-muted); letter-spacing:0.05em; margin-bottom:6px;">
                    3. Pajak Penerangan (PBJT 2,4%)
                </div>
                <div style="font-size:1.6rem; font-weight:700; color:#38bdf8; font-variant-numeric:tabular-nums; font-family:var(--font-serif, 'Cormorant Garamond', Georgia, serif);">
                    {{ $rp($summaryGedung['pajak_penerangan']) }}
                </div>
                <div style="font-size:0.76rem; color:var(--text-secondary); margin-top:4px;">
                    Biaya 2,4% atas Subtotal Pemakaian
                </div>
                <div style="font-size:0.74rem; color:var(--text-muted); margin-top:6px;">
                    Tarif PBJT Tenaga Listrik Daerah
                </div>
            </div>

            {{-- Pillar 4: Total Tagihan PLN --}}
            <div style="background:linear-gradient(135deg, rgba(251,191,36,0.12), rgba(251,191,36,0.03)); border:1.5px solid rgba(251,191,36,0.4); border-radius:var(--radius-md); padding:16px; box-shadow:0 4px 14px rgba(251,191,36,0.1);">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                    <span style="font-size:0.72rem; text-transform:uppercase; font-weight:800; color:#fbbf24; letter-spacing:0.05em;">
                        4. Total Estimasi Tagihan PLN
                    </span>
                    <span class="badge" style="background:#fbbf24; color:#0f172a; font-weight:700; font-size:0.62rem; padding:1px 5px;">
                        TOTAL OPEX
                    </span>
                </div>
                <div style="font-size:1.6rem; font-weight:800; color:#fbbf24; font-variant-numeric:tabular-nums; font-family:var(--font-serif, 'Cormorant Garamond', Georgia, serif);">
                    {{ $rp($summaryGedung['total_rupiah']) }}
                </div>
                <div style="font-size:0.76rem; color:var(--text-primary); margin-top:4px; font-weight:600;">
                    Subtotal + Pajak Penerangan (2,4%)
                </div>
                <div style="font-size:0.74rem; color:#fde047; margin-top:6px; font-weight:600;">
                    Run-rate: {{ $rp($summaryGedung['rata_rupiah']) }} / hari
                </div>
            </div>
        </div>

        {{-- Comparative Split: LWBP vs WBP --}}
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(320px, 1fr)); gap:16px; margin-bottom:20px;">
            {{-- LWBP Card --}}
            <div style="background:var(--bg-card-subtle); border:1px solid rgba(56,189,248,0.3); border-radius:var(--radius-md); padding:16px; position:relative; overflow:hidden;">
                <div style="position:absolute; top:0; left:0; width:4px; height:100%; background:#38bdf8;"></div>
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                    <span style="font-size:0.88rem; font-weight:700; color:var(--text-primary);">
                        🌙 LWBP (Luar Waktu Beban Puncak)
                    </span>
                    <span class="badge" style="background:rgba(56,189,248,0.15); color:#38bdf8; font-size:0.7rem; font-weight:600;">
                        Tarif: Rp 1.035,78 / kWh
                    </span>
                </div>

                <div style="background:rgba(0,0,0,0.25); border-radius:var(--radius-sm); padding:8px 12px; margin-bottom:12px; font-size:0.76rem; color:var(--text-secondary); font-family:monospace;">
                    Formula: (LBP1 &times; 5.000) + (LBP2 &times; 5.000) = LWBP (kWh)
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:10px;">
                    <div>
                        <div style="font-size:0.7rem; color:var(--text-muted); text-transform:uppercase;">Energi Aktual</div>
                        <div style="font-size:1.25rem; font-weight:700; color:#38bdf8;">
                            {{ $n($summaryGedung['lwbp']) }} <small style="font-size:0.75rem; color:var(--text-muted);">kWh</small>
                        </div>
                        <div style="font-size:0.72rem; color:var(--text-muted);">
                            Porsi Energi: <b>{{ $summaryGedung['persen_lwbp'] }}%</b>
                        </div>
                    </div>
                    <div>
                        <div style="font-size:0.7rem; color:var(--text-muted); text-transform:uppercase;">Subtotal Biaya LWBP</div>
                        <div style="font-size:1.25rem; font-weight:700; color:var(--text-primary);">
                            {{ $rp($summaryGedung['biaya_lwbp']) }}
                        </div>
                        <div style="font-size:0.72rem; color:var(--text-muted);">
                            Porsi Biaya: <b>{{ $summaryGedung['persen_biaya_lwbp'] }}%</b>
                        </div>
                    </div>
                </div>

                <div style="font-size:0.74rem; color:var(--text-muted); line-height:1.4; border-top:1px solid var(--border); padding-top:8px;">
                    🕒 <b>19 Jam Operasional (22:00 - 17:00 WIB)</b> &middot; Tarif dasar efisien untuk operasional rutin chiller, tata udara hunian &amp; fasilitas umum.
                </div>
            </div>

            {{-- WBP Card --}}
            <div style="background:var(--bg-card-subtle); border:1px solid rgba(244,63,94,0.3); border-radius:var(--radius-md); padding:16px; position:relative; overflow:hidden;">
                <div style="position:absolute; top:0; left:0; width:4px; height:100%; background:#f43f5e;"></div>
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                    <span style="font-size:0.88rem; font-weight:700; color:var(--text-primary);">
                        🔥 WBP (Waktu Beban Puncak)
                    </span>
                    <span class="badge" style="background:rgba(244,63,94,0.15); color:#fb7185; font-size:0.7rem; font-weight:600;">
                        Tarif: Rp 1.553,67 / kWh (+50%)
                    </span>
                </div>

                <div style="background:rgba(0,0,0,0.25); border-radius:var(--radius-sm); padding:8px 12px; margin-bottom:12px; font-size:0.76rem; color:var(--text-secondary); font-family:monospace;">
                    Formula: (BP &times; 5.000) = WBP (kWh)
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:10px;">
                    <div>
                        <div style="font-size:0.7rem; color:var(--text-muted); text-transform:uppercase;">Energi Aktual</div>
                        <div style="font-size:1.25rem; font-weight:700; color:#fb7185;">
                            {{ $n($summaryGedung['wbp']) }} <small style="font-size:0.75rem; color:var(--text-muted);">kWh</small>
                        </div>
                        <div style="font-size:0.72rem; color:var(--text-muted);">
                            Porsi Energi: <b>{{ $summaryGedung['persen_wbp'] }}%</b>
                        </div>
                    </div>
                    <div>
                        <div style="font-size:0.7rem; color:var(--text-muted); text-transform:uppercase;">Subtotal Biaya WBP</div>
                        <div style="font-size:1.25rem; font-weight:700; color:var(--text-primary);">
                            {{ $rp($summaryGedung['biaya_wbp']) }}
                        </div>
                        <div style="font-size:0.72rem; color:var(--text-muted);">
                            Porsi Biaya: <b>{{ $summaryGedung['persen_biaya_wbp'] }}%</b>
                        </div>
                    </div>
                </div>

                <div style="font-size:0.74rem; color:var(--text-muted); line-height:1.4; border-top:1px solid var(--border); padding-top:8px;">
                    🕒 <b>5 Jam Beban Puncak (17:00 - 22:00 WIB)</b> &middot; Tarif premium 150%. Manajemen operasional chiller, pompa &amp; penerangan fasad pada jam ini sangat krusial untuk menekan tagihan.
                </div>
            </div>
        </div>

        {{-- Visual Split Bar & Strategic Summary Table --}}
        <div style="background:var(--bg-card-subtle); border:1px solid var(--border); border-radius:var(--radius-md); padding:16px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px; flex-wrap:wrap; gap:8px;">
                <span style="font-size:0.78rem; font-weight:700; color:var(--text-primary); text-transform:uppercase; letter-spacing:0.04em;">
                    Proporsi Konsumsi Energi: LWBP vs WBP
                </span>
                <span style="font-size:0.75rem; color:var(--text-muted);">
                    Total: <b>{{ $n($summaryGedung['kwh']) }} kWh</b> (100%)
                </span>
            </div>

            {{-- Dual Color Progress Bar --}}
            <div style="height:10px; border-radius:999px; overflow:hidden; display:flex; background:rgba(255,255,255,0.06); margin-bottom:14px;">
                <div style="width:{{ $summaryGedung['persen_lwbp'] }}%; background:linear-gradient(90deg, #0284c7, #38bdf8);" title="LWBP: {{ $summaryGedung['persen_lwbp'] }}%"></div>
                <div style="width:{{ $summaryGedung['persen_wbp'] }}%; background:linear-gradient(90deg, #f43f5e, #fb7185);" title="WBP: {{ $summaryGedung['persen_wbp'] }}%"></div>
            </div>

            {{-- Strategic Transparency Table --}}
            <div class="table-wrap" style="margin-top:10px;">
                <table class="log-table" style="font-size:0.8rem; margin:0;">
                    <thead>
                        <tr>
                            <th class="l">Komponen Tarif PLN</th>
                            <th class="r">Stand Mentah (Delta)</th>
                            <th class="c">Faktor Meter</th>
                            <th class="r">Konsumsi Aktual (kWh)</th>
                            <th class="r">Tarif Satuan</th>
                            <th class="r">Subtotal Biaya</th>
                            <th class="c">Proporsi Energi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="l">
                                <span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:#38bdf8; margin-right:6px;"></span>
                                <b>LWBP</b> (Luar Waktu Beban Puncak: LBP 1 + LBP 2)
                            </td>
                            <td class="r" style="font-family:monospace;">{{ $n($summaryGedung['raw_lwbp']) }}</td>
                            <td class="c"><span class="badge" style="background:rgba(251,191,36,0.1); color:#fbbf24; font-size:0.7rem;">5.000x</span></td>
                            <td class="r" style="color:#38bdf8; font-weight:700;">{{ $n($summaryGedung['lwbp']) }} kWh</td>
                            <td class="r">Rp 1.035,78</td>
                            <td class="r" style="font-weight:600;">{{ $rp($summaryGedung['biaya_lwbp']) }}</td>
                            <td class="c"><b>{{ $summaryGedung['persen_lwbp'] }}%</b></td>
                        </tr>
                        <tr>
                            <td class="l">
                                <span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:#f43f5e; margin-right:6px;"></span>
                                <b>WBP</b> (Waktu Beban Puncak: BP 17:00 - 22:00 WIB)
                            </td>
                            <td class="r" style="font-family:monospace;">{{ $n($summaryGedung['raw_wbp']) }}</td>
                            <td class="c"><span class="badge" style="background:rgba(251,191,36,0.1); color:#fbbf24; font-size:0.7rem;">5.000x</span></td>
                            <td class="r" style="color:#fb7185; font-weight:700;">{{ $n($summaryGedung['wbp']) }} kWh</td>
                            <td class="r">Rp 1.553,67</td>
                            <td class="r" style="font-weight:600;">{{ $rp($summaryGedung['biaya_wbp']) }}</td>
                            <td class="c"><b>{{ $summaryGedung['persen_wbp'] }}%</b></td>
                        </tr>
                        <tr style="border-top:1.5px solid var(--border); font-weight:700;">
                            <td class="l" colspan="3"><b>JUMLAH PEMAKAIAN LISTRIK (SUBTOTAL)</b></td>
                            <td class="r" style="color:#fbbf24; font-weight:800;">{{ $n($summaryGedung['kwh']) }} kWh</td>
                            <td class="r">—</td>
                            <td class="r" style="color:var(--text-primary); font-weight:800;">{{ $rp($summaryGedung['subtotal_rupiah']) }}</td>
                            <td class="c"><b>100%</b></td>
                        </tr>
                        <tr>
                            <td class="l" colspan="4">
                                🏛️ <b>Pajak Penerangan Jalan (PBJT Tenaga Listrik 2,4%)</b>
                            </td>
                            <td class="r">2,4%</td>
                            <td class="r" style="color:#38bdf8; font-weight:700;">+ {{ $rp($summaryGedung['pajak_penerangan']) }}</td>
                            <td class="c"><span class="badge" style="font-size:0.65rem; background:rgba(56,189,248,0.15); color:#38bdf8;">Pajak Daerah</span></td>
                        </tr>
                        <tr style="background:rgba(251,191,36,0.06); font-weight:800; border-top:2px solid rgba(251,191,36,0.3);">
                            <td class="l" colspan="3" style="color:#fbbf24; font-size:0.88rem;">
                                ⚡ TOTAL ESTIMASI BIAYA LISTRIK PLN (GEDUNG)
                            </td>
                            <td class="r" style="color:#fbbf24; font-size:0.95rem;">
                                {{ $n($summaryGedung['kwh']) }} kWh
                            </td>
                            <td class="r" style="font-size:0.75rem; color:var(--text-muted);">
                                Rata-rata: {{ $n($summaryGedung['rata_kwh']) }} kWh/hr
                            </td>
                            <td class="r" style="color:#fbbf24; font-size:1.05rem;">
                                {{ $rp($summaryGedung['total_rupiah']) }}
                            </td>
                            <td class="c">
                                <span class="badge" style="background:#fbbf24; color:#0f172a; font-weight:800; font-size:0.7rem; padding:3px 8px;">
                                    FINAL OPEX
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ── 3.C. Ringkasan Eksekutif Penagihan Utilitas Tenant & Recovery Analysis ── --}}
    @if(isset($tenantBillingSummary) && $tenantBillingSummary['has_data'])
    <div class="panel" style="margin-bottom:24px; padding:22px; border:1.5px solid rgba(250,204,21,0.35); background:linear-gradient(135deg, rgba(250,204,21,0.06) 0%, rgba(56,189,248,0.05) 50%, var(--bg-card) 100%); box-shadow:0 8px 24px rgba(0,0,0,0.15);">
        {{-- Header Section --}}
        <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:16px; margin-bottom:20px; padding-bottom:16px; border-bottom:1px solid var(--border);">
            <div>
                <div style="display:flex; align-items:center; gap:8px; margin-bottom:4px;">
                    <span class="badge" style="background:linear-gradient(135deg, #f59e0b, #0ea5e9); color:#fff; border:none; padding:4px 10px; font-weight:700; letter-spacing:0.04em;">
                        🏢 + ⚡ + 💧 PENAGIHAN UTILITAS TENANT
                    </span>
                    <span style="font-size:0.8rem; color:var(--accent); font-weight:600;">
                        Periode {{ $selectedMonthName }} · 261 Tenant Residence
                    </span>
                </div>
                <h3 style="font-size:1.25rem; font-weight:700; color:var(--text-primary); margin:2px 0 0; font-family:var(--font-serif, 'Cormorant Garamond', Georgia, serif);">
                    Ringkasan Penagihan Utilitas Tenant &amp; Analisis Pemulihan Biaya (Cost Recovery)
                </h3>
                <p style="font-size:0.82rem; color:var(--text-muted); margin:4px 0 0; line-height:1.4;">
                    Rekapitulasi penggunaan listrik (reguler &amp; 11 fasilitas EV charger) serta air bersih bulanan seluruh tenant. Kalkulasi billing mengacu pada ketentuan resmi: <b>Tarif Rp 1.699,53/kWh, Minimum 40 Jam Nyala, PPJ 2,4%, Biaya Materai Rp 10.000</b>, dan <b>Tarif Air Rp 21.500/m³</b>.
                </p>
            </div>

            <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                <a href="{{ route('billing.utilities.hub', ['year' => $year, 'month' => $month]) }}" class="btn btn-sm btn-primary" style="padding:8px 14px; font-size:0.82rem; font-weight:700;">
                    ⚙️ Buka Menu Utilitas &rarr;
                </a>
            </div>
        </div>

        {{-- 4 Kartu Metrik Utama Penagihan Tenant --}}
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(240px, 1fr)); gap:16px; margin-bottom:20px;">
            {{-- Card 1: Grand Total Tagihan Tenant --}}
            <div style="background:var(--bg-card); border:1.5px solid rgba(16,185,129,0.4); border-radius:var(--radius-md); padding:16px; position:relative; overflow:hidden; box-shadow:0 4px 12px rgba(16,185,129,0.1);">
                <div style="position:absolute; top:0; left:0; width:4px; height:100%; background:#10b981;"></div>
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                    <span style="font-size:0.75rem; text-transform:uppercase; font-weight:800; color:var(--text-muted); letter-spacing:0.04em;">Grand Total Penagihan</span>
                    <span class="badge" style="background:rgba(16,185,129,0.15); color:#10b981; font-size:0.7rem; font-weight:700;">Listrik + Air</span>
                </div>
                <div style="font-size:1.6rem; font-weight:800; color:#10b981; font-family:var(--font-serif, 'Cormorant Garamond', Georgia, serif); font-variant-numeric:tabular-nums; margin:2px 0;">
                    {{ $rp($tenantBillingSummary['grand_total_billing']) }}
                </div>
                <div style="font-size:0.75rem; color:var(--text-secondary); margin-top:4px;">
                    Pemulihan Beban Utilitas: <b style="color:#10b981;">{{ $tenantBillingSummary['total_recovery_pct'] }}%</b> dari total OpEx
                </div>
            </div>

            {{-- Card 2: Listrik Reguler --}}
            <div style="background:var(--bg-card); border:1.5px solid rgba(250,204,21,0.35); border-radius:var(--radius-md); padding:16px; position:relative; overflow:hidden;">
                <div style="position:absolute; top:0; left:0; width:4px; height:100%; background:#facc15;"></div>
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                    <span style="font-size:0.75rem; text-transform:uppercase; font-weight:800; color:var(--text-muted); letter-spacing:0.04em;">⚡ Listrik Reguler ({{ $tenantBillingSummary['count_meter_regular'] }} Unit)</span>
                    <span class="badge" style="background:rgba(250,204,21,0.15); color:#facc15; font-size:0.7rem; font-weight:700;">{{ $n($tenantBillingSummary['kwh_regular']) }} kWh</span>
                </div>
                <div style="font-size:1.5rem; font-weight:700; color:var(--text-primary); font-family:var(--font-serif, 'Cormorant Garamond', Georgia, serif); font-variant-numeric:tabular-nums; margin:2px 0;">
                    {{ $rp($tenantBillingSummary['regular_total']) }}
                </div>
                <div style="font-size:0.73rem; color:var(--text-muted); margin-top:4px;">
                    Billing: {{ $rp($tenantBillingSummary['regular_billing']) }} · PPJ: {{ $rp($tenantBillingSummary['regular_ppj']) }} · Materai: {{ $rp($tenantBillingSummary['regular_materai']) }}
                </div>
                <div style="font-size:0.72rem; color:var(--accent-light); margin-top:4px;">
                    Unit kena batas minimum 40 jam: <b>{{ $tenantBillingSummary['regular_min_applied'] }} unit</b>
                </div>
            </div>

            {{-- Card 3: Fasilitas EV Charging --}}
            <div style="background:var(--bg-card); border:1.5px solid rgba(56,189,248,0.35); border-radius:var(--radius-md); padding:16px; position:relative; overflow:hidden;">
                <div style="position:absolute; top:0; left:0; width:4px; height:100%; background:#38bdf8;"></div>
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                    <span style="font-size:0.75rem; text-transform:uppercase; font-weight:800; color:var(--text-muted); letter-spacing:0.04em;">🚗 Fasilitas EV ({{ $tenantBillingSummary['count_meter_ev'] }} Unit)</span>
                    <span class="badge" style="background:rgba(56,189,248,0.15); color:#38bdf8; font-size:0.7rem; font-weight:700;">{{ $n($tenantBillingSummary['kwh_ev']) }} kWh</span>
                </div>
                <div style="font-size:1.5rem; font-weight:700; color:var(--text-primary); font-family:var(--font-serif, 'Cormorant Garamond', Georgia, serif); font-variant-numeric:tabular-nums; margin:2px 0;">
                    {{ $rp($tenantBillingSummary['ev_total']) }}
                </div>
                <div style="font-size:0.73rem; color:var(--text-muted); margin-top:4px;">
                    Billing: {{ $rp($tenantBillingSummary['ev_billing']) }} · PPJ: {{ $rp($tenantBillingSummary['ev_ppj']) }} · Materai: {{ $rp($tenantBillingSummary['ev_materai']) }}
                </div>
                <div style="font-size:0.72rem; color:#38bdf8; margin-top:4px;">
                    Total Titik Listrik: <b>{{ $tenantBillingSummary['count_total_meter'] }} titik meter</b>
                </div>
            </div>

            {{-- Card 4: Air Bersih Tenant --}}
            <div style="background:var(--bg-card); border:1.5px solid rgba(6,182,212,0.35); border-radius:var(--radius-md); padding:16px; position:relative; overflow:hidden;">
                <div style="position:absolute; top:0; left:0; width:4px; height:100%; background:#06b6d4;"></div>
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                    <span style="font-size:0.75rem; text-transform:uppercase; font-weight:800; color:var(--text-muted); letter-spacing:0.04em;">💧 Air Bersih ({{ $tenantBillingSummary['count_tenant_air'] }} Unit)</span>
                    <span class="badge" style="background:rgba(6,182,212,0.15); color:#06b6d4; font-size:0.7rem; font-weight:700;">{{ $n($tenantBillingSummary['total_m3_air']) }} m³</span>
                </div>
                <div style="font-size:1.5rem; font-weight:700; color:var(--text-primary); font-family:var(--font-serif, 'Cormorant Garamond', Georgia, serif); font-variant-numeric:tabular-nums; margin:2px 0;">
                    {{ $rp($tenantBillingSummary['grand_total_air']) }}
                </div>
                <div style="font-size:0.73rem; color:var(--text-muted); margin-top:4px;">
                    Tarif resmi: Rp 21.500 / m³ · Total konsumsi: <b>{{ $n($tenantBillingSummary['total_m3_air']) }} m³</b>
                </div>
                <div style="font-size:0.72rem; color:#06b6d4; margin-top:4px;">
                    Pemulihan Beban Air Gedung: <b>{{ $tenantBillingSummary['pdam_recovery_pct'] }}%</b>
                </div>
            </div>
        </div>

        {{-- Tabel Breakdown Tagihan Tenant & Ketentuan --}}
        <div style="background:var(--bg-card-subtle); border:1px solid var(--border); border-radius:var(--radius-md); padding:16px; margin-bottom:16px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; flex-wrap:wrap; gap:8px;">
                <span style="font-size:0.8rem; font-weight:700; color:var(--text-primary); text-transform:uppercase; letter-spacing:0.04em;">
                    Rincian Perhitungan Tagihan Utilitas Tenant (Ketentuan Tarif, Minimum Jam Nyala, PPJ, Materai)
                </span>
                <span style="font-size:0.75rem; color:var(--text-muted);">
                    Semua komponen ditagihkan secara resmi per tenant
                </span>
            </div>

            <div class="table-responsive">
                <table class="data-table" style="font-size:0.8rem; width:100%;">
                    <thead>
                        <tr>
                            <th class="l" style="width:22%;">Komponen Utilitas</th>
                            <th class="c" style="width:9%;">Titik Meter</th>
                            <th class="r" style="width:12%;">Pemakaian (Volume)</th>
                            <th class="r" style="width:12%;">Tarif Dasar</th>
                            <th class="r" style="width:13%;">Billing / Biaya Pokok</th>
                            <th class="r" style="width:10%;">PPJ (2,4%)</th>
                            <th class="r" style="width:9%;">Materai 10k</th>
                            <th class="r" style="width:13%;">Total Tagihan (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="l">
                                <b style="color:#facc15;">⚡ Listrik Hunian Reguler</b>
                                <div style="font-size:0.7rem; color:var(--text-muted);">Ketentuan: max(40 Jam x Daya, Aktual) · {{ $tenantBillingSummary['regular_min_applied'] }} unit kena minimum</div>
                            </td>
                            <td class="c"><b>{{ $tenantBillingSummary['count_meter_regular'] }}</b> unit</td>
                            <td class="r" style="font-weight:700; color:#facc15;">{{ $n($tenantBillingSummary['kwh_regular']) }} kWh</td>
                            <td class="r">Rp 1.699,53</td>
                            <td class="r" style="font-weight:600;">{{ $rp($tenantBillingSummary['regular_billing']) }}</td>
                            <td class="r" style="color:#38bdf8;">{{ $rp($tenantBillingSummary['regular_ppj']) }}</td>
                            <td class="r">{{ $rp($tenantBillingSummary['regular_materai']) }}</td>
                            <td class="r" style="font-weight:800; color:var(--text-primary);">{{ $rp($tenantBillingSummary['regular_total']) }}</td>
                        </tr>
                        <tr>
                            <td class="l">
                                <b style="color:#38bdf8;">🚗 Listrik Fasilitas EV Charger</b>
                                <div style="font-size:0.7rem; color:var(--text-muted);">Ketentuan: max(40 Jam x Daya EV, Aktual) · {{ $tenantBillingSummary['ev_min_applied'] }} unit kena minimum</div>
                            </td>
                            <td class="c"><b>{{ $tenantBillingSummary['count_meter_ev'] }}</b> unit</td>
                            <td class="r" style="font-weight:700; color:#38bdf8;">{{ $n($tenantBillingSummary['kwh_ev']) }} kWh</td>
                            <td class="r">Rp 1.699,53</td>
                            <td class="r" style="font-weight:600;">{{ $rp($tenantBillingSummary['ev_billing']) }}</td>
                            <td class="r" style="color:#38bdf8;">{{ $rp($tenantBillingSummary['ev_ppj']) }}</td>
                            <td class="r">{{ $rp($tenantBillingSummary['ev_materai']) }}</td>
                            <td class="r" style="font-weight:800; color:var(--text-primary);">{{ $rp($tenantBillingSummary['ev_total']) }}</td>
                        </tr>
                        <tr style="background:rgba(250,204,21,0.04); font-weight:700; border-top:1px dashed var(--border);">
                            <td class="l" colspan="1"><b>SUBTOTAL LISTRIK RESIDENCE</b></td>
                            <td class="c"><b>{{ $tenantBillingSummary['count_total_meter'] }}</b> titik</td>
                            <td class="r" style="color:#facc15;">{{ $n($tenantBillingSummary['total_kwh']) }} kWh</td>
                            <td class="r">—</td>
                            <td class="r">{{ $rp($tenantBillingSummary['total_billing_listrik']) }}</td>
                            <td class="r" style="color:#38bdf8;">{{ $rp($tenantBillingSummary['total_ppj_listrik']) }}</td>
                            <td class="r">{{ $rp($tenantBillingSummary['total_materai_listrik']) }}</td>
                            <td class="r" style="color:#facc15; font-size:0.92rem; font-weight:800;">{{ $rp($tenantBillingSummary['grand_total_listrik']) }}</td>
                        </tr>
                        <tr>
                            <td class="l">
                                <b style="color:#06b6d4;">💧 Air Bersih Hunian (PAM / Gedung)</b>
                                <div style="font-size:0.7rem; color:var(--text-muted);">Kalkulasi: Volume Pemakaian m³ &times; Rp 21.500</div>
                            </td>
                            <td class="c"><b>{{ $tenantBillingSummary['count_tenant_air'] }}</b> unit</td>
                            <td class="r" style="font-weight:700; color:#06b6d4;">{{ $n($tenantBillingSummary['total_m3_air']) }} m³</td>
                            <td class="r">Rp 21.500,00</td>
                            <td class="r" style="font-weight:600;">{{ $rp($tenantBillingSummary['grand_total_air']) }}</td>
                            <td class="r" style="color:var(--text-muted);">—</td>
                            <td class="r" style="color:var(--text-muted);">—</td>
                            <td class="r" style="font-weight:800; color:#06b6d4;">{{ $rp($tenantBillingSummary['grand_total_air']) }}</td>
                        </tr>
                        <tr style="background:rgba(16,185,129,0.08); font-weight:800; border-top:2px solid rgba(16,185,129,0.3); font-size:0.88rem;">
                            <td class="l" colspan="2" style="color:#10b981;">
                                🏢 GRAND TOTAL PENAGIHAN TENANT (BULANAN)
                            </td>
                            <td class="r" colspan="2" style="font-size:0.75rem; color:var(--text-muted);">
                                261 Tenant Aktif (272 Titik Listrik + 261 Air)
                            </td>
                            <td class="r" colspan="3" style="font-size:0.75rem; color:var(--text-muted);">
                                Terdiri dari Billing, PPJ &amp; Materai
                            </td>
                            <td class="r" style="color:#10b981; font-size:1.05rem;">
                                {{ $rp($tenantBillingSummary['grand_total_billing']) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Executive Recovery Comparison Box (Beban Gedung vs Tagihan Tenant) --}}
        <div style="background:var(--bg-card); border:1px solid var(--border); border-radius:var(--radius-md); padding:16px;">
            <div style="font-size:0.8rem; font-weight:700; color:var(--text-primary); margin-bottom:10px; text-transform:uppercase; letter-spacing:0.04em;">
                ⚖️ Rekonsiliasi &amp; Analisis Beban Utilitas Bersama (Gedung vs Tenant)
            </div>
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:14px;">
                {{-- Listrik Gedung vs Tenant --}}
                <div style="padding:12px; background:var(--bg-card-subtle); border-radius:var(--radius-sm); border:1px solid var(--border);">
                    <div style="display:flex; justify-content:space-between; margin-bottom:6px; font-size:0.78rem;">
                        <span style="color:var(--text-muted);">Beban Tagihan Listrik PLN:</span>
                        <b style="color:var(--text-primary);">{{ $rp($summaryGedung['total_rupiah']) }}</b>
                    </div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:6px; font-size:0.78rem;">
                        <span style="color:var(--text-muted);">Total Ditagihkan ke Tenant:</span>
                        <b style="color:#10b981;">{{ $rp($tenantBillingSummary['grand_total_listrik']) }}</b>
                    </div>
                    <div style="display:flex; justify-content:space-between; padding-top:6px; border-top:1px dashed var(--border); font-size:0.78rem;">
                        <span style="color:var(--text-muted);">Selisih (Beban Public Area / Losses):</span>
                        <b style="color:{{ $tenantBillingSummary['net_selisih_listrik'] >= 0 ? '#fbbf24' : '#10b981' }};">
                            {{ $rp($tenantBillingSummary['net_selisih_listrik']) }} ({{ $tenantBillingSummary['pln_recovery_pct'] }}% Pulih)
                        </b>
                    </div>
                </div>

                {{-- Air Gedung vs Tenant --}}
                <div style="padding:12px; background:var(--bg-card-subtle); border-radius:var(--radius-sm); border:1px solid var(--border);">
                    <div style="display:flex; justify-content:space-between; margin-bottom:6px; font-size:0.78rem;">
                        <span style="color:var(--text-muted);">Beban Biaya Air Utama Gedung:</span>
                        <b style="color:var(--text-primary);">{{ $rp($summaryGedung['biaya_air']) }}</b>
                    </div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:6px; font-size:0.78rem;">
                        <span style="color:var(--text-muted);">Total Ditagihkan ke Tenant:</span>
                        <b style="color:#10b981;">{{ $rp($tenantBillingSummary['grand_total_air']) }}</b>
                    </div>
                    <div style="display:flex; justify-content:space-between; padding-top:6px; border-top:1px dashed var(--border); font-size:0.78rem;">
                        <span style="color:var(--text-muted);">Selisih (Cooling Tower &amp; Public Area):</span>
                        <b style="color:{{ $tenantBillingSummary['net_selisih_air'] >= 0 ? '#38bdf8' : '#10b981' }};">
                            {{ $rp($tenantBillingSummary['net_selisih_air']) }} ({{ $tenantBillingSummary['pdam_recovery_pct'] }}% Pulih)
                        </b>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ── 4. Grafik Tren Pemakaian ── --}}
    <div class="form-grid" style="margin-bottom:24px;">
        <section class="panel">
            <div class="panel-head">
                <div>
                    <h2>Tren Pemakaian Listrik Harian (kWh)</h2>
                    <p>AMR PLN T33N &middot; Konsumsi Aktual (Faktor Meter 5.000x) &middot; Bulan {{ $selectedMonthName }}</p>
                </div>
            </div>
            <div class="chart-box"><canvas id="gmChartListrik"></canvas></div>
        </section>

        <section class="panel">
            <div class="panel-head">
                <div>
                    <h2>Tren Pemakaian Air Harian (m³)</h2>
                    <p>Water Meter Gedung &middot; Tarif Rp 21.500/m³ &middot; Bulan {{ $selectedMonthName }}</p>
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
                        <td class="l">
                            <b>⚡ AMR PLN T33N (LWBP &amp; WBP)</b>
                            <div style="font-size:0.72rem; color:var(--text-muted); margin-top:2px;">
                                Faktor Meter: <b>5.000x</b> &middot; Termasuk Pajak Penerangan 2,4%
                            </div>
                        </td>
                        <td class="l">Main Incoming Gardu PLN Gedung</td>
                        <td class="r" style="color:#fbbf24; font-weight:700;">
                            <div>{{ $n($summaryGedung['kwh']) }} kWh</div>
                            <div style="font-size:0.74rem; color:var(--text-primary); font-weight:600; margin-top:2px;">{{ $rp($summaryGedung['total_rupiah']) }}</div>
                        </td>
                        <td class="r">
                            <div>{{ $n($summaryGedung['rata_kwh']) }} kWh</div>
                            <div style="font-size:0.72rem; color:var(--text-muted); margin-top:2px;">{{ $rp($summaryGedung['rata_rupiah']) }}</div>
                        </td>
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
                        <td class="l">
                            <b>💧 Water Meter Gedung Utama</b>
                            <div style="font-size:0.72rem; color:var(--text-muted); margin-top:2px;">
                                Suplai Air Bersih Utama Gedung &middot; Tarif: <b>Rp 21.500 / m³</b>
                            </div>
                        </td>
                        <td class="l">Main Suplai Meteran Gedung</td>
                        <td class="r" style="color:#38bdf8; font-weight:700;">
                            <div>{{ $n($summaryGedung['air']) }} m³</div>
                            <div style="font-size:0.74rem; color:var(--text-primary); font-weight:600; margin-top:2px;">{{ $rp($summaryGedung['biaya_air']) }}</div>
                        </td>
                        <td class="r">
                            <div>{{ $n($summaryGedung['rata_air']) }} m³</div>
                            <div style="font-size:0.72rem; color:var(--text-muted); margin-top:2px;">{{ $rp($summaryGedung['rata_biaya_air']) }}</div>
                        </td>
                        <td class="c">{{ $summaryGedung['jumlah_hari'] }} hari</td>
                        <td class="c">
                            <span class="badge badge-ok">Aktif</span>
                        </td>
                    </tr>

                    {{-- 3. Cooling Tower 12A --}}
                    <tr>
                        <td class="l">
                            <b>🏢 Water Meter Cooling Tower</b>
                            <div style="font-size:0.72rem; color:var(--text-muted); margin-top:2px;">Tarif: Rp 21.500 / m³</div>
                        </td>
                        <td class="l">Operasional Chiller Lantai 12A</td>
                        <td class="r" style="color:#06b6d4; font-weight:700;">
                            <div>{{ $n($waterPaByMeter['cooling_tower']['total_pakai'] ?? 0) }} m³</div>
                            <div style="font-size:0.74rem; color:var(--text-primary); font-weight:600; margin-top:2px;">{{ $rp($waterPaByMeter['cooling_tower']['biaya'] ?? 0) }}</div>
                        </td>
                        <td class="r">{{ ($waterPaByMeter['cooling_tower']['count'] ?? 0) > 0 ? $n(($waterPaByMeter['cooling_tower']['total_pakai'] ?? 0) / $waterPaByMeter['cooling_tower']['count']) : '—' }} m³</td>
                        <td class="c">{{ $waterPaByMeter['cooling_tower']['count'] ?? 0 }} data</td>
                        <td class="c"><span class="badge badge-ok">Public Area</span></td>
                    </tr>

                    {{-- 4. Toilet Sky Lobby & Mushola --}}
                    <tr>
                        <td class="l">
                            <b>🏢 Water Meter Toilet &amp; Mushola</b>
                            <div style="font-size:0.72rem; color:var(--text-muted); margin-top:2px;">Tarif: Rp 21.500 / m³</div>
                        </td>
                        <td class="l">Sky Lobby Lt. 11 &amp; Mushola Lt. 12</td>
                        <td class="r" style="color:#3b82f6; font-weight:700;">
                            <div>{{ $n($waterPaByMeter['toilet_sky_lobby']['total_pakai'] ?? 0) }} m³</div>
                            <div style="font-size:0.74rem; color:var(--text-primary); font-weight:600; margin-top:2px;">{{ $rp($waterPaByMeter['toilet_sky_lobby']['biaya'] ?? 0) }}</div>
                        </td>
                        <td class="r">{{ ($waterPaByMeter['toilet_sky_lobby']['count'] ?? 0) > 0 ? $n(($waterPaByMeter['toilet_sky_lobby']['total_pakai'] ?? 0) / $waterPaByMeter['toilet_sky_lobby']['count']) : '—' }} m³</td>
                        <td class="c">{{ $waterPaByMeter['toilet_sky_lobby']['count'] ?? 0 }} data</td>
                        <td class="c"><span class="badge badge-ok">Public Area</span></td>
                    </tr>

                    {{-- 5. Pantry Office --}}
                    <tr>
                        <td class="l">
                            <b>🏢 Water Meter Pantry Office</b>
                            <div style="font-size:0.72rem; color:var(--text-muted); margin-top:2px;">Tarif: Rp 21.500 / m³</div>
                        </td>
                        <td class="l">Area Pantry Office</td>
                        <td class="r" style="color:#10b981; font-weight:700;">
                            <div>{{ $n($waterPaByMeter['pantry_office']['total_pakai'] ?? 0) }} m³</div>
                            <div style="font-size:0.74rem; color:var(--text-primary); font-weight:600; margin-top:2px;">{{ $rp($waterPaByMeter['pantry_office']['biaya'] ?? 0) }}</div>
                        </td>
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
            },
            tooltip: {
                callbacks: {
                    label: function (ctx) {
                        const val = ctx.parsed.y !== null ? new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(ctx.parsed.y) : '0';
                        return ` ${ctx.dataset.label}: ${val}`;
                    }
                }
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
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: { color: th.textColor, font: { family: 'Inter', size: 11 } }
                    },
                    tooltip: {
                        callbacks: {
                            label: function (ctx) {
                                const m3 = ctx.parsed.y !== null ? ctx.parsed.y : 0;
                                const rp = Math.round(m3 * 21500);
                                const fmtM3 = new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(m3);
                                const fmtRp = new Intl.NumberFormat('id-ID').format(rp);
                                return ` Pemakaian: ${fmtM3} m³ (Rp ${fmtRp})`;
                            }
                        }
                    }
                },
                scales: commonOptions.scales
            }
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
