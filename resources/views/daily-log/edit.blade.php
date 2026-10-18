@extends('layouts.panel')

@php
    $authUser = auth()->user();
@endphp

@section('title', 'Edit Daily Log Book')
@section('page-title', 'Edit Daily Log Book')
@section('page-subtitle', 'Perbarui Data Pekerjaan Harian #' . $log->id)

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
    background: rgba(15, 23, 42, 0.7);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 12px;
    transition: all var(--transition);
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
    color: #38bdf8;
    border: 1px solid rgba(56, 189, 248, 0.3);
}
.photo-badge-proses {
    background: rgba(245, 158, 11, 0.15);
    color: #fbbf24;
    border: 1px solid rgba(245, 158, 11, 0.3);
}
.photo-badge-akhir {
    background: rgba(16, 185, 129, 0.15);
    color: #34d399;
    border: 1px solid rgba(16, 185, 129, 0.3);
}

.photo-preview-wrap {
    width: 100%;
    aspect-ratio: 4 / 3;
    border-radius: 12px;
    background: #000;
    overflow: hidden;
    position: relative;
    border: 1px solid rgba(255, 255, 255, 0.08);
}
.photo-preview-wrap img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
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
    background: rgba(255, 255, 255, 0.05);
    color: var(--text-primary);
    transition: all var(--transition);
}
.btn-photo-action:hover {
    background: rgba(255, 255, 255, 0.12);
}
.btn-camera {
    background: rgba(99, 102, 241, 0.15);
    color: #818cf8;
    border-color: rgba(99, 102, 241, 0.35);
}
</style>
@endpush

