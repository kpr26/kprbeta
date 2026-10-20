@extends('layouts.panel')

@php
    $canManage = in_array(auth()->user()->role, ['admin', 'superadmin', 'eng_admin', 'eng_spv'], true);
    $isAdmin = in_array(auth()->user()->role, ['admin', 'superadmin'], true);
    $n = fn ($v) => $v !== null ? number_format($v, 2, ',', '.') : '—';
    $isTeknisi = auth()->user()->role === 'teknisi';
@endphp

@section('title', 'Water Meter Public Area')
@section('page-title', 'Water Meter Public Area')
@section('page-subtitle', 'Pencatatan Mandiri 3 Titik Water Meter Publik: Cooling Tower 12A, Toilet & Mushola, dan Pantry Office')

@section('panel')
<section class="panel">
    {{-- Header Sub Menu --}}
    <div class="panel-head">
        <div>
            <h2><span style="color:#38bdf8;">💧</span> Sub Menu: Water Meter Public Area</h2>
            <p>Pencatatan stand meter air publik secara terpisah untuk setiap titik area publik gedung.</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('public-area.index') }}" class="btn btn-sm" style="background:rgba(255,255,255,0.06); color:var(--text-secondary);">
                ← Menu Public Area
            </a>
            <a href="{{ route('public-area.lvsb.index') }}" class="btn btn-sm" style="background:rgba(251,191,36,0.15); color:#fbbf24; border:1px solid rgba(251,191,36,0.35);">
                ⚡ Pindah ke LVSB
            </a>
            <form class="filter-form" method="GET" style="margin:0;">
                <input type="month" name="periode" value="{{ $periode }}" max="{{ now()->format('Y-m') }}" onchange="this.form.submit()" aria-label="Periode" style="padding:6px 12px; font-size:0.82rem;">
            </form>
            @if ($canManage)
                <a href="{{ route('public-area.excel', ['category' => 'water', 'periode' => $periode]) }}" class="btn btn-excel btn-sm" title="Export Excel">
                    <svg viewBox="0 0 20 20" fill="currentColor" style="width:14px;height:14px;"><path d="M10.75 2.75a.75.75 0 0 0-1.5 0v8.614L6.295 8.235a.75.75 0 1 0-1.09 1.03l4.25 4.5a.75.75 0 0 0 1.09 0l4.25-4.5a.75.75 0 0 0-1.09-1.03l-2.955 3.129V2.75Z"/><path d="M3.5 12.75a.75.75 0 0 0-1.5 0v2.5A2.75 2.75 0 0 0 4.75 18h10.5A2.75 2.75 0 0 0 18 15.25v-2.5a.75.75 0 0 0-1.5 0v2.5c0 .69-.56 1.25-1.25 1.25H4.75c-.69 0-1.25-.56-1.25-1.25v-2.5Z"/></svg>Excel
                </a>
                <a href="{{ route('public-area.pdf', ['category' => 'water', 'periode' => $periode]) }}" target="_blank" class="btn btn-pdf btn-sm" title="Export PDF">
                    <svg viewBox="0 0 20 20" fill="currentColor" style="width:14px;height:14px;"><path fill-rule="evenodd" d="M5 2.75C5 1.784 5.784 1 6.75 1h6.5c.966 0 1.75.784 1.75 1.75v3.552c.377.046.752.097 1.126.153A2.212 2.212 0 0 1 18 8.653v4.097A2.25 2.25 0 0 1 15.75 15h-.241l.305 1.984A1.75 1.75 0 0 1 14.084 19H5.915a1.75 1.75 0 0 1-1.73-2.016L4.492 15H4.25A2.25 2.25 0 0 1 2 12.75V8.653c0-1.082.775-2.034 1.874-2.198.374-.056.75-.107 1.127-.153L5 6.25v-3.5Zm8.5 3.397a41.533 41.533 0 0 0-7 0V2.75a.25.25 0 0 1 .25-.25h6.5a.25.25 0 0 1 .25.25v3.397ZM6.608 12.5a.25.25 0 0 0-.247.212l-.693 4.5a.25.25 0 0 0 .247.288h8.17a.25.25 0 0 0 .246-.288l-.692-4.5a.25.25 0 0 0-.247-.212H6.608Z" clip-rule="evenodd"/></svg>PDF
                </a>
            @endif
        </div>
    </div>

    @if($isTeknisi)
        <div class="offline-feature-pill" style="margin-bottom:20px;">
            <span style="display:inline-block; width:7px; height:7px; border-radius:50%; background:#10b981; box-shadow:0 0 6px #10b981;"></span>
            <span><strong style="color:var(--accent-light);">Mode Offline Aktif:</strong> Anda dapat menginput tiap titik water meter tanpa koneksi sinyal internet. Data otomatis tersinkron saat online.</span>
        </div>
    @endif

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- 3 CARD INPUT TERPISAH WATER METER HARI INI                              --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    <div style="margin-bottom:28px;">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px; flex-wrap:wrap; gap:10px;">
            <div>
                <h3 style="font-size:1.15rem; color:var(--text-primary); margin:0 0 4px; display:flex; align-items:center; gap:8px;">
                    <span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:#38bdf8;"></span>
                    Pencatatan Hari Ini — {{ now()->translatedFormat('l, d F Y') }}
                </h3>
                <p style="font-size:0.8rem; color:var(--text-muted); margin:0;">Masing-masing titik water meter dicatat secara terpisah sesuai lokasi fisiknya.</p>
            </div>
        </div>

        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(320px, 1fr)); gap:18px;">
            @foreach($waterMeters as $meterKey => $meta)
                @php
                    $rec = $todayReadings->get($meterKey);
                    $prev = $prevReadings[$meterKey] ?? null;
                @endphp

                <div class="meter-card" style="border:1px solid {{ $meta['color'] }}44; border-top:4px solid {{ $meta['color'] }}; background:rgba(15,22,35,0.85); padding:18px 20px; border-radius:12px; display:flex; flex-direction:column; justify-content:space-between; box-shadow:0 6px 20px rgba(0,0,0,0.3);">
                    <div>
                        {{-- Card Header --}}
                        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:12px;">
                            <div>
                                <span class="badge" style="background:{{ $meta['color'] }}22; color:{{ $meta['color'] }}; border:1px solid {{ $meta['color'] }}55; font-size:0.72rem; padding:2px 8px; font-weight:700;">
                                    {{ $meta['location'] }}
                                </span>
                                <h4 style="font-size:1.05rem; font-weight:700; color:var(--text-primary); margin:6px 0 2px;">
                                    {{ $meta['name'] }}
                                </h4>
                                <span style="font-size:0.76rem; color:var(--text-muted);">{{ $meta['desc'] }}</span>
                            </div>

                            @if($rec)
                                <span class="badge badge-ok" style="font-size:0.75rem; flex-shrink:0;">✓ Sudah Dicatat</span>
                            @else
                                <span class="badge badge-wait" style="font-size:0.75rem; flex-shrink:0;">Belum Dicatat</span>
                            @endif
                        </div>

                        {{-- Stand Info --}}
                        <div style="background:rgba(255,255,255,0.03); border:1px solid rgba(200,170,110,0.12); border-radius:8px; padding:10px 12px; margin-bottom:14px;">
                            @if($rec)
                                <div style="display:flex; justify-content:space-between; align-items:baseline;">
                                    <span style="font-size:0.78rem; color:var(--text-muted);">Stand Hari Ini:</span>
                                    <span style="font-size:1.35rem; font-weight:700; color:var(--text-primary); font-family:var(--font-serif);">
                                        {{ $n($rec->stand_meter) }} <small style="font-size:0.78rem; color:var(--text-muted); font-family:var(--font-sans);">m³</small>
                                    </span>
                                </div>
                                <div style="display:flex; justify-content:space-between; font-size:0.75rem; color:var(--text-muted); margin-top:4px;">
                                    <span>Petugas: <b style="color:var(--accent-light);">{{ $rec->user?->name ?? 'Teknisi' }}</b></span>
                                    <span>Pukul: {{ $rec->updated_at->format('H:i') }}</span>
                                </div>
                                @if($rec->catatan)
                                    <div style="font-size:0.75rem; color:var(--text-secondary); margin-top:6px; padding-top:6px; border-top:1px dashed rgba(200,170,110,0.15);">
                                        <i>"{{ $rec->catatan }}"</i>
                                    </div>
                                @endif
                            @else
                                <div style="font-size:0.78rem; color:var(--text-muted);">
                                    Stand sebelumnya:
                                    @if($prev)
                                        <b style="color:var(--text-primary);">{{ $n($prev->stand_meter) }} m³</b> ({{ $prev->tanggal->translatedFormat('d M') }})
                                    @else
                                        <i>Belum ada pencatatan sebelumnya</i>
                                    @endif
                                </div>
                            @endif
                        </div>

                        {{-- Foto Preview jika sudah dicatat --}}
                        @if($rec && $rec->foto)
                            <div style="display:flex; align-items:center; gap:10px; margin-bottom:14px; background:rgba(0,0,0,0.2); padding:8px; border-radius:8px;">
                                <img src="{{ asset('storage/'.$rec->foto) }}" alt="Foto Meter" data-lightbox="{{ asset('storage/'.$rec->foto) }}" data-caption="{{ $meta['name'] }} — {{ $rec->tanggal->format('d/m/Y') }}" style="width:50px; height:50px; object-fit:cover; border-radius:6px; border:1px solid rgba(200,170,110,0.3); cursor:pointer;">
                                <div style="font-size:0.76rem; color:var(--text-secondary);">
                                    <strong style="color:var(--text-primary); display:block;">Foto Fisik Meteran</strong>
                                    Klik gambar untuk memperbesar
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Form Input / Aksi --}}
                    <div>
                        @if(! $rec || $canManage)
                            <form method="POST" action="{{ route('public-area.water.store') }}" enctype="multipart/form-data" class="form-offline-pa-water" data-offline-type="public_area_water" data-meter-label="{{ $meta['name'] }}" data-target-redirect="{{ route('public-area.water.index') }}" style="margin-top:10px;">
                                @csrf
                                <input type="hidden" name="meter_key" value="{{ $meterKey }}">

                                <div style="display:flex; flex-direction:column; gap:10px;">
                                    {{-- Input Stand --}}
                                    <div>
                                        <label style="font-size:0.76rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; display:block; margin-bottom:4px;">
                                            {{ $rec ? 'Ubah Stand Meter (m³)' : 'Input Stand Meter (m³)' }} <span style="color:#ef4444;">*</span>
                                        </label>
                                        <div class="input-unit" style="position:relative;">
                                            <input type="number" step="0.01" min="0" inputmode="decimal" name="stand_meter" class="input input-stand-meter" value="{{ old('stand_meter', $rec?->stand_meter) }}" data-prev="{{ $prev?->stand_meter }}" placeholder="0,00" required style="width:100%; padding-right:45px; font-weight:600;">
                                            <span style="position:absolute; right:12px; top:50%; transform:translateY(-50%); font-size:0.8rem; color:var(--text-muted);">m³</span>
                                        </div>
                                        <div class="calc-diff-hint" style="font-size:0.74rem; color:var(--accent-light); margin-top:3px; min-height:16px;"></div>
                                    </div>

                                    {{-- Input Foto (Bisa dari Galeri / File atau Langsung Kamera) --}}
                                    <div class="photo-upload-group">
                                        <label style="font-size:0.76rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; display:block; margin-bottom:4px;">
                                            Lampiran Foto Meteran <span style="color:#ef4444;">{{ $rec ? '(Opsional ganti)' : '*' }}</span>
                                        </label>

                                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px;">
                                            {{-- Opsi 1: Dari Galeri / File HP --}}
                                            <label class="btn-pick-gallery" style="display:flex; align-items:center; justify-content:center; gap:6px; background:rgba(200,170,110,0.08); border:1px solid rgba(200,170,110,0.3); padding:8px 10px; border-radius:8px; cursor:pointer; font-size:0.78rem; color:var(--text-secondary); transition:all 0.2s; text-align:center;">
                                                <svg viewBox="0 0 20 20" fill="currentColor" style="width:16px;height:16px;color:#38bdf8;flex-shrink:0;"><path fill-rule="evenodd" d="M1 5.25A2.25 2.25 0 0 1 3.25 3h13.5A2.25 2.25 0 0 1 19 5.25v9.5A2.25 2.25 0 0 1 16.75 17H3.25A2.25 2.25 0 0 1 1 14.75v-9.5Zm1.5 5.81v3.69c0 .414.336.75.75.75h13.5a.75.75 0 0 0 .75-.75v-2.69l-2.22-2.219a.75.75 0 0 0-1.06 0l-1.91 1.909-4.22-4.219a.75.75 0 0 0-1.06 0L2.5 11.06Zm10.25-3.81a1.25 1.25 0 1 1-2.5 0 1.25 1.25 0 0 1 2.5 0Z" clip-rule="evenodd"/></svg>
                                                <span>🖼️ Dari Galeri</span>
                                                <input type="file" name="foto" accept="image/*" class="input-file-main" style="display:none;" {{ $rec ? '' : 'required' }}>
                                            </label>

                                            {{-- Opsi 2: Kamera Langsung --}}
                                            <label class="btn-pick-camera" style="display:flex; align-items:center; justify-content:center; gap:6px; background:rgba(200,170,110,0.08); border:1px solid rgba(200,170,110,0.3); padding:8px 10px; border-radius:8px; cursor:pointer; font-size:0.78rem; color:var(--text-secondary); transition:all 0.2s; text-align:center;">
                                                <svg viewBox="0 0 20 20" fill="currentColor" style="width:16px;height:16px;color:#fbbf24;flex-shrink:0;"><path fill-rule="evenodd" d="M1 8a2 2 0 0 1 2-2h.93a2 2 0 0 0 1.664-.89l.812-1.22A2 2 0 0 1 8.07 3h3.86a2 2 0 0 1 1.664.89l.812 1.22A2 2 0 0 0 16.07 6H17a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8Zm13.5 3a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM10 14a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" clip-rule="evenodd"/></svg>
                                                <span>📷 Buka Kamera</span>
                                                <input type="file" accept="image/*" capture="environment" class="input-file-camera" style="display:none;">
                                            </label>
                                        </div>

                                        {{-- Pratinjau Foto Terpilih --}}
                                        <div class="photo-preview-wrap" style="display:none; margin-top:8px; align-items:center; gap:10px; background:rgba(0,0,0,0.35); padding:8px 12px; border-radius:8px; border:1px solid rgba(200,170,110,0.25);">
                                            <img class="img-preview" src="" alt="Preview" style="width:48px; height:48px; object-fit:cover; border-radius:6px; border:1px solid rgba(200,170,110,0.4);">
                                            <div style="flex:1; overflow:hidden;">
                                                <div class="photo-preview-name" style="font-size:0.78rem; color:var(--text-primary); font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"></div>
                                                <div class="photo-preview-status" style="font-size:0.72rem; color:#34d399;">✓ Foto siap dilampirkan</div>
                                            </div>
                                            <button type="button" class="btn-clear-photo" style="background:none; border:none; color:#ef4444; font-size:1.1rem; cursor:pointer;" title="Batalkan foto">✕</button>
                                        </div>
                                    </div>

                                    {{-- Catatan Opsional --}}
                                    <div>
                                        <input type="text" name="catatan" class="input" placeholder="Catatan kondisi (opsional)..." value="{{ old('catatan', $rec?->catatan) }}" style="font-size:0.8rem; padding:6px 10px;">
                                    </div>

                                    <button type="submit" class="btn btn-sm btn-primary" style="width:100%; justify-content:center; background:linear-gradient(135deg, {{ $meta['color'] }}, #0284c7); border:none; color:#fff; font-weight:700;">
                                        <svg viewBox="0 0 20 20" fill="currentColor" style="width:16px;height:16px;"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd"/></svg>
                                        Simpan Stand {{ $meta['label'] }}
                                    </button>
                                </div>
                            </form>
                        @else
                            <div style="display:flex; justify-content:flex-end;">
                                <a href="{{ route('public-area.show', $rec) }}" class="btn btn-sm" style="width:100%; justify-content:center; background:rgba(255,255,255,0.04); color:var(--text-secondary);">
                                    Lihat Detail
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- RINGKASAN PEMAKAIAN BULANAN (SUMMARY CARDS)                            --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    <div style="margin-bottom:24px;">
        <h3 style="font-size:1.05rem; color:var(--text-primary); margin:0 0 12px; font-weight:700;">
            Ringkasan Pemakaian Bulanan Periode {{ \Carbon\Carbon::create($year, $month)->translatedFormat('F Y') }}
        </h3>
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(240px, 1fr)); gap:14px;">
            @foreach($waterMeters as $k => $m)
                @php
                    $u = $summaryPerMeter[$k]['totalUsage'] ?? 0;
                @endphp
                <div style="background:rgba(15,22,35,0.7); border:1px solid {{ $m['color'] }}33; border-radius:10px; padding:14px 16px;">
                    <span style="font-size:0.72rem; text-transform:uppercase; color:{{ $m['color'] }}; font-weight:700;">{{ $m['name'] }}</span>
                    <div style="font-size:1.6rem; font-weight:700; color:var(--text-primary); font-family:var(--font-serif); margin:4px 0;">
                        {{ $n($u) }} <small style="font-size:0.8rem; color:var(--text-muted); font-family:var(--font-sans);">m³</small>
                    </div>
                    <span style="font-size:0.76rem; color:var(--text-muted);">{{ $summaryPerMeter[$k]['count'] ?? 0 }} hari tercatat</span>
                </div>
            @endforeach
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- TABEL REKAPITULASI DATA WATER METER PUBLIC AREA                        --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    <div style="margin-top:20px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; flex-wrap:wrap; gap:8px;">
            <h3 style="font-size:1.05rem; font-weight:700; color:var(--text-primary); margin:0;">
                Tabel Rincian Pencatatan Water Meter Public Area
            </h3>
        </div>

        <div class="table-wrap">
            <table class="log-table">
                <thead>
                    <tr>
                        <th class="l" style="width:130px;">Tanggal</th>
                        <th class="l">Meteran Air Public Area</th>
                        <th class="l">Lokasi</th>
                        <th>Stand Meter (m³)</th>
                        <th>Pemakaian (m³)</th>
                        <th class="c">Foto</th>
                        <th class="l">Petugas</th>
                        <th class="c">Status</th>
                        <th class="c">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($rows as $r)
                    @php $meta = $waterMeters[$r->meter_key] ?? null; @endphp
                    <tr>
                        <td class="l">{{ $r->tanggal->translatedFormat('D, d M Y') }}</td>
                        <td class="l" style="font-weight:600; color:{{ $meta['color'] ?? 'var(--text-primary)' }};">
                            {{ $r->meter_name }}
                        </td>
                        <td class="l" style="font-size:0.78rem; color:var(--text-muted);">
                            {{ $meta['location'] ?? '—' }}
                        </td>
                        <td style="font-weight:700;">{{ $n($r->stand_meter) }}</td>
                        <td class="use use-water">{{ $n($r->pemakaian) }}</td>
                        <td class="c">
                            @if ($r->foto)
                                <img src="{{ asset('storage/'.$r->foto) }}" alt="Foto" data-lightbox="{{ asset('storage/'.$r->foto) }}" data-caption="{{ $r->meter_name }} — {{ $r->tanggal->format('d/m/Y') }}" style="width:36px; height:36px; object-fit:cover; border-radius:4px; border:1px solid rgba(200,170,110,0.3); cursor:pointer;">
                            @else
                                <span style="color:var(--text-muted); font-size:0.75rem;">—</span>
                            @endif
                        </td>
                        <td class="l">{{ $r->user?->name ?? 'User Terhapus' }}</td>
                        <td class="c">
                            @if ($r->verified_at)
                                <span class="badge badge-ok" title="Diverifikasi oleh {{ $r->verifier?->name ?? 'Admin' }}">✓ Terverifikasi</span>
                            @else
                                <span class="badge badge-wait">Menunggu</span>
                            @endif
                        </td>
                        <td class="c">
                            <div class="btn-group" style="justify-content:center;">
                                <a href="{{ route('public-area.show', $r) }}" class="btn btn-sm">Detail</a>
                                @if ($canManage && ! $r->verified_at)
                                    <form method="POST" action="{{ route('public-area.verify', $r) }}" class="inline">@csrf
                                        <button class="btn btn-sm btn-success">Verifikasi</button>
                                    </form>
                                @endif
                                @if ($isAdmin || in_array(auth()->user()->role, ['eng_admin', 'eng_spv'], true))
                                    <form method="POST" action="{{ route('public-area.destroy', $r) }}" class="inline" onsubmit="return confirm('Hapus permanen pencatatan {{ $r->meter_name }} {{ $r->tanggal->translatedFormat('d F Y') }}?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-danger">Hapus</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="empty">Belum ada data pencatatan water meter public area pada periode ini.</td>
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
    // Kalkulasi selisih stand hari ini dengan stand kemarin
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
                hint.innerHTML = `<span style="color:#ef4444;">⚠ Stand lebih kecil dari sebelumnya (${diff.toFixed(2)} m³)</span>`;
            } else {
                hint.innerHTML = `<span style="color:#34d399;">Pemakaian: <b>+${diff.toFixed(2)} m³</b></span>`;
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
