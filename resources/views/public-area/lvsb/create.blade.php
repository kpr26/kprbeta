@extends('layouts.panel')

@php
    $n = fn ($v, $d = 2) => ($v !== null && $v !== '') ? number_format((float)$v, $d, ',', '.') : '—';
    $tglCarbon = \Carbon\Carbon::parse($tanggal);
@endphp

@section('title', 'Input Logsheet LVSB — ' . $tglCarbon->format('d/m/Y'))
@section('page-title', 'Form Logsheet LVSB')
@section('page-subtitle', 'Pencatatan Parameter Feeder Panel LVSB — ' . $tglCarbon->translatedFormat('l, d F Y'))

@section('panel')
<section class="panel" style="max-width:1200px; margin:0 auto;">
    <div class="panel-head" style="align-items:center; flex-wrap:wrap; gap:12px;">
        <div style="display:flex; align-items:center; gap:12px;">
            <a href="{{ route('public-area.lvsb.index') }}" class="btn btn-sm">
                ← Kembali ke Daftar LVSB
            </a>
            <h2 style="margin:0; font-size:1.25rem;">
                Input Logsheet LVSB Tanggal {{ $tglCarbon->format('d/m/Y') }}
            </h2>
        </div>
        <div style="font-size:0.8rem; color:var(--text-muted);">
            Petugas: <b style="color:#fbbf24;">{{ auth()->user()->name }}</b>
        </div>
    </div>

    @if(auth()->user()->role === 'teknisi')
        <div style="display:inline-flex; align-items:center; gap:8px; background:rgba(200,170,110,0.06); border:1px solid rgba(200,170,110,0.22); border-radius:20px; padding:4px 12px; margin-bottom:16px; font-size:0.78rem; color:var(--text-secondary);">
            <span style="display:inline-block; width:7px; height:7px; border-radius:50%; background:#10b981; box-shadow:0 0 6px #10b981;"></span>
            <span><strong style="color:var(--accent-light);">Mode Offline:</strong> Formulir ini dapat disimpan ke HP Anda meski tanpa sinyal dan otomatis tersinkron saat online.</span>
        </div>
    @endif

    <form method="POST" action="{{ route('public-area.lvsb.store') }}" enctype="multipart/form-data" class="form-offline-pa-lvsb" data-offline-type="public_area_lvsb" data-meter-label="LVSB Public Area" data-target-redirect="{{ route('public-area.lvsb.index') }}">
        @csrf
        <input type="hidden" name="tanggal" value="{{ $tanggal }}">

        {{-- 6 GRUP LVSB --}}
        <div style="display:flex; flex-direction:column; gap:20px;">
            @foreach ($structure as $gKey => $group)
                <div style="background:rgba(20,28,45,0.7); border:1px solid {{ $group['color'] }}33; border-radius:10px; overflow:hidden;">
                    {{-- Header Grup --}}
                    <div style="background:rgba(0,0,0,0.35); padding:10px 16px; border-bottom:1px solid {{ $group['color'] }}25; display:flex; align-items:center; gap:10px;">
                        <span style="background:{{ $group['color'] }}20; color:{{ $group['color'] }}; border:1px solid {{ $group['color'] }}40; font-size:0.72rem; padding:2px 8px; border-radius:6px; font-weight:700; text-transform:uppercase;">
                            {{ $group['badge'] }}
                        </span>
                        <span style="font-weight:700; color:var(--text-primary); font-size:0.95rem;">
                            {{ $group['title'] }}
                        </span>
                    </div>

                    {{-- Isi Grup --}}
                    <div style="padding:14px 16px;">
                        @if ($group['has_subgroups'])
                            <div style="display:flex; flex-direction:column; gap:14px;">
                                @foreach ($group['subgroups'] as $sKey => $sub)
                                    <div style="background:rgba(10,14,23,0.5); border:1px solid rgba(255,255,255,0.06); border-radius:8px; padding:12px 14px;">
                                        <div style="font-weight:600; font-size:0.85rem; color:#e2e8f0; margin-bottom:10px; display:flex; align-items:center; gap:6px;">
                                            <span style="color:{{ $group['color'] }};">▸</span>
                                            <span>{{ $sub['label'] }}</span>
                                        </div>

                                        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(170px, 1fr)); gap:10px;">
                                            @foreach ($sub['fields'] as $fKey => $fMeta)
                                                @php
                                                    $val = old("readings.{$gKey}.{$sKey}.{$fKey}", $reading->val($gKey, $sKey, $fKey));
                                                    $prevVal = $prevReading?->val($gKey, $sKey, $fKey);
                                                @endphp
                                                <div>
                                                    <label style="font-size:0.74rem; font-weight:600; color:var(--text-muted); display:block; margin-bottom:3px;">
                                                        {{ $fMeta['label'] }}
                                                    </label>
                                                    <div class="input-unit" style="position:relative;">
                                                        <input type="number" step="{{ $fMeta['step'] }}" name="readings[{{ $gKey }}][{{ $sKey }}][{{ $fKey }}]" class="input" value="{{ $val }}" placeholder="{{ $fMeta['placeholder'] }}" style="width:100%; padding-right:56px; font-size:0.85rem;">
                                                        <span style="position:absolute; right:10px; top:50%; transform:translateY(-50%); font-size:0.75rem; color:var(--text-muted); pointer-events:none;">
                                                            {{ $fMeta['unit'] }}
                                                        </span>
                                                    </div>
                                                    @if($prevVal !== null)
                                                        <div style="font-size:0.68rem; color:var(--text-muted); margin-top:2px;">
                                                            Sebelumnya: {{ $n($prevVal, $fMeta['step'] === '0.0001' ? 4 : 2) }}
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:12px;">
                                @foreach ($group['fields'] as $fKey => $fMeta)
                                    @php
                                        $val = old("readings.{$gKey}.{$fKey}", $reading->val($gKey, $fKey));
                                        $prevVal = $prevReading?->val($gKey, $fKey);
                                    @endphp
                                    <div>
                                        <label style="font-size:0.76rem; font-weight:600; color:var(--text-muted); display:block; margin-bottom:3px;">
                                            {{ $fMeta['label'] }}
                                        </label>
                                        <div class="input-unit" style="position:relative;">
                                            <input type="number" step="{{ $fMeta['step'] }}" name="readings[{{ $gKey }}][{{ $fKey }}]" class="input" value="{{ $val }}" placeholder="{{ $fMeta['placeholder'] }}" style="width:100%; padding-right:60px; font-size:0.88rem;">
                                            <span style="position:absolute; right:10px; top:50%; transform:translateY(-50%); font-size:0.75rem; color:var(--text-muted); pointer-events:none;">
                                                {{ $fMeta['unit'] }}
                                            </span>
                                        </div>
                                        @if($prevVal !== null)
                                            <div style="font-size:0.68rem; color:var(--text-muted); margin-top:2px;">
                                                Sebelumnya: {{ $n($prevVal, $fMeta['step'] === '0.0001' ? 4 : 2) }}
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        {{-- Lampiran Foto Kompak Per-Grup (Total 6 Foto) --}}
                        @php
                            $groupFoto = $reading?->getFoto($gKey);
                        @endphp
                        <div class="photo-upload-group" style="margin-top:14px; padding-top:10px; border-top:1px dashed rgba(255,255,255,0.08); display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px;">
                            <div style="display:flex; align-items:center; gap:6px;">
                                <span style="font-size:0.75rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.04em;">
                                    📷 Foto Panel {{ $group['title'] }} <span style="font-size:0.68rem; font-weight:normal; color:var(--text-muted);">(Opsional)</span>
                                </span>
                            </div>

                            <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                                {{-- Tombol Galeri --}}
                                <label class="btn-pick-gallery" style="display:inline-flex; align-items:center; gap:5px; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.15); padding:5px 10px; border-radius:6px; cursor:pointer; font-size:0.76rem; color:var(--text-secondary); transition:all 0.2s;" title="Pilih foto dari galeri HP">
                                    <svg viewBox="0 0 20 20" fill="currentColor" style="width:14px;height:14px;color:#38bdf8;"><path fill-rule="evenodd" d="M1 5.25A2.25 2.25 0 0 1 3.25 3h13.5A2.25 2.25 0 0 1 19 5.25v9.5A2.25 2.25 0 0 1 16.75 17H3.25A2.25 2.25 0 0 1 1 14.75v-9.5Zm1.5 5.81v3.69c0 .414.336.75.75.75h13.5a.75.75 0 0 0 .75-.75v-2.69l-2.22-2.219a.75.75 0 0 0-1.06 0l-1.91 1.909-4.22-4.219a.75.75 0 0 0-1.06 0L2.5 11.06Zm10.25-3.81a1.25 1.25 0 1 1-2.5 0 1.25 1.25 0 0 1 2.5 0Z" clip-rule="evenodd"/></svg>
                                    <span>Galeri</span>
                                    <input type="file" name="fotos[{{ $gKey }}]" accept="image/*" class="input-file-main" style="display:none;">
                                </label>

                                {{-- Tombol Kamera --}}
                                <label class="btn-pick-camera" style="display:inline-flex; align-items:center; gap:5px; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.15); padding:5px 10px; border-radius:6px; cursor:pointer; font-size:0.76rem; color:var(--text-secondary); transition:all 0.2s;" title="Ambil foto langsung dari kamera">
                                    <svg viewBox="0 0 20 20" fill="currentColor" style="width:14px;height:14px;color:#fbbf24;"><path fill-rule="evenodd" d="M1 8a2 2 0 0 1 2-2h.93a2 2 0 0 0 1.664-.89l.812-1.22A2 2 0 0 1 8.07 3h3.86a2 2 0 0 1 1.664.89l.812 1.22A2 2 0 0 0 16.07 6H17a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8Zm13.5 3a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM10 14a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" clip-rule="evenodd"/></svg>
                                    <span>Kamera</span>
                                    <input type="file" accept="image/*" capture="environment" class="input-file-camera" style="display:none;">
                                </label>

                                {{-- Chip Pratinjau Ringkas --}}
                                <div class="photo-preview-wrap" style="{{ $groupFoto ? 'display:inline-flex;' : 'display:none;' }} align-items:center; gap:8px; background:rgba(0,0,0,0.45); padding:4px 8px; border-radius:6px; border:1px solid rgba(251,191,36,0.3);">
                                    <img class="img-preview" src="{{ $groupFoto ? asset('storage/'.$groupFoto) : '' }}" alt="Thumb" style="width:30px; height:30px; object-fit:cover; border-radius:4px; border:1px solid rgba(255,255,255,0.15);">
                                    <span class="photo-preview-name" style="font-size:0.72rem; color:var(--text-primary); max-width:140px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                        {{ $groupFoto ? 'Foto tersimpan' : '' }}
                                    </span>
                                    <button type="button" class="btn-clear-photo" style="background:none; border:none; color:#ef4444; font-size:0.95rem; cursor:pointer; line-height:1; padding:0 2px;" title="Batalkan foto">✕</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Catatan Kondisi Panel --}}
        <div style="margin-top:18px; padding-top:14px; border-top:1px solid rgba(255,255,255,0.08);">
            <label style="font-size:0.8rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; display:block; margin-bottom:6px;">
                Catatan Kondisi Panel LVSB (Opsional)
            </label>
            <textarea name="catatan" rows="2" class="input" placeholder="Contoh: Suhu trafo normal, Breaker Riser 3 indikator hijau, Capacitor bank stabil..." style="width:100%; font-size:0.84rem; padding:8px 12px;">{{ old('catatan', $reading->catatan) }}</textarea>
        </div>

        {{-- Tombol Batal & Simpan --}}
        <div style="margin-top:20px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
            <a href="{{ route('public-area.lvsb.index') }}" class="btn">
                Batal
            </a>
            <button type="submit" class="btn btn-primary" style="background:linear-gradient(135deg, #fbbf24, #d97706); border:none; color:#090d15; font-weight:700; padding:10px 24px; font-size:0.92rem; display:inline-flex; align-items:center; gap:8px;">
                <svg viewBox="0 0 20 20" fill="currentColor" style="width:18px;height:18px;"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd"/></svg>
                <span>Simpan Logsheet LVSB</span>
            </button>
        </div>
    </form>
</section>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('.photo-upload-group').forEach(group => {
        const mainInput   = group.querySelector('.input-file-main');
        const cameraInput = group.querySelector('.input-file-camera');
        const previewWrap = group.querySelector('.photo-preview-wrap');
        const imgPreview  = group.querySelector('.img-preview');
        const namePreview = group.querySelector('.photo-preview-name');
        const clearBtn    = group.querySelector('.btn-clear-photo');

        function handleFile(file, source) {
            if (!file) return;
            if (imgPreview) imgPreview.src = URL.createObjectURL(file);
            if (namePreview) {
                const srcLabel = source === 'camera' ? '📷 Kamera' : '🖼️ Galeri';
                namePreview.textContent = `${srcLabel}: ${file.name}`;
            }
            if (previewWrap) previewWrap.style.display = 'flex';
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
                if (this.files && this.files[0]) handleFile(this.files[0], 'gallery');
            });
        }
        if (cameraInput) {
            cameraInput.addEventListener('change', function() {
                if (this.files && this.files[0]) handleFile(this.files[0], 'camera');
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
