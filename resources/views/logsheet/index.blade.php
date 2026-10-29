@extends('layouts.panel')

@php
    $isAdmin = in_array(auth()->user()->role, ['admin', 'superadmin'], true);
    $canManage = in_array(auth()->user()->role, ['admin', 'superadmin', 'eng_admin', 'eng_spv'], true);
    $periode = sprintf('%04d-%02d', $year, $month);
    $n = fn ($v) => $v === null ? '—' : number_format($v, 2, ',', '.');
    $pakaiBp = $summary['bp'] ?? $summary['wbp'] ?? 0;
    $pakaiLbp = (($summary['lbp1'] ?? 0) + ($summary['lbp2'] ?? 0)) > 0 ? (($summary['lbp1'] ?? 0) + ($summary['lbp2'] ?? 0)) : ($summary['lwbp'] ?? 0);
@endphp

@section('title', 'Riwayat & Paparan Rekap Logsheet AMR PLN & Air')
@section('page-title', 'Rekapitulasi Logsheet AMR PLN & Air')
@section('page-subtitle', 'Periode ' . \Carbon\Carbon::create($year, $month)->translatedFormat('F Y'))

@section('panel')

{{-- Status hari ini --}}
@php
    $hasElecToday  = $today && ($today->foto_kwh || $today->stand_total !== null || $today->stand_bp !== null);
    $hasWaterToday = $today && ($today->foto_air || $today->stand_air !== null);
@endphp

@if ($hasElecToday && $hasWaterToday)
    <div class="status-banner ok">
        <span><span class="dot"></span>Logsheet hari ini (AMR PLN T33N ⚡ &amp; Air 💧) sudah lengkap dicatat oleh <b>{{ $today->user?->name ?? 'Teknisi' }}</b>.</span>
        <a href="{{ route('logsheet.show', $today) }}" class="btn btn-sm">Lihat Detail</a>
    </div>
@elseif ($hasElecToday && !$hasWaterToday)
    <div class="status-banner warn">
        <span><span class="dot"></span>AMR PLN T33N hari ini sudah dicatat. <b>Water Meter Air 💧 belum dicatat.</b></span>
        <a href="{{ route('logsheet.create.air') }}" class="btn btn-sm btn-primary">+ Input Stand Air Sekarang</a>
    </div>
@elseif (!$hasElecToday && $hasWaterToday)
    <div class="status-banner warn">
        <span><span class="dot"></span>Air hari ini sudah dicatat. <b>Logsheet AMR PLN T33N ⚡ belum dicatat.</b></span>
        <a href="{{ route('logsheet.create.listrik') }}" class="btn btn-sm btn-primary">+ Input AMR PLN T33N</a>
    </div>
@else
    <div class="status-banner warn">
        <span><span class="dot"></span>Logsheet hari ini (<b>{{ now()->translatedFormat('d F Y') }}</b>) belum dicatat.</span>
        <div style="display:flex; gap:8px; margin-left:auto; flex-wrap:wrap;">
            <a href="{{ route('logsheet.create.listrik') }}" class="btn btn-sm btn-primary">+ Input AMR PLN</a>
            <a href="{{ route('logsheet.create.air') }}" class="btn btn-sm" style="background:rgba(6,182,212,0.18); border-color:rgba(6,182,212,0.4); color:#38bdf8; font-weight:600;">+ Input Air</a>
        </div>
    </div>
@endif

