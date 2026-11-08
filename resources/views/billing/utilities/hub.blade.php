@extends('layouts.panel')

@php
    $n = fn ($v) => $v !== null ? number_format($v, 2, ',', '.') : '—';
@endphp

@section('title', 'Utilities Hub - Billing System')
@section('page-title', 'Billing System: Utilities Hub')
@section('page-subtitle', 'Pusat Manajemen Utilitas Bulanan Tenant (' . $namaBulan . ' ' . $year . ')')

@section('panel')
<div class="panel logsheet-choice-container">
    <div class="panel-head">
        <div>
            <h2>Menu Utilitas Tenant</h2>
            <p>Pilih sub menu di bawah untuk mencatat atau memantau pemakaian listrik (kWh) dan air (m³) tenant bulanan.</p>
        </div>
        <div class="btn-group">
            @if(auth()->user()->isSuperAdmin())
                <a href="{{ route('billing.energy.bulk.create', ['year' => $year, 'month' => $month]) }}" class="btn btn-sm" style="color:#facc15; border-color:rgba(250,204,21,0.4); font-weight:700;">
                    ⚡ Bulk Listrik
                </a>
                <a href="{{ route('billing.water.bulk.create', ['year' => $year, 'month' => $month]) }}" class="btn btn-sm" style="color:#38bdf8; border-color:rgba(56,189,248,0.4); font-weight:700;">
                    💧 Bulk Air
                </a>
            @endif
            @if($setting->is_open || auth()->user()->isSuperAdmin())
                <a href="{{ route('billing.quick-input', ['year' => $year, 'month' => $month]) }}" class="btn btn-primary">
                    ⚡ Mode Input Cepat Teknisi
                </a>
            @else
                <a href="{{ route('billing.quick-input') }}" class="btn btn-sm" style="color:#f87171; border-color:rgba(239,68,68,0.3);">
                    🔒 Input Terkunci
                </a>
            @endif
            <a href="{{ route('billing.photos', ['year' => $year, 'month' => $month]) }}" class="btn btn-sm" style="color:#c084fc; border-color:rgba(192,132,252,0.4); font-weight:700;">
                📸 Galeri &amp; Backup Foto
            </a>
            @if(auth()->user()->canManageTenants())
                <a href="{{ route('billing.tenants.index') }}" class="btn btn-sm">
                    🏢 Kelola Tenant
                </a>
            @endif
        </div>
    </div>

    @php
        $authUser = auth()->user();
        $isSuperAdmin = $authUser->isSuperAdmin();
    @endphp

    <!-- PANEL PENGATURAN KHUSUS SUPER ADMIN: KONTROL AKSES PENGINPUTAN TEKNISI -->
    @if($isSuperAdmin)
        <div style="background:var(--bg-card); border:2px solid {{ $setting->is_open ? 'rgba(34,197,94,0.4)' : 'rgba(239,68,68,0.4)' }}; border-radius:var(--radius-md); padding:16px 20px; margin-bottom:24px; box-shadow:0 4px 14px rgba(0,0,0,0.1);">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:12px; border-bottom:1px solid var(--border-color); padding-bottom:12px;">
                <div style="display:flex; align-items:center; gap:10px;">
                    <span style="font-size:1.3rem;">🛡️</span>
                    <div>
                        <h3 style="font-size:0.98rem; font-weight:800; margin:0; color:var(--text-primary); text-transform:uppercase; letter-spacing:0.04em;">
                            Pengaturan Khusus Super Admin: Akses Penginputan Teknisi (1x Sebulan)
                        </h3>
                        <p style="font-size:0.78rem; color:var(--text-secondary); margin:2px 0 0;">
                            Kunci menu penginputan agar teknisi tidak melakukan pencatatan di luar jadwal resmi bulanan.
                        </p>
                    </div>
                </div>

                <div>
                    @if($setting->is_open)
                        <span class="badge badge-ok" style="font-size:0.8rem; padding:5px 12px; font-weight:700;">
                            🟢 SESI DIBUKA (Periode {{ $setting->nama_bulan_aktif }} {{ $setting->active_year }})
                        </span>
                    @else
                        <span class="badge" style="background:rgba(239,68,68,0.15); color:#f87171; border:1px solid rgba(239,68,68,0.3); font-size:0.8rem; padding:5px 12px; font-weight:700;">
                            🔒 TERKUNCI (Penginputan Teknisi Ditutup)
                        </span>
                    @endif
                </div>
            </div>

            <!-- Form Kontrol Buka/Tutup Sesi -->
            <form method="POST" action="{{ route('billing.utilities.toggle-session') }}" style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:14px;">
                @csrf
                @if($setting->is_open)
                    <input type="hidden" name="action" value="close">
                    <div style="font-size:0.85rem; color:var(--text-secondary); flex:1; min-width:240px;">
                        <span>Status: <strong>Sedang dibuka</strong> sejak {{ $setting->opened_at?->translatedFormat('d F Y H:i') ?? '—' }} WIB</span>
                        @if($setting->openedByUser)
                            oleh <strong>{{ $setting->openedByUser->name }}</strong>
                        @endif
                        @if($setting->keterangan)
                            <div style="font-size:0.78rem; color:var(--text-muted); margin-top:2px;">Catatan: <em>"{{ $setting->keterangan }}"</em></div>
                        @endif
                    </div>
                    <div>
                        <button type="submit" class="btn btn-sm" onclick="return confirm('Apakah Anda yakin ingin MENUTUP / MENGUNCI sesi penginputan utilitas? Teknisi tidak akan bisa menginput data baru lagi.')" style="background:rgba(239,68,68,0.15); color:#f87171; border-color:rgba(239,68,68,0.4); font-weight:700; padding:8px 16px;">
                            🔒 Tutup / Kunci Sesi Penginputan
                        </button>
                    </div>
                @else
                    <input type="hidden" name="action" value="open">
                    <div style="display:flex; flex-wrap:wrap; align-items:center; gap:10px; flex:1;">
                        <span style="font-size:0.82rem; font-weight:700; color:var(--text-secondary);">Buka Penginputan Bulan:</span>
                        <select name="active_month" class="form-control" style="width:auto; padding:6px 10px; font-size:0.85rem;">
                            @for($m = 1; $m <= 12; $m++)
                                @php $bNames = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember']; @endphp
                                <option value="{{ $m }}" {{ $m == $setting->active_month ? 'selected' : '' }}>{{ $bNames[$m] }}</option>
                            @endfor
                        </select>
                        <select name="active_year" class="form-control" style="width:auto; padding:6px 10px; font-size:0.85rem;">
                            @for($y = now()->year - 1; $y <= now()->year + 1; $y++)
                                <option value="{{ $y }}" {{ $y == $setting->active_year ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                        <input type="text" name="keterangan" class="form-control" placeholder="Instruksi pencatatan teknisi (opsional)..." style="min-width:200px; font-size:0.85rem; padding:6px 10px;">
                    </div>
                    <div>
                        <button type="submit" class="btn btn-sm btn-primary" style="background:#22c55e; border-color:#22c55e; font-weight:700; padding:8px 16px;">
                            🔓 Buka Sesi Penginputan Sekarang
                        </button>
                    </div>
                @endif
            </form>
        </div>
    @elseif(! $setting->is_open)
        <!-- BANNER KHUSUS TEKNISI SAAT TERKUNCI -->
        <div style="background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.3); border-radius:var(--radius-md); padding:14px 18px; margin-bottom:24px; display:flex; align-items:center; gap:14px;">
            <div style="font-size:1.8rem; color:#f87171;">🔒</div>
            <div style="flex:1;">
                <h4 style="margin:0 0 3px; font-size:0.95rem; font-weight:700; color:#f87171;">Sesi Penginputan Meteran Sedang Ditutup oleh Super Admin</h4>
                <p style="margin:0; font-size:0.82rem; color:var(--text-secondary);">
                    Pencatatan meteran utilitas tenant hanya dilakukan 1x dalam sebulan. Penginputan saat ini dikunci dan hanya dapat diisi kembali setelah Super Admin membuka jadwal pencatatan.
                    @if($setting->keterangan)
                        <br><span style="color:var(--text-muted); font-style:italic;">Pesan Super Admin: "{{ $setting->keterangan }}"</span>
                    @endif
                </p>
            </div>
        </div>
    @endif

    <!-- Filter Periode Bulan & Tahun -->
    <div class="filter-bar" style="margin-bottom:24px; display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:12px; background:var(--bg-card); padding:14px 18px; border-radius:var(--radius-md); border:1px solid var(--border-color);">
        <form method="GET" action="{{ route('billing.utilities.hub') }}" style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
            <label style="font-size:0.82rem; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.04em;">Periode Penagihan:</label>
            <select name="month" class="form-control" style="width:auto; padding:6px 12px; font-size:0.875rem;" onchange="this.form.submit()">
                @for($m = 1; $m <= 12; $m++)
                    @php
                        $bNames = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];
                    @endphp
                    <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>{{ $bNames[$m] }}</option>
                @endfor
            </select>
            <select name="year" class="form-control" style="width:auto; padding:6px 12px; font-size:0.875rem;" onchange="this.form.submit()">
                @for($y = now()->year - 2; $y <= now()->year + 1; $y++)
                    <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
            <noscript><button type="submit" class="btn btn-sm">Terapkan</button></noscript>
        </form>

        <div style="font-size:0.82rem; color:var(--text-secondary); display:flex; align-items:center; gap:8px;">
            <span>Total Tenant Aktif: <strong style="color:var(--text-primary);">{{ $totalTenants }}</strong></span>
            <span>•</span>
            <span>Tercatat Lengkap: <strong style="color:var(--success);">{{ $completeBoth }} / {{ $totalTenants }}</strong></span>
        </div>
    </div>

    <!-- Quick Action / Alert Teknisi -->
    @if($setting->is_open || $isSuperAdmin)
        <div style="background:linear-gradient(135deg, rgba(14,165,233,0.1) 0%, rgba(34,197,94,0.1) 100%); border:1px solid rgba(14,165,233,0.25); border-radius:var(--radius-md); padding:16px 20px; margin-bottom:24px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:16px;">
            <div style="display:flex; align-items:center; gap:14px;">
                <div style="width:42px; height:42px; border-radius:10px; background:var(--accent-glow); display:flex; align-items:center; justify-content:center; color:var(--accent); font-size:1.4rem; flex-shrink:0;">
                    ⚡
                </div>
                <div>
                    <h4 style="margin:0 0 4px; font-size:1rem; font-weight:700; color:var(--text-primary);">Pencatatan Rutin Bulanan Teknisi</h4>
                    <p style="margin:0; font-size:0.83rem; color:var(--text-secondary);">Gunakan <strong>Mode Input Cepat</strong> untuk membaca stand kWh listrik &amp; air meter secara otomatis dari bulan lalu, hitung pemakaian langsung, serta ambil foto meteran dalam 1 langkah mudah.</p>
                </div>
            </div>
            <a href="{{ route('billing.quick-input', ['year' => $year, 'month' => $month]) }}" class="btn btn-primary" style="white-space:nowrap; padding:10px 18px;">
                Buka Checklist Rute Input &rarr;
            </a>
        </div>
    @endif

    <!-- Grid Card Pilihan Sub Menu Utilitas -->
    <div class="logsheet-choice-grid">
        {{-- 1. SUB MENU: TENANT ENERGY USAGE (LISTRIK) --}}
        <div class="logsheet-choice-card logsheet-choice-card--elec">
            <div>
                <div class="choice-card-head">
                    <div class="choice-icon-wrap choice-icon-wrap--elec">
                        <svg viewBox="0 0 20 20" fill="currentColor">
                            <path d="M11.983 1.907a.75.75 0 0 0-1.292-.657l-8.5 9.5A.75.75 0 0 0 2.75 12h6.572l-1.305 6.093a.75.75 0 0 0 1.292.657l8.5-9.5A.75.75 0 0 0 17.25 8h-6.572l1.305-6.093Z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="choice-title">Tenant Energy Usage</h3>
                        <span class="choice-subtitle">Penggunaan Listrik Bulanan (kWh)</span>
                    </div>
                </div>

                <p class="choice-desc">
                    Monitoring dan pencatatan meteran kWh listrik per tenant setiap 1 bulan sekali, dilengkapi bukti foto kWh meter dan kalkulasi otomatis.
                </p>

                <div class="choice-status-box">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px;">
                        <span class="choice-status-label">Status Periode {{ $namaBulan }} {{ $year }}</span>
                        @if($energyCount === $totalTenants && $totalTenants > 0)
                            <span class="badge badge-ok" style="font-size:0.75rem;">✓ Selesai ({{ $energyCount }}/{{ $totalTenants }})</span>
                        @elseif($energyCount > 0)
                            <span class="badge badge-wait" style="font-size:0.75rem;">Sebagian ({{ $energyCount }}/{{ $totalTenants }})</span>
                        @else
                            <span class="badge badge-wait" style="font-size:0.75rem; background:rgba(239,68,68,0.15); color:#f87171;">Belum Ada Data (0/{{ $totalTenants }})</span>
                        @endif
                    </div>

                    <div style="display:flex; justify-content:space-between; align-items:baseline; margin-bottom:8px; padding-bottom:8px; border-bottom:1px dashed var(--border-color);">
                        <span style="font-size:0.8rem; color:var(--text-muted);">Total Konsumsi Listrik:</span>
                        <strong style="font-size:1.15rem; color:#facc15; font-family:var(--font-mono, monospace);">{{ $n($totalKwh) }} <span style="font-size:0.8rem; font-weight:normal; color:var(--text-secondary);">kWh</span></strong>
                    </div>

                    <div style="display:flex; justify-content:space-between; align-items:center; font-size:0.8rem;">
                        <span style="color:var(--text-muted);">Tenant Terdata:</span>
                        <span style="font-weight:600; color:var(--text-primary);">{{ $energyCount }} dari {{ $totalTenants }} Tenant</span>
                    </div>
                </div>
            </div>

            <div style="display:flex; gap:10px; margin-top:16px;">
                <a href="{{ route('billing.energy.index', ['year' => $year, 'month' => $month]) }}" class="choice-btn-cta choice-btn-cta--elec" style="flex:1;">
                    <span>Lihat Data &amp; Laporan</span>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.638L10.23 5.29a.75.75 0 1 1 1.04-1.08l5.5 5.25a.75.75 0 0 1 0 1.08l-5.5 5.25a.75.75 0 1 1-1.04-1.08l4.158-3.96H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd"/></svg>
                </a>
                @if($setting->is_open || $isSuperAdmin)
                    <a href="{{ route('billing.energy.create', ['year' => $year, 'month' => $month]) }}" class="btn btn-sm" style="display:flex; align-items:center; justify-content:center; padding:10px 14px; background:rgba(234,179,8,0.15); color:#facc15; border-color:rgba(234,179,8,0.3); font-weight:600;" title="Input Stand Listrik">
                        + Input Stand
                    </a>
                @else
                    <span class="btn btn-sm" style="display:flex; align-items:center; justify-content:center; padding:10px 14px; background:rgba(239,68,68,0.1); color:#f87171; border-color:rgba(239,68,68,0.3); font-size:0.78rem;" title="Penginputan dikunci Super Admin">
                        🔒 Terkunci
                    </span>
                @endif
            </div>
        </div>

        {{-- 2. SUB MENU: TENANT WATER USAGE (AIR) --}}
        <div class="logsheet-choice-card logsheet-choice-card--water">
            <div>
                <div class="choice-card-head">
                    <div class="choice-icon-wrap choice-icon-wrap--water">
                        <svg viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 2.5c.3 0 .58.14.77.37C12.4 4.87 16 9.45 16 12.5a6 6 0 0 1-12 0c0-3.05 3.6-7.63 5.23-9.63A1 1 0 0 1 10 2.5Z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="choice-title">Tenant Water Usage</h3>
                        <span class="choice-subtitle">Penggunaan Air Bulanan (m³)</span>
                    </div>
                </div>

                <p class="choice-desc">
                    Monitoring dan pencatatan stand water meter bulanan setiap tenant, dilengkapi foto fisik water meter serta kalkulasi pemakaian m³ secara akurat.
                </p>

                <div class="choice-status-box">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px;">
                        <span class="choice-status-label">Status Periode {{ $namaBulan }} {{ $year }}</span>
                        @if($waterCount === $totalTenants && $totalTenants > 0)
                            <span class="badge badge-ok" style="font-size:0.75rem;">✓ Selesai ({{ $waterCount }}/{{ $totalTenants }})</span>
                        @elseif($waterCount > 0)
                            <span class="badge badge-wait" style="font-size:0.75rem;">Sebagian ({{ $waterCount }}/{{ $totalTenants }})</span>
                        @else
                            <span class="badge badge-wait" style="font-size:0.75rem; background:rgba(239,68,68,0.15); color:#f87171;">Belum Ada Data (0/{{ $totalTenants }})</span>
                        @endif
                    </div>

                    <div style="display:flex; justify-content:space-between; align-items:baseline; margin-bottom:8px; padding-bottom:8px; border-bottom:1px dashed var(--border-color);">
                        <span style="font-size:0.8rem; color:var(--text-muted);">Total Konsumsi Air:</span>
                        <strong style="font-size:1.15rem; color:#38bdf8; font-family:var(--font-mono, monospace);">{{ $n($totalWater) }} <span style="font-size:0.8rem; font-weight:normal; color:var(--text-secondary);">m³</span></strong>
                    </div>

                    <div style="display:flex; justify-content:space-between; align-items:center; font-size:0.8rem;">
                        <span style="color:var(--text-muted);">Tenant Terdata:</span>
                        <span style="font-weight:600; color:var(--text-primary);">{{ $waterCount }} dari {{ $totalTenants }} Tenant</span>
                    </div>
                </div>
            </div>

            <div style="display:flex; gap:10px; margin-top:16px;">
                <a href="{{ route('billing.water.index', ['year' => $year, 'month' => $month]) }}" class="choice-btn-cta choice-btn-cta--water" style="flex:1;">
                    <span>Lihat Data &amp; Laporan</span>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.638L10.23 5.29a.75.75 0 1 1 1.04-1.08l5.5 5.25a.75.75 0 0 1 0 1.08l-5.5 5.25a.75.75 0 1 1-1.04-1.08l4.158-3.96H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd"/></svg>
                </a>
                @if($setting->is_open || $isSuperAdmin)
                    <a href="{{ route('billing.water.create', ['year' => $year, 'month' => $month]) }}" class="btn btn-sm" style="display:flex; align-items:center; justify-content:center; padding:10px 14px; background:rgba(56,189,248,0.15); color:#38bdf8; border-color:rgba(56,189,248,0.3); font-weight:600;" title="Input Stand Air">
                        + Input Stand
                    </a>
                @else
                    <span class="btn btn-sm" style="display:flex; align-items:center; justify-content:center; padding:10px 14px; background:rgba(239,68,68,0.1); color:#f87171; border-color:rgba(239,68,68,0.3); font-size:0.78rem;" title="Penginputan dikunci Super Admin">
                        🔒 Terkunci
                    </span>
                @endif
            </div>
        </div>

        {{-- 3. SUB MENU: BUKTI FOTO & BACKUP METERAN --}}
        <div class="logsheet-choice-card" style="border-top:4px solid #c084fc;">
            <div>
                <div class="choice-card-head">
                    <div class="choice-icon-wrap" style="background:rgba(192,132,252,0.15); color:#c084fc;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:24px; height:24px;">
                            <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
                            <circle cx="12" cy="13" r="4"></circle>
                        </svg>
                    </div>
                    <div>
                        <h3 class="choice-title">Bukti Foto Meteran</h3>
                        <span class="choice-subtitle">Galeri &amp; Backup Dokumen ZIP</span>
                    </div>
                </div>

                <p class="choice-desc">
                    Arsip visual foto fisik kWh meter (reguler &amp; EV) dan water meter per tenant. Siap di-export file ZIP atau dicetak sebagai bukti resmi jika diminta tenant.
                </p>

                <div class="choice-status-box">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px;">
                        <span class="choice-status-label">Kelengkapan Foto {{ $namaBulan }} {{ $year }}</span>
                        <span class="badge" style="background:rgba(192,132,252,0.15); color:#c084fc; font-size:0.75rem;">Tersimpan di Cloud/Server</span>
                    </div>

                    <div style="display:flex; justify-content:space-between; align-items:baseline; margin-bottom:8px; padding-bottom:8px; border-bottom:1px dashed var(--border-color);">
                        <span style="font-size:0.8rem; color:var(--text-muted);">Paket Backup:</span>
                        <strong style="font-size:0.95rem; color:#c084fc;">ZIP + Manifest CSV</strong>
                    </div>

                    <div style="display:flex; justify-content:space-between; align-items:center; font-size:0.8rem;">
                        <span style="color:var(--text-muted);">Format Bukti:</span>
                        <span style="font-weight:600; color:var(--text-primary);">Lembar Cetak &amp; PDF</span>
                    </div>
                </div>
            </div>

            <div style="display:flex; gap:10px; margin-top:16px;">
                <a href="{{ route('billing.photos', ['year' => $year, 'month' => $month]) }}" class="choice-btn-cta" style="flex:1; background:linear-gradient(135deg, #9333ea 0%, #7e22ce 100%); color:#ffffff; font-weight:700;">
                    <span>Buka Galeri Foto</span>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.638L10.23 5.29a.75.75 0 1 1 1.04-1.08l5.5 5.25a.75.75 0 0 1 0 1.08l-5.5 5.25a.75.75 0 1 1-1.04-1.08l4.158-3.96H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd"/></svg>
                </a>
                <a href="{{ route('billing.photos.zip', ['year' => $year, 'month' => $month]) }}" class="btn btn-sm" style="display:flex; align-items:center; justify-content:center; padding:10px 14px; background:rgba(192,132,252,0.15); color:#c084fc; border-color:rgba(192,132,252,0.3); font-weight:600;" title="Unduh Arsip ZIP Semua Foto">
                    📦 Backup ZIP
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
