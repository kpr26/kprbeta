@extends('layouts.panel')

@php
    $n = fn ($v, $d = 2) => ($v !== null && $v !== '') ? number_format((float)$v, $d, ',', '.') : '—';
    $selectedDateCarbon = \Carbon\Carbon::parse($tanggal);
@endphp

@section('title', 'Input Manual MVSB (Back Date)')
@section('page-title', 'Input Manual MVSB')
@section('page-subtitle', 'Khusus Super Admin — Input data parameter MVSB 20kV untuk tanggal yang terlewat')

@section('panel')
<section class="panel" style="max-width:800px; margin:0 auto;">
    <div class="panel-head" style="align-items:flex-start; flex-wrap:wrap; gap:14px;">
        <div>
            <h2><span style="color:#a855f7;">⚡</span> Input Manual MVSB (Back Date)</h2>
            <p style="color:var(--text-secondary); margin:4px 0 0; font-size:0.88rem;">
                Menu khusus <b>Super Admin</b> untuk mencatat data tegangan menengah (Voltage 20kV) dan arus panel incoming PLN pada tanggal lampau.
            </p>
        </div>
        <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
            <a href="{{ route('public-area.mvsb.index') }}" class="btn btn-sm">
                ← Sub Menu MVSB
            </a>
            <a href="{{ route('public-area.manual.water', ['tanggal' => $tanggal]) }}" class="btn btn-sm" style="background:rgba(56,189,248,0.15); color:#38bdf8; border:1px solid rgba(56,189,248,0.3);">
                💧 Manual Water
            </a>
            <a href="{{ route('public-area.manual.lvsb', ['tanggal' => $tanggal]) }}" class="btn btn-sm" style="background:rgba(251,191,36,0.15); color:#fbbf24; border:1px solid rgba(251,191,36,0.3);">
                ⚡ Manual LVSB
            </a>
            <span class="badge" style="background:rgba(168,85,247,0.2); color:#c084fc; border:1px solid rgba(168,85,247,0.4); font-size:0.75rem; padding:4px 8px; border-radius:6px; font-weight:700;">
                ★ Super Admin Akses Penuh
            </span>
        </div>
    </div>

    <form method="POST" action="{{ route('public-area.manual.mvsb.store') }}" enctype="multipart/form-data">
        @csrf

        {{-- Pemilih Tanggal Back Date --}}
        <div style="background:rgba(9, 13, 21, 0.7); border:1px solid rgba(168, 85, 247, 0.3); border-radius:12px; padding:18px; margin-bottom:24px;">
            <div style="font-size:0.8rem; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:#c084fc; margin-bottom:12px; display:flex; align-items:center; gap:6px;">
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
                    <div id="status-mvsb-date" style="font-size:0.75rem; color:{{ $reading->exists ? '#34d399' : 'var(--text-muted)' }}; margin-top:2px;">
                        {{ $reading->exists ? '✓ Tanggal ini sudah memiliki data tersimpan. Menyimpan akan memperbarui data.' : 'Belum ada data pada tanggal ini.' }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Parameter MVSB --}}
        <div style="background:rgba(15,22,35,0.7); border:1px solid rgba(168,85,247,0.3); border-radius:12px; padding:20px;">
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(260px, 1fr)); gap:16px;">
                {{-- Voltage 20kV --}}
                <div class="field-item">
                    <label style="font-size:0.8rem; font-weight:700; color:var(--text-secondary); text-transform:uppercase; display:block; margin-bottom:6px;">
                        Voltage 20kV <span style="color:#ef4444;">*</span>
                    </label>
                    <div style="position:relative;">
                        <input type="number" step="0.01" name="voltage_20kv" class="input" value="{{ old('voltage_20kv', $reading->voltage_20kv) }}" placeholder="Contoh: 20000" style="width:100%; padding-right:60px; font-size:1.05rem; font-weight:600;">
                        <span style="position:absolute; right:12px; top:50%; transform:translateY(-50%); font-size:0.8rem; color:var(--text-muted); pointer-events:none; font-weight:600;">Volt</span>
                    </div>
                    @if($prevReading && $prevReading->voltage_20kv !== null)
                        <div style="font-size:0.72rem; color:var(--text-muted); margin-top:4px;">
                            Sebelumnya ({{ $prevReading->tanggal->format('d/m') }}): <b>{{ $n($prevReading->voltage_20kv, 1) }} Volt</b>
                        </div>
                    @endif
                </div>

                {{-- Current --}}
                <div class="field-item">
                    <label style="font-size:0.8rem; font-weight:700; color:var(--text-secondary); text-transform:uppercase; display:block; margin-bottom:6px;">
                        Current <span style="color:#ef4444;">*</span>
                    </label>
                    <div style="position:relative;">
                        <input type="number" step="0.01" name="current" class="input" value="{{ old('current', $reading->current) }}" placeholder="Contoh: 25.5" style="width:100%; padding-right:75px; font-size:1.05rem; font-weight:600;">
                        <span style="position:absolute; right:12px; top:50%; transform:translateY(-50%); font-size:0.8rem; color:var(--text-muted); pointer-events:none; font-weight:600;">Ampere</span>
                    </div>
                    @if($prevReading && $prevReading->current !== null)
                        <div style="font-size:0.72rem; color:var(--text-muted); margin-top:4px;">
                            Sebelumnya ({{ $prevReading->tanggal->format('d/m') }}): <b>{{ $n($prevReading->current, 2) }} Ampere</b>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Foto & Catatan --}}
            <div style="margin-top:20px; padding-top:16px; border-top:1px dashed rgba(255,255,255,0.08);">
                {{-- Foto Fisik Panel MVSB --}}
                <div class="photo-upload-group" style="margin-bottom:16px;">
                    <label style="font-size:0.78rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; display:block; margin-bottom:6px;">
                        📷 Foto Fisik Panel MVSB (Bisa Galeri / Kamera Langsung)
                    </label>

                    <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                        <label class="btn-pick-gallery" style="display:inline-flex; align-items:center; gap:6px; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.15); padding:7px 12px; border-radius:6px; cursor:pointer; font-size:0.8rem; color:var(--text-secondary);">
                            <svg viewBox="0 0 20 20" fill="currentColor" style="width:15px;height:15px;color:#38bdf8;"><path fill-rule="evenodd" d="M1 5.25A2.25 2.25 0 0 1 3.25 3h13.5A2.25 2.25 0 0 1 19 5.25v9.5A2.25 2.25 0 0 1 16.75 17H3.25A2.25 2.25 0 0 1 1 14.75v-9.5Zm1.5 5.81v3.69c0 .414.336.75.75.75h13.5a.75.75 0 0 0 .75-.75v-2.69l-2.22-2.219a.75.75 0 0 0-1.06 0l-1.91 1.909-4.22-4.219a.75.75 0 0 0-1.06 0L2.5 11.06Zm10.25-3.81a1.25 1.25 0 1 1-2.5 0 1.25 1.25 0 0 1 2.5 0Z" clip-rule="evenodd"/></svg>
                            <span>🖼️ Galeri</span>
                            <input type="file" name="foto" accept="image/*" class="input-file-main" style="display:none;">
                        </label>

                        <label class="btn-pick-camera" style="display:inline-flex; align-items:center; gap:6px; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.15); padding:7px 12px; border-radius:6px; cursor:pointer; font-size:0.8rem; color:var(--text-secondary);">
                            <svg viewBox="0 0 20 20" fill="currentColor" style="width:15px;height:15px;color:#c084fc;"><path fill-rule="evenodd" d="M1 8a2 2 0 0 1 2-2h.93a2 2 0 0 0 1.664-.89l.812-1.22A2 2 0 0 1 8.07 3h3.86a2 2 0 0 1 1.664.89l.812 1.22A2 2 0 0 0 16.07 6H17a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8Zm13.5 3a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM10 14a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" clip-rule="evenodd"/></svg>
                            <span>📷 Kamera</span>
                            <input type="file" accept="image/*" capture="environment" class="input-file-camera" style="display:none;">
                        </label>

                        <div class="photo-preview-wrap" style="{{ $reading->foto ? 'display:inline-flex;' : 'display:none;' }} align-items:center; gap:8px; background:rgba(0,0,0,0.45); padding:5px 10px; border-radius:6px; border:1px solid rgba(168,85,247,0.3);">
                            <img class="img-preview" src="{{ $reading->foto ? asset('storage/'.$reading->foto) : '' }}" alt="Thumb" style="width:36px; height:36px; object-fit:cover; border-radius:4px;">
                            <span class="photo-preview-name" style="font-size:0.75rem; color:var(--text-primary); max-width:160px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                {{ $reading->foto ? 'Foto tersimpan' : '' }}
                            </span>
                            <button type="button" class="btn-clear-photo" style="background:none; border:none; color:#ef4444; font-size:1rem; cursor:pointer;" title="Batalkan foto">✕</button>
                        </div>
                    </div>
                </div>

                {{-- Catatan --}}
                <div>
                    <label style="font-size:0.78rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; display:block; margin-bottom:6px;">
                        Catatan Kondisi Panel (Opsional)
                    </label>
                    <textarea name="catatan" rows="2" class="input" placeholder="Contoh: Input manual tanggal terlewat oleh Super Admin..." style="width:100%; font-size:0.84rem; padding:8px 12px;">{{ old('catatan', $reading->catatan) }}</textarea>
                </div>
            </div>
        </div>

        {{-- Tombol Batal & Simpan --}}
        <div style="margin-top:24px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
            <a href="{{ route('public-area.mvsb.index') }}" class="btn">
                Batal
            </a>
            <button type="submit" class="btn btn-primary" style="background:linear-gradient(135deg, #a855f7, #7c3aed); border:none; color:#fff; font-weight:700; padding:10px 24px; font-size:0.92rem; display:inline-flex; align-items:center; gap:8px; box-shadow:0 4px 12px rgba(168,85,247,0.3);">
                <svg viewBox="0 0 20 20" fill="currentColor" style="width:18px;height:18px;"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd"/></svg>
                <span>Simpan Data Manual MVSB</span>
            </button>
        </div>
    </form>
</section>
@endsection

@push('scripts')
<script>
    const tanggalInput = document.getElementById('tanggal');
    const dateText = document.getElementById('formatted-selected-date');

    tanggalInput.addEventListener('change', function () {
        const val = this.value;
        if (!val) return;
        window.location.href = '{{ route('public-area.manual.mvsb') }}?tanggal=' + encodeURIComponent(val);
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