{{-- ═══════════ TOOLBAR PEMILIHAN MODEL TAMPILAN (GRAFIK vs TABEL) ═══════════ --}}
<div class="view-switch-container">
    <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
        <span style="font-weight:700; font-size:0.86rem; color:var(--text-primary); display:flex; align-items:center; gap:8px;">
            <svg style="width:18px;height:18px;color:var(--accent);" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="3" y1="9" x2="21" y2="9"></line>
                <line x1="9" y1="21" x2="9" y2="9"></line>
            </svg>
            Model Tampilan Rekap:
        </span>
        <div class="view-switch-group" role="tablist" aria-label="Model Rekapitulasi">
            <button type="button" class="view-toggle-btn active" id="btn-mode-graphic" onclick="switchLogsheetView('graphic')" title="Tampilan Visual Grafik untuk Paparan Manajemen">
                <svg viewBox="0 0 20 20" fill="currentColor"><path d="M15.5 2A1.5 1.5 0 0 0 14 3.5v13a1.5 1.5 0 0 0 1.5 1.5h1a1.5 1.5 0 0 0 1.5-1.5v-13A1.5 1.5 0 0 0 16.5 2h-1ZM9.5 6A1.5 1.5 0 0 0 8 7.5v9A1.5 1.5 0 0 0 9.5 18h1a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 10.5 6h-1ZM3.5 10A1.5 1.5 0 0 0 2 11.5v5A1.5 1.5 0 0 0 3.5 18h1A1.5 1.5 0 0 0 6 16.5v-5A1.5 1.5 0 0 0 4.5 10h-1Z"/></svg>
                <span>1. Model Grafik</span>
            </button>
            <button type="button" class="view-toggle-btn" id="btn-mode-table" onclick="switchLogsheetView('table')" title="Tampilan Data Tabel Rinci Stand & Pemakaian">
                <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M2 4.75A.75.75 0 0 1 2.75 4h14.5a.75.75 0 0 1 0 1.5H2.75A.75.75 0 0 1 2 4.75ZM2 10a.75.75 0 0 1 .75-.75h14.5a.75.75 0 0 1 0 1.5H2.75A.75.75 0 0 1 2 10Zm0 5.25a.75.75 0 0 1 .75-.75h14.5a.75.75 0 0 1 0 1.5H2.75a.75.75 0 0 1-.75-.75Z" clip-rule="evenodd"/></svg>
                <span>2. Model Tabel</span>
            </button>
            <button type="button" class="view-toggle-btn" id="btn-mode-both" onclick="switchLogsheetView('both')" title="Tampilkan Kedua Model Sekaligus">
                <svg viewBox="0 0 20 20" fill="currentColor"><path d="M2 4.25A2.25 2.25 0 0 1 4.25 2h11.5A2.25 2.25 0 0 1 18 4.25v11.5A2.25 2.25 0 0 1 15.75 18H4.25A2.25 2.25 0 0 1 2 15.75V4.25Zm4 1.5a.75.75 0 0 0-.75.75v7c0 .414.336.75.75.75h8a.75.75 0 0 0 .75-.75v-7a.75.75 0 0 0-.75-.75H6Z"/></svg>
                <span>Tampilkan Keduanya</span>
            </button>
        </div>
    </div>

    <div style="display:flex; align-items:center; gap:8px;">
        <form class="filter-form" method="GET" style="margin:0;">
            <input type="month" name="periode" value="{{ $periode }}" max="{{ now()->format('Y-m') }}" onchange="this.form.submit()" aria-label="Periode" style="padding:6px 12px; font-size:0.82rem;">
        </form>
        @if ($canManage)
            <a href="{{ route('logsheet.excel', ['periode' => $periode]) }}" class="btn btn-excel btn-sm" id="btn-export-excel" title="Download Format Excel">
                <svg viewBox="0 0 20 20" fill="currentColor" style="width:14px;height:14px;"><path d="M10.75 2.75a.75.75 0 0 0-1.5 0v8.614L6.295 8.235a.75.75 0 1 0-1.09 1.03l4.25 4.5a.75.75 0 0 0 1.09 0l4.25-4.5a.75.75 0 0 0-1.09-1.03l-2.955 3.129V2.75Z"/><path d="M3.5 12.75a.75.75 0 0 0-1.5 0v2.5A2.75 2.75 0 0 0 4.75 18h10.5A2.75 2.75 0 0 0 18 15.25v-2.5a.75.75 0 0 0-1.5 0v2.5c0 .69-.56 1.25-1.25 1.25H4.75c-.69 0-1.25-.56-1.25-1.25v-2.5Z"/></svg>Excel
            </a>
            <a href="{{ route('logsheet.pdf', ['periode' => $periode]) }}" target="_blank" class="btn btn-pdf btn-sm" id="btn-export-pdf" title="Cetak Format Laporan PDF">
                <svg viewBox="0 0 20 20" fill="currentColor" style="width:14px;height:14px;"><path fill-rule="evenodd" d="M5 2.75C5 1.784 5.784 1 6.75 1h6.5c.966 0 1.75.784 1.75 1.75v3.552c.377.046.752.097 1.126.153A2.212 2.212 0 0 1 18 8.653v4.097A2.25 2.25 0 0 1 15.75 15h-.241l.305 1.984A1.75 1.75 0 0 1 14.084 19H5.915a1.75 1.75 0 0 1-1.73-2.016L4.492 15H4.25A2.25 2.25 0 0 1 2 12.75V8.653c0-1.082.775-2.034 1.874-2.198.374-.056.75-.107 1.127-.153L5 6.25v-3.5Zm8.5 3.397a41.533 41.533 0 0 0-7 0V2.75a.25.25 0 0 1 .25-.25h6.5a.25.25 0 0 1 .25.25v3.397ZM6.608 12.5a.25.25 0 0 0-.247.212l-.693 4.5a.25.25 0 0 0 .247.288h8.17a.25.25 0 0 0 .246-.288l-.692-4.5a.25.25 0 0 0-.247-.212H6.608Z" clip-rule="evenodd"/></svg>PDF
            </a>
        @endif
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════ --}}
{{-- 1. MODEL GRAFIK: BAHAN PAPARAN BULANAN MANAJEMEN                       --}}
{{-- ══════════════════════════════════════════════════════════════════════ --}}
<div id="section-model-graphic" class="logsheet-view-section">
    {{-- Banner Eksekutif --}}
    <div class="mgmt-presentation-banner">
        <div>
            <div style="display:flex; align-items:center; gap:8px; margin-bottom:6px;">
                <span class="badge badge-ok" style="font-size:0.7rem; letter-spacing:0.06em; text-transform:uppercase;">Paparan Manajemen Engineering</span>
                <span style="font-size:0.8rem; color:var(--accent-light); font-weight:600;">Periode {{ \Carbon\Carbon::create($year, $month)->translatedFormat('F Y') }}</span>
            </div>
            <h2 style="font-size:1.4rem; color:var(--text-primary); margin:0 0 6px 0; font-family:var(--font-serif, 'Cormorant Garamond', Georgia, serif); font-weight:700;">
                Analisis Visual Beban Listrik (AMR PLN T33N) &amp; Air Bersih
            </h2>
            <p style="font-size:0.84rem; color:var(--text-secondary); margin:0; max-width:820px; line-height:1.5;">
                Data visual mengacu langsung dari rekapitulasi stand dan pemakaian harian. Menampilkan evaluasi beban puncak (BP), luar beban puncak (LBP 1+2), serta kurva pertumbuhan akumulatif untuk akurasi monitoring biaya operasional.
            </p>
        </div>
        <div style="display:flex; gap:8px; flex-wrap:wrap; align-items:center;">
            <button type="button" onclick="window.print()" class="btn btn-sm" style="background:rgba(200,170,110,0.15); border:1px solid rgba(200,170,110,0.35); color:var(--accent-light);">
                🖨️ Cetak Paparan
            </button>
            <a href="#section-model-table" onclick="switchLogsheetView('table')" class="btn btn-sm btn-primary">
                📋 Buka Model Tabel
            </a>
        </div>
    </div>

    {{-- Executive KPI Grid --}}
    <div class="mgmt-kpi-grid">
        {{-- KPI 1: Total Listrik --}}
        <div class="mgmt-kpi-card mgmt-kpi-card--elec">
            <div class="mgmt-kpi-title">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" style="width:16px; height:16px;">
                    <polygon points="32,3 61,32 32,61 3,32" fill="#FFC90E" stroke="#D32F2F" stroke-width="2.5"/>
                    <polygon points="36,11 23,33 31,33 26,53 43,28 34,28" fill="#D32F2F"/>
                </svg>
                Total Listrik (AMR PLN T33N)
            </div>
            <div class="mgmt-kpi-val">{{ $n($kpi['total_kwh']) }}<small>kWh</small></div>
            <div class="mgmt-kpi-sub">
                Rata-rata: <b>{{ $n($kpi['avg_kwh']) }} kWh/hari</b><br>
                Puncak: <b>{{ $kpi['peak_kwh'] ? $n($kpi['peak_kwh']['nilai']) . ' kWh' : '—' }}</b>
                @if($kpi['peak_kwh']) <span style="color:var(--text-muted); font-size:0.72rem;">({{ $kpi['peak_kwh']['tanggal'] }})</span> @endif
            </div>
        </div>

        {{-- KPI 2: Beban Puncak (BP) --}}
        <div class="mgmt-kpi-card mgmt-kpi-card--bp">
            <div class="mgmt-kpi-title">
                <span style="display:inline-block; width:10px; height:10px; border-radius:50%; background:#f43f5e;"></span>
                Pemakaian Beban Puncak (BP)
            </div>
            <div class="mgmt-kpi-val" style="color:#f43f5e;">{{ $n($kpi['total_bp']) }}<small>kWh</small></div>
            <div class="mgmt-kpi-sub">
                Porsi Beban: <b>{{ $kpi['pct_bp'] }}%</b> dari total kWh<br>
                Jam Puncak: 17:00 – 22:00 WIB (Tarif Tinggi)
            </div>
        </div>

        {{-- KPI 3: Luar Beban Puncak (LBP 1+2) --}}
        <div class="mgmt-kpi-card mgmt-kpi-card--lbp">
            <div class="mgmt-kpi-title">
                <span style="display:inline-block; width:10px; height:10px; border-radius:50%; background:#fbbf24;"></span>
                Pemakaian Luar BP (LBP 1+2)
            </div>
            <div class="mgmt-kpi-val" style="color:#fbbf24;">{{ $n($kpi['total_lbp']) }}<small>kWh</small></div>
            <div class="mgmt-kpi-sub">
                Porsi Beban: <b>{{ $kpi['pct_lbp'] }}%</b> dari total kWh<br>
                Jam Standar: 22:00 – 17:00 WIB (Tarif Normal)
            </div>
        </div>

        {{-- KPI 4: Pemakaian Air --}}
        <div class="mgmt-kpi-card mgmt-kpi-card--water">
            <div class="mgmt-kpi-title">
                <svg viewBox="0 0 20 20" fill="#38bdf8" style="width:16px; height:16px;"><path fill-rule="evenodd" d="M10 2.5c.3 0 .58.14.77.37C12.4 4.87 16 9.45 16 12.5a6 6 0 0 1-12 0c0-3.05 3.6-7.63 5.23-9.63A1 1 0 0 1 10 2.5Z" clip-rule="evenodd"/></svg>
                Total Konsumsi Air PDAM
            </div>
            <div class="mgmt-kpi-val" style="color:#38bdf8;">{{ $n($kpi['total_air']) }}<small>m³</small></div>
            <div class="mgmt-kpi-sub">
                Rata-rata: <b>{{ $n($kpi['avg_air']) }} m³/hari</b><br>
                Puncak: <b>{{ $kpi['peak_air'] ? $n($kpi['peak_air']['nilai']) . ' m³' : '—' }}</b>
                @if($kpi['peak_air']) <span style="color:var(--text-muted); font-size:0.72rem;">({{ $kpi['peak_air']['tanggal'] }})</span> @endif
            </div>
        </div>
    </div>

    {{-- Presentation Charts Row 1: Listrik Dual-Axis & Proporsi Beban --}}
    <div class="chart-presentation-grid">
        {{-- Chart 1: Listrik AMR PLN T33N Harian & Akumulatif --}}
        <div class="chart-mgmt-card">
            <div class="chart-mgmt-card-header">
                <div>
                    <div class="chart-mgmt-card-title">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" style="width:18px; height:18px;">
                            <polygon points="32,3 61,32 32,61 3,32" fill="#FFC90E" stroke="#D32F2F" stroke-width="2.5"/>
                            <polygon points="36,11 23,33 31,33 26,53 43,28 34,28" fill="#D32F2F"/>
                        </svg>
                        Tren Harian AMR PLN T33N &amp; Kurva Akumulatif
                    </div>
                    <div class="chart-mgmt-card-sub">Batang: Pemakaian BP &amp; LBP (Sumbu Kiri) | Garis: Akumulatif kWh Bulanan (Sumbu Kanan)</div>
                </div>
                <div style="display:flex; gap:6px; flex-wrap:wrap;">
                    <span class="chart-legend-badge"><span class="chart-legend-dot" style="background:#f43f5e;"></span> BP (kWh)</span>
                    <span class="chart-legend-badge"><span class="chart-legend-dot" style="background:#fbbf24;"></span> LBP 1+2 (kWh)</span>
                    <span class="chart-legend-badge"><span class="chart-legend-dot" style="background:#38bdf8;"></span> Akumulatif (kWh)</span>
                </div>
            </div>
            <div class="chart-box-large">
                <canvas id="chartElecMgmt"></canvas>
            </div>
        </div>

        {{-- Chart 2: Proporsi Beban Listrik (Doughnut) --}}
        <div class="chart-mgmt-card">
            <div class="chart-mgmt-card-header">
                <div>
                    <div class="chart-mgmt-card-title">
                        <svg viewBox="0 0 20 20" fill="currentColor" style="width:18px; height:18px; color:var(--accent);"><path d="M2 10a8 8 0 0 1 8-8v8h8a8 8 0 1 1-16 0Z"/><path d="M12 2.25A8.004 8.004 0 0 1 17.75 8H12V2.25Z"/></svg>
                        Proporsi Beban BP vs LBP
                    </div>
                    <div class="chart-mgmt-card-sub">Distribusi Tarif Jam Puncak vs Luar Beban Puncak</div>
                </div>
            </div>
            <div class="chart-box-large" style="height:240px;">
                <canvas id="chartProporsiMgmt"></canvas>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-top:12px; padding-top:12px; border-top:1px solid rgba(200,170,110,0.12); text-align:center;">
                <div style="background:rgba(244,63,94,0.08); padding:8px; border-radius:6px; border:1px solid rgba(244,63,94,0.2);">
                    <div style="font-size:0.72rem; color:#f43f5e; font-weight:700;">BEBAN PUNCAK</div>
                    <div style="font-size:1.1rem; font-weight:700; color:var(--text-primary); margin-top:2px;">{{ $kpi['pct_bp'] }}%</div>
                    <div style="font-size:0.75rem; color:var(--text-muted);">{{ $n($kpi['total_bp']) }} kWh</div>
                </div>
                <div style="background:rgba(251,191,36,0.08); padding:8px; border-radius:6px; border:1px solid rgba(251,191,36,0.2);">
                    <div style="font-size:0.72rem; color:#fbbf24; font-weight:700;">LUAR BEBAN PUNCAK</div>
                    <div style="font-size:1.1rem; font-weight:700; color:var(--text-primary); margin-top:2px;">{{ $kpi['pct_lbp'] }}%</div>
                    <div style="font-size:0.75rem; color:var(--text-muted);">{{ $n($kpi['total_lbp']) }} kWh</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Presentation Charts Row 2: Air Dual-Axis & Management Note --}}
    <div class="chart-presentation-grid">
        {{-- Chart 3: Air Bersih Harian & Akumulatif --}}
        <div class="chart-mgmt-card">
            <div class="chart-mgmt-card-header">
                <div>
                    <div class="chart-mgmt-card-title">
                        <svg viewBox="0 0 20 20" fill="#38bdf8" style="width:18px; height:18px;"><path fill-rule="evenodd" d="M10 2.5c.3 0 .58.14.77.37C12.4 4.87 16 9.45 16 12.5a6 6 0 0 1-12 0c0-3.05 3.6-7.63 5.23-9.63A1 1 0 0 1 10 2.5Z" clip-rule="evenodd"/></svg>
                        Tren Harian Pemakaian Air (PDAM) &amp; Kurva Akumulatif
                    </div>
                    <div class="chart-mgmt-card-sub">Batang: Pemakaian Harian (Sumbu Kiri) | Garis: Akumulatif Total m³ (Sumbu Kanan)</div>
                </div>
                <div style="display:flex; gap:6px; flex-wrap:wrap;">
                    <span class="chart-legend-badge"><span class="chart-legend-dot" style="background:#06b6d4;"></span> Harian (m³)</span>
                    <span class="chart-legend-badge"><span class="chart-legend-dot" style="background:#38bdf8;"></span> Akumulatif (m³)</span>
                </div>
            </div>
            <div class="chart-box-large">
                <canvas id="chartAirMgmt"></canvas>
            </div>
        </div>

        {{-- Insights & Management Summary Box --}}
        <div class="chart-mgmt-card">
            <div class="chart-mgmt-card-header">
                <div class="chart-mgmt-card-title">
                    <svg viewBox="0 0 20 20" fill="currentColor" style="width:18px; height:18px; color:var(--accent);"><path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-7-4a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM9 9a.75.75 0 0 0 0 1.5h.253a.25.25 0 0 1 .244.304l-.459 2.066A1.75 1.75 0 0 0 10.747 15H11a.75.75 0 0 0 0-1.5h-.253a.25.25 0 0 1-.244-.304l.459-2.066A1.75 1.75 0 0 0 9.253 9H9Z" clip-rule="evenodd"/></svg>
                    Catatan Pengawasan Manajemen
                </div>
            </div>
            <div style="font-size:0.83rem; color:var(--text-secondary); line-height:1.6; display:flex; flex-direction:column; gap:12px;">
                <div style="background:rgba(255,255,255,0.03); border:1px solid rgba(200,170,110,0.15); border-radius:6px; padding:10px 12px;">
                    <strong style="color:var(--text-primary); display:block; margin-bottom:2px;">Kepatuhan Pencatatan:</strong>
                    Tercatat <b>{{ $summary['jumlah'] }} hari</b> pada periode {{ \Carbon\Carbon::create($year, $month)->translatedFormat('F Y') }}.
                </div>

                <div style="background:rgba(255,255,255,0.03); border:1px solid rgba(200,170,110,0.15); border-radius:6px; padding:10px 12px;">
                    <strong style="color:var(--text-primary); display:block; margin-bottom:2px;">Analisis Efisiensi Beban:</strong>
                    Rasio pemakaian Luar Beban Puncak berada di angka <b>{{ $kpi['pct_lbp'] }}%</b>. Pertahankan pengoperasian beban besar pada jam tarif dasar untuk menekan pengeluaran tagihan PLN.
                </div>

                <div class="mgmt-insight-box" style="margin-top:auto;">
                    <strong>Rekomendasi Operasional:</strong>
                    Pastikan teknisi mencatat stand meteran setiap hari pada jam yang konsisten (pagi hari) serta melampirkan foto meteran yang jernih agar deviasi harian tetap terkendali.
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════ --}}
{{-- 2. MODEL TABEL: REKAPITULASI DATA RINCI STAND & PEMAKAIAN             --}}
{{-- ══════════════════════════════════════════════════════════════════════ --}}
<section class="panel logsheet-view-section" id="section-model-table">
    <div class="panel-head">
        <div>
            <h2>Rekap Data Logsheet Harian</h2>
            <p>Pemakaian harian dihitung dari selisih stand hari ini dengan stand hari sebelumnya, beserta nilai akumulatif bulanan.</p>
        </div>
        <div class="btn-group">
            @if ($canManage)
                <a href="{{ route('logsheet.excel', ['periode' => $periode]) }}" class="btn btn-excel">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path d="M10.75 2.75a.75.75 0 0 0-1.5 0v8.614L6.295 8.235a.75.75 0 1 0-1.09 1.03l4.25 4.5a.75.75 0 0 0 1.09 0l4.25-4.5a.75.75 0 0 0-1.09-1.03l-2.955 3.129V2.75Z"/><path d="M3.5 12.75a.75.75 0 0 0-1.5 0v2.5A2.75 2.75 0 0 0 4.75 18h10.5A2.75 2.75 0 0 0 18 15.25v-2.5a.75.75 0 0 0-1.5 0v2.5c0 .69-.56 1.25-1.25 1.25H4.75c-.69 0-1.25-.56-1.25-1.25v-2.5Z"/></svg>Excel
                </a>
                <a href="{{ route('logsheet.pdf', ['periode' => $periode]) }}" target="_blank" class="btn btn-pdf">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5 2.75C5 1.784 5.784 1 6.75 1h6.5c.966 0 1.75.784 1.75 1.75v3.552c.377.046.752.097 1.126.153A2.212 2.212 0 0 1 18 8.653v4.097A2.25 2.25 0 0 1 15.75 15h-.241l.305 1.984A1.75 1.75 0 0 1 14.084 19H5.915a1.75 1.75 0 0 1-1.73-2.016L4.492 15H4.25A2.25 2.25 0 0 1 2 12.75V8.653c0-1.082.775-2.034 1.874-2.198.374-.056.75-.107 1.127-.153L5 6.25v-3.5Zm8.5 3.397a41.533 41.533 0 0 0-7 0V2.75a.25.25 0 0 1 .25-.25h6.5a.25.25 0 0 1 .25.25v3.397ZM6.608 12.5a.25.25 0 0 0-.247.212l-.693 4.5a.25.25 0 0 0 .247.288h8.17a.25.25 0 0 0 .246-.288l-.692-4.5a.25.25 0 0 0-.247-.212H6.608Z" clip-rule="evenodd"/></svg>PDF
                </a>
            @endif
            @if (auth()->user()->isSuperAdmin())
                <a href="{{ route('logsheet.manual.listrik') }}" class="btn btn-sm" style="background:rgba(245,158,11,0.15); color:#fbbf24; border:1px solid rgba(245,158,11,0.35);" title="Input manual logsheet AMR PLN untuk tanggal terlewat">
                    ⚡ Manual AMR PLN
                </a>
                <a href="{{ route('logsheet.manual.air') }}" class="btn btn-sm" style="background:rgba(56,189,248,0.15); color:#38bdf8; border:1px solid rgba(56,189,248,0.35);" title="Input manual logsheet air untuk tanggal terlewat">
                    💧 Manual Air
                </a>
            @endif
            <a href="{{ route('logsheet.create') }}" class="btn btn-primary" id="btn-input-logsheet">
                <svg viewBox="0 0 20 20" fill="currentColor"><path d="M10.75 4.75a.75.75 0 0 0-1.5 0v4.5h-4.5a.75.75 0 0 0 0 1.5h4.5v4.5a.75.75 0 0 0 1.5 0v-4.5h4.5a.75.75 0 0 0 0-1.5h-4.5v-4.5Z"/></svg>Input Logsheet
            </a>
        </div>
    </div>

    {{-- ── Panel Aksi Masal: Verifikasi Masal & Hapus Masal (Khusus Super Admin, Maks 1 Bulan) ── --}}
    @if (auth()->user()->isSuperAdmin() && $rows->count())
        <div class="bulk-action-bar" style="margin-bottom:14px; background:linear-gradient(135deg, rgba(200,170,110,0.12), rgba(15,22,35,0.7)); border:1px solid rgba(200,170,110,0.3); border-radius:10px; padding:12px 16px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
            <div style="display:flex; align-items:center; gap:14px; flex-wrap:wrap;">
                <label style="display:inline-flex; align-items:center; gap:8px; cursor:pointer; font-weight:700; font-size:0.88rem; color:var(--accent-light); margin:0; user-select:none;">
                    <input type="checkbox" id="bulk-master-check" style="width:18px; height:18px; cursor:pointer; accent-color:#c8aa6e;">
                    <span>Pilih Semua Bulan Ini ({{ $rows->count() }} Data)</span>
                </label>
                <div style="display:flex; align-items:center; gap:8px;">
                    <button type="button" class="btn btn-sm" id="btn-select-unverified" style="font-size:0.75rem; padding:3px 8px; background:rgba(234,179,8,0.15); color:#facc15; border:1px solid rgba(234,179,8,0.3);" title="Pilih hanya data yang belum diverifikasi">
                        ⚡ Pilih Belum Verifikasi
                    </button>
                    <button type="button" class="btn btn-sm" id="btn-clear-selection" style="font-size:0.75rem; padding:3px 8px; background:rgba(255,255,255,0.06); color:var(--text-muted); display:none;" title="Batalkan semua pilihan">
                        ✕ Batal Pilih
                    </button>
                </div>
                <span id="bulk-selected-badge" class="badge" style="background:rgba(200,170,110,0.2); color:var(--accent-light); border:1px solid rgba(200,170,110,0.4); font-size:0.78rem; padding:4px 10px; border-radius:6px; font-weight:700; display:none;">
                    <span id="bulk-selected-count">0</span> data dipilih
                </span>
            </div>

            <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                {{-- Tombol Verifikasi Masal --}}
                <button type="button" id="btn-trigger-bulk-verify" class="btn btn-sm btn-success" disabled style="display:inline-flex; align-items:center; gap:6px; opacity:0.5; cursor:not-allowed; transition:all 0.2s ease;">
                    <svg viewBox="0 0 20 20" fill="currentColor" style="width:16px; height:16px;"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd"/></svg>
                    <span id="btn-text-bulk-verify">Verifikasi Masal</span>
                </button>

                {{-- Tombol Hapus Masal --}}
                <button type="button" id="btn-trigger-bulk-delete" class="btn btn-sm btn-danger" disabled style="display:inline-flex; align-items:center; gap:6px; opacity:0.5; cursor:not-allowed; transition:all 0.2s ease;">
                    <svg viewBox="0 0 20 20" fill="currentColor" style="width:16px; height:16px;"><path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 0 0 6 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 1 0 .23 1.482l.149-.022.841 10.518A2.75 2.75 0 0 0 7.596 19h4.807a2.75 2.75 0 0 0 2.742-2.53l.841-10.52.149.023a.75.75 0 0 0 .23-1.482A41.03 41.03 0 0 0 14 4.193V3.75A2.75 2.75 0 0 0 11.25 1h-2.5ZM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4ZM8.58 7.72a.75.75 0 0 1 .75.748l.5 6a.75.75 0 0 1-1.498.124l-.5-6a.75.75 0 0 1 .748-.872Zm3.588.748a.75.75 0 0 0-1.498-.124l-.5 6a.75.75 0 0 0 1.498.124l.5-6Z" clip-rule="evenodd"/></svg>
                    <span id="btn-text-bulk-delete">Hapus Masal</span>
                </button>
            </div>
        </div>

        {{-- Hidden Form untuk Submit Aksi Masal --}}
        <form id="bulk-action-form" method="POST" action="" style="display:none;">
            @csrf
            <input type="hidden" name="periode" value="{{ $periode }}">
            <div id="bulk-hidden-inputs"></div>
        </form>
    @endif

    {{-- Tabel Rekapitulasi (Stand Total ditiadakan sesuai instruksi) --}}
    <div class="table-wrap">
        <table class="log-table">
            <thead>
                <tr>
                    @if (auth()->user()->isSuperAdmin())
                        <th rowspan="2" class="c" style="width:42px; vertical-align:middle;">
                            <input type="checkbox" id="bulk-check-table-all" title="Pilih Semua" style="width:17px; height:17px; cursor:pointer; accent-color:#c8aa6e;">
                        </th>
                    @endif
                    <th rowspan="2" class="l">Tanggal</th>
                    <th colspan="7" class="g-elec">AMR PLN T33N (kWh)</th>
                    <th rowspan="2" class="c" style="background:rgba(245,158,11,0.12); color:#fbbf24; border-left:1px solid rgba(200,170,110,0.2); border-right:1px solid rgba(200,170,110,0.2);">
                        Total Listrik<br><small>(BP + LBP)</small>
                    </th>
                    <th colspan="3" class="g-water">Air Bersih (m³)</th>
                    <th rowspan="2" class="c">Foto</th>
                    <th rowspan="2" class="l">Teknisi</th>
                    <th rowspan="2" class="c">Status</th>
                    <th rowspan="2" class="c">Aksi</th>
                </tr>
                <tr>
                    {{-- Sub-kolom AMR PLN T33N (Stand BP, Pakai BP, Akum BP, Stand LBP1, Stand LBP2, Pakai LBP 1+2, Akum LBP) --}}
                    <th>Stand BP</th>
                    <th>Pakai BP</th>
                    <th style="color:var(--accent-light);">Total BP (Akum.)</th>
                    <th>Stand LBP 1</th>
                    <th>Stand LBP 2</th>
                    <th>Pakai LBP 1+2</th>
                    <th style="color:var(--accent-light);">Total LBP (Akum.)</th>

                    {{-- Sub-kolom Air (Stand Air, Pakai Air, Akum Air) --}}
                    <th>Stand Air</th>
                    <th>Pakai Air</th>
                    <th style="color:#38bdf8;">Total Air (Akum.)</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($rows as $r)
                <tr id="row-logsheet-{{ $r->id }}">
                    @if (auth()->user()->isSuperAdmin())
                        <td class="c" style="vertical-align:middle;">
                            <input type="checkbox" name="bulk_ids[]" value="{{ $r->id }}" class="bulk-item-check" data-verified="{{ $r->isVerified() ? '1' : '0' }}" data-tanggal="{{ $r->tanggal->format('d/m/Y') }}" style="width:17px; height:17px; cursor:pointer; accent-color:#c8aa6e;">
                        </td>
                    @endif
                    <td class="l">{{ $r->tanggal->translatedFormat('D, d M') }}</td>

                    {{-- Stand & Pemakaian BP --}}
                    <td>{{ $r->stand_bp !== null ? $n($r->stand_bp) : ($r->stand_wbp !== null ? $n($r->stand_wbp) : '—') }}</td>
                    <td class="use use-elec">{{ $n($r->pakai_bp) }}</td>
                    <td class="col-highlight-cum">{{ $n($r->cum_bp) }}</td>

                    {{-- Stand & Pemakaian LBP 1 & 2 --}}
                    <td>{{ $r->stand_lbp1 !== null ? $n($r->stand_lbp1) : ($r->stand_lwbp !== null ? $n($r->stand_lwbp) : '—') }}</td>
                    <td>{{ $r->stand_lbp2 !== null ? $n($r->stand_lbp2) : '—' }}</td>
                    <td class="use use-elec">{{ $n($r->pakai_lbp) }}</td>
                    <td class="col-highlight-cum">{{ $n($r->cum_lbp) }}</td>

                    {{-- Total Pemakaian Listrik kWh --}}
                    <td class="use use-elec" style="background:rgba(245,158,11,0.06); font-weight:700;">
                        <b>{{ $n($r->pakai_kwh) }}</b>
                    </td>

                    {{-- Stand & Pemakaian Air --}}
                    <td>{{ $n($r->stand_air) }}</td>
                    <td class="use use-water">{{ $n($r->pakai_air) }}</td>
                    <td class="col-highlight-cum" style="color:#38bdf8 !important;">{{ $n($r->cum_air) }}</td>

                    {{-- Foto Lampiran Meteran --}}
                    <td class="c">
                        <span class="thumbs">
                            @if ($r->foto_kwh)
                                <img src="{{ asset('storage/'.$r->foto_kwh) }}" alt="kWh" data-lightbox="{{ asset('storage/'.$r->foto_kwh) }}" data-caption="AMR PLN T33N — {{ $r->tanggal->format('d/m/Y') }}">
                            @endif
                            @if ($r->foto_air)
                                <img src="{{ asset('storage/'.$r->foto_air) }}" alt="Air" data-lightbox="{{ asset('storage/'.$r->foto_air) }}" data-caption="Water meter — {{ $r->tanggal->format('d/m/Y') }}">
                            @endif
                            @if (! $r->foto_kwh && ! $r->foto_air)
                                <span style="color:var(--text-muted);font-size:0.75rem;">—</span>
                            @endif
                        </span>
                    </td>

                    {{-- Teknisi & Status --}}
                    <td class="l">{{ $r->user?->name ?? 'User Terhapus' }}</td>
                    <td class="c">
                        @if ($r->isVerified())
                            <span class="badge badge-ok" title="{{ $r->verified_at ? 'Oleh ' . ($r->verifier?->name ?? 'Admin') : 'Otomatis Terverifikasi (Input Admin / Backdate)' }}">✓ Terverifikasi</span>
                        @else
                            <span class="badge badge-wait">Menunggu</span>
                        @endif
                    </td>

                    {{-- Aksi --}}
                    <td class="c">
                        <div class="btn-group" style="justify-content:center">
                            <a href="{{ route('logsheet.show', $r) }}" class="btn btn-sm">Detail</a>
                            @if ($canManage || (auth()->user()->role !== 'teknisi' && $r->user_id === auth()->id() && $r->tanggal->isToday()))
                                <a href="{{ route('logsheet.edit', $r) }}" class="btn btn-sm">Edit</a>
                            @endif
                            @if ($canManage && $r->needsVerification())
                                <form method="POST" action="{{ route('logsheet.verify', $r) }}" class="inline">@csrf
                                    <button class="btn btn-sm btn-success">Verifikasi</button>
                                </form>
                            @endif
                            @if ($isAdmin || in_array(auth()->user()->role, ['eng_admin', 'eng_spv'], true))
                                <form method="POST" action="{{ route('logsheet.destroy', $r) }}" class="inline" onsubmit="return confirm('Hapus permanen logsheet {{ $r->tanggal->translatedFormat('d F Y') }}?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-danger">Hapus</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="{{ auth()->user()->isSuperAdmin() ? 17 : 16 }}" class="empty">Belum ada data logsheet pada periode ini.</td></tr>
            @endforelse
            </tbody>
            @if ($rows->count())
            <tfoot>
                <tr>
                    @if (auth()->user()->isSuperAdmin())
                        <td></td>
                    @endif
                    <td class="l">TOTAL</td>
                    <td>—</td>
                    <td class="use-elec"><b>{{ $n($summary['bp']) }}</b></td>
                    <td class="col-highlight-cum"><b>{{ $n($summary['bp']) }}</b></td>
                    <td>—</td>
                    <td>—</td>
                    <td class="use-elec"><b>{{ $n($summary['lbp']) }}</b></td>
                    <td class="col-highlight-cum"><b>{{ $n($summary['lbp']) }}</b></td>
                    <td class="use-elec" style="background:rgba(245,158,11,0.12);">
                        <b>{{ $n($summary['kwh']) }} kWh</b>
                    </td>
                    <td>—</td>
                    <td class="use-water"><b>{{ $n($summary['air']) }} m³</b></td>
                    <td class="col-highlight-cum" style="color:#38bdf8 !important;"><b>{{ $n($summary['air']) }} m³</b></td>
                    <td colspan="4" style="text-align:center; color:var(--text-muted); font-size:0.75rem;">
                        {{ $summary['jumlah'] }} hari tercatat
                    </td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>

    {{-- ── Tampilan Khusus Layar HP / Mobile Card List ── --}}
    <div class="mobile-logsheet-list">
        @forelse ($rows as $r)
            <div class="mobile-log-card" id="card-logsheet-{{ $r->id }}" style="transition:border-color 0.2s ease, background 0.2s ease;">
                <div class="mlc-header">
                    <div style="display:flex; align-items:center; gap:10px;">
                        @if (auth()->user()->isSuperAdmin())
                            <input type="checkbox" name="bulk_ids[]" value="{{ $r->id }}" class="bulk-item-check mlc-item-check" data-verified="{{ $r->isVerified() ? '1' : '0' }}" data-tanggal="{{ $r->tanggal->format('d/m/Y') }}" style="width:20px; height:20px; cursor:pointer; accent-color:#c8aa6e; margin-top:2px;">
                        @endif
                        <div>
                            <span class="mlc-date">{{ $r->tanggal->translatedFormat('D, d M Y') }}</span>
                            <div class="mlc-tech">Oleh <b>{{ $r->user?->name ?? 'User Terhapus' }}</b></div>
                        </div>
                    </div>
                    <div>
                        @if ($r->isVerified())
                            <span class="badge badge-ok">✓ Terverifikasi</span>
                        @else
                            <span class="badge badge-wait">Menunggu</span>
                        @endif
                    </div>
                </div>

                <div class="mlc-grid">
                    <div class="mlc-stat-box mlc-stat-elec">
                        <div class="mlc-stat-label" style="display:flex; align-items:center; gap:6px;">
                            <span style="display:inline-flex; width:16px; height:16px;">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" style="width:16px; height:16px;">
                                    <polygon points="32,3 61,32 32,61 3,32" fill="#FFC90E" stroke="#D32F2F" stroke-width="2.5"/>
                                    <polygon points="36,11 23,33 31,33 26,53 43,28 34,28" fill="#D32F2F"/>
                                </svg>
                            </span>
                            Total Listrik (AMR PLN)
                        </div>
                        <div class="mlc-stat-val">{{ $n($r->pakai_kwh) }} <small>kWh</small></div>
                        <div class="mlc-stat-sub">
                            <div>BP: {{ $n($r->pakai_bp) }} kWh (Akum: {{ $n($r->cum_bp) }})</div>
                            <div>LBP: {{ $n($r->pakai_lbp) }} kWh (Akum: {{ $n($r->cum_lbp) }})</div>
                        </div>
                    </div>

                    <div class="mlc-stat-box mlc-stat-water">
                        <div class="mlc-stat-label">
                            <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 2.5c.3 0 .58.14.77.37C12.4 4.87 16 9.45 16 12.5a6 6 0 0 1-12 0c0-3.05 3.6-7.63 5.23-9.63A1 1 0 0 1 10 2.5Z" clip-rule="evenodd"/></svg>
                            Air Bersih
                        </div>
                        <div class="mlc-stat-val">{{ $n($r->pakai_air) }} <small>m³</small></div>
                        <div class="mlc-stat-sub">
                            <div>Stand: {{ $n($r->stand_air) }}</div>
                            <div>Akumulatif: {{ $n($r->cum_air) }} m³</div>
                        </div>
                    </div>
                </div>

                <div class="mlc-footer">
                    <div class="thumbs">
                        @if ($r->foto_kwh)
                            <img src="{{ asset('storage/'.$r->foto_kwh) }}" alt="kWh" data-lightbox="{{ asset('storage/'.$r->foto_kwh) }}" data-caption="AMR PLN T33N — {{ $r->tanggal->format('d/m/Y') }}">
                        @endif
                        @if ($r->foto_air)
                            <img src="{{ asset('storage/'.$r->foto_air) }}" alt="Air" data-lightbox="{{ asset('storage/'.$r->foto_air) }}" data-caption="Water meter — {{ $r->tanggal->format('d/m/Y') }}">
                        @endif
                        @if (! $r->foto_kwh && ! $r->foto_air)
                            <span style="color:var(--text-muted);font-size:0.78rem;">Tanpa lampiran</span>
                        @endif
                    </div>
                    <div class="btn-group">
                        <a href="{{ route('logsheet.show', $r) }}" class="btn btn-sm">Lihat Detail</a>
                        @if ($canManage || (auth()->user()->role !== 'teknisi' && $r->user_id === auth()->id() && $r->tanggal->isToday()))
                            <a href="{{ route('logsheet.edit', $r) }}" class="btn btn-sm">Edit</a>
                        @endif
                        @if ($canManage && $r->needsVerification())
                            <form method="POST" action="{{ route('logsheet.verify', $r) }}" class="inline">@csrf
                                <button class="btn btn-sm btn-success">Verifikasi</button>
                            </form>
                        @endif
                        @if ($isAdmin || in_array(auth()->user()->role, ['eng_admin', 'eng_spv'], true))
                            <form method="POST" action="{{ route('logsheet.destroy', $r) }}" class="inline" onsubmit="return confirm('Hapus permanen logsheet {{ $r->tanggal->translatedFormat('d F Y') }}?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger">Hapus</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="empty" style="text-align:center;padding:30px 10px;color:var(--text-muted)">
                Belum ada data logsheet pada periode ini.
            </div>
        @endforelse
    </div>