@section('panel')
<div class="panel">
    <div class="panel-head">
        <div>
            <h2>Edit Data Daily Log Book</h2>
            <p>Pekerjaan #{{ $log->id }} &middot; Teknisi: <b style="color:#38bdf8;">{{ $log->user->name }}</b></p>
        </div>
        <div class="btn-group">
            <a href="{{ route('daily-log.show', $log) }}" class="btn btn-sm">
                ← Batal &amp; Kembali
            </a>
        </div>
    </div>

    @if ($errors->any())
        <div class="status-banner warn" style="margin-bottom: 24px;">
            <span><span class="dot"></span>Terdapat <b>{{ $errors->count() }} kesalahan</b> pada form pengisian. Silakan periksa kembali.</span>
        </div>
    @endif

    <form method="POST" action="{{ route('daily-log.update', $log) }}" enctype="multipart/form-data" id="dailyLogEditForm">
        @csrf
        @method('PUT')

        {{-- Real hidden file inputs for form submission (binary multipart, bukan base64) --}}
        <input type="file" name="foto_awal" id="real_foto_awal" style="display:none;">
        <input type="file" name="foto_proses" id="real_foto_proses" style="display:none;">
        <input type="file" name="foto_akhir" id="real_foto_akhir" style="display:none;">

        <input type="hidden" name="foto_awal_is_watermarked" id="foto_awal_is_watermarked" value="1">
        <input type="hidden" name="foto_proses_is_watermarked" id="foto_proses_is_watermarked" value="1">
        <input type="hidden" name="foto_akhir_is_watermarked" id="foto_akhir_is_watermarked" value="1">

        <div class="daily-log-grid">
            <div>
                <div class="field" style="margin-bottom:18px;">
                    <label for="tanggal">Hari &amp; Tanggal Pekerjaan <span class="req">*</span></label>
                    <input type="date" id="tanggal" name="tanggal" class="input" value="{{ old('tanggal', $log->tanggal->toDateString()) }}" required>
                    <p class="hint" id="tanggalHelper" style="color:#38bdf8; font-weight:500; margin-top:4px;">
                        {{ $log->hari_tanggal }}
                    </p>
                    @error('tanggal')<p class="err">{{ $message }}</p>@enderror
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom:18px;">
                    <div class="field">
                        <label for="jam_mulai">Jam Start <span class="req">*</span></label>
                        <input type="time" id="jam_mulai" name="jam_mulai" class="input" value="{{ old('jam_mulai', $log->jam_mulai) }}" required>
                        @error('jam_mulai')<p class="err">{{ $message }}</p>@enderror
                    </div>
                    <div class="field">
                        <label for="jam_selesai">Jam Selesai <span class="req">*</span></label>
                        <input type="time" id="jam_selesai" name="jam_selesai" class="input" value="{{ old('jam_selesai', $log->jam_selesai) }}" required>
                        @error('jam_selesai')<p class="err">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="field" style="margin-bottom:18px;">
                    <label for="lokasi">Lokasi Pekerjaan <span class="req">*</span></label>
                    <input type="text" id="lokasi" name="lokasi" class="input" value="{{ old('lokasi', $log->lokasi) }}" required>
                    @error('lokasi')<p class="err">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <div class="field" style="margin-bottom:18px;">
                    <label>Status Pekerjaan <span class="req">*</span></label>
                    <div class="status-radio-group">
                        <label class="status-radio-card {{ old('status', $log->status) === 'Done' ? 'active-done' : '' }}" id="labelStatusDone">
                            <input type="radio" name="status" value="Done" {{ old('status', $log->status) === 'Done' ? 'checked' : '' }} onchange="updateStatusCard(this)">
                            <div class="status-radio-indicator"></div>
                            <div>
                                <strong style="color:#10b981; font-size:0.95rem; display:block;">Done</strong>
                                <span style="font-size:0.75rem; color:var(--text-muted);">Pekerjaan selesai tuntas</span>
                            </div>
                        </label>
                        <label class="status-radio-card {{ old('status', $log->status) === 'Continue' ? 'active-continue' : '' }}" id="labelStatusContinue">
                            <input type="radio" name="status" value="Continue" {{ old('status', $log->status) === 'Continue' ? 'checked' : '' }} onchange="updateStatusCard(this)">
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
                    <textarea id="deskripsi" name="deskripsi" rows="3" class="input" required>{{ old('deskripsi', $log->deskripsi) }}</textarea>
                    @error('deskripsi')<p class="err">{{ $message }}</p>@enderror
                </div>

                <div class="field" style="margin-bottom:18px;">
                    <label for="catatan">Catatan Tambahan (Opsional)</label>
                    <input type="text" id="catatan" name="catatan" class="input" value="{{ old('catatan', $log->catatan) }}">
                </div>
            </div>
        </div>

        {{-- FOTO PEKERJAAN (BISA DIPERBARUI ATAU PERTAHANKAN YANG SUDAH ADA) --}}
        <div style="margin-top: 24px; padding-top: 24px; border-top: 1px solid var(--border);">
            <div style="margin-bottom:12px;">
                <h3 style="font-size:1.05rem; font-weight:700; color:var(--text-primary);">
                    Foto Dokumentasi Pekerjaan
                </h3>
                <p style="font-size:0.83rem; color:var(--text-secondary); margin-top:2px;">
                    Anda dapat mempertahankan foto saat ini atau mengunggah foto pengganti baru (otomatis ber-watermark).
                </p>
            </div>

            <div class="photo-slot-grid">
                {{-- Foto Awal --}}
                <div class="photo-slot-card">
                    <div class="photo-slot-header">
                        <span class="photo-slot-badge photo-badge-awal">1. Foto Awal</span>
                        <small style="color:var(--text-muted); font-size:0.75rem;">Sebelum dikerjakan</small>
                    </div>
                    <div class="photo-preview-wrap">
                        <img id="imgPreview_awal" src="{{ $log->foto_awal_url }}" alt="Foto Awal">
                    </div>
                    <div class="photo-actions-group">
                        <label class="btn-photo-action btn-camera">
                            <span>Kamera</span>
                            <input type="file" accept="image/*" capture="environment" style="display:none;" onchange="handlePhotoSelect(this, 'awal', 'Foto Awal')">
                        </label>
                        <label class="btn-photo-action">
                            <span>Ganti File</span>
                            <input type="file" id="file_foto_awal" accept="image/*" style="display:none;" onchange="handlePhotoSelect(this, 'awal', 'Foto Awal')">
                        </label>
                    </div>
                </div>

                {{-- Foto Proses --}}
                <div class="photo-slot-card">
                    <div class="photo-slot-header">
                        <span class="photo-slot-badge photo-badge-proses">2. Foto Proses</span>
                        <small style="color:var(--text-muted); font-size:0.75rem;">Saat berlangsung</small>
                    </div>
                    <div class="photo-preview-wrap">
                        <img id="imgPreview_proses" src="{{ $log->foto_proses_url }}" alt="Foto Proses">
                    </div>
                    <div class="photo-actions-group">
                        <label class="btn-photo-action btn-camera">
                            <span>Kamera</span>
                            <input type="file" accept="image/*" capture="environment" style="display:none;" onchange="handlePhotoSelect(this, 'proses', 'Foto Proses')">
                        </label>
                        <label class="btn-photo-action">
                            <span>Ganti File</span>
                            <input type="file" id="file_foto_proses" accept="image/*" style="display:none;" onchange="handlePhotoSelect(this, 'proses', 'Foto Proses')">
                        </label>
                    </div>
                </div>

                {{-- Foto Akhir --}}
                <div class="photo-slot-card">
                    <div class="photo-slot-header">
                        <span class="photo-slot-badge photo-badge-akhir">3. Foto Akhir</span>
                        <small style="color:var(--text-muted); font-size:0.75rem;">Selesai / Tindak Lanjut</small>
                    </div>
                    <div class="photo-preview-wrap">
                        <img id="imgPreview_akhir" src="{{ $log->foto_akhir_url }}" alt="Foto Akhir">
                    </div>
                    <div class="photo-actions-group">
                        <label class="btn-photo-action btn-camera">
                            <span>Kamera</span>
                            <input type="file" accept="image/*" capture="environment" style="display:none;" onchange="handlePhotoSelect(this, 'akhir', 'Foto Akhir')">
                        </label>
                        <label class="btn-photo-action">
                            <span>Ganti File</span>
                            <input type="file" id="file_foto_akhir" accept="image/*" style="display:none;" onchange="handlePhotoSelect(this, 'akhir', 'Foto Akhir')">
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div style="margin-top: 32px; display: flex; align-items: center; justify-content: flex-end; gap: 12px; flex-wrap: wrap;">
            <a href="{{ route('daily-log.show', $log) }}" class="btn" style="background:rgba(255,255,255,0.05);">
                Batal
            </a>
            <button type="submit" class="btn btn-primary" style="background:linear-gradient(135deg, #10b981, #059669); padding:12px 28px;">
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>

