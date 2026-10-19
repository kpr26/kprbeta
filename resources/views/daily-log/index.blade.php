@extends('layouts.panel')

@php
    $authUser = auth()->user();
    $canCreate = in_array($authUser->role, ['teknisi', 'admin', 'eng_admin', 'eng_spv'], true);
@endphp

@section('title', 'Daily Log Book')
@section('page-title', 'Daily Log Book')
@section('page-subtitle', 'Rekap Laporan Pekerjaan Harian Teknisi (Terbuka untuk Semua Role)')

@push('styles')
<style>
.daily-log-card-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
    gap: 20px;
    margin-top: 20px;
}
@media (max-width: 480px) {
    .daily-log-card-grid {
        grid-template-columns: 1fr;
    }
}
.job-card {
    background: rgba(15, 22, 35, 0.85);
    border: 1px solid rgba(200, 170, 110, 0.15);
    border-radius: var(--radius-lg);
    padding: 20px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    gap: 16px;
    transition: all var(--transition);
    position: relative;
    overflow: hidden;
    backdrop-filter: blur(14px);
    box-shadow: 0 4px 16px rgba(0,0,0,0.3);
}
.job-card:hover {
    transform: translateY(-3px);
    border-color: rgba(200, 170, 110, 0.4);
    box-shadow: 0 12px 32px -8px rgba(0, 0, 0, 0.6), 0 0 20px rgba(200, 170, 110, 0.12);
}
.job-card-indicator-done {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #10b981, #059669);
}
.job-card-indicator-continue {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #d4a359, #f3dfa2);
}

.job-card-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
}
.job-card-date {
    font-size: 1.05rem;
    font-weight: 600;
    color: var(--accent-light);
    font-family: var(--font-serif, 'Cormorant Garamond', Georgia, serif);
}
.job-card-time {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 0.8rem;
    color: var(--text-secondary);
    margin-top: 4px;
    font-weight: 500;
}

.badge-status {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    flex-shrink: 0;
}
.badge-status-done {
    background: rgba(16, 185, 129, 0.15);
    color: #34d399;
    border: 1px solid rgba(16, 185, 129, 0.35);
}
.badge-status-continue {
    background: rgba(200, 170, 110, 0.15);
    color: var(--accent-light);
    border: 1px solid rgba(200, 170, 110, 0.35);
}

.job-meta-row {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.84rem;
    color: var(--text-primary);
}
.job-location-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    color: var(--accent-light);
    font-weight: 600;
    font-size: 0.86rem;
}

.job-desc-box {
    background: rgba(9, 13, 21, 0.65);
    border: 1px solid rgba(200, 170, 110, 0.12);
    border-radius: var(--radius-sm);
    padding: 10px 14px;
    font-size: 0.88rem;
    color: #f1f5f9;
    line-height: 1.55;
    max-height: 78px;
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    font-weight: 500;
}

/* ── Filter Form Box & High-Contrast Inputs / Dropdown ── */
.filter-form-box {
    background: rgba(15, 22, 35, 0.85);
    border: 1px solid rgba(200, 170, 110, 0.15);
    border-radius: var(--radius-md);
    padding: 18px;
    margin-bottom: 22px;
}
.filter-form-box label {
    font-size: 0.8rem !important;
    color: var(--text-secondary) !important;
    font-weight: 600 !important;
    display: block;
    margin-bottom: 6px;
}
.filter-form-box select.input,
select.input {
    background-color: #0c1220 !important;
    color: var(--accent-light) !important;
    color-scheme: dark !important;
    border: 1px solid rgba(200, 170, 110, 0.2) !important;
    cursor: pointer;
}
.filter-form-box select.input option,
select.input option {
    background-color: #0c1220 !important;
    color: #f8fafc !important;
    padding: 10px 14px !important;
    font-size: 0.9rem !important;
}
.filter-form-box select.input option:checked,
select.input option:checked {
    background-color: var(--accent-dark, #a08845) !important;
    color: #ffffff !important;
    font-weight: 700 !important;
}
.filter-form-box select.input option:hover,
select.input option:hover {
    background-color: #1e293b !important;
    color: #ffffff !important;
}
.filter-form-box input.input {
    background-color: #0c1220 !important;
    color: #f8fafc !important;
    border: 1px solid rgba(255, 255, 255, 0.2) !important;
}
.filter-form-box input.input::placeholder {
    color: #94a3b8 !important;
    opacity: 1;
}

/* 3 Photos Row */
.job-photos-preview {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 8px;
}
.job-photo-thumb {
    position: relative;
    aspect-ratio: 4 / 3;
    border-radius: 8px;
    overflow: hidden;
    border: 1px solid rgba(255, 255, 255, 0.1);
    background: #000;
    cursor: pointer;
    transition: transform 0.2s ease, border-color 0.2s ease;
}
.job-photo-thumb:hover {
    transform: scale(1.03);
    border-color: var(--accent-light);
}
.job-photo-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
.job-photo-stage-label {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: rgba(10, 14, 26, 0.85);
    color: #fff;
    font-size: 0.65rem;
    font-weight: 700;
    text-align: center;
    padding: 2px 4px;
    text-transform: uppercase;
}

.job-card-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 12px;
    border-top: 1px solid rgba(255, 255, 255, 0.06);
    gap: 8px;
}
.job-author-info {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.82rem;
    color: #f1f5f9;
    font-weight: 600;
}
.job-author-avatar {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--accent), #a855f7);
    color: #fff;
    font-size: 0.7rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}
