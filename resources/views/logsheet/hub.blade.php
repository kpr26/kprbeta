@extends('layouts.panel')

@php
    $n = fn ($v) => $v !== null ? number_format($v, 2, ',', '.') : '—';
    $hasElecToday  = $today && $today->foto_kwh;
    $hasWaterToday = $today && $today->foto_air;
@endphp

@section('title', 'Pilih Logsheet')
@section('page-title', 'Pencatatan Logsheet')
@section('page-subtitle', 'Pilih jenis utilitas yang ingin diinput hari ini (' . now()->translatedFormat('l, d F Y') . ')')

@section('panel')
<div class="panel logsheet-choice-container">
    <div class="panel-head">
        <div>
            <h2>Pilih Utilitas Logsheet</h2>
            <p>Petugas: <b style="color:#fbbf24;">{{ auth()->user()->name }}</b> &middot; Pilih meteran yang ingin Anda catat stand dan fotonya secara terpisah.</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('public-area.index') }}" class="btn btn-sm">
                🏢 Logsheet Public Area →
            </a>
            <a href="{{ route('logsheet.index') }}" class="btn btn-sm">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.5 2A1.5 1.5 0 0 0 3 3.5v13A1.5 1.5 0 0 0 4.5 18h11a1.5 1.5 0 0 0 1.5-1.5V7.621a1.5 1.5 0 0 0-.44-1.06l-4.12-4.122A1.5 1.5 0 0 0 11.378 2H4.5Zm2.25 8.5a.75.75 0 0 0 0 1.5h6.5a.75.75 0 0 0 0-1.5h-6.5Zm0 3a.75.75 0 0 0 0 1.5h6.5a.75.75 0 0 0 0-1.5h-6.5Z" clip-rule="evenodd"/></svg>
                Lihat Riwayat Logbook
            </a>
        </div>
    </div>

    @if(auth()->user()->role === 'teknisi')
        <div class="offline-feature-pill">
            <span style="display:inline-block; width:7px; height:7px; border-radius:50%; background:#10b981; box-shadow:0 0 6px #10b981;"></span>
            <span><strong style="color:var(--accent);">Mode Offline Aktif:</strong> Otomatis sinkron saat HP kembali terhubung ke jaringan</span>
        </div>
    @endif

    <div class="logsheet-choice-grid">
        {{-- 1. KARTU PILIHAN LOGSHEET AMR PLN T33N --}}
        <div class="logsheet-choice-card logsheet-choice-card--elec">
            <div>
                <div class="choice-card-head">
                    <div class="choice-icon-wrap choice-icon-wrap--pln" style="background:rgba(255,201,14,0.12); border:1px solid rgba(255,201,14,0.3); padding:4px; display:flex; align-items:center; justify-content:center; width:48px; height:48px; border-radius:12px; flex-shrink:0;">
                        {{-- Logo PLN Indonesia --}}
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" style="width:34px; height:34px; filter:drop-shadow(0 2px 4px rgba(0,0,0,0.3));">
                            <!-- Yellow diamond with red border -->
                            <polygon points="32,3 61,32 32,61 3,32" fill="#FFC90E" stroke="#D32F2F" stroke-width="2.5" stroke-linejoin="round"/>
                            <!-- 3 Blue waves -->
                            <path d="M14 36c4-2 7 2 11 0s7-2 11 0 7 2 11 0" fill="none" stroke="#0288D1" stroke-width="2.2" stroke-linecap="round"/>
                            <path d="M16 41c4-2 7 2 11 0s7-2 11 0 7 2 9 0" fill="none" stroke="#0288D1" stroke-width="2.2" stroke-linecap="round"/>
                            <path d="M19 46c3.5-1.8 6.5 1.8 10 0s6.5-1.8 10 0" fill="none" stroke="#0288D1" stroke-width="2.2" stroke-linecap="round"/>
                            <!-- Red lightning bolt -->
                            <polygon points="36,11 23,33 31,33 26,53 43,28 34,28" fill="#D32F2F"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="choice-title">Logsheet AMR PLN T33N</h3>
                        <span class="choice-subtitle">kWh Meter (BP, LBP1, LBP2, Total)</span>
                    </div>
                </div>

                <p class="choice-desc">
                    Catat angka stand AMR PLN T33N: BP, LBP 1, LBP 2, dan Total (kWh) pada gardu utama dengan 1 foto fisik meteran.
                </p>

                <div class="choice-status-box">
                    @if ($hasElecToday)
                        <div class="badge badge-ok" style="font-size:0.8rem; padding:6px 12px;">
                            ✓ Sudah Dicatat Hari Ini
                        </div>
                        <div style="font-size:0.8rem; color:var(--text-secondary); margin-top:8px; line-height:1.5;">
                            @if($today->stand_bp !== null || $today->stand_total !== null)
                                BP: <b>{{ $n($today->stand_bp) }}</b> · LBP1: <b>{{ $n($today->stand_lbp1) }}</b><br>
                                LBP2: <b>{{ $n($today->stand_lbp2) }}</b> · Total: <b>{{ $n($today->stand_total) }} kWh</b>
                            @else
                                LWBP: <b>{{ $n($today->stand_lwbp) }} kWh</b> · WBP: <b>{{ $n($today->stand_wbp) }} kWh</b>
                            @endif
                        </div>
                    @else
                        <div class="badge badge-wait" style="font-size:0.8rem; padding:6px 12px;">
                            Belum Dicatat Hari Ini
                        </div>
                        @if ($prevElec)
                            <div style="font-size:0.78rem; color:var(--text-muted); margin-top:8px; line-height:1.4;">
                                @if($prevElec->stand_total !== null)
                                    Stand kemarin: Total <b>{{ $n($prevElec->stand_total) }} kWh</b> (BP {{ $n($prevElec->stand_bp) }} · LBP1 {{ $n($prevElec->stand_lbp1) }} · LBP2 {{ $n($prevElec->stand_lbp2) }})
                                @else
                                    Stand kemarin: LWBP {{ $n($prevElec->stand_lwbp) }} · WBP {{ $n($prevElec->stand_wbp) }}
                                @endif
                            </div>
                        @endif
                    @endif
                </div>
            </div>

            @if ($hasElecToday && auth()->user()->role === 'teknisi')
                <a href="{{ route('logsheet.show', $today) }}" class="choice-btn-cta choice-btn-cta--secondary">
                    <span>Lihat Detail AMR PLN T33N</span>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.638L10.23 5.29a.75.75 0 1 1 1.04-1.08l5.5 5.25a.75.75 0 0 1 0 1.08l-5.5 5.25a.75.75 0 1 1-1.04-1.08l4.158-3.96H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd"/>
                    </svg>
                </a>
            @else
                <a href="{{ route('logsheet.create.listrik') }}" class="choice-btn-cta choice-btn-cta--elec">
                    <span>{{ $hasElecToday ? 'Perbarui AMR PLN T33N' : 'Input AMR PLN T33N' }}</span>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.638L10.23 5.29a.75.75 0 1 1 1.04-1.08l5.5 5.25a.75.75 0 0 1 0 1.08l-5.5 5.25a.75.75 0 1 1-1.04-1.08l4.158-3.96H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd"/>
                    </svg>
                </a>
            @endif
        </div>

        {{-- 2. KARTU PILIHAN LOGSHEET AIR --}}
        <div class="logsheet-choice-card logsheet-choice-card--water">
            <div>
                <div class="choice-card-head">
                    <div class="choice-icon-wrap choice-icon-wrap--water">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 2.5c.3 0 .58.14.77.37C12.4 4.87 16 9.45 16 12.5a6 6 0 0 1-12 0c0-3.05 3.6-7.63 5.23-9.63A1 1 0 0 1 10 2.5Z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="choice-title">Logsheet Air</h3>
                        <span class="choice-subtitle">Water Meter (PDAM m³)</span>
                    </div>
                </div>

                <p class="choice-desc">
                    Catat angka stand meteran air utama (m³) di lokasi water meter serta lampirkan foto fisik meteran.
                </p>

                <div class="choice-status-box">
                    @if ($hasWaterToday)
                        <div class="badge badge-ok" style="font-size:0.8rem; padding:6px 12px;">
                            ✓ Sudah Dicatat Hari Ini
                        </div>
                        <div style="font-size:0.82rem; color:var(--text-secondary); margin-top:8px;">
                            Stand Air: <b>{{ $n($today->stand_air) }} m³</b>
                        </div>
                    @else
                        <div class="badge badge-wait" style="font-size:0.8rem; padding:6px 12px;">
                            Belum Dicatat Hari Ini
                        </div>
                        @if ($prevWater)
                            <div style="font-size:0.82rem; color:var(--text-muted); margin-top:8px;">
                                Stand kemarin: {{ $n($prevWater->stand_air) }} m³
                            </div>
                        @endif
                    @endif
                </div>
            </div>

            @if ($hasWaterToday && auth()->user()->role === 'teknisi')
                <a href="{{ route('logsheet.show', $today) }}" class="choice-btn-cta choice-btn-cta--secondary">
                    <span>Lihat Detail Catatan Air</span>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.638L10.23 5.29a.75.75 0 1 1 1.04-1.08l5.5 5.25a.75.75 0 0 1 0 1.08l-5.5 5.25a.75.75 0 1 1-1.04-1.08l4.158-3.96H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd"/>
                    </svg>
                </a>
            @else
                <a href="{{ route('logsheet.create.air') }}" class="choice-btn-cta choice-btn-cta--water">
                    <span>{{ $hasWaterToday ? 'Perbarui Stand Air' : 'Input Stand Air' }}</span>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.638L10.23 5.29a.75.75 0 1 1 1.04-1.08l5.5 5.25a.75.75 0 0 1 0 1.08l-5.5 5.25a.75.75 0 1 1-1.04-1.08l4.158-3.96H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd"/>
                    </svg>
                </a>
            @endif
        </div>

        {{-- 3. KARTU PILIHAN LOGSHEET PUBLIC AREA --}}
        <div class="logsheet-choice-card logsheet-choice-card--water">
            <div>
                <div class="choice-card-head">
                    <div class="choice-icon-wrap choice-icon-wrap--water" style="font-size:1.4rem;">
                        🏢
                    </div>
                    <div>
                        <h3 class="choice-title">Logsheet Public Area</h3>
                        <span class="choice-subtitle">Water Meter (3 Area) &amp; LVSB</span>
                    </div>
                </div>

                <p class="choice-desc">
                    Pencatatan terpisah untuk <b>Cooling Tower 12A</b>, <b>Toilet Sky Lobby 11 &amp; Mushola 12</b>, <b>Pantry Office</b>, serta Panel <b>LVSB (Low Voltage Switchboard)</b>.
                </p>

                <div class="choice-status-box">
                    <div class="badge badge-info" style="font-size:0.8rem; padding:6px 12px;">
                        ⚡ Dukungan Offline &amp; Galeri Foto
                    </div>
                    <div style="font-size:0.82rem; color:var(--text-secondary); margin-top:8px;">
                        Foto meteran dapat diambil terlebih dahulu lalu dipilih langsung dari galeri HP.
                    </div>
                </div>
            </div>

            <div style="display:flex; flex-direction:column; gap:8px;">
                <a href="{{ route('public-area.water.index') }}" class="choice-btn-cta choice-btn-cta--water">
                    <span>Input Water Meter Public Area</span>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.638L10.23 5.29a.75.75 0 1 1 1.04-1.08l5.5 5.25a.75.75 0 0 1 0 1.08l-5.5 5.25a.75.75 0 1 1-1.04-1.08l4.158-3.96H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd"/>
                    </svg>
                </a>
                <a href="{{ route('public-area.index') }}" class="choice-btn-cta choice-btn-cta--secondary" style="font-size:0.85rem; padding:10px 16px;">
                    <span>Buka Menu Hub Public Area</span>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Z" clip-rule="evenodd"/>
                    </svg>
                </a>
            </div>
        </div>

        {{-- 4. KARTU PILIHAN DAILY LOG BOOK (Pekerjaan Harian Teknisi) --}}
        <div class="logsheet-choice-card logsheet-choice-card--dailylog">
            <div>
                <div class="choice-card-head">
                    <div class="choice-icon-wrap choice-icon-wrap--dailylog">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
                            <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
                            <path d="M9 12l2 2 4-4"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="choice-title">Daily Log Book</h3>
                        <span class="choice-subtitle" style="color:var(--accent);">Pekerjaan Harian &amp; Bukti Foto</span>
                    </div>
                </div>

                <p class="choice-desc">
                    Catat jam start &amp; selesai, hari/tanggal, lokasi, deskripsi tugas, status (Done/Continue), dan lampirkan minimal 3 foto ber-watermark otomatis.
                </p>

                <div class="choice-status-box">
                    <div class="badge badge-ok" style="font-size:0.8rem; padding:6px 12px;">
                        Fitur Aktif · Watermark Otomatis
                    </div>
                    <div style="font-size:0.82rem; color:var(--text-secondary); margin-top:8px;">
                        Foto awal, proses, dan akhir dilengkapi tanggal, jam, dan lokasi.
                    </div>
                </div>
            </div>

            <div style="display:flex; flex-direction:column; gap:8px;">
                <a href="{{ route('daily-log.create') }}" class="choice-btn-cta" style="background:linear-gradient(135deg, #10b981, #059669); color:#fff; border:none; box-shadow:0 4px 14px rgba(16,185,129,0.3);">
                    <span>+ Input Daily Log Baru</span>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.638L10.23 5.29a.75.75 0 1 1 1.04-1.08l5.5 5.25a.75.75 0 0 1 0 1.08l-5.5 5.25a.75.75 0 1 1-1.04-1.08l4.158-3.96H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd"/>
                    </svg>
                </a>
                <a href="{{ route('daily-log.index') }}" class="choice-btn-cta choice-btn-cta--secondary" style="font-size:0.85rem; padding:10px 16px;">
                    <span>Lihat Riwayat Daily Log Book</span>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Z" clip-rule="evenodd"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>

    @if (auth()->user()->isSuperAdmin())
    {{-- 5. SECTION KHUSUS SUPER ADMIN: INPUT MANUAL TANGGAL TERLEWAT --}}
    <div style="margin-top:36px; padding-top:24px; border-top:1px solid var(--border);">
        <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px; margin-bottom:18px;">
            <div>
                <div style="display:flex; align-items:center; gap:8px;">
                    <span class="badge badge-warning" style="font-size:0.75rem; padding:4px 8px;">
                        ★ Khusus Super Admin
                    </span>
                    <h3 style="font-size:1.15rem; font-weight:600; color:var(--text-primary); margin:0;">
                        Input Manual Logsheet (Tanggal Terlewat)
                    </h3>
                </div>
                <p style="font-size:0.83rem; color:var(--text-muted); margin:4px 0 0;">
                    Gunakan menu ini jika ada data pencatatan listrik atau air pada hari-hari sebelumnya yang belum sempat diinput teknisi atau memerlukan koreksi.
                </p>
            </div>
        </div>

        <div class="logsheet-choice-grid">
            {{-- Kartu Manual AMR PLN T33N --}}
            <div class="logsheet-choice-card logsheet-choice-card--elec">
                <div>
                    <div class="choice-card-head">
                        <div class="choice-icon-wrap" style="background:rgba(255,201,14,0.12); border:1px solid rgba(255,201,14,0.3); padding:4px; display:flex; align-items:center; justify-content:center; width:48px; height:48px; border-radius:12px; flex-shrink:0;">
                            {{-- Logo PLN Indonesia --}}
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" style="width:32px; height:32px;">
                                <polygon points="32,3 61,32 32,61 3,32" fill="#FFC90E" stroke="#D32F2F" stroke-width="2.5" stroke-linejoin="round"/>
                                <path d="M14 36c4-2 7 2 11 0s7-2 11 0 7 2 11 0" fill="none" stroke="#0288D1" stroke-width="2.2" stroke-linecap="round"/>
                                <path d="M16 41c4-2 7 2 11 0s7-2 11 0 7 2 9 0" fill="none" stroke="#0288D1" stroke-width="2.2" stroke-linecap="round"/>
                                <path d="M19 46c3.5-1.8 6.5 1.8 10 0s6.5-1.8 10 0" fill="none" stroke="#0288D1" stroke-width="2.2" stroke-linecap="round"/>
                                <polygon points="36,11 23,33 31,33 26,53 43,28 34,28" fill="#D32F2F"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="choice-title">Manual AMR PLN T33N</h3>
                            <span class="choice-subtitle">Pilih Tanggal Bebas (Lampau)</span>
                        </div>
                    </div>
                    <p class="choice-desc">
                        Masukkan stand AMR PLN T33N: BP, LBP 1, LBP 2, dan Total (kWh) untuk tanggal yang terlewat lengkap dengan pengecekan data acuan sebelum dan sesudahnya.
                    </p>
                </div>
                <div style="margin-top:16px;">
                    <a href="{{ route('logsheet.manual.listrik') }}" class="choice-btn-cta choice-btn-cta--outline-elec">
                        <span>+ Input Manual AMR PLN</span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.638L10.23 5.29a.75.75 0 1 1 1.04-1.08l5.5 5.25a.75.75 0 0 1 0 1.08l-5.5 5.25a.75.75 0 1 1-1.04-1.08l4.158-3.96H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd"/>
                        </svg>
                    </a>
                </div>
            </div>

            {{-- Kartu Manual Air --}}
            <div class="logsheet-choice-card logsheet-choice-card--water">
                <div>
                    <div class="choice-card-head">
                        <div class="choice-icon-wrap choice-icon-wrap--water">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 2.5c.3 0 .58.14.77.37C12.4 4.87 16 9.45 16 12.5a6 6 0 0 1-12 0c0-3.05 3.6-7.63 5.23-9.63A1 1 0 0 1 10 2.5Z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="choice-title">Manual Air</h3>
                            <span class="choice-subtitle">Pilih Tanggal Bebas (Lampau)</span>
                        </div>
                    </div>
                    <p class="choice-desc">
                        Masukkan stand Water Meter (m³) untuk tanggal yang terlewat lengkap dengan pengecekan data acuan tanggal sebelum dan sesudahnya.
                    </p>
                </div>
                <div style="margin-top:16px;">
                    <a href="{{ route('logsheet.manual.air') }}" class="choice-btn-cta choice-btn-cta--outline-water">
                        <span>+ Input Manual Air</span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.638L10.23 5.29a.75.75 0 1 1 1.04-1.08l5.5 5.25a.75.75 0 0 1 0 1.08l-5.5 5.25a.75.75 0 1 1-1.04-1.08l4.158-3.96H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
