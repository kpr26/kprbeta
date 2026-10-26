@extends('layouts.panel')

@php
    $n = fn ($v, $d = 2) => ($v !== null && $v !== '') ? number_format((float)$v, $d, ',', '.') : '—';
    $canManage = auth()->user()->isSuperAdmin() || auth()->user()->isSupervisor() || auth()->user()->isAdmin();
    $isSuperAdmin = auth()->user()->isSuperAdmin();
    $todayStr = \Carbon\Carbon::today()->translatedFormat('l, d F Y');
    $intervalDays = (int) round($startDate->diffInDays($endDate) + 1);
@endphp

@section('title', 'Logsheet LVSB Public Area')
@section('page-title', 'LVSB (Low Voltage Switchboard)')
@section('page-subtitle', 'Pencatatan Parameter Tegangan, Arus, Daya, Energi & PF Panel Distribusi LVSB')

@section('panel')
<section class="panel" style="max-width:1300px; margin:0 auto;">
    {{-- Header Sub Menu --}}
    <div class="panel-head" style="align-items:flex-start; flex-wrap:wrap; gap:16px;">
        <div>
            <div style="display:flex; align-items:center; gap:8px;">
                <span style="font-size:1.4rem;">⚡</span>
                <h2 style="margin:0; font-size:1.35rem;">Sub Menu: LVSB (Low Voltage Switchboard)</h2>
            </div>
            <p style="color:var(--text-secondary); margin:4px 0 0; font-size:0.88rem;">
                Pencatatan harian Incoming Trafo, Outgoing Feeder Riser & AC, serta Power Factor Kapasitor.
            </p>
        </div>

        <div style="display:flex; gap:8px; flex-wrap:wrap; align-items:center;">
            <a href="{{ route('public-area.index') }}" class="btn btn-sm">
                ← Menu Public Area
            </a>
            <a href="{{ route('public-area.water.index') }}" class="btn btn-sm" style="background:rgba(56,189,248,0.12); color:#38bdf8; border:1px solid rgba(56,189,248,0.3);">
                💧 Water Meter
            </a>
            <a href="{{ route('public-area.mvsb.index') }}" class="btn btn-sm" style="background:rgba(168,85,247,0.12); color:#c084fc; border:1px solid rgba(168,85,247,0.3);">
                ⚡ MVSB 20kV
            </a>

            {{-- Tombol Khusus Super Admin: Input Back Date (Data Terlewat) --}}
            @if ($isSuperAdmin)
                <a href="{{ route('public-area.manual.lvsb') }}" class="btn btn-sm" style="background:rgba(234,179,8,0.18); color:#facc15; border:1px solid rgba(234,179,8,0.45); font-weight:700; display:inline-flex; align-items:center; gap:6px;" title="Input data back date secara manual (Khusus Super Admin)">
                    <span>📅 Input Back Date (Terlewat)</span>
                </a>
            @endif

            {{-- Export Buttons --}}
            @if ($canManage)
                <a href="{{ route('public-area.lvsb.excel', ['start_date' => $startDate->toDateString(), 'end_date' => $endDate->toDateString(), 'preset' => $preset]) }}" class="btn btn-excel btn-sm" title="Export Excel Rekapitulasi Data LVSB">
                    <svg viewBox="0 0 20 20" fill="currentColor" style="width:14px;height:14px;"><path d="M10.75 2.75a.75.75 0 0 0-1.5 0v8.614L6.295 8.235a.75.75 0 1 0-1.09 1.03l4.25 4.5a.75.75 0 0 0 1.09 0l4.25-4.5a.75.75 0 0 0-1.09-1.03l-2.955 3.129V2.75Z"/><path d="M3.5 12.75a.75.75 0 0 0-1.5 0v2.5A2.75 2.75 0 0 0 4.75 18h10.5A2.75 2.75 0 0 0 18 15.25v-2.5a.75.75 0 0 0-1.5 0v2.5c0 .69-.56 1.25-1.25 1.25H4.75c-.69 0-1.25-.56-1.25-1.25v-2.5Z"/></svg>Export to Excel
                </a>
                <a href="{{ route('public-area.lvsb.pdf', ['start_date' => $startDate->toDateString(), 'end_date' => $endDate->toDateString(), 'preset' => $preset]) }}" target="_blank" class="btn btn-pdf btn-sm" title="Export PDF Rekapitulasi Data (Grafik & Tabel)">
                    <svg viewBox="0 0 20 20" fill="currentColor" style="width:14px;height:14px;"><path fill-rule="evenodd" d="M5 2.75C5 1.784 5.784 1 6.75 1h6.5c.966 0 1.75.784 1.75 1.75v3.552c.377.046.752.097 1.126.153A2.212 2.212 0 0 1 18 8.653v4.097A2.25 2.25 0 0 1 15.75 15h-.241l.305 1.984A1.75 1.75 0 0 1 14.084 19H5.915a1.75 1.75 0 0 1-1.73-2.016L4.492 15H4.25A2.25 2.25 0 0 1 2 12.75V8.653c0-1.082.775-2.034 1.874-2.198.374-.056.75-.107 1.127-.153L5 6.25v-3.5Zm8.5 3.397a41.533 41.533 0 0 0-7 0V2.75a.25.25 0 0 1 .25-.25h6.5a.25.25 0 0 1 .25.25v3.397ZM6.608 12.5a.25.25 0 0 0-.247.212l-.693 4.5a.25.25 0 0 0 .247.288h8.17a.25.25 0 0 0 .246-.288l-.692-4.5a.25.25 0 0 0-.247-.212H6.608Z" clip-rule="evenodd"/></svg>Export to PDF
                </a>
            @endif
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- TOOLBAR FILTER INTERVAL DATA LVSB (MAKSIMAL 12 BULAN / 1 TAHUN)        --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    <div style="background:var(--bg-card); border:1px solid var(--border); border-radius:12px; padding:14px 18px; margin-bottom:24px; box-shadow:var(--shadow-sm);">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
            {{-- Quick Presets --}}
            <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                <span style="font-size:0.75rem; text-transform:uppercase; font-weight:700; color:var(--text-muted); letter-spacing:0.04em;">Preset Interval:</span>
                <a href="{{ route('public-area.lvsb.index') }}" class="btn btn-sm {{ empty($preset) && !request()->filled('start_date') ? 'btn-primary' : '' }}" style="{{ empty($preset) && !request()->filled('start_date') ? 'background:#d97706; color:#fff;' : 'background:rgba(255,255,255,0.05); color:var(--text-secondary);' }} font-size:0.78rem; padding:4px 10px;">
                    Bulan Ini
                </a>
                <a href="{{ route('public-area.lvsb.index', ['preset' => '3m']) }}" class="btn btn-sm" style="{{ $preset === '3m' ? 'background:#d97706; color:#fff;' : 'background:rgba(255,255,255,0.05); color:var(--text-secondary);' }} font-size:0.78rem; padding:4px 10px;">
                    3 Bulan
                </a>
                <a href="{{ route('public-area.lvsb.index', ['preset' => '6m']) }}" class="btn btn-sm" style="{{ $preset === '6m' ? 'background:#d97706; color:#fff;' : 'background:rgba(255,255,255,0.05); color:var(--text-secondary);' }} font-size:0.78rem; padding:4px 10px;">
                    6 Bulan
                </a>
                <a href="{{ route('public-area.lvsb.index', ['preset' => '12m']) }}" class="btn btn-sm" style="{{ in_array($preset, ['12m', '1y'], true) ? 'background:#d97706; color:#fff;' : 'background:rgba(255,255,255,0.05); color:var(--text-secondary);' }} font-size:0.78rem; padding:4px 10px;">
                    1 Tahun (Maks)
                </a>
            </div>

            {{-- Custom Date Range Form --}}
            <form method="GET" action="{{ route('public-area.lvsb.index') }}" style="display:flex; align-items:center; gap:8px; flex-wrap:wrap; margin:0;">
                <span style="font-size:0.75rem; color:var(--text-muted); font-weight:600;">Rentang:</span>
                <input type="date" name="start_date" value="{{ $startDate->format('Y-m-d') }}" max="{{ today()->toDateString() }}" class="input" style="padding:4px 8px; font-size:0.8rem; width:135px;" title="Tanggal Mulai">
                <span style="color:var(--text-muted); font-size:0.8rem;">s/d</span>
                <input type="date" name="end_date" value="{{ $endDate->format('Y-m-d') }}" max="{{ today()->toDateString() }}" class="input" style="padding:4px 8px; font-size:0.8rem; width:135px;" title="Tanggal Selesai">
                <button type="submit" class="btn btn-sm btn-primary" style="padding:5px 12px; font-size:0.8rem; background:#d97706; border-color:#d97706;">
                    Filter
                </button>
            </form>
        </div>

        {{-- Active Interval Info Pill --}}
        <div style="margin-top:10px; padding-top:8px; border-top:1px solid rgba(255,255,255,0.06); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px; font-size:0.78rem;">
            <div style="color:var(--text-secondary); display:flex; align-items:center; gap:6px;">
                <span style="display:inline-block; width:6px; height:6px; border-radius:50%; background:#fbbf24;"></span>
                <span>Interval Aktif: <strong style="color:var(--text-primary);">{{ $startDate->translatedFormat('d M Y') }} s/d {{ $endDate->translatedFormat('d M Y') }}</strong> ({{ $intervalDays }} hari)</span>
            </div>
            <span style="color:var(--text-muted); font-size:0.74rem;">* Ekspor Excel & PDF otomatis mengikuti interval aktif (maksimal 12 bulan / 1 tahun).</span>
        </div>
    </div>
    </div>

    {{-- Pill Mode Offline --}}
    @if(auth()->user()->role === 'teknisi')
        <div style="display:inline-flex; align-items:center; gap:8px; background:rgba(200,170,110,0.06); border:1px solid rgba(200,170,110,0.22); border-radius:20px; padding:4px 12px; margin-bottom:16px; font-size:0.78rem; color:var(--text-secondary);">
            <span style="display:inline-block; width:7px; height:7px; border-radius:50%; background:#10b981; box-shadow:0 0 6px #10b981;"></span>
            <span><strong style="color:var(--accent-light);">Mode Offline Aktif:</strong> Anda dapat menginput parameter panel LVSB di basement/ruang panel tanpa sinyal. Data otomatis tersinkron saat online.</span>
        </div>
    @endif

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- FORM INPUT PENCATATAN HARI INI                                          --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    <div style="background:var(--bg-card); border:1px solid var(--border); border-radius:12px; padding:20px; margin-bottom:28px; box-shadow:var(--shadow-sm);">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:16px; border-bottom:1px solid rgba(255,255,255,0.06); padding-bottom:12px;">
            <div>
                <span style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.06em; color:#fbbf24; font-weight:700;">Panel Distribusi Utama (LVSB)</span>
                <h3 style="margin:2px 0 0; font-size:1.15rem; color:var(--text-primary); font-weight:700;">
                    Pencatatan Hari Ini — {{ $todayStr }}
                </h3>
            </div>
            <div>
                @if ($todayReading)
                    <div style="display:inline-flex; align-items:center; gap:6px; background:rgba(16,185,129,0.15); color:#34d399; border:1px solid rgba(16,185,129,0.3); border-radius:20px; padding:4px 12px; font-size:0.78rem; font-weight:600;">
                        <span>✓ Sudah Dicatat Hari Ini</span>
                        <a href="{{ route('public-area.lvsb.show', $todayReading) }}" style="color:#38bdf8; text-decoration:underline; margin-left:6px;">Lihat Detail</a>
                    </div>
                @else
                    <span class="badge badge-wait" style="font-size:0.78rem; padding:4px 10px;">Belum Dicatat Hari Ini</span>
                @endif
            </div>
        </div>

        <form method="POST" action="{{ route('public-area.lvsb.store') }}" enctype="multipart/form-data" class="form-offline-pa-lvsb" data-offline-type="public_area_lvsb" data-meter-label="LVSB Public Area" data-target-redirect="{{ route('public-area.lvsb.index') }}">
            @csrf
            <input type="hidden" name="tanggal" value="{{ \Carbon\Carbon::today()->toDateString() }}">

            {{-- LOOP SEMUA 6 GRUP INPUT SESUAI SPESIFIKASI --}}
            <div style="display:flex; flex-direction:column; gap:20px;">
                @foreach ($structure as $gKey => $group)
                    <div style="background:rgba(20,28,45,0.7); border:1px solid {{ $group['color'] }}33; border-radius:10px; overflow:hidden;">
                        {{-- Header Grup --}}
                        <div style="background:rgba(0,0,0,0.35); padding:10px 16px; border-bottom:1px solid {{ $group['color'] }}25; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
                            <div style="display:flex; align-items:center; gap:10px;">
                                <span style="background:{{ $group['color'] }}20; color:{{ $group['color'] }}; border:1px solid {{ $group['color'] }}40; font-size:0.72rem; padding:2px 8px; border-radius:6px; font-weight:700; text-transform:uppercase;">
                                    {{ $group['badge'] }}
                                </span>
                                <span style="font-weight:700; color:var(--text-primary); font-size:0.95rem;">
                                    {{ $group['title'] }}
                                </span>
                            </div>
                        </div>

                        {{-- Isi Grup --}}
                        <div style="padding:14px 16px;">
                            @if ($group['has_subgroups'])
                                {{-- Grup memiliki sub-grup feeder (misal Outgoing 1.2, 2.1, dll) --}}
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
                                                        $val = old("readings.{$gKey}.{$sKey}.{$fKey}", $todayReading?->val($gKey, $sKey, $fKey));
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
                                {{-- Grup langsung berisi fields (Incoming Trafo atau Capacitor) --}}
                                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:12px;">
                                    @foreach ($group['fields'] as $fKey => $fMeta)
                                        @php
                                            $val = old("readings.{$gKey}.{$fKey}", $todayReading?->val($gKey, $fKey));
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
                                $groupFoto = $todayReading?->getFoto($gKey);
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
                <textarea name="catatan" rows="2" class="input" placeholder="Contoh: Suhu trafo normal, Breaker Riser 3 indikator hijau, Capacitor bank stabil..." style="width:100%; font-size:0.84rem; padding:8px 12px;">{{ old('catatan', $todayReading?->catatan) }}</textarea>
            </div>

            {{-- Tombol Simpan --}}
            <div style="margin-top:20px; display:flex; justify-content:flex-end;">
                <button type="submit" class="btn btn-primary" style="background:linear-gradient(135deg, #fbbf24, #d97706); border:none; color:#090d15; font-weight:700; padding:10px 24px; font-size:0.92rem; display:inline-flex; align-items:center; gap:8px;">
                    <svg viewBox="0 0 20 20" fill="currentColor" style="width:18px;height:18px;"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd"/></svg>
                    <span>Simpan Logsheet LVSB</span>
                </button>
            </div>
        </form>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- RANGKUMAN STATISTIK LVSB (TABEL & GRAFIK)                              --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    <div style="background:var(--bg-card); border:1px solid var(--border); border-radius:12px; padding:20px; margin-bottom:28px; box-shadow:var(--shadow-sm);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; flex-wrap:wrap; gap:10px;">
            <div>
                <span style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.06em; color:#fbbf24; font-weight:700;">Rangkuman & Visualisasi Parameter LVSB</span>
                <h3 style="font-size:1.15rem; color:var(--text-primary); margin:2px 0 0; font-weight:700;">
                    Rangkuman Panel LVSB — Interval {{ $startDate->translatedFormat('d M Y') }} s/d {{ $endDate->translatedFormat('d M Y') }}
                </h3>
            </div>
            <span class="badge" style="background:rgba(251,191,36,0.12); color:#fbbf24; border:1px solid rgba(251,191,36,0.3); font-size:0.76rem;">
                {{ $intervalDays }} Hari Terpilih
            </span>
        </div>

        {{-- Metric KPI Cards --}}
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:12px; margin-bottom:20px;">
            <div style="background:rgba(20,28,45,0.7); border:1px solid rgba(251,191,36,0.3); border-radius:10px; padding:12px 14px;">
                <span style="font-size:0.72rem; color:var(--text-muted); text-transform:uppercase; font-weight:700;">Avg Voltage Trafo</span>
                <div style="font-size:1.5rem; font-weight:700; color:#fbbf24; font-family:var(--font-serif); margin:4px 0;">
                    {{ $n($summary['avg_voltage'], 1) }} <small style="font-size:0.78rem; color:var(--text-muted); font-family:var(--font-sans);">Volt</small>
                </div>
                <span style="font-size:0.74rem; color:var(--text-secondary);">Tegangan 3-P Trafo</span>
            </div>

            <div style="background:rgba(20,28,45,0.7); border:1px solid rgba(239,68,68,0.3); border-radius:10px; padding:12px 14px;">
                <span style="font-size:0.72rem; color:var(--text-muted); text-transform:uppercase; font-weight:700;">Beban Puncak (Max kW)</span>
                <div style="font-size:1.5rem; font-weight:700; color:#f87171; font-family:var(--font-serif); margin:4px 0;">
                    {{ $n($summary['max_power'], 2) }} <small style="font-size:0.78rem; color:var(--text-muted); font-family:var(--font-sans);">kW</small>
                </div>
                <span style="font-size:0.74rem; color:var(--text-secondary);">Daya aktif tertinggi</span>
            </div>

            <div style="background:rgba(20,28,45,0.7); border:1px solid rgba(56,189,248,0.3); border-radius:10px; padding:12px 14px;">
                <span style="font-size:0.72rem; color:var(--text-muted); text-transform:uppercase; font-weight:700;">Rata-rata Daya (Avg kW)</span>
                <div style="font-size:1.5rem; font-weight:700; color:#38bdf8; font-family:var(--font-serif); margin:4px 0;">
                    {{ $n($summary['avg_power'], 2) }} <small style="font-size:0.78rem; color:var(--text-muted); font-family:var(--font-sans);">kW</small>
                </div>
                <span style="font-size:0.74rem; color:var(--text-secondary);">Konsumsi beban rata-rata</span>
            </div>

            <div style="background:rgba(20,28,45,0.7); border:1px solid rgba(168,85,247,0.3); border-radius:10px; padding:12px 14px;">
                <span style="font-size:0.72rem; color:var(--text-muted); text-transform:uppercase; font-weight:700;">Arus Puncak (Max A)</span>
                <div style="font-size:1.5rem; font-weight:700; color:#c084fc; font-family:var(--font-serif); margin:4px 0;">
                    {{ $n($summary['max_current'], 2) }} <small style="font-size:0.78rem; color:var(--text-muted); font-family:var(--font-sans);">Ampere</small>
                </div>
                <span style="font-size:0.74rem; color:var(--text-secondary);">Arus incoming trafo</span>
            </div>

            <div style="background:rgba(20,28,45,0.7); border:1px solid rgba(16,185,129,0.3); border-radius:10px; padding:12px 14px;">
                <span style="font-size:0.72rem; color:var(--text-muted); text-transform:uppercase; font-weight:700;">Avg PF Kapasitor</span>
                <div style="font-size:1.5rem; font-weight:700; color:#34d399; font-family:var(--font-serif); margin:4px 0;">
                    {{ $n($summary['avg_pf'], 2) }}
                </div>
                <span style="font-size:0.74rem; color:var(--text-secondary);">Kompensasi cos φ</span>
            </div>

            <div style="background:rgba(20,28,45,0.7); border:1px solid rgba(255,255,255,0.12); border-radius:10px; padding:12px 14px;">
                <span style="font-size:0.72rem; color:var(--text-muted); text-transform:uppercase; font-weight:700;">Total Hari Pencatatan</span>
                <div style="font-size:1.5rem; font-weight:700; color:var(--text-primary); font-family:var(--font-serif); margin:4px 0;">
                    {{ $summary['count'] }} <small style="font-size:0.78rem; color:var(--text-muted); font-family:var(--font-sans);">hari</small>
                </div>
                <span style="font-size:0.74rem; color:var(--text-secondary);">Dalam interval terpilih</span>
            </div>
        </div>

        {{-- TABEL RANGKUMAN PARAMETER LVSB --}}
        <div style="margin-bottom:20px;">
            <h4 style="font-size:0.92rem; color:var(--text-primary); margin:0 0 10px; font-weight:700; display:flex; align-items:center; gap:6px;">
                <span>📊</span> Tabel Rangkuman Statistik Panel LVSB
            </h4>
            <div class="table-wrap">
                <table class="log-table" style="font-size:0.84rem;">
                    <thead>
                        <tr>
                            <th class="l">Parameter Kunci</th>
                            <th class="c">Satuan</th>
                            <th class="r">Rata-rata (Avg)</th>
                            <th class="r">Nilai Tertinggi (Max)</th>
                            <th class="l">Keterangan / Posisi Panel</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="l" style="font-weight:700; color:#fbbf24;">Incoming Trafo Voltage 3-P</td>
                            <td class="c">Volt</td>
                            <td class="r" style="font-weight:700; font-family:var(--font-serif); color:#fbbf24;">{{ $n($summary['avg_voltage'], 1) }} V</td>
                            <td class="r">—</td>
                            <td class="l" style="color:var(--text-secondary);">Tegangan incoming trafo Apart A/B</td>
                        </tr>
                        <tr>
                            <td class="l" style="font-weight:700; color:#38bdf8;">Incoming Trafo Power (kW Total)</td>
                            <td class="c">kW</td>
                            <td class="r" style="font-weight:700; font-family:var(--font-serif); color:#38bdf8;">{{ $n($summary['avg_power'], 2) }} kW</td>
                            <td class="r" style="font-weight:700; color:#f87171;">{{ $n($summary['max_power'], 2) }} kW</td>
                            <td class="l" style="color:var(--text-secondary);">Beban aktif trafo gedung</td>
                        </tr>
                        <tr>
                            <td class="l" style="font-weight:700; color:#c084fc;">Incoming Trafo Current (Arus)</td>
                            <td class="c">Ampere</td>
                            <td class="r">—</td>
                            <td class="r" style="font-weight:700; font-family:var(--font-serif); color:#c084fc;">{{ $n($summary['max_current'], 2) }} A</td>
                            <td class="l" style="color:var(--text-secondary);">Arus incoming operasional</td>
                        </tr>
                        <tr>
                            <td class="l" style="font-weight:700; color:#34d399;">Incoming Capacitor Bank Power Factor</td>
                            <td class="c">Cos φ</td>
                            <td class="r" style="font-weight:700; font-family:var(--font-serif); color:#34d399;">{{ $n($summary['avg_pf'], 2) }}</td>
                            <td class="r">—</td>
                            <td class="l" style="color:var(--text-secondary);">Faktor daya kapasitor bank</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- GRAFIK VISUALISASI CHART.JS --}}
        <div>
            <h4 style="font-size:0.92rem; color:var(--text-primary); margin:0 0 10px; font-weight:700; display:flex; align-items:center; gap:6px;">
                <span>📈</span> Grafik Tren Beban Daya (kW), Tegangan (V), & PF Kapasitor
            </h4>
            <div style="position:relative; min-height:260px; height:260px; background:var(--bg-card); border:1px solid var(--border); border-radius:10px; padding:12px; box-shadow:var(--shadow-sm);">
                @if(empty($chartData['labels']))
                    <div style="height:100%; display:flex; flex-direction:column; align-items:center; justify-content:center; color:var(--text-muted); gap:8px;">
                        <span style="font-size:1.8rem;">📊</span>
                        <span style="font-size:0.85rem;">Belum ada data pencatatan LVSB pada interval ini.</span>
                    </div>
                @else
                    <canvas id="chartLvsbInterval"></canvas>
                @endif
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- TABEL RIWAYAT PENCATATAN INTERVAL LVSB                                 --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    <div style="margin-top:20px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; flex-wrap:wrap; gap:8px;">
            <div>
                <h3 style="font-size:1.1rem; font-weight:700; color:var(--text-primary); margin:0;">
                    Riwayat Pencatatan LVSB — Interval {{ $startDate->translatedFormat('d M Y') }} s/d {{ $endDate->translatedFormat('d M Y') }}
                </h3>
                <span style="font-size:0.78rem; color:var(--text-muted);">{{ $rows->count() }} hari logsheet tercatat</span>
            </div>
        </div>

        <div class="table-wrap">
            <table class="log-table">
                <thead>
                    <tr>
                        <th class="l" style="width:120px;">Tanggal</th>
                        <th class="r">Incoming Voltage (V)</th>
                        <th class="r">Incoming Current (A)</th>
                        <th class="r">Incoming Power (kW)</th>
                        <th class="r">Incoming Energy (GWh)</th>
                        <th class="r">Incoming PF</th>
                        <th class="r">Capacitor PF</th>
                        <th class="l">Petugas</th>
                        <th class="c">Status</th>
                        <th class="c" style="width:120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($rows as $r)
                    @php
                        $inVolt = $r->val('incoming_trafo', 'voltage_3p');
                        $inAmp  = $r->val('incoming_trafo', 'current');
                        $inKw   = $r->val('incoming_trafo', 'power');
                        $inGwh  = $r->val('incoming_trafo', 'energy');
                        $inPf   = $r->val('incoming_trafo', 'power_factor');
                        $capPf  = $r->val('incoming_capacitor', 'power_factor');
                    @endphp
                    <tr>
                        <td class="l" style="font-weight:600; white-space:nowrap;">
                            {{ $r->tanggal->format('d/m/Y') }}
                            <div style="font-size:0.7rem; color:var(--text-muted); font-weight:normal;">{{ $r->tanggal->translatedFormat('l') }}</div>
                        </td>
                        <td class="r" style="font-weight:600; color:#fbbf24;">{{ $n($inVolt, 1) }}</td>
                        <td class="r">{{ $n($inAmp) }}</td>
                        <td class="r" style="font-weight:600; color:#38bdf8;">{{ $n($inKw) }}</td>
                        <td class="r" style="color:#a78bfa;">{{ $n($inGwh, 4) }}</td>
                        <td class="r">{{ $n($inPf) }}</td>
                        <td class="r" style="font-weight:600; color:#34d399;">{{ $n($capPf) }}</td>
                        <td class="l">
                            <span style="font-size:0.8rem; color:var(--text-secondary);">{{ $r->user?->name ?? 'User Terhapus' }}</span>
                        </td>
                        <td class="c">
                            @if ($r->isVerified())
                                <span class="badge badge-ok" title="{{ $r->verified_at ? 'Diverifikasi oleh ' . ($r->verifier?->name ?? 'Admin') . ' pada ' . $r->verified_at->format('d/m/Y H:i') : 'Otomatis Terverifikasi (Input Admin / Backdate)' }}">✓ Terverifikasi</span>
                            @else
                                <span class="badge badge-wait">Menunggu</span>
                            @endif
                        </td>
                        <td class="c">
                            <div style="display:inline-flex; gap:6px; align-items:center;">
                                <a href="{{ route('public-area.lvsb.show', $r) }}" class="btn btn-sm" style="padding:4px 8px; font-size:0.75rem;" title="Lihat detail pembacaan">
                                    Detail
                                </a>
                                @if (auth()->user()->isSuperAdmin())
                                    <form method="POST" action="{{ route('public-area.lvsb.destroy', $r) }}" onsubmit="return confirm('Hapus data logsheet LVSB tanggal {{ $r->tanggal->format('d/m/Y') }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" style="padding:4px 8px; font-size:0.75rem;" title="Hapus logsheet">
                                            ✕
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" style="text-align:center; padding:30px; color:var(--text-muted);">
                            Belum ada catatan logsheet LVSB pada interval {{ $startDate->translatedFormat('d M Y') }} s/d {{ $endDate->translatedFormat('d M Y') }}.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    // Handler Upload Foto (Pilih dari Galeri & Buka Kamera)
    document.querySelectorAll('.photo-upload-group').forEach(group => {
        const mainInput   = group.querySelector('.input-file-main');
        const cameraInput = group.querySelector('.input-file-camera');
        const previewWrap = group.querySelector('.photo-preview-wrap');
        const imgPreview  = group.querySelector('.img-preview');
        const namePreview = group.querySelector('.photo-preview-name');
        const clearBtn    = group.querySelector('.btn-clear-photo');

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

    // Inisialisasi Grafik Visualisasi Chart.js Tren Interval LVSB
    document.addEventListener('DOMContentLoaded', function() {
        const chartData = @json($chartData);
        const ctx = document.getElementById('chartLvsbInterval');
        if (!ctx || !chartData || !chartData.labels || chartData.labels.length === 0) return;

        function getThemeColors() {
            const isLight = document.documentElement.getAttribute('data-theme') === 'light';
            return {
                textColor: isLight ? '#475569' : 'rgba(255,255,255,0.75)',
                tickColor: isLight ? '#64748b' : 'rgba(255,255,255,0.6)',
                gridColor: isLight ? 'rgba(0,0,0,0.06)' : 'rgba(255,255,255,0.05)',
            };
        }

        let th = getThemeColors();

        const chartLvsb = new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.labels,
                datasets: [
                    {
                        label: 'Incoming Power (kW)',
                        data: chartData.power,
                        borderColor: '#38bdf8',
                        backgroundColor: 'rgba(56,189,248,0.12)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.25,
                        yAxisID: 'y',
                        pointRadius: chartData.labels.length > 35 ? 0 : 3,
                    },
                    {
                        label: 'Incoming Voltage (V)',
                        data: chartData.voltage,
                        borderColor: '#fbbf24',
                        borderWidth: 2,
                        fill: false,
                        tension: 0.25,
                        yAxisID: 'yVolt',
                        pointRadius: chartData.labels.length > 35 ? 0 : 2,
                    },
                    {
                        label: 'Capacitor PF',
                        data: chartData.pf,
                        borderColor: '#34d399',
                        borderWidth: 2,
                        fill: false,
                        tension: 0.2,
                        yAxisID: 'yPF',
                        pointRadius: chartData.labels.length > 35 ? 0 : 2,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            color: th.textColor,
                            font: { size: 11 },
                            usePointStyle: true,
                            boxWidth: 8
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { color: th.gridColor },
                        ticks: { color: th.tickColor, font: { size: 10 } }
                    },
                    y: {
                        type: 'linear',
                        position: 'left',
                        title: { display: true, text: 'Power (kW)', color: '#38bdf8', font: { size: 10 } },
                        grid: { color: th.gridColor },
                        ticks: { color: th.tickColor, font: { size: 10 } }
                    },
                    yVolt: {
                        type: 'linear',
                        position: 'right',
                        title: { display: true, text: 'Voltage (V)', color: '#fbbf24', font: { size: 10 } },
                        grid: { drawOnChartArea: false },
                        ticks: { color: th.tickColor, font: { size: 10 } }
                    },
                    yPF: {
                        type: 'linear',
                        position: 'right',
                        min: 0.7,
                        max: 1.0,
                        title: { display: true, text: 'PF', color: '#34d399', font: { size: 10 } },
                        grid: { drawOnChartArea: false },
                        ticks: { color: th.tickColor, font: { size: 10 } }
                    }
                }
            }
        });

        window.addEventListener('kpr-theme-changed', function() {
            const next = getThemeColors();
            chartLvsb.options.plugins.legend.labels.color = next.textColor;
            chartLvsb.options.scales.x.grid.color = next.gridColor;
            chartLvsb.options.scales.x.ticks.color = next.tickColor;
            chartLvsb.options.scales.y.grid.color = next.gridColor;
            chartLvsb.options.scales.y.ticks.color = next.tickColor;
            chartLvsb.options.scales.yVolt.ticks.color = next.tickColor;
            chartLvsb.options.scales.yPF.ticks.color = next.tickColor;
            chartLvsb.update();
        });
    });
</script>
@endpush
