@extends('layouts.panel')

@php
    $editing = $reading->exists;
    $isAdmin = auth()->user()->role === 'admin';
    $canManage = in_array(auth()->user()->role, ['admin', 'eng_admin', 'eng_spv'], true);
    $n = fn ($v) => number_format($v, 2, ',', '.');
@endphp

@section('title', $editing ? 'Edit Logsheet' : 'Input Logsheet Harian')
@section('page-title', $editing ? 'Edit Logsheet' : 'Input Logsheet Harian')

@section('panel')
<form method="POST" action="{{ $editing ? route('logsheet.update', $reading) : route('logsheet.store') }}" enctype="multipart/form-data" class="panel" id="logsheet-form">
    @csrf
    @if ($editing) @method('PUT') @endif

    <div class="panel-head">
        <div>
            <h2>Pencatatan Stand Meter</h2>
            <p>Masukkan angka stand yang tertera pada meter dan unggah foto meter sebagai bukti.</p>
        </div>
        <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
            @if (auth()->user()->isSuperAdmin())
                <a href="{{ route('logsheet.bulk.air') }}" class="btn btn-sm" style="background:rgba(56,189,248,0.18); color:#38bdf8; border:1px solid rgba(56,189,248,0.35); font-weight:700;">
                    <span>📤 Bulk Input Water Meter (Excel)</span>
                </a>
            @endif
            @if (auth()->user()->role === 'teknisi')
                <span class="badge badge-role-teknisi">Role: Teknisi (Mode Input)</span>
            @endif
        </div>
    </div>

    @if (isset($today) && $today && ! $editing && auth()->user()->role === 'teknisi')
        <div class="status-banner ok" style="margin-bottom:24px; display:block;">
            <div style="display:flex; align-items:center; gap:10px; margin-bottom:8px;">
                <span class="dot"></span>
                <strong style="font-size:1.05rem;">Logsheet Hari Ini Sudah Berhasil Dicatat</strong>
            </div>
            <p style="margin:0 0 16px 20px; color:var(--text-secondary); font-size:0.88rem;">
                Pencatatan untuk tanggal <b>{{ now()->translatedFormat('d F Y') }}</b> telah diinput oleh <b>{{ $today->user?->name ?? 'Teknisi' }}</b> pukul {{ $today->created_at?->format('H:i') ?? '—' }} WIB. Role Teknisi hanya dapat memasukan logsheet baru dan tidak memiliki izin untuk mengedit atau merubah data yang telah tersimpan.
            </p>
            <div style="margin-left:20px; display:flex; gap:10px; flex-wrap:wrap;">
                <a href="{{ route('logsheet.show', $today) }}" class="btn btn-sm btn-primary">Lihat Detail Data Hari Ini</a>
                <a href="{{ route('logsheet.index') }}" class="btn btn-sm">Lihat Rekap Riwayat</a>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="status-banner warn" style="margin-bottom:20px">Periksa kembali isian Anda — terdapat {{ $errors->count() }} kesalahan.</div>
    @endif

    <div class="top-fields">
        <div class="field">
            <label for="tanggal">Tanggal Pencatatan <span class="req">*</span></label>
            <input type="date" id="tanggal" name="tanggal" class="input"
                   value="{{ old('tanggal', $reading->tanggal?->format('Y-m-d')) }}"
                   max="{{ now()->format('Y-m-d') }}" {{ $canManage ? '' : 'readonly' }} required>
            @unless ($canManage)<p class="hint">Teknisi hanya dapat mencatat untuk hari ini.</p>@endunless
            @error('tanggal')<p class="err">{{ $message }}</p>@enderror
        </div>
        <div class="field">
            <label>Pencatatan Sebelumnya</label>
            <div class="input" style="opacity:.8">
                @if ($last)
                    {{ $last->tanggal->translatedFormat('d F Y') }} · oleh {{ $last->user->name ?? '-' }}
                @else
                    Belum ada — ini pencatatan pertama
                @endif
            </div>
        </div>
    </div>

    <div class="form-grid">
        {{-- AMR PLN T33N --}}
        <div class="meter-section elec">
            <h3 style="display:flex; align-items:center; gap:8px;">
                <span class="ico" style="background:rgba(255,201,14,0.12); padding:3px; border-radius:6px; display:inline-flex;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" style="width:20px; height:20px;">
                        <polygon points="32,3 61,32 32,61 3,32" fill="#FFC90E" stroke="#D32F2F" stroke-width="2.5" stroke-linejoin="round"/>
                        <path d="M14 36c4-2 7 2 11 0s7-2 11 0" fill="none" stroke="#0288D1" stroke-width="2.2" stroke-linecap="round"/>
                        <polygon points="36,11 23,33 31,33 26,53 43,28 34,28" fill="#D32F2F"/>
                    </svg>
                </span>
                AMR PLN T33N (kWh)
            </h3>

            @php
                $amrFormFields = [
                    'stand_bp'    => ['label' => 'Stand BP', 'fallback' => 'stand_wbp'],
                    'stand_lbp1'  => ['label' => 'Stand LBP 1', 'fallback' => 'stand_lwbp'],
                    'stand_lbp2'  => ['label' => 'Stand LBP 2', 'fallback' => null],
                    'stand_total' => ['label' => 'Stand Total', 'fallback' => null],
                ];
            @endphp

            @foreach ($amrFormFields as $key => $info)
            @php
                $prevVal = $last ? ($last->$key ?? ($info['fallback'] ? $last->{$info['fallback']} : null)) : null;
                $val = old($key, $reading->$key ?? ($info['fallback'] ? $reading->{$info['fallback']} : null));
            @endphp
            <div class="field">
                <label for="{{ $key }}">{{ $info['label'] }} <span class="req">*</span></label>
                <div class="input-unit">
                    <input type="number" step="0.01" min="0" inputmode="decimal" id="{{ $key }}" name="{{ $key }}" class="input"
                           value="{{ $val }}" data-prev="{{ $prevVal }}" data-unit="kWh" placeholder="0,00" required>
                    <span>kWh</span>
                </div>
                @if ($prevVal !== null)<p class="hint">Stand sebelumnya: <b>{{ $n($prevVal) }} kWh</b></p>@endif
                <p class="calc" data-calc-for="{{ $key }}"></p>
                @error($key)<p class="err">{{ $message }}</p>@enderror
            </div>
            @endforeach

            <div class="field" style="margin-top:20px;">
                <label>Foto Fisik Meteran AMR PLN <span class="req" id="req-foto-kwh">{{ $editing ? '' : '*' }}</span></label>
                <div class="upload {{ $reading->foto_kwh ? 'has-img' : '' }}" id="upload-box-kwh" role="button" tabindex="0" title="Klik untuk ambil foto dari kamera atau pilih dari galeri">
                    <input type="file" name="foto_kwh" id="foto_kwh_gallery" accept="image/*" class="input-file-gallery" style="display:none;" {{ $editing ? '' : 'required' }}>
                    <input type="file" id="foto_kwh_camera" accept="image/*" capture="environment" class="input-file-camera" style="display:none;">
                    @if ($editing && $reading->foto_kwh)
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
        </div>

        {{-- AIR --}}
        <div class="meter-section water">
            <h3><span class="ico"><svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 2.5c.3 0 .58.14.77.37C12.4 4.87 16 9.45 16 12.5a6 6 0 0 1-12 0c0-3.05 3.6-7.63 5.23-9.63A1 1 0 0 1 10 2.5Z" clip-rule="evenodd"/></svg></span>Water Meter</h3>

            <div class="field">
                <label for="stand_air">Stand Water Meter <span class="req">*</span></label>
                <div class="input-unit">
                    <input type="number" step="0.01" min="0" inputmode="decimal" id="stand_air" name="stand_air" class="input"
                           value="{{ old('stand_air', $reading->stand_air) }}" data-prev="{{ $last?->stand_air }}" data-unit="m³" placeholder="0,00" required>
                    <span>m³</span>
                </div>
                @if ($last)<p class="hint">Stand sebelumnya: <b>{{ $n($last->stand_air) }}</b></p>@endif
                <p class="calc" data-calc-for="stand_air"></p>
                @error('stand_air')<p class="err">{{ $message }}</p>@enderror
            </div>

            <div class="field">
                <label>Foto Water Meter <span class="req">{{ $editing ? '' : '*' }}</span></label>
                <label class="upload {{ $editing ? 'has-img' : '' }}">
                    <input type="file" name="foto_air" accept="image/*" capture="environment" {{ $editing ? '' : 'required' }}>
                    @if ($editing)<img class="preview" src="{{ asset('storage/'.$reading->foto_air) }}" alt="Foto air">@endif
                    <span class="upload-text">
                        <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M1 8a2 2 0 0 1 2-2h.93a2 2 0 0 0 1.664-.89l.812-1.22A2 2 0 0 1 8.07 3h3.86a2 2 0 0 1 1.664.89l.812 1.22A2 2 0 0 0 16.07 6H17a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8Zm13.5 3a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM10 14a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" clip-rule="evenodd"/></svg><br>
                        Ambil foto / pilih gambar<br><small>Pastikan angka meter terbaca jelas · maks 5 MB</small>
                    </span>
                    <span class="change">Ganti foto</span>
                </label>
                @error('foto_air')<p class="err">{{ $message }}</p>@enderror
            </div>

            <div class="field">
                <label for="catatan">Catatan (opsional)</label>
                <textarea id="catatan" name="catatan" rows="3" class="input" placeholder="Contoh: meter berembun, ada kebocoran, dsb.">{{ old('catatan', $reading->catatan) }}</textarea>
            </div>
        </div>
    </div>

    <div class="form-foot">
        <a href="{{ route('logsheet.index') }}" class="btn">Batal</a>
        <button type="submit" class="btn btn-primary" id="btn-simpan-logsheet">
            <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd"/></svg>
            {{ $editing ? 'Simpan Perubahan' : 'Simpan Logsheet' }}
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
    // Hitung pemakaian secara langsung
    const fmt = v => v.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    document.querySelectorAll('input[data-prev]').forEach(inp => {
        const out = document.querySelector(`[data-calc-for="${inp.id}"]`);
        const update = () => {
            const prev = parseFloat(inp.dataset.prev), cur = parseFloat(inp.value);
            if (isNaN(prev) || isNaN(cur)) { out.textContent = ''; return; }
            const diff = cur - prev;
            out.classList.toggle('bad', diff < 0);
            out.innerHTML = diff < 0
                ? `⚠ Stand lebih kecil dari sebelumnya (<b>${fmt(diff)}</b>)`
                : `Pemakaian: <b>${fmt(diff)} ${inp.dataset.unit}</b>`;
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

        if (file.size > 5 * 1024 * 1024) {
            alert('Ukuran foto melebihi batas maksimal 5 MB. Silakan pilih foto dengan ukuran lebih kecil.');
            if (source === 'camera') cameraInput.value = '';
            else galleryInput.value = '';
            return;
        }

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

    // Preview foto lainnya (seperti air)
    document.querySelectorAll('.upload input[type=file]').forEach(inp => {
        const box = inp.closest('.upload');
        if (box && box.id === 'upload-box-kwh') return;
        inp.addEventListener('change', () => {
            const f = inp.files[0]; if (!f) return;
            let img = box.querySelector('img.preview');
            if (!img) { img = document.createElement('img'); img.className = 'preview'; box.appendChild(img); }
            img.src = URL.createObjectURL(f);
            box.classList.add('has-img');
        });
        if (box) {
            ['dragenter', 'dragover'].forEach(e => box.addEventListener(e, () => box.classList.add('drag')));
            ['dragleave', 'drop'].forEach(e => box.addEventListener(e, () => box.classList.remove('drag')));
        }
    });
</script>
@endpush
