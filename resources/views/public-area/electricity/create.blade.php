@extends('layouts.panel')

@php
    $n = fn ($v) => $v !== null ? number_format($v, 2, ',', '.') : '—';
@endphp

@section('title', 'Input KWh Meter Public Area')
@section('page-title', 'Pencatatan KWh Meter Public Area')
@section('page-subtitle', 'Stand Listrik Daya & Penerangan Area Publik (' . now()->translatedFormat('l, d F Y') . ')')

@section('panel')
<form method="POST" action="{{ route('public-area.electricity.store') }}" enctype="multipart/form-data" class="panel form-offline-pa-electricity" data-offline-type="public_area_electricity" data-meter-label="KWh Meter Public Area" data-target-redirect="{{ route('public-area.electricity.index') }}" id="form-pa-elec-single">
    @csrf

    <div class="panel-head">
        <div>
            <h2><span style="color:#fbbf24;">⚡</span> Pencatatan KWh Meter Public Area</h2>
            <p>Masukkan angka stand kWh meteran listrik penerangan &amp; daya area publik gedung serta lampirkan foto fisik meteran.</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('public-area.electricity.index') }}" class="btn btn-sm" style="background:rgba(255,255,255,0.06); color:var(--text-secondary);">
                ← Kembali ke Daftar KWh Meter
            </a>
        </div>
    </div>

    @if($errors->any())
        <div class="status-banner warn" style="margin-bottom:20px;">
            Periksa kembali isian formulir — terdapat {{ $errors->count() }} kesalahan input.
        </div>
    @endif

    <div class="top-fields">
        <div class="field">
            <label>Tanggal Pencatatan</label>
            <div class="input" style="font-weight:600; color:var(--text-primary); background:rgba(255,255,255,0.03);">
                {{ now()->translatedFormat('l, d F Y') }}
            </div>
        </div>
        <div class="field">
            <label>Pencatatan Sebelumnya</label>
            <div class="input" style="opacity:.85;">
                @if ($last)
                    {{ $last->tanggal->translatedFormat('d F Y') }} (Stand: {{ $n($last->stand_meter) }} kWh)
                @else
                    Belum ada riwayat stand sebelumnya
                @endif
            </div>
        </div>
    </div>

    <div class="meter-section elec" style="margin-top:20px;">
        <h3>
            <span class="ico"><svg viewBox="0 0 20 20" fill="currentColor"><path d="M11.983 1.907a.75.75 0 0 0-1.292-.657l-8.5 9.5A.75.75 0 0 0 2.75 12h6.572l-1.305 6.093a.75.75 0 0 0 1.292.657l8.5-9.5A.75.75 0 0 0 17.25 8h-6.572l1.305-6.093Z"/></svg></span>
            Angka Stand KWh Meter
        </h3>

        <div class="field">
            <label for="stand_meter">Stand KWh Meter <span class="req">*</span></label>
            <div class="input-unit">
                <input type="number" step="0.01" min="0" inputmode="decimal" id="stand_meter" name="stand_meter" class="input input-stand-meter" value="{{ old('stand_meter', $reading->stand_meter) }}" data-prev="{{ $last?->stand_meter }}" placeholder="0,00" required>
                <span>kWh</span>
            </div>
            @if ($last)<p class="hint">Stand sebelumnya: <b>{{ $n($last->stand_meter) }} kWh</b></p>@endif
            <p class="calc-diff-hint" style="font-size:0.78rem; color:var(--accent-light); margin-top:4px;"></p>
            @error('stand_meter')<p class="err">{{ $message }}</p>@enderror
        </div>

        <div class="field photo-upload-group">
            <label style="font-size:0.82rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; display:block; margin-bottom:6px;">
                Foto Fisik Meteran <span class="req" style="color:#ef4444;">{{ $reading->foto ? '(Opsional ganti)' : '*' }}</span>
            </label>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:8px;">
                {{-- Opsi 1: Dari Galeri / File HP --}}
                <label class="btn-pick-gallery" style="display:flex; align-items:center; justify-content:center; gap:8px; background:var(--bg-card-subtle); border:1px solid var(--border); padding:10px 14px; border-radius:8px; cursor:pointer; font-size:0.85rem; color:var(--text-secondary); transition:all 0.2s; text-align:center;">
                    <svg viewBox="0 0 20 20" fill="currentColor" style="width:18px;height:18px;color:var(--info);flex-shrink:0;"><path fill-rule="evenodd" d="M1 5.25A2.25 2.25 0 0 1 3.25 3h13.5A2.25 2.25 0 0 1 19 5.25v9.5A2.25 2.25 0 0 1 16.75 17H3.25A2.25 2.25 0 0 1 1 14.75v-9.5Zm1.5 5.81v3.69c0 .414.336.75.75.75h13.5a.75.75 0 0 0 .75-.75v-2.69l-2.22-2.219a.75.75 0 0 0-1.06 0l-1.91 1.909-4.22-4.219a.75.75 0 0 0-1.06 0L2.5 11.06Zm10.25-3.81a1.25 1.25 0 1 1-2.5 0 1.25 1.25 0 0 1 2.5 0Z" clip-rule="evenodd"/></svg>
                    <span>🖼️ Pilih dari Galeri</span>
                    <input type="file" name="foto" accept="image/*" class="input-file-main" style="display:none;" {{ $reading->foto ? '' : 'required' }}>
                </label>

                {{-- Opsi 2: Kamera Langsung --}}
                <label class="btn-pick-camera" style="display:flex; align-items:center; justify-content:center; gap:8px; background:var(--bg-card-subtle); border:1px solid var(--border); padding:10px 14px; border-radius:8px; cursor:pointer; font-size:0.85rem; color:var(--text-secondary); transition:all 0.2s; text-align:center;">
                    <svg viewBox="0 0 20 20" fill="currentColor" style="width:18px;height:18px;color:var(--warning);flex-shrink:0;"><path fill-rule="evenodd" d="M1 8a2 2 0 0 1 2-2h.93a2 2 0 0 0 1.664-.89l.812-1.22A2 2 0 0 1 8.07 3h3.86a2 2 0 0 1 1.664.89l.812 1.22A2 2 0 0 0 16.07 6H17a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8Zm13.5 3a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM10 14a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" clip-rule="evenodd"/></svg>
                    <span>📷 Buka Kamera</span>
                    <input type="file" accept="image/*" capture="environment" class="input-file-camera" style="display:none;">
                </label>
            </div>

            {{-- Pratinjau Foto --}}
            <div class="photo-preview-wrap" style="{{ $reading->foto ? 'display:flex;' : 'display:none;' }} margin-top:10px; align-items:center; gap:12px; background:var(--bg-card-subtle); padding:10px 14px; border-radius:8px; border:1px solid var(--border);">
                <img class="img-preview" src="{{ $reading->foto ? asset('storage/'.$reading->foto) : '' }}" alt="Preview" style="width:56px; height:56px; object-fit:cover; border-radius:6px; border:1px solid var(--border);">
                <div style="flex:1; overflow:hidden;">
                    <div class="photo-preview-name" style="font-size:0.84rem; color:var(--text-primary); font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                        {{ $reading->foto ? 'Foto tersimpan sebelumnya' : '' }}
                    </div>
                    <div class="photo-preview-status" style="font-size:0.75rem; color:var(--success);">✓ Foto siap dilampirkan</div>
                </div>
                <button type="button" class="btn-clear-photo" style="background:none; border:none; color:#ef4444; font-size:1.2rem; cursor:pointer;" title="Batalkan foto">✕</button>
            </div>
            @error('foto')<p class="err">{{ $message }}</p>@enderror
        </div>

        <div class="field">
            <label for="catatan">Catatan Meteran (opsional)</label>
            <textarea id="catatan" name="catatan" rows="2" class="input" placeholder="Contoh: kondisi fisik baik, MCB panel normal...">{{ old('catatan', $reading->catatan) }}</textarea>
        </div>
    </div>

    <div class="form-foot">
        <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
            <a href="{{ route('public-area.electricity.index') }}" class="btn">Batal</a>
            <span style="font-size:0.78rem; color:var(--text-muted); display:inline-flex; align-items:center; gap:5px;">
                <span style="color:#fbbf24;">⚡</span> Tersimpan otomatis di HP jika tanpa sinyal
            </span>
        </div>
        <button type="submit" class="btn btn-primary" style="background:linear-gradient(135deg,#fbbf24,#d97706); border:none; color:#090d15; font-weight:700;">
            <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd"/></svg>
            Simpan Stand KWh Meter
        </button>
    </div>
