@extends('layouts.panel')

@php
    $namaBulan = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ][$month] ?? (string)$month;
@endphp

@section('title', 'Bukti Foto Meteran Tenant - Billing System')
@section('page-title', 'Bukti Foto Meteran Tenant')
@section('page-subtitle', 'Monitoring & Backup Fisik Foto kWh Listrik dan Water Meter Periode ' . $namaBulan . ' ' . $year)

@section('panel')
<div class="panel">
    <!-- Header -->
    <div class="panel-head">
        <div>
            <h2>Galeri &amp; Backup Bukti Foto Meteran</h2>
            <p>Arsip foto fisik meteran listrik (termasuk EV Charger) dan water meter per tenant untuk verifikasi &amp; bukti tagihan resmi.</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('billing.photos.zip', ['year' => $year, 'month' => $month, 'lantai' => $lantai]) }}" class="btn btn-primary" style="font-weight:700;">
                📦 Unduh Backup Semua Foto (.ZIP)
            </a>
            <a href="{{ route('billing.photos.print', ['year' => $year, 'month' => $month, 'lantai' => $lantai]) }}" target="_blank" class="btn btn-sm" style="color:#c084fc; border-color:rgba(192,132,252,0.4); font-weight:600;">
                🖨️ Cetak Lembar Bukti / PDF
            </a>
            <a href="{{ route('billing.utilities.hub', ['year' => $year, 'month' => $month]) }}" class="btn btn-sm">
                &larr; Hub Utilities
            </a>
        </div>
    </div>

    <!-- KPI Summary Cards -->
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(210px, 1fr)); gap:16px; margin:20px 0;">
        <div style="background:var(--bg-card); border:1px solid rgba(234,179,8,0.35); border-radius:var(--radius-md); padding:16px;">
            <span style="font-size:0.75rem; font-weight:700; color:var(--text-muted); text-transform:uppercase;">Foto kWh Listrik Terkumpul</span>
            <div style="font-size:1.6rem; font-weight:800; color:#facc15; font-family:monospace; margin-top:4px;">
                {{ $totalPhotoEnergy }} <span style="font-size:0.9rem; font-weight:normal; color:var(--text-secondary);">/ {{ $totalTenants }}</span>
            </div>
            <div style="font-size:0.75rem; color:var(--text-secondary); margin-top:4px;">
                Meter Reguler &amp; EV Charger
            </div>
        </div>

        <div style="background:var(--bg-card); border:1px solid rgba(56,189,248,0.35); border-radius:var(--radius-md); padding:16px;">
            <span style="font-size:0.75rem; font-weight:700; color:var(--text-muted); text-transform:uppercase;">Foto Water Meter Terkumpul</span>
            <div style="font-size:1.6rem; font-weight:800; color:#38bdf8; font-family:monospace; margin-top:4px;">
                {{ $totalPhotoWater }} <span style="font-size:0.9rem; font-weight:normal; color:var(--text-secondary);">/ {{ $totalTenants }}</span>
            </div>
            <div style="font-size:0.75rem; color:var(--text-secondary); margin-top:4px;">
                Water Meter m³
            </div>
        </div>

        <div style="background:var(--bg-card); border:1px solid var(--border-color); border-radius:var(--radius-md); padding:16px;">
            <span style="font-size:0.75rem; font-weight:700; color:var(--text-muted); text-transform:uppercase;">Format Backup File ZIP</span>
            <div style="font-size:1.1rem; font-weight:700; color:var(--text-primary); margin-top:8px;">
                Folder Otomatis &amp; Manifest
            </div>
            <div style="font-size:0.75rem; color:var(--text-secondary); margin-top:4px;">
                Tersusun per kategori &amp; rekap CSV di dalam ZIP
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar" style="margin-bottom:20px; background:var(--bg-card); padding:14px 18px; border-radius:var(--radius-md); border:1px solid var(--border-color); display:flex; flex-wrap:wrap; justify-content:space-between; align-items:center; gap:12px;">
        <form method="GET" action="{{ route('billing.photos') }}" style="display:flex; flex-wrap:wrap; align-items:center; gap:10px; flex:1;">
            <span style="font-size:0.82rem; font-weight:600; color:var(--text-muted); text-transform:uppercase;">Periode:</span>
            <select name="month" class="form-control" style="width:auto; padding:6px 12px; font-size:0.875rem;" onchange="this.form.submit()">
                @for($m = 1; $m <= 12; $m++)
                    @php $bNames = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember']; @endphp
                    <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>{{ $bNames[$m] }}</option>
                @endfor
            </select>
            <select name="year" class="form-control" style="width:auto; padding:6px 12px; font-size:0.875rem;" onchange="this.form.submit()">
                @for($y = now()->year - 2; $y <= now()->year + 1; $y++)
                    <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>

            <select name="lantai" class="form-control" style="width:auto; padding:6px 12px; font-size:0.875rem;" onchange="this.form.submit()">
                <option value="all">Semua Lantai</option>
                @foreach($allLantai as $l)
                    <option value="{{ $l }}" {{ $lantai == $l ? 'selected' : '' }}>Lantai {{ $l }}</option>
                @endforeach
            </select>

            <select name="photo_status" class="form-control" style="width:auto; padding:6px 12px; font-size:0.875rem;" onchange="this.form.submit()">
                <option value="all" {{ $filterPhoto == 'all' ? 'selected' : '' }}>Semua Tenant</option>
                <option value="has_photo" {{ $filterPhoto == 'has_photo' ? 'selected' : '' }}>Memiliki Foto</option>
                <option value="missing_photo" {{ $filterPhoto == 'missing_photo' ? 'selected' : '' }}>Belum Lengkap Foto</option>
            </select>

            <input type="text" name="search" class="form-control" placeholder="Cari nama tenant / unit..." value="{{ $search }}" style="min-width:180px; padding:6px 12px; font-size:0.875rem;">
            <button type="submit" class="btn btn-sm btn-primary">Filter</button>
        </form>

        <a href="{{ route('billing.photos') }}" class="btn btn-sm">Reset</a>
    </div>

    <!-- Grid Kartu Foto per Tenant -->
    <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(320px, 1fr)); gap:18px;">
        @forelse($galleryList as $item)
            @php
                $t = $item->tenant;
                $e = $item->energy;
                $ev = $item->ev;
                $w = $item->water;
            @endphp
            <div style="background:var(--bg-card); border:1px solid var(--border-color); border-radius:var(--radius-md); padding:16px; display:flex; flex-direction:column; justify-content:space-between; box-shadow:0 2px 8px rgba(0,0,0,0.05);">
                <div>
                    <!-- Header Kartu Unit -->
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:12px; border-bottom:1px solid var(--border-color); padding-bottom:8px;">
                        <div>
                            <h3 style="font-size:0.98rem; font-weight:800; margin:0; color:var(--text-primary);">
                                {{ $t->nama_tenant }}
                            </h3>
                            <span style="font-size:0.75rem; color:var(--accent); font-weight:600;">
                                📍 {{ $t->unit_label }}
                            </span>
                        </div>
                        <a href="{{ route('billing.photos.print', ['year' => $year, 'month' => $month, 'tenant_id' => $t->id]) }}" target="_blank" class="btn btn-sm" style="padding:3px 8px; font-size:0.72rem;" title="Cetak Lembar Bukti Khusus Unit Ini">
                            🖨️ Bukti Unit
                        </a>
                    </div>

                    <!-- Grid 2 atau 3 Foto Meteran -->
                    <div style="display:grid; grid-template-columns:{{ $t->has_ev_charger ? '1fr 1fr 1fr' : '1fr 1fr' }}; gap:10px; margin-bottom:12px;">
                        <!-- 1. Foto Listrik Reguler -->
                        <div style="background:var(--bg-main); border:1px solid rgba(234,179,8,0.25); border-radius:var(--radius-sm); padding:8px; text-align:center;">
                            <div style="font-size:0.72rem; font-weight:700; color:#facc15; margin-bottom:6px;">⚡ Listrik</div>
                            @if($e && $e->foto_url)
                                <a href="#" data-lightbox="{{ $e->foto_url }}" data-caption="Foto Listrik: {{ $t->nama_tenant }} (Stand Akhir: {{ $e->stand_akhir }} kWh)" style="display:block;">
                                    <img src="{{ $e->foto_url }}" alt="Foto Listrik" style="width:100%; height:90px; object-fit:cover; border-radius:4px; border:1px solid var(--border-color);">
                                </a>
                                <div style="font-size:0.7rem; font-family:monospace; margin-top:4px; font-weight:700; color:var(--text-primary);">
                                    {{ number_format($e->stand_akhir, 2, ',', '.') }} kWh
                                </div>
                                <div style="font-size:0.65rem; color:var(--text-muted);">
                                    Pakai: <strong style="color:#facc15;">{{ number_format($e->pemakaian, 2, ',', '.') }}</strong>
                                </div>
                                @if($e->is_special_case)
                                    <span style="font-size:0.62rem; background:rgba(239,68,68,0.2); color:#f87171; border-radius:3px; padding:1px 4px; display:inline-block; margin-top:2px;" title="{{ $e->keterangan }}">⚠️ Kendala</span>
                                @endif
                            @else
                                <div style="height:90px; display:flex; align-items:center; justify-content:center; color:var(--text-muted); font-size:0.72rem; border:1px dashed var(--border-color); border-radius:4px;">
                                    Belum Ada Foto
                                </div>
                                @if($e)
                                    <div style="font-size:0.7rem; font-family:monospace; margin-top:4px; color:var(--text-secondary);">
                                        {{ number_format($e->stand_akhir, 2, ',', '.') }} kWh
                                    </div>
                                    <div style="font-size:0.65rem; color:var(--text-muted);">
                                        Pakai: <strong>{{ number_format($e->pemakaian, 2, ',', '.') }}</strong>
                                    </div>
                                @else
                                    <div style="font-size:0.7rem; color:var(--text-muted); margin-top:4px;">Belum Dicatat</div>
                                @endif
                            @endif
                        </div>

                        <!-- 2. Foto EV Charger jika ada -->
                        @if($t->has_ev_charger)
                            <div style="background:var(--bg-main); border:1px solid rgba(56,189,248,0.25); border-radius:var(--radius-sm); padding:8px; text-align:center;">
                                <div style="font-size:0.72rem; font-weight:700; color:#38bdf8; margin-bottom:6px;">🚗⚡ EV</div>
                                @if($ev && $ev->foto_url)
                                    <a href="#" data-lightbox="{{ $ev->foto_url }}" data-caption="Foto EV Charger: {{ $t->nama_tenant }} (Stand Akhir: {{ $ev->stand_akhir }} kWh)" style="display:block;">
                                        <img src="{{ $ev->foto_url }}" alt="Foto EV" style="width:100%; height:90px; object-fit:cover; border-radius:4px; border:1px solid var(--border-color);">
                                    </a>
                                    <div style="font-size:0.7rem; font-family:monospace; margin-top:4px; font-weight:700; color:var(--text-primary);">
                                        {{ number_format($ev->stand_akhir, 2, ',', '.') }} kWh
                                    </div>
                                    <div style="font-size:0.65rem; color:var(--text-muted);">
                                        Pakai: <strong style="color:#38bdf8;">{{ number_format($ev->pemakaian, 2, ',', '.') }}</strong>
                                    </div>
                                    @if($ev->is_special_case)
                                        <span style="font-size:0.62rem; background:rgba(239,68,68,0.2); color:#f87171; border-radius:3px; padding:1px 4px; display:inline-block; margin-top:2px;" title="{{ $ev->keterangan }}">⚠️ Kendala</span>
                                    @endif
                                @else
                                    <div style="height:90px; display:flex; align-items:center; justify-content:center; color:var(--text-muted); font-size:0.72rem; border:1px dashed var(--border-color); border-radius:4px;">
                                        Belum Ada Foto
                                    </div>
                                    @if($ev)
                                        <div style="font-size:0.7rem; font-family:monospace; margin-top:4px; color:var(--text-secondary);">
                                            {{ number_format($ev->stand_akhir, 2, ',', '.') }} kWh
                                        </div>
                                        <div style="font-size:0.65rem; color:var(--text-muted);">
                                            Pakai: <strong>{{ number_format($ev->pemakaian, 2, ',', '.') }}</strong>
                                        </div>
                                    @else
                                        <div style="font-size:0.7rem; color:var(--text-muted); margin-top:4px;">Belum Dicatat</div>
                                    @endif
                                @endif
                            </div>
                        @endif

                        <!-- 3. Foto Water Meter -->
                        <div style="background:var(--bg-main); border:1px solid rgba(6,182,212,0.25); border-radius:var(--radius-sm); padding:8px; text-align:center;">
                            <div style="font-size:0.72rem; font-weight:700; color:#06b6d4; margin-bottom:6px;">💧 Air</div>
                            @if($w && $w->foto_url)
                                <a href="#" data-lightbox="{{ $w->foto_url }}" data-caption="Foto Water Meter: {{ $t->nama_tenant }} (Stand Akhir: {{ $w->stand_akhir }} m³)" style="display:block;">
                                    <img src="{{ $w->foto_url }}" alt="Foto Air" style="width:100%; height:90px; object-fit:cover; border-radius:4px; border:1px solid var(--border-color);">
                                </a>
                                <div style="font-size:0.7rem; font-family:monospace; margin-top:4px; font-weight:700; color:var(--text-primary);">
                                    {{ number_format($w->stand_akhir, 3, ',', '.') }} m³
                                </div>
                                <div style="font-size:0.65rem; color:var(--text-muted);">
                                    Pakai: <strong style="color:#06b6d4;">{{ number_format($w->pemakaian, 3, ',', '.') }}</strong>
                                </div>
                                @if($w->is_special_case)
                                    <span style="font-size:0.62rem; background:rgba(239,68,68,0.2); color:#f87171; border-radius:3px; padding:1px 4px; display:inline-block; margin-top:2px;" title="{{ $w->keterangan }}">⚠️ Kendala</span>
                                @endif
                            @else
                                <div style="height:90px; display:flex; align-items:center; justify-content:center; color:var(--text-muted); font-size:0.72rem; border:1px dashed var(--border-color); border-radius:4px;">
                                    Belum Ada Foto
                                </div>
                                @if($w)
                                    <div style="font-size:0.7rem; font-family:monospace; margin-top:4px; color:var(--text-secondary);">
                                        {{ number_format($w->stand_akhir, 3, ',', '.') }} m³
                                    </div>
                                    <div style="font-size:0.65rem; color:var(--text-muted);">
                                        Pakai: <strong>{{ number_format($w->pemakaian, 3, ',', '.') }}</strong>
                                    </div>
                                @else
                                    <div style="font-size:0.7rem; color:var(--text-muted); margin-top:4px;">Belum Dicatat</div>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Footer Metadata -->
                <div style="font-size:0.73rem; color:var(--text-muted); border-top:1px dashed var(--border-color); padding-top:8px; display:flex; justify-content:space-between; align-items:center;">
                    <span>Petugas: <strong style="color:var(--text-secondary);">{{ $e?->user?->name ?? $w?->user?->name ?? '—' }}</strong></span>
                    <span>{{ $e?->tanggal_catat?->format('d/m/Y') ?? $w?->tanggal_catat?->format('d/m/Y') ?? '—' }}</span>
                </div>
            </div>
        @empty
            <div style="grid-column:1 / -1; padding:40px; text-align:center; color:var(--text-muted); background:var(--bg-card); border-radius:var(--radius-md);">
                Tidak ada data bukti foto yang sesuai dengan filter pencarian.
            </div>
        @endforelse
    </div>
</div>
@endsection
