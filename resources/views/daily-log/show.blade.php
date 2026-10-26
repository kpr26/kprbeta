@extends('layouts.panel')

@section('title', 'Detail Pekerjaan — ' . $log->lokasi)
@section('page-title', 'Detail Daily Log Book')
@section('page-subtitle', $log->hari_tanggal . ' &middot; ' . $log->lokasi)

@push('styles')
<style>
.detail-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 24px;
}
@media (max-width: 860px) {
    .detail-grid {
        grid-template-columns: 1fr;
    }
}
.detail-photo-gallery {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
    margin-top: 20px;
}
.detail-photo-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: var(--radius-md);
    overflow: hidden;
    display: flex;
    flex-direction: column;
}
.detail-photo-card-head {
    padding: 12px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: var(--bg-card-subtle);
    border-bottom: 1px solid var(--border);
}
.detail-photo-media {
    position: relative;
    aspect-ratio: 4 / 3;
    background: var(--bg-card-subtle);
    overflow: hidden;
    cursor: pointer;
}
.detail-photo-media img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform 0.3s ease;
}
.detail-photo-media:hover img {
    transform: scale(1.03);
}
.detail-photo-footer {
    padding: 12px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 0.78rem;
    color: var(--text-secondary);
}
.info-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.88rem;
}
.info-table tr td {
    padding: 10px 12px;
    border-bottom: 1px solid var(--border);
}
.info-table tr td:first-child {
    width: 140px;
    color: var(--text-secondary);
    font-weight: 500;
}
.info-table tr td:last-child {
    color: var(--text-primary);
    font-weight: 600;
}

@media print {
    .sidebar, .mobile-topbar, .mobile-bottom-nav, .topbar, .btn-group, .non-printable {
        display: none !important;
    }
    .main-content {
        margin: 0 !important;
        padding: 0 !important;
    }
    .panel {
        background: none !important;
        border: none !important;
        color: #000 !important;
    }
    body {
        background: #fff !important;
        color: #000 !important;
    }
    .detail-photo-media img {
        max-height: 250px;
        object-fit: contain;
    }
}
</style>
@endpush