</section>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    // ── Fungsi Pengalih Model Tampilan (Model Grafik vs Model Tabel) ──
    function switchLogsheetView(mode) {
        const secGraphic = document.getElementById('section-model-graphic');
        const secTable = document.getElementById('section-model-table');
        const btnGraphic = document.getElementById('btn-mode-graphic');
        const btnTable = document.getElementById('btn-mode-table');
        const btnBoth = document.getElementById('btn-mode-both');

        if (!secGraphic || !secTable) return;

        btnGraphic?.classList.remove('active');
        btnTable?.classList.remove('active');
        btnBoth?.classList.remove('active');

        if (mode === 'graphic') {
            secGraphic.style.display = 'block';
            secTable.style.display = 'none';
            btnGraphic?.classList.add('active');
        } else if (mode === 'table') {
            secGraphic.style.display = 'none';
            secTable.style.display = 'block';
            btnTable?.classList.add('active');
        } else {
            secGraphic.style.display = 'block';
            secTable.style.display = 'block';
            btnBoth?.classList.add('active');
        }

        try {
            localStorage.setItem('kpr_logsheet_view_mode', mode);
        } catch(e) {}

        // Trigger resize untuk Chart.js agar re-render proporsional
        window.dispatchEvent(new Event('resize'));
    }

    // Restore saved view mode preference
    document.addEventListener('DOMContentLoaded', () => {
        let savedMode = 'graphic';
        try {
            savedMode = localStorage.getItem('kpr_logsheet_view_mode') || 'graphic';
        } catch(e) {}
        switchLogsheetView(savedMode);
    });

    // ── Inisialisasi Grafik Paparan Manajemen (Chart.js) ──
    const chartData = @json($chartData ?? []);
    const kpiData = @json($kpi ?? []);

    Chart.defaults.color = 'rgba(200, 170, 110, 0.7)';
    Chart.defaults.font.family = "'Inter', -apple-system, sans-serif";
    const chartGrid = { color: 'rgba(200, 170, 110, 0.08)', drawBorder: false };

    // Format angka standar Indonesia
    const fmtNum = (val) => new Intl.NumberFormat('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 }).format(val);

    // 1. Grafik Listrik AMR PLN T33N (BP vs LBP Stacked Bars + Akumulatif Line)
    const ctxElec = document.getElementById('chartElecMgmt');
    if (ctxElec && chartData.labels && chartData.labels.length > 0) {
        new Chart(ctxElec, {
            data: {
                labels: chartData.dates || chartData.labels,
                datasets: [
                    {
                        type: 'bar',
                        label: 'Beban Puncak (BP) kWh',
                        data: chartData.bp,
                        backgroundColor: '#f43f5e',
                        borderRadius: 3,
                        stack: 'elecStack',
                        yAxisID: 'y',
                        order: 2,
                    },
                    {
                        type: 'bar',
                        label: 'Luar BP (LBP 1+2) kWh',
                        data: chartData.lbp,
                        backgroundColor: '#fbbf24',
                        borderRadius: 3,
                        stack: 'elecStack',
                        yAxisID: 'y',
                        order: 2,
                    },
                    {
                        type: 'line',
                        label: 'Total Akumulatif kWh',
                        data: chartData.cum_kwh,
                        borderColor: '#38bdf8',
                        backgroundColor: 'rgba(56, 189, 248, 0.1)',
                        fill: false,
                        tension: 0.3,
                        pointRadius: 3,
                        pointHoverRadius: 6,
                        borderWidth: 2.5,
                        yAxisID: 'y1',
                        order: 1,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: {
                        display: false // Menggunakan legend kustom pada header card
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return ` ${context.dataset.label}: ${fmtNum(context.raw)}`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: chartGrid,
                        ticks: { color: 'rgba(200, 170, 110, 0.7)' }
                    },
                    y: {
                        position: 'left',
                        stacked: true,
                        beginAtZero: true,
                        grid: chartGrid,
                        title: { display: true, text: 'Harian (kWh)', color: 'rgba(200, 170, 110, 0.6)' },
                        ticks: { callback: (v) => fmtNum(v) }
                    },
                    y1: {
                        position: 'right',
                        beginAtZero: true,
                        grid: { drawOnChartArea: false },
                        title: { display: true, text: 'Akumulatif (kWh)', color: '#38bdf8' },
                        ticks: { callback: (v) => fmtNum(v), color: '#38bdf8' }
                    }
                }
            }
        });
    }

    // 2. Donut Proporsi Beban (BP vs LBP)
    const ctxProporsi = document.getElementById('chartProporsiMgmt');
    if (ctxProporsi) {
        const totalBp = kpiData.total_bp || 0;
        const totalLbp = kpiData.total_lbp || 0;
        new Chart(ctxProporsi, {
            type: 'doughnut',
            data: {
                labels: ['Beban Puncak (BP)', 'Luar Beban Puncak (LBP 1+2)'],
                datasets: [{
                    data: [totalBp, totalLbp],
                    backgroundColor: ['#f43f5e', '#fbbf24'],
                    borderWidth: 0,
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: 'rgba(200, 170, 110, 0.85)',
                            padding: 12,
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = totalBp + totalLbp;
                                const pct = total > 0 ? ((context.raw / total) * 100).toFixed(1) : 0;
                                return ` ${context.label}: ${fmtNum(context.raw)} kWh (${pct}%)`;
                            }
                        }
                    }
                },
                cutout: '68%'
            }
        });
    }

    // 3. Grafik Air PDAM (Harian Bar + Akumulatif Line)
    const ctxAir = document.getElementById('chartAirMgmt');
    if (ctxAir && chartData.labels && chartData.labels.length > 0) {
        new Chart(ctxAir, {
            data: {
                labels: chartData.dates || chartData.labels,
                datasets: [
                    {
                        type: 'bar',
                        label: 'Pemakaian Harian Air (m³)',
                        data: chartData.air,
                        backgroundColor: '#06b6d4',
                        borderRadius: 3,
                        yAxisID: 'y',
                        order: 2,
                    },
                    {
                        type: 'line',
                        label: 'Total Akumulatif Air (m³)',
                        data: chartData.cum_air,
                        borderColor: '#38bdf8',
                        backgroundColor: 'rgba(56, 189, 248, 0.12)',
                        fill: false,
                        tension: 0.3,
                        pointRadius: 3,
                        pointHoverRadius: 6,
                        borderWidth: 2.5,
                        yAxisID: 'y1',
                        order: 1,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return ` ${context.dataset.label}: ${fmtNum(context.raw)} m³`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: chartGrid,
                        ticks: { color: 'rgba(200, 170, 110, 0.7)' }
                    },
                    y: {
                        position: 'left',
                        beginAtZero: true,
                        grid: chartGrid,
                        title: { display: true, text: 'Harian (m³)', color: '#06b6d4' },
                        ticks: { callback: (v) => fmtNum(v) }
                    },
                    y1: {
                        position: 'right',
                        beginAtZero: true,
                        grid: { drawOnChartArea: false },
                        title: { display: true, text: 'Akumulatif (m³)', color: '#38bdf8' },
                        ticks: { callback: (v) => fmtNum(v), color: '#38bdf8' }
                    }
                }
            }
        });
    }

    // ── Fitur Aksi Masal: Verifikasi Masal & Hapus Masal (Khusus Super Admin) ──
    @if (auth()->user()->isSuperAdmin())
    (function() {
        const bulkMasterCheck = document.getElementById('bulk-master-check');
        const bulkCheckTableAll = document.getElementById('bulk-check-table-all');
        const btnSelectUnverified = document.getElementById('btn-select-unverified');
        const btnClearSelection = document.getElementById('btn-clear-selection');
        const badgeSelected = document.getElementById('bulk-selected-badge');
        const countSelected = document.getElementById('bulk-selected-count');
        const btnBulkVerify = document.getElementById('btn-trigger-bulk-verify');
        const btnBulkDelete = document.getElementById('btn-trigger-bulk-delete');
        const textBulkVerify = document.getElementById('btn-text-bulk-verify');
        const textBulkDelete = document.getElementById('btn-text-bulk-delete');
        const bulkForm = document.getElementById('bulk-action-form');
        const bulkHiddenInputs = document.getElementById('bulk-hidden-inputs');

        function getUniqueSelectedIds() {
            const checkedBoxes = document.querySelectorAll('.bulk-item-check:checked');
            const ids = new Set();
            checkedBoxes.forEach(cb => ids.add(cb.value));
            return Array.from(ids);
        }

        function updateBulkToolbar() {
            const selectedIds = getUniqueSelectedIds();
            const totalSelected = selectedIds.length;
            const allItems = Array.from(new Set(Array.from(document.querySelectorAll('.bulk-item-check')).map(cb => cb.value)));
            const totalItems = allItems.length;

            if (totalSelected > 0) {
                if (badgeSelected) badgeSelected.style.display = 'inline-block';
                if (countSelected) countSelected.textContent = totalSelected;
                if (btnClearSelection) btnClearSelection.style.display = 'inline-flex';

                if (btnBulkVerify) {
                    btnBulkVerify.disabled = false;
                    btnBulkVerify.style.opacity = '1';
                    btnBulkVerify.style.cursor = 'pointer';
                    if (textBulkVerify) textBulkVerify.textContent = `Verifikasi Masal (${totalSelected})`;
                }

                if (btnBulkDelete) {
                    btnBulkDelete.disabled = false;
                    btnBulkDelete.style.opacity = '1';
                    btnBulkDelete.style.cursor = 'pointer';
                    if (textBulkDelete) textBulkDelete.textContent = `Hapus Masal (${totalSelected})`;
                }
            } else {
                if (badgeSelected) badgeSelected.style.display = 'none';
                if (btnClearSelection) btnClearSelection.style.display = 'none';

                if (btnBulkVerify) {
                    btnBulkVerify.disabled = true;
                    btnBulkVerify.style.opacity = '0.5';
                    btnBulkVerify.style.cursor = 'not-allowed';
                    if (textBulkVerify) textBulkVerify.textContent = 'Verifikasi Masal';
                }

                if (btnBulkDelete) {
                    btnBulkDelete.disabled = true;
                    btnBulkDelete.style.opacity = '0.5';
                    btnBulkDelete.style.cursor = 'not-allowed';
                    if (textBulkDelete) textBulkDelete.textContent = 'Hapus Masal';
                }
            }

            const allChecked = totalItems > 0 && totalSelected === totalItems;
            const someChecked = totalSelected > 0 && totalSelected < totalItems;

            if (bulkMasterCheck) {
                bulkMasterCheck.checked = allChecked;
                bulkMasterCheck.indeterminate = someChecked;
            }
            if (bulkCheckTableAll) {
                bulkCheckTableAll.checked = allChecked;
                bulkCheckTableAll.indeterminate = someChecked;
            }

            // Visual feedback on mobile cards
            document.querySelectorAll('.mobile-log-card').forEach(card => {
                const cardId = card.id.replace('card-logsheet-', '');
                if (selectedIds.includes(cardId)) {
                    card.style.borderColor = 'rgba(200, 170, 110, 0.6)';
                    card.style.background = 'rgba(200, 170, 110, 0.05)';
                } else {
                    card.style.borderColor = '';
                    card.style.background = '';
                }
            });
        }

        function toggleAll(checked) {
            document.querySelectorAll('.bulk-item-check').forEach(cb => {
                cb.checked = checked;
            });
            updateBulkToolbar();
        }

        if (bulkMasterCheck) {
            bulkMasterCheck.addEventListener('change', function() {
                toggleAll(this.checked);
            });
        }
        if (bulkCheckTableAll) {
            bulkCheckTableAll.addEventListener('change', function() {
                toggleAll(this.checked);
            });
        }

        document.querySelectorAll('.bulk-item-check').forEach(cb => {
            cb.addEventListener('change', function() {
                const val = this.value;
                const checked = this.checked;
                document.querySelectorAll(`.bulk-item-check[value="${val}"]`).forEach(sibling => {
                    sibling.checked = checked;
                });
                updateBulkToolbar();
            });
        });

        if (btnSelectUnverified) {
            btnSelectUnverified.addEventListener('click', function() {
                document.querySelectorAll('.bulk-item-check').forEach(cb => {
                    cb.checked = (cb.getAttribute('data-verified') === '0');
                });
                updateBulkToolbar();
            });
        }

        if (btnClearSelection) {
            btnClearSelection.addEventListener('click', function() {
                toggleAll(false);
            });
        }

        if (btnBulkVerify) {
            btnBulkVerify.addEventListener('click', function() {
                const ids = getUniqueSelectedIds();
                if (ids.length === 0) {
                    alert('Silakan pilih minimal 1 data logsheet terlebih dahulu.');
                    return;
                }

                const monthName = '{{ \Carbon\Carbon::create($year, $month)->translatedFormat('F Y') }}';
                if (!confirm(`Konfirmasi Verifikasi Masal:\n\nApakah Anda yakin ingin memverifikasi ${ids.length} data logsheet periode ${monthName} secara sekaligus?`)) {
                    return;
                }

                if (bulkHiddenInputs && bulkForm) {
                    bulkHiddenInputs.innerHTML = '';
                    ids.forEach(id => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'ids[]';
                        input.value = id;
                        bulkHiddenInputs.appendChild(input);
                    });
                    bulkForm.action = '{{ route('logsheet.bulk.verify') }}';
                    bulkForm.submit();
                }
            });
        }

        if (btnBulkDelete) {
            btnBulkDelete.addEventListener('click', function() {
                const ids = getUniqueSelectedIds();
                if (ids.length === 0) {
                    alert('Silakan pilih minimal 1 data logsheet yang ingin dihapus.');
                    return;
                }

                const monthName = '{{ \Carbon\Carbon::create($year, $month)->translatedFormat('F Y') }}';
                const confirmMsg = `⚠️ PERINGATAN HAPUS MASAL (SUPER ADMIN):\n\nApakah Anda benar-benar yakin ingin MENGHAPUS PERMANEN ${ids.length} data logsheet periode ${monthName}?\n\n• Semua catatan stand AMR PLN & Water meter akan dihapus.\n• Semua foto meteran terkait akan dihapus dari server.\n• Tindakan ini TIDAK DAPAT DIBATALKAN.`;
                
                if (!confirm(confirmMsg)) {
                    return;
                }

                if (bulkHiddenInputs && bulkForm) {
                    bulkHiddenInputs.innerHTML = '';
                    ids.forEach(id => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'ids[]';
                        input.value = id;
                        bulkHiddenInputs.appendChild(input);
                    });
                    bulkForm.action = '{{ route('logsheet.bulk.destroy') }}';
                    bulkForm.submit();
                }
            });
        }
    })();
    @endif
</script>
@endpush
