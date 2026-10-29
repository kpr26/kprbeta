@extends('layouts.panel')

@php
    $n = fn ($v, $d = 2) => ($v !== null && $v !== '') ? number_format((float)$v, $d, ',', '.') : '—';
    $canManage = auth()->user()->isSuperAdmin() || auth()->user()->isSupervisor() || auth()->user()->isAdmin();
    $isSuperAdmin = auth()->user()->isSuperAdmin();
    $todayStr = \Carbon\Carbon::today()->translatedFormat('l, d F Y');
    $intervalDays = (int) round($startDate->diffInDays($endDate) + 1);
@endphp

@section('title', 'Logsheet MVSB')
@section('page-title', 'MVSB')
@section('page-subtitle', 'Pencatatan Parameter Tegangan Menengah (Voltage 20kV) & Arus (Current) Panel Incoming PLN')

@section('panel')
<section class="panel" style="max-width:1200px; margin:0 auto;">
    {{-- Header Sub Menu --}}
    <div class="panel-head" style="align-items:flex-start; flex-wrap:wrap; gap:16px;">
        <div>
            <div style="display:flex; align-items:center; gap:8px;">
                <span style="font-size:1.4rem;">⚡</span>
                <h2 style="margin:0; font-size:1.35rem;">Sub Menu: MVSB</h2>
            </div>
            <p style="color:var(--text-secondary); margin:4px 0 0; font-size:0.88rem;">
                Pencatatan harian tegangan menengah 20kV dan arus panel incoming PLN beserta lampiran foto fisik.
            </p>
        </div>

        <div style="display:flex; gap:8px; flex-wrap:wrap; align-items:center;">
            <a href="{{ route('public-area.index') }}" class="btn btn-sm">
                ← Menu Public Area
            </a>
            <a href="{{ route('public-area.water.index') }}" class="btn btn-sm" style="background:rgba(56,189,248,0.12); color:#38bdf8; border:1px solid rgba(56,189,248,0.3);">
                💧 Water Meter
            </a>
            <a href="{{ route('public-area.lvsb.index') }}" class="btn btn-sm" style="background:rgba(251,191,36,0.12); color:#fbbf24; border:1px solid rgba(251,191,36,0.3);">
                ⚡ LVSB
            </a>

            {{-- Tombol Khusus Super Admin: Input Back Date (Data Terlewat) --}}
            @if ($isSuperAdmin)
                <a href="{{ route('public-area.manual.mvsb') }}" class="btn btn-sm" style="background:rgba(234,179,8,0.18); color:#facc15; border:1px solid rgba(234,179,8,0.45); font-weight:700; display:inline-flex; align-items:center; gap:6px;" title="Input data back date secara manual (Khusus Super Admin)">
                    <span>📅 Input Back Date (Terlewat)</span>
                </a>
            @endif

            {{-- Export Buttons --}}
            @if ($canManage)
                <a href="{{ route('public-area.mvsb.excel', ['start_date' => $startDate->toDateString(), 'end_date' => $endDate->toDateString(), 'preset' => $preset]) }}" class="btn btn-excel btn-sm" title="Export Excel Rekapitulasi Data MVSB">
                    <svg viewBox="0 0 20 20" fill="currentColor" style="width:14px;height:14px;"><path d="M10.75 2.75a.75.75 0 0 0-1.5 0v8.614L6.295 8.235a.75.75 0 1 0-1.09 1.03l4.25 4.5a.75.75 0 0 0 1.09 0l4.25-4.5a.75.75 0 0 0-1.09-1.03l-2.955 3.129V2.75Z"/><path d="M3.5 12.75a.75.75 0 0 0-1.5 0v2.5A2.75 2.75 0 0 0 4.75 18h10.5A2.75 2.75 0 0 0 18 15.25v-2.5a.75.75 0 0 0-1.5 0v2.5c0 .69-.56 1.25-1.25 1.25H4.75c-.69 0-1.25-.56-1.25-1.25v-2.5Z"/></svg>Export to Excel
                </a>
                <a href="{{ route('public-area.mvsb.pdf', ['start_date' => $startDate->toDateString(), 'end_date' => $endDate->toDateString(), 'preset' => $preset]) }}" target="_blank" class="btn btn-pdf btn-sm" title="Export PDF Rekapitulasi Data (Grafik & Tabel)">
                    <svg viewBox="0 0 20 20" fill="currentColor" style="width:14px;height:14px;"><path fill-rule="evenodd" d="M5 2.75C5 1.784 5.784 1 6.75 1h6.5c.966 0 1.75.784 1.75 1.75v3.552c.377.046.752.097 1.126.153A2.212 2.212 0 0 1 18 8.653v4.097A2.25 2.25 0 0 1 15.75 15h-.241l.305 1.984A1.75 1.75 0 0 1 14.084 19H5.915a1.75 1.75 0 0 1-1.73-2.016L4.492 15H4.25A2.25 2.25 0 0 1 2 12.75V8.653c0-1.082.775-2.034 1.874-2.198.374-.056.75-.107 1.127-.153L5 6.25v-3.5Zm8.5 3.397a41.533 41.533 0 0 0-7 0V2.75a.25.25 0 0 1 .25-.25h6.5a.25.25 0 0 1 .25.25v3.397ZM6.608 12.5a.25.25 0 0 0-.247.212l-.693 4.5a.25.25 0 0 0 .247.288h8.17a.25.25 0 0 0 .246-.288l-.692-4.5a.25.25 0 0 0-.247-.212H6.608Z" clip-rule="evenodd"/></svg>Export to PDF
                </a>
            @endif
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- TOOLBAR FILTER INTERVAL DATA MVSB (MAKSIMAL 12 BULAN / 1 TAHUN)        --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    <div style="background:var(--bg-card); border:1px solid var(--border); border-radius:12px; padding:14px 18px; margin-bottom:24px; box-shadow:var(--shadow-sm);">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
            {{-- Quick Presets --}}
            <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                <span style="font-size:0.75rem; text-transform:uppercase; font-weight:700; color:var(--text-muted); letter-spacing:0.04em;">Preset Interval:</span>
                <a href="{{ route('public-area.mvsb.index') }}" class="btn btn-sm {{ empty($preset) && !request()->filled('start_date') ? 'btn-primary' : '' }}" style="{{ empty($preset) && !request()->filled('start_date') ? 'background:#7c3aed; color:#fff;' : 'background:var(--bg-card-subtle); border:1px solid var(--border); color:var(--text-secondary);' }} font-size:0.78rem; padding:4px 10px;">
                    Bulan Ini
                </a>
                <a href="{{ route('public-area.mvsb.index', ['preset' => '3m']) }}" class="btn btn-sm" style="{{ $preset === '3m' ? 'background:#7c3aed; color:#fff;' : 'background:var(--bg-card-subtle); border:1px solid var(--border); color:var(--text-secondary);' }} font-size:0.78rem; padding:4px 10px;">
                    3 Bulan
                </a>
                <a href="{{ route('public-area.mvsb.index', ['preset' => '6m']) }}" class="btn btn-sm" style="{{ $preset === '6m' ? 'background:#7c3aed; color:#fff;' : 'background:var(--bg-card-subtle); border:1px solid var(--border); color:var(--text-secondary);' }} font-size:0.78rem; padding:4px 10px;">
                    6 Bulan
                </a>
                <a href="{{ route('public-area.mvsb.index', ['preset' => '12m']) }}" class="btn btn-sm" style="{{ in_array($preset, ['12m', '1y'], true) ? 'background:#7c3aed; color:#fff;' : 'background:var(--bg-card-subtle); border:1px solid var(--border); color:var(--text-secondary);' }} font-size:0.78rem; padding:4px 10px;">
                    1 Tahun (Maks)
                </a>
            </div>

            {{-- Custom Date Range Form --}}
            <form method="GET" action="{{ route('public-area.mvsb.index') }}" style="display:flex; align-items:center; gap:8px; flex-wrap:wrap; margin:0;">
                <span style="font-size:0.75rem; color:var(--text-muted); font-weight:600;">Rentang:</span>
                <input type="date" name="start_date" value="{{ $startDate->format('Y-m-d') }}" max="{{ today()->toDateString() }}" class="input" style="padding:4px 8px; font-size:0.8rem; width:135px;" title="Tanggal Mulai">
                <span style="color:var(--text-muted); font-size:0.8rem;">s/d</span>
                <input type="date" name="end_date" value="{{ $endDate->format('Y-m-d') }}" max="{{ today()->toDateString() }}" class="input" style="padding:4px 8px; font-size:0.8rem; width:135px;" title="Tanggal Selesai">
                <button type="submit" class="btn btn-sm btn-primary" style="padding:5px 12px; font-size:0.8rem; background:#7c3aed; border-color:#7c3aed;">
                    Filter
                </button>
            </form>
        </div>

        {{-- Active Interval Info Pill --}}
        <div style="margin-top:10px; padding-top:8px; border-top:1px solid var(--border); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px; font-size:0.78rem;">
            <div style="color:var(--text-secondary); display:flex; align-items:center; gap:6px;">
                <span style="display:inline-block; width:6px; height:6px; border-radius:50%; background:var(--purple);"></span>
                <span>Interval Aktif: <strong style="color:var(--text-primary);">{{ $startDate->translatedFormat('d M Y') }} s/d {{ $endDate->translatedFormat('d M Y') }}</strong> ({{ $intervalDays }} hari)</span>
            </div>
            <span style="color:var(--text-muted); font-size:0.74rem;">* Ekspor Excel & PDF otomatis mengikuti interval aktif (maksimal 12 bulan / 1 tahun).</span>
        </div>
    </div>

    {{-- Pill Mode Offline --}}
    @if(auth()->user()->role === 'teknisi')
        <div style="display:inline-flex; align-items:center; gap:8px; background:rgba(200,170,110,0.06); border:1px solid rgba(200,170,110,0.22); border-radius:20px; padding:4px 12px; margin-bottom:16px; font-size:0.78rem; color:var(--text-secondary);">
            <span style="display:inline-block; width:7px; height:7px; border-radius:50%; background:#10b981; box-shadow:0 0 6px #10b981;"></span>
            <span><strong style="color:var(--accent-light);">Mode Offline Aktif:</strong> Anda dapat menginput parameter panel MVSB tanpa sinyal di gardu. Data otomatis tersinkron saat online.</span>
        </div>
    @endif

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- FORM INPUT PENCATATAN HARI INI                                          --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    <div style="background:var(--bg-card); border:1px solid var(--border); border-radius:12px; padding:20px; margin-bottom:28px; box-shadow:var(--shadow-sm);">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:16px; border-bottom:1px solid rgba(255,255,255,0.06); padding-bottom:12px;">
            <div>
                <span style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.06em; color:#c084fc; font-weight:700;">Panel Incoming PLN (MVSB 20kV)</span>
                <h3 style="margin:2px 0 0; font-size:1.15rem; color:var(--text-primary); font-weight:700;">
                    Pencatatan Hari Ini — {{ $todayStr }}
                </h3>
            </div>

            @if($todayReading)
                <div style="display:flex; align-items:center; gap:10px;">
                    <span class="badge badge-ok" style="font-size:0.8rem; padding:4px 10px;">
                        ✓ Sudah Dicatat Hari Ini
                    </span>
                    <a href="{{ route('public-area.mvsb.show', $todayReading) }}" class="btn btn-sm" style="background:rgba(255,255,255,0.06); font-size:0.78rem;">
                        Lihat Detail
                    </a>
                </div>
            @endif
        </div>

        <form method="POST" action="{{ route('public-area.mvsb.store') }}" enctype="multipart/form-data" class="form-offline-pa-mvsb" data-offline-type="public_area_mvsb" data-meter-label="MVSB Incoming From PLN" data-target-redirect="{{ route('public-area.mvsb.index') }}">
            @csrf
            <input type="hidden" name="tanggal" value="{{ \Carbon\Carbon::today()->toDateString() }}">

            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(260px, 1fr)); gap:16px;">
                {{-- Field 1: Voltage 20kV --}}
                <div class="field-item">
                    <label style="font-size:0.8rem; font-weight:700; color:var(--text-secondary); text-transform:uppercase; display:block; margin-bottom:6px;">
                        Voltage 20kV <span style="color:#ef4444;">*</span>
                    </label>
                    <div style="position:relative;">
                        <input type="number" step="0.01" name="voltage_20kv" class="input" value="{{ old('voltage_20kv', $todayReading?->voltage_20kv) }}" placeholder="Contoh: 20000" style="width:100%; padding-right:60px; font-size:1.05rem; font-weight:600;">
                        <span style="position:absolute; right:12px; top:50%; transform:translateY(-50%); font-size:0.8rem; color:var(--text-muted); pointer-events:none; font-weight:600;">
                            Volt
                        </span>
                    </div>
                    @if($prevReading && $prevReading->voltage_20kv !== null)
                        <div style="font-size:0.72rem; color:var(--text-muted); margin-top:4px;">
                            Sebelumnya ({{ $prevReading->tanggal->format('d/m') }}): <b>{{ $n($prevReading->voltage_20kv, 1) }} Volt</b>
                        </div>
                    @endif
                </div>

                {{-- Field 2: Current --}}
                <div class="field-item">
                    <label style="font-size:0.8rem; font-weight:700; color:var(--text-secondary); text-transform:uppercase; display:block; margin-bottom:6px;">
                        Current <span style="color:#ef4444;">*</span>
                    </label>
                    <div style="position:relative;">
                        <input type="number" step="0.01" name="current" class="input" value="{{ old('current', $todayReading?->current) }}" placeholder="Contoh: 25.5" style="width:100%; padding-right:75px; font-size:1.05rem; font-weight:600;">
                        <span style="position:absolute; right:12px; top:50%; transform:translateY(-50%); font-size:0.8rem; color:var(--text-muted); pointer-events:none; font-weight:600;">
                            Ampere
                        </span>
                    </div>
                    @if($prevReading && $prevReading->current !== null)
                        <div style="font-size:0.72rem; color:var(--text-muted); margin-top:4px;">
                            Sebelumnya ({{ $prevReading->tanggal->format('d/m') }}): <b>{{ $n($prevReading->current, 2) }} Ampere</b>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Lampiran Foto & Catatan --}}
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:16px; margin-top:18px; padding-top:16px; border-top:1px dashed rgba(255,255,255,0.08);">
                {{-- Lampiran Foto Fisik Panel MVSB --}}
                <div class="photo-upload-group">
                    <label style="font-size:0.78rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; display:block; margin-bottom:6px;">
                        📷 Foto Fisik Panel MVSB <span style="font-size:0.72rem; color:var(--text-muted); font-weight:normal;">(Bisa Galeri / Kamera Langsung)</span>
                    </label>

                    <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                        {{-- Tombol Galeri --}}
                        <label class="btn-pick-gallery" style="display:inline-flex; align-items:center; gap:6px; background:var(--bg-card-subtle); border:1px solid var(--border); padding:7px 12px; border-radius:6px; cursor:pointer; font-size:0.8rem; color:var(--text-secondary); transition:all 0.2s;" title="Pilih foto dari galeri HP / File">
                            <svg viewBox="0 0 20 20" fill="currentColor" style="width:15px;height:15px;color:var(--info);"><path fill-rule="evenodd" d="M1 5.25A2.25 2.25 0 0 1 3.25 3h13.5A2.25 2.25 0 0 1 19 5.25v9.5A2.25 2.25 0 0 1 16.75 17H3.25A2.25 2.25 0 0 1 1 14.75v-9.5Zm1.5 5.81v3.69c0 .414.336.75.75.75h13.5a.75.75 0 0 0 .75-.75v-2.69l-2.22-2.219a.75.75 0 0 0-1.06 0l-1.91 1.909-4.22-4.219a.75.75 0 0 0-1.06 0L2.5 11.06Zm10.25-3.81a1.25 1.25 0 1 1-2.5 0 1.25 1.25 0 0 1 2.5 0Z" clip-rule="evenodd"/></svg>
                            <span>🖼️ Galeri</span>
                            <input type="file" name="foto" accept="image/*" class="input-file-main" style="display:none;">
                        </label>

                        {{-- Tombol Kamera --}}
                        <label class="btn-pick-camera" style="display:inline-flex; align-items:center; gap:6px; background:var(--bg-card-subtle); border:1px solid var(--border); padding:7px 12px; border-radius:6px; cursor:pointer; font-size:0.8rem; color:var(--text-secondary); transition:all 0.2s;" title="Ambil foto langsung dengan kamera">
                            <svg viewBox="0 0 20 20" fill="currentColor" style="width:15px;height:15px;color:var(--purple);"><path fill-rule="evenodd" d="M1 8a2 2 0 0 1 2-2h.93a2 2 0 0 0 1.664-.89l.812-1.22A2 2 0 0 1 8.07 3h3.86a2 2 0 0 1 1.664.89l.812 1.22A2 2 0 0 0 16.07 6H17a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8Zm13.5 3a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM10 14a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" clip-rule="evenodd"/></svg>
                            <span>📷 Kamera</span>
                            <input type="file" accept="image/*" capture="environment" class="input-file-camera" style="display:none;">
                        </label>

                        {{-- Chip Pratinjau Foto --}}
                        <div class="photo-preview-wrap" style="{{ $todayReading?->foto ? 'display:inline-flex;' : 'display:none;' }} align-items:center; gap:8px; background:var(--bg-card-subtle); padding:5px 10px; border-radius:6px; border:1px solid var(--purple-border);">
                            <img class="img-preview" src="{{ $todayReading?->foto ? asset('storage/'.$todayReading->foto) : '' }}" alt="Thumb" style="width:36px; height:36px; object-fit:cover; border-radius:4px; border:1px solid var(--border);">
                            <span class="photo-preview-name" style="font-size:0.75rem; color:var(--text-primary); max-width:160px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                {{ $todayReading?->foto ? 'Foto tersimpan' : '' }}
                            </span>
                            <button type="button" class="btn-clear-photo" style="background:none; border:none; color:var(--danger); font-size:1rem; cursor:pointer; line-height:1; padding:0 3px;" title="Batalkan foto">✕</button>
                        </div>
                    </div>
                </div>

                {{-- Catatan --}}
                <div>
                    <label style="font-size:0.78rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; display:block; margin-bottom:6px;">
                        Catatan Kondisi Panel (Opsional)
                    </label>
                    <textarea name="catatan" rows="2" class="input" placeholder="Contoh: Indikator tegangan stabil, suhu ruang MVSB normal..." style="width:100%; font-size:0.84rem; padding:8px 12px;">{{ old('catatan', $todayReading?->catatan) }}</textarea>
                </div>
            </div>

            {{-- Tombol Simpan --}}
            <div style="margin-top:18px; display:flex; justify-content:flex-end;">
                <button type="submit" class="btn btn-primary" style="background:linear-gradient(135deg, #a855f7, #7c3aed); border:none; color:#fff; font-weight:700; padding:10px 24px; font-size:0.92rem; display:inline-flex; align-items:center; gap:8px; box-shadow:0 4px 12px rgba(168,85,247,0.3);">
                    <svg viewBox="0 0 20 20" fill="currentColor" style="width:18px;height:18px;"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd"/></svg>
                    <span>Simpan Logsheet MVSB</span>
                </button>
            </div>
        </form>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- RANGKUMAN STATISTIK PANEL MVSB (TABEL & GRAFIK)                        --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    <div style="background:var(--bg-card); border:1px solid var(--border); border-radius:12px; padding:20px; margin-bottom:28px; box-shadow:var(--shadow-sm);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; flex-wrap:wrap; gap:10px;">
            <div>
                <span style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.06em; color:#c084fc; font-weight:700;">Rangkuman & Visualisasi Parameter MVSB</span>
                <h3 style="font-size:1.15rem; color:var(--text-primary); margin:2px 0 0; font-weight:700;">
                    Rangkuman Panel MVSB — Interval {{ $startDate->translatedFormat('d M Y') }} s/d {{ $endDate->translatedFormat('d M Y') }}
                </h3>
            </div>
            <span class="badge" style="background:rgba(168,85,247,0.12); color:#c084fc; border:1px solid rgba(168,85,247,0.3); font-size:0.76rem;">
                {{ $intervalDays }} Hari Terpilih
            </span>
        </div>

        {{-- Metric KPI Cards --}}
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:12px; margin-bottom:20px;">
            <div class="metric-kpi-card metric-kpi-card--purple">
                <span style="font-size:0.72rem; color:var(--text-muted); text-transform:uppercase; font-weight:700;">Avg Voltage 20kV</span>
                <div style="font-size:1.5rem; font-weight:700; color:var(--purple); font-family:var(--font-serif); margin:4px 0;">
                    {{ $n($summary['avg_voltage'], 1) }} <small style="font-size:0.78rem; color:var(--text-muted); font-family:var(--font-sans);">Volt</small>
                </div>
                <span style="font-size:0.74rem; color:var(--text-secondary);">Rata-rata tegangan menengah</span>
            </div>

            <div class="metric-kpi-card metric-kpi-card--danger">
                <span style="font-size:0.72rem; color:var(--text-muted); text-transform:uppercase; font-weight:700;">Max / Min Voltage</span>
                <div style="font-size:1.4rem; font-weight:700; color:var(--danger); font-family:var(--font-serif); margin:4px 0;">
                    {{ $n($summary['max_voltage'], 1) }} / {{ $n($summary['min_voltage'], 1) }} <small style="font-size:0.75rem; color:var(--text-muted); font-family:var(--font-sans);">V</small>
                </div>
                <span style="font-size:0.74rem; color:var(--text-secondary);">Fluktuasi tegangan PLN</span>
            </div>

            <div class="metric-kpi-card metric-kpi-card--info">
                <span style="font-size:0.72rem; color:var(--text-muted); text-transform:uppercase; font-weight:700;">Rata-rata Arus (Current)</span>
                <div style="font-size:1.5rem; font-weight:700; color:var(--info); font-family:var(--font-serif); margin:4px 0;">
                    {{ $n($summary['avg_current'], 2) }} <small style="font-size:0.78rem; color:var(--text-muted); font-family:var(--font-sans);">Ampere</small>
                </div>
                <span style="font-size:0.74rem; color:var(--text-secondary);">Arus beban incoming</span>
            </div>

            <div class="metric-kpi-card metric-kpi-card--warning">
                <span style="font-size:0.72rem; color:var(--text-muted); text-transform:uppercase; font-weight:700;">Arus Puncak (Max Current)</span>
                <div style="font-size:1.5rem; font-weight:700; color:var(--warning); font-family:var(--font-serif); margin:4px 0;">
                    {{ $n($summary['max_current'], 2) }} <small style="font-size:0.78rem; color:var(--text-muted); font-family:var(--font-sans);">Ampere</small>
                </div>
                <span style="font-size:0.74rem; color:var(--text-secondary);">Beban tertinggi tercatat</span>
            </div>

            <div class="metric-kpi-card">
                <span style="font-size:0.72rem; color:var(--text-muted); text-transform:uppercase; font-weight:700;">Total Hari Pencatatan</span>
                <div style="font-size:1.5rem; font-weight:700; color:var(--text-primary); font-family:var(--font-serif); margin:4px 0;">
                    {{ $summary['count'] }} <small style="font-size:0.78rem; color:var(--text-muted); font-family:var(--font-sans);">hari</small>
                </div>
                <span style="font-size:0.74rem; color:var(--text-secondary);">Dalam interval terpilih</span>
            </div>
        </div>

        {{-- TABEL RANGKUMAN PARAMETER MVSB --}}
        <div style="margin-bottom:20px;">
            <h4 style="font-size:0.92rem; color:var(--text-primary); margin:0 0 10px; font-weight:700; display:flex; align-items:center; gap:6px;">
                <span>📊</span> Tabel Rangkuman Statistik Panel MVSB 20kV
            </h4>
            <div class="table-wrap">
                <table class="log-table" style="font-size:0.84rem;">
                    <thead>
                        <tr>
                            <th class="l">Parameter Panel</th>
                            <th class="c">Satuan</th>
                            <th class="r">Rata-rata (Avg)</th>
                            <th class="r">Nilai Maksimum (Max)</th>
                            <th class="r">Nilai Minimum (Min)</th>
                            <th class="l">Keterangan Sumber</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="l" style="font-weight:700; color:var(--purple);">Voltage 20kV Incoming</td>
                            <td class="c">Volt</td>
                            <td class="r" style="font-weight:700; font-family:var(--font-serif); color:var(--purple);">{{ $n($summary['avg_voltage'], 1) }} V</td>
                            <td class="r">{{ $n($summary['max_voltage'], 1) }} V</td>
                            <td class="r">{{ $n($summary['min_voltage'], 1) }} V</td>
                            <td class="l" style="color:var(--text-secondary);">Tegangan menengah dari gardu PLN</td>
                        </tr>
                        <tr>
                            <td class="l" style="font-weight:700; color:var(--info);">Current (Arus Beban)</td>
                            <td class="c">Ampere</td>
                            <td class="r" style="font-weight:700; font-family:var(--font-serif); color:var(--info);">{{ $n($summary['avg_current'], 2) }} A</td>
                            <td class="r" style="font-weight:700; color:var(--warning);">{{ $n($summary['max_current'], 2) }} A</td>
                            <td class="r">{{ $n($summary['min_current'], 2) }} A</td>
                            <td class="l" style="color:var(--text-secondary);">Arus operasional beban PLN</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- GRAFIK VISUALISASI CHART.JS --}}
        <div>
            <h4 style="font-size:0.92rem; color:var(--text-primary); margin:0 0 10px; font-weight:700; display:flex; align-items:center; gap:6px;">
                <span>📈</span> Grafik Tren Tegangan 20kV & Arus Beban PLN
            </h4>
            <div style="position:relative; min-height:260px; height:260px; background:var(--bg-card); border:1px solid var(--border); border-radius:10px; padding:12px; box-shadow:var(--shadow-sm);">
                @if(empty($chartData['labels']))
                    <div style="height:100%; display:flex; flex-direction:column; align-items:center; justify-content:center; color:var(--text-muted); gap:8px;">
                        <span style="font-size:1.8rem;">📊</span>
                        <span style="font-size:0.85rem;">Belum ada data pencatatan MVSB pada interval ini.</span>
                    </div>
                @else
                    <canvas id="chartMvsbInterval"></canvas>
                @endif
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- TABEL RIWAYAT PENCATATAN INTERVAL MVSB                                 --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    <div class="panel-head" style="margin-top:12px; margin-bottom:12px;">
        <div>
            <h3 style="margin:0; font-size:1.1rem; color:var(--text-primary);">
                Riwayat Pencatatan MVSB — Interval {{ $startDate->translatedFormat('d M Y') }} s/d {{ $endDate->translatedFormat('d M Y') }}
            </h3>
            <span style="font-size:0.8rem; color:var(--text-secondary);">
                Total {{ $rows->count() }} hari tercatat dalam interval ini.
            </span>
        </div>
    </div>

    {{-- ── Panel Aksi Masal: Verifikasi Masal & Hapus Masal (Khusus Super Admin, Maks 1 Bulan) ── --}}
    @if (auth()->user()->isSuperAdmin() && $rows->count())
        <div class="bulk-action-bar" style="margin-bottom:14px; background:linear-gradient(135deg, rgba(168,85,247,0.12), rgba(15,22,35,0.7)); border:1px solid rgba(168,85,247,0.3); border-radius:10px; padding:12px 16px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
            <div style="display:flex; align-items:center; gap:14px; flex-wrap:wrap;">
                <label style="display:inline-flex; align-items:center; gap:8px; cursor:pointer; font-weight:700; font-size:0.88rem; color:var(--purple); margin:0; user-select:none;">
                    <input type="checkbox" id="bulk-master-mvsb" style="width:18px; height:18px; cursor:pointer; accent-color:#a855f7;">
                    <span>Pilih Semua Data ({{ $rows->count() }} Data)</span>
                </label>
                <div style="display:flex; align-items:center; gap:8px;">
                    <button type="button" class="btn btn-sm" id="btn-select-unverified-mvsb" style="font-size:0.75rem; padding:3px 8px; background:rgba(234,179,8,0.15); color:#facc15; border:1px solid rgba(234,179,8,0.3);" title="Pilih hanya data yang belum diverifikasi">
                        ⚡ Pilih Belum Verifikasi
                    </button>
                    <button type="button" class="btn btn-sm" id="btn-clear-selection-mvsb" style="font-size:0.75rem; padding:3px 8px; background:rgba(255,255,255,0.06); color:var(--text-muted); display:none;" title="Batalkan semua pilihan">
                        ✕ Batal Pilih
                    </button>
                </div>
                <span id="bulk-selected-badge-mvsb" class="badge" style="background:rgba(168,85,247,0.2); color:#c084fc; border:1px solid rgba(168,85,247,0.4); font-size:0.78rem; padding:4px 10px; border-radius:6px; font-weight:700; display:none;">
                    <span id="bulk-selected-count-mvsb">0</span> data dipilih
                </span>
            </div>

            <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                <button type="button" id="btn-trigger-bulk-verify-mvsb" class="btn btn-sm btn-success" disabled style="display:inline-flex; align-items:center; gap:6px; opacity:0.5; cursor:not-allowed; transition:all 0.2s ease;">
                    <svg viewBox="0 0 20 20" fill="currentColor" style="width:16px; height:16px;"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd"/></svg>
                    <span id="btn-text-bulk-verify-mvsb">Verifikasi Masal</span>
                </button>

                <button type="button" id="btn-trigger-bulk-delete-mvsb" class="btn btn-sm btn-danger" disabled style="display:inline-flex; align-items:center; gap:6px; opacity:0.5; cursor:not-allowed; transition:all 0.2s ease;">
                    <svg viewBox="0 0 20 20" fill="currentColor" style="width:16px; height:16px;"><path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 0 0 6 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 1 0 .23 1.482l.149-.022.841 10.518A2.75 2.75 0 0 0 7.596 19h4.807a2.75 2.75 0 0 0 2.742-2.53l.841-10.52.149.023a.75.75 0 0 0 .23-1.482A41.03 41.03 0 0 0 14 4.193V3.75A2.75 2.75 0 0 0 11.25 1h-2.5ZM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4ZM8.58 7.72a.75.75 0 0 1 .75.748l.5 6a.75.75 0 0 1-1.498.124l-.5-6a.75.75 0 0 1 .748-.872Zm3.588.748a.75.75 0 0 0-1.498-.124l-.5 6a.75.75 0 0 0 1.498.124l.5-6Z" clip-rule="evenodd"/></svg>
                    <span id="btn-text-bulk-delete-mvsb">Hapus Masal</span>
                </button>
            </div>
        </div>

        <form id="bulk-action-form-mvsb" method="POST" action="" style="display:none;">
            @csrf
            <div id="bulk-inputs-mvsb"></div>
        </form>
    @endif

    <div class="table-wrap">
        <table class="log-table">
            <thead>
                <tr>
                    @if (auth()->user()->isSuperAdmin())
                        <th class="c" style="width:42px; vertical-align:middle;">
                            <input type="checkbox" id="bulk-table-all-mvsb" title="Pilih Semua" style="width:17px; height:17px; cursor:pointer; accent-color:#a855f7;">
                        </th>
                    @endif
                    <th class="l">Tanggal</th>
                    <th class="r">Voltage 20kV (Volt)</th>
                    <th class="r">Current (Ampere)</th>
                    <th class="c">Foto Fisik</th>
                    <th class="l">Pencatat</th>
                    <th class="c">Status</th>
                    <th class="c">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $r)
                    <tr>
                        @if (auth()->user()->isSuperAdmin())
                            <td class="c" style="vertical-align:middle;">
                                <input type="checkbox" name="bulk_ids[]" value="{{ $r->id }}" class="bulk-item-mvsb" data-verified="{{ $r->isVerified() ? '1' : '0' }}" style="width:17px; height:17px; cursor:pointer; accent-color:#a855f7;">
                            </td>
                        @endif
                        <td class="l" style="white-space:nowrap;">
                            <span style="font-weight:600; color:var(--text-primary);">
                                {{ $r->tanggal->translatedFormat('d M Y') }}
                            </span>
                            <small style="display:block; color:var(--text-muted); font-size:0.74rem;">
                                {{ $r->tanggal->translatedFormat('l') }}
                            </small>
                        </td>

                        <td class="r" style="font-weight:600; color:var(--purple);">
                            {{ $n($r->voltage_20kv, 1) }} <small style="font-size:0.72rem; color:var(--text-muted); font-weight:normal;">Volt</small>
                        </td>

                        <td class="r" style="font-weight:600; color:var(--info);">
                            {{ $n($r->current, 2) }} <small style="font-size:0.72rem; color:var(--text-muted); font-weight:normal;">A</small>
                        </td>

                        <td class="c">
                            @if($r->foto)
                                <a href="{{ asset('storage/'.$r->foto) }}" data-lightbox="{{ asset('storage/'.$r->foto) }}" data-caption="MVSB 20kV — {{ $r->tanggal->format('d/m/Y') }}" style="display:inline-block; border-radius:4px; overflow:hidden; border:1px solid var(--border);">
                                    <img src="{{ asset('storage/'.$r->foto) }}" alt="Foto" style="width:34px; height:34px; object-fit:cover; display:block;">
                                </a>
                            @else
                                <span style="color:var(--text-muted); font-size:0.74rem;">—</span>
                            @endif
                        </td>

                        <td class="l" style="white-space:nowrap; color:var(--text-secondary); font-size:0.8rem;">
                            {{ $r->user?->name ?? 'Teknisi' }}
                        </td>

                        <td class="c">
                            @if($r->isVerified())
                                <span class="badge badge-ok" style="font-size:0.72rem;" title="Diverifikasi oleh {{ $r->verifier?->name ?? 'Supervisor' }} pada {{ $r->verified_at?->format('d/m/Y H:i') }}">
                                    ✓ Diverifikasi
                                </span>
                            @else
                                <span class="badge badge-wait" style="font-size:0.72rem;">
                                    Menunggu
                                </span>
                            @endif
                        </td>

                        <td class="c" style="white-space:nowrap;">
                            <div style="display:inline-flex; align-items:center; gap:6px;">
                                <a href="{{ route('public-area.mvsb.show', $r) }}" class="btn btn-sm" style="padding:4px 8px; font-size:0.75rem;" title="Lihat detail pembacaan">
                                    Detail
                                </a>

                                @if($canManage && !$r->isVerified())
                                    <form method="POST" action="{{ route('public-area.mvsb.verify', $r) }}" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-sm" style="background:var(--success-bg); color:var(--success); border:1px solid var(--success-border); padding:4px 8px; font-size:0.75rem;" title="Verifikasi Logsheet">
                                            ✓
                                        </button>
                                    </form>
                                @endif

                                @if(auth()->user()->isSuperAdmin())
                                    <form method="POST" action="{{ route('public-area.mvsb.destroy', $r) }}" onsubmit="return confirm('Hapus data logsheet MVSB tanggal {{ $r->tanggal->format('d/m/Y') }}?');" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm" style="background:var(--danger-bg); color:var(--danger); border:1px solid var(--danger-border); padding:4px 8px; font-size:0.75rem;" title="Hapus Permanen">
                                            ✕
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ auth()->user()->isSuperAdmin() ? 8 : 7 }}" style="padding:32px 14px; text-align:center; color:var(--text-muted);">
                            Belum ada data pencatatan MVSB Incoming From PLN pada interval {{ $startDate->translatedFormat('d M Y') }} s/d {{ $endDate->translatedFormat('d M Y') }}.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    document.querySelectorAll('.photo-upload-group').forEach(group => {
        const mainInput   = group.querySelector('.input-file-main');
        const cameraInput = group.querySelector('.input-file-camera');
        const previewWrap = group.querySelector('.photo-preview-wrap');
        const imgPreview  = group.querySelector('.img-preview');
        const namePreview = group.querySelector('.photo-preview-name');
        const clearBtn    = group.querySelector('.btn-clear-photo');

        function handleFile(file, source) {
            if (!file) return;
            if (imgPreview) imgPreview.src = URL.createObjectURL(file);
            if (namePreview) {
                const srcLabel = source === 'camera' ? '📷 Kamera' : '🖼️ Galeri';
                namePreview.textContent = `${srcLabel}: ${file.name}`;
            }
            if (previewWrap) previewWrap.style.display = 'inline-flex';
            if (source === 'camera' && mainInput) {
                try {
                    const dt = new DataTransfer();
                    dt.items.add(file);
                    mainInput.files = dt.files;
                } catch(e) {}
            }
        }

        if (mainInput) {
            mainInput.addEventListener('change', function() {
                if (this.files && this.files[0]) handleFile(this.files[0], 'gallery');
            });
        }
        if (cameraInput) {
            cameraInput.addEventListener('change', function() {
                if (this.files && this.files[0]) handleFile(this.files[0], 'camera');
            });
        }
        if (clearBtn) {
            clearBtn.addEventListener('click', function() {
                if (mainInput) mainInput.value = '';
                if (cameraInput) cameraInput.value = '';
                if (previewWrap) previewWrap.style.display = 'none';
                if (imgPreview) imgPreview.src = '';
                if (namePreview) namePreview.textContent = '';
            });
        }
    });

    // Inisialisasi Grafik Visualisasi Chart.js Tren Interval MVSB
    document.addEventListener('DOMContentLoaded', function() {
        const chartData = @json($chartData);
        const ctx = document.getElementById('chartMvsbInterval');
        if (!ctx || !chartData || !chartData.labels || chartData.labels.length === 0) return;

        function getThemeColors() {
            const isLight = document.documentElement.getAttribute('data-theme') === 'light';
            return {
                isLight: isLight,
                textColor: isLight ? '#334155' : 'rgba(255,255,255,0.75)',
                tickColor: isLight ? '#64748b' : 'rgba(255,255,255,0.6)',
                gridColor: isLight ? 'rgba(0,0,0,0.06)' : 'rgba(255,255,255,0.05)',
                voltColor: isLight ? '#7c3aed' : '#c084fc',
                voltBg:    isLight ? 'rgba(124,58,237,0.08)' : 'rgba(192,132,252,0.12)',
                currColor: isLight ? '#0284c7' : '#38bdf8',
            };
        }

        let th = getThemeColors();

        const chartMvsb = new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.labels,
                datasets: [
                    {
                        label: 'Voltage 20kV (Volt)',
                        data: chartData.voltage,
                        borderColor: th.voltColor,
                        backgroundColor: th.voltBg,
                        borderWidth: 2,
                        fill: true,
                        tension: 0.25,
                        yAxisID: 'yVolt',
                        pointRadius: chartData.labels.length > 35 ? 0 : 3,
                    },
                    {
                        label: 'Current (Ampere)',
                        data: chartData.current,
                        borderColor: th.currColor,
                        borderWidth: 2,
                        fill: false,
                        tension: 0.25,
                        yAxisID: 'yCurr',
                        pointRadius: chartData.labels.length > 35 ? 0 : 3,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            color: th.textColor,
                            font: { size: 11 },
                            usePointStyle: true,
                            boxWidth: 8
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { color: th.gridColor },
                        ticks: { color: th.tickColor, font: { size: 10 } }
                    },
                    yVolt: {
                        type: 'linear',
                        position: 'left',
                        title: { display: true, text: 'Volt', color: th.voltColor, font: { size: 10 } },
                        grid: { color: th.gridColor },
                        ticks: { color: th.tickColor, font: { size: 10 } }
                    },
                    yCurr: {
                        type: 'linear',
                        position: 'right',
                        title: { display: true, text: 'Ampere', color: th.currColor, font: { size: 10 } },
                        grid: { drawOnChartArea: false },
                        ticks: { color: th.tickColor, font: { size: 10 } }
                    }
                }
            }
        });

        window.addEventListener('kpr-theme-changed', function() {
            const next = getThemeColors();
            chartMvsb.data.datasets[0].borderColor = next.voltColor;
            chartMvsb.data.datasets[0].backgroundColor = next.voltBg;
            chartMvsb.data.datasets[1].borderColor = next.currColor;
            chartMvsb.options.plugins.legend.labels.color = next.textColor;
            chartMvsb.options.scales.x.grid.color = next.gridColor;
            chartMvsb.options.scales.x.ticks.color = next.tickColor;
            chartMvsb.options.scales.yVolt.grid.color = next.gridColor;
            chartMvsb.options.scales.yVolt.ticks.color = next.tickColor;
            chartMvsb.options.scales.yVolt.title.color = next.voltColor;
            chartMvsb.options.scales.yCurr.ticks.color = next.tickColor;
            chartMvsb.options.scales.yCurr.title.color = next.currColor;
            chartMvsb.update();
        });
    });

    // ── Fitur Aksi Masal: Verifikasi Masal & Hapus Masal MVSB (Khusus Super Admin) ──
    @if (auth()->user()->isSuperAdmin())
    (function() {
        const bulkMaster = document.getElementById('bulk-master-mvsb');
        const bulkTableAll = document.getElementById('bulk-table-all-mvsb');
        const btnSelectUnverified = document.getElementById('btn-select-unverified-mvsb');
        const btnClearSelection = document.getElementById('btn-clear-selection-mvsb');
        const badge = document.getElementById('bulk-selected-badge-mvsb');
        const badgeCount = document.getElementById('bulk-selected-count-mvsb');
        const btnVerify = document.getElementById('btn-trigger-bulk-verify-mvsb');
        const btnDelete = document.getElementById('btn-trigger-bulk-delete-mvsb');
        const textVerify = document.getElementById('btn-text-bulk-verify-mvsb');
        const textDelete = document.getElementById('btn-text-bulk-delete-mvsb');
        const bulkForm = document.getElementById('bulk-action-form-mvsb');
        const bulkInputs = document.getElementById('bulk-inputs-mvsb');

        function getSelected() {
            return Array.from(document.querySelectorAll('.bulk-item-mvsb:checked')).map(cb => cb.value);
        }

        function updateToolbar() {
            const checkboxes = document.querySelectorAll('.bulk-item-mvsb');
            const total = checkboxes.length;
            const checked = document.querySelectorAll('.bulk-item-mvsb:checked');
            const count = checked.length;

            if (badgeCount) badgeCount.textContent = count;
            if (badge) badge.style.display = count > 0 ? 'inline-flex' : 'none';
            if (btnClearSelection) btnClearSelection.style.display = count > 0 ? 'inline-flex' : 'none';

            if (count > 0) {
                if (btnVerify) {
                    btnVerify.disabled = false;
                    btnVerify.style.opacity = '1';
                    btnVerify.style.cursor = 'pointer';
                    if (textVerify) textVerify.textContent = `Verifikasi (${count})`;
                }
                if (btnDelete) {
                    btnDelete.disabled = false;
                    btnDelete.style.opacity = '1';
                    btnDelete.style.cursor = 'pointer';
                    if (textDelete) textDelete.textContent = `Hapus (${count})`;
                }
            } else {
                if (btnVerify) {
                    btnVerify.disabled = true;
                    btnVerify.style.opacity = '0.5';
                    btnVerify.style.cursor = 'not-allowed';
                    if (textVerify) textVerify.textContent = 'Verifikasi Masal';
                }
                if (btnDelete) {
                    btnDelete.disabled = true;
                    btnDelete.style.opacity = '0.5';
                    btnDelete.style.cursor = 'not-allowed';
                    if (textDelete) textDelete.textContent = 'Hapus Masal';
                }
            }

            const allChecked = total > 0 && count === total;
            const someChecked = count > 0 && count < total;

            if (bulkMaster) {
                bulkMaster.checked = allChecked;
                bulkMaster.indeterminate = someChecked;
            }
            if (bulkTableAll) {
                bulkTableAll.checked = allChecked;
                bulkTableAll.indeterminate = someChecked;
            }
        }

        function toggleAll(checked) {
            document.querySelectorAll('.bulk-item-mvsb').forEach(cb => { cb.checked = checked; });
            updateToolbar();
        }

        if (bulkMaster) bulkMaster.addEventListener('change', function() { toggleAll(this.checked); });
        if (bulkTableAll) bulkTableAll.addEventListener('change', function() { toggleAll(this.checked); });

        document.querySelectorAll('.bulk-item-mvsb').forEach(cb => {
            cb.addEventListener('change', updateToolbar);
        });

        if (btnSelectUnverified) {
            btnSelectUnverified.addEventListener('click', function() {
                document.querySelectorAll('.bulk-item-mvsb').forEach(cb => {
                    cb.checked = (cb.getAttribute('data-verified') === '0');
                });
                updateToolbar();
            });
        }

        if (btnClearSelection) {
            btnClearSelection.addEventListener('click', function() {
                toggleAll(false);
            });
        }

        if (btnVerify) {
            btnVerify.addEventListener('click', function() {
                const ids = getSelected();
                if (ids.length === 0) return alert('Pilih minimal 1 data MVSB.');
                if (!confirm(`Konfirmasi Verifikasi Masal:\n\nApakah Anda yakin ingin memverifikasi ${ids.length} data logsheet MVSB terpilih?`)) return;

                bulkInputs.innerHTML = '';
                ids.forEach(id => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'ids[]';
                    input.value = id;
                    bulkInputs.appendChild(input);
                });
                bulkForm.action = '{{ route('public-area.mvsb.bulk.verify') }}';
                bulkForm.submit();
            });
        }

        if (btnDelete) {
            btnDelete.addEventListener('click', function() {
                const ids = getSelected();
                if (ids.length === 0) return alert('Pilih minimal 1 data MVSB.');
                if (!confirm(`⚠️ PERINGATAN HAPUS MASAL (SUPER ADMIN):\n\nApakah Anda yakin ingin MENGHAPUS PERMANEN ${ids.length} data logsheet MVSB terpilih?\n\n• Semua foto fisik lampiran akan dihapus dari server.\n• Tindakan ini TIDAK DAPAT DIBATALKAN.`)) return;

                bulkInputs.innerHTML = '';
                ids.forEach(id => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'ids[]';
                    input.value = id;
                    bulkInputs.appendChild(input);
                });
                bulkForm.action = '{{ route('public-area.mvsb.bulk.destroy') }}';
                bulkForm.submit();
            });
        }
    })();
    @endif
</script>
@endpush