</style>
@endpush

@section('panel')
{{-- STATS SUMMARY --}}
<div class="meter-grid">
    <div class="meter-card" style="--c:#38bdf8;">
        <div class="mc-label">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M10 2a.75.75 0 0 1 .75.75v5.59l4.22-4.22a.75.75 0 1 1 1.06 1.06l-4.22 4.22h5.59a.75.75 0 0 1 0 1.5h-5.59l4.22 4.22a.75.75 0 1 1-1.06 1.06l-4.22-4.22v5.59a.75.75 0 0 1-1.5 0v-5.59l-4.22 4.22a.75.75 0 0 1-1.06-1.06l4.22-4.22H2.75a.75.75 0 0 1 0-1.5h5.59L4.12 5.18a.75.75 0 0 1 1.06-1.06l4.22 4.22V2.75A.75.75 0 0 1 10 2Z"/></svg>
            Pekerjaan Hari Ini
        </div>
        <div class="mc-value">{{ $summary['today_count'] }}<small>catatan</small></div>
        <div class="mc-sub">{{ now()->translatedFormat('l, d F Y') }}</div>
    </div>

    <div class="meter-card" style="--c:#818cf8;">
        <div class="mc-label">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M6 3.75A2.75 2.75 0 0 1 8.75 1h2.5A2.75 2.75 0 0 1 14 3.75v.443c.572.055 1.14.122 1.706.2 1.298.18 2.294 1.29 2.294 2.607v10.5A2.5 2.5 0 0 1 15.5 20h-11A2.5 2.5 0 0 1 2 17.5V7c0-1.317.996-2.427 2.294-2.607.566-.078 1.134-.145 1.706-.2V3.75ZM8.75 2.5c-.69 0-1.25.56-1.25 1.25v.25h5V3.75c0-.69-.56-1.25-1.25-1.25h-2.5ZM14.28 9.03a.75.75 0 0 0-1.06-1.06l-4.5 4.5-1.97-1.97a.75.75 0 1 0-1.06 1.06l2.5 2.5a.75.75 0 0 0 1.06 0l5.03-5.03Z" clip-rule="evenodd"/></svg>
            Total Bulan Ini
        </div>
        <div class="mc-value">{{ $summary['total_pekerjaan'] }}<small>pekerjaan</small></div>
        <div class="mc-sub">Periode {{ $filters['bulan'] ? \Carbon\Carbon::parse($filters['bulan'].'-01')->translatedFormat('F Y') : now()->translatedFormat('F Y') }}</div>
    </div>

    <div class="meter-card" style="--c:#10b981;">
        <div class="mc-label">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd"/></svg>
            Status Done
        </div>
        <div class="mc-value" style="color:#34d399;">{{ $summary['done'] }}<small>selesai</small></div>
        <div class="mc-sub">Pekerjaan terselesaikan tuntas</div>
    </div>

    <div class="meter-card" style="--c:#f59e0b;">
        <div class="mc-label">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm.75-13a.75.75 0 0 0-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 0 0 0-1.5h-3.25V5Z" clip-rule="evenodd"/></svg>
            Status Continue
        </div>
        <div class="mc-value" style="color:#fbbf24;">{{ $summary['continue'] }}<small>berlanjut</small></div>
        <div class="mc-sub">Perlu kelanjutan / follow up</div>
    </div>
</div>

