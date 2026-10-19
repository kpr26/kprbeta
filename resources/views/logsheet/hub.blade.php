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
            <a href="{{ route('logsheet.index') }}" class="btn btn-sm">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.5 2A1.5 1.5 0 0 0 3 3.5v13A1.5 1.5 0 0 0 4.5 18h11a1.5 1.5 0 0 0 1.5-1.5V7.621a1.5 1.5 0 0 0-.44-1.06l-4.12-4.122A1.5 1.5 0 0 0 11.378 2H4.5Zm2.25 8.5a.75.75 0 0 0 0 1.5h6.5a.75.75 0 0 0 0-1.5h-6.5Zm0 3a.75.75 0 0 0 0 1.5h6.5a.75.75 0 0 0 0-1.5h-6.5Z" clip-rule="evenodd"/></svg>
                Lihat Riwayat Logbook
            </a>
        </div>
    </div>

    @if(auth()->user()->role === 'teknisi')
        <div class="offline-feature-banner" style="background:rgba(200,170,110,0.08); border:1px dashed rgba(200,170,110,0.3); border-radius:12px; padding:10px 16px; margin-bottom:20px; display:flex; align-items:center; gap:12px; font-size:0.84rem; color:var(--text-secondary);">
            <div style="width:32px; height:32px; border-radius:8px; background:rgba(200,170,110,0.15); color:var(--accent-light); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width:18px;height:18px;">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm.75-13a.75.75 0 0 0-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 0 0 0-1.5h-3.25V5Z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div>
                <strong style="color:var(--accent-light);">Fitur Offline Aktif:</strong>
                Aplikasi mendukung input stand &amp; foto di basement/ruang tanpa sinyal. Data otomatis disinkronkan ke server saat HP Anda terhubung kembali ke jaringan.
            </div>
        </div>
    @endif

    <div class="logsheet-choice-grid">
        {{-- 1. KARTU PILIHAN LOGSHEET LISTRIK --}}
        <div class="logsheet-choice-card logsheet-choice-card--elec">
            <div>
                <div class="choice-card-head">
                    <div class="choice-icon-wrap choice-icon-wrap--elec">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M11.983 1.907a.75.75 0 0 0-1.292-.657l-8.5 9.5A.75.75 0 0 0 2.75 12h6.572l-1.305 6.093a.75.75 0 0 0 1.292.657l8.5-9.5A.75.75 0 0 0 17.25 8h-6.572l1.305-6.093Z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="choice-title">Logsheet Listrik</h3>
                        <span class="choice-subtitle">kWh Meter (LWBP &amp; WBP)</span>
                    </div>
                </div>

                <p class="choice-desc">
                    Catat angka stand meteran listrik LWBP dan WBP pada gardu serta lampirkan foto fisik kWh meter.
                </p>

                <div class="choice-status-box">
                    @if ($hasElecToday)
                        <div class="badge badge-ok" style="font-size:0.8rem; padding:6px 12px;">
                            ✓ Sudah Dicatat Hari Ini
                        </div>
                        <div style="font-size:0.82rem; color:var(--text-secondary); margin-top:8px;">
                            LWBP: <b>{{ $n($today->stand_lwbp) }} kWh</b> · WBP: <b>{{ $n($today->stand_wbp) }} kWh</b>
                        </div>
                    @else
                        <div class="badge badge-wait" style="font-size:0.8rem; padding:6px 12px;">
                            Belum Dicatat Hari Ini
                        </div>
                        @if ($prevElec)
                            <div style="font-size:0.82rem; color:var(--text-muted); margin-top:8px;">
                                Stand kemarin: LWBP {{ $n($prevElec->stand_lwbp) }} · WBP {{ $n($prevElec->stand_wbp) }}
                            </div>
                        @endif
                    @endif
                </div>
            </div>

            @if ($hasElecToday && auth()->user()->role === 'teknisi')
                <a href="{{ route('logsheet.show', $today) }}" class="choice-btn-cta" style="background:rgba(200,170,110,0.08); color:var(--accent-light); border:1px solid rgba(200,170,110,0.25);">
                    <span>Lihat Detail Catatan Listrik</span>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.638L10.23 5.29a.75.75 0 1 1 1.04-1.08l5.5 5.25a.75.75 0 0 1 0 1.08l-5.5 5.25a.75.75 0 1 1-1.04-1.08l4.158-3.96H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd"/>
                    </svg>
                </a>
            @else
                <a href="{{ route('logsheet.create.listrik') }}" class="choice-btn-cta choice-btn-cta--elec">
                    <span>{{ $hasElecToday ? 'Perbarui Stand Listrik' : 'Input Stand Listrik' }}</span>
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
                <a href="{{ route('logsheet.show', $today) }}" class="choice-btn-cta" style="background:rgba(200,170,110,0.08); color:var(--accent-light); border:1px solid rgba(200,170,110,0.25);">
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

        {{-- 3. KARTU PILIHAN DAILY LOG BOOK (Pekerjaan Harian Teknisi) --}}
        <div class="logsheet-choice-card logsheet-choice-card--dailylog">
            <div style="position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg, #10b981, var(--accent));"></div>
            <div>
                <div class="choice-card-head">
                    <div class="choice-icon-wrap" style="background:rgba(16,185,129,0.12);color:#34d399;border:1px solid rgba(16,185,129,0.3);">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
                            <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
                            <path d="M9 12l2 2 4-4"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="choice-title">Daily Log Book</h3>
                        <span class="choice-subtitle" style="color:#34d399;">Pekerjaan Harian &amp; Bukti Foto</span>
                    </div>
                </div>

                <p class="choice-desc">
                    Catat jam start &amp; selesai, hari/tanggal, lokasi, deskripsi tugas, status (Done/Continue), dan lampirkan minimal 3 foto ber-watermark otomatis.
                </p>

                <div class="choice-status-box">
                    <div class="badge badge-ok" style="font-size:0.8rem; padding:6px 12px; background:rgba(16,185,129,0.15); color:#34d399; border:1px solid rgba(16,185,129,0.3);">
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
                <a href="{{ route('daily-log.index') }}" class="choice-btn-cta" style="background:rgba(200,170,110,0.06); color:var(--accent-light); border:1px solid rgba(200,170,110,0.2); font-size:0.85rem; padding:10px 16px;">
                    <span>Lihat Riwayat Daily Log Book</span>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Z" clip-rule="evenodd"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>

    </div>

    @if (auth()->user()->isSuperAdmin())
    {{-- 4. SECTION KHUSUS SUPER ADMIN: INPUT MANUAL TANGGAL TERLEWAT --}}
    <div style="margin-top:36px; padding-top:24px; border-top:1px solid rgba(245,158,11,0.25);">
        <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px; margin-bottom:18px;">
            <div>
                <div style="display:flex; align-items:center; gap:8px;">
                    <span style="background:rgba(245,158,11,0.2); color:#fbbf24; border:1px solid rgba(245,158,11,0.4); font-size:0.75rem; padding:3px 8px; border-radius:6px; font-weight:700; text-transform:uppercase;">
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
            {{-- Kartu Manual Listrik --}}
            <div class="logsheet-choice-card" style="border-color:rgba(245,158,11,0.3); background:rgba(15,22,35,0.9);">
                <div>
                    <div class="choice-card-head">
                        <div class="choice-icon-wrap" style="background:rgba(245,158,11,0.15); color:#fbbf24; border:1px solid rgba(245,158,11,0.3);">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M11.983 1.907a.75.75 0 0 0-1.292-.657l-8.5 9.5A.75.75 0 0 0 2.75 12h6.572l-1.305 6.093a.75.75 0 0 0 1.292.657l8.5-9.5A.75.75 0 0 0 17.25 8h-6.572l1.305-6.093Z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="choice-title" style="color:#fbbf24;">Manual Listrik</h3>
                            <span class="choice-subtitle">Pilih Tanggal Bebas (Lampau)</span>
                        </div>
                    </div>
                    <p class="choice-desc">
                        Masukkan stand meter LWBP &amp; WBP untuk tanggal yang terlewat lengkap dengan pengecekan data acuan tanggal sebelum dan sesudahnya.
                    </p>
                </div>
                <div style="margin-top:16px;">
                    <a href="{{ route('logsheet.manual.listrik') }}" class="choice-btn-cta" style="background:rgba(245,158,11,0.15); color:#fbbf24; border:1px solid rgba(245,158,11,0.35);">
                        <span>+ Input Manual Listrik</span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.638L10.23 5.29a.75.75 0 1 1 1.04-1.08l5.5 5.25a.75.75 0 0 1 0 1.08l-5.5 5.25a.75.75 0 1 1-1.04-1.08l4.158-3.96H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd"/>
                        </svg>
                    </a>
                </div>
            </div>

            {{-- Kartu Manual Air --}}
            <div class="logsheet-choice-card" style="border-color:rgba(56,189,248,0.3); background:rgba(15,22,35,0.9);">
                <div>
                    <div class="choice-card-head">
                        <div class="choice-icon-wrap" style="background:rgba(56,189,248,0.15); color:#38bdf8; border:1px solid rgba(56,189,248,0.3);">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 2.5c.3 0 .58.14.77.37C12.4 4.87 16 9.45 16 12.5a6 6 0 0 1-12 0c0-3.05 3.6-7.63 5.23-9.63A1 1 0 0 1 10 2.5Z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="choice-title" style="color:#38bdf8;">Manual Air</h3>
                            <span class="choice-subtitle">Pilih Tanggal Bebas (Lampau)</span>
                        </div>
                    </div>
                    <p class="choice-desc">
                        Masukkan stand Water Meter (m³) untuk tanggal yang terlewat lengkap dengan pengecekan data acuan tanggal sebelum dan sesudahnya.
                    </p>
                </div>
                <div style="margin-top:16px;">
                    <a href="{{ route('logsheet.manual.air') }}" class="choice-btn-cta" style="background:rgba(56,189,248,0.15); color:#38bdf8; border:1px solid rgba(56,189,248,0.35);">
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
