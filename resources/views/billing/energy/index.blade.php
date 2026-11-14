@extends('layouts.panel')

@php
    $n = fn ($v) => $v !== null ? number_format($v, 2, ',', '.') : '—';
    $namaBulan = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ][$month] ?? (string)$month;
@endphp

@section('title', 'Tenant Energy Usage (Listrik) - Billing System')
@section('page-title', 'Tenant Energy Usage')
@section('page-subtitle', 'Monitoring & Pencatatan Konsumsi Listrik Bulanan (kWh) Periode ' . $namaBulan . ' ' . $year)

@section('panel')
<div class="panel">
    <!-- Header -->
    <div class="panel-head">
        <div>
            <h2>Penggunaan Listrik Bulanan Tenant</h2>
            <p>Rekapitulasi stand awal, stand akhir, dan pemakaian listrik kWh per tenant setiap bulan.</p>
        </div>
        <div class="btn-group">
            @if(auth()->user()->isSuperAdmin())
                <a href="{{ route('billing.energy.bulk.create', ['year' => $year, 'month' => $month]) }}" class="btn btn-sm" style="color:#c084fc; border-color:rgba(192,132,252,0.4); font-weight:700;">
                    📋 Input Bulk Manual
                </a>
            @endif
            @if($setting->is_open || auth()->user()->isSuperAdmin())
                <a href="{{ route('billing.quick-input', ['year' => $year, 'month' => $month]) }}" class="btn btn-primary">
                    ⚡ Mode Input Cepat Teknisi
                </a>
                <a href="{{ route('billing.energy.create', ['year' => $year, 'month' => $month]) }}" class="btn btn-sm" style="color:#facc15; font-weight:600;">
                    + Input Stand Listrik
                </a>
            @else
                <a href="{{ route('billing.energy.create') }}" class="btn btn-sm" style="color:#f87171; border-color:rgba(239,68,68,0.3);">
                    🔒 Penginputan Terkunci
                </a>
            @endif
            <a href="{{ route('billing.photos.zip', ['year' => $year, 'month' => $month, 'type' => 'energy']) }}" class="btn btn-sm" style="color:#fde047; border-color:rgba(250,204,21,0.4); font-weight:600;" title="Unduh arsip file foto meteran listrik (termasuk EV Charger)">
                📸 Backup Foto (.ZIP)
            </a>
            <a href="{{ route('billing.energy.excel', ['year' => $year, 'month' => $month]) }}" class="btn btn-sm">
                📥 Unduh Excel
            </a>
            <a href="{{ route('billing.energy.csv', ['year' => $year, 'month' => $month]) }}" class="btn btn-sm">
                📄 Unduh CSV
            </a>
            <a href="{{ route('billing.energy.pdf', ['year' => $year, 'month' => $month]) }}" target="_blank" class="btn btn-sm">
                🖨️ Cetak / PDF
            </a>
        </div>
    </div>

    <!-- KPI Summary Cards -->
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:16px; margin:20px 0;">
        <div style="background:var(--bg-card); border:1px solid rgba(234,179,8,0.3); border-radius:var(--radius-md); padding:16px;">
            <span style="font-size:0.75rem; font-weight:700; color:var(--text-muted); text-transform:uppercase;">Total Pemakaian Listrik</span>
            <div style="font-size:1.6rem; font-weight:800; color:#facc15; font-family:monospace; margin-top:4px;">
                {{ $n($stats['total_kwh']) }} <span style="font-size:0.9rem; font-weight:normal; color:var(--text-secondary);">kWh</span>
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
            <div style="font-size:0.75rem; color:{{ $stats['progress_pct'] === 100 ? 'var(--success)' : '#facc15' }}; font-weight:600; margin-top:4px;">
                {{ $stats['progress_pct'] }}% Tenant Sudah Dicatat
            </div>
        </div>

        <div style="background:var(--bg-card); border:1px solid var(--border-color); border-radius:var(--radius-md); padding:16px;">
            <span style="font-size:0.75rem; font-weight:700; color:var(--text-muted); text-transform:uppercase;">Rata-rata Konsumsi / Tenant</span>
            <div style="font-size:1.6rem; font-weight:800; color:var(--accent-light); font-family:monospace; margin-top:4px;">
                {{ $n($stats['avg_kwh']) }} <span style="font-size:0.9rem; font-weight:normal; color:var(--text-secondary);">kWh</span>
            </div>
            <div style="font-size:0.75rem; color:var(--text-secondary); margin-top:4px;">
                Dari {{ $stats['total_recorded'] }} tenant yang telah tercatat
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar" style="margin-bottom:20px; background:var(--bg-card); padding:14px 18px; border-radius:var(--radius-md); border:1px solid var(--border-color); display:flex; flex-wrap:wrap; justify-content:space-between; align-items:center; gap:12px;">
        <form method="GET" action="{{ route('billing.energy.index') }}" style="display:flex; flex-wrap:wrap; align-items:center; gap:10px; flex:1;">
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
                    <option value="{{ $l }}" {{ $lantai == $l ? 'selected' : '' }}>{{ $l }}</option>
                @endforeach
            </select>

            <select name="status" class="form-control" style="width:auto; padding:6px 12px; font-size:0.875rem;" onchange="this.form.submit()">
                <option value="all" {{ $status == 'all' ? 'selected' : '' }}>Semua Status</option>
                <option value="recorded" {{ $status == 'recorded' ? 'selected' : '' }}>Sudah Dicatat ({{ $stats['total_recorded'] }})</option>
                <option value="pending" {{ $status == 'pending' ? 'selected' : '' }}>Belum Dicatat ({{ $stats['total_pending'] }})</option>
            </select>

            <input type="text" name="search" class="form-control" placeholder="Cari nama tenant / meter..." value="{{ $search }}" style="min-width:180px; padding:6px 12px; font-size:0.875rem;">
            <button type="submit" class="btn btn-sm btn-primary">Filter</button>
        </form>

        <a href="{{ route('billing.energy.index') }}" class="btn btn-sm">Reset</a>
    </div>

    <!-- Tabel Data Penggunaan Listrik Bulanan -->
    {{-- Navigasi Paginasi Halaman Atas (Maksimal 10 Data per Halaman) --}}
    <div class="kpr-pagination-top" id="billing-energy-pagination-top"></div>

    <div class="table-container" style="overflow-x:auto;">
        <table class="table" style="width:100%; border-collapse:collapse;">
            <thead>
                <tr style="border-bottom:1px solid var(--border-color); font-size:0.75rem; text-transform:uppercase; color:var(--text-muted); text-align:left; letter-spacing:0.04em;">
                    <th style="padding:12px 14px;">Tenant &amp; Lokasi</th>
                    <th style="padding:12px 14px;">No. Meter (CT)</th>
                    <th style="padding:12px 14px; text-align:right;">Stand Awal (kWh)</th>
                    <th style="padding:12px 14px; text-align:right;">Stand Akhir (kWh)</th>
                    <th style="padding:12px 14px; text-align:right;">Pemakaian</th>
                    <th style="padding:12px 14px; text-align:center;">Foto Meter</th>
                    <th style="padding:12px 14px;">Petugas &amp; Tgl</th>
                    <th style="padding:12px 14px; text-align:center;">Status</th>
                    <th style="padding:12px 14px; text-align:right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tenantList as $row)
                    @php $t = $row->tenant; @endphp
                    <tr class="billing-energy-row" style="border-bottom:1px solid var(--border-color); font-size:0.85rem;">
                        <td style="padding:12px 14px;">
                            <a href="{{ route('billing.tenants.show', $t) }}" style="font-weight:700; color:var(--text-primary); text-decoration:none;" class="hover-accent">
                                {{ $t->nama_tenant }}
                            </a>
                            <div style="font-size:0.72rem; color:var(--accent); display:flex; align-items:center; gap:6px; margin-top:2px;">
                                <span>📍 {{ $t->unit_label }}</span>
                                @if($t->has_ev_charger)
                                    <span class="badge" style="background:rgba(56,189,248,0.15); color:#38bdf8; font-size:0.68rem; padding:2px 6px;">
                                        🚗⚡ EV Charger
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td style="padding:12px 14px;">
                            <div>
                                <span style="font-family:monospace; font-weight:600; color:#facc15;">
                                    {{ $t->nomor_meter_listrik ?? '—' }}
                                </span>
                                @if($t->daya_listrik)
                                    <span style="font-size:0.72rem; color:var(--text-muted); margin-left:4px;">({{ (float)$t->daya_listrik }} kVA)</span>
                                @endif
                            </div>
                            @if($t->has_ev_charger)
                                <div style="margin-top:4px; font-size:0.75rem; color:#38bdf8;">
                                    <span style="font-family:monospace;">EV: {{ $t->nomor_meter_ev ?? 'KWH-EV' }}</span>
                                    @if($t->daya_ev)
                                        <span style="font-size:0.7rem; color:var(--text-muted);">({{ (float)$t->daya_ev }} kVA)</span>
                                    @endif
                                </div>
                            @endif
                        </td>
                        <td style="padding:12px 14px; text-align:right; font-family:monospace;">
                            <div style="color:var(--text-secondary);">{{ $n($row->stand_awal_regular) }}</div>
                            @if($t->has_ev_charger)
                                <div style="font-size:0.75rem; color:#38bdf8; margin-top:2px;">
                                    {{ $n($row->stand_awal_ev) }}
                                </div>
                            @endif
                        </td>
                        <td style="padding:12px 14px; text-align:right; font-family:monospace; font-weight:700;">
                            <div style="color:#facc15;">
                                {{ $row->stand_akhir_regular !== null ? $n($row->stand_akhir_regular) : '—' }}
                            </div>
                            @if($t->has_ev_charger)
                                <div style="font-size:0.75rem; color:#38bdf8; margin-top:2px;">
                                    {{ $row->stand_akhir_ev !== null ? $n($row->stand_akhir_ev) : '—' }}
                                </div>
                            @endif
                        </td>
                        <td style="padding:12px 14px; text-align:right; font-weight:700;">
                            <div style="color:{{ $row->pemakaian_regular > 0 ? 'var(--success)' : 'var(--text-muted)' }};">
                                {{ $row->is_recorded ? $n($row->pemakaian_regular) . ' kWh' : '—' }}
                            </div>
                            @if($t->has_ev_charger)
                                <div style="font-size:0.75rem; color:#38bdf8; margin-top:2px;">
                                    {{ $row->ev_reading ? $n($row->pemakaian_ev) . ' kWh' : '—' }}
                                </div>
                            @endif
                        </td>
                        <td style="padding:12px 14px; text-align:center;">
                            <div style="display:flex; justify-content:center; gap:4px;">
                                @if($row->foto_url_regular)
                                    <a href="#" data-lightbox="{{ $row->foto_url_regular }}" data-caption="Foto Meter Listrik: {{ $t->nama_tenant }} ({{ $namaBulan }} {{ $year }})" style="display:inline-block;" title="Foto Unit Utama">
                                        <img src="{{ $row->foto_url_regular }}" alt="Foto Meter" style="width:32px; height:32px; object-fit:cover; border-radius:4px; border:1px solid var(--border-color);">
                                    </a>
                                @endif
                                @if($row->foto_url_ev)
                                    <a href="#" data-lightbox="{{ $row->foto_url_ev }}" data-caption="Foto Meter EV Charger: {{ $t->nama_tenant }} ({{ $namaBulan }} {{ $year }})" style="display:inline-block;" title="Foto EV Charger">
                                        <img src="{{ $row->foto_url_ev }}" alt="Foto EV" style="width:32px; height:32px; object-fit:cover; border-radius:4px; border:1px solid #38bdf8;">
                                    </a>
                                @endif
                                @if(!$row->foto_url_regular && !$row->foto_url_ev)
                                    <span style="color:var(--text-muted); font-size:0.75rem;">—</span>
                                @endif
                            </div>
                        </td>
                        <td style="padding:12px 14px;">
                            <div style="font-size:0.8rem; color:var(--text-primary); font-weight:500;">{{ $row->petugas }}</div>
                            <div style="font-size:0.72rem; color:var(--text-muted);">{{ $row->tanggal }}</div>
                        </td>
                        <td style="padding:12px 14px; text-align:center;">
                            @if($row->is_fully_recorded)
                                <span class="badge badge-ok" style="font-size:0.72rem;">Lengkap</span>
                            @elseif($row->is_recorded)
                                <span class="badge" style="background:rgba(234,179,8,0.15); color:#facc15; font-size:0.72rem;">Sebagian</span>
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
                                <div style="display:inline-flex; flex-direction:column; gap:4px; align-items:flex-end;">
                                    <div style="display:inline-flex; gap:4px;">
                                        <a href="{{ route('billing.energy.create', ['tenant_id' => $t->id, 'meter_type' => 'regular', 'year' => $year, 'month' => $month]) }}" class="btn btn-sm {{ $row->is_recorded ? '' : 'btn-primary' }}" style="padding:3px 8px; font-size:0.75rem;">
                                            {{ $row->is_recorded ? 'Ubah' : 'Input' }}
                                        </a>
                                        @if($t->has_ev_charger)
                                            <a href="{{ route('billing.energy.create', ['tenant_id' => $t->id, 'meter_type' => 'ev', 'year' => $year, 'month' => $month]) }}" class="btn btn-sm" style="padding:3px 8px; font-size:0.75rem; color:#38bdf8; border-color:rgba(56,189,248,0.4);">
                                                {{ $row->ev_reading ? 'Ubah EV' : '+ EV' }}
                                            </a>
                                        @endif
                                    </div>
                                    @if($row->regular_reading || $row->ev_reading)
                                        <div style="display:inline-flex; gap:4px;">
                                            @if($row->regular_reading)
                                                <form method="POST" action="{{ route('billing.energy.destroy', $row->regular_reading) }}" onsubmit="return confirm('Hapus data stand listrik reguler ini?')" style="display:inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm" style="color:var(--danger); padding:2px 6px; font-size:0.7rem;" title="Hapus Reguler">
                                                        Hapus
                                                    </button>
                                                </form>
                                            @endif
                                            @if($row->ev_reading)
                                                <form method="POST" action="{{ route('billing.energy.destroy', $row->ev_reading) }}" onsubmit="return confirm('Hapus data stand EV charger ini?')" style="display:inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm" style="color:#f87171; padding:2px 6px; font-size:0.7rem;" title="Hapus EV">
                                                        Hapus EV
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            @else
                                {{-- Aksi untuk Role Teknisi: Hanya Input Baru saat Belum Tercatat, TIDAK BISA Ubah/Hapus --}}
                                @if($row->is_fully_recorded)
                                    <span class="badge badge-ok" style="font-size:0.72rem; padding:4px 8px;" title="Data stand sudah tersimpan dan terkunci. Pengubahan hanya oleh Super Admin/SPV.">
                                        ✓ Tercatat
                                    </span>
                                @elseif($row->is_recorded)
                                    @if($t->has_ev_charger && ! $row->ev_reading && ($setting->is_open ?? true))
                                        <a href="{{ route('billing.energy.create', ['tenant_id' => $t->id, 'meter_type' => 'ev', 'year' => $year, 'month' => $month]) }}" class="btn btn-sm btn-primary" style="padding:3px 8px; font-size:0.75rem;">
                                            + Input EV
                                        </a>
                                    @else
                                        <span class="badge badge-ok" style="font-size:0.72rem; padding:4px 8px;">✓ Tercatat</span>
                                    @endif
                                @else
                                    @if($setting->is_open ?? true)
                                        <a href="{{ route('billing.energy.create', ['tenant_id' => $t->id, 'meter_type' => 'regular', 'year' => $year, 'month' => $month]) }}" class="btn btn-sm btn-primary" style="padding:3px 8px; font-size:0.75rem;">
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
                        <td style="padding:12px 14px; text-align:right; font-family:monospace; color:#facc15; font-size:1.05rem;">
                            {{ $n($stats['total_kwh']) }} kWh
                        </td>
                        <td colspan="4" style="padding:12px 14px;"></td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>

    {{-- Navigasi Paginasi Halaman Bawah --}}
    <div class="kpr-pagination-bottom" id="billing-energy-pagination-bottom"></div>
</div>
@endsection

@push('scripts')
<script>
    if (typeof window.initKprTablePagination === 'function') {
        window.initKprTablePagination({
            containerTopId: 'billing-energy-pagination-top',
            containerBottomId: 'billing-energy-pagination-bottom',
            rowSelector: '.table tbody tr.billing-energy-row',
            defaultPerPage: 10,
            perPageOptions: [10, 20, 30, 'all'],
            itemName: 'tenant listrik',
            storageKey: 'kpr_billing_energy_per_page'
        });
    }
</script>
@endpush
