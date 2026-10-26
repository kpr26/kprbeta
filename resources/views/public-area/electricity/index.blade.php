@extends('layouts.panel')

@php
    $canManage = in_array(auth()->user()->role, ['admin', 'superadmin', 'eng_admin', 'eng_spv'], true);
    $isAdmin = in_array(auth()->user()->role, ['admin', 'superadmin'], true);
    $n = fn ($v) => $v !== null ? number_format($v, 2, ',', '.') : '—';
    $isTeknisi = auth()->user()->role === 'teknisi';
@endphp

@section('title', 'KWh Meter Public Area')
@section('page-title', 'KWh Meter Public Area')
@section('page-subtitle', 'Pencatatan Stand Meteran Listrik Penerangan & Beban Daya Area Publik Gedung')

@section('panel')
<section class="panel">
    {{-- Header Sub Menu --}}
    <div class="panel-head">
        <div>
            <h2><span style="color:var(--warning);">⚡</span> Sub Menu: KWh Meter Public Area</h2>
            <p>Pencatatan stand meteran listrik untuk utilitas penerangan dan daya area publik gedung.</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('public-area.index') }}" class="btn btn-sm">
                ← Menu Public Area
            </a>
            <a href="{{ route('public-area.water.index') }}" class="btn btn-sm" style="background:rgba(56,189,248,0.15); color:var(--info); border:1px solid rgba(56,189,248,0.35);">
                💧 Pindah ke Water Meter
            </a>
            <form class="filter-form" method="GET" style="margin:0;">
                <input type="month" name="periode" value="{{ $periode }}" max="{{ now()->format('Y-m') }}" onchange="this.form.submit()" aria-label="Periode" style="padding:6px 12px; font-size:0.82rem;">
            </form>
            @if ($canManage)
                <a href="{{ route('public-area.excel', ['category' => 'electricity', 'periode' => $periode]) }}" class="btn btn-excel btn-sm" title="Export Excel">
                    <svg viewBox="0 0 20 20" fill="currentColor" style="width:14px;height:14px;"><path d="M10.75 2.75a.75.75 0 0 0-1.5 0v8.614L6.295 8.235a.75.75 0 1 0-1.09 1.03l4.25 4.5a.75.75 0 0 0 1.09 0l4.25-4.5a.75.75 0 0 0-1.09-1.03l-2.955 3.129V2.75Z"/><path d="M3.5 12.75a.75.75 0 0 0-1.5 0v2.5A2.75 2.75 0 0 0 4.75 18h10.5A2.75 2.75 0 0 0 18 15.25v-2.5a.75.75 0 0 0-1.5 0v2.5c0 .69-.56 1.25-1.25 1.25H4.75c-.69 0-1.25-.56-1.25-1.25v-2.5Z"/></svg>Excel
                </a>
                <a href="{{ route('public-area.pdf', ['category' => 'electricity', 'periode' => $periode]) }}" target="_blank" class="btn btn-pdf btn-sm" title="Export PDF">
                    <svg viewBox="0 0 20 20" fill="currentColor" style="width:14px;height:14px;"><path fill-rule="evenodd" d="M5 2.75C5 1.784 5.784 1 6.75 1h6.5c.966 0 1.75.784 1.75 1.75v3.552c.377.046.752.097 1.126.153A2.212 2.212 0 0 1 18 8.653v4.097A2.25 2.25 0 0 1 15.75 15h-.241l.305 1.984A1.75 1.75 0 0 1 14.084 19H5.915a1.75 1.75 0 0 1-1.73-2.016L4.492 15H4.25A2.25 2.25 0 0 1 2 12.75V8.653c0-1.082.775-2.034 1.874-2.198.374-.056.75-.107 1.127-.153L5 6.25v-3.5Zm8.5 3.397a41.533 41.533 0 0 0-7 0V2.75a.25.25 0 0 1 .25-.25h6.5a.25.25 0 0 1 .25.25v3.397ZM6.608 12.5a.25.25 0 0 0-.247.212l-.693 4.5a.25.25 0 0 0 .247.288h8.17a.25.25 0 0 0 .246-.288l-.692-4.5a.25.25 0 0 0-.247-.212H6.608Z" clip-rule="evenodd"/></svg>PDF
                </a>
            @endif
        </div>
    </div>

    @if($isTeknisi)
        <div class="offline-feature-pill" style="margin-bottom:20px;">
            <span style="display:inline-block; width:7px; height:7px; border-radius:50%; background:#10b981; box-shadow:0 0 6px #10b981;"></span>
            <span><strong style="color:var(--accent-light);">Mode Offline Aktif:</strong> Anda dapat menginput stand KWh meter tanpa sinyal. Data otomatis tersinkron saat HP online.</span>
        </div>
    @endif

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- CARD INPUT KWH METER HARI INI                                          --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    <div style="margin-bottom:28px;">
        <div class="meter-card" style="border:1px solid rgba(251,191,36,0.35); border-top:4px solid var(--warning); background:var(--bg-card); padding:20px 24px; border-radius:12px; box-shadow:var(--shadow-sm);">
            <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:14px; flex-wrap:wrap; gap:10px;">
                <div>
                    <span class="badge" style="background:rgba(251,191,36,0.15); color:var(--warning); border:1px solid rgba(251,191,36,0.35); font-size:0.75rem; padding:3px 10px; font-weight:700;">
                        Panel Distribusi Public Area
                    </span>
                    <h3 style="font-size:1.25rem; font-weight:700; color:var(--text-primary); margin:8px 0 2px;">
                        KWh Meter Public Area
                    </h3>
                    <p style="font-size:0.82rem; color:var(--text-muted); margin:0;">
                        Hari ini: <b>{{ now()->translatedFormat('l, d F Y') }}</b>
                    </p>
                </div>

                @if($todayReading)
                    <span class="badge badge-ok" style="font-size:0.8rem; padding:6px 12px;">✓ Sudah Dicatat Hari Ini</span>
                @else
                    <span class="badge badge-wait" style="font-size:0.8rem; padding:6px 12px;">Belum Dicatat Hari Ini</span>
                @endif
            </div>

            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:20px;">
                {{-- Info Box Stand & Foto --}}
                <div style="background:var(--bg-card-subtle); border:1px solid var(--border); border-radius:10px; padding:16px;">
                    @if($todayReading)
                        <div style="margin-bottom:12px;">
                            <span style="font-size:0.78rem; color:var(--text-muted); text-transform:uppercase; font-weight:700;">Angka Stand Hari Ini</span>
                            <div style="font-size:2rem; font-weight:700; color:var(--text-primary); font-family:var(--font-serif); margin-top:2px;">
                                {{ $n($todayReading->stand_meter) }} <small style="font-size:0.9rem; color:var(--text-muted); font-family:var(--font-sans);">kWh</small>
                            </div>
                            <div style="font-size:0.78rem; color:var(--text-muted); margin-top:4px;">
                                Dicatat oleh <b style="color:var(--accent-light);">{{ $todayReading->user?->name ?? 'Teknisi' }}</b> pukul {{ $todayReading->updated_at->format('H:i') }}
                            </div>
                        </div>

                        @if($todayReading->foto)
                            <div style="display:flex; align-items:center; gap:12px; margin-top:14px; padding-top:12px; border-top:1px solid var(--border);">
                                <img src="{{ asset('storage/'.$todayReading->foto) }}" alt="Foto KWh" data-lightbox="{{ asset('storage/'.$todayReading->foto) }}" data-caption="KWh Meter Public Area — {{ $todayReading->tanggal->format('d/m/Y') }}" style="width:60px; height:60px; object-fit:cover; border-radius:8px; border:1px solid rgba(200,170,110,0.3); cursor:pointer;">
                                <div style="font-size:0.78rem; color:var(--text-secondary);">
                                    <strong style="color:var(--text-primary); display:block;">Foto Fisik Meteran</strong>
                                    Klik gambar untuk memperbesar
                                </div>
                            </div>
                        @endif
                    @else
                        <div>
                            <span style="font-size:0.78rem; color:var(--text-muted); text-transform:uppercase; font-weight:700;">Pencatatan Sebelumnya</span>
                            @if($prevReading)
                                <div style="font-size:1.4rem; font-weight:700; color:var(--text-primary); font-family:var(--font-serif); margin:6px 0 2px;">
                                    {{ $n($prevReading->stand_meter) }} <small style="font-size:0.8rem; color:var(--text-muted); font-family:var(--font-sans);">kWh</small>
                                </div>
                                <span style="font-size:0.78rem; color:var(--text-muted);">
                                    Tanggal {{ $prevReading->tanggal->translatedFormat('d F Y') }} oleh {{ $prevReading->user?->name ?? 'Teknisi' }}
                                </span>
                            @else
                                <p style="font-size:0.82rem; color:var(--text-muted); margin-top:6px;">Belum ada riwayat stand sebelumnya.</p>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Form Input / Update --}}
                <div>
                    @if(! $todayReading || $canManage)
                        <form method="POST" action="{{ route('public-area.electricity.store') }}" enctype="multipart/form-data" class="form-offline-pa-electricity" data-offline-type="public_area_electricity" data-meter-label="KWh Meter Public Area" data-target-redirect="{{ route('public-area.electricity.index') }}">
                            @csrf
                            <div style="display:flex; flex-direction:column; gap:12px;">
                                <div>
                                    <label style="font-size:0.78rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; display:block; margin-bottom:4px;">
                                        {{ $todayReading ? 'Ubah Stand Meter (kWh)' : 'Input Stand Meter (kWh)' }} <span style="color:#ef4444;">*</span>
                                    </label>
                                    <div class="input-unit" style="position:relative;">
                                        <input type="number" step="0.01" min="0" inputmode="decimal" name="stand_meter" class="input input-stand-meter" value="{{ old('stand_meter', $todayReading?->stand_meter) }}" data-prev="{{ $prevReading?->stand_meter }}" placeholder="0,00" required style="width:100%; padding-right:50px; font-weight:600;">
                                        <span style="position:absolute; right:12px; top:50%; transform:translateY(-50%); font-size:0.8rem; color:var(--text-muted);">kWh</span>
                                    </div>
                                    <div class="calc-diff-hint" style="font-size:0.75rem; color:var(--accent-light); margin-top:4px; min-height:16px;"></div>
                                </div>

                                <div class="photo-upload-group">
                                    <label style="font-size:0.78rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; display:block; margin-bottom:4px;">
                                        Foto Fisik KWh Meter <span style="color:#ef4444;">{{ $todayReading ? '(Opsional ganti)' : '*' }}</span>
                                    </label>
                                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px;">
                                        {{-- Opsi 1: Galeri / File --}}
                                        <label class="btn-pick-gallery" style="display:flex; align-items:center; justify-content:center; gap:6px; background:var(--bg-card-subtle); border:1px solid var(--border); padding:8px 10px; border-radius:6px; cursor:pointer; font-size:0.8rem; color:var(--text-secondary); transition:all 0.2s; text-align:center;">
                                            <svg viewBox="0 0 20 20" fill="currentColor" style="width:16px;height:16px;color:var(--info);flex-shrink:0;"><path fill-rule="evenodd" d="M1 5.25A2.25 2.25 0 0 1 3.25 3h13.5A2.25 2.25 0 0 1 19 5.25v9.5A2.25 2.25 0 0 1 16.75 17H3.25A2.25 2.25 0 0 1 1 14.75v-9.5Zm1.5 5.81v3.69c0 .414.336.75.75.75h13.5a.75.75 0 0 0 .75-.75v-2.69l-2.22-2.219a.75.75 0 0 0-1.06 0l-1.91 1.909-4.22-4.219a.75.75 0 0 0-1.06 0L2.5 11.06Zm10.25-3.81a1.25 1.25 0 1 1-2.5 0 1.25 1.25 0 0 1 2.5 0Z" clip-rule="evenodd"/></svg>
                                            <span>🖼️ Dari Galeri</span>
                                            <input type="file" name="foto" accept="image/*" class="input-file-main" style="display:none;" {{ $todayReading ? '' : 'required' }}>
                                        </label>

                                        {{-- Opsi 2: Buka Kamera --}}
                                        <label class="btn-pick-camera" style="display:flex; align-items:center; justify-content:center; gap:6px; background:var(--bg-card-subtle); border:1px solid var(--border); padding:8px 10px; border-radius:6px; cursor:pointer; font-size:0.8rem; color:var(--text-secondary); transition:all 0.2s; text-align:center;">
                                            <svg viewBox="0 0 20 20" fill="currentColor" style="width:16px;height:16px;color:var(--warning);flex-shrink:0;"><path fill-rule="evenodd" d="M1 8a2 2 0 0 1 2-2h.93a2 2 0 0 0 1.664-.89l.812-1.22A2 2 0 0 1 8.07 3h3.86a2 2 0 0 1 1.664.89l.812 1.22A2 2 0 0 0 16.07 6H17a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8Zm13.5 3a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM10 14a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" clip-rule="evenodd"/></svg>
                                            <span>📷 Buka Kamera</span>
                                            <input type="file" accept="image/*" capture="environment" class="input-file-camera" style="display:none;">
                                        </label>
                                    </div>

                                    {{-- Pratinjau Foto Terpilih --}}
                                    <div class="photo-preview-wrap" style="display:none; margin-top:8px; align-items:center; gap:10px; background:var(--bg-card-subtle); padding:8px 12px; border-radius:8px; border:1px solid var(--border);">
                                        <img class="img-preview" src="" alt="Preview" style="width:48px; height:48px; object-fit:cover; border-radius:6px; border:1px solid var(--border);">
                                        <div style="flex:1; overflow:hidden;">
                                            <div class="photo-preview-name" style="font-size:0.78rem; color:var(--text-primary); font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"></div>
                                            <div class="photo-preview-status" style="font-size:0.72rem; color:var(--success);">✓ Foto siap dilampirkan</div>
                                        </div>
                                        <button type="button" class="btn-clear-photo" style="background:none; border:none; color:#ef4444; font-size:1.1rem; cursor:pointer;" title="Batalkan foto">✕</button>
                                    </div>
                                </div>

                                <div>
                                    <input type="text" name="catatan" class="input" placeholder="Catatan kondisi (opsional)..." value="{{ old('catatan', $todayReading?->catatan) }}" style="font-size:0.82rem; padding:8px 12px;">
                                </div>

                                <button type="submit" class="btn btn-primary" style="justify-content:center; background:linear-gradient(135deg, #fbbf24, #d97706); border:none; color:#090d15; font-weight:700;">
                                    <svg viewBox="0 0 20 20" fill="currentColor" style="width:16px;height:16px;"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd"/></svg>
                                    Simpan Stand KWh Meter Public Area
                                </button>
                            </div>
                        </form>
                    @else
                        <div style="display:flex; justify-content:flex-end;">
                            <a href="{{ route('public-area.show', $todayReading) }}" class="btn btn-sm">Lihat Detail Data</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- RINGKASAN PEMAKAIAN LISTRIK BULANAN                                    --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    <div style="margin-bottom:24px;">
        <div style="background:var(--bg-card); border:1px solid rgba(251,191,36,0.3); border-radius:10px; padding:16px 20px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; box-shadow:var(--shadow-sm);">
            <div>
                <span style="font-size:0.75rem; text-transform:uppercase; color:#fbbf24; font-weight:700; letter-spacing:0.06em;">Total Konsumsi Listrik Public Area</span>
                <div style="font-size:1.8rem; font-weight:700; color:var(--text-primary); font-family:var(--font-serif); margin-top:2px;">
                    {{ $n($totalKwhUsage) }} <small style="font-size:0.85rem; color:var(--text-muted); font-family:var(--font-sans);">kWh</small>
                </div>
            </div>
            <div style="font-size:0.82rem; color:var(--text-secondary); text-align:right;">
                Periode: <b>{{ \Carbon\Carbon::create($year, $month)->translatedFormat('F Y') }}</b><br>
                <span>{{ $rows->count() }} hari pencatatan</span>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- TABEL REKAPITULASI KWH METER PUBLIC AREA                               --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    <div>
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; flex-wrap:wrap; gap:8px;">
            <h3 style="font-size:1.05rem; font-weight:700; color:var(--text-primary); margin:0;">
                Tabel Rincian Pencatatan KWh Meter Public Area
            </h3>
        </div>

        <div class="table-wrap">
            <table class="log-table">
                <thead>
                    <tr>
                        <th class="l" style="width:130px;">Tanggal</th>
                        <th class="l">Panel / Meteran</th>
                        <th>Stand Meter (kWh)</th>
                        <th>Pemakaian (kWh)</th>
                        <th style="color:var(--accent-light);">Akumulatif (kWh)</th>
                        <th class="c">Foto</th>
                        <th class="l">Petugas</th>
                        <th class="c">Status</th>
                        <th class="c">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($rows as $r)
                    <tr>
                        <td class="l">{{ $r->tanggal->translatedFormat('D, d M Y') }}</td>
                        <td class="l" style="font-weight:600; color:var(--warning);">{{ $r->meter_name }}</td>
                        <td style="font-weight:700;">{{ $n($r->stand_meter) }}</td>
                        <td class="use use-elec">{{ $n($r->pemakaian) }}</td>
                        <td class="col-highlight-cum">{{ $n($r->cum_usage) }}</td>
                        <td class="c">
                            @if ($r->foto)
                                <img src="{{ asset('storage/'.$r->foto) }}" alt="Foto" data-lightbox="{{ asset('storage/'.$r->foto) }}" data-caption="{{ $r->meter_name }} — {{ $r->tanggal->format('d/m/Y') }}" style="width:36px; height:36px; object-fit:cover; border-radius:4px; border:1px solid rgba(200,170,110,0.3); cursor:pointer;">
                            @else
                                <span style="color:var(--text-muted); font-size:0.75rem;">—</span>
                            @endif
                        </td>
                        <td class="l">{{ $r->user?->name ?? 'User Terhapus' }}</td>
                        <td class="c">
                            @if ($r->isVerified())
                                <span class="badge badge-ok" title="{{ $r->verified_at ? 'Diverifikasi oleh ' . ($r->verifier?->name ?? 'Admin') : 'Otomatis Terverifikasi (Input Admin / Backdate)' }}">✓ Terverifikasi</span>
                            @else
                                <span class="badge badge-wait">Menunggu</span>
                            @endif
                        </td>
                        <td class="c">
                            <div class="btn-group" style="justify-content:center;">
                                <a href="{{ route('public-area.show', $r) }}" class="btn btn-sm">Detail</a>
                                @if ($canManage && $r->needsVerification())
                                    <form method="POST" action="{{ route('public-area.verify', $r) }}" class="inline">@csrf
                                        <button class="btn btn-sm btn-success">Verifikasi</button>
                                    </form>
                                @endif
                                @if ($isAdmin || in_array(auth()->user()->role, ['eng_admin', 'eng_spv'], true))
                                    <form method="POST" action="{{ route('public-area.destroy', $r) }}" class="inline" onsubmit="return confirm('Hapus permanen pencatatan KWh meter {{ $r->tanggal->translatedFormat('d F Y') }}?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-danger">Hapus</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="empty">Belum ada data pencatatan KWh meter public area pada periode ini.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('.input-stand-meter').forEach(inp => {
        const hint = inp.closest('div').parentElement.querySelector('.calc-diff-hint');
        const update = () => {
            if (!hint) return;
            const prev = parseFloat(inp.dataset.prev);
            const cur = parseFloat(inp.value);
            if (isNaN(cur) || isNaN(prev)) {
                hint.textContent = '';
                return;
            }
            const diff = cur - prev;
            if (diff < 0) {
                hint.innerHTML = `<span style="color:#ef4444;">⚠ Stand lebih kecil dari sebelumnya (${diff.toFixed(2)} kWh)</span>`;
            } else {
                hint.innerHTML = `<span style="color:#34d399;">Pemakaian: <b>+${diff.toFixed(2)} kWh</b></span>`;
            }
        };
        inp.addEventListener('input', update);
        update();
    });

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
