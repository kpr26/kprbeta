@extends('layouts.panel')

@php
    $n = fn ($v) => $v !== null ? number_format($v, 3, ',', '.') : '—';
    $namaBulan = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ][$month] ?? (string)$month;
@endphp

@section('title', 'Tenant Water Usage (Air) - Billing System')
@section('page-title', 'Tenant Water Usage')
@section('page-subtitle', 'Monitoring & Pencatatan Konsumsi Air Bulanan (m³) Periode ' . $namaBulan . ' ' . $year)

@section('panel')
<div class="panel">
    <!-- Header -->
    <div class="panel-head">
        <div>
            <h2>Penggunaan Air Bulanan Tenant</h2>
            <p>Rekapitulasi stand awal, stand akhir, dan pemakaian air m³ per tenant setiap bulan.</p>
        </div>
        <div class="btn-group">
            @if(auth()->user()->isSuperAdmin())
                <a href="{{ route('billing.water.bulk.create', ['year' => $year, 'month' => $month]) }}" class="btn btn-sm" style="background:#0284c7; color:#fff; font-weight:600;">
                    📥 Bulk Input Super Admin
                </a>
            @endif

            @if($setting->is_open || auth()->user()->isSuperAdmin())
                <a href="{{ route('billing.quick-input', ['year' => $year, 'month' => $month]) }}" class="btn btn-primary">
                    ⚡ Mode Input Cepat Teknisi
                </a>
                <a href="{{ route('billing.water.create', ['year' => $year, 'month' => $month]) }}" class="btn btn-sm" style="color:#38bdf8; font-weight:600;">
                    + Input Stand Air
                </a>
            @else
                <a href="{{ route('billing.water.create') }}" class="btn btn-sm" style="color:#f87171; border-color:rgba(239,68,68,0.3);">
                    🔒 Penginputan Terkunci
                </a>
            @endif
            <a href="{{ route('billing.photos.zip', ['year' => $year, 'month' => $month, 'type' => 'water']) }}" class="btn btn-sm" style="color:#38bdf8; border-color:rgba(56,189,248,0.4); font-weight:600;" title="Unduh arsip file foto water meter">
                📸 Backup Foto (.ZIP)
            </a>
            <a href="{{ route('billing.water.excel', ['year' => $year, 'month' => $month]) }}" class="btn btn-sm">
                📥 Unduh Excel
            </a>
            <a href="{{ route('billing.water.csv', ['year' => $year, 'month' => $month]) }}" class="btn btn-sm" style="color:#38bdf8;">
                📄 CSV
            </a>
            <a href="{{ route('billing.water.pdf', ['year' => $year, 'month' => $month]) }}" target="_blank" class="btn btn-sm">
                🖨️ Cetak / PDF
            </a>
        </div>
    </div>

    <!-- KPI Summary Cards -->
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(210px, 1fr)); gap:16px; margin:20px 0;">
        <div style="background:var(--bg-card); border:1px solid rgba(56,189,248,0.3); border-radius:var(--radius-md); padding:16px;">
            <span style="font-size:0.75rem; font-weight:700; color:var(--text-muted); text-transform:uppercase;">Total Pemakaian Air</span>
            <div style="font-size:1.6rem; font-weight:800; color:#38bdf8; font-family:monospace; margin-top:4px;">
                {{ $n($stats['total_water']) }} <span style="font-size:0.9rem; font-weight:normal; color:var(--text-secondary);">m³</span>
            </div>
            <div style="font-size:0.75rem; color:var(--text-secondary); margin-top:4px;">
                Periode {{ $namaBulan }} {{ $year }}
            </div>
        </div>

        <div style="background:var(--bg-card); border:1px solid var(--border-color); border-radius:var(--radius-md); padding:16px;">
            <span style="font-size:0.75rem; font-weight:700; color:var(--text-muted); text-transform:uppercase;">Status Pencatatan Tenant</span>
            <div style="font-size:1.6rem; font-weight:800; color:var(--text-primary); margin-top:4px;">
                {{ $stats['total_recorded'] }} <span style="font-size:1rem; color:var(--text-muted);">/ {{ $stats['total_active'] }}</span>
            </div>
            <div style="font-size:0.75rem; color:{{ $stats['progress_pct'] === 100 ? 'var(--success)' : '#38bdf8' }}; font-weight:600; margin-top:4px;">
                {{ $stats['progress_pct'] }}% Tenant Sudah Dicatat
            </div>
        </div>

        <div style="background:var(--bg-card); border:1px solid var(--border-color); border-radius:var(--radius-md); padding:16px;">
            <span style="font-size:0.75rem; font-weight:700; color:var(--text-muted); text-transform:uppercase;">Rata-rata Konsumsi / Tenant</span>
            <div style="font-size:1.6rem; font-weight:800; color:var(--accent-light); font-family:monospace; margin-top:4px;">
                {{ $n($stats['avg_water']) }} <span style="font-size:0.9rem; font-weight:normal; color:var(--text-secondary);">m³</span>
            </div>
            <div style="font-size:0.75rem; color:var(--text-secondary); margin-top:4px;">
                Dari {{ $stats['total_recorded'] }} tenant yang telah tercatat
            </div>
        </div>

        <div style="background:var(--bg-card); border:1px solid {{ $stats['total_terkendala'] > 0 ? 'rgba(245,158,11,0.4)' : 'var(--border-color)' }}; border-radius:var(--radius-md); padding:16px;">
            <span style="font-size:0.75rem; font-weight:700; color:var(--text-muted); text-transform:uppercase;">Meter Terkendala / Stand Khusus</span>
            <div style="font-size:1.6rem; font-weight:800; color:{{ $stats['total_terkendala'] > 0 ? '#f59e0b' : 'var(--text-primary)' }}; margin-top:4px;">
                {{ $stats['total_terkendala'] }} <span style="font-size:0.9rem; font-weight:normal; color:var(--text-secondary);">Unit</span>
            </div>
            <div style="font-size:0.75rem; color:{{ $stats['total_terkendala'] > 0 ? '#f59e0b' : 'var(--text-secondary)' }}; margin-top:4px;">
                {{ $stats['total_terkendala'] > 0 ? 'Segel, Stand Mundur, atau Ganti Meter' : 'Semua Meter Normal' }}
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar" style="margin-bottom:20px; background:var(--bg-card); padding:14px 18px; border-radius:var(--radius-md); border:1px solid var(--border-color); display:flex; flex-wrap:wrap; justify-content:space-between; align-items:center; gap:12px;">
        <form method="GET" action="{{ route('billing.water.index') }}" style="display:flex; flex-wrap:wrap; align-items:center; gap:10px; flex:1;">
            <span style="font-size:0.82rem; font-weight:600; color:var(--text-muted); text-transform:uppercase;">Periode:</span>
            <select name="month" class="form-control" style="width:auto; padding:6px 12px; font-size:0.875rem;" onchange="this.form.submit()">
                @for($m = 1; $m <= 12; $m++)
                    @php $bNames = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember']; @endphp
                    <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>{{ $bNames[$m] }}</option>
                @endfor
            </select>
            <select name="year" class="form-control" style="width:auto; padding:6px 12px; font-size:0.875rem;" onchange="this.form.submit()">
                @for($y = now()->year - 2; $y <= now()->year + 1; $y++)
                    <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>

            <select name="lantai" class="form-control" style="width:auto; padding:6px 12px; font-size:0.875rem;" onchange="this.form.submit()">
                <option value="all">Semua Lantai</option>
                @foreach($allLantai as $l)
                    <option value="{{ $l }}" {{ $lantai == $l ? 'selected' : '' }}>Lantai {{ $l }}</option>
                @endforeach
            </select>

            <select name="status" class="form-control" style="width:auto; padding:6px 12px; font-size:0.875rem;" onchange="this.form.submit()">
                <option value="all" {{ $status == 'all' ? 'selected' : '' }}>Semua Status</option>
                <option value="recorded" {{ $status == 'recorded' ? 'selected' : '' }}>Sudah Dicatat ({{ $stats['total_recorded'] }})</option>
                <option value="pending" {{ $status == 'pending' ? 'selected' : '' }}>Belum Dicatat ({{ $stats['total_pending'] }})</option>
                <option value="terkendala" {{ $status == 'terkendala' ? 'selected' : '' }}>Terkendala / Stand Khusus ({{ $stats['total_terkendala'] }})</option>
            </select>

            <input type="text" name="search" class="form-control" placeholder="Cari unit / meter..." value="{{ $search }}" style="min-width:160px; padding:6px 12px; font-size:0.875rem;">
            <button type="submit" class="btn btn-sm btn-primary">Filter</button>
        </form>

        <a href="{{ route('billing.water.index') }}" class="btn btn-sm">Reset</a>
    </div>

    <!-- Tabel Data Penggunaan Air Bulanan -->
    <div class="table-container" style="overflow-x:auto;">
        <table class="table" style="width:100%; border-collapse:collapse;">
            <thead>
                <tr style="border-bottom:1px solid var(--border-color); font-size:0.75rem; text-transform:uppercase; color:var(--text-muted); text-align:left; letter-spacing:0.04em;">
                    <th style="padding:12px 14px;">Tenant &amp; Lokasi</th>
                    <th style="padding:12px 14px;">No. Water Meter</th>
                    <th style="padding:12px 14px; text-align:right;">Stand Awal (m³)</th>
                    <th style="padding:12px 14px; text-align:right;">Stand Akhir (m³)</th>
                    <th style="padding:12px 14px; text-align:right;">Pemakaian</th>
                    <th style="padding:12px 14px; text-align:center;">Foto Meter</th>
                    <th style="padding:12px 14px;">Petugas &amp; Tgl</th>
                    <th style="padding:12px 14px; text-align:center;">Status / Kendala</th>
                    <th style="padding:12px 14px; text-align:right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tenantList as $row)
                    @php $t = $row->tenant; @endphp
                    <tr style="border-bottom:1px solid var(--border-color); font-size:0.85rem; {{ $row->is_terkendala ? 'background:rgba(245,158,11,0.04);' : '' }}">
                        <td style="padding:12px 14px;">
                            <a href="{{ route('billing.tenants.show', $t) }}" style="font-weight:700; color:var(--text-primary); text-decoration:none;" class="hover-accent">
                                {{ $t->nama_tenant }}
                            </a>
                            <div style="font-size:0.72rem; color:var(--accent);">
                                📍 {{ $t->unit_label }}
                            </div>
                        </td>
                        <td style="padding:12px 14px;">
                            <span style="font-family:monospace; font-weight:600; color:#38bdf8;">
                                {{ $t->nomor_meter_air ?? '—' }}
                            </span>
                            @if((float)$t->faktor_kali_air != 1.0)
                                <div style="font-size:0.7rem; color:var(--text-muted);">FK: {{ (float)$t->faktor_kali_air }}x</div>
                            @endif
                        </td>
                        <td style="padding:12px 14px; text-align:right; font-family:monospace; color:var(--text-secondary);">
                            {{ $n($row->stand_awal) }}
                        </td>
                        <td style="padding:12px 14px; text-align:right; font-family:monospace; font-weight:700; color:#38bdf8;">
                            {{ $row->stand_akhir !== null ? $n($row->stand_akhir) : '—' }}
                        </td>
                        <td style="padding:12px 14px; text-align:right; font-weight:700; color:{{ $row->pemakaian > 0 ? 'var(--success)' : 'var(--text-muted)' }};">
                            {{ $row->is_recorded ? $n($row->pemakaian) . ' m³' : '—' }}
                        </td>
                        <td style="padding:12px 14px; text-align:center;">
                            @if($row->foto_url)
                                <a href="#" data-lightbox="{{ $row->foto_url }}" data-caption="Foto Water Meter: {{ $t->nama_tenant }} ({{ $namaBulan }} {{ $year }})" style="display:inline-block;">
                                    <img src="{{ $row->foto_url }}" alt="Foto Meter" style="width:36px; height:36px; object-fit:cover; border-radius:6px; border:1px solid var(--border-color);">
                                </a>
                            @else
                                <span style="color:var(--text-muted); font-size:0.75rem;">—</span>
                            @endif
                        </td>
                        <td style="padding:12px 14px;">
                            <div style="font-size:0.8rem; color:var(--text-primary); font-weight:500;">{{ $row->petugas }}</div>
                            <div style="font-size:0.72rem; color:var(--text-muted);">{{ $row->tanggal }}</div>
                        </td>
                        <td style="padding:12px 14px; text-align:center;">
                            @if($row->is_terkendala)
                                <span class="badge" style="background:rgba(245,158,11,0.18); color:#f59e0b; border:1px solid rgba(245,158,11,0.35); font-size:0.72rem;" title="{{ $row->keterangan }}">
                                    ⚠️ {{ $row->keterangan ?: 'Terkendala' }}
                                </span>
                            @elseif($row->is_recorded)
                                <span class="badge badge-ok" style="font-size:0.72rem;">Sudah Dicatat</span>
                            @else
                                <span class="badge" style="background:rgba(239,68,68,0.15); color:#f87171; font-size:0.72rem;">Belum</span>
                            @endif
                        </td>
                        <td style="padding:12px 14px; text-align:right;">
                            @php
                                $canModify = auth()->user()->canModifyBillingData();
                            @endphp

                            @if($canModify)
                                {{-- Aksi untuk Super Admin dan SPV: Kontrol penuh Ubah dan Hapus --}}
                                <div style="display:inline-flex; gap:6px;">
                                    <a href="{{ route('billing.water.create', ['tenant_id' => $t->id, 'year' => $year, 'month' => $month]) }}" class="btn btn-sm {{ $row->is_recorded ? '' : 'btn-primary' }}" style="padding:4px 10px; font-size:0.78rem;">
                                        {{ $row->is_recorded ? 'Ubah' : 'Input' }}
                                    </a>
                                    @if($row->reading)
                                        <form method="POST" action="{{ route('billing.water.destroy', $row->reading) }}" onsubmit="return confirm('Hapus data stand air ini?')" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm" style="color:var(--danger); padding:4px 8px; font-size:0.78rem;" title="Hapus">
                                                &times;
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            @else
                                {{-- Aksi untuk Role Teknisi: Hanya Input Baru saat Belum Tercatat, TIDAK BISA Ubah/Hapus --}}
                                @if($row->is_recorded)
                                    <span class="badge badge-ok" style="font-size:0.72rem; padding:4px 8px;" title="Data stand sudah tersimpan dan terkunci. Pengubahan hanya oleh Super Admin/SPV.">
                                        ✓ Tercatat
                                    </span>
                                @else
                                    @if($setting->is_open ?? true)
                                        <a href="{{ route('billing.water.create', ['tenant_id' => $t->id, 'year' => $year, 'month' => $month]) }}" class="btn btn-sm btn-primary" style="padding:4px 10px; font-size:0.78rem;">
                                            + Input
                                        </a>
                                    @else
                                        <span style="font-size:0.72rem; color:var(--text-muted);">Terkunci</span>
                                    @endif
                                @endif
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="padding:32px; text-align:center; color:var(--text-muted);">
                            Tidak ada tenant yang sesuai dengan filter.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if($tenantList->isNotEmpty())
                <tfoot>
                    <tr style="background:var(--bg-main); font-weight:700; font-size:0.85rem; border-top:2px solid var(--border-color);">
                        <td colspan="4" style="padding:12px 14px; text-align:right; text-transform:uppercase;">Total Pemakaian Bulan Ini:</td>
                        <td style="padding:12px 14px; text-align:right; font-family:monospace; color:#38bdf8; font-size:1.05rem;">
                            {{ $n($stats['total_water']) }} m³
                        </td>
                        <td colspan="4" style="padding:12px 14px;"></td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
</div>
@endsection
