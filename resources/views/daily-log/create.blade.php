@extends('layouts.panel')

@php
    $authUser = auth()->user();
@endphp

@section('title', 'Input Daily Log Book')
@section('page-title', 'Input Daily Log Book')
@section('page-subtitle', 'Pencatatan Pekerjaan Harian Teknisi dengan Bukti Foto Ber-Watermark')

@push('styles')
<style>
.daily-log-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}
@media (max-width: 768px) {
    .daily-log-grid {
        grid-template-columns: 1fr;
    }
}
.status-radio-group {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    margin-top: 6px;
}
.status-radio-card {
    position: relative;
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 16px;
    border-radius: var(--radius-md);
    background: rgba(255, 255, 255, 0.04);
    border: 1px solid var(--border);
    cursor: pointer;
    transition: all var(--transition);
}
.status-radio-card input {
    position: absolute;
    opacity: 0;
}
.status-radio-card:hover {
    background: var(--bg-card-hover);
}
.status-radio-card.active-done {
    background: rgba(16, 185, 129, 0.15);
    border-color: #10b981;
    box-shadow: 0 0 16px rgba(16, 185, 129, 0.25);
}
.status-radio-card.active-continue {
    background: rgba(245, 158, 11, 0.15);
    border-color: #f59e0b;
    box-shadow: 0 0 16px rgba(245, 158, 11, 0.25);
}
.status-radio-indicator {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    border: 2px solid var(--text-muted);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: all var(--transition);
}
.status-radio-card.active-done .status-radio-indicator {
    border-color: #10b981;
    background: #10b981;
}
.status-radio-card.active-continue .status-radio-indicator {
    border-color: #f59e0b;
    background: #f59e0b;
}
.status-radio-indicator::after {
    content: '';
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #fff;
    display: none;
}
.status-radio-card.active-done .status-radio-indicator::after,
.status-radio-card.active-continue .status-radio-indicator::after {
    display: block;
}

/* Photo Upload Cards */
.photo-slot-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
    margin-top: 14px;
}
.photo-slot-card {
    background: var(--bg-card-subtle);
    border: 1px dashed var(--border);
    border-radius: 16px;
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 12px;
    transition: all var(--transition);
    position: relative;
}
.photo-slot-card:hover {
    border-color: var(--accent-light);
    background: var(--bg-card-hover);
}
.photo-slot-card.has-photo {
    border-style: solid;
    border-color: var(--success);
    background: var(--bg-card);
}
.photo-slot-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.photo-slot-badge {
    font-size: 0.72rem;
    font-weight: 700;
    padding: 4px 10px;
    border-radius: 20px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}
.photo-badge-awal {
    background: rgba(56, 189, 248, 0.15);
    color: var(--info);
    border: 1px solid rgba(56, 189, 248, 0.3);
}
.photo-badge-proses {
    background: rgba(245, 158, 11, 0.15);
    color: var(--warning);
    border: 1px solid rgba(245, 158, 11, 0.3);
}
.photo-badge-akhir {
    background: rgba(16, 185, 129, 0.15);
    color: var(--success);
    border: 1px solid rgba(16, 185, 129, 0.3);
}

.photo-preview-wrap {
    width: 100%;
    aspect-ratio: 4 / 3;
    border-radius: 12px;
    background: var(--bg-card-subtle);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    position: relative;
    border: 1px solid var(--border);
}
.photo-preview-wrap img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
.photo-empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    color: var(--text-muted);
    font-size: 0.8rem;
    text-align: center;
    padding: 16px;
}
.photo-empty-state svg {
    width: 40px;
    height: 40px;
    opacity: 0.5;
}
.photo-actions-group {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
}
.btn-photo-action {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 8px 12px;
    font-size: 0.78rem;
    font-weight: 600;
    border-radius: var(--radius-sm);
    cursor: pointer;
    border: 1px solid var(--border);
    background: var(--bg-card-subtle);
    color: var(--text-primary);
    transition: all var(--transition);
    text-decoration: none;
}
.btn-photo-action:hover {
    background: var(--bg-card-hover);
    transform: translateY(-1px);
}
.btn-camera {
    background: rgba(99, 102, 241, 0.15);
    color: var(--primary);
    border-color: rgba(99, 102, 241, 0.35);
}
.btn-camera:hover {
    background: rgba(99, 102, 241, 0.28);
    color: var(--primary);
}
.watermark-notice-badge {
    position: absolute;
    bottom: 8px;
    right: 8px;
    background: var(--bg-card);
    border: 1px solid var(--border);
    color: var(--info);
    padding: 3px 8px;
    border-radius: 6px;
    font-size: 0.68rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 4px;
    backdrop-filter: blur(8px);
}
</style>
@endpush