<div class="panel">
    {{-- HEADER & ACTIONS --}}
    <div class="panel-head">
        <div>
            <h2>Rekap Daily Log Book Teknisi</h2>
            <p>Menampilkan seluruh laporan pekerjaan teknisi lapangan beserta foto bukti ber-watermark.</p>
        </div>
        <div class="btn-group">
            @if ($canCreate)
                <a href="{{ route('daily-log.create') }}" class="btn btn-primary" style="background:linear-gradient(135deg, #10b981, #059669);">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M10.75 4.75a.75.75 0 0 0-1.5 0v4.5h-4.5a.75.75 0 0 0 0 1.5h4.5v4.5a.75.75 0 0 0 1.5 0v-4.5h4.5a.75.75 0 0 0 0-1.5h-4.5v-4.5Z"/></svg>
                    + Input Daily Log Baru
                </a>
            @endif
        </div>
    </div>

    {{-- FILTER FORM --}}
    <form method="GET" action="{{ route('daily-log.index') }}" class="filter-form-box">
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:14px; align-items:end;">
            <div>
                <label>Filter Bulan</label>
                <input type="month" name="bulan" class="input" value="{{ request('bulan', $filters['bulan']) }}">
            </div>

            <div>
                <label>Tanggal Spesifik</label>
                <input type="date" name="tanggal" class="input" value="{{ request('tanggal') }}">
            </div>

            <div>
                <label>Status Pekerjaan</label>
                <select name="status" class="input">
                    <option value="">Semua Status</option>
                    <option value="Done" {{ request('status') === 'Done' ? 'selected' : '' }}>Done (Selesai)</option>
                    <option value="Continue" {{ request('status') === 'Continue' ? 'selected' : '' }}>Continue (Berlanjut)</option>
                </select>
            </div>

            <div>
                <label>Teknisi</label>
                <select name="user_id" class="input">
                    <option value="">Semua Teknisi</option>
                    @foreach ($technicians as $tech)
                        <option value="{{ $tech->id }}" {{ request('user_id') == $tech->id ? 'selected' : '' }}>{{ $tech->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label>Cari Lokasi / Uraian</label>
                <input type="text" name="search" class="input" placeholder="Ketik lokasi atau tugas..." value="{{ request('search') }}">
            </div>

            <div style="display:flex; gap:8px;">
                <button type="submit" class="btn btn-sm btn-primary" style="flex:1; justify-content:center; padding:9px 16px;">
                    Terapkan
                </button>
                <a href="{{ route('daily-log.index') }}" class="btn btn-sm" style="background:rgba(255,255,255,0.08); color:#f8fafc; border:1px solid rgba(255,255,255,0.2); padding:9px 14px;" title="Reset Filter">
                    Reset
                </a>
            </div>
        </div>
    </form>

    {{-- CARD LIST PEKERJAAN --}}
    @if ($logs->count() > 0)
        <div class="daily-log-card-grid">
            @foreach ($logs as $log)
                <div class="job-card">
                    <div class="{{ $log->status === 'Done' ? 'job-card-indicator-done' : 'job-card-indicator-continue' }}"></div>

                    <div>
                        <div class="job-card-head">
                            <div>
                                <div class="job-card-date">{{ $log->hari_tanggal }}</div>
                                <div class="job-card-time">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="14" height="14">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm.75-13a.75.75 0 0 0-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 0 0 0-1.5h-3.25V5Z" clip-rule="evenodd"/>
                                    </svg>
                                    <span>{{ $log->jam_mulai }} - {{ $log->jam_selesai }} WIB &middot; ({{ $log->durasi_kerja }})</span>
                                </div>
                            </div>
                            <span class="badge-status {{ $log->status === 'Done' ? 'badge-status-done' : 'badge-status-continue' }}">
                                {{ $log->status }}
                            </span>
                        </div>

                        <div class="job-meta-row" style="margin-top:12px;">
                            <span class="job-location-badge">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="15" height="15">
                                    <path fill-rule="evenodd" d="m9.69 18.933.003.001C9.89 19.02 10 19 10 19s.11.02.308-.066l.002-.001.006-.003.018-.008a5.741 5.741 0 0 0 .281-.14c.186-.096.446-.24.757-.433.62-.384 1.445-.966 2.274-1.765C15.302 14.988 17 12.493 17 9A7 7 0 1 0 3 9c0 3.492 1.698 5.988 3.355 7.584a13.731 13.731 0 0 0 2.273 1.765 11.842 11.842 0 0 0 .976.544l.062.029.018.008.006.003ZM10 11.25a2.25 2.25 0 1 0 0-4.5 2.25 2.25 0 0 0 0 4.5Z" clip-rule="evenodd"/>
                                </svg>
                                {{ $log->lokasi }}
                            </span>
                        </div>

                        <div class="job-desc-box" style="margin-top:10px;" title="{{ $log->deskripsi }}">
                            {{ $log->deskripsi }}
                        </div>
                    </div>

                    {{-- 3 FOTO PEKERJAAN --}}
                    <div>
                        <div style="font-size:0.75rem; color:#cbd5e1; font-weight:600; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:6px;">
                            Foto Bukti (Watermark Otomatis)
                        </div>
                        <div class="job-photos-preview">
                            {{-- Foto Awal --}}
                            <div class="job-photo-thumb" data-lightbox="{{ $log->foto_awal_url }}" data-caption="[FOTO AWAL] {{ $log->lokasi }} — {{ $log->hari_tanggal }} ({{ $log->jam_mulai }} WIB) by {{ $log->user->name }}">
                                <img src="{{ $log->foto_awal_url }}" alt="Foto Awal" loading="lazy">
                                <div class="job-photo-stage-label" style="color:#38bdf8;">Awal</div>
                            </div>
                            {{-- Foto Proses --}}
                            <div class="job-photo-thumb" data-lightbox="{{ $log->foto_proses_url }}" data-caption="[FOTO PROSES] {{ $log->lokasi }} — {{ $log->hari_tanggal }} (Pekerjaan Berlangsung) by {{ $log->user->name }}">
                                <img src="{{ $log->foto_proses_url }}" alt="Foto Proses" loading="lazy">
                                <div class="job-photo-stage-label" style="color:#fbbf24;">Proses</div>
                            </div>
                            {{-- Foto Akhir --}}
                            <div class="job-photo-thumb" data-lightbox="{{ $log->foto_akhir_url }}" data-caption="[FOTO AKHIR] {{ $log->lokasi }} — {{ $log->hari_tanggal }} ({{ $log->jam_selesai }} WIB) [{{ $log->status }}] by {{ $log->user->name }}">
                                <img src="{{ $log->foto_akhir_url }}" alt="Foto Akhir" loading="lazy">
                                <div class="job-photo-stage-label" style="color:#34d399;">Akhir</div>
                            </div>
                        </div>
                    </div>

                    {{-- CARD FOOTER --}}
                    <div class="job-card-footer">
                        <div class="job-author-info">
                            <div class="job-author-avatar">
                                @if($log->user->avatar_url)
                                    <img src="{{ $log->user->avatar_url }}" alt="{{ $log->user->name }}" style="width:100%;height:100%;object-fit:cover;">
                                @else
                                    {{ strtoupper(substr($log->user->name, 0, 1)) }}
                                @endif
                            </div>
                            <span>{{ $log->user->name }}</span>
                        </div>

                        <div style="display:flex; align-items:center; gap:6px;">
                            <a href="{{ route('daily-log.show', $log) }}" class="btn btn-sm" style="background:rgba(255,255,255,0.06); font-size:0.75rem; padding:5px 10px;">
                                Detail →
                            </a>
                            @if(auth()->id() === $log->user_id || in_array(auth()->user()->role, ['admin', 'eng_admin', 'eng_spv'], true))
                                <a href="{{ route('daily-log.edit', $log) }}" class="btn btn-sm" style="background:rgba(255,255,255,0.04); padding:5px 8px;" title="Edit Pekerjaan">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="14" height="14">
                                        <path d="m5.433 13.917 1.262-3.155A4 4 0 0 1 7.58 9.42l6.92-6.918a2.121 2.121 0 0 1 3 3l-6.92 6.918c-.383.383-.84.685-1.343.886l-3.154 1.262a.5.5 0 0 1-.65-.65Z" />
                                        <path d="M3.5 5.75c0-.69.56-1.25 1.25-1.25H10A.75.75 0 0 0 10 3H4.75A2.75 2.75 0 0 0 2 5.75v9.5A2.75 2.75 0 0 0 4.75 18h9.5A2.75 2.75 0 0 0 17 15.25V10a.75.75 0 0 0-1.5 0v5.25c0 .69-.56 1.25-1.25 1.25h-9.5c-.69 0-1.25-.56-1.25-1.25v-9.5Z" />
                                    </svg>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- PAGINATION --}}
        <div style="margin-top: 24px;">
            {{ $logs->links() }}
        </div>
    @else
        <div style="text-align:center; padding:50px 20px; background:rgba(255,255,255,0.02); border-radius:var(--radius-md); border:1px dashed var(--border); margin-top:20px;">
            <div style="width:64px; height:64px; border-radius:50%; background:rgba(16,185,129,0.1); color:#34d399; display:flex; align-items:center; justify-content:center; margin:0 auto 16px;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="32" height="32">
                    <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
                    <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
                    <path d="M9 12l2 2 4-4"></path>
                </svg>
            </div>
            <h3 style="font-size:1.1rem; font-weight:700; color:var(--text-primary); margin-bottom:6px;">Belum Ada Catatan Daily Log Book</h3>
            <p style="font-size:0.85rem; color:var(--text-secondary); max-width:460px; margin:0 auto 20px;">
                Belum ditemukan catatan pekerjaan harian pada filter yang dipilih. Teknisi dapat mulai mendokumentasikan pekerjaan dengan foto ber-watermark.
            </p>
            @if ($canCreate)
                <a href="{{ route('daily-log.create') }}" class="btn btn-primary" style="background:linear-gradient(135deg, #10b981, #059669);">
                    + Input Daily Log Sekarang
                </a>
            @endif
        </div>
    @endif
</div>
@endsection
