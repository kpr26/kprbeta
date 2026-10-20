@extends('layouts.panel')

@php
    $n = fn ($v, $d = 2) => ($v !== null && $v !== '') ? number_format((float)$v, $d, ',', '.') : '—';
    $canManage = auth()->user()->isSuperAdmin() || auth()->user()->isSupervisor();
@endphp

@section('title', 'Detail Logsheet MVSB Incoming From PLN')
@section('page-title', 'Detail MVSB Incoming From PLN')
@section('page-subtitle', 'Rincian Pembacaan Parameter Panel MVSB ' . $reading->tanggal->translatedFormat('l, d F Y'))

@section('panel')
<section class="panel" style="max-width:1000px; margin:0 auto;">
    {{-- Header & Aksi --}}
    <div class="panel-head" style="align-items:flex-start; flex-wrap:wrap; gap:16px;">
        <div>
            <div style="display:flex; align-items:center; gap:8px;">
                <span style="font-size:1.4rem;">⚡</span>
                <h2 style="margin:0; font-size:1.35rem;">Logsheet MVSB — {{ $reading->tanggal->translatedFormat('d F Y') }}</h2>
            </div>
            <p style="color:var(--text-secondary); margin:4px 0 0; font-size:0.88rem;">
                Dicatat oleh <b>{{ $reading->user?->name ?? 'Teknisi' }}</b> pada {{ $reading->created_at->format('d/m/Y H:i') }}
            </p>
        </div>

        <div style="display:flex; gap:8px; flex-wrap:wrap; align-items:center;">
            <a href="{{ route('public-area.mvsb.index') }}" class="btn btn-sm">
                ← Kembali ke Riwayat
            </a>

            @if($canManage && !$reading->isVerified())
                <form method="POST" action="{{ route('public-area.mvsb.verify', $reading) }}">
                    @csrf
                    <button type="submit" class="btn btn-sm" style="background:#10b981; color:#fff; border:none; display:inline-flex; align-items:center; gap:6px;">
                        ✓ Verifikasi Logsheet
                    </button>
                </form>
            @endif

            @if(auth()->user()->isSuperAdmin())
                <form method="POST" action="{{ route('public-area.mvsb.destroy', $reading) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data logsheet MVSB tanggal {{ $reading->tanggal->format('d/m/Y') }}?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm" style="background:rgba(239,68,68,0.15); color:#f87171; border:1px solid rgba(239,68,68,0.3);">
                        Hapus Data
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- Banner Status Verifikasi --}}
    @if($reading->isVerified())
        <div style="background:rgba(16,185,129,0.12); border:1px solid rgba(16,185,129,0.3); border-radius:10px; padding:12px 16px; margin-bottom:20px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:8px;">
            <div style="display:flex; align-items:center; gap:10px;">
                <span style="color:#34d399; font-size:1.2rem;">✓</span>
                <div>
                    <strong style="color:#34d399; font-size:0.9rem;">Logsheet Sudah Terverifikasi</strong>
                    <div style="color:var(--text-secondary); font-size:0.78rem;">
                        Diverifikasi oleh <b>{{ $reading->verifier?->name ?? 'Supervisor' }}</b> pada {{ $reading->verified_at->translatedFormat('d F Y H:i') }}
                    </div>
                </div>
            </div>
            <span class="badge badge-ok">Verified</span>
        </div>
    @else
        <div style="background:rgba(245,158,11,0.08); border:1px solid rgba(245,158,11,0.25); border-radius:10px; padding:12px 16px; margin-bottom:20px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:8px;">
            <div style="display:flex; align-items:center; gap:10px;">
                <span style="color:#fbbf24; font-size:1.2rem;">⏳</span>
                <div>
                    <strong style="color:#fbbf24; font-size:0.9rem;">Menunggu Verifikasi</strong>
                    <div style="color:var(--text-secondary); font-size:0.78rem;">
                        Logsheet belum ditinjau oleh Supervisor / Super Admin.
                    </div>
                </div>
            </div>
            <span class="badge badge-wait">Pending</span>
        </div>
    @endif

    {{-- Grid Parameter Metrik MVSB --}}
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(260px, 1fr)); gap:16px; margin-bottom:24px;">
        {{-- Card Voltage 20kV --}}
        <div style="background:rgba(15,22,35,0.7); border:1px solid rgba(168,85,247,0.3); border-radius:10px; padding:18px;">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px;">
                <span style="font-size:0.78rem; text-transform:uppercase; letter-spacing:0.06em; color:var(--text-muted); font-weight:700;">
                    Tegangan Menengah
                </span>
                <span class="badge" style="background:rgba(168,85,247,0.15); color:#c084fc; border:1px solid rgba(168,85,247,0.3); font-size:0.72rem;">
                    Incoming PLN
                </span>
            </div>
            <h4 style="margin:0 0 6px; font-size:0.92rem; color:var(--text-secondary);">Voltage 20kV</h4>
            <div style="font-size:2rem; font-weight:700; color:#c084fc;">
                {{ $n($reading->voltage_20kv, 1) }}
                <small style="font-size:1rem; color:var(--text-muted); font-weight:normal;">Volt</small>
            </div>
        </div>

        {{-- Card Current --}}
        <div style="background:rgba(15,22,35,0.7); border:1px solid rgba(56,189,248,0.3); border-radius:10px; padding:18px;">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px;">
                <span style="font-size:0.78rem; text-transform:uppercase; letter-spacing:0.06em; color:var(--text-muted); font-weight:700;">
                    Arus Listrik
                </span>
                <span class="badge" style="background:rgba(56,189,248,0.15); color:#38bdf8; border:1px solid rgba(56,189,248,0.3); font-size:0.72rem;">
                    Current
                </span>
            </div>
            <h4 style="margin:0 0 6px; font-size:0.92rem; color:var(--text-secondary);">Current (Ampere)</h4>
            <div style="font-size:2rem; font-weight:700; color:#38bdf8;">
                {{ $n($reading->current, 2) }}
                <small style="font-size:1rem; color:var(--text-muted); font-weight:normal;">Ampere</small>
            </div>
        </div>
    </div>

    {{-- Foto & Catatan --}}
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:16px;">
        {{-- Foto Fisik Panel --}}
        <div style="background:rgba(15,22,35,0.7); border:1px solid rgba(255,255,255,0.06); border-radius:10px; padding:16px;">
            <span style="font-size:0.78rem; text-transform:uppercase; letter-spacing:0.06em; color:var(--text-muted); font-weight:700; display:block; margin-bottom:8px;">
                Foto Fisik Panel MVSB
            </span>
            @if ($reading->foto)
                <a href="{{ asset('storage/'.$reading->foto) }}" data-lightbox="{{ asset('storage/'.$reading->foto) }}" data-caption="Foto Fisik MVSB — {{ $reading->tanggal->format('d/m/Y') }}" style="display:inline-block; border-radius:8px; overflow:hidden; border:1px solid rgba(168,85,247,0.3);">
                    <img src="{{ asset('storage/'.$reading->foto) }}" alt="Foto MVSB" style="max-height:220px; width:auto; display:block; object-fit:cover;">
                </a>
                <p style="font-size:0.74rem; color:var(--text-muted); margin:6px 0 0;">Klik gambar untuk memperbesar</p>
            @else
                <p style="color:var(--text-muted); font-size:0.84rem; margin:0;">Tidak ada lampiran foto untuk pencatatan ini.</p>
            @endif
        </div>

        {{-- Catatan --}}
        <div style="background:rgba(15,22,35,0.7); border:1px solid rgba(255,255,255,0.06); border-radius:10px; padding:16px;">
            <span style="font-size:0.78rem; text-transform:uppercase; letter-spacing:0.06em; color:var(--text-muted); font-weight:700; display:block; margin-bottom:8px;">
                Catatan Kondisi Panel
            </span>
            @if ($reading->catatan)
                <p style="color:var(--text-primary); font-size:0.9rem; line-height:1.6; margin:0; white-space:pre-line;">
                    {{ $reading->catatan }}
                </p>
            @else
                <p style="color:var(--text-muted); font-size:0.84rem; margin:0;">Tidak ada catatan khusus.</p>
            @endif
        </div>
    </div>
</section>
@endsection
