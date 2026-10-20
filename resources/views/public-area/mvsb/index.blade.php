@extends('layouts.panel')

@php
    $n = fn ($v, $d = 2) => ($v !== null && $v !== '') ? number_format((float)$v, $d, ',', '.') : '—';
    $canManage = auth()->user()->isSuperAdmin() || auth()->user()->isSupervisor() || auth()->user()->isAdmin();
    $todayStr = \Carbon\Carbon::today()->translatedFormat('l, d F Y');
@endphp

@section('title', 'Logsheet MVSB Incoming From PLN')
@section('page-title', 'MVSB Incoming From PLN')
@section('page-subtitle', 'Pencatatan Parameter Tegangan Menengah (Voltage 20kV) & Arus (Current) Panel Incoming PLN')

@section('panel')
<section class="panel" style="max-width:1200px; margin:0 auto;">
    {{-- Header Sub Menu --}}
    <div class="panel-head" style="align-items:flex-start; flex-wrap:wrap; gap:16px;">
        <div>
            <div style="display:flex; align-items:center; gap:8px;">
                <span style="font-size:1.4rem;">⚡</span>
                <h2 style="margin:0; font-size:1.35rem;">Sub Menu: MVSB Incoming From PLN</h2>
            </div>
            <p style="color:var(--text-secondary); margin:4px 0 0; font-size:0.88rem;">
                Pencatatan harian tegangan menengah 20kV dan arus panel incoming PLN beserta lampiran foto fisik.
            </p>
        </div>

        <div style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
            <a href="{{ route('public-area.index') }}" class="btn btn-sm">
                ← Menu Public Area
            </a>
            <a href="{{ route('public-area.water.index') }}" class="btn btn-sm" style="background:rgba(56,189,248,0.12); color:#38bdf8; border:1px solid rgba(56,189,248,0.3);">
                💧 Water Meter
            </a>
            <a href="{{ route('public-area.lvsb.index') }}" class="btn btn-sm" style="background:rgba(251,191,36,0.12); color:#fbbf24; border:1px solid rgba(251,191,36,0.3);">
                ⚡ LVSB
            </a>

            {{-- Filter Bulan --}}
            <form method="GET" action="{{ route('public-area.mvsb.index') }}" style="display:flex; align-items:center; gap:6px;">
                <input type="month" name="period" value="{{ sprintf('%04d-%02d', $year, $month) }}" class="input" style="padding:6px 10px; font-size:0.84rem;" onchange="
                    const val = this.value.split('-');
                    window.location.href = '{{ route('public-area.mvsb.index') }}?year=' + val[0] + '&month=' + val[1];
                ">
            </form>

            @if($canManage)
                <div style="display:flex; gap:6px;">
                    <a href="{{ route('public-area.mvsb.excel', ['year' => $year, 'month' => $month]) }}" class="btn btn-sm" style="background:#10b981; color:#fff; border:none;">
                        📊 Excel
                    </a>
                    <a href="{{ route('public-area.mvsb.pdf', ['year' => $year, 'month' => $month]) }}" target="_blank" class="btn btn-sm" style="background:#6366f1; color:#fff; border:none;">
                        📄 PDF
                    </a>
                </div>
            @endif
        </div>
    </div>

    {{-- Pill Mode Offline --}}
    @if(auth()->user()->role === 'teknisi')
        <div style="display:inline-flex; align-items:center; gap:8px; background:rgba(200,170,110,0.06); border:1px solid rgba(200,170,110,0.22); border-radius:20px; padding:4px 12px; margin-bottom:16px; font-size:0.78rem; color:var(--text-secondary);">
            <span style="display:inline-block; width:7px; height:7px; border-radius:50%; background:#10b981; box-shadow:0 0 6px #10b981;"></span>
            <span><strong style="color:var(--accent-light);">Mode Offline Aktif:</strong> Anda dapat menginput parameter panel MVSB tanpa sinyal di gardu. Data otomatis tersinkron saat online.</span>
        </div>
    @endif

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- FORM INPUT PENCATATAN HARI INI                                          --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    <div style="background:rgba(15,22,35,0.7); border:1px solid rgba(168,85,247,0.3); border-radius:12px; padding:20px; margin-bottom:28px;">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:16px; border-bottom:1px solid rgba(255,255,255,0.06); padding-bottom:12px;">
            <div>
                <span style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.06em; color:#c084fc; font-weight:700;">Panel Incoming PLN (MVSB 20kV)</span>
                <h3 style="margin:2px 0 0; font-size:1.15rem; color:var(--text-primary); font-weight:700;">
                    Pencatatan Hari Ini — {{ $todayStr }}
                </h3>
            </div>

            @if($todayReading)
                <div style="display:flex; align-items:center; gap:10px;">
                    <span class="badge badge-ok" style="font-size:0.8rem; padding:4px 10px;">
                        ✓ Sudah Dicatat Hari Ini
                    </span>
                    <a href="{{ route('public-area.mvsb.show', $todayReading) }}" class="btn btn-sm" style="background:rgba(255,255,255,0.06); font-size:0.78rem;">
                        Lihat Detail
                    </a>
                </div>
            @endif
        </div>

        <form method="POST" action="{{ route('public-area.mvsb.store') }}" enctype="multipart/form-data" class="form-offline-pa-mvsb" data-offline-type="public_area_mvsb" data-meter-label="MVSB Incoming From PLN" data-target-redirect="{{ route('public-area.mvsb.index') }}">
            @csrf
            <input type="hidden" name="tanggal" value="{{ \Carbon\Carbon::today()->toDateString() }}">

            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(260px, 1fr)); gap:16px;">
                {{-- Field 1: Voltage 20kV --}}
                <div class="field-item">
                    <label style="font-size:0.8rem; font-weight:700; color:var(--text-secondary); text-transform:uppercase; display:block; margin-bottom:6px;">
                        Voltage 20kV <span style="color:#ef4444;">*</span>
                    </label>
                    <div style="position:relative;">
                        <input type="number" step="0.01" name="voltage_20kv" class="input" value="{{ old('voltage_20kv', $todayReading?->voltage_20kv) }}" placeholder="Contoh: 20000" style="width:100%; padding-right:60px; font-size:1.05rem; font-weight:600;">
                        <span style="position:absolute; right:12px; top:50%; transform:translateY(-50%); font-size:0.8rem; color:var(--text-muted); pointer-events:none; font-weight:600;">
                            Volt
                        </span>
                    </div>
                    @if($prevReading && $prevReading->voltage_20kv !== null)
                        <div style="font-size:0.72rem; color:var(--text-muted); margin-top:4px;">
                            Sebelumnya ({{ $prevReading->tanggal->format('d/m') }}): <b>{{ $n($prevReading->voltage_20kv, 1) }} Volt</b>
                        </div>
                    @endif
                </div>

                {{-- Field 2: Current --}}
                <div class="field-item">
                    <label style="font-size:0.8rem; font-weight:700; color:var(--text-secondary); text-transform:uppercase; display:block; margin-bottom:6px;">
                        Current <span style="color:#ef4444;">*</span>
                    </label>
                    <div style="position:relative;">
                        <input type="number" step="0.01" name="current" class="input" value="{{ old('current', $todayReading?->current) }}" placeholder="Contoh: 25.5" style="width:100%; padding-right:75px; font-size:1.05rem; font-weight:600;">
                        <span style="position:absolute; right:12px; top:50%; transform:translateY(-50%); font-size:0.8rem; color:var(--text-muted); pointer-events:none; font-weight:600;">
                            Ampere
                        </span>
                    </div>
                    @if($prevReading && $prevReading->current !== null)
                        <div style="font-size:0.72rem; color:var(--text-muted); margin-top:4px;">
                            Sebelumnya ({{ $prevReading->tanggal->format('d/m') }}): <b>{{ $n($prevReading->current, 2) }} Ampere</b>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Lampiran Foto & Catatan --}}
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:16px; margin-top:18px; padding-top:16px; border-top:1px dashed rgba(255,255,255,0.08);">
                {{-- Lampiran Foto Fisik Panel MVSB --}}
                <div class="photo-upload-group">
                    <label style="font-size:0.78rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; display:block; margin-bottom:6px;">
                        📷 Foto Fisik Panel MVSB <span style="font-size:0.72rem; color:var(--text-muted); font-weight:normal;">(Bisa Galeri / Kamera Langsung)</span>
                    </label>

                    <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                        {{-- Tombol Galeri --}}
                        <label class="btn-pick-gallery" style="display:inline-flex; align-items:center; gap:6px; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.15); padding:7px 12px; border-radius:6px; cursor:pointer; font-size:0.8rem; color:var(--text-secondary); transition:all 0.2s;" title="Pilih foto dari galeri HP / File">
                            <svg viewBox="0 0 20 20" fill="currentColor" style="width:15px;height:15px;color:#38bdf8;"><path fill-rule="evenodd" d="M1 5.25A2.25 2.25 0 0 1 3.25 3h13.5A2.25 2.25 0 0 1 19 5.25v9.5A2.25 2.25 0 0 1 16.75 17H3.25A2.25 2.25 0 0 1 1 14.75v-9.5Zm1.5 5.81v3.69c0 .414.336.75.75.75h13.5a.75.75 0 0 0 .75-.75v-2.69l-2.22-2.219a.75.75 0 0 0-1.06 0l-1.91 1.909-4.22-4.219a.75.75 0 0 0-1.06 0L2.5 11.06Zm10.25-3.81a1.25 1.25 0 1 1-2.5 0 1.25 1.25 0 0 1 2.5 0Z" clip-rule="evenodd"/></svg>
                            <span>🖼️ Galeri</span>
                            <input type="file" name="foto" accept="image/*" class="input-file-main" style="display:none;">
                        </label>

                        {{-- Tombol Kamera --}}
                        <label class="btn-pick-camera" style="display:inline-flex; align-items:center; gap:6px; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.15); padding:7px 12px; border-radius:6px; cursor:pointer; font-size:0.8rem; color:var(--text-secondary); transition:all 0.2s;" title="Ambil foto langsung dengan kamera">
                            <svg viewBox="0 0 20 20" fill="currentColor" style="width:15px;height:15px;color:#c084fc;"><path fill-rule="evenodd" d="M1 8a2 2 0 0 1 2-2h.93a2 2 0 0 0 1.664-.89l.812-1.22A2 2 0 0 1 8.07 3h3.86a2 2 0 0 1 1.664.89l.812 1.22A2 2 0 0 0 16.07 6H17a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8Zm13.5 3a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM10 14a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" clip-rule="evenodd"/></svg>
                            <span>📷 Kamera</span>
                            <input type="file" accept="image/*" capture="environment" class="input-file-camera" style="display:none;">
                        </label>

                        {{-- Chip Pratinjau Foto --}}
                        <div class="photo-preview-wrap" style="{{ $todayReading?->foto ? 'display:inline-flex;' : 'display:none;' }} align-items:center; gap:8px; background:rgba(0,0,0,0.45); padding:5px 10px; border-radius:6px; border:1px solid rgba(168,85,247,0.3);">
                            <img class="img-preview" src="{{ $todayReading?->foto ? asset('storage/'.$todayReading->foto) : '' }}" alt="Thumb" style="width:36px; height:36px; object-fit:cover; border-radius:4px; border:1px solid rgba(255,255,255,0.15);">
                            <span class="photo-preview-name" style="font-size:0.75rem; color:var(--text-primary); max-width:160px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                {{ $todayReading?->foto ? 'Foto tersimpan' : '' }}
                            </span>
                            <button type="button" class="btn-clear-photo" style="background:none; border:none; color:#ef4444; font-size:1rem; cursor:pointer; line-height:1; padding:0 3px;" title="Batalkan foto">✕</button>
                        </div>
                    </div>
                </div>

                {{-- Catatan --}}
                <div>
                    <label style="font-size:0.78rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; display:block; margin-bottom:6px;">
                        Catatan Kondisi Panel (Opsional)
                    </label>
                    <textarea name="catatan" rows="2" class="input" placeholder="Contoh: Indikator tegangan stabil, suhu ruang MVSB normal..." style="width:100%; font-size:0.84rem; padding:8px 12px;">{{ old('catatan', $todayReading?->catatan) }}</textarea>
                </div>
            </div>

            {{-- Tombol Simpan --}}
            <div style="margin-top:18px; display:flex; justify-content:flex-end;">
                <button type="submit" class="btn btn-primary" style="background:linear-gradient(135deg, #a855f7, #7c3aed); border:none; color:#fff; font-weight:700; padding:10px 24px; font-size:0.92rem; display:inline-flex; align-items:center; gap:8px; box-shadow:0 4px 12px rgba(168,85,247,0.3);">
                    <svg viewBox="0 0 20 20" fill="currentColor" style="width:18px;height:18px;"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd"/></svg>
                    <span>Simpan Logsheet MVSB</span>
                </button>
            </div>
        </form>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- TABEL RIWAYAT PENCATATAN BULAN INI                                     --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    <div class="panel-head" style="margin-top:12px; margin-bottom:12px;">
        <div>
            <h3 style="margin:0; font-size:1.1rem; color:var(--text-primary);">
                Riwayat Pencatatan MVSB — {{ \Carbon\Carbon::createFromDate($year, $month, 1)->translatedFormat('F Y') }}
            </h3>
            <span style="font-size:0.8rem; color:var(--text-secondary);">
                Total {{ $rows->count() }} hari tercatat dalam bulan ini.
            </span>
        </div>
    </div>

    <div class="table-responsive" style="border:1px solid rgba(255,255,255,0.06); border-radius:10px; overflow:hidden;">
        <table class="table" style="width:100%; margin:0; font-size:0.86rem;">
            <thead>
                <tr style="background:rgba(255,255,255,0.03);">
                    <th style="padding:10px 14px; text-align:left;">Tanggal</th>
                    <th style="padding:10px 14px; text-align:right;">Voltage 20kV (Volt)</th>
                    <th style="padding:10px 14px; text-align:right;">Current (Ampere)</th>
                    <th style="padding:10px 14px; text-align:center;">Foto Fisik</th>
                    <th style="padding:10px 14px; text-align:left;">Pencatat</th>
                    <th style="padding:10px 14px; text-align:center;">Status</th>
                    <th style="padding:10px 14px; text-align:center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $r)
                    <tr style="border-top:1px solid rgba(255,255,255,0.04);">
                        <td style="padding:10px 14px; white-space:nowrap;">
                            <span style="font-weight:600; color:var(--text-primary);">
                                {{ $r->tanggal->translatedFormat('d M Y') }}
                            </span>
                            <small style="display:block; color:var(--text-muted); font-size:0.74rem;">
                                {{ $r->tanggal->translatedFormat('l') }}
                            </small>
                        </td>

                        <td style="padding:10px 14px; text-align:right; font-weight:600; color:#c084fc;">
                            {{ $n($r->voltage_20kv, 1) }} <small style="font-size:0.72rem; color:var(--text-muted); font-weight:normal;">Volt</small>
                        </td>

                        <td style="padding:10px 14px; text-align:right; font-weight:600; color:#38bdf8;">
                            {{ $n($r->current, 2) }} <small style="font-size:0.72rem; color:var(--text-muted); font-weight:normal;">A</small>
                        </td>

                        <td style="padding:10px 14px; text-align:center;">
                            @if($r->foto)
                                <a href="{{ asset('storage/'.$r->foto) }}" data-lightbox="{{ asset('storage/'.$r->foto) }}" data-caption="MVSB 20kV — {{ $r->tanggal->format('d/m/Y') }}" style="display:inline-block; border-radius:4px; overflow:hidden; border:1px solid rgba(168,85,247,0.3);">
                                    <img src="{{ asset('storage/'.$r->foto) }}" alt="Foto" style="width:34px; height:34px; object-fit:cover; display:block;">
                                </a>
                            @else
                                <span style="color:var(--text-muted); font-size:0.74rem;">—</span>
                            @endif
                        </td>

                        <td style="padding:10px 14px; white-space:nowrap; color:var(--text-secondary); font-size:0.8rem;">
                            {{ $r->user?->name ?? 'Teknisi' }}
                        </td>

                        <td style="padding:10px 14px; text-align:center;">
                            @if($r->isVerified())
                                <span class="badge badge-ok" style="font-size:0.72rem;" title="Diverifikasi oleh {{ $r->verifier?->name ?? 'Supervisor' }} pada {{ $r->verified_at?->format('d/m/Y H:i') }}">
                                    ✓ Diverifikasi
                                </span>
                            @else
                                <span class="badge badge-wait" style="font-size:0.72rem;">
                                    Menunggu
                                </span>
                            @endif
                        </td>

                        <td style="padding:10px 14px; text-align:center; white-space:nowrap;">
                            <div style="display:inline-flex; align-items:center; gap:6px;">
                                <a href="{{ route('public-area.mvsb.show', $r) }}" class="btn btn-sm" style="padding:4px 8px; font-size:0.75rem;" title="Lihat detail pembacaan">
                                    Detail
                                </a>

                                @if($canManage && !$r->isVerified())
                                    <form method="POST" action="{{ route('public-area.mvsb.verify', $r) }}" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-sm" style="background:rgba(16,185,129,0.15); color:#34d399; border:1px solid rgba(16,185,129,0.3); padding:4px 8px; font-size:0.75rem;" title="Verifikasi Logsheet">
                                            ✓
                                        </button>
                                    </form>
                                @endif

                                @if(auth()->user()->isSuperAdmin())
                                    <form method="POST" action="{{ route('public-area.mvsb.destroy', $r) }}" onsubmit="return confirm('Hapus data logsheet MVSB tanggal {{ $r->tanggal->format('d/m/Y') }}?');" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm" style="background:rgba(239,68,68,0.12); color:#f87171; border:1px solid rgba(239,68,68,0.25); padding:4px 8px; font-size:0.75rem;" title="Hapus Permanen">
                                            ✕
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="padding:32px 14px; text-align:center; color:var(--text-muted);">
                            Belum ada data pencatatan MVSB Incoming From PLN pada bulan {{ \Carbon\Carbon::createFromDate($year, $month, 1)->translatedFormat('F Y') }}.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
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
            if (previewWrap) previewWrap.style.display = 'inline-flex';
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
