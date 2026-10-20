@extends('layouts.panel')

@php
    $n = fn ($v) => $v !== null ? number_format($v, 2, ',', '.') : '—';
    $selectedDateCarbon = \Carbon\Carbon::parse($tanggal);
@endphp

@section('title', 'Input Manual Water Meter Public Area (Back Date)')
@section('page-title', 'Input Manual Water Meter Public Area')
@section('page-subtitle', 'Khusus Super Admin — Input data stand Water Meter untuk tanggal yang terlewat')

@section('panel')
<form method="POST" action="{{ route('public-area.manual.water.store') }}" enctype="multipart/form-data" class="panel" id="manual-water-form">
    @csrf

    <div class="panel-head" style="align-items:flex-start; flex-wrap:wrap; gap:14px;">
        <div>
            <h2><span style="color:#06b6d4;">💧</span> Input Manual Water Meter Public Area (Back Date)</h2>
            <p style="color:var(--text-secondary); margin:4px 0 0; font-size:0.88rem;">
                Menu khusus <b>Super Admin</b> untuk menginput atau mengoreksi data 3 titik water meter publik pada tanggal-tanggal lampau.
            </p>
        </div>
        <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
            <a href="{{ route('public-area.water.index') }}" class="btn btn-sm">
                ← Sub Menu Water Meter
            </a>
            <a href="{{ route('public-area.manual.lvsb', ['tanggal' => $tanggal]) }}" class="btn btn-sm" style="background:rgba(251,191,36,0.15); color:#fbbf24; border:1px solid rgba(251,191,36,0.3);">
                ⚡ Manual LVSB
            </a>
            <a href="{{ route('public-area.manual.mvsb', ['tanggal' => $tanggal]) }}" class="btn btn-sm" style="background:rgba(168,85,247,0.15); color:#c084fc; border:1px solid rgba(168,85,247,0.3);">
                ⚡ Manual MVSB
            </a>
            <span class="badge" style="background:rgba(6,182,212,0.2); color:#06b6d4; border:1px solid rgba(6,182,212,0.4); font-size:0.75rem; padding:4px 8px; border-radius:6px; font-weight:700;">
                ★ Super Admin Akses Penuh
            </span>
        </div>
    </div>

    {{-- Pemilih Tanggal Back Date --}}
    <div style="background:rgba(9, 13, 21, 0.7); border:1px solid rgba(6, 182, 212, 0.3); border-radius:12px; padding:18px; margin-bottom:24px;">
        <div style="font-size:0.8rem; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:#06b6d4; margin-bottom:12px; display:flex; align-items:center; gap:6px;">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width:16px; height:16px;"><path fill-rule="evenodd" d="M5.75 2a.75.75 0 0 1 .75.75V4h7V2.75a.75.75 0 0 1 1.5 0V4h.25A2.75 2.75 0 0 1 18 6.75v8.5A2.75 2.75 0 0 1 15.25 18H4.75A2.75 2.75 0 0 1 2 15.25v-8.5A2.75 2.75 0 0 1 4.75 4H5V2.75A.75.75 0 0 1 5.75 2Zm-1 5.5c-.69 0-1.25.56-1.25 1.25v6.5c0 .69.56 1.25 1.25 1.25h10.5c.69 0 1.25-.56 1.25-1.25v-6.5c0-.69-.56-1.25-1.25-1.25H4.75Z" clip-rule="evenodd"/></svg>
            <span>Pilih Tanggal Logsheet Terlewat (Back Date)</span>
        </div>

        <div style="display:flex; align-items:center; gap:16px; flex-wrap:wrap;">
            <div style="flex:1; min-width:240px; max-width:320px;">
                <label for="tanggal" style="font-size:0.8rem; font-weight:600; color:var(--text-secondary); display:block; margin-bottom:6px;">
                    Tanggal Pencatatan <span style="color:#ef4444;">*</span>
                </label>
                <input type="date" id="tanggal" name="tanggal" class="input" value="{{ old('tanggal', $tanggal) }}" max="{{ now()->format('Y-m-d') }}" required style="font-weight:600; font-size:1rem; padding:8px 12px; width:100%;">
            </div>
            <div style="font-size:0.84rem; color:var(--text-secondary);">
                Hari: <b id="formatted-selected-date" style="color:var(--text-primary);">{{ $selectedDateCarbon->translatedFormat('l, d F Y') }}</b>
                <div style="font-size:0.75rem; color:var(--text-muted); margin-top:2px;">Mengubah tanggal akan otomatis memuat acuan stand sebelum &amp; sesudahnya.</div>
            </div>
        </div>
    </div>

    {{-- Grid 3 Titik Water Meter --}}
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(320px, 1fr)); gap:18px;">
        @foreach($waterMeters as $key => $meta)
            @php
                $cur = $existing->get($key);
                $prev = $prevReadings[$key] ?? null;
                $next = $nextReadings[$key] ?? null;
            @endphp
            <div class="card-meter-manual" data-meter-key="{{ $key }}" style="background:rgba(15,22,35,0.7); border:1px solid rgba(255,255,255,0.08); border-radius:12px; padding:18px; display:flex; flex-direction:column; justify-content:space-between;">
                <div>
                    {{-- Header Kartu Meter --}}
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:12px;">
                        <div>
                            <span class="badge" style="background:rgba(6,182,212,0.15); color:{{ $meta['color'] }}; border:1px solid rgba(6,182,212,0.3); font-size:0.72rem;">
                                {{ $meta['badge'] }}
                            </span>
                            <h3 style="margin:6px 0 2px; font-size:1.05rem; color:var(--text-primary);">{{ $meta['label'] }}</h3>
                            <span style="font-size:0.75rem; color:var(--text-muted);">{{ $meta['location'] }}</span>
                        </div>
                    </div>

                    {{-- Acuan Stand Sebelum & Sesudah --}}
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-bottom:14px; background:rgba(0,0,0,0.3); padding:10px; border-radius:8px; border:1px solid rgba(255,255,255,0.04);">
                        <div>
                            <div style="font-size:0.68rem; color:var(--text-muted); text-transform:uppercase;">Sebelumnya</div>
                            <div class="prev-ref-date" style="font-size:0.76rem; color:var(--text-secondary); font-weight:600;">
                                {{ $prev ? $prev->tanggal->format('d/m/Y') : '—' }}
                            </div>
                            <div class="prev-ref-stand" style="font-size:0.82rem; color:#38bdf8; font-weight:700;">
                                {{ $prev && $prev->stand_meter !== null ? $n($prev->stand_meter) . ' m³' : '—' }}
                            </div>
                        </div>
                        <div>
                            <div style="font-size:0.68rem; color:var(--text-muted); text-transform:uppercase;">Sesudahnya</div>
                            <div class="next-ref-date" style="font-size:0.76rem; color:var(--text-secondary); font-weight:600;">
                                {{ $next ? $next->tanggal->format('d/m/Y') : '—' }}
                            </div>
                            <div class="next-ref-stand" style="font-size:0.82rem; color:#a855f7; font-weight:700;">
                                {{ $next && $next->stand_meter !== null ? $n($next->stand_meter) . ' m³' : '—' }}
                            </div>
                        </div>
                    </div>

                    {{-- Input Stand Meter --}}
                    <div style="margin-bottom:14px;">
                        <label style="font-size:0.78rem; font-weight:700; color:var(--text-secondary); text-transform:uppercase; display:block; margin-bottom:6px;">
                            Stand Meter (m³)
                        </label>
                        <div style="position:relative;">
                            <input type="number" step="0.01" name="stand[{{ $key }}]" class="input input-stand-manual" value="{{ old("stand.{$key}", $cur?->stand_meter) }}" placeholder="Contoh: 1234.50" style="width:100%; padding-right:55px; font-size:1.1rem; font-weight:700;">
                            <span style="position:absolute; right:12px; top:50%; transform:translateY(-50%); font-size:0.85rem; color:var(--text-muted); pointer-events:none; font-weight:600;">m³</span>
                        </div>
                    </div>

                    {{-- Foto Lampiran --}}
                    <div class="photo-upload-group" style="margin-bottom:12px; padding-top:10px; border-top:1px dashed rgba(255,255,255,0.06);">
                        <label style="font-size:0.74rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; display:block; margin-bottom:6px;">
                            Foto Meteran (Opsional)
                        </label>
                        <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                            <label class="btn-pick-gallery" style="display:inline-flex; align-items:center; gap:5px; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.15); padding:6px 10px; border-radius:6px; cursor:pointer; font-size:0.76rem; color:var(--text-secondary);" title="Pilih foto dari galeri">
                                <svg viewBox="0 0 20 20" fill="currentColor" style="width:14px;height:14px;color:#38bdf8;"><path fill-rule="evenodd" d="M1 5.25A2.25 2.25 0 0 1 3.25 3h13.5A2.25 2.25 0 0 1 19 5.25v9.5A2.25 2.25 0 0 1 16.75 17H3.25A2.25 2.25 0 0 1 1 14.75v-9.5Zm1.5 5.81v3.69c0 .414.336.75.75.75h13.5a.75.75 0 0 0 .75-.75v-2.69l-2.22-2.219a.75.75 0 0 0-1.06 0l-1.91 1.909-4.22-4.219a.75.75 0 0 0-1.06 0L2.5 11.06Zm10.25-3.81a1.25 1.25 0 1 1-2.5 0 1.25 1.25 0 0 1 2.5 0Z" clip-rule="evenodd"/></svg>
                                <span>Galeri</span>
                                <input type="file" name="fotos[{{ $key }}]" accept="image/*" class="input-file-main" style="display:none;">
                            </label>

                            <label class="btn-pick-camera" style="display:inline-flex; align-items:center; gap:5px; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.15); padding:6px 10px; border-radius:6px; cursor:pointer; font-size:0.76rem; color:var(--text-secondary);" title="Ambil foto kamera">
                                <svg viewBox="0 0 20 20" fill="currentColor" style="width:14px;height:14px;color:#fbbf24;"><path fill-rule="evenodd" d="M1 8a2 2 0 0 1 2-2h.93a2 2 0 0 0 1.664-.89l.812-1.22A2 2 0 0 1 8.07 3h3.86a2 2 0 0 1 1.664.89l.812 1.22A2 2 0 0 0 16.07 6H17a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8Zm13.5 3a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM10 14a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" clip-rule="evenodd"/></svg>
                                <span>Kamera</span>
                                <input type="file" accept="image/*" capture="environment" class="input-file-camera" style="display:none;">
                            </label>

                            <div class="photo-preview-wrap" style="{{ $cur?->foto ? 'display:inline-flex;' : 'display:none;' }} align-items:center; gap:6px; background:rgba(0,0,0,0.45); padding:4px 8px; border-radius:6px; border:1px solid rgba(6,182,212,0.3);">
                                <img class="img-preview" src="{{ $cur?->foto ? asset('storage/'.$cur->foto) : '' }}" alt="Thumb" style="width:30px; height:30px; object-fit:cover; border-radius:4px;">
                                <span class="photo-preview-name" style="font-size:0.72rem; color:var(--text-primary); max-width:120px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                    {{ $cur?->foto ? 'Tersimpan' : '' }}
                                </span>
                                <button type="button" class="btn-clear-photo" style="background:none; border:none; color:#ef4444; font-size:0.95rem; cursor:pointer;" title="Hapus foto">✕</button>
                            </div>
                        </div>
                    </div>

                    {{-- Catatan --}}
                    <div>
                        <label style="font-size:0.74rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; display:block; margin-bottom:4px;">Catatan (Opsional)</label>
                        <input type="text" name="catatan[{{ $key }}]" value="{{ old("catatan.{$key}", $cur?->catatan) }}" placeholder="Catatan kondisi fisik meter..." class="input" style="font-size:0.82rem; padding:6px 10px; width:100%;">
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Tombol Submit --}}
    <div style="margin-top:24px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
        <a href="{{ route('public-area.water.index') }}" class="btn">
            Batal
        </a>
        <button type="submit" class="btn btn-primary" style="background:linear-gradient(135deg, #06b6d4, #0891b2); border:none; color:#fff; font-weight:700; padding:10px 24px; font-size:0.92rem; display:inline-flex; align-items:center; gap:8px; box-shadow:0 4px 12px rgba(6,182,212,0.3);">
            <svg viewBox="0 0 20 20" fill="currentColor" style="width:18px;height:18px;"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd"/></svg>
            <span>Simpan Data Manual Water Public Area</span>
        </button>
    </div>
