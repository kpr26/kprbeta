@extends('layouts.panel')

@php
    $n = fn ($v, $d = 2) => ($v !== null && $v !== '') ? number_format((float)$v, $d, ',', '.') : '—';
    $canManage = auth()->user()->isSuperAdmin() || auth()->user()->isSupervisor() || auth()->user()->isAdmin();
    $isSuperAdmin = auth()->user()->isSuperAdmin();
    $todayStr = \Carbon\Carbon::today()->translatedFormat('l, d F Y');
    $intervalDays = (int) round($startDate->diffInDays($endDate) + 1);
@endphp

@section('title', 'Logsheet LVSB Public Area')
@section('page-title', 'LVSB (Low Voltage Switchboard)')
@section('page-subtitle', 'Pencatatan Parameter Tegangan, Arus, Daya, Energi & PF Panel Distribusi LVSB')

@section('panel')
<section class="panel" style="max-width:1300px; margin:0 auto;">
    {{-- Header Sub Menu --}}
    <div class="panel-head" style="align-items:flex-start; flex-wrap:wrap; gap:16px;">
        <div>
            <div style="display:flex; align-items:center; gap:8px;">
                <span style="font-size:1.4rem;">⚡</span>
                <h2 style="margin:0; font-size:1.35rem;">Sub Menu: LVSB (Low Voltage Switchboard)</h2>
            </div>
            <p style="color:var(--text-secondary); margin:4px 0 0; font-size:0.88rem;">
                Pencatatan harian Incoming Trafo, Outgoing Feeder Riser & AC, serta Power Factor Kapasitor.
            </p>
        </div>

        <div style="display:flex; gap:8px; flex-wrap:wrap; align-items:center;">
            <a href="{{ route('public-area.index') }}" class="btn btn-sm">
                ← Menu Public Area
            </a>
            <a href="{{ route('public-area.water.index') }}" class="btn btn-sm" style="background:rgba(56,189,248,0.12); color:#38bdf8; border:1px solid rgba(56,189,248,0.3);">
                💧 Water Meter
            </a>
            <a href="{{ route('public-area.mvsb.index') }}" class="btn btn-sm" style="background:rgba(168,85,247,0.12); color:#c084fc; border:1px solid rgba(168,85,247,0.3);">
                ⚡ MVSB 20kV
            </a>

            {{-- Tombol Khusus Super Admin: Input Back Date (Data Terlewat) --}}
            @if ($isSuperAdmin)
                <a href="{{ route('public-area.manual.lvsb') }}" class="btn btn-sm" style="background:rgba(234,179,8,0.18); color:#facc15; border:1px solid rgba(234,179,8,0.45); font-weight:700; display:inline-flex; align-items:center; gap:6px;" title="Input data back date secara manual (Khusus Super Admin)">
                    <span>📅 Input Back Date (Terlewat)</span>
                </a>
            @endif

            {{-- Export Buttons --}}
            @if ($canManage)
                <a href="{{ route('public-area.lvsb.excel', ['start_date' => $startDate->toDateString(), 'end_date' => $endDate->toDateString(), 'preset' => $preset]) }}" class="btn btn-excel btn-sm" title="Export Excel Rekapitulasi Data LVSB">
                    <svg viewBox="0 0 20 20" fill="currentColor" style="width:14px;height:14px;"><path d="M10.75 2.75a.75.75 0 0 0-1.5 0v8.614L6.295 8.235a.75.75 0 1 0-1.09 1.03l4.25 4.5a.75.75 0 0 0 1.09 0l4.25-4.5a.75.75 0 0 0-1.09-1.03l-2.955 3.129V2.75Z"/><path d="M3.5 12.75a.75.75 0 0 0-1.5 0v2.5A2.75 2.75 0 0 0 4.75 18h10.5A2.75 2.75 0 0 0 18 15.25v-2.5a.75.75 0 0 0-1.5 0v2.5c0 .69-.56 1.25-1.25 1.25H4.75c-.69 0-1.25-.56-1.25-1.25v-2.5Z"/></svg>Export to Excel
                </a>
                <a href="{{ route('public-area.lvsb.pdf', ['start_date' => $startDate->toDateString(), 'end_date' => $endDate->toDateString(), 'preset' => $preset]) }}" target="_blank" class="btn btn-pdf btn-sm" title="Export PDF Rekapitulasi Data (Grafik & Tabel)">
                    <svg viewBox="0 0 20 20" fill="currentColor" style="width:14px;height:14px;"><path fill-rule="evenodd" d="M5 2.75C5 1.784 5.784 1 6.75 1h6.5c.966 0 1.75.784 1.75 1.75v3.552c.377.046.752.097 1.126.153A2.212 2.212 0 0 1 18 8.653v4.097A2.25 2.25 0 0 1 15.75 15h-.241l.305 1.984A1.75 1.75 0 0 1 14.084 19H5.915a1.75 1.75 0 0 1-1.73-2.016L4.492 15H4.25A2.25 2.25 0 0 1 2 12.75V8.653c0-1.082.775-2.034 1.874-2.198.374-.056.75-.107 1.127-.153L5 6.25v-3.5Zm8.5 3.397a41.533 41.533 0 0 0-7 0V2.75a.25.25 0 0 1 .25-.25h6.5a.25.25 0 0 1 .25.25v3.397ZM6.608 12.5a.25.25 0 0 0-.247.212l-.693 4.5a.25.25 0 0 0 .247.288h8.17a.25.25 0 0 0 .246-.288l-.692-4.5a.25.25 0 0 0-.247-.212H6.608Z" clip-rule="evenodd"/></svg>Export to PDF
                </a>
            @endif
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- TOOLBAR FILTER INTERVAL DATA LVSB (MAKSIMAL 12 BULAN / 1 TAHUN)        --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    <div style="background:var(--bg-card); border:1px solid var(--border); border-radius:12px; padding:14px 18px; margin-bottom:24px; box-shadow:var(--shadow-sm);">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
            {{-- Quick Presets --}}
            <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                <span style="font-size:0.75rem; text-transform:uppercase; font-weight:700; color:var(--text-muted); letter-spacing:0.04em;">Preset Interval:</span>
                <a href="{{ route('public-area.lvsb.index') }}" class="btn btn-sm {{ empty($preset) && !request()->filled('start_date') ? 'btn-primary' : '' }}" style="{{ empty($preset) && !request()->filled('start_date') ? 'background:#d97706; color:#fff;' : 'background:rgba(255,255,255,0.05); color:var(--text-secondary);' }} font-size:0.78rem; padding:4px 10px;">
                    Bulan Ini
                </a>
                <a href="{{ route('public-area.lvsb.index', ['preset' => '3m']) }}" class="btn btn-sm" style="{{ $preset === '3m' ? 'background:#d97706; color:#fff;' : 'background:rgba(255,255,255,0.05); color:var(--text-secondary);' }} font-size:0.78rem; padding:4px 10px;">
                    3 Bulan
                </a>
                <a href="{{ route('public-area.lvsb.index', ['preset' => '6m']) }}" class="btn btn-sm" style="{{ $preset === '6m' ? 'background:#d97706; color:#fff;' : 'background:rgba(255,255,255,0.05); color:var(--text-secondary);' }} font-size:0.78rem; padding:4px 10px;">
                    6 Bulan
                </a>
                <a href="{{ route('public-area.lvsb.index', ['preset' => '12m']) }}" class="btn btn-sm" style="{{ in_array($preset, ['12m', '1y'], true) ? 'background:#d97706; color:#fff;' : 'background:rgba(255,255,255,0.05); color:var(--text-secondary);' }} font-size:0.78rem; padding:4px 10px;">
                    1 Tahun (Maks)
                </a>
            </div>

            {{-- Custom Date Range Form --}}
            <form method="GET" action="{{ route('public-area.lvsb.index') }}" style="display:flex; align-items:center; gap:8px; flex-wrap:wrap; margin:0;">
                <span style="font-size:0.75rem; color:var(--text-muted); font-weight:600;">Rentang:</span>
                <input type="date" name="start_date" value="{{ $startDate->format('Y-m-d') }}" max="{{ today()->toDateString() }}" class="input" style="padding:4px 8px; font-size:0.8rem; width:135px;" title="Tanggal Mulai">
                <span style="color:var(--text-muted); font-size:0.8rem;">s/d</span>
                <input type="date" name="end_date" value="{{ $endDate->format('Y-m-d') }}" max="{{ today()->toDateString() }}" class="input" style="padding:4px 8px; font-size:0.8rem; width:135px;" title="Tanggal Selesai">
                <button type="submit" class="btn btn-sm btn-primary" style="padding:5px 12px; font-size:0.8rem; background:#d97706; border-color:#d97706;">
                    Filter
                </button>
            </form>
        </div>

        {{-- Active Interval Info Pill --}}
        <div style="margin-top:10px; padding-top:8px; border-top:1px solid rgba(255,255,255,0.06); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px; font-size:0.78rem;">
            <div style="color:var(--text-secondary); display:flex; align-items:center; gap:6px;">
                <span style="display:inline-block; width:6px; height:6px; border-radius:50%; background:#fbbf24;"></span>
                <span>Interval Aktif: <strong style="color:var(--text-primary);">{{ $startDate->translatedFormat('d M Y') }} s/d {{ $endDate->translatedFormat('d M Y') }}</strong> ({{ $intervalDays }} hari)</span>
            </div>
            <span style="color:var(--text-muted); font-size:0.74rem;">* Ekspor Excel & PDF otomatis mengikuti interval aktif (maksimal 12 bulan / 1 tahun).</span>
        </div>
    </div>

    {{-- Pill Mode Offline --}}
    @if(auth()->user()->role === 'teknisi')
        <div style="display:inline-flex; align-items:center; gap:8px; background:rgba(200,170,110,0.06); border:1px solid rgba(200,170,110,0.22); border-radius:20px; padding:4px 12px; margin-bottom:16px; font-size:0.78rem; color:var(--text-secondary);">
            <span style="display:inline-block; width:7px; height:7px; border-radius:50%; background:#10b981; box-shadow:0 0 6px #10b981;"></span>
            <span><strong style="color:var(--accent-light);">Mode Offline Aktif:</strong> Anda dapat menginput parameter panel LVSB di basement/ruang panel tanpa sinyal. Data otomatis tersinkron saat online.</span>
        </div>
    @endif

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- CARD INPUT TERPISAH PER SUB-MENU PANEL LVSB (SEAMLESS & SLIDEABLE)     --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    @php
        $filledCount = $todayReading ? collect(array_keys($structure))->filter(fn($k) => $todayReading->isGroupFilled($k))->count() : 0;
        $totalGroups = count($structure);
    @endphp

    <style>
        .lvsb-tab-pill {
            user-select: none;
            outline: none;
        }
        .lvsb-tab-pill:hover {
            background: var(--bg-card-subtle) !important;
            border-color: var(--accent) !important;
        }
        .lvsb-tab-pill.active {
            background: linear-gradient(135deg, #fbbf24, #d97706) !important;
            color: #090d15 !important;
            border-color: #f59e0b !important;
            font-weight: 700 !important;
            box-shadow: 0 2px 8px rgba(245, 158, 11, 0.35);
        }
        .btn-view-mode {
            background: transparent;
            color: var(--text-muted);
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-view-mode.active {
            background: var(--bg-card);
            color: var(--text-primary);
            font-weight: 700;
            box-shadow: var(--shadow-sm);
        }
        .lvsb-slide-active {
            animation: fadeInLvsbSlide 0.22s ease-out;
        }
        @keyframes fadeInLvsbSlide {
            from { opacity: 0; transform: translateY(5px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @media (max-width: 640px) {
            .lvsb-subcard {
                padding: 14px !important;
            }
        }
    </style>

    <div style="margin-bottom:28px;">
        {{-- Section Header --}}
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:14px;">
            <div>
                <h3 style="font-size:1.15rem; color:var(--text-primary); margin:0 0 4px; display:flex; align-items:center; gap:8px;">
                    <span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:#fbbf24;"></span>
                    Pencatatan LVSB Hari Ini — {{ $todayStr }}
                </h3>
                <p style="font-size:0.8rem; color:var(--text-muted); margin:0;">
                    Pencatatan mandiri untuk tiap sub-menu panel. Simpan setiap sub-menu tanpa harus menyelesaikan seluruh panel sekaligus.
                </p>
            </div>
            <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                {{-- Progress Badge --}}
                <div style="display:inline-flex; align-items:center; gap:6px; background:var(--bg-card-subtle); border:1px solid var(--border); border-radius:20px; padding:4px 12px; font-size:0.78rem; font-weight:600;">
                    <span style="color:var(--text-muted);">Progres:</span>
                    <strong style="color:{{ $filledCount === $totalGroups ? 'var(--success)' : 'var(--warning)' }};">
                        {{ $filledCount }}/{{ $totalGroups }} Sub-Menu
                    </strong>
                    @if($filledCount === $totalGroups)
                        <span style="color:var(--success);">✓ Selesai</span>
                    @endif
                </div>

                {{-- View Mode Switcher: Slide vs Grid --}}
                <div class="view-mode-toggle" style="display:inline-flex; background:var(--bg-card-subtle); border:1px solid var(--border); border-radius:8px; padding:2px;">
                    <button type="button" id="btnModeSlide" class="btn btn-sm btn-view-mode active" style="padding:4px 10px; font-size:0.75rem; border-radius:6px; border:none; display:flex; align-items:center; gap:5px;" title="Mode Slide / Carousel (Satu Per Satu)">
                        <span>📑 Slide Fokus</span>
                    </button>
                    <button type="button" id="btnModeGrid" class="btn btn-sm btn-view-mode" style="padding:4px 10px; font-size:0.75rem; border-radius:6px; border:none; display:flex; align-items:center; gap:5px;" title="Mode Grid (Tampilkan Semua Seperti Water Meter)">
                        <span>▦ Grid Semua</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Horizontal Slideable Tabs Sub-Menu (Swipe / Slide Navigation) --}}
        <div style="position:relative; margin-bottom:14px;">
            <div id="lvsbTabsBar" style="display:flex; align-items:center; gap:8px; overflow-x:auto; white-space:nowrap; scrollbar-width:none; -webkit-overflow-scrolling:touch; padding:4px 2px 8px;">
                <button type="button" class="lvsb-tab-pill active" data-target="all" style="padding:7px 14px; border-radius:20px; font-size:0.78rem; font-weight:600; cursor:pointer; transition:all 0.2s; border:1px solid var(--border); background:var(--bg-card-subtle); color:var(--text-primary); display:inline-flex; align-items:center; gap:6px;">
                    <span>⚡ Semua Sub-Menu</span>
                    <span style="font-size:0.7rem; background:rgba(255,255,255,0.1); padding:1px 6px; border-radius:10px;">{{ $totalGroups }}</span>
                </button>

                @foreach ($structure as $gKey => $group)
                    @php
                        $isFilled = $todayReading?->isGroupFilled($gKey);
                    @endphp
                    <button type="button" class="lvsb-tab-pill" data-target="{{ $gKey }}" style="padding:7px 14px; border-radius:20px; font-size:0.78rem; font-weight:600; cursor:pointer; transition:all 0.2s; border:1px solid var(--border); background:var(--bg-card); color:var(--text-secondary); display:inline-flex; align-items:center; gap:6px;">
                        <span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:{{ $isFilled ? '#10b981' : $group['color'] }}; box-shadow:0 0 6px {{ $isFilled ? '#10b981' : $group['color'] }}88;"></span>
                        <span>{{ $group['badge'] }}</span>
                        @if($isFilled)
                            <span style="color:#10b981; font-size:0.75rem;">✓</span>
                        @endif
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Slide Navigation Header (Aktif pada Mode Slide) --}}
        <div id="lvsbSlideNavHeader" style="display:flex; justify-content:space-between; align-items:center; background:var(--bg-card); border:1px solid var(--border); border-radius:10px; padding:8px 14px; margin-bottom:16px; box-shadow:var(--shadow-sm);">
            <button type="button" id="btnSlidePrev" class="btn btn-sm" style="padding:5px 12px; font-size:0.8rem; display:inline-flex; align-items:center; gap:6px;">
                <span>← Prev</span>
            </button>
            <div style="font-size:0.82rem; font-weight:700; color:var(--text-primary); text-align:center;">
                <span id="slideIndicator">Sub-Menu 1 dari {{ $totalGroups }}</span> &middot; <span id="slideCurrentTitle" style="color:#fbbf24;">Incoming From Trafo Apart A/B</span>
            </div>
            <button type="button" id="btnSlideNext" class="btn btn-sm" style="padding:5px 12px; font-size:0.8rem; display:inline-flex; align-items:center; gap:6px;">
                <span>Next →</span>
            </button>
        </div>

        {{-- Container Kartu Sub-Menu LVSB --}}
        <div id="lvsbCardsContainer" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(340px, 1fr)); gap:18px;">
            @php $slideIndex = 0; @endphp
            @foreach ($structure as $gKey => $group)
                @php
                    $slideIndex++;
                    $isGroupDone = $todayReading?->isGroupFilled($gKey);
                    $groupFoto   = $todayReading?->getFoto($gKey);
                @endphp

                <div class="meter-card lvsb-subcard" data-group="{{ $gKey }}" data-index="{{ $slideIndex }}" data-title="{{ $group['title'] }}" data-color="{{ $group['color'] }}" style="border:1px solid {{ $group['color'] }}44; border-top:4px solid {{ $group['color'] }}; background:var(--bg-card); padding:18px 20px; border-radius:12px; display:flex; flex-direction:column; justify-content:space-between; box-shadow:var(--shadow-sm);">
                    <div>
                        {{-- Card Header --}}
                        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:12px; gap:8px;">
                            <div>
                                <span class="badge" style="background:{{ $group['color'] }}20; color:{{ $group['color'] }}; border:1px solid {{ $group['color'] }}44; font-size:0.72rem; padding:2px 8px; font-weight:700; text-transform:uppercase;">
                                    {{ $group['badge'] }}
                                </span>
                                <h4 style="font-size:1.05rem; font-weight:700; color:var(--text-primary); margin:6px 0 2px;">
                                    {{ $group['title'] }}
                                </h4>
                                <span style="font-size:0.76rem; color:var(--text-muted);">
                                    @if($group['has_subgroups'])
                                        {{ count($group['subgroups']) }} Feeder: {{ collect($group['subgroups'])->pluck('label')->join(', ') }}
                                    @else
                                        {{ count($group['fields']) }} Parameter Panel Distribusi
                                    @endif
                                </span>
                            </div>

                            @if($isGroupDone)
                                <span class="badge badge-ok" style="font-size:0.75rem; flex-shrink:0;">✓ Sudah Dicatat</span>
                            @else
                                <span class="badge badge-wait" style="font-size:0.75rem; flex-shrink:0;">Belum Dicatat</span>
                            @endif
                        </div>

                        {{-- Stand / Parameter Info Box --}}
                        <div style="background:var(--bg-card-subtle); border:1px solid var(--border); border-radius:8px; padding:10px 12px; margin-bottom:14px;">
                            @if($isGroupDone)
                                <div style="display:flex; justify-content:space-between; align-items:baseline; margin-bottom:4px; flex-wrap:wrap; gap:4px;">
                                    <span style="font-size:0.78rem; color:var(--text-muted); font-weight:600;">Status Hari Ini:</span>
                                    <span style="font-size:0.78rem; font-weight:700; color:var(--success);">✓ Data Tersimpan di Sistem</span>
                                </div>
                                @if($gKey === 'incoming_trafo')
                                    <div style="font-size:0.76rem; color:var(--text-secondary); line-height:1.45; margin:4px 0;">
                                        Volt: <b style="color:var(--warning);">{{ $n($todayReading->val('incoming_trafo', 'voltage_3p'), 1) }} V</b> &middot; 
                                        Arus: <b style="color:var(--purple);">{{ $n($todayReading->val('incoming_trafo', 'current')) }} A</b> &middot; 
                                        Daya: <b style="color:var(--info);">{{ $n($todayReading->val('incoming_trafo', 'power')) }} kW</b> &middot; 
                                        Energy: <b style="color:var(--text-primary);">{{ $n($todayReading->val('incoming_trafo', 'energy'), 8) }} GWh</b>
                                    </div>
                                @elseif($gKey === 'incoming_capacitor')
                                    <div style="font-size:0.76rem; color:var(--text-secondary); line-height:1.45; margin:4px 0;">
                                        Power Factor (PF): <b style="color:var(--success);">{{ $n($todayReading->val('incoming_capacitor', 'power_factor'), 2) }}</b>
                                    </div>
                                @endif
                                <div style="display:flex; justify-content:space-between; font-size:0.74rem; color:var(--text-muted); margin-top:4px;">
                                    <span>Petugas: <b style="color:var(--accent-light);">{{ $todayReading->user?->name ?? 'Teknisi' }}</b></span>
                                    <span>Pukul: {{ $todayReading->updated_at->format('H:i') }} WIB</span>
                                </div>
                                @if($todayReading->catatan)
                                    <div style="font-size:0.74rem; color:var(--text-secondary); margin-top:6px; padding-top:6px; border-top:1px dashed var(--border);">
                                        <i>"{{ $todayReading->catatan }}"</i>
                                    </div>
                                @endif
                            @else
                                <div style="font-size:0.78rem; color:var(--text-muted);">
                                    Acuan data sebelumnya:
                                    @if($prevReading && $prevReading->isGroupFilled($gKey))
                                        <b style="color:var(--text-primary);">Tersedia</b> ({{ $prevReading->tanggal->translatedFormat('d M') }})
                                    @else
                                        <i>Belum ada pencatatan sebelumnya</i>
                                    @endif
                                </div>
                            @endif
                        </div>

                        {{-- Foto Preview Jika Sudah Ada Foto Tersimpan --}}
                        @if($groupFoto)
                            <div style="display:flex; align-items:center; gap:10px; margin-bottom:14px; background:var(--bg-card-subtle); border:1px solid var(--border); padding:8px 10px; border-radius:8px;">
                                <img src="{{ asset('storage/'.$groupFoto) }}" alt="Foto Panel" data-lightbox="{{ asset('storage/'.$groupFoto) }}" data-caption="{{ $group['title'] }} — {{ $todayStr }}" style="width:48px; height:48px; object-fit:cover; border-radius:6px; border:1px solid var(--border); cursor:pointer;">
                                <div style="font-size:0.76rem; color:var(--text-secondary);">
                                    <strong style="color:var(--text-primary); display:block;">Foto Panel Tersimpan</strong>
                                    Klik gambar untuk memperbesar
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Form Input Mandiri Khusus Sub-Menu Ini --}}
                    <div>
                        <form method="POST" action="{{ route('public-area.lvsb.store') }}" enctype="multipart/form-data" class="form-offline-pa-lvsb" data-offline-type="public_area_lvsb" data-meter-label="{{ $group['title'] }}" data-target-redirect="{{ route('public-area.lvsb.index') }}" style="margin-top:8px;">
                            @csrf
                            <input type="hidden" name="tanggal" value="{{ \Carbon\Carbon::today()->toDateString() }}">
                            <input type="hidden" name="group_key" value="{{ $gKey }}">

                            {{-- Fields List --}}
                            @if ($group['has_subgroups'])
                                <div style="display:flex; flex-direction:column; gap:12px; margin-bottom:12px;">
                                    @foreach ($group['subgroups'] as $sKey => $sub)
                                        <div style="background:var(--bg-card-subtle); border:1px solid var(--border); border-radius:8px; padding:10px 12px;">
                                            <div style="font-weight:700; font-size:0.84rem; color:var(--text-primary); margin-bottom:8px; display:flex; align-items:center; gap:6px;">
                                                <span style="color:{{ $group['color'] }};">▸</span>
                                                <span>{{ $sub['label'] }}</span>
                                            </div>

                                            <div style="overflow-x:auto; -webkit-overflow-scrolling:touch; padding-bottom:4px;">
                                                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(130px, 1fr)); gap:8px; min-width:280px;">
                                                    @foreach ($sub['fields'] as $fKey => $fMeta)
                                                        @php
                                                            $val = old("readings.{$gKey}.{$sKey}.{$fKey}", $todayReading?->val($gKey, $sKey, $fKey));
                                                            $prevVal = $prevReading?->val($gKey, $sKey, $fKey);
                                                        @endphp
                                                        <div>
                                                            <label style="font-size:0.72rem; font-weight:600; color:var(--text-muted); display:block; margin-bottom:2px;">
                                                                {{ $fMeta['label'] }}
                                                            </label>
                                                            <div class="input-unit" style="position:relative;">
                                                                <input type="number" step="{{ $fMeta['step'] }}" inputmode="decimal" name="readings[{{ $gKey }}][{{ $sKey }}][{{ $fKey }}]" class="input" value="{{ $val }}" placeholder="{{ $fMeta['placeholder'] }}" style="width:100%; padding-right:52px; font-size:0.84rem; font-weight:600;">
                                                                <span style="position:absolute; right:8px; top:50%; transform:translateY(-50%); font-size:0.72rem; color:var(--text-muted); pointer-events:none;">
                                                                    {{ $fMeta['unit'] }}
                                                                </span>
                                                            </div>
                                                            @if($prevVal !== null)
                                                                <div style="font-size:0.67rem; color:var(--text-muted); margin-top:2px;">
                                                                    Prev: {{ $n($prevVal, $fKey === 'energy' ? 8 : ($fMeta['step'] === '0.1' ? 1 : 2)) }}
                                                                </div>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div style="background:var(--bg-card-subtle); border:1px solid var(--border); border-radius:8px; padding:12px; margin-bottom:12px;">
                                    <div style="overflow-x:auto; -webkit-overflow-scrolling:touch; padding-bottom:4px;">
                                        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(130px, 1fr)); gap:10px; min-width:280px;">
                                            @foreach ($group['fields'] as $fKey => $fMeta)
                                                @php
                                                    $val = old("readings.{$gKey}.{$fKey}", $todayReading?->val($gKey, $fKey));
                                                    $prevVal = $prevReading?->val($gKey, $fKey);
                                                @endphp
                                                <div>
                                                    <label style="font-size:0.74rem; font-weight:600; color:var(--text-muted); display:block; margin-bottom:3px;">
                                                        {{ $fMeta['label'] }}
                                                    </label>
                                                    <div class="input-unit" style="position:relative;">
                                                        <input type="number" step="{{ $fMeta['step'] }}" inputmode="decimal" name="readings[{{ $gKey }}][{{ $fKey }}]" class="input" value="{{ $val }}" placeholder="{{ $fMeta['placeholder'] }}" style="width:100%; padding-right:55px; font-size:0.86rem; font-weight:600;">
                                                        <span style="position:absolute; right:8px; top:50%; transform:translateY(-50%); font-size:0.74rem; color:var(--text-muted); pointer-events:none;">
                                                            {{ $fMeta['unit'] }}
                                                        </span>
                                                    </div>
                                                    @if($prevVal !== null)
                                                        <div style="font-size:0.68rem; color:var(--text-muted); margin-top:2px;">
                                                            Sebelumnya: {{ $n($prevVal, $fKey === 'energy' ? 8 : ($fMeta['step'] === '0.1' ? 1 : 2)) }}
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- Lampiran Foto Mandiri Sub-Menu --}}
                            <div class="photo-upload-group" style="margin-bottom:10px;">
                                <label style="font-size:0.74rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; display:block; margin-bottom:4px;">
                                    Lampiran Foto Panel {{ $group['badge'] }} <span style="font-size:0.68rem; font-weight:normal; color:var(--text-muted);">{{ $groupFoto ? '(Ganti foto opsional)' : '(Opsional)' }}</span>
                                </label>
                                <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px;">
                                    <label class="btn-pick-gallery" style="display:flex; align-items:center; justify-content:center; gap:6px; background:var(--bg-card-subtle); border:1px solid var(--border); padding:7px 10px; border-radius:8px; cursor:pointer; font-size:0.76rem; color:var(--text-secondary); transition:all 0.2s;">
                                        <svg viewBox="0 0 20 20" fill="currentColor" style="width:15px;height:15px;color:var(--info);"><path fill-rule="evenodd" d="M1 5.25A2.25 2.25 0 0 1 3.25 3h13.5A2.25 2.25 0 0 1 19 5.25v9.5A2.25 2.25 0 0 1 16.75 17H3.25A2.25 2.25 0 0 1 1 14.75v-9.5Zm1.5 5.81v3.69c0 .414.336.75.75.75h13.5a.75.75 0 0 0 .75-.75v-2.69l-2.22-2.219a.75.75 0 0 0-1.06 0l-1.91 1.909-4.22-4.219a.75.75 0 0 0-1.06 0L2.5 11.06Zm10.25-3.81a1.25 1.25 0 1 1-2.5 0 1.25 1.25 0 0 1 2.5 0Z" clip-rule="evenodd"/></svg>
                                        <span>🖼️ Galeri</span>
                                        <input type="file" name="foto" accept="image/*" class="input-file-main" style="display:none;">
                                    </label>
                                    <label class="btn-pick-camera" style="display:flex; align-items:center; justify-content:center; gap:6px; background:var(--bg-card-subtle); border:1px solid var(--border); padding:7px 10px; border-radius:8px; cursor:pointer; font-size:0.76rem; color:var(--text-secondary); transition:all 0.2s;">
                                        <svg viewBox="0 0 20 20" fill="currentColor" style="width:15px;height:15px;color:var(--warning);"><path fill-rule="evenodd" d="M1 8a2 2 0 0 1 2-2h.93a2 2 0 0 0 1.664-.89l.812-1.22A2 2 0 0 1 8.07 3h3.86a2 2 0 0 1 1.664.89l.812 1.22A2 2 0 0 0 16.07 6H17a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8Zm13.5 3a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM10 14a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" clip-rule="evenodd"/></svg>
                                        <span>📷 Kamera</span>
                                        <input type="file" accept="image/*" capture="environment" class="input-file-camera" style="display:none;">
                                    </label>
                                </div>
                                <div class="photo-preview-wrap" style="display:none; margin-top:8px; align-items:center; gap:10px; background:var(--bg-card-subtle); padding:6px 10px; border-radius:8px; border:1px solid var(--border);">
                                    <img class="img-preview" src="" alt="Thumb" style="width:36px; height:36px; object-fit:cover; border-radius:6px; border:1px solid var(--border);">
                                    <div style="flex:1; overflow:hidden;">
                                        <div class="photo-preview-name" style="font-size:0.74rem; color:var(--text-primary); font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"></div>
                                        <div style="font-size:0.68rem; color:var(--success);">✓ Foto siap diunggah</div>
                                    </div>
                                    <button type="button" class="btn-clear-photo" style="background:none; border:none; color:var(--danger); font-size:1rem; cursor:pointer;" title="Batalkan foto">✕</button>
                                </div>
                            </div>

                            {{-- Catatan Opsional --}}
                            <div style="margin-bottom:10px;">
                                <input type="text" name="catatan" class="input" placeholder="Catatan kondisi {{ $group['badge'] }} (opsional)..." value="{{ old('catatan', $todayReading?->catatan) }}" style="font-size:0.78rem; padding:6px 10px; width:100%;">
                            </div>

                            {{-- TOMBOL SIMPAN KHUSUS SUB-MENU INI --}}
                            <button type="submit" class="btn btn-sm btn-primary" style="width:100%; justify-content:center; background:linear-gradient(135deg, {{ $group['color'] }}, #d97706); border:none; color:#090d15; font-weight:700; padding:10px 16px; font-size:0.86rem; box-shadow:var(--shadow-sm); display:flex; align-items:center; gap:8px;">
                                <svg viewBox="0 0 20 20" fill="currentColor" style="width:16px;height:16px;"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd"/></svg>
                                <span>Simpan {{ $group['title'] }}</span>
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- RANGKUMAN STATISTIK LVSB (TABEL & GRAFIK)                              --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    <div style="background:var(--bg-card); border:1px solid var(--border); border-radius:12px; padding:20px; margin-bottom:28px; box-shadow:var(--shadow-sm);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; flex-wrap:wrap; gap:10px;">
            <div>
                <span style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.06em; color:#fbbf24; font-weight:700;">Rangkuman & Visualisasi Parameter LVSB</span>
                <h3 style="font-size:1.15rem; color:var(--text-primary); margin:2px 0 0; font-weight:700;">
                    Rangkuman Panel LVSB — Interval {{ $startDate->translatedFormat('d M Y') }} s/d {{ $endDate->translatedFormat('d M Y') }}
                </h3>
            </div>
            <span class="badge" style="background:rgba(251,191,36,0.12); color:#fbbf24; border:1px solid rgba(251,191,36,0.3); font-size:0.76rem;">
                {{ $intervalDays }} Hari Terpilih
            </span>
        </div>

        {{-- Metric KPI Cards --}}
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:12px; margin-bottom:20px;">
            <div class="metric-kpi-card metric-kpi-card--warning">
                <span style="font-size:0.72rem; color:var(--text-muted); text-transform:uppercase; font-weight:700;">Avg Voltage Trafo</span>
                <div style="font-size:1.5rem; font-weight:700; color:var(--warning); font-family:var(--font-serif); margin:4px 0;">
                    {{ $n($summary['avg_voltage'], 1) }} <small style="font-size:0.78rem; color:var(--text-muted); font-family:var(--font-sans);">Volt</small>
                </div>
                <span style="font-size:0.74rem; color:var(--text-secondary);">Tegangan 3-P Trafo</span>
            </div>

            <div class="metric-kpi-card metric-kpi-card--danger">
                <span style="font-size:0.72rem; color:var(--text-muted); text-transform:uppercase; font-weight:700;">Beban Puncak (Max kW)</span>
                <div style="font-size:1.5rem; font-weight:700; color:var(--danger); font-family:var(--font-serif); margin:4px 0;">
                    {{ $n($summary['max_power'], 2) }} <small style="font-size:0.78rem; color:var(--text-muted); font-family:var(--font-sans);">kW</small>
                </div>
                <span style="font-size:0.74rem; color:var(--text-secondary);">Daya aktif tertinggi</span>
            </div>

            <div class="metric-kpi-card metric-kpi-card--info">
                <span style="font-size:0.72rem; color:var(--text-muted); text-transform:uppercase; font-weight:700;">Rata-rata Daya (Avg kW)</span>
                <div style="font-size:1.5rem; font-weight:700; color:var(--info); font-family:var(--font-serif); margin:4px 0;">
                    {{ $n($summary['avg_power'], 2) }} <small style="font-size:0.78rem; color:var(--text-muted); font-family:var(--font-sans);">kW</small>
                </div>
                <span style="font-size:0.74rem; color:var(--text-secondary);">Konsumsi beban rata-rata</span>
            </div>

            <div class="metric-kpi-card metric-kpi-card--purple">
                <span style="font-size:0.72rem; color:var(--text-muted); text-transform:uppercase; font-weight:700;">Arus Puncak (Max A)</span>
                <div style="font-size:1.5rem; font-weight:700; color:var(--purple); font-family:var(--font-serif); margin:4px 0;">
                    {{ $n($summary['max_current'], 2) }} <small style="font-size:0.78rem; color:var(--text-muted); font-family:var(--font-sans);">Ampere</small>
                </div>
                <span style="font-size:0.74rem; color:var(--text-secondary);">Arus incoming trafo</span>
            </div>

            <div class="metric-kpi-card metric-kpi-card--success">
                <span style="font-size:0.72rem; color:var(--text-muted); text-transform:uppercase; font-weight:700;">Avg PF Kapasitor</span>
                <div style="font-size:1.5rem; font-weight:700; color:var(--success); font-family:var(--font-serif); margin:4px 0;">
                    {{ $n($summary['avg_pf'], 2) }}
                </div>
                <span style="font-size:0.74rem; color:var(--text-secondary);">Kompensasi cos φ</span>
            </div>

            <div class="metric-kpi-card">
                <span style="font-size:0.72rem; color:var(--text-muted); text-transform:uppercase; font-weight:700;">Total Hari Pencatatan</span>
                <div style="font-size:1.5rem; font-weight:700; color:var(--text-primary); font-family:var(--font-serif); margin:4px 0;">
                    {{ $summary['count'] }} <small style="font-size:0.78rem; color:var(--text-muted); font-family:var(--font-sans);">hari</small>
                </div>
                <span style="font-size:0.74rem; color:var(--text-secondary);">Dalam interval terpilih</span>
            </div>
        </div>

        {{-- TABEL RANGKUMAN PARAMETER LVSB --}}
        <div style="margin-bottom:20px;">
            <h4 style="font-size:0.92rem; color:var(--text-primary); margin:0 0 10px; font-weight:700; display:flex; align-items:center; gap:6px;">
                <span>📊</span> Tabel Rangkuman Statistik Panel LVSB
            </h4>
            <div class="table-wrap">
                <table class="log-table" style="font-size:0.84rem;">
                    <thead>
                        <tr>
                            <th class="l">Parameter Kunci</th>
                            <th class="c">Satuan</th>
                            <th class="r">Rata-rata (Avg)</th>
                            <th class="r">Nilai Tertinggi (Max)</th>
                            <th class="l">Keterangan / Posisi Panel</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="l" style="font-weight:700; color:#fbbf24;">Incoming Trafo Voltage 3-P</td>
                            <td class="c">Volt</td>
                            <td class="r" style="font-weight:700; font-family:var(--font-serif); color:#fbbf24;">{{ $n($summary['avg_voltage'], 1) }} V</td>
                            <td class="r">—</td>
                            <td class="l" style="color:var(--text-secondary);">Tegangan incoming trafo Apart A/B</td>
                        </tr>
                        <tr>
                            <td class="l" style="font-weight:700; color:#38bdf8;">Incoming Trafo Power (kW Total)</td>
                            <td class="c">kW</td>
                            <td class="r" style="font-weight:700; font-family:var(--font-serif); color:#38bdf8;">{{ $n($summary['avg_power'], 2) }} kW</td>
                            <td class="r" style="font-weight:700; color:#f87171;">{{ $n($summary['max_power'], 2) }} kW</td>
                            <td class="l" style="color:var(--text-secondary);">Beban aktif trafo gedung</td>
                        </tr>
                        <tr>
                            <td class="l" style="font-weight:700; color:#c084fc;">Incoming Trafo Current (Arus)</td>
                            <td class="c">Ampere</td>
                            <td class="r">—</td>
                            <td class="r" style="font-weight:700; font-family:var(--font-serif); color:#c084fc;">{{ $n($summary['max_current'], 2) }} A</td>
                            <td class="l" style="color:var(--text-secondary);">Arus incoming operasional</td>
                        </tr>
                        <tr>
                            <td class="l" style="font-weight:700; color:#34d399;">Incoming Capacitor Bank Power Factor</td>
                            <td class="c">Cos φ</td>
                            <td class="r" style="font-weight:700; font-family:var(--font-serif); color:#34d399;">{{ $n($summary['avg_pf'], 2) }}</td>
                            <td class="r">—</td>
                            <td class="l" style="color:var(--text-secondary);">Faktor daya kapasitor bank</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- GRAFIK VISUALISASI CHART.JS --}}
        <div>
            <h4 style="font-size:0.92rem; color:var(--text-primary); margin:0 0 10px; font-weight:700; display:flex; align-items:center; gap:6px;">
                <span>📈</span> Grafik Tren Beban Daya (kW), Tegangan (V), & PF Kapasitor
            </h4>
            <div style="position:relative; min-height:260px; height:260px; background:var(--bg-card); border:1px solid var(--border); border-radius:10px; padding:12px; box-shadow:var(--shadow-sm);">
                @if(empty($chartData['labels']))
                    <div style="height:100%; display:flex; flex-direction:column; align-items:center; justify-content:center; color:var(--text-muted); gap:8px;">
                        <span style="font-size:1.8rem;">📊</span>
                        <span style="font-size:0.85rem;">Belum ada data pencatatan LVSB pada interval ini.</span>
                    </div>
                @else
                    <canvas id="chartLvsbInterval"></canvas>
                @endif
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- TABEL RIWAYAT PENCATATAN INTERVAL LVSB                                 --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    <div style="margin-top:20px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; flex-wrap:wrap; gap:8px;">
            <div>
                <h3 style="font-size:1.1rem; font-weight:700; color:var(--text-primary); margin:0;">
                    Riwayat Pencatatan LVSB — Interval {{ $startDate->translatedFormat('d M Y') }} s/d {{ $endDate->translatedFormat('d M Y') }}
                </h3>
                <span style="font-size:0.78rem; color:var(--text-muted);">{{ $rows->count() }} hari logsheet tercatat</span>
            </div>
        </div>

        {{-- ── Panel Aksi Masal: Verifikasi Masal & Hapus Masal (Khusus Super Admin, Maks 1 Bulan) ── --}}
        @if (auth()->user()->isSuperAdmin() && $rows->count())
            <div class="bulk-action-bar" style="margin-bottom:14px; background:linear-gradient(135deg, rgba(245,158,11,0.12), rgba(15,22,35,0.7)); border:1px solid rgba(245,158,11,0.3); border-radius:10px; padding:12px 16px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
                <div style="display:flex; align-items:center; gap:14px; flex-wrap:wrap;">
                    <label style="display:inline-flex; align-items:center; gap:8px; cursor:pointer; font-weight:700; font-size:0.88rem; color:#fbbf24; margin:0; user-select:none;">
                        <input type="checkbox" id="bulk-master-lvsb" style="width:18px; height:18px; cursor:pointer; accent-color:#f59e0b;">
                        <span>Pilih Semua Data ({{ $rows->count() }} Data)</span>
                    </label>
                    <div style="display:flex; align-items:center; gap:8px;">
                        <button type="button" class="btn btn-sm" id="btn-select-unverified-lvsb" style="font-size:0.75rem; padding:3px 8px; background:rgba(234,179,8,0.15); color:#facc15; border:1px solid rgba(234,179,8,0.3);" title="Pilih hanya data yang belum diverifikasi">
                            ⚡ Pilih Belum Verifikasi
                        </button>
                        <button type="button" class="btn btn-sm" id="btn-clear-selection-lvsb" style="font-size:0.75rem; padding:3px 8px; background:rgba(255,255,255,0.06); color:var(--text-muted); display:none;" title="Batalkan semua pilihan">
                            ✕ Batal Pilih
                        </button>
                    </div>
                    <span id="bulk-selected-badge-lvsb" class="badge" style="background:rgba(245,158,11,0.2); color:#fbbf24; border:1px solid rgba(245,158,11,0.4); font-size:0.78rem; padding:4px 10px; border-radius:6px; font-weight:700; display:none;">
                        <span id="bulk-selected-count-lvsb">0</span> data dipilih
                    </span>
                </div>

                <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                    <button type="button" id="btn-trigger-bulk-verify-lvsb" class="btn btn-sm btn-success" disabled style="display:inline-flex; align-items:center; gap:6px; opacity:0.5; cursor:not-allowed; transition:all 0.2s ease;">
                        <svg viewBox="0 0 20 20" fill="currentColor" style="width:16px; height:16px;"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd"/></svg>
                        <span id="btn-text-bulk-verify-lvsb">Verifikasi Masal</span>
                    </button>

                    <button type="button" id="btn-trigger-bulk-delete-lvsb" class="btn btn-sm btn-danger" disabled style="display:inline-flex; align-items:center; gap:6px; opacity:0.5; cursor:not-allowed; transition:all 0.2s ease;">
                        <svg viewBox="0 0 20 20" fill="currentColor" style="width:16px; height:16px;"><path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 0 0 6 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 1 0 .23 1.482l.149-.022.841 10.518A2.75 2.75 0 0 0 7.596 19h4.807a2.75 2.75 0 0 0 2.742-2.53l.841-10.52.149.023a.75.75 0 0 0 .23-1.482A41.03 41.03 0 0 0 14 4.193V3.75A2.75 2.75 0 0 0 11.25 1h-2.5ZM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4ZM8.58 7.72a.75.75 0 0 1 .75.748l.5 6a.75.75 0 0 1-1.498.124l-.5-6a.75.75 0 0 1 .748-.872Zm3.588.748a.75.75 0 0 0-1.498-.124l-.5 6a.75.75 0 0 0 1.498.124l.5-6Z" clip-rule="evenodd"/></svg>
                        <span id="btn-text-bulk-delete-lvsb">Hapus Masal</span>
                    </button>
                </div>
            </div>

            <form id="bulk-action-form-lvsb" method="POST" action="" style="display:none;">
                @csrf
                <div id="bulk-inputs-lvsb"></div>
            </form>
        @endif

        <div class="table-wrap">
            <table class="log-table">
                <thead>
                    <tr>
                        @if (auth()->user()->isSuperAdmin())
                            <th class="c" style="width:42px; vertical-align:middle;">
                                <input type="checkbox" id="bulk-table-all-lvsb" title="Pilih Semua" style="width:17px; height:17px; cursor:pointer; accent-color:#f59e0b;">
                            </th>
                        @endif
                        <th class="l" style="width:120px;">Tanggal</th>
                        <th class="r">Incoming Voltage (V)</th>
                        <th class="r">Incoming Current (A)</th>
                        <th class="r">Incoming Power (kW)</th>
                        <th class="r">Incoming Energy (GWh)</th>
                        <th class="r">Incoming PF</th>
                        <th class="r">Capacitor PF</th>
                        <th class="l">Petugas</th>
                        <th class="c">Status</th>
                        <th class="c" style="width:120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($rows as $r)
                    @php
                        $inVolt = $r->val('incoming_trafo', 'voltage_3p');
                        $inAmp  = $r->val('incoming_trafo', 'current');
                        $inKw   = $r->val('incoming_trafo', 'power');
                        $inGwh  = $r->val('incoming_trafo', 'energy');
                        $inPf   = $r->val('incoming_trafo', 'power_factor');
                        $capPf  = $r->val('incoming_capacitor', 'power_factor');
                    @endphp
                    <tr>
                        @if (auth()->user()->isSuperAdmin())
                            <td class="c" style="vertical-align:middle;">
                                <input type="checkbox" name="bulk_ids[]" value="{{ $r->id }}" class="bulk-item-lvsb" data-verified="{{ $r->isVerified() ? '1' : '0' }}" style="width:17px; height:17px; cursor:pointer; accent-color:#f59e0b;">
                            </td>
                        @endif
                        <td class="l" style="font-weight:600; white-space:nowrap;">
                            {{ $r->tanggal->format('d/m/Y') }}
                            <div style="font-size:0.7rem; color:var(--text-muted); font-weight:normal;">{{ $r->tanggal->translatedFormat('l') }}</div>
                        </td>
                        <td class="r" style="font-weight:600; color:#fbbf24;">{{ $n($inVolt, 1) }}</td>
                        <td class="r">{{ $n($inAmp) }}</td>
                        <td class="r" style="font-weight:600; color:#38bdf8;">{{ $n($inKw) }}</td>
                        <td class="r" style="color:#a78bfa;">{{ $n($inGwh, 8) }}</td>
                        <td class="r">{{ $n($inPf) }}</td>
                        <td class="r" style="font-weight:600; color:#34d399;">{{ $n($capPf) }}</td>
                        <td class="l">
                            <span style="font-size:0.8rem; color:var(--text-secondary);">{{ $r->user?->name ?? 'User Terhapus' }}</span>
                        </td>
                        <td class="c">
                            @if ($r->isVerified())
                                <span class="badge badge-ok" title="{{ $r->verified_at ? 'Diverifikasi oleh ' . ($r->verifier?->name ?? 'Admin') . ' pada ' . $r->verified_at->format('d/m/Y H:i') : 'Otomatis Terverifikasi (Input Admin / Backdate)' }}">✓ Terverifikasi</span>
                            @else
                                <span class="badge badge-wait">Menunggu</span>
                            @endif
                        </td>
                        <td class="c">
                            <div style="display:inline-flex; gap:6px; align-items:center;">
                                <a href="{{ route('public-area.lvsb.show', $r) }}" class="btn btn-sm" style="padding:4px 8px; font-size:0.75rem;" title="Lihat detail pembacaan">
                                    Detail
                                </a>
                                @if (auth()->user()->isSuperAdmin())
                                    <form method="POST" action="{{ route('public-area.lvsb.destroy', $r) }}" onsubmit="return confirm('Hapus data logsheet LVSB tanggal {{ $r->tanggal->format('d/m/Y') }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" style="padding:4px 8px; font-size:0.75rem;" title="Hapus logsheet">
                                            ✕
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ auth()->user()->isSuperAdmin() ? 11 : 10 }}" style="text-align:center; padding:30px; color:var(--text-muted);">
                            Belum ada catatan logsheet LVSB pada interval {{ $startDate->translatedFormat('d M Y') }} s/d {{ $endDate->translatedFormat('d M Y') }}.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    // Controller Slide & Tabs Sub-Menu LVSB
    (function() {
        const tabPills       = document.querySelectorAll('.lvsb-tab-pill');
        const cards          = document.querySelectorAll('.lvsb-subcard');
        const container      = document.getElementById('lvsbCardsContainer');
        const btnModeSlide   = document.getElementById('btnModeSlide');
        const btnModeGrid    = document.getElementById('btnModeGrid');
        const slideNavHeader = document.getElementById('lvsbSlideNavHeader');
        const btnSlidePrev   = document.getElementById('btnSlidePrev');
        const btnSlideNext   = document.getElementById('btnSlideNext');
        const slideIndicator = document.getElementById('slideIndicator');
        const slideTitle     = document.getElementById('slideCurrentTitle');

        if (!cards.length) return;

        // Default mode: HP/mobile (<850px) otomatis mode slide fokus; desktop mode grid
        let currentMode = window.innerWidth < 850 ? 'slide' : 'grid';
        let currentIndex = 1;
        const totalCards = cards.length;

        function updateView() {
            if (currentMode === 'slide') {
                btnModeSlide?.classList.add('active');
                btnModeGrid?.classList.remove('active');
                if (slideNavHeader) slideNavHeader.style.display = 'flex';
                if (container) {
                    container.style.gridTemplateColumns = '1fr';
                }

                cards.forEach(card => {
                    const idx = parseInt(card.dataset.index, 10);
                    if (idx === currentIndex) {
                        card.style.display = 'flex';
                        card.classList.add('lvsb-slide-active');
                        if (slideIndicator) slideIndicator.textContent = `Sub-Menu ${idx} dari ${totalCards}`;
                        if (slideTitle) {
                            slideTitle.textContent = card.dataset.title;
                            slideTitle.style.color = card.dataset.color || 'var(--accent)';
                        }
                    } else {
                        card.style.display = 'none';
                        card.classList.remove('lvsb-slide-active');
                    }
                });

                // Sinkronkan tab pill aktif
                const activeCard = Array.from(cards).find(c => parseInt(c.dataset.index, 10) === currentIndex);
                tabPills.forEach(pill => {
                    if (activeCard && pill.dataset.target === activeCard.dataset.group) {
                        pill.classList.add('active');
                        pill.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
                    } else {
                        pill.classList.remove('active');
                    }
                });
            } else {
                // Mode Grid: Tampilkan semua kartu berjejer responsif seperti water meter
                btnModeSlide?.classList.remove('active');
                btnModeGrid?.classList.add('active');
                if (slideNavHeader) slideNavHeader.style.display = 'none';
                if (container) {
                    container.style.gridTemplateColumns = 'repeat(auto-fit, minmax(340px, 1fr))';
                }

                cards.forEach(card => {
                    card.style.display = 'flex';
                    card.classList.remove('lvsb-slide-active');
                });

                // Tab pill 'all' aktif
                tabPills.forEach(pill => {
                    if (pill.dataset.target === 'all') {
                        pill.classList.add('active');
                        pill.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
                    } else {
                        pill.classList.remove('active');
                    }
                });
            }
        }

        btnModeSlide?.addEventListener('click', () => {
            currentMode = 'slide';
            updateView();
        });

        btnModeGrid?.addEventListener('click', () => {
            currentMode = 'grid';
            updateView();
        });

        tabPills.forEach(pill => {
            pill.addEventListener('click', function() {
                const target = this.dataset.target;
                if (target === 'all') {
                    currentMode = 'grid';
                    updateView();
                } else {
                    const matchedCard = Array.from(cards).find(c => c.dataset.group === target);
                    if (matchedCard) {
                        const targetIdx = parseInt(matchedCard.dataset.index, 10);
                        if (currentMode === 'slide') {
                            currentIndex = targetIdx;
                            updateView();
                        } else {
                            tabPills.forEach(p => p.classList.remove('active'));
                            this.classList.add('active');
                            matchedCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            matchedCard.style.outline = `3px solid ${matchedCard.dataset.color || 'var(--accent)'}`;
                            setTimeout(() => { matchedCard.style.outline = 'none'; }, 1600);
                        }
                    }
                }
            });
        });

        btnSlidePrev?.addEventListener('click', () => {
            currentIndex = currentIndex > 1 ? currentIndex - 1 : totalCards;
            updateView();
        });

        btnSlideNext?.addEventListener('click', () => {
            currentIndex = currentIndex < totalCards ? currentIndex + 1 : 1;
            updateView();
        });

        // Touch swipe horizontal pada container kartu untuk swipe jari di APK HP
        let touchStartX = 0;
        let touchEndX = 0;
        let touchStartY = 0;
        let touchEndY = 0;

        container?.addEventListener('touchstart', e => {
            touchStartX = e.changedTouches[0].screenX;
            touchStartY = e.changedTouches[0].screenY;
        }, { passive: true });

        container?.addEventListener('touchend', e => {
            touchEndX = e.changedTouches[0].screenX;
            touchEndY = e.changedTouches[0].screenY;
            const diffX = touchEndX - touchStartX;
            const diffY = touchEndY - touchStartY;
            if (currentMode === 'slide' && Math.abs(diffX) > Math.abs(diffY) && Math.abs(diffX) > 45) {
                if (diffX < 0) {
                    btnSlideNext?.click();
                } else {
                    btnSlidePrev?.click();
                }
            }
        }, { passive: true });

        updateView();
    })();

    // Handler Upload Foto (Pilih dari Galeri & Buka Kamera)
    document.querySelectorAll('.photo-upload-group').forEach(group => {
        const mainInput   = group.querySelector('.input-file-main');
        const cameraInput = group.querySelector('.input-file-camera');
        const previewWrap = group.querySelector('.photo-preview-wrap');
        const imgPreview  = group.querySelector('.img-preview');
        const namePreview = group.querySelector('.photo-preview-name');
        const clearBtn    = group.querySelector('.btn-clear-photo');

        function handleFile(file, source) {
            if (!file) return;
            if (imgPreview) {
                imgPreview.src = URL.createObjectURL(file);
            }
            if (namePreview) {
                const srcLabel = source === 'camera' ? '📷 Kamera' : '🖼️ Galeri';
                namePreview.textContent = `${srcLabel}: ${file.name}`;
            }
            if (previewWrap) {
                previewWrap.style.display = 'flex';
            }
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
                if (this.files && this.files[0]) {
                    handleFile(this.files[0], 'gallery');
                }
            });
        }

        if (cameraInput) {
            cameraInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    handleFile(this.files[0], 'camera');
                }
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

    // Inisialisasi Grafik Visualisasi Chart.js Tren Interval LVSB
    document.addEventListener('DOMContentLoaded', function() {
        const chartData = @json($chartData);
        const ctx = document.getElementById('chartLvsbInterval');
        if (!ctx || !chartData || !chartData.labels || chartData.labels.length === 0) return;

        function getThemeColors() {
            const isLight = document.documentElement.getAttribute('data-theme') === 'light';
            return {
                textColor: isLight ? '#475569' : 'rgba(255,255,255,0.75)',
                tickColor: isLight ? '#64748b' : 'rgba(255,255,255,0.6)',
                gridColor: isLight ? 'rgba(0,0,0,0.06)' : 'rgba(255,255,255,0.05)',
            };
        }

        let th = getThemeColors();

        const chartLvsb = new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.labels,
                datasets: [
                    {
                        label: 'Incoming Power (kW)',
                        data: chartData.power,
                        borderColor: '#38bdf8',
                        backgroundColor: 'rgba(56,189,248,0.12)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.25,
                        yAxisID: 'y',
                        pointRadius: chartData.labels.length > 35 ? 0 : 3,
                    },
                    {
                        label: 'Incoming Voltage (V)',
                        data: chartData.voltage,
                        borderColor: '#fbbf24',
                        borderWidth: 2,
                        fill: false,
                        tension: 0.25,
                        yAxisID: 'yVolt',
                        pointRadius: chartData.labels.length > 35 ? 0 : 2,
                    },
                    {
                        label: 'Capacitor PF',
                        data: chartData.pf,
                        borderColor: '#34d399',
                        borderWidth: 2,
                        fill: false,
                        tension: 0.2,
                        yAxisID: 'yPF',
                        pointRadius: chartData.labels.length > 35 ? 0 : 2,
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
                    y: {
                        type: 'linear',
                        position: 'left',
                        title: { display: true, text: 'Power (kW)', color: '#38bdf8', font: { size: 10 } },
                        grid: { color: th.gridColor },
                        ticks: { color: th.tickColor, font: { size: 10 } }
                    },
                    yVolt: {
                        type: 'linear',
                        position: 'right',
                        title: { display: true, text: 'Voltage (V)', color: '#fbbf24', font: { size: 10 } },
                        grid: { drawOnChartArea: false },
                        ticks: { color: th.tickColor, font: { size: 10 } }
                    },
                    yPF: {
                        type: 'linear',
                        position: 'right',
                        min: 0.7,
                        max: 1.0,
                        title: { display: true, text: 'PF', color: '#34d399', font: { size: 10 } },
                        grid: { drawOnChartArea: false },
                        ticks: { color: th.tickColor, font: { size: 10 } }
                    }
                }
            }
        });

        window.addEventListener('kpr-theme-changed', function() {
            const next = getThemeColors();
            chartLvsb.options.plugins.legend.labels.color = next.textColor;
            chartLvsb.options.scales.x.grid.color = next.gridColor;
            chartLvsb.options.scales.x.ticks.color = next.tickColor;
            chartLvsb.options.scales.y.grid.color = next.gridColor;
            chartLvsb.options.scales.y.ticks.color = next.tickColor;
            chartLvsb.options.scales.yVolt.ticks.color = next.tickColor;
            chartLvsb.options.scales.yPF.ticks.color = next.tickColor;
            chartLvsb.update();
        });
    });

    // ── Fitur Aksi Masal: Verifikasi Masal & Hapus Masal LVSB (Khusus Super Admin) ──
    @if (auth()->user()->isSuperAdmin())
    (function() {
        const bulkMaster = document.getElementById('bulk-master-lvsb');
        const bulkTableAll = document.getElementById('bulk-table-all-lvsb');
        const btnSelectUnverified = document.getElementById('btn-select-unverified-lvsb');
        const btnClearSelection = document.getElementById('btn-clear-selection-lvsb');
        const badge = document.getElementById('bulk-selected-badge-lvsb');
        const badgeCount = document.getElementById('bulk-selected-count-lvsb');
        const btnVerify = document.getElementById('btn-trigger-bulk-verify-lvsb');
        const btnDelete = document.getElementById('btn-trigger-bulk-delete-lvsb');
        const textVerify = document.getElementById('btn-text-bulk-verify-lvsb');
        const textDelete = document.getElementById('btn-text-bulk-delete-lvsb');
        const bulkForm = document.getElementById('bulk-action-form-lvsb');
        const bulkInputs = document.getElementById('bulk-inputs-lvsb');

        function getSelected() {
            return Array.from(document.querySelectorAll('.bulk-item-lvsb:checked')).map(cb => cb.value);
        }

        function updateToolbar() {
            const selected = getSelected();
            const count = selected.length;
            const allItems = Array.from(document.querySelectorAll('.bulk-item-lvsb')).map(cb => cb.value);
            const total = allItems.length;

            if (count > 0) {
                if (badge) badge.style.display = 'inline-block';
                if (badgeCount) badgeCount.textContent = count;
                if (btnClearSelection) btnClearSelection.style.display = 'inline-flex';

                if (btnVerify) {
                    btnVerify.disabled = false;
                    btnVerify.style.opacity = '1';
                    btnVerify.style.cursor = 'pointer';
                    if (textVerify) textVerify.textContent = `Verifikasi Masal (${count})`;
                }
                if (btnDelete) {
                    btnDelete.disabled = false;
                    btnDelete.style.opacity = '1';
                    btnDelete.style.cursor = 'pointer';
                    if (textDelete) textDelete.textContent = `Hapus Masal (${count})`;
                }
            } else {
                if (badge) badge.style.display = 'none';
                if (btnClearSelection) btnClearSelection.style.display = 'none';

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
            document.querySelectorAll('.bulk-item-lvsb').forEach(cb => { cb.checked = checked; });
            updateToolbar();
        }

        if (bulkMaster) bulkMaster.addEventListener('change', function() { toggleAll(this.checked); });
        if (bulkTableAll) bulkTableAll.addEventListener('change', function() { toggleAll(this.checked); });

        document.querySelectorAll('.bulk-item-lvsb').forEach(cb => {
            cb.addEventListener('change', updateToolbar);
        });

        if (btnSelectUnverified) {
            btnSelectUnverified.addEventListener('click', function() {
                document.querySelectorAll('.bulk-item-lvsb').forEach(cb => {
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
                if (ids.length === 0) return alert('Pilih minimal 1 data LVSB.');
                if (!confirm(`Konfirmasi Verifikasi Masal:\n\nApakah Anda yakin ingin memverifikasi ${ids.length} data logsheet LVSB terpilih?`)) return;

                bulkInputs.innerHTML = '';
                ids.forEach(id => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'ids[]';
                    input.value = id;
                    bulkInputs.appendChild(input);
                });
                bulkForm.action = '{{ route('public-area.lvsb.bulk.verify') }}';
                if (typeof window.showKprLoading === 'function') window.showKprLoading('Memverifikasi data logsheet LVSB masal...');
                bulkForm.submit();
            });
        }

        if (btnDelete) {
            btnDelete.addEventListener('click', function() {
                const ids = getSelected();
                if (ids.length === 0) return alert('Pilih minimal 1 data LVSB.');
                if (!confirm(`⚠️ PERINGATAN HAPUS MASAL (SUPER ADMIN):\n\nApakah Anda yakin ingin MENGHAPUS PERMANEN ${ids.length} data logsheet LVSB terpilih?\n\n• Semua rekaman panel dan foto fisik lampiran akan dihapus dari server.\n• Tindakan ini TIDAK DAPAT DIBATALKAN.`)) return;

                bulkInputs.innerHTML = '';
                ids.forEach(id => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'ids[]';
                    input.value = id;
                    bulkInputs.appendChild(input);
                });
                bulkForm.action = '{{ route('public-area.lvsb.bulk.destroy') }}';
                if (typeof window.showKprLoading === 'function') window.showKprLoading('Menghapus data logsheet LVSB masal...');
                bulkForm.submit();
            });
        }
    })();
    @endif
</script>
@endpush
