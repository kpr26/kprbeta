@extends('layouts.panel')

@php
    $n = fn ($v, $d = 2) => ($v !== null && $v !== '') ? number_format((float)$v, $d, ',', '.') : '—';
    $canManage = auth()->user()->isSuperAdmin() || auth()->user()->isSupervisor() || auth()->user()->isAdmin();
    $todayStr = \Carbon\Carbon::today()->translatedFormat('l, d F Y');
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

        <div style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
            <a href="{{ route('public-area.index') }}" class="btn btn-sm">
                ← Menu Public Area
            </a>
            <a href="{{ route('public-area.water.index') }}" class="btn btn-sm" style="background:rgba(56,189,248,0.12); color:#38bdf8; border:1px solid rgba(56,189,248,0.3);">
                💧 Pindah ke Water Meter
            </a>

            {{-- Filter Bulan --}}
            <form method="GET" action="{{ route('public-area.lvsb.index') }}" style="display:flex; align-items:center; gap:6px;">
                <input type="month" name="period" value="{{ sprintf('%04d-%02d', $year, $month) }}" class="input" style="padding:6px 10px; font-size:0.84rem;" onchange="this.form.submit()">
            </form>
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
    <div style="background:rgba(15,22,35,0.7); border:1px solid rgba(251,191,36,0.3); border-radius:12px; padding:20px; margin-bottom:28px;">
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
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Lampiran Foto & Catatan --}}
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:16px; margin-top:20px; padding-top:16px; border-top:1px solid rgba(255,255,255,0.08);">
                {{-- Foto Fisik Panel --}}
                <div class="photo-upload-group">
                    <label style="font-size:0.8rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; display:block; margin-bottom:6px;">
                        Foto Fisik Panel LVSB <span style="font-size:0.72rem; color:var(--text-muted); font-weight:normal;">(Opsional)</span>
                    </label>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px;">
                        {{-- Opsi 1: Dari Galeri --}}
                        <label class="btn-pick-gallery" style="display:flex; align-items:center; justify-content:center; gap:6px; background:rgba(251,191,36,0.08); border:1px solid rgba(251,191,36,0.3); padding:8px 10px; border-radius:6px; cursor:pointer; font-size:0.8rem; color:var(--text-secondary); text-align:center;">
                            <svg viewBox="0 0 20 20" fill="currentColor" style="width:16px;height:16px;color:#38bdf8;flex-shrink:0;"><path fill-rule="evenodd" d="M1 5.25A2.25 2.25 0 0 1 3.25 3h13.5A2.25 2.25 0 0 1 19 5.25v9.5A2.25 2.25 0 0 1 16.75 17H3.25A2.25 2.25 0 0 1 1 14.75v-9.5Zm1.5 5.81v3.69c0 .414.336.75.75.75h13.5a.75.75 0 0 0 .75-.75v-2.69l-2.22-2.219a.75.75 0 0 0-1.06 0l-1.91 1.909-4.22-4.219a.75.75 0 0 0-1.06 0L2.5 11.06Zm10.25-3.81a1.25 1.25 0 1 1-2.5 0 1.25 1.25 0 0 1 2.5 0Z" clip-rule="evenodd"/></svg>
                            <span>🖼️ Dari Galeri</span>
                            <input type="file" name="foto" accept="image/*" class="input-file-main" style="display:none;">
                        </label>

                        {{-- Opsi 2: Buka Kamera --}}
                        <label class="btn-pick-camera" style="display:flex; align-items:center; justify-content:center; gap:6px; background:rgba(251,191,36,0.08); border:1px solid rgba(251,191,36,0.3); padding:8px 10px; border-radius:6px; cursor:pointer; font-size:0.8rem; color:var(--text-secondary); text-align:center;">
                            <svg viewBox="0 0 20 20" fill="currentColor" style="width:16px;height:16px;color:#fbbf24;flex-shrink:0;"><path fill-rule="evenodd" d="M1 8a2 2 0 0 1 2-2h.93a2 2 0 0 0 1.664-.89l.812-1.22A2 2 0 0 1 8.07 3h3.86a2 2 0 0 1 1.664.89l.812 1.22A2 2 0 0 0 16.07 6H17a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8Zm13.5 3a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM10 14a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" clip-rule="evenodd"/></svg>
                            <span>📷 Buka Kamera</span>
                            <input type="file" accept="image/*" capture="environment" class="input-file-camera" style="display:none;">
                        </label>
                    </div>

                    {{-- Pratinjau Foto --}}
                    <div class="photo-preview-wrap" style="{{ $todayReading?->foto ? 'display:flex;' : 'display:none;' }} margin-top:8px; align-items:center; gap:10px; background:rgba(0,0,0,0.35); padding:8px 12px; border-radius:8px; border:1px solid rgba(251,191,36,0.25);">
                        <img class="img-preview" src="{{ $todayReading?->foto ? asset('storage/'.$todayReading->foto) : '' }}" alt="Preview" style="width:48px; height:48px; object-fit:cover; border-radius:6px; border:1px solid rgba(251,191,36,0.4);">
                        <div style="flex:1; overflow:hidden;">
                            <div class="photo-preview-name" style="font-size:0.78rem; color:var(--text-primary); font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                {{ $todayReading?->foto ? 'Foto tersimpan sebelumnya' : '' }}
                            </div>
                            <div class="photo-preview-status" style="font-size:0.72rem; color:#34d399;">✓ Foto siap dilampirkan</div>
                        </div>
                        <button type="button" class="btn-clear-photo" style="background:none; border:none; color:#ef4444; font-size:1.1rem; cursor:pointer;" title="Batalkan foto">✕</button>
                    </div>
                </div>

                {{-- Catatan --}}
                <div>
                    <label style="font-size:0.8rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; display:block; margin-bottom:6px;">
                        Catatan Kondisi Panel (Opsional)
                    </label>
                    <textarea name="catatan" rows="3" class="input" placeholder="Contoh: Suhu trafo normal, Breaker Riser 3 indikator hijau..." style="width:100%; font-size:0.84rem; padding:8px 12px;">{{ old('catatan', $todayReading?->catatan) }}</textarea>
                </div>
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
    {{-- TABEL RIWAYAT PENCATATAN BULANAN LVSB                                  --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    <div style="margin-top:20px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; flex-wrap:wrap; gap:8px;">
            <div>
                <h3 style="font-size:1.1rem; font-weight:700; color:var(--text-primary); margin:0;">
                    Riwayat Pencatatan LVSB Periode {{ \Carbon\Carbon::create($year, $month)->translatedFormat('F Y') }}
                </h3>
                <span style="font-size:0.78rem; color:var(--text-muted);">{{ $rows->count() }} hari pencatatan tercatat</span>
            </div>

            @if($canManage && $rows->count())
                <div style="display:flex; gap:8px;">
                    <a href="{{ route('public-area.lvsb.excel', ['year' => $year, 'month' => $month]) }}" class="btn btn-sm" style="background:#10b981; color:#fff; border:none;">
                        ⬇ Unduh Excel
                    </a>
                    <a href="{{ route('public-area.lvsb.pdf', ['year' => $year, 'month' => $month]) }}" target="_blank" class="btn btn-sm" style="background:#6366f1; color:#fff; border:none;">
                        🖨 Cetak PDF
                    </a>
                </div>
            @endif
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
                            @if ($r->verified_at)
                                <span class="badge badge-ok" title="Diverifikasi oleh {{ $r->verifier?->name ?? 'Admin' }} pada {{ $r->verified_at->format('d/m/Y H:i') }}">✓ Terverifikasi</span>
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
                            Belum ada catatan logsheet LVSB pada bulan {{ \Carbon\Carbon::create($year, $month)->translatedFormat('F Y') }}.
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
</script>
@endpush
