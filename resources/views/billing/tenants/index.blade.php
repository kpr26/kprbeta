@extends('layouts.panel')

@section('title', 'Daftar Tenant - Billing System')
@section('page-title', 'Billing System: Master Tenant')
@section('page-subtitle', 'Manajemen Data Tenant Gedung, Nomor Meter Listrik & Air')

@section('panel')
<div class="panel">
    <!-- Header -->
    <div class="panel-head">
        <div>
            <h2>Data Tenant &amp; Titik Meteran</h2>
            <p>Kelola data profil tenant, lokasi lantai/unit, nomor meteran kWh &amp; water meter gedung.</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('billing.utilities.hub') }}" class="btn btn-sm">
                ⚙️ Utilities Hub
            </a>
            <a href="{{ route('billing.quick-input') }}" class="btn btn-sm">
                ⚡ Input Cepat Meteran
            </a>
            <a href="{{ route('billing.tenants.create') }}" class="btn btn-primary">
                + Tambah Tenant Baru
            </a>
        </div>
    </div>

    <!-- Filter & Pencarian -->
    <div class="filter-bar" style="margin-bottom:20px; background:var(--bg-card); padding:14px 18px; border-radius:var(--radius-md); border:1px solid var(--border-color); display:flex; flex-wrap:wrap; justify-content:space-between; align-items:center; gap:12px;">
        <form method="GET" action="{{ route('billing.tenants.index') }}" style="display:flex; flex-wrap:wrap; align-items:center; gap:10px; flex:1;">
            <input type="text" name="search" class="form-control" placeholder="Cari nama tenant, unit, atau nomor meter..." value="{{ $search }}" style="min-width:240px; padding:6px 12px; font-size:0.875rem;">
            
            <select name="lantai" class="form-control" style="width:auto; padding:6px 12px; font-size:0.875rem;" onchange="this.form.submit()">
                <option value="all">Semua Lantai</option>
                @foreach($allLantai as $l)
                    <option value="{{ $l }}" {{ $lantai == $l ? 'selected' : '' }}>{{ $l }}</option>
                @endforeach
            </select>

            <select name="status" class="form-control" style="width:auto; padding:6px 12px; font-size:0.875rem;" onchange="this.form.submit()">
                <option value="all" {{ $status == 'all' ? 'selected' : '' }}>Semua Status</option>
                <option value="active" {{ $status == 'active' ? 'selected' : '' }}>Aktif ({{ $stats['active'] }})</option>
                <option value="inactive" {{ $status == 'inactive' ? 'selected' : '' }}>Non-Aktif ({{ $stats['inactive'] }})</option>
            </select>

            <button type="submit" class="btn btn-sm btn-primary">Cari</button>
            @if($search || ($lantai && $lantai !== 'all') || ($status && $status !== 'all'))
                <a href="{{ route('billing.tenants.index') }}" class="btn btn-sm">Reset</a>
            @endif
        </form>

        <div style="font-size:0.82rem; color:var(--text-secondary);">
            Total: <strong style="color:var(--text-primary);">{{ $stats['total'] }}</strong> Tenant ({{ $stats['active'] }} Aktif)
        </div>
    </div>

    <!-- Tabel Data Tenant -->
    <div class="table-container" style="overflow-x:auto;">
        <table class="table" style="width:100%; border-collapse:collapse;">
            <thead>
                <tr style="border-bottom:1px solid var(--border-color); text-align:left; font-size:0.75rem; text-transform:uppercase; color:var(--text-muted); letter-spacing:0.05em;">
                    <th style="padding:12px 14px;">Tenant &amp; Kode</th>
                    <th style="padding:12px 14px;">Lokasi Unit</th>
                    <th style="padding:12px 14px;">Meter Listrik (kWh)</th>
                    <th style="padding:12px 14px;">Water Meter (m³)</th>
                    <th style="padding:12px 14px;">Kontak Person</th>
                    <th style="padding:12px 14px; text-align:center;">Status</th>
                    <th style="padding:12px 14px; text-align:right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tenants as $t)
                    <tr style="border-bottom:1px solid var(--border-color); font-size:0.85rem;">
                        <td style="padding:12px 14px;">
                            <a href="{{ route('billing.tenants.show', $t) }}" style="font-weight:700; color:var(--text-primary); text-decoration:none;" class="hover-accent">
                                {{ $t->nama_tenant }}
                            </a>
                            <div style="font-size:0.72rem; color:var(--text-muted); font-family:monospace;">
                                {{ $t->kode_tenant ?? '—' }}
                            </div>
                        </td>
                        <td style="padding:12px 14px;">
                            <span class="badge" style="background:var(--bg-card-subtle); color:var(--accent); font-weight:600; font-size:0.75rem;">
                                {{ $t->unit_label }}
                            </span>
                        </td>
                        <td style="padding:12px 14px;">
                            @if($t->nomor_meter_listrik)
                                <div style="display:flex; align-items:center; gap:6px;">
                                    <span style="color:#facc15;">⚡</span>
                                    <span style="font-family:monospace; font-weight:600; color:var(--text-primary);">{{ $t->nomor_meter_listrik }}</span>
                                </div>
                                <div style="font-size:0.72rem; color:var(--text-muted);">
                                    {{ $t->daya_listrik_va ? number_format($t->daya_listrik_va, 0, ',', '.') . ' VA' : 'Daya —' }} 
                                    (FK: {{ (float)$t->faktor_kali_listrik }}x)
                                </div>
                            @else
                                <span style="color:var(--text-muted); font-size:0.75rem;">— Tidak ada meter</span>
                            @endif
                        </td>
                        <td style="padding:12px 14px;">
                            @if($t->nomor_meter_air)
                                <div style="display:flex; align-items:center; gap:6px;">
                                    <span style="color:#38bdf8;">💧</span>
                                    <span style="font-family:monospace; font-weight:600; color:var(--text-primary);">{{ $t->nomor_meter_air }}</span>
                                </div>
                                <div style="font-size:0.72rem; color:var(--text-muted);">
                                    Faktor Kali: {{ (float)$t->faktor_kali_air }}x
                                </div>
                            @else
                                <span style="color:var(--text-muted); font-size:0.75rem;">— Tidak ada meter</span>
                            @endif
                        </td>
                        <td style="padding:12px 14px;">
                            <div style="font-weight:500; color:var(--text-primary);">{{ $t->kontak_person ?? '—' }}</div>
                            <div style="font-size:0.72rem; color:var(--text-secondary);">{{ $t->no_telepon ?? ($t->email ?? '') }}</div>
                        </td>
                        <td style="padding:12px 14px; text-align:center;">
                            @if($t->is_active)
                                <span class="badge badge-ok" style="font-size:0.72rem;">Aktif</span>
                            @else
                                <span class="badge badge-wait" style="font-size:0.72rem; background:rgba(239,68,68,0.15); color:#f87171;">Non-Aktif</span>
                            @endif
                        </td>
                        <td style="padding:12px 14px; text-align:right;">
                            <div style="display:inline-flex; gap:6px;">
                                <a href="{{ route('billing.tenants.show', $t) }}" class="btn btn-sm" title="Lihat Profil & Riwayat">
                                    Detail
                                </a>
                                <a href="{{ route('billing.tenants.edit', $t) }}" class="btn btn-sm" title="Ubah Data">
                                    Edit
                                </a>
                                <form method="POST" action="{{ route('billing.tenants.destroy', $t) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus tenant {{ $t->nama_tenant }}? Semua riwayat pembacaan meterannya juga akan terhapus.')" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm" style="color:var(--danger); border-color:rgba(239,68,68,0.3);" title="Hapus Tenant">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="padding:32px; text-align:center; color:var(--text-muted);">
                            Belum ada data tenant yang terdaftar.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($tenants->hasPages())
        <div style="margin-top:20px;">
            {{ $tenants->links() }}
        </div>
    @endif
</div>
@endsection
