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
            <a href="{{ route('logsheet.hub') }}" class="btn btn-sm" style="background:rgba(255,255,255,0.06); color:var(--text-secondary);">
                ⚡ Logsheet AMR PLN &amp; Air Utama
            </a>
        </div>
    </div>

    @if(auth()->user()->role === 'teknisi')
        <div class="offline-feature-pill">
            <span style="display:inline-block; width:7px; height:7px; border-radius:50%; background:#10b981; box-shadow:0 0 6px #10b981;"></span>
            <span><strong style="color:var(--accent-light);">Mode Offline Aktif:</strong> Setiap inputan tersimpan di HP dan otomatis sinkron saat mendapatkan sinyal</span>
        </div>
    @endif

    <div class="logsheet-choice-grid">
        {{-- 1. SUB MENU: WATER METER PUBLIC AREA --}}
        <div class="logsheet-choice-card logsheet-choice-card--water">
            <div>
                <div class="choice-card-head">
                    <div class="choice-icon-wrap" style="background:rgba(56,189,248,0.12); border:1px solid rgba(56,189,248,0.3); color:#38bdf8;">
                        <svg viewBox="0 0 20 20" fill="currentColor" style="width:26px; height:26px;">
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

                <div class="choice-status-box" style="margin-top:14px;">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px;">
                        <span style="font-size:0.78rem; text-transform:uppercase; font-weight:700; color:var(--text-muted); letter-spacing:0.06em;">Status Hari Ini</span>
                        @if($waterCountDone === $waterTotalCount)
                            <span class="badge badge-ok" style="font-size:0.75rem;">✓ Lengkap ({{ $waterCountDone }}/{{ $waterTotalCount }})</span>
                        @elseif($waterCountDone > 0)
                            <span class="badge badge-wait" style="font-size:0.75rem;">Sebagian ({{ $waterCountDone }}/{{ $waterTotalCount }})</span>
                        @else
                            <span class="badge badge-wait" style="font-size:0.75rem; background:rgba(239,68,68,0.15); color:#f87171; border-color:rgba(239,68,68,0.3);">Belum Dicatat (0/{{ $waterTotalCount }})</span>
                        @endif
                    </div>

                    <div style="font-size:0.8rem; color:var(--text-secondary); display:flex; flex-direction:column; gap:6px;">
                        @foreach($waterMeters as $k => $m)
                            @php $recorded = $todayWater->get($k); @endphp
                            <div style="display:flex; justify-content:space-between; align-items:center; padding:4px 0; border-bottom:1px dashed rgba(200,170,110,0.12);">
                                <span>{{ $m['label'] }}:</span>
                                @if($recorded)
                                    <span style="color:#34d399; font-weight:600;">✓ {{ $n($recorded->stand_meter) }} m³</span>
                                @else
                                    <span style="color:var(--text-muted); font-size:0.75rem;">Belum</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <a href="{{ route('public-area.water.index') }}" class="choice-btn-cta choice-btn-cta--water" style="margin-top:20px;">
                <span>Buka Sub Menu Water Meter</span>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.638L10.23 5.29a.75.75 0 1 1 1.04-1.08l5.5 5.25a.75.75 0 0 1 0 1.08l-5.5 5.25a.75.75 0 1 1-1.04-1.08l4.158-3.96H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd"/>
                </svg>
            </a>
        </div>

        {{-- 2. SUB MENU: KWH METER PUBLIC AREA --}}
        <div class="logsheet-choice-card logsheet-choice-card--elec">
            <div>
                <div class="choice-card-head">
                    <div class="choice-icon-wrap" style="background:rgba(251,191,36,0.12); border:1px solid rgba(251,191,36,0.3); color:#fbbf24;">
                        <svg viewBox="0 0 20 20" fill="currentColor" style="width:26px; height:26px;">
                            <path d="M11.983 1.907a.75.75 0 0 0-1.292-.657l-8.5 9.5A.75.75 0 0 0 2.75 12h6.572l-1.305 6.093a.75.75 0 0 0 1.292.657l8.5-9.5A.75.75 0 0 0 17.25 8h-6.572l1.305-6.093Z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="choice-title">KWh Meter Public Area</h3>
                        <span class="choice-subtitle">Listrik Penerangan &amp; Daya</span>
                    </div>
                </div>

                <p class="choice-desc">
                    Pencatatan angka stand KWh meteran listrik untuk penerangan dan beban daya area publik gedung.
                </p>

                <div class="choice-status-box" style="margin-top:14px;">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px;">
                        <span style="font-size:0.78rem; text-transform:uppercase; font-weight:700; color:var(--text-muted); letter-spacing:0.06em;">Status Hari Ini</span>
                        @if($todayElec)
                            <span class="badge badge-ok" style="font-size:0.75rem;">✓ Sudah Dicatat</span>
                        @else
                            <span class="badge badge-wait" style="font-size:0.75rem;">Belum Dicatat</span>
                        @endif
                    </div>

                    <div style="font-size:0.82rem; color:var(--text-secondary); line-height:1.5;">
                        @if($todayElec)
                            Stand hari ini: <b>{{ $n($todayElec->stand_meter) }} kWh</b><br>
                            <span style="font-size:0.75rem; color:var(--text-muted);">Dicatat oleh {{ $todayElec->user?->name ?? 'Teknisi' }}</span>
                        @elseif($prevElec)
                            Stand sebelumnya: <b>{{ $n($prevElec->stand_meter) }} kWh</b> ({{ $prevElec->tanggal->translatedFormat('d M') }})
                        @else
                            Belum ada riwayat stand sebelumnya.
                        @endif
                    </div>
                </div>
            </div>

            <a href="{{ route('public-area.electricity.index') }}" class="choice-btn-cta choice-btn-cta--elec" style="margin-top:20px;">
                <span>Buka Sub Menu KWh Meter</span>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.638L10.23 5.29a.75.75 0 1 1 1.04-1.08l5.5 5.25a.75.75 0 0 1 0 1.08l-5.5 5.25a.75.75 0 1 1-1.04-1.08l4.158-3.96H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd"/>
                </svg>
            </a>
        </div>
    </div>
</div>
@endsection