</form>
@endsection

@push('scripts')
<script>
    // Tangani perubahan tanggal via AJAX
    const tanggalInput = document.getElementById('tanggal');
    const dateText = document.getElementById('formatted-selected-date');

    tanggalInput.addEventListener('change', function () {
        const val = this.value;
        if (!val) return;

        const d = new Date(val + 'T00:00:00');
        dateText.textContent = d.toLocaleDateString('id-ID', { weekday:'long', year:'numeric', month:'long', day:'numeric' });

        fetch(`{{ route('public-area.manual.water.reference') }}?tanggal=${encodeURIComponent(val)}`)
            .then(res => res.json())
            .then(data => {
                if (!data.meters) return;
                document.querySelectorAll('.card-meter-manual').forEach(card => {
                    const key = card.dataset.meterKey;
                    const mData = data.meters[key];
                    if (!mData) return;

                    const prevDate = card.querySelector('.prev-ref-date');
                    const prevStand = card.querySelector('.prev-ref-stand');
                    const nextDate = card.querySelector('.next-ref-date');
                    const nextStand = card.querySelector('.next-ref-stand');
                    const inputStand = card.querySelector('.input-stand-manual');

                    if (prevDate) prevDate.textContent = mData.prev_date || '—';
                    if (prevStand) prevStand.textContent = mData.prev_stand !== null ? Number(mData.prev_stand).toLocaleString('id-ID', {minimumFractionDigits:2}) + ' m³' : '—';
                    if (nextDate) nextDate.textContent = mData.next_date || '—';
                    if (nextStand) nextStand.textContent = mData.next_stand !== null ? Number(mData.next_stand).toLocaleString('id-ID', {minimumFractionDigits:2}) + ' m³' : '—';

                    if (mData.existing_stand !== null && mData.existing_stand !== undefined) {
                        inputStand.value = mData.existing_stand;
                    }
                });
            })
            .catch(err => console.error(err));
    });

    // Handle foto uploads
    document.querySelectorAll('.photo-upload-group').forEach(group => {
        const mainInput   = group.querySelector('.input-file-main');
        const cameraInput = group.querySelector('.input-file-camera');
        const previewWrap = group.querySelector('.photo-preview-wrap');
        const imgPreview  = group.querySelector('.img-preview');
        const namePreview = group.querySelector('.photo-preview-name');
        const clearBtn    = group.querySelector('.btn-clear-photo');

        function handleFile(file) {
            if (!file) return;
            if (imgPreview) imgPreview.src = URL.createObjectURL(file);
            if (namePreview) namePreview.textContent = file.name;
            if (previewWrap) previewWrap.style.display = 'inline-flex';
        }

        if (mainInput) {
            mainInput.addEventListener('change', function () {
                if (this.files && this.files[0]) handleFile(this.files[0]);
            });
        }
        if (cameraInput) {
            cameraInput.addEventListener('change', function () {
                if (this.files && this.files[0]) {
                    handleFile(this.files[0]);
                    if (mainInput) {
                        try {
                            const dt = new DataTransfer();
                            dt.items.add(this.files[0]);
                            mainInput.files = dt.files;
                        } catch (e) {}
                    }
                }
            });
        }
        if (clearBtn) {
            clearBtn.addEventListener('click', function () {
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