@section('panel')
<div class="panel">
    <div class="panel-head">
        <div>
            <h2>Laporan Pekerjaan Harian #LOG-{{ str_pad($log->id, 5, '0', STR_PAD_LEFT) }}</h2>
            <p>Dicatat oleh <b style="color:var(--info);">{{ $log->user->name }}</b> pada {{ $log->created_at->translatedFormat('d F Y, H:i') }} WIB</p>
        </div>
        <div class="btn-group non-printable">
            <a href="{{ route('daily-log.index') }}" class="btn btn-sm">
                ← Kembali ke Riwayat
            </a>
            <button type="button" class="btn btn-sm" onclick="window.print()" style="background:var(--bg-card-subtle);">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="16" height="16">
                    <path fill-rule="evenodd" d="M5 2.75C5 1.784 5.784 1 6.75 1h6.5c.966 0 1.75.784 1.75 1.75v3.5A1.75 1.75 0 0 1 13.25 8H6.75A1.75 1.75 0 0 1 5 6.25v-3.5Zm1.5 0v3.5c0 .138.112.25.25.25h6.5a.25.25 0 0 0 .25-.25v-3.5a.25.25 0 0 0-.25-.25h-6.5a.25.25 0 0 0-.25.25Z" clip-rule="evenodd"/>
                    <path fill-rule="evenodd" d="M2.5 7A2.5 2.5 0 0 0 0 9.5v5A2.5 2.5 0 0 0 2.5 17H4v1.25C4 19.216 4.784 20 5.75 20h8.5c.966 0 1.75-.784 1.75-1.75V17h1.5a2.5 2.5 0 0 0 2.5-2.5v-5A2.5 2.5 0 0 0 17.5 7h-15Zm12 8.5V14H5.5v1.5h9ZM16 11a1 1 0 1 1 0-2 1 1 0 0 1 0 2Z" clip-rule="evenodd"/>
                </svg>
                Cetak Lembar Laporan
            </button>
            @if ($canEdit)
                <a href="{{ route('daily-log.edit', $log) }}" class="btn btn-sm btn-primary">
                    Edit Pekerjaan
                </a>
                <form method="POST" action="{{ route('daily-log.destroy', $log) }}" style="display:inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data pekerjaan ini beserta foto-fotonya?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger">
                        Hapus
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- STATUS BANNER --}}
    @if ($log->status === 'Done')
        <div class="status-banner ok" style="margin-bottom:20px;">
            <span><span class="dot"></span>Pekerjaan ini berstatus <b>Done (Selesai Tuntas)</b> pada {{ $log->jam_selesai }} WIB.</span>
            <span class="badge" style="background:#10b981; color:#fff; font-weight:700;">DONE</span>
        </div>
    @else
        <div class="status-banner warn" style="margin-bottom:20px;">
            <span><span class="dot"></span>Pekerjaan ini berstatus <b>Continue (Perlu Tindak Lanjut / Belum Tuntas)</b>.</span>
            <span class="badge" style="background:#f59e0b; color:#fff; font-weight:700;">CONTINUE</span>
        </div>
    @endif

    <div class="detail-grid">
        {{-- KOLOM KIRI: RINCIAN PEKERJAAN --}}
        <div>
            <div style="background:var(--bg-card-subtle); border:1px solid var(--border); border-radius:var(--radius-md); padding:20px; margin-bottom:20px;">
                <h3 style="font-size:0.95rem; font-weight:700; color:var(--text-primary); margin-bottom:12px; display:flex; align-items:center; gap:8px;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="18" height="18" style="color:var(--accent-light);">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-7-4a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM9 9a.75.75 0 0 0 0 1.5h.253a.25.25 0 0 1 .244.304l-.459 2.066A1.75 1.75 0 0 0 10.747 15H11a.75.75 0 0 0 0-1.5h-.253a.25.25 0 0 1-.244-.304l.459-2.066A1.75 1.75 0 0 0 9.253 9H9Z" clip-rule="evenodd"/>
                    </svg>
                    Deskripsi &amp; Uraian Pekerjaan
                </h3>
                <div style="font-size:0.92rem; color:var(--text-primary); line-height:1.7; white-space:pre-line;">
                    {{ $log->deskripsi }}
                </div>

                @if ($log->catatan)
                    <div style="margin-top:16px; padding-top:14px; border-top:1px dashed var(--border); font-size:0.85rem; color:var(--warning);">
                        <strong>Catatan Tambahan:</strong> {{ $log->catatan }}
                    </div>
                @endif
            </div>

            {{-- 3 FOTO DENGAN WATERMARK --}}
            <div>
                <h3 style="font-size:1rem; font-weight:700; color:var(--text-primary); display:flex; align-items:center; gap:8px;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="18" height="18" style="color:var(--warning);">
                        <path fill-rule="evenodd" d="M1 5.25A2.25 2.25 0 0 1 3.25 3h13.5A2.25 2.25 0 0 1 19 5.25v9.5A2.25 2.25 0 0 1 16.75 17H3.25A2.25 2.25 0 0 1 1 14.75v-9.5Zm1.5 5.81v3.69c0 .414.336.75.75.75h13.5a.75.75 0 0 0 .75-.75v-1.69l-3.375-3.375a1.125 1.125 0 0 0-1.59 0l-1.035 1.035-4.47-4.47a1.125 1.125 0 0 0-1.59 0L2.5 11.06ZM10.5 7.5a1.5 1.5 0 1 1 3 0 1.5 1.5 0 0 1-3 0Z" clip-rule="evenodd" />
                    </svg>
                    Foto Dokumentasi Pekerjaan (Watermark Otomatis)
                </h3>
                <p style="font-size:0.8rem; color:var(--text-muted); margin-top:2px;">
                    Klik foto untuk memperbesar tampilan resolusi penuh dengan watermark tanggal, jam, dan lokasi.
                </p>

                <div class="detail-photo-gallery">
                    {{-- 1. FOTO AWAL --}}
                    <div class="detail-photo-card">
                        <div class="detail-photo-card-head">
                            <span style="color:var(--info); font-weight:700; font-size:0.82rem;">1. Foto Awal Pekerjaan</span>
                            <span style="font-size:0.75rem; color:var(--text-muted);">{{ $log->jam_mulai }} WIB</span>
                        </div>
                        <div class="detail-photo-media" data-lightbox="{{ $log->foto_awal_url }}" data-caption="[FOTO AWAL] {{ $log->lokasi }} — {{ $log->hari_tanggal }} ({{ $log->jam_mulai }} WIB) by {{ $log->user->name }}">
                            <img src="{{ $log->foto_awal_url }}" alt="Foto Awal">
                        </div>
                        <div class="detail-photo-footer">
                            <span>Kondisi sebelum dikerjakan</span>
                            <a href="{{ $log->foto_awal_url }}" target="_blank" download class="btn btn-sm" style="padding:3px 8px; font-size:0.72rem;">Unduh</a>
                        </div>
                    </div>

                    {{-- 2. FOTO PROSES --}}
                    <div class="detail-photo-card">
                        <div class="detail-photo-card-head">
                            <span style="color:var(--warning); font-weight:700; font-size:0.82rem;">2. Foto Proses Pekerjaan</span>
                            <span style="font-size:0.75rem; color:var(--text-muted);">Pekerjaan Berlangsung</span>
                        </div>
                        <div class="detail-photo-media" data-lightbox="{{ $log->foto_proses_url }}" data-caption="[FOTO PROSES] {{ $log->lokasi }} — {{ $log->hari_tanggal }} (Proses Pekerjaan) by {{ $log->user->name }}">
                            <img src="{{ $log->foto_proses_url }}" alt="Foto Proses">
                        </div>
                        <div class="detail-photo-footer">
                            <span>Saat pekerjaan berlangsung</span>
                            <a href="{{ $log->foto_proses_url }}" target="_blank" download class="btn btn-sm" style="padding:3px 8px; font-size:0.72rem;">Unduh</a>
                        </div>
                    </div>

                    {{-- 3. FOTO AKHIR --}}
                    <div class="detail-photo-card">
                        <div class="detail-photo-card-head">
                            <span style="color:var(--success); font-weight:700; font-size:0.82rem;">3. Foto Akhir Pekerjaan</span>
                            <span style="font-size:0.75rem; color:var(--text-muted);">{{ $log->jam_selesai }} WIB</span>
                        </div>
                        <div class="detail-photo-media" data-lightbox="{{ $log->foto_akhir_url }}" data-caption="[FOTO AKHIR] {{ $log->lokasi }} — {{ $log->hari_tanggal }} ({{ $log->jam_selesai }} WIB) [{{ $log->status }}] by {{ $log->user->name }}">
                            <img src="{{ $log->foto_akhir_url }}" alt="Foto Akhir">
                        </div>
                        <div class="detail-photo-footer">
                            <span>Hasil selesai / tindak lanjut</span>
                            <a href="{{ $log->foto_akhir_url }}" target="_blank" download class="btn btn-sm" style="padding:3px 8px; font-size:0.72rem;">Unduh</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- KOLOM KANAN: SPESIFIKASI METADATA --}}
        <div>
            <div style="background:var(--bg-card-subtle); border:1px solid var(--border); border-radius:var(--radius-md); padding:18px;">
                <h3 style="font-size:0.92rem; font-weight:700; color:var(--text-primary); margin-bottom:14px; text-transform:uppercase; letter-spacing:0.06em; color:var(--text-muted);">
                    Data Metadata Pekerjaan
                </h3>

                <table class="info-table">
                    <tr>
                        <td>Hari &amp; Tanggal</td>
                        <td>{{ $log->hari_tanggal }}</td>
                    </tr>
                    <tr>
                        <td>Jam Kerja</td>
                        <td>{{ $log->jam_mulai }} - {{ $log->jam_selesai }} WIB</td>
                    </tr>
                    <tr>
                        <td>Total Durasi</td>
                        <td><span style="color:var(--info);">{{ $log->durasi_kerja }}</span></td>
                    </tr>
                    <tr>
                        <td>Lokasi</td>
                        <td><span style="color:var(--warning);">📍 {{ $log->lokasi }}</span></td>
                    </tr>
                    <tr>
                        <td>Status</td>
                        <td>
                            @if ($log->status === 'Done')
                                <span class="badge" style="background:rgba(16,185,129,0.2); color:var(--success); border:1px solid rgba(16,185,129,0.4); padding:3px 8px; border-radius:4px;">DONE</span>
                            @else
                                <span class="badge" style="background:rgba(245,158,11,0.2); color:var(--warning); border:1px solid rgba(245,158,11,0.4); padding:3px 8px; border-radius:4px;">CONTINUE</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td>Teknisi Pelaksana</td>
                        <td>
                            <div style="display:flex; align-items:center; gap:8px;">
                                <div style="width:22px; height:22px; border-radius:50%; background:linear-gradient(135deg, var(--accent), #a855f7); color:#fff; font-size:0.65rem; font-weight:700; display:flex; align-items:center; justify-content:center;">
                                    {{ strtoupper(substr($log->user->name, 0, 1)) }}
                                </div>
                                <span>{{ $log->user->name }}</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>Input Masuk</td>
                        <td style="font-size:0.8rem; color:var(--text-muted);">{{ $log->created_at->format('d/m/Y H:i') }} WIB</td>
                    </tr>
                    @if ($log->updated_at != $log->created_at)
                    <tr>
                        <td>Terakhir Diupdate</td>
                        <td style="font-size:0.8rem; color:var(--text-muted);">{{ $log->updated_at->format('d/m/Y H:i') }} WIB</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