<canvas id="watermarkCanvas" style="display:none;"></canvas>
@endsection

@push('scripts')
<script>
    const technicianName = @json($log->user->name);

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

    // State cache original image elements jika foto diganti
    const originalImages = {
        awal: null,
        proses: null,
        akhir: null
    };

    // Blobs menyimpan file biner hasil watermark jika foto diganti
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

    function refreshAllWatermarks() {
        ['awal', 'proses', 'akhir'].forEach(slotKey => {
            if (originalImages[slotKey]) {
                const titles = { awal: 'Foto Awal', proses: 'Foto Proses', akhir: 'Foto Akhir' };
                renderWatermarkForSlot(slotKey, titles[slotKey]);
            }
        });
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

                // Export as binary JPEG Blob
                canvas.toBlob(function(blob) {
                    if (!blob) return;
                    watermarkBlobs[slotKey] = blob;

                    const objectUrl = URL.createObjectURL(blob);
                    const imgElem = document.getElementById(`imgPreview_${slotKey}`);
                    imgElem.src = objectUrl;

                    try {
                        const fileObj = new File([blob], `foto_${slotKey}.jpg`, { type: 'image/jpeg' });
                        const dt = new DataTransfer();
                        dt.items.add(fileObj);
                        document.getElementById(`real_foto_${slotKey}`).files = dt.files;
                    } catch (e) {}
                }, 'image/jpeg', 0.85);
    }

    // Submit form edit via AJAX fetch Multipart FormData
    document.getElementById('dailyLogEditForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const submitBtn = this.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        const originalBtnHtml = submitBtn.innerHTML;
        submitBtn.innerHTML = `
            <svg class="spin" style="width:18px;height:18px;animation:spin 1s linear infinite;vertical-align:middle;margin-right:6px;" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <circle cx="12" cy="12" r="10" stroke-width="4" stroke="rgba(255,255,255,0.2)"></circle>
                <path d="M4 12a8 8 0 0 1 8-8" stroke-width="4" stroke="currentColor"></path>
            </svg>
            Menyimpan Perubahan...
        `;

        const formData = new FormData(this);

        // Jika foto diperbarui, lampirkan Blob binary baru
        if (watermarkBlobs.awal) {
            formData.set('foto_awal', watermarkBlobs.awal, 'foto_awal.jpg');
        }
        if (watermarkBlobs.proses) {
            formData.set('foto_proses', watermarkBlobs.proses, 'foto_proses.jpg');
        }
        if (watermarkBlobs.akhir) {
            formData.set('foto_akhir', watermarkBlobs.akhir, 'foto_akhir.jpg');
        }

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
                window.location.href = data.redirect || '{{ route("daily-log.show", $log) }}';
            } else if (response.status === 422) {
                const errJson = await response.json();
                let errMsg = 'Gagal menyimpan perubahan:';
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
