@extends('layouts.panel')

@php
    $n = fn ($v) => $v !== null ? number_format($v, 2, ',', '.') : '—';
@endphp

@section('title', 'Logsheet Public Area')
@section('page-title', 'Logsheet Public Area')
@section('page-subtitle', 'Pencatatan Mandiri Water Meter & KWh Meter Area Publik Gedung (' . now()->translatedFormat('l, d F Y') . ')')

@section('panel')
<div class="panel logsheet-choice-container">
    <div class="panel-head">
        <div>
            <h2>Menu Logsheet Public Area</h2>
            <p>Pilih sub menu utilitas di bawah ini untuk mencatat stand meteran area publik gedung.</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('logsheet.hub') }}" class="btn btn-sm">
                ⚡ Logsheet AMR PLN &amp; Air Utama
            </a>
        </div>
    </div>

    @if(auth()->user()->role === 'teknisi')
        <div class="offline-feature-pill">
            <span style="display:inline-block; width:7px; height:7px; border-radius:50%; background:#10b981; box-shadow:0 0 6px #10b981;"></span>
            <span><strong style="color:var(--accent);">Mode Offline Aktif:</strong> Setiap inputan tersimpan di HP dan otomatis sinkron saat mendapatkan sinyal</span>
        </div>
    @endif

    <div class="logsheet-choice-grid">
        {{-- 1. SUB MENU: WATER METER PUBLIC AREA --}}
        <div class="logsheet-choice-card logsheet-choice-card--water">
            <div>
                <div class="choice-card-head">
                    <div class="choice-icon-wrap choice-icon-wrap--water">
                        <svg viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 2.5c.3 0 .58.14.77.37C12.4 4.87 16 9.45 16 12.5a6 6 0 0 1-12 0c0-3.05 3.6-7.63 5.23-9.63A1 1 0 0 1 10 2.5Z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="choice-title">Water Meter Public Area</h3>
                        <span class="choice-subtitle">3 Titik Meter Air Gedung</span>
                    </div>
                </div>

                <p class="choice-desc">
                    Pencatatan 3 titik meter air publik secara terpisah: <b>Cooling Tower 12A</b>, <b>Toilet Sky Lobby 11 &amp; Mushola 12</b>, serta <b>Pantry Office</b>.
                </p>

                <div class="choice-status-box">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px;">
                        <span class="choice-status-label">Status Hari Ini</span>
                        @if($waterCountDone === $waterTotalCount)
                            <span class="badge badge-ok" style="font-size:0.75rem;">✓ Lengkap ({{ $waterCountDone }}/{{ $waterTotalCount }})</span>
                        @elseif($waterCountDone > 0)
                            <span class="badge badge-wait" style="font-size:0.75rem;">Sebagian ({{ $waterCountDone }}/{{ $waterTotalCount }})</span>
                        @else
                            <span class="badge badge-wait" style="font-size:0.75rem;">Belum Dicatat (0/{{ $waterTotalCount }})</span>
                        @endif
                    </div>

                    <div class="choice-item-list">
                        @foreach($waterMeters as $k => $m)
                            @php $recorded = $todayWater->get($k); @endphp
                            <div class="choice-item-row">
                                <span>{{ $m['label'] }}:</span>
                                @if($recorded)
                                    <span style="color:var(--success); font-weight:600;">✓ {{ $n($recorded->stand_meter) }} m³</span>
                                @else
                                    <span style="color:var(--text-muted); font-size:0.75rem;">Belum</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <a href="{{ route('public-area.water.index') }}" class="choice-btn-cta choice-btn-cta--water">
                <span>Buka Sub Menu Water Meter</span>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.638L10.23 5.29a.75.75 0 1 1 1.04-1.08l5.5 5.25a.75.75 0 0 1 0 1.08l-5.5 5.25a.75.75 0 1 1-1.04-1.08l4.158-3.96H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd"/>
                </svg>
            </a>
        </div>

        {{-- 2. SUB MENU: LVSB (LOW VOLTAGE SWITCHBOARD) --}}
        <div class="logsheet-choice-card logsheet-choice-card--elec">
            <div>
                <div class="choice-card-head">
                    <div class="choice-icon-wrap choice-icon-wrap--elec">
                        <svg viewBox="0 0 20 20" fill="currentColor">
                            <path d="M11.983 1.907a.75.75 0 0 0-1.292-.657l-8.5 9.5A.75.75 0 0 0 2.75 12h6.572l-1.305 6.093a.75.75 0 0 0 1.292.657l8.5-9.5A.75.75 0 0 0 17.25 8h-6.572l1.305-6.093Z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="choice-title">LVSB (Low Voltage Switchboard)</h3>
                        <span class="choice-subtitle">Panel Utama &amp; Distribusi Feeder</span>
                    </div>
                </div>

                <p class="choice-desc">
                    Pencatatan parameter panel tegangan rendah (Voltage 3-P, Current, Power kW, Energy GWh, PF, Frequency) untuk 6 grup: <b>Incoming Trafo</b>, <b>Feeder Riser 1-5</b>, <b>AC Sky Lobby &amp; Trafo</b>, <b>DB Fire</b>, serta <b>Kapasitor</b>.
                </p>

                <div class="choice-status-box">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px;">
                        <span class="choice-status-label">Status Hari Ini</span>
                        @if($todayLvsb)
                            <span class="badge badge-ok" style="font-size:0.75rem;">✓ Sudah Dicatat</span>
                        @else
                            <span class="badge badge-wait" style="font-size:0.75rem;">Belum Dicatat</span>
                        @endif
                    </div>

                    <div style="font-size:0.82rem; color:var(--text-secondary); line-height:1.5;">
                        @if($todayLvsb)
                            Incoming Trafo: <b>{{ $n($todayLvsb->val('incoming_trafo', 'voltage_3p'), 1) }} V</b> · <b>{{ $n($todayLvsb->val('incoming_trafo', 'power')) }} kW</b><br>
                            Capacitor PF: <b>{{ $n($todayLvsb->val('incoming_capacitor', 'power_factor')) }}</b><br>
                            <span style="font-size:0.75rem; color:var(--text-muted);">Dicatat oleh {{ $todayLvsb->user?->name ?? 'Teknisi' }}</span>
                        @else
                            Belum ada pencatatan LVSB hari ini.
                        @endif
                    </div>
                </div>
            </div>

            <a href="{{ route('public-area.lvsb.index') }}" class="choice-btn-cta choice-btn-cta--elec">
                <span>Buka Sub Menu LVSB</span>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.638L10.23 5.29a.75.75 0 1 1 1.04-1.08l5.5 5.25a.75.75 0 0 1 0 1.08l-5.5 5.25a.75.75 0 1 1-1.04-1.08l4.158-3.96H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd"/>
                </svg>
            </a>
        </div>

        {{-- 3. SUB MENU: MVSB INCOMING FROM PLN --}}
        <div class="logsheet-choice-card logsheet-choice-card--mvsb">
            <div>
                <div class="choice-card-head">
                    <div class="choice-icon-wrap choice-icon-wrap--mvsb">
                        <svg viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0 1 12 2v5h4a1 1 0 0 1 .82 1.573l-7 10A1 1 0 0 1 8 18v-5H4a1 1 0 0 1-.82-1.573l7-10a1 1 0 0 1 1.12-.381Z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="choice-title">MVSB</h3>
                        <span class="choice-subtitle">Medium Voltage Switchboard 20kV (Incoming PLN)</span>
                    </div>
                </div>

                <p class="choice-desc">
                    Pencatatan parameter tegangan menengah panel incoming PLN: <b>Voltage 20kV (Volt)</b>, <b>Current (Ampere)</b>, serta lampiran foto fisik panel dari galeri atau kamera langsung.
                </p>

                <div class="choice-status-box">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px;">
                        <span class="choice-status-label">Status Hari Ini</span>
                        @if($todayMvsb)
                            <span class="badge badge-ok" style="font-size:0.75rem;">✓ Sudah Dicatat</span>
                        @else
                            <span class="badge badge-wait" style="font-size:0.75rem;">Belum Dicatat</span>
                        @endif
                    </div>

                    <div style="font-size:0.82rem; color:var(--text-secondary); line-height:1.5;">
                        @if($todayMvsb)
                            Voltage 20kV: <b>{{ $todayMvsb->voltage_20kv !== null ? number_format($todayMvsb->voltage_20kv, 1, ',', '.') : '—' }} Volt</b><br>
                            Current: <b>{{ $todayMvsb->current !== null ? number_format($todayMvsb->current, 2, ',', '.') : '—' }} Ampere</b>
                            @if($todayMvsb->foto)
                                · <span style="color:var(--info);">📷 Foto Ada</span>
                            @endif
                            <br>
                            <span style="font-size:0.75rem; color:var(--text-muted);">Dicatat oleh {{ $todayMvsb->user?->name ?? 'Teknisi' }}</span>
                        @else
                            Belum ada pencatatan MVSB hari ini.
                        @endif
                    </div>
                </div>
            </div>

            <a href="{{ route('public-area.mvsb.index') }}" class="choice-btn-cta choice-btn-cta--mvsb">
                <span>Buka Sub Menu MVSB</span>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.638L10.23 5.29a.75.75 0 1 1 1.04-1.08l5.5 5.25a.75.75 0 0 1 0 1.08l-5.5 5.25a.75.75 0 1 1-1.04-1.08l4.158-3.96H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd"/>
                </svg>
            </a>
        </div>
    </div>
</div>
@endsection
