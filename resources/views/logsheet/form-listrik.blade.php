@extends('layouts.panel')

@php
    $isAdmin = auth()->user()->role === 'admin';
    $canManage = in_array(auth()->user()->role, ['admin', 'eng_admin', 'eng_spv'], true);
    $n = fn ($v) => $v !== null ? number_format($v, 2, ',', '.') : '—';
    $hasRecordedToday = $today && ($today->foto_kwh || $today->stand_total !== null || $today->stand_bp !== null);
@endphp

@section('title', 'Input Logsheet AMR PLN T33N')
@section('page-title', 'Logsheet AMR PLN T33N')
@section('page-subtitle', 'Pencatatan Stand Meter AMR PLN T33N (BP, LBP1, LBP2, Total)')

@section('panel')
<form method="POST" action="{{ route('logsheet.store.listrik') }}" enctype="multipart/form-data" class="panel" id="logsheet-listrik-form">
    @csrf

    <div class="panel-head">
        <div style="display:flex; align-items:center; gap:12px;">
            <div style="width:44px; height:44px; border-radius:10px; background:rgba(255,201,14,0.12); border:1px solid rgba(255,201,14,0.3); padding:3px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                {{-- Logo PLN Indonesia --}}
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" style="width:30px; height:30px;">
                    <polygon points="32,3 61,32 32,61 3,32" fill="#FFC90E" stroke="#D32F2F" stroke-width="2.5" stroke-linejoin="round"/>
                    <path d="M14 36c4-2 7 2 11 0s7-2 11 0 7 2 11 0" fill="none" stroke="#0288D1" stroke-width="2.2" stroke-linecap="round"/>
                    <path d="M16 41c4-2 7 2 11 0s7-2 11 0 7 2 9 0" fill="none" stroke="#0288D1" stroke-width="2.2" stroke-linecap="round"/>
                    <path d="M19 46c3.5-1.8 6.5 1.8 10 0s6.5-1.8 10 0" fill="none" stroke="#0288D1" stroke-width="2.2" stroke-linecap="round"/>
                    <polygon points="36,11 23,33 31,33 26,53 43,28 34,28" fill="#D32F2F"/>
                </svg>
            </div>
            <div>
                <h2>Pencatatan Logsheet AMR PLN T33N</h2>
                <p>Masukkan angka stand BP, LBP 1, LBP 2, dan Total (kWh) yang tertera pada meteran serta lampirkan 1 foto fisik meter.</p>
            </div>
        </div>
        <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
            <a href="{{ route('logsheet.hub') }}" class="btn btn-sm" style="background:rgba(255,255,255,0.06); color:var(--text-secondary);">
                ← Pilihan Logsheet
            </a>
            <a href="{{ route('logsheet.create.air') }}" class="btn btn-sm" style="background:rgba(56,189,248,0.15); color:#38bdf8; border:1px solid rgba(56,189,248,0.3);">
                💧 Pindah ke Input Air
            </a>
            @if (auth()->user()->isSuperAdmin())
                <a href="{{ route('logsheet.manual.listrik') }}" class="btn btn-sm" style="background:rgba(245,158,11,0.15); color:#fbbf24; border:1px solid rgba(245,158,11,0.35);" title="Input tanggal yang sudah terlewat">
                    ⚡ Tanggal Terlewat
                </a>
            @endif
            @if (auth()->user()->role === 'teknisi')
                <span class="badge badge-role-teknisi">Teknisi</span>
            @endif
        </div>
    </div>

    @if ($hasRecordedToday)
        <div class="status-banner ok" style="margin-bottom:24px; display:block;">
            <div style="display:flex; align-items:center; gap:10px; margin-bottom:8px;">
                <span class="dot"></span>
                <strong style="font-size:1.05rem;">Meter AMR PLN T33N Hari Ini Sudah Dicatat</strong>
            </div>
            <p style="margin:0 0 12px 20px; color:var(--text-secondary); font-size:0.88rem; line-height:1.5;">
                @if($today->stand_bp !== null || $today->stand_total !== null)
                    Tercatat Stand BP: <b>{{ $n($today->stand_bp) }} kWh</b> · LBP 1: <b>{{ $n($today->stand_lbp1) }} kWh</b> · LBP 2: <b>{{ $n($today->stand_lbp2) }} kWh</b> · Total: <b>{{ $n($today->stand_total) }} kWh</b>
                @else
                    Tercatat Stand LWBP: <b>{{ $n($today->stand_lwbp) }} kWh</b> · WBP: <b>{{ $n($today->stand_wbp) }} kWh</b>
                @endif
                <br>oleh <b>{{ $today->user?->name ?? 'Teknisi' }}</b> pukul {{ $today->updated_at?->format('H:i') ?? '—' }} WIB.
            </p>
            <div style="margin-left:20px; display:flex; gap:10px; flex-wrap:wrap;">
                @if(!$today->foto_air)
                    <a href="{{ route('logsheet.create.air') }}" class="btn btn-sm btn-primary">Lanjut Input Water Meter Air →</a>
                @endif
                <a href="{{ route('logsheet.show', $today) }}" class="btn btn-sm">Lihat Detail Data</a>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="status-banner warn" style="margin-bottom:20px">
            Periksa kembali isian Anda — terdapat {{ $errors->count() }} kesalahan.
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
            <label>Pencatatan AMR PLN Sebelumnya</label>
            <div class="input" style="opacity:.85; font-size:0.85rem;">
                @if ($last)
                    {{ $last->tanggal->translatedFormat('d F Y') }}
                    @if($last->stand_total !== null)
                        (BP: {{ $n($last->stand_bp) }} · LBP1: {{ $n($last->stand_lbp1) }} · LBP2: {{ $n($last->stand_lbp2) }} · Total: {{ $n($last->stand_total) }})
                    @else
                        (LWBP: {{ $n($last->stand_lwbp) }} · WBP: {{ $n($last->stand_wbp) }})
                    @endif
                @else
                    Belum ada — ini pencatatan pertama
                @endif
            </div>
        </div>
    </div>

    <div class="meter-section elec" style="margin-top:20px;">
        <h3>
            <span class="ico" style="background:rgba(255,201,14,0.12); padding:3px; border-radius:6px; display:inline-flex;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" style="width:20px; height:20px;">
                    <polygon points="32,3 61,32 32,61 3,32" fill="#FFC90E" stroke="#D32F2F" stroke-width="2.5" stroke-linejoin="round"/>
                    <path d="M14 36c4-2 7 2 11 0s7-2 11 0 7 2 11 0" fill="none" stroke="#0288D1" stroke-width="2.2" stroke-linecap="round"/>
                    <path d="M16 41c4-2 7 2 11 0s7-2 11 0 7 2 9 0" fill="none" stroke="#0288D1" stroke-width="2.2" stroke-linecap="round"/>
                    <polygon points="36,11 23,33 31,33 26,53 43,28 34,28" fill="#D32F2F"/>
                </svg>
            </span>
            Angka Stand AMR PLN T33N (kWh)
        </h3>

        @php
            $amrFields = [
                'stand_bp'    => ['label' => 'Stand BP (Beban Puncak)', 'fallback' => 'stand_wbp'],
                'stand_lbp1'  => ['label' => 'Stand LBP 1 (Luar Beban Puncak 1)', 'fallback' => null],
                'stand_lbp2'  => ['label' => 'Stand LBP 2 (Luar Beban Puncak 2)', 'fallback' => null],
                'stand_total' => ['label' => 'Stand Total', 'fallback' => null],
            ];
        @endphp

        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(240px, 1fr)); gap:16px;">
            @foreach ($amrFields as $key => $info)
            @php
                $prevVal = $last ? ($last->$key ?? ($info['fallback'] ? $last->{$info['fallback']} : null)) : null;
            @endphp
            <div class="field" style="margin-bottom:0;">
                <label for="{{ $key }}">{{ $info['label'] }} <span class="req">*</span></label>
                <div class="input-unit">
                    <input type="number" step="0.01" min="0" inputmode="decimal" id="{{ $key }}" name="{{ $key }}" class="input"
                           value="{{ old($key, $reading->$key ?? ($info['fallback'] ? $reading->{$info['fallback']} : null)) }}" 
                           data-prev="{{ $prevVal }}" data-unit="kWh" placeholder="0,00" required>
                    <span>kWh</span>
                </div>
                @if ($prevVal !== null)<p class="hint">Stand sebelumnya: <b>{{ $n($prevVal) }} kWh</b></p>@endif
                <p class="calc" data-calc-for="{{ $key }}"></p>
                @error($key)<p class="err">{{ $message }}</p>@enderror
            </div>
            @endforeach
        </div>

        {{-- 1 Lampiran Foto Fisik Meteran (Seragam dengan Water Meter + Pilihan Kamera & Galeri) --}}
        <div class="field" style="margin-top:20px;">
            <label>Foto Fisik Meteran AMR PLN T33N <span class="req" id="req-foto-kwh">{{ $reading->foto_kwh ? '' : '*' }}</span></label>
            <div class="upload {{ $reading->foto_kwh ? 'has-img' : '' }}" id="upload-box-kwh" role="button" tabindex="0" title="Klik untuk ambil foto dari kamera atau pilih dari galeri">
                <input type="file" name="foto_kwh" id="foto_kwh_gallery" accept="image/*" class="input-file-gallery" style="display:none;" {{ $reading->foto_kwh ? '' : 'required' }}>
                <input type="file" id="foto_kwh_camera" accept="image/*" capture="environment" class="input-file-camera" style="display:none;">
                @if ($reading->foto_kwh)
                    <img class="preview" id="kwh-preview-img" src="{{ asset('storage/'.$reading->foto_kwh) }}" alt="Foto kWh AMR PLN">
                @endif
                <span class="upload-text">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M1 8a2 2 0 0 1 2-2h.93a2 2 0 0 0 1.664-.89l.812-1.22A2 2 0 0 1 8.07 3h3.86a2 2 0 0 1 1.664.89l.812 1.22A2 2 0 0 0 16.07 6H17a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8Zm13.5 3a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM10 14a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" clip-rule="evenodd"/></svg><br>
                    Ambil foto / pilih gambar<br><small>Pastikan angka meter terbaca jelas · maks 5 MB</small>
                </span>
                <span class="change">Ganti foto</span>
            </div>
            @error('foto_kwh')<p class="err">{{ $message }}</p>@enderror
        </div>

        <div class="field">
            <label for="catatan">Catatan Meter Listrik AMR PLN (opsional)</label>
            <textarea id="catatan" name="catatan" rows="2" class="input" placeholder="Contoh: Kondisi display normal, CT/PT stabil, dsb.">{{ old('catatan') }}</textarea>
        </div>
    </div>

    <div class="form-foot">
        <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
            <a href="{{ route('logsheet.hub') }}" class="btn">Batal</a>
            @if(auth()->user()->role === 'teknisi')
                <span style="font-size:0.78rem; color:var(--text-muted); display:inline-flex; align-items:center; gap:5px;">
                    <span style="color:#fbbf24;">⚡</span> Otomatis tersimpan ke HP jika tanpa sinyal
                </span>
            @endif
        </div>
        <button type="submit" class="btn btn-primary" id="btn-simpan-listrik">
            <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd"/></svg>
            Simpan Stand AMR PLN T33N
        </button>
    </div>