@section('panel')
<div class="panel">
    <div class="panel-head">
        <div>
            <h2><span style="color:#10b981;">📋</span> Form Daily Log Book</h2>
            <p>Petugas: <b style="color:var(--info);">{{ $authUser->name }}</b> &middot; Catat pekerjaan harian dan lampirkan minimal 3 foto dengan watermark otomatis.</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('daily-log.index') }}" class="btn btn-sm">
                ← Kembali ke Riwayat
            </a>
        </div>
    </div>

    @if ($errors->any())
        <div class="status-banner warn" style="margin-bottom: 24px;">
            <span><span class="dot"></span>Terdapat <b>{{ $errors->count() }} kesalahan</b> pada form pengisian. Silakan periksa kolom isian di bawah ini.</span>
        </div>
    @endif

    <form method="POST" action="{{ route('daily-log.store') }}" enctype="multipart/form-data" id="dailyLogForm">
        @csrf

        {{-- Real hidden file inputs for form submission (binary multipart, bukan base64) --}}
        <input type="file" name="foto_awal" id="real_foto_awal" style="display:none;">
        <input type="file" name="foto_proses" id="real_foto_proses" style="display:none;">
        <input type="file" name="foto_akhir" id="real_foto_akhir" style="display:none;">

        <input type="hidden" name="foto_awal_is_watermarked" id="foto_awal_is_watermarked" value="1">
        <input type="hidden" name="foto_proses_is_watermarked" id="foto_proses_is_watermarked" value="1">
        <input type="hidden" name="foto_akhir_is_watermarked" id="foto_akhir_is_watermarked" value="1">

        <div class="daily-log-grid">
            {{-- KOLOM KIRI: Data Waktu & Tempat --}}
            <div>
                <div class="field" style="margin-bottom:18px;">
                    <label for="tanggal">Hari &amp; Tanggal Pekerjaan <span class="req">*</span></label>
                    <input type="date" id="tanggal" name="tanggal" class="input" value="{{ old('tanggal', $defaultDate) }}" required>
                    <p class="hint" id="tanggalHelper" style="color:#38bdf8; font-weight:500; margin-top:4px;">
                        {{ now()->translatedFormat('l, d F Y') }}
                    </p>
                    @error('tanggal')<p class="err">{{ $message }}</p>@enderror
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom:18px;">
                    <div class="field">
                        <label for="jam_mulai">Jam Start (Mulai) <span class="req">*</span></label>
                        <input type="time" id="jam_mulai" name="jam_mulai" class="input" value="{{ old('jam_mulai', $defaultStartTime) }}" required>
                        @error('jam_mulai')<p class="err">{{ $message }}</p>@enderror
                    </div>
                    <div class="field">
                        <label for="jam_selesai">Jam Selesai <span class="req">*</span></label>
                        <input type="time" id="jam_selesai" name="jam_selesai" class="input" value="{{ old('jam_selesai', $defaultEndTime) }}" required>
                        @error('jam_selesai')<p class="err">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="field" style="margin-bottom:18px;">
                    <label for="lokasi">Lokasi Pekerjaan <span class="req">*</span></label>
                    <input type="text" id="lokasi" name="lokasi" class="input" list="lokasiList" placeholder="Contoh: Ruang Genset Basement, Panel Distribusi Lt 2..." value="{{ old('lokasi') }}" required>
                    <datalist id="lokasiList">
                        <option value="Ruang Genset Basement">
                        <option value="Ruang Panel Utama (MDP)">
                        <option value="Ruang Trafo & Gardu Listrik">
                        <option value="Ruang Pompa Air & Roof Tank">
                        <option value="Ruang Chiller & Cooling Tower">
                        <option value="Ruang AHU Lantai 1">
                        <option value="Ruang AHU Lantai 2">
                        <option value="Ruang AHU Lantai 3">
                        <option value="Area Lobby Utama">
                        <option value="Area Koridor & Tangga Darurat">
                        <option value="Area Parkir & Pos Keamanan">
                        <option value="Rooftop Gedung">
                    </datalist>
                    @error('lokasi')<p class="err">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- KOLOM KANAN: Status & Deskripsi --}}
            <div>
                <div class="field" style="margin-bottom:18px;">
                    <label>Status Pekerjaan <span class="req">*</span></label>
                    <div class="status-radio-group">
                        <label class="status-radio-card active-done" id="labelStatusDone">
                            <input type="radio" name="status" value="Done" {{ old('status', 'Done') === 'Done' ? 'checked' : '' }} onchange="updateStatusCard(this)">
                            <div class="status-radio-indicator"></div>
                            <div>
                                <strong style="color:#10b981; font-size:0.95rem; display:block;">Done</strong>
                                <span style="font-size:0.75rem; color:var(--text-muted);">Pekerjaan selesai tuntas</span>
                            </div>
                        </label>
                        <label class="status-radio-card" id="labelStatusContinue">
                            <input type="radio" name="status" value="Continue" {{ old('status') === 'Continue' ? 'checked' : '' }} onchange="updateStatusCard(this)">
                            <div class="status-radio-indicator"></div>
                            <div>
                                <strong style="color:#f59e0b; font-size:0.95rem; display:block;">Continue</strong>
                                <span style="font-size:0.75rem; color:var(--text-muted);">Lanjut besok / shift berikutnya</span>
                            </div>
                        </label>
                    </div>
                    @error('status')<p class="err">{{ $message }}</p>@enderror
                </div>

                <div class="field" style="margin-bottom:18px;">
                    <label for="deskripsi">Deskripsi Pekerjaan <span class="req">*</span></label>
                    <textarea id="deskripsi" name="deskripsi" rows="3" class="input" placeholder="Jelaskan secara rinci tindakan, pemeriksaan, atau perbaikan yang dilakukan..." required>{{ old('deskripsi') }}</textarea>
                    @error('deskripsi')<p class="err">{{ $message }}</p>@enderror
                </div>

                <div class="field" style="margin-bottom:18px;">
                    <label for="catatan">Catatan Tambahan (Opsional)</label>
                    <input type="text" id="catatan" name="catatan" class="input" placeholder="Contoh: Perlu penggantian sparepart seal pompa bulan depan..." value="{{ old('catatan') }}">
                </div>
            </div>
        </div>

        {{-- SECTION FOTO PEKERJAAN (MINIMAL 3 FOTO: AWAL - PROSES - AKHIR) --}}
        <div style="margin-top: 24px; padding-top: 24px; border-top: 1px solid var(--border);">
            <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px; margin-bottom:6px;">
                <div>
                    <h3 style="font-size:1.05rem; font-weight:700; color:var(--text-primary); display:flex; align-items:center; gap:8px;">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="22" height="22" style="color:#fbbf24;">
                            <path d="M12 9a3.75 3.75 0 1 0 0 7.5A3.75 3.75 0 0 0 12 9Z" />
                            <path fill-rule="evenodd" d="M9.344 3.071a49.52 49.52 0 0 1 5.312 0c.967.052 1.83.585 2.332 1.39l.821 1.317c.24.383.645.643 1.11.71.386.054.77.113 1.152.177 1.432.239 2.429 1.493 2.429 2.909V18a3 3 0 0 1-3 3h-15a3 3 0 0 1-3-3V9.574c0-1.416.997-2.67 2.429-2.909.382-.064.766-.123 1.151-.178a1.56 1.56 0 0 0 1.11-.71l.822-1.315a2.942 2.942 0 0 1 2.332-1.39ZM6.75 12.75a5.25 5.25 0 1 1 10.5 0 5.25 5.25 0 0 1-10.5 0Zm12-1.5a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Z" clip-rule="evenodd" />
                        </svg>
                        Foto Dokumentasi Pekerjaan <span class="req">* (Wajib Minimal 3 Foto)</span>
                    </h3>
                    <p style="font-size:0.83rem; color:var(--text-secondary); margin-top:2px;">
                        Foto otomatis dibubuhi <b>watermark tanggal, jam, lokasi, dan tahap pekerjaan</b> sesuai inputan di atas.
                    </p>
                </div>
                <div class="badge badge-wait" style="font-size:0.75rem; padding:4px 10px;">
                    Watermark Terpasang Otomatis
                </div>
            </div>

            <div class="photo-slot-grid">
                {{-- 1. FOTO AWAL --}}
                <div class="photo-slot-card" id="slotCard_awal">
                    <div class="photo-slot-header">
                        <span class="photo-slot-badge photo-badge-awal">1. Foto Awal</span>
                        <small style="color:var(--text-muted); font-size:0.75rem;">Sebelum dikerjakan</small>
                    </div>

                    <div class="photo-preview-wrap" id="previewWrap_awal">
                        <div class="photo-empty-state" id="emptyState_awal">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                <path fill-rule="evenodd" d="M1.5 6a2.25 2.25 0 0 1 2.25-2.25h16.5A2.25 2.25 0 0 1 22.5 6v12a2.25 2.25 0 0 1-2.25 2.25H3.75A2.25 2.25 0 0 1 1.5 18V6ZM3 16.06V18c0 .414.336.75.75.75h16.5A.75.75 0 0 0 21 18v-1.94l-2.69-2.689a1.5 1.5 0 0 0-2.12 0l-.88.879.97.97a.75.75 0 1 1-1.06 1.06l-5.16-5.159a1.5 1.5 0 0 0-2.12 0L3 16.061Zm10.125-7.81a1.125 1.125 0 1 1 2.25 0 1.125 1.125 0 0 1-2.25 0Z" clip-rule="evenodd" />
                            </svg>
                            <span>Belum ada foto awal</span>
                        </div>
                        <img id="imgPreview_awal" style="display:none;" alt="Preview Foto Awal">
                        <div class="watermark-notice-badge" id="wmBadge_awal" style="display:none;">
                            ✓ Watermarked
                        </div>
                    </div>

                    <div class="photo-actions-group">
                        {{-- Ambil Kamera Langsung --}}
                        <label class="btn-photo-action btn-camera" title="Buka Kamera HP">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="16" height="16">
                                <path d="M10 12.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z" />
                                <path fill-rule="evenodd" d="M3.75 4A2.75 2.75 0 0 0 1 6.75v8.5A2.75 2.75 0 0 0 3.75 18h12.5A2.75 2.75 0 0 0 19 15.25v-8.5A2.75 2.75 0 0 0 16.25 4h-1.636a.75.75 0 0 1-.6-.3l-.84-1.12A2.75 2.75 0 0 0 11.008 1.5H8.992a2.75 2.75 0 0 0-2.166 1.08l-.84 1.12a.75.75 0 0 1-.6.3H3.75ZM10 14a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" clip-rule="evenodd" />
                            </svg>
                            <span>Kamera</span>
                            <input type="file" accept="image/*" capture="environment" style="display:none;" onchange="handlePhotoSelect(this, 'awal', 'Foto Awal')">
                        </label>

                        {{-- Pilih dari Galeri / File --}}
                        <label class="btn-photo-action" title="Pilih dari Galeri atau File">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="16" height="16">
                                <path fill-rule="evenodd" d="M1 5.25A2.25 2.25 0 0 1 3.25 3h13.5A2.25 2.25 0 0 1 19 5.25v9.5A2.25 2.25 0 0 1 16.75 17H3.25A2.25 2.25 0 0 1 1 14.75v-9.5Zm1.5 5.81v3.69c0 .414.336.75.75.75h13.5a.75.75 0 0 0 .75-.75v-1.69l-3.375-3.375a1.125 1.125 0 0 0-1.59 0l-1.035 1.035-4.47-4.47a1.125 1.125 0 0 0-1.59 0L2.5 11.06ZM10.5 7.5a1.5 1.5 0 1 1 3 0 1.5 1.5 0 0 1-3 0Z" clip-rule="evenodd" />
                            </svg>
                            <span>Galeri / File</span>
                            <input type="file" id="file_foto_awal" accept="image/*" style="display:none;" onchange="handlePhotoSelect(this, 'awal', 'Foto Awal')">
                        </label>
                    </div>

                    <button type="button" class="btn btn-sm btn-danger" id="btnRemove_awal" style="display:none; width:100%; justify-content:center;" onclick="removePhoto('awal')">
                        Hapus Foto Awal
                    </button>
                    @error('foto_awal')<p class="err">{{ $message }}</p>@enderror
                </div>

                {{-- 2. FOTO PROSES --}}
                <div class="photo-slot-card" id="slotCard_proses">
                    <div class="photo-slot-header">
                        <span class="photo-slot-badge photo-badge-proses">2. Foto Proses</span>
                        <small style="color:var(--text-muted); font-size:0.75rem;">Saat dikerjakan</small>
                    </div>

                    <div class="photo-preview-wrap" id="previewWrap_proses">
                        <div class="photo-empty-state" id="emptyState_proses">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                <path fill-rule="evenodd" d="M1.5 6a2.25 2.25 0 0 1 2.25-2.25h16.5A2.25 2.25 0 0 1 22.5 6v12a2.25 2.25 0 0 1-2.25 2.25H3.75A2.25 2.25 0 0 1 1.5 18V6ZM3 16.06V18c0 .414.336.75.75.75h16.5A.75.75 0 0 0 21 18v-1.94l-2.69-2.689a1.5 1.5 0 0 0-2.12 0l-.88.879.97.97a.75.75 0 1 1-1.06 1.06l-5.16-5.159a1.5 1.5 0 0 0-2.12 0L3 16.061Zm10.125-7.81a1.125 1.125 0 1 1 2.25 0 1.125 1.125 0 0 1-2.25 0Z" clip-rule="evenodd" />
                            </svg>
                            <span>Belum ada foto proses</span>
                        </div>
                        <img id="imgPreview_proses" style="display:none;" alt="Preview Foto Proses">
                        <div class="watermark-notice-badge" id="wmBadge_proses" style="display:none;">
                            ✓ Watermarked
                        </div>
                    </div>

                    <div class="photo-actions-group">
                        <label class="btn-photo-action btn-camera" title="Buka Kamera HP">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="16" height="16">
                                <path d="M10 12.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z" />
                                <path fill-rule="evenodd" d="M3.75 4A2.75 2.75 0 0 0 1 6.75v8.5A2.75 2.75 0 0 0 3.75 18h12.5A2.75 2.75 0 0 0 19 15.25v-8.5A2.75 2.75 0 0 0 16.25 4h-1.636a.75.75 0 0 1-.6-.3l-.84-1.12A2.75 2.75 0 0 0 11.008 1.5H8.992a2.75 2.75 0 0 0-2.166 1.08l-.84 1.12a.75.75 0 0 1-.6.3H3.75ZM10 14a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" clip-rule="evenodd" />
                            </svg>
                            <span>Kamera</span>
                            <input type="file" accept="image/*" capture="environment" style="display:none;" onchange="handlePhotoSelect(this, 'proses', 'Foto Proses')">
                        </label>

                        <label class="btn-photo-action" title="Pilih dari Galeri atau File">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="16" height="16">
                                <path fill-rule="evenodd" d="M1 5.25A2.25 2.25 0 0 1 3.25 3h13.5A2.25 2.25 0 0 1 19 5.25v9.5A2.25 2.25 0 0 1 16.75 17H3.25A2.25 2.25 0 0 1 1 14.75v-9.5Zm1.5 5.81v3.69c0 .414.336.75.75.75h13.5a.75.75 0 0 0 .75-.75v-1.69l-3.375-3.375a1.125 1.125 0 0 0-1.59 0l-1.035 1.035-4.47-4.47a1.125 1.125 0 0 0-1.59 0L2.5 11.06ZM10.5 7.5a1.5 1.5 0 1 1 3 0 1.5 1.5 0 0 1-3 0Z" clip-rule="evenodd" />
                            </svg>
                            <span>Galeri / File</span>
                            <input type="file" id="file_foto_proses" accept="image/*" style="display:none;" onchange="handlePhotoSelect(this, 'proses', 'Foto Proses')">
                        </label>
                    </div>

                    <button type="button" class="btn btn-sm btn-danger" id="btnRemove_proses" style="display:none; width:100%; justify-content:center;" onclick="removePhoto('proses')">
                        Hapus Foto Proses
                    </button>
                    @error('foto_proses')<p class="err">{{ $message }}</p>@enderror
                </div>

                {{-- 3. FOTO AKHIR --}}
                <div class="photo-slot-card" id="slotCard_akhir">
                    <div class="photo-slot-header">
                        <span class="photo-slot-badge photo-badge-akhir">3. Foto Akhir</span>
                        <small style="color:var(--text-muted); font-size:0.75rem;">Selesai / Hasil</small>
                    </div>

                    <div class="photo-preview-wrap" id="previewWrap_akhir">
                        <div class="photo-empty-state" id="emptyState_akhir">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                <path fill-rule="evenodd" d="M1.5 6a2.25 2.25 0 0 1 2.25-2.25h16.5A2.25 2.25 0 0 1 22.5 6v12a2.25 2.25 0 0 1-2.25 2.25H3.75A2.25 2.25 0 0 1 1.5 18V6ZM3 16.06V18c0 .414.336.75.75.75h16.5A.75.75 0 0 0 21 18v-1.94l-2.69-2.689a1.5 1.5 0 0 0-2.12 0l-.88.879.97.97a.75.75 0 1 1-1.06 1.06l-5.16-5.159a1.5 1.5 0 0 0-2.12 0L3 16.061Zm10.125-7.81a1.125 1.125 0 1 1 2.25 0 1.125 1.125 0 0 1-2.25 0Z" clip-rule="evenodd" />
                            </svg>
                            <span>Belum ada foto akhir</span>
                        </div>
                        <img id="imgPreview_akhir" style="display:none;" alt="Preview Foto Akhir">
                        <div class="watermark-notice-badge" id="wmBadge_akhir" style="display:none;">
                            ✓ Watermarked
                        </div>
                    </div>

                    <div class="photo-actions-group">
                        <label class="btn-photo-action btn-camera" title="Buka Kamera HP">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="16" height="16">
                                <path d="M10 12.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z" />
                                <path fill-rule="evenodd" d="M3.75 4A2.75 2.75 0 0 0 1 6.75v8.5A2.75 2.75 0 0 0 3.75 18h12.5A2.75 2.75 0 0 0 19 15.25v-8.5A2.75 2.75 0 0 0 16.25 4h-1.636a.75.75 0 0 1-.6-.3l-.84-1.12A2.75 2.75 0 0 0 11.008 1.5H8.992a2.75 2.75 0 0 0-2.166 1.08l-.84 1.12a.75.75 0 0 1-.6.3H3.75ZM10 14a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" clip-rule="evenodd" />
                            </svg>
                            <span>Kamera</span>
                            <input type="file" accept="image/*" capture="environment" style="display:none;" onchange="handlePhotoSelect(this, 'akhir', 'Foto Akhir')">
                        </label>

                        <label class="btn-photo-action" title="Pilih dari Galeri atau File">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="16" height="16">
                                <path fill-rule="evenodd" d="M1 5.25A2.25 2.25 0 0 1 3.25 3h13.5A2.25 2.25 0 0 1 19 5.25v9.5A2.25 2.25 0 0 1 16.75 17H3.25A2.25 2.25 0 0 1 1 14.75v-9.5Zm1.5 5.81v3.69c0 .414.336.75.75.75h13.5a.75.75 0 0 0 .75-.75v-1.69l-3.375-3.375a1.125 1.125 0 0 0-1.59 0l-1.035 1.035-4.47-4.47a1.125 1.125 0 0 0-1.59 0L2.5 11.06ZM10.5 7.5a1.5 1.5 0 1 1 3 0 1.5 1.5 0 0 1-3 0Z" clip-rule="evenodd" />
                            </svg>
                            <span>Galeri / File</span>
                            <input type="file" id="file_foto_akhir" accept="image/*" style="display:none;" onchange="handlePhotoSelect(this, 'akhir', 'Foto Akhir')">
                        </label>
                    </div>

                    <button type="button" class="btn btn-sm btn-danger" id="btnRemove_akhir" style="display:none; width:100%; justify-content:center;" onclick="removePhoto('akhir')">
                        Hapus Foto Akhir
                    </button>
                    @error('foto_akhir')<p class="err">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        {{-- SUBMIT BUTTON --}}
        <div style="margin-top: 32px; display: flex; align-items: center; justify-content: flex-end; gap: 12px; flex-wrap: wrap;">
            <a href="{{ route('daily-log.index') }}" class="btn" style="background:rgba(255,255,255,0.05);">
                Batal
            </a>
            <button type="submit" class="btn btn-primary" id="btnSubmitDailyLog" style="background:linear-gradient(135deg, #10b981, #059669); padding:12px 28px; font-size:0.95rem;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="18" height="18">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd" />
                </svg>
                Simpan Daily Log Book
            </button>
        </div>
    </form>
