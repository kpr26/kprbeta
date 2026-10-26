@extends('layouts.panel')

@php
    $n = fn ($v, $d = 2) => ($v !== null && $v !== '') ? number_format((float)$v, $d, ',', '.') : '—';
    $selectedDateCarbon = \Carbon\Carbon::parse($tanggal);
@endphp

@section('title', 'Input Manual LVSB (Back Date)')
@section('page-title', 'Input Manual LVSB')
@section('page-subtitle', 'Khusus Super Admin — Input data parameter LVSB untuk tanggal yang terlewat')

@section('panel')
<section class="panel" style="max-width:1200px; margin:0 auto;">
    <div class="panel-head" style="align-items:flex-start; flex-wrap:wrap; gap:14px;">
        <div>
            <h2><span style="color:#fbbf24;">⚡</span> Input Manual Logsheet LVSB (Back Date)</h2>
            <p style="color:var(--text-secondary); margin:4px 0 0; font-size:0.88rem;">
                Menu khusus <b>Super Admin</b> untuk mencatat data panel distribusi LVSB pada tanggal-tanggal lampau.
            </p>
        </div>
        <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
            <a href="{{ route('public-area.lvsb.index') }}" class="btn btn-sm">
                ← Sub Menu LVSB
            </a>
            <a href="{{ route('public-area.manual.water', ['tanggal' => $tanggal]) }}" class="btn btn-sm" style="background:rgba(56,189,248,0.15); color:var(--info); border:1px solid rgba(56,189,248,0.3);">
                💧 Manual Water
            </a>
            <a href="{{ route('public-area.manual.mvsb', ['tanggal' => $tanggal]) }}" class="btn btn-sm" style="background:rgba(168,85,247,0.15); color:var(--purple); border:1px solid rgba(168,85,247,0.3);">
                ⚡ Manual MVSB
            </a>
            <span class="badge" style="background:rgba(251,191,36,0.2); color:var(--warning); border:1px solid rgba(251,191,36,0.4); font-size:0.75rem; padding:4px 8px; border-radius:6px; font-weight:700;">
                ★ Super Admin Akses Penuh
            </span>
        </div>
    </div>

    <form method="POST" action="{{ route('public-area.manual.lvsb.store') }}" enctype="multipart/form-data">
        @csrf

        {{-- Pemilih Tanggal Back Date --}}
        <div style="background:var(--bg-card); border:1px solid var(--border); border-radius:12px; padding:18px; margin-bottom:24px; box-shadow:var(--shadow-sm);">
            <div style="font-size:0.8rem; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:var(--warning); margin-bottom:12px; display:flex; align-items:center; gap:6px;">
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
                    <div id="status-lvsb-date" style="font-size:0.75rem; color:{{ $reading->exists ? 'var(--success)' : 'var(--text-muted)' }}; margin-top:2px;">
                        {{ $reading->exists ? '✓ Tanggal ini sudah memiliki data tersimpan. Menyimpan akan memperbarui data.' : 'Belum ada data pada tanggal ini.' }}
                    </div>
                </div>
            </div>
        </div>

        {{-- 6 Grup Panel LVSB --}}
        <div style="display:flex; flex-direction:column; gap:20px;">
            @foreach ($structure as $gKey => $group)
                <div style="background:var(--bg-card); border:1px solid var(--border); border-radius:10px; padding:16px; box-shadow:var(--shadow-sm);">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px; border-bottom:1px solid var(--border); padding-bottom:8px;">
                        <div style="display:flex; align-items:center; gap:8px;">
                            <span class="badge" style="background:rgba(251,191,36,0.12); color:{{ $group['color'] }}; border:1px solid rgba(251,191,36,0.3); font-size:0.75rem;">
                                {{ $group['badge'] }}
                            </span>
                            <h4 style="margin:0; font-size:1rem; color:var(--text-primary); font-weight:700;">
                                {{ $group['title'] }}
                            </h4>
                        </div>
                    </div>

                    @if ($group['has_subgroups'])
                        <div style="display:flex; flex-direction:column; gap:14px;">
                            @foreach ($group['subgroups'] as $sKey => $sub)
                                <div style="background:var(--bg-card-subtle); border:1px solid var(--border); border-radius:8px; padding:12px;">
                                    <span style="font-size:0.8rem; font-weight:700; color:var(--text-secondary); text-transform:uppercase; display:block; margin-bottom:8px;">
                                        Sub Grup: {{ $sub['label'] }}
                                    </span>
                                    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(170px, 1fr)); gap:10px;">
                                        @foreach ($sub['fields'] as $fKey => $fMeta)
                                            @php
                                                $val = old("readings.{$gKey}.{$sKey}.{$fKey}", $reading->val($gKey, $sKey, $fKey));
                                            @endphp
                                            <div>
                                                <label style="font-size:0.72rem; color:var(--text-muted); display:block; margin-bottom:3px;">
                                                    {{ $fMeta['label'] }}
                                                </label>
                                                <div style="position:relative;">
                                                    <input type="number" step="{{ $fMeta['step'] }}" name="readings[{{ $gKey }}][{{ $sKey }}][{{ $fKey }}]" class="input" value="{{ $val }}" placeholder="{{ $fMeta['placeholder'] }}" style="width:100%; padding-right:55px; font-size:0.86rem;">
                                                    <span style="position:absolute; right:8px; top:50%; transform:translateY(-50%); font-size:0.72rem; color:var(--text-muted); pointer-events:none;">
                                                        {{ $fMeta['unit'] }}
                                                    </span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(170px, 1fr)); gap:10px;">
                            @foreach ($group['fields'] as $fKey => $fMeta)
                                @php
                                    $val = old("readings.{$gKey}.{$fKey}", $reading->val($gKey, null, $fKey));
                                @endphp
                                <div>
                                    <label style="font-size:0.72rem; color:var(--text-muted); display:block; margin-bottom:3px;">
                                        {{ $fMeta['label'] }}
                                    </label>
                                    <div style="position:relative;">
                                        <input type="number" step="{{ $fMeta['step'] }}" name="readings[{{ $gKey }}][{{ $fKey }}]" class="input" value="{{ $val }}" placeholder="{{ $fMeta['placeholder'] }}" style="width:100%; padding-right:55px; font-size:0.86rem;">
                                        <span style="position:absolute; right:8px; top:50%; transform:translateY(-50%); font-size:0.72rem; color:var(--text-muted); pointer-events:none;">
                                            {{ $fMeta['unit'] }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    {{-- Foto Lampiran Grup --}}
                    @php $groupFoto = $reading->getFoto($gKey); @endphp
                    <div class="photo-upload-group" style="margin-top:12px; padding-top:10px; border-top:1px dashed var(--border); display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px;">
                        <span style="font-size:0.74rem; font-weight:700; color:var(--text-muted); text-transform:uppercase;">
                            📷 Foto Panel {{ $group['title'] }}
                        </span>
                        <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                            <label class="btn-pick-gallery" style="display:inline-flex; align-items:center; gap:5px; background:var(--bg-card-subtle); border:1px solid var(--border); padding:5px 10px; border-radius:6px; cursor:pointer; font-size:0.76rem; color:var(--text-secondary);">
                                <svg viewBox="0 0 20 20" fill="currentColor" style="width:14px;height:14px;color:var(--info);"><path fill-rule="evenodd" d="M1 5.25A2.25 2.25 0 0 1 3.25 3h13.5A2.25 2.25 0 0 1 19 5.25v9.5A2.25 2.25 0 0 1 16.75 17H3.25A2.25 2.25 0 0 1 1 14.75v-9.5Zm1.5 5.81v3.69c0 .414.336.75.75.75h13.5a.75.75 0 0 0 .75-.75v-2.69l-2.22-2.219a.75.75 0 0 0-1.06 0l-1.91 1.909-4.22-4.219a.75.75 0 0 0-1.06 0L2.5 11.06Zm10.25-3.81a1.25 1.25 0 1 1-2.5 0 1.25 1.25 0 0 1 2.5 0Z" clip-rule="evenodd"/></svg>
                                <span>Galeri</span>
                                <input type="file" name="fotos[{{ $gKey }}]" accept="image/*" class="input-file-main" style="display:none;">
                            </label>

                            <label class="btn-pick-camera" style="display:inline-flex; align-items:center; gap:5px; background:var(--bg-card-subtle); border:1px solid var(--border); padding:5px 10px; border-radius:6px; cursor:pointer; font-size:0.76rem; color:var(--text-secondary);">
                                <svg viewBox="0 0 20 20" fill="currentColor" style="width:14px;height:14px;color:var(--warning);"><path fill-rule="evenodd" d="M1 8a2 2 0 0 1 2-2h.93a2 2 0 0 0 1.664-.89l.812-1.22A2 2 0 0 1 8.07 3h3.86a2 2 0 0 1 1.664.89l.812 1.22A2 2 0 0 0 16.07 6H17a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8Zm13.5 3a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM10 14a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" clip-rule="evenodd"/></svg>
                                <span>Kamera</span>
                                <input type="file" accept="image/*" capture="environment" class="input-file-camera" style="display:none;">
                            </label>

                            <div class="photo-preview-wrap" style="{{ $groupFoto ? 'display:inline-flex;' : 'display:none;' }} align-items:center; gap:6px; background:var(--bg-card-subtle); padding:4px 8px; border-radius:6px; border:1px solid var(--border);">
                                <img class="img-preview" src="{{ $groupFoto ? asset('storage/'.$groupFoto) : '' }}" alt="Thumb" style="width:28px; height:28px; object-fit:cover; border-radius:4px;">
                                <span class="photo-preview-name" style="font-size:0.72rem; color:var(--text-primary); max-width:120px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                    {{ $groupFoto ? 'Tersimpan' : '' }}
                                </span>
                                <button type="button" class="btn-clear-photo" style="background:none; border:none; color:#ef4444; font-size:0.95rem; cursor:pointer;" title="Hapus foto">✕</button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Catatan --}}
        <div style="margin-top:20px; padding-top:14px; border-top:1px solid var(--border);">
            <label style="font-size:0.8rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; display:block; margin-bottom:6px;">
                Catatan Kondisi Panel LVSB (Opsional)
            </label>
            <textarea name="catatan" rows="2" class="input" placeholder="Contoh: Input manual tanggal terlewat oleh Super Admin..." style="width:100%; font-size:0.84rem; padding:8px 12px;">{{ old('catatan', $reading->catatan) }}</textarea>
        </div>

        {{-- Tombol Submit --}}
        <div style="margin-top:24px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
            <a href="{{ route('public-area.lvsb.index') }}" class="btn">
                Batal
            </a>
            <button type="submit" class="btn btn-primary" style="background:linear-gradient(135deg, #fbbf24, #d97706); border:none; color:#090d15; font-weight:700; padding:10px 24px; font-size:0.92rem; display:inline-flex; align-items:center; gap:8px;">
                <svg viewBox="0 0 20 20" fill="currentColor" style="width:18px;height:18px;"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd"/></svg>
                <span>Simpan Data Manual LVSB</span>
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
        window.location.href = '{{ route('public-area.manual.lvsb') }}?tanggal=' + encodeURIComponent(val);
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