</form>
@endsection

@push('scripts')
<script>
    const inp = document.getElementById('stand_meter');
    const hint = document.querySelector('.calc-diff-hint');
    if (inp && hint) {
        const update = () => {
            const prev = parseFloat(inp.dataset.prev);
            const cur = parseFloat(inp.value);
            if (isNaN(cur) || isNaN(prev)) { hint.textContent = ''; return; }
            const diff = cur - prev;
            if (diff < 0) {
                hint.innerHTML = `<span style="color:#ef4444;">⚠ Stand lebih kecil dari sebelumnya (${diff.toFixed(2)} kWh)</span>`;
            } else {
                hint.innerHTML = `<span style="color:#34d399;">Pemakaian: <b>+${diff.toFixed(2)} kWh</b></span>`;
            }
        };
        inp.addEventListener('input', update);
        update();
    }

    // Dukungan input foto: Kamera Langsung atau Pilih dari Galeri / File
    document.querySelectorAll('.photo-upload-group').forEach(group => {
        const mainInput = group.querySelector('.input-file-main');
        const cameraInput = group.querySelector('.input-file-camera');
        const previewWrap = group.querySelector('.photo-preview-wrap');
        const imgPreview = group.querySelector('.img-preview');
        const namePreview = group.querySelector('.photo-preview-name');
        const clearBtn = group.querySelector('.btn-clear-photo');

        function handleFile(file, source) {
            if (!file) return;
            if (imgPreview) {
                imgPreview.src = URL.createObjectURL(file);
            }
            if (namePreview) {
                const srcLabel = source === 'camera' ? '📷 Kamera' : '🖼️ Galeri';
                namePreview.textContent = `${srcLabel}: ${file.name}`;
            }
            if (previewWrap) {
                previewWrap.style.display = 'flex';
            }
            if (source === 'camera' && mainInput) {
                try {
                    const dt = new DataTransfer();
                    dt.items.add(file);
                    mainInput.files = dt.files;
                } catch(e) {}
            }
        }

        if (mainInput) {
            mainInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    handleFile(this.files[0], 'gallery');
                }
            });
        }

        if (cameraInput) {
            cameraInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    handleFile(this.files[0], 'camera');
                }
            });
        }

        if (clearBtn) {
            clearBtn.addEventListener('click', function() {
                if (mainInput) mainInput.value = '';
                if (cameraInput) cameraInput.value = '';
                if (previewWrap) previewWrap.style.display = 'none';
                if (imgPreview) imgPreview.src = '';
                if (namePreview) namePreview.textContent = '';
            });
        }
    });
</script>
@endpush