</div>

{{-- Offscreen Canvas untuk Memproses Watermark --}}
<canvas id="watermarkCanvas" style="display:none;"></canvas>
@endsection

@push('scripts')
<script>
    const technicianName = @json($authUser->name);

    // Update status radio card active style
    function updateStatusCard(radio) {
        const cardDone = document.getElementById('labelStatusDone');
        const cardContinue = document.getElementById('labelStatusContinue');
        if (radio.value === 'Done') {
            cardDone.classList.add('active-done');
            cardContinue.classList.remove('active-continue');
        } else {
            cardDone.classList.remove('active-done');
            cardContinue.classList.add('active-continue');
        }
        refreshAllWatermarks();
    }

    // Dynamic indonesian day formatting
    const tanggalInput = document.getElementById('tanggal');
    const tanggalHelper = document.getElementById('tanggalHelper');
    const daysIndo = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    const monthsIndo = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

    function formatTanggalIndo(dateStr) {
        if (!dateStr) return '';
        const d = new Date(dateStr + 'T00:00:00');
        if (isNaN(d.getTime())) return dateStr;
        return `${daysIndo[d.getDay()]}, ${d.getDate()} ${monthsIndo[d.getMonth()]} ${d.getFullYear()}`;
    }

    tanggalInput.addEventListener('change', function() {
        tanggalHelper.textContent = formatTanggalIndo(this.value);
        refreshAllWatermarks();
    });

    document.getElementById('jam_mulai').addEventListener('change', refreshAllWatermarks);
    document.getElementById('jam_selesai').addEventListener('change', refreshAllWatermarks);
    document.getElementById('lokasi').addEventListener('input', refreshAllWatermarks);

    // State cache original image elements for re-watermarking when inputs change
    const originalImages = {
        awal: null,
        proses: null,
        akhir: null
    };

    // Blobs menyimpan file biner hasil watermark (bukan string base64)
    const watermarkBlobs = {
        awal: null,
        proses: null,
        akhir: null
    };

    function handlePhotoSelect(input, slotKey, stageTitle) {
        if (!input.files || !input.files[0]) return;
        const file = input.files[0];

        const reader = new FileReader();
        reader.onload = function(e) {
            const img = new Image();
            img.onload = function() {
                originalImages[slotKey] = img;
                renderWatermarkForSlot(slotKey, stageTitle);
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    }

    function renderWatermarkForSlot(slotKey, stageTitle) {
        const img = originalImages[slotKey];
        if (!img) return;

        const tanggalVal = tanggalInput.value;
        const tanggalFormatted = formatTanggalIndo(tanggalVal) || tanggalVal;
        const jamMulai = document.getElementById('jam_mulai').value || '08:00';
        const jamSelesai = document.getElementById('jam_selesai').value || '17:00';
        const jamText = `${jamMulai} - ${jamSelesai} WIB`;
        const lokasi = document.getElementById('lokasi').value || 'Lokasi Pekerjaan';
        const statusSelected = document.querySelector('input[name="status"]:checked')?.value || 'Done';

        const canvas = document.getElementById('watermarkCanvas');
        const ctx = canvas.getContext('2d');

        // Scale to maximum dimension of 1600px
        const maxDim = 1600;
        let width = img.width;
        let height = img.height;
        if (width > maxDim || height > maxDim) {
            const ratio = Math.min(maxDim / width, maxDim / height);
            width = Math.round(width * ratio);
            height = Math.round(height * ratio);
        }

        canvas.width = width;
        canvas.height = height;

        // Draw source image
        ctx.drawImage(img, 0, 0, width, height);

        // ==========================================
        // WATERMARK KARTU POJOK KANAN BAWAH (STYLE APLIKASI MARKI)
        // ==========================================
        const baseFontSize = Math.max(14, Math.round(width * 0.018));
        const padX = Math.round(baseFontSize * 1.0);
        const padY = Math.round(baseFontSize * 0.85);
        const barWidth = Math.max(4, Math.round(baseFontSize * 0.22));
        const innerLeft = padX + barWidth + Math.round(baseFontSize * 0.45);

        const maxCardWidth = Math.min(Math.round(width * 0.85), Math.max(320, Math.round(width * 0.58)));
        const maxTextW = maxCardWidth - innerLeft - padX;

        function fitText(text, maxW) {
            if (ctx.measureText(text).width <= maxW) return text;
            let t = text;
            while (t.length > 4 && ctx.measureText(t + '...').width > maxW) {
                t = t.slice(0, -1);
            }
            return t + '...';
        }

        const stageName = stageTitle ? stageTitle.toUpperCase() : `FOTO ${slotKey.toUpperCase()}`;
        const line1 = `KPR BETA · ${stageName} [${statusSelected.toUpperCase()}]`;
        const line2 = `📅 ${tanggalFormatted}`;
        const line3 = `⏰ ${jamText}`;

        ctx.font = `500 ${Math.round(baseFontSize * 0.9)}px 'Inter', sans-serif`;
        const line4 = fitText(`📍 ${lokasi}`, maxTextW);
        const line5 = fitText(`👤 ${technicianName}`, maxTextW);

        // Ukur panjang teks untuk lebar kartu yang pas & rapi
        ctx.font = `bold ${Math.round(baseFontSize * 0.92)}px 'Inter', sans-serif`;
        const w1 = ctx.measureText(line1).width;
        ctx.font = `600 ${baseFontSize}px 'Inter', sans-serif`;
        const w2 = ctx.measureText(line2).width;
        const w3 = ctx.measureText(line3).width;
        ctx.font = `500 ${Math.round(baseFontSize * 0.9)}px 'Inter', sans-serif`;
        const w4 = ctx.measureText(line4).width;
        const w5 = ctx.measureText(line5).width;

        const contentW = Math.max(w1, w2, w3, w4, w5);
        const cardWidth = Math.min(maxCardWidth, Math.max(260, contentW + innerLeft + padX));

        const lineSpacing = Math.round(baseFontSize * 1.34);
        const totalLines = 5;
        const cardHeight = (padY * 2) + (lineSpacing * (totalLines - 1)) + Math.round(baseFontSize * 0.5);

        // Posisi Pojok Kanan Bawah dengan margin
        const margin = Math.max(16, Math.round(width * 0.02));
        const cardX = width - cardWidth - margin;
        const cardY = height - cardHeight - margin;
        const radius = Math.max(8, Math.round(baseFontSize * 0.5));

        // Helper rounded rectangle cross-browser
        function drawRoundedRect(cx, cy, cw, ch, cr) {
            ctx.beginPath();
            if (typeof ctx.roundRect === 'function') {
                ctx.roundRect(cx, cy, cw, ch, cr);
            } else {
                ctx.moveTo(cx + cr, cy);
                ctx.lineTo(cx + cw - cr, cy);
                ctx.quadraticCurveTo(cx + cw, cy, cx + cw, cy + cr);
                ctx.lineTo(cx + cw, cy + ch - cr);
                ctx.quadraticCurveTo(cx + cw, cy + ch, cx + cw - cr, cy + ch);
                ctx.lineTo(cx + cr, cy + ch);
                ctx.quadraticCurveTo(cx, cy + ch, cx, cy + ch - cr);
                ctx.lineTo(cx, cy + cr);
                ctx.quadraticCurveTo(cx, cy, cx + cr, cy);
                ctx.closePath();
            }
        }

        ctx.save();

        // 1. Kotak Kartu Dark Translucent (Marki Style)
        drawRoundedRect(cardX, cardY, cardWidth, cardHeight, radius);
        ctx.fillStyle = 'rgba(10, 15, 29, 0.82)';
        ctx.fill();
        ctx.strokeStyle = 'rgba(255, 255, 255, 0.22)';
        ctx.lineWidth = Math.max(1, Math.round(width * 0.0012));
        ctx.stroke();

        // 2. Bar Aksen Vertikal di Kiri Kartu (Hijau untuk Done, Amber untuk Continue)
        const accentColor = statusSelected === 'Done' ? '#10b981' : '#f59e0b';
        const barMarginY = Math.max(6, Math.round(padY * 0.35));
        ctx.fillStyle = accentColor;
        drawRoundedRect(cardX + padX * 0.38, cardY + barMarginY, barWidth, cardHeight - (barMarginY * 2), barWidth / 2);
        ctx.fill();

        // 3. Teks Watermark
        const textX = cardX + innerLeft;
        let textY = cardY + padY + Math.round(baseFontSize * 0.72);

        ctx.shadowColor = 'rgba(0, 0, 0, 0.85)';
        ctx.shadowBlur = 3;
        ctx.shadowOffsetX = 1;
        ctx.shadowOffsetY = 1;

        // Line 1: Header Tahap & Status
        ctx.font = `bold ${Math.round(baseFontSize * 0.92)}px 'Inter', sans-serif`;
        ctx.fillStyle = '#fbbf24'; // Gold
        ctx.fillText(line1, textX, textY);

        // Line 2: Tanggal
        textY += lineSpacing;
        ctx.font = `600 ${baseFontSize}px 'Inter', sans-serif`;
        ctx.fillStyle = '#ffffff';
        ctx.fillText(line2, textX, textY);

        // Line 3: Jam
        textY += lineSpacing;
        ctx.font = `600 ${Math.round(baseFontSize * 0.95)}px 'Inter', sans-serif`;
        ctx.fillStyle = '#f1f5f9';
        ctx.fillText(line3, textX, textY);

        // Line 4: Lokasi
        textY += lineSpacing;
        ctx.font = `500 ${Math.round(baseFontSize * 0.9)}px 'Inter', sans-serif`;
        ctx.fillStyle = '#38bdf8'; // Sky blue
        ctx.fillText(line4, textX, textY);

        // Line 5: Teknisi
        textY += lineSpacing;
        ctx.font = `500 ${Math.round(baseFontSize * 0.85)}px 'Inter', sans-serif`;
        ctx.fillStyle = '#94a3b8'; // Slate
        ctx.fillText(line5, textX, textY);

        ctx.restore();

        // Export as binary JPEG Blob (tidak menggunakan base64 string besar di form post)
        canvas.toBlob(function(blob) {
            if (!blob) return;
            watermarkBlobs[slotKey] = blob;

            // Update UI Preview
            const objectUrl = URL.createObjectURL(blob);
            const imgElem = document.getElementById(`imgPreview_${slotKey}`);
            imgElem.src = objectUrl;
            imgElem.style.display = 'block';

            document.getElementById(`emptyState_${slotKey}`).style.display = 'none';
            document.getElementById(`wmBadge_${slotKey}`).style.display = 'flex';
            document.getElementById(`btnRemove_${slotKey}`).style.display = 'inline-flex';
            document.getElementById(`slotCard_${slotKey}`).classList.add('has-photo');

            // Set file biner ke real input jika browser mendukung DataTransfer
            try {
                const file = new File([blob], `foto_${slotKey}.jpg`, { type: 'image/jpeg' });
                const dt = new DataTransfer();
                dt.items.add(file);
                document.getElementById(`real_foto_${slotKey}`).files = dt.files;
            } catch (e) {}
        }, 'image/jpeg', 0.85);
    }

    function refreshAllWatermarks() {
        ['awal', 'proses', 'akhir'].forEach(slotKey => {
            if (originalImages[slotKey]) {
                const titles = { awal: 'Foto Awal', proses: 'Foto Proses', akhir: 'Foto Akhir' };
                renderWatermarkForSlot(slotKey, titles[slotKey]);
            }
        });
    }

    function removePhoto(slotKey) {
        originalImages[slotKey] = null;
        watermarkBlobs[slotKey] = null;

        const fileInput = document.getElementById(`real_foto_${slotKey}`);
        if (fileInput) fileInput.value = '';

        const triggerInput = document.getElementById(`file_foto_${slotKey}`);
        if (triggerInput) triggerInput.value = '';

        const imgElem = document.getElementById(`imgPreview_${slotKey}`);
        imgElem.src = '';
        imgElem.style.display = 'none';

        document.getElementById(`emptyState_${slotKey}`).style.display = 'flex';
        document.getElementById(`wmBadge_${slotKey}`).style.display = 'none';
        document.getElementById(`btnRemove_${slotKey}`).style.display = 'none';
        document.getElementById(`slotCard_${slotKey}`).classList.remove('has-photo');
    }

    // Submit form via AJAX fetch Multipart FormData untuk mencegah max_allowed_packet error di database sessions
    document.getElementById('dailyLogForm').addEventListener('submit', function(e) {
        e.preventDefault();

        if (!watermarkBlobs.awal || !watermarkBlobs.proses || !watermarkBlobs.akhir) {
            alert('Wajib melampirkan minimal 3 foto pekerjaan: Foto Awal, Foto Proses, dan Foto Akhir!');
            return false;
        }

        const submitBtn = document.getElementById('btnSubmitDailyLog');
        submitBtn.disabled = true;
        const originalBtnHtml = submitBtn.innerHTML;
        submitBtn.innerHTML = `
            <svg class="spin" style="width:18px;height:18px;animation:spin 1s linear infinite;" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <circle cx="12" cy="12" r="10" stroke-width="4" stroke="rgba(255,255,255,0.2)"></circle>
                <path d="M4 12a8 8 0 0 1 8-8" stroke-width="4" stroke="currentColor"></path>
            </svg>
            Menyimpan Log Book...
        `;

        const formData = new FormData(this);
        // Pastikan foto yang dikirim adalah biner Blob (bukan string base64)
        formData.set('foto_awal', watermarkBlobs.awal, 'foto_awal.jpg');
        formData.set('foto_proses', watermarkBlobs.proses, 'foto_proses.jpg');
        formData.set('foto_akhir', watermarkBlobs.akhir, 'foto_akhir.jpg');

        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(async response => {
            if (response.ok) {
                const data = await response.json();
                window.location.href = data.redirect || '{{ route("daily-log.index") }}';
            } else if (response.status === 422) {
                const errJson = await response.json();
                let errMsg = 'Gagal menyimpan isian:';
                if (errJson.errors) {
                    errMsg += '\n- ' + Object.values(errJson.errors).flat().join('\n- ');
                } else if (errJson.message) {
                    errMsg += '\n' + errJson.message;
                }
                alert(errMsg);
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnHtml;
            } else {
                const errText = await response.text();
                console.error(errText);
                alert('Terjadi kesalahan pada server (Kode: ' + response.status + '). Silakan coba kembali.');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnHtml;
            }
        })
        .catch(err => {
            console.error(err);
            alert('Gagal mengirim data. Silakan periksa koneksi internet Anda dan coba lagi.');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnHtml;
        });
    });
</script>
<style>
@keyframes spin { 100% { transform: rotate(360deg); } }
</style>
@endpush