</form>

{{-- Modal / Bottom Sheet Pilih Sumber Foto AMR (Kamera & Galeri) --}}
<div class="photo-source-modal" id="photoSourceModal" aria-hidden="true" style="display:none;">
    <div class="photo-source-backdrop" id="photoSourceBackdrop"></div>
    <div class="photo-source-sheet" role="dialog" aria-modal="true" aria-labelledby="photoSourceTitle">
        <div class="photo-source-handle"></div>
        <div class="photo-source-header">
            <h4 id="photoSourceTitle">Pilih Sumber Foto AMR PLN</h4>
            <p>Ambil langsung menggunakan kamera HP atau pilih dari galeri foto / file</p>
        </div>
        <div class="photo-source-options">
            <button type="button" class="photo-source-btn btn-src-camera" id="btnChooseCamera">
                <div class="photo-source-icon">
                    <svg viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M1 8a2 2 0 0 1 2-2h.93a2 2 0 0 0 1.664-.89l.812-1.22A2 2 0 0 1 8.07 3h3.86a2 2 0 0 1 1.664.89l.812 1.22A2 2 0 0 0 16.07 6H17a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8Zm13.5 3a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM10 14a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="photo-source-text">
                    <strong>Buka Kamera</strong>
                    <span>Buka kamera HP langsung untuk memotret fisik meteran AMR</span>
                </div>
                <div class="photo-source-arrow">›</div>
            </button>

            <button type="button" class="photo-source-btn btn-src-gallery" id="btnChooseGallery">
                <div class="photo-source-icon">
                    <svg viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M1 5.25A2.25 2.25 0 0 1 3.25 3h13.5A2.25 2.25 0 0 1 19 5.25v9.5A2.25 2.25 0 0 1 16.75 17H3.25A2.25 2.25 0 0 1 1 14.75v-9.5Zm1.5 5.81v3.69c0 .414.336.75.75.75h13.5a.75.75 0 0 0 .75-.75v-2.69l-2.22-2.219a.75.75 0 0 0-1.06 0l-1.91 1.909-4.22-4.219a.75.75 0 0 0-1.06 0L2.5 11.06Zm10.25-3.81a1.25 1.25 0 1 1-2.5 0 1.25 1.25 0 0 1 2.5 0Z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="photo-source-text">
                    <strong>Pilih dari Galeri</strong>
                    <span>Pilih foto fisik meteran dari album galeri HP atau file</span>
                </div>
                <div class="photo-source-arrow">›</div>
            </button>

            <button type="button" class="photo-source-btn btn-src-remove" id="btnRemovePhotoSource" style="display:none; border-color:rgba(239,68,68,0.25);">
                <div class="photo-source-icon" style="background:rgba(239,68,68,0.15); color:#ef4444; border:1px solid rgba(239,68,68,0.3);">
                    <svg viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 0 0 6 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 1 0 .23 1.482l.149-.022.841 10.518A2.75 2.75 0 0 0 7.596 19h4.807a2.75 2.75 0 0 0 2.742-2.53l.841-10.52.149.023a.75.75 0 0 0 .23-1.482A41.03 41.03 0 0 0 14 4.193V3.75A2.75 2.75 0 0 0 11.25 1h-2.5ZM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4ZM8.58 7.72a.75.75 0 0 1 .75.75v6a.75.75 0 0 1-1.5 0v-6a.75.75 0 0 1 .75-.75Zm3.59.75a.75.75 0 0 0-1.5 0v6a.75.75 0 0 0 1.5 0v-6Z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="photo-source-text">
                    <strong style="color:#ef4444;">Hapus Foto Terpilih</strong>
                    <span>Kosongkan foto fisik meteran AMR</span>
                </div>
                <div class="photo-source-arrow" style="color:#ef4444;">✕</div>
            </button>
        </div>
        <button type="button" class="photo-source-cancel" id="btnCancelPhotoSource">Batal</button>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Hitung pemakaian langsung
    const fmt = v => v.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    document.querySelectorAll('input[data-prev]').forEach(inp => {
        const out = document.querySelector(`[data-calc-for="${inp.id}"]`);
        const update = () => {
            if (!out) return;
            const prev = parseFloat(inp.dataset.prev), cur = parseFloat(inp.value);
            if (isNaN(prev) || isNaN(cur)) { out.textContent = ''; return; }
            const diff = cur - prev;
            out.classList.toggle('bad', diff < 0);
            out.innerHTML = diff < 0
                ? `⚠ Stand lebih kecil dari sebelumnya (<b>${fmt(diff)}</b>)`
                : `Pemakaian: <b>+${fmt(diff)} ${inp.dataset.unit}</b>`;
        };
        inp.addEventListener('input', update); update();
    });

    // ── Handler Upload Foto AMR PLN (Seragam dengan Water Meter + Pilihan Kamera/Galeri) ──
    const uploadBox    = document.getElementById('upload-box-kwh');
    const galleryInput = document.getElementById('foto_kwh_gallery');
    const cameraInput  = document.getElementById('foto_kwh_camera');
    const reqIndicator = document.getElementById('req-foto-kwh');

    const sourceModal  = document.getElementById('photoSourceModal');
    const backdrop     = document.getElementById('photoSourceBackdrop');
    const btnCamera    = document.getElementById('btnChooseCamera');
    const btnGallery   = document.getElementById('btnChooseGallery');
    const btnRemove    = document.getElementById('btnRemovePhotoSource');
    const btnCancel    = document.getElementById('btnCancelPhotoSource');

    const hasInitialPhoto = {{ $reading->foto_kwh ? 'true' : 'false' }};
    let currentPhotoPresent = hasInitialPhoto;

    function openSourceModal() {
        if (!sourceModal) return;
        if (btnRemove) {
            btnRemove.style.display = currentPhotoPresent ? 'flex' : 'none';
        }
        sourceModal.style.display = 'flex';
        sourceModal.setAttribute('aria-hidden', 'false');
    }

    function closeSourceModal() {
        if (!sourceModal) return;
        sourceModal.style.display = 'none';
        sourceModal.setAttribute('aria-hidden', 'true');
    }

    if (uploadBox) {
        uploadBox.addEventListener('click', function (e) {
            if (e.target.tagName === 'INPUT') return;
            openSourceModal();
        });
        uploadBox.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                openSourceModal();
            }
        });
    }

    if (backdrop)  backdrop.addEventListener('click', closeSourceModal);
    if (btnCancel) btnCancel.addEventListener('click', closeSourceModal);

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && sourceModal && sourceModal.style.display !== 'none') {
            closeSourceModal();
        }
    });

    if (btnCamera) {
        btnCamera.addEventListener('click', function () {
            closeSourceModal();
            cameraInput.click();
        });
    }

    if (btnGallery) {
        btnGallery.addEventListener('click', function () {
            closeSourceModal();
            galleryInput.click();
        });
    }

    function handleSelectedPhoto(file, source) {
        if (!file) return;

        // Validasi ukuran maks 5 MB
        if (file.size > 5 * 1024 * 1024) {
            alert('Ukuran foto melebihi batas maksimal 5 MB. Silakan pilih foto dengan ukuran lebih kecil.');
            if (source === 'camera') cameraInput.value = '';
            else galleryInput.value = '';
            return;
        }

        // Tampilkan preview di dalam .upload
        let previewImg = uploadBox.querySelector('img.preview');
        if (!previewImg) {
            previewImg = document.createElement('img');
            previewImg.className = 'preview';
            previewImg.id = 'kwh-preview-img';
            previewImg.alt = 'Foto kWh AMR PLN';
            uploadBox.insertBefore(previewImg, uploadBox.firstChild);
        }
        previewImg.src = URL.createObjectURL(file);
        uploadBox.classList.add('has-img');
        currentPhotoPresent = true;

        if (source === 'camera') {
            let transferred = false;
            try {
                if (window.DataTransfer) {
                    const dt = new DataTransfer();
                    dt.items.add(file);
                    galleryInput.files = dt.files;
                    transferred = (galleryInput.files.length > 0);
                }
            } catch (err) {}

            if (!transferred) {
                cameraInput.name = 'foto_kwh';
                galleryInput.removeAttribute('name');
            } else {
                galleryInput.name = 'foto_kwh';
                cameraInput.removeAttribute('name');
            }
        } else {
            galleryInput.name = 'foto_kwh';
            cameraInput.removeAttribute('name');
            cameraInput.value = '';
        }

        // Hilangkan atribut required karena foto sudah terisi
        galleryInput.required = false;
        cameraInput.required = false;
        if (reqIndicator) reqIndicator.style.display = 'none';
    }

    if (galleryInput) {
        galleryInput.addEventListener('change', function () {
            if (this.files && this.files[0]) {
                handleSelectedPhoto(this.files[0], 'gallery');
            }
        });
    }

    if (cameraInput) {
        cameraInput.addEventListener('change', function () {
            if (this.files && this.files[0]) {
                handleSelectedPhoto(this.files[0], 'camera');
            }
        });
    }

    if (btnRemove) {
        btnRemove.addEventListener('click', function () {
            closeSourceModal();
            galleryInput.value = '';
            cameraInput.value = '';
            galleryInput.name = 'foto_kwh';
            cameraInput.removeAttribute('name');

            const previewImg = uploadBox.querySelector('img.preview');
            if (previewImg) previewImg.remove();
            uploadBox.classList.remove('has-img');
            currentPhotoPresent = false;

            if (!hasInitialPhoto) {
                galleryInput.required = true;
                if (reqIndicator) reqIndicator.style.display = 'inline';
            }
        });
    }

    // Drag and drop di desktop
    if (uploadBox) {
        ['dragenter', 'dragover'].forEach(eventName => {
            uploadBox.addEventListener(eventName, e => {
                e.preventDefault();
                e.stopPropagation();
                uploadBox.classList.add('drag');
            });
        });

        ['dragleave', 'drop'].forEach(eventName => {
            uploadBox.addEventListener(eventName, e => {
                e.preventDefault();
                e.stopPropagation();
                uploadBox.classList.remove('drag');
            });
        });

        uploadBox.addEventListener('drop', e => {
            const dt = e.dataTransfer;
            if (dt && dt.files && dt.files[0]) {
                handleSelectedPhoto(dt.files[0], 'gallery');
            }
        });
    }
</script>
@endpush
