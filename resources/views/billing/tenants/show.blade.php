@extends('layouts.panel')

@php
    $n = fn ($v) => $v !== null ? number_format($v, 2, ',', '.') : '—';
@endphp

@section('title', 'Detail Tenant: ' . $tenant->nama_tenant . ' - Billing System')
@section('page-title', 'Billing System: Profil Tenant')
@section('page-subtitle', 'Informasi Detail & Riwayat Konsumsi Bulanan ' . $tenant->nama_tenant)

@section('panel')
<div class="panel">
    <!-- Header -->
    <div class="panel-head">
        <div style="display:flex; align-items:center; gap:12px;">
            <div style="width:48px; height:48px; border-radius:12px; background:var(--accent-glow); display:flex; align-items:center; justify-content:center; color:var(--accent); font-size:1.5rem; font-weight:700;">
                🏢
            </div>
            <div>
                <h2>{{ $tenant->nama_tenant }}</h2>
                <p>📍 {{ $tenant->unit_label }} &bull; Kode: <code>{{ $tenant->kode_tenant ?? '—' }}</code></p>
            </div>
        </div>
        <div class="btn-group">
            <a href="{{ route('billing.tenants.index') }}" class="btn btn-sm">
                &larr; Daftar Tenant
            </a>
            <a href="{{ route('billing.energy.create', ['tenant_id' => $tenant->id]) }}" class="btn btn-sm" style="color:#facc15;">
                + Input Listrik
            </a>
            <a href="{{ route('billing.water.create', ['tenant_id' => $tenant->id]) }}" class="btn btn-sm" style="color:#38bdf8;">
                + Input Air
            </a>
            <a href="{{ route('billing.tenants.edit', $tenant) }}" class="btn btn-primary btn-sm">
                Edit Profil
            </a>
        </div>
    </div>

    <!-- Info Grid Profil & Spesifikasi -->
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(260px, 1fr)); gap:16px; margin:20px 0 30px;">
        <!-- Card 1: Lokasi & Kontak -->
        <div style="background:var(--bg-card); border:1px solid var(--border-color); border-radius:var(--radius-md); padding:16px;">
            <h4 style="font-size:0.8rem; font-weight:700; color:var(--accent); text-transform:uppercase; margin:0 0 10px;">Informasi Lokasi &amp; Kontak</h4>
            <div style="font-size:0.85rem; line-height:1.6;">
                <div>Lantai: <strong style="color:var(--text-primary);">{{ $tenant->lantai }}</strong></div>
                <div>Unit: <strong style="color:var(--text-primary);">{{ $tenant->nomor_unit ?? '—' }}</strong></div>
                <div>PIC: <strong style="color:var(--text-primary);">{{ $tenant->kontak_person ?? '—' }}</strong></div>
                <div>Telepon / WA: <strong style="color:var(--text-primary);">{{ $tenant->no_telepon ?? '—' }}</strong></div>
                <div>Email: <strong style="color:var(--text-primary);">{{ $tenant->email ?? '—' }}</strong></div>
                <div>Status: <span class="badge {{ $tenant->is_active ? 'badge-ok' : 'badge-wait' }}">{{ $tenant->is_active ? 'Aktif' : 'Non-Aktif' }}</span></div>
            </div>
        </div>

        <!-- Card 2: Meter Listrik -->
        <div style="background:var(--bg-card); border:1px solid rgba(234,179,8,0.3); border-radius:var(--radius-md); padding:16px;">
            <h4 style="font-size:0.8rem; font-weight:700; color:#facc15; text-transform:uppercase; margin:0 0 10px;">Spesifikasi Meter Listrik</h4>
            <div style="font-size:0.85rem; line-height:1.6;">
                <div>No. Meter: <strong style="color:#facc15; font-family:monospace;">{{ $tenant->nomor_meter_listrik ?? '— Tidak Ada' }}</strong></div>
                <div>Daya Listrik: <strong style="color:var(--text-primary);">{{ $tenant->daya_listrik_va ? number_format($tenant->daya_listrik_va, 0, ',', '.') . ' VA' : '—' }}</strong></div>
                <div>Faktor Kali (CT): <strong style="color:var(--text-primary);">{{ (float)$tenant->faktor_kali_listrik }}x</strong></div>
                <div>Total Riwayat Dicatat: <strong style="color:var(--text-primary);">{{ $energyReadings->count() }} Bulan</strong></div>
            </div>
        </div>

        <!-- Card 3: Water Meter -->
        <div style="background:var(--bg-card); border:1px solid rgba(56,189,248,0.3); border-radius:var(--radius-md); padding:16px;">
            <h4 style="font-size:0.8rem; font-weight:700; color:#38bdf8; text-transform:uppercase; margin:0 0 10px;">Spesifikasi Water Meter</h4>
            <div style="font-size:0.85rem; line-height:1.6;">
                <div>No. Meter Air: <strong style="color:#38bdf8; font-family:monospace;">{{ $tenant->nomor_meter_air ?? '— Tidak Ada' }}</strong></div>
                <div>Faktor Kali Air: <strong style="color:var(--text-primary);">{{ (float)$tenant->faktor_kali_air }}x</strong></div>
                <div>Total Riwayat Dicatat: <strong style="color:var(--text-primary);">{{ $waterReadings->count() }} Bulan</strong></div>
            </div>
        </div>
    </div>

    <!-- Riwayat Penggunaan Listrik Bulanan (Energy Usage) -->
    <div style="margin-bottom:32px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
            <h3 style="font-size:1.05rem; font-weight:700; color:#facc15; margin:0; display:flex; align-items:center; gap:8px;">
                <span>⚡ Riwayat Penggunaan Listrik Bulanan (kWh)</span>
            </h3>
            <a href="{{ route('billing.energy.create', ['tenant_id' => $tenant->id]) }}" class="btn btn-sm" style="color:#facc15;">
                + Input Listrik
            </a>
        </div>

        <div class="table-container" style="overflow-x:auto;">
            <table class="table" style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="border-bottom:1px solid var(--border-color); font-size:0.75rem; text-transform:uppercase; color:var(--text-muted); text-align:left;">
                        <th style="padding:10px 12px;">Periode</th>
                        <th style="padding:10px 12px;">Tgl Catat</th>
                        <th style="padding:10px 12px; text-align:right;">Stand Awal</th>
                        <th style="padding:10px 12px; text-align:right;">Stand Akhir</th>
                        <th style="padding:10px 12px; text-align:right;">Pemakaian</th>
                        <th style="padding:10px 12px; text-align:center;">Foto Meter</th>
                        <th style="padding:10px 12px;">Petugas</th>
                        <th style="padding:10px 12px;">Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($energyReadings as $er)
                        <tr style="border-bottom:1px solid var(--border-color); font-size:0.85rem;">
                            <td style="padding:10px 12px; font-weight:600; color:var(--text-primary);">{{ $er->periode_label }}</td>
                            <td style="padding:10px 12px; color:var(--text-secondary);">{{ $er->tanggal_catat?->format('d/m/Y') }}</td>
                            <td style="padding:10px 12px; text-align:right; font-family:monospace;">{{ $n($er->stand_awal) }}</td>
                            <td style="padding:10px 12px; text-align:right; font-family:monospace; font-weight:700; color:#facc15;">{{ $n($er->stand_akhir) }}</td>
                            <td style="padding:10px 12px; text-align:right; font-weight:700; color:var(--success);">{{ $n($er->pemakaian) }} kWh</td>
                            <td style="padding:10px 12px; text-align:center;">
                                @if($er->foto_url)
                                    <a href="#" data-lightbox="{{ $er->foto_url }}" data-caption="Foto Meter Listrik: {{ $tenant->nama_tenant }} ({{ $er->periode_label }})" style="display:inline-block;">
                                        <img src="{{ $er->foto_url }}" alt="Foto Meter" style="width:36px; height:36px; object-fit:cover; border-radius:6px; border:1px solid var(--border-color);">
                                    </a>
                                @else
                                    <span style="color:var(--text-muted); font-size:0.75rem;">—</span>
                                @endif
                            </td>
                            <td style="padding:10px 12px; color:var(--text-secondary);">{{ $er->user?->name ?? '—' }}</td>
                            <td style="padding:10px 12px; color:var(--text-muted); font-size:0.8rem;">{{ $er->catatan ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="padding:24px; text-align:center; color:var(--text-muted);">
                                Belum ada riwayat pencatatan listrik untuk tenant ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Riwayat Penggunaan Air Bulanan (Water Usage) -->
    <div>
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
            <h3 style="font-size:1.05rem; font-weight:700; color:#38bdf8; margin:0; display:flex; align-items:center; gap:8px;">
                <span>💧 Riwayat Penggunaan Air Bulanan (m³)</span>
            </h3>
            <a href="{{ route('billing.water.create', ['tenant_id' => $tenant->id]) }}" class="btn btn-sm" style="color:#38bdf8;">
                + Input Air
            </a>
        </div>

        <div class="table-container" style="overflow-x:auto;">
            <table class="table" style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="border-bottom:1px solid var(--border-color); font-size:0.75rem; text-transform:uppercase; color:var(--text-muted); text-align:left;">
                        <th style="padding:10px 12px;">Periode</th>
                        <th style="padding:10px 12px;">Tgl Catat</th>
                        <th style="padding:10px 12px; text-align:right;">Stand Awal</th>
                        <th style="padding:10px 12px; text-align:right;">Stand Akhir</th>
                        <th style="padding:10px 12px; text-align:right;">Pemakaian</th>
                        <th style="padding:10px 12px; text-align:center;">Foto Meter</th>
                        <th style="padding:10px 12px;">Petugas</th>
                        <th style="padding:10px 12px;">Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($waterReadings as $wr)
                        <tr style="border-bottom:1px solid var(--border-color); font-size:0.85rem;">
                            <td style="padding:10px 12px; font-weight:600; color:var(--text-primary);">{{ $wr->periode_label }}</td>
                            <td style="padding:10px 12px; color:var(--text-secondary);">{{ $wr->tanggal_catat?->format('d/m/Y') }}</td>
                            <td style="padding:10px 12px; text-align:right; font-family:monospace;">{{ $n($wr->stand_awal) }}</td>
                            <td style="padding:10px 12px; text-align:right; font-family:monospace; font-weight:700; color:#38bdf8;">{{ $n($wr->stand_akhir) }}</td>
                            <td style="padding:10px 12px; text-align:right; font-weight:700; color:var(--success);">{{ $n($wr->pemakaian) }} m³</td>
                            <td style="padding:10px 12px; text-align:center;">
                                @if($wr->foto_url)
                                    <a href="#" data-lightbox="{{ $wr->foto_url }}" data-caption="Foto Water Meter: {{ $tenant->nama_tenant }} ({{ $wr->periode_label }})" style="display:inline-block;">
                                        <img src="{{ $wr->foto_url }}" alt="Foto Meter" style="width:36px; height:36px; object-fit:cover; border-radius:6px; border:1px solid var(--border-color);">
                                    </a>
                                @else
                                    <span style="color:var(--text-muted); font-size:0.75rem;">—</span>
                                @endif
                            </td>
                            <td style="padding:10px 12px; color:var(--text-secondary);">{{ $wr->user?->name ?? '—' }}</td>
                            <td style="padding:10px 12px; color:var(--text-muted); font-size:0.8rem;">{{ $wr->catatan ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="padding:24px; text-align:center; color:var(--text-muted);">
                                Belum ada riwayat pencatatan air untuk tenant ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
