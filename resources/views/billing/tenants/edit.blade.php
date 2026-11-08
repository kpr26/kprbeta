@extends('layouts.panel')

@section('title', 'Edit Tenant - Billing System')
@section('page-title', 'Billing System: Edit Tenant')
@section('page-subtitle', 'Perbarui Data Profil & Meteran Tenant ' . $tenant->nama_tenant)

@section('panel')
<div class="panel" style="max-width:800px; margin:0 auto;">
    <div class="panel-head">
        <div>
            <h2>Edit Data Tenant: {{ $tenant->nama_tenant }}</h2>
            <p>Perbarui profil tenant, lantai/unit, nomor seri meter, atau faktor kali.</p>
        </div>
        <a href="{{ route('billing.tenants.index') }}" class="btn btn-sm">
            &larr; Kembali ke Daftar Tenant
        </a>
    </div>

    <form method="POST" action="{{ route('billing.tenants.update', $tenant) }}" style="margin-top:20px;">
        @csrf
        @method('PUT')

        <!-- 1. IDENTITAS TENANT -->
        <div style="background:var(--bg-card); border:1px solid var(--border-color); border-radius:var(--radius-md); padding:18px; margin-bottom:20px;">
            <h3 style="font-size:0.95rem; font-weight:700; color:var(--accent); text-transform:uppercase; margin:0 0 16px; letter-spacing:0.04em;">
                1. Profil &amp; Lokasi Unit Tenant
            </h3>

            <div class="form-row" style="display:grid; grid-template-columns:2fr 1fr; gap:16px; margin-bottom:14px;">
                <div>
                    <label style="font-size:0.82rem; font-weight:600; color:var(--text-secondary); display:block; margin-bottom:6px;">Nama Tenant / Perusahaan: *</label>
                    <input type="text" name="nama_tenant" class="form-control" value="{{ old('nama_tenant', $tenant->nama_tenant) }}" required>
                    @error('nama_tenant')<span style="color:var(--danger); font-size:0.75rem;">{{ $message }}</span>@enderror
                </div>
                <div>
                    <label style="font-size:0.82rem; font-weight:600; color:var(--text-secondary); display:block; margin-bottom:6px;">Kode Tenant:</label>
                    <input type="text" name="kode_tenant" class="form-control" value="{{ old('kode_tenant', $tenant->kode_tenant) }}">
                    @error('kode_tenant')<span style="color:var(--danger); font-size:0.75rem;">{{ $message }}</span>@enderror
                </div>
            </div>

            <div class="form-row" style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                <div>
                    <label style="font-size:0.82rem; font-weight:600; color:var(--text-secondary); display:block; margin-bottom:6px;">Lantai Gedung: *</label>
                    <input type="text" name="lantai" class="form-control" value="{{ old('lantai', $tenant->lantai) }}" required>
                    @error('lantai')<span style="color:var(--danger); font-size:0.75rem;">{{ $message }}</span>@enderror
                </div>
                <div>
                    <label style="font-size:0.82rem; font-weight:600; color:var(--text-secondary); display:block; margin-bottom:6px;">Nomor Unit / Ruangan:</label>
                    <input type="text" name="nomor_unit" class="form-control" value="{{ old('nomor_unit', $tenant->nomor_unit) }}">
                    @error('nomor_unit')<span style="color:var(--danger); font-size:0.75rem;">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>

        <!-- 2. SPESIFIKASI METERAN LISTRIK & AIR -->
        <div style="background:var(--bg-card); border:1px solid var(--border-color); border-radius:var(--radius-md); padding:18px; margin-bottom:20px;">
            <h3 style="font-size:0.95rem; font-weight:700; color:var(--accent); text-transform:uppercase; margin:0 0 16px; letter-spacing:0.04em;">
                2. Spesifikasi Meter Listrik &amp; Air
            </h3>

            <!-- Listrik -->
            <div style="background:var(--bg-main); border:1px solid rgba(234,179,8,0.3); border-radius:var(--radius-sm); padding:14px; margin-bottom:14px;">
                <div style="font-size:0.85rem; font-weight:700; color:#facc15; margin-bottom:10px; display:flex; align-items:center; gap:6px;">
                    <span>⚡ Meteran Listrik Unit Utama (kWh)</span>
                </div>
                <div class="form-row" style="display:grid; grid-template-columns:1.5fr 1fr 1fr; gap:12px;">
                    <div>
                        <label style="font-size:0.78rem; font-weight:600; color:var(--text-muted); display:block; margin-bottom:4px;">No. Seri / ID Meter Listrik:</label>
                        <input type="text" name="nomor_meter_listrik" class="form-control" value="{{ old('nomor_meter_listrik', $tenant->nomor_meter_listrik) }}">
                    </div>
                    <div>
                        <label style="font-size:0.78rem; font-weight:600; color:var(--text-muted); display:block; margin-bottom:4px;">Daya Listrik (kVA):</label>
                        <input type="number" step="0.01" name="daya_listrik" class="form-control" value="{{ old('daya_listrik', $tenant->daya_listrik ?: round($tenant->daya_listrik_va / 1000, 2)) }}">
                    </div>
                    <div>
                        <label style="font-size:0.78rem; font-weight:600; color:var(--text-muted); display:block; margin-bottom:4px;">Faktor Kali (CT):</label>
                        <input type="number" step="0.0001" name="faktor_kali_listrik" class="form-control" value="{{ old('faktor_kali_listrik', (float)$tenant->faktor_kali_listrik) }}">
                    </div>
                </div>
            </div>

            <!-- EV Charger (Fasilitas Mobil Listrik) -->
            <div style="background:var(--bg-main); border:1px solid rgba(56,189,248,0.3); border-radius:var(--radius-sm); padding:14px; margin-bottom:14px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                    <div style="font-size:0.85rem; font-weight:700; color:#38bdf8; display:flex; align-items:center; gap:6px;">
                        <span>🚗⚡ Fasilitas Tambahan: EV Charger</span>
                    </div>
                    <label style="display:inline-flex; align-items:center; gap:6px; font-size:0.8rem; font-weight:600; cursor:pointer;">
                        <input type="checkbox" name="has_ev_charger" value="1" {{ old('has_ev_charger', $tenant->has_ev_charger) ? 'checked' : '' }} onchange="document.getElementById('evFieldsWrap').style.display = this.checked ? 'grid' : 'none'">
                        <span>Aktifkan EV Charger</span>
                    </label>
                </div>
                <div id="evFieldsWrap" class="form-row" style="display:{{ old('has_ev_charger', $tenant->has_ev_charger) ? 'grid' : 'none' }}; grid-template-columns:1.5fr 1fr; gap:12px;">
                    <div>
                        <label style="font-size:0.78rem; font-weight:600; color:var(--text-muted); display:block; margin-bottom:4px;">No. Seri / ID Meter EV:</label>
                        <input type="text" name="nomor_meter_ev" class="form-control" value="{{ old('nomor_meter_ev', $tenant->nomor_meter_ev) }}" placeholder="Contoh: KWH-17-DE-EV">
                    </div>
                    <div>
                        <label style="font-size:0.78rem; font-weight:600; color:var(--text-muted); display:block; margin-bottom:4px;">Daya EV Charger (kVA):</label>
                        <input type="number" step="0.01" name="daya_ev" class="form-control" value="{{ old('daya_ev', $tenant->daya_ev) }}" placeholder="Contoh: 33.00">
                    </div>
                </div>
            </div>

            <!-- Air -->
            <div style="background:var(--bg-main); border:1px solid rgba(56,189,248,0.3); border-radius:var(--radius-sm); padding:14px;">
                <div style="font-size:0.85rem; font-weight:700; color:#38bdf8; margin-bottom:10px; display:flex; align-items:center; gap:6px;">
                    <span>💧 Water Meter (m³)</span>
                </div>
                <div class="form-row" style="display:grid; grid-template-columns:2fr 1fr; gap:12px;">
                    <div>
                        <label style="font-size:0.78rem; font-weight:600; color:var(--text-muted); display:block; margin-bottom:4px;">No. Seri / ID Water Meter:</label>
                        <input type="text" name="nomor_meter_air" class="form-control" value="{{ old('nomor_meter_air', $tenant->nomor_meter_air) }}">
                    </div>
                    <div>
                        <label style="font-size:0.78rem; font-weight:600; color:var(--text-muted); display:block; margin-bottom:4px;">Faktor Kali Air:</label>
                        <input type="number" step="0.0001" name="faktor_kali_air" class="form-control" value="{{ old('faktor_kali_air', (float)$tenant->faktor_kali_air) }}">
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. KONTAK & STATUS -->
        <div style="background:var(--bg-card); border:1px solid var(--border-color); border-radius:var(--radius-md); padding:18px; margin-bottom:20px;">
            <h3 style="font-size:0.95rem; font-weight:700; color:var(--accent); text-transform:uppercase; margin:0 0 16px; letter-spacing:0.04em;">
                3. Kontak Person &amp; Status
            </h3>

            <div class="form-row" style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:14px; margin-bottom:14px;">
                <div>
                    <label style="font-size:0.78rem; font-weight:600; color:var(--text-muted); display:block; margin-bottom:4px;">Nama PIC / Kontak Person:</label>
                    <input type="text" name="kontak_person" class="form-control" value="{{ old('kontak_person', $tenant->kontak_person) }}">
                </div>
                <div>
                    <label style="font-size:0.78rem; font-weight:600; color:var(--text-muted); display:block; margin-bottom:4px;">Nomor Telepon / WhatsApp:</label>
                    <input type="text" name="no_telepon" class="form-control" value="{{ old('no_telepon', $tenant->no_telepon) }}">
                </div>
                <div>
                    <label style="font-size:0.78rem; font-weight:600; color:var(--text-muted); display:block; margin-bottom:4px;">Email Penagihan:</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $tenant->email) }}">
                </div>
            </div>

            <div style="margin-bottom:14px;">
                <label style="font-size:0.78rem; font-weight:600; color:var(--text-muted); display:block; margin-bottom:4px;">Catatan Khusus:</label>
                <textarea name="catatan" rows="2" class="form-control">{{ old('catatan', $tenant->catatan) }}</textarea>
            </div>

            <div>
                <label style="display:inline-flex; align-items:center; gap:8px; cursor:pointer;">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $tenant->is_active) ? 'checked' : '' }}>
                    <span style="font-size:0.875rem; font-weight:600; color:var(--text-primary);">Tenant Berstatus Aktif</span>
                </label>
            </div>
        </div>

        <div style="display:flex; justify-content:flex-end; gap:12px;">
            <a href="{{ route('billing.tenants.index') }}" class="btn btn-sm">Batal</a>
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </div>
    </form>
</div>
@endsection
