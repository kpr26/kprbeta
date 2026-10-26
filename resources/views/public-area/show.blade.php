@extends('layouts.panel')

@php
    $canManage = in_array(auth()->user()->role, ['admin', 'superadmin', 'eng_admin', 'eng_spv'], true);
    $isAdmin = in_array(auth()->user()->role, ['admin', 'superadmin'], true);
    $n = fn ($v) => $v !== null ? number_format($v, 2, ',', '.') : '—';
    $backRoute = $reading->category === 'water' ? route('public-area.water.index') : route('public-area.electricity.index');
@endphp

@section('title', 'Detail ' . $reading->meter_name)
@section('page-title', 'Detail Pencatatan Meteran')
@section('page-subtitle', $reading->meter_name . ' — ' . $reading->tanggal->translatedFormat('l, d F Y'))

@section('panel')
<div class="panel logsheet-choice-container">
    <div class="panel-head">
        <div>
            <h2>{{ $reading->meter_name }}</h2>
            <p>Tanggal pencatatan: <b>{{ $reading->tanggal->translatedFormat('d F Y') }}</b> &middot; Dicatat oleh <b>{{ $reading->user?->name ?? 'User Terhapus' }}</b></p>
        </div>
        <div class="btn-group">
            <a href="{{ $backRoute }}" class="btn btn-sm">← Kembali</a>
        </div>
    </div>

    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:20px; margin-top:16px;">
        <div style="background:var(--bg-card); border:1px solid var(--border); border-radius:12px; padding:20px; box-shadow:var(--shadow-sm);">
            <div style="font-size:0.75rem; text-transform:uppercase; color:var(--text-muted); font-weight:700;">Stand Meteran</div>
            <div style="font-size:2.2rem; font-weight:700; color:var(--text-primary); font-family:var(--font-serif); margin:6px 0;">
                {{ $n($reading->stand_meter) }} <small style="font-size:0.9rem; color:var(--text-muted); font-family:var(--font-sans);">{{ $reading->unit }}</small>
            </div>

            <div style="border-top:1px solid var(--border); padding-top:12px; margin-top:12px; font-size:0.84rem; color:var(--text-secondary); line-height:1.6;">
                <div>Stand Sebelumnya: <b>{{ $prev ? $n($prev->stand_meter) . ' ' . $reading->unit : '—' }}</b></div>
                <div>Pemakaian: <b style="color:var(--accent);">{{ $pemakaian !== null ? '+' . $n($pemakaian) . ' ' . $reading->unit : '—' }}</b></div>
                <div style="margin-top:6px;">Status Verifikasi:
                    @if($reading->isVerified())
                        <span class="badge badge-ok">✓ Terverifikasi {{ $reading->verified_at ? 'oleh ' . ($reading->verifier?->name ?? 'Admin') : '(Input Admin / Backdate)' }}</span>
                    @else
                        <span class="badge badge-wait">Menunggu Verifikasi</span>
                    @endif
                </div>
                @if($reading->catatan)
                    <div style="margin-top:10px; padding:10px; background:var(--bg-card-subtle); border-radius:6px; border:1px solid var(--border);">
                        <strong style="color:var(--text-primary); font-size:0.78rem; display:block;">Catatan Petugas:</strong>
                        <i>"{{ $reading->catatan }}"</i>
                    </div>
                @endif
            </div>
        </div>

        <div>
            @if($reading->foto)
                <div style="text-align:center;">
                    <img src="{{ asset('storage/'.$reading->foto) }}" alt="Foto Meter" data-lightbox="{{ asset('storage/'.$reading->foto) }}" data-caption="{{ $reading->meter_name }} — {{ $reading->tanggal->format('d/m/Y') }}" style="max-width:100%; max-height:360px; border-radius:12px; border:1px solid rgba(200,170,110,0.3); box-shadow:0 8px 24px rgba(0,0,0,0.4); cursor:pointer;">
                    <p style="font-size:0.75rem; color:var(--text-muted); margin-top:8px;">Klik foto untuk memperbesar</p>
                </div>
            @else
                <div style="padding:40px; text-align:center; color:var(--text-muted); background:var(--bg-card-subtle); border:1px dashed var(--border); border-radius:12px;">
                    Tidak ada lampiran foto.
                </div>
            @endif
        </div>
    </div>

    <div style="margin-top:24px; padding-top:16px; border-top:1px solid var(--border); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
        <a href="{{ $backRoute }}" class="btn">Kembali ke Rekap</a>
        <div class="btn-group">
            @if ($canManage && $reading->needsVerification())
                <form method="POST" action="{{ route('public-area.verify', $reading) }}" class="inline">@csrf
                    <button class="btn btn-success">✓ Verifikasi Pencatatan</button>
                </form>
            @endif
            @if ($isAdmin || in_array(auth()->user()->role, ['eng_admin', 'eng_spv'], true))
                <form method="POST" action="{{ route('public-area.destroy', $reading) }}" class="inline" onsubmit="return confirm('Hapus permanen pencatatan ini?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-danger">Hapus Data</button>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection
