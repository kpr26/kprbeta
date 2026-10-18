@extends('layouts.panel')

@php
    $n = fn ($v) => $v === null ? '—' : number_format($v, 2, ',', '.');
    $dElec = fn ($k) => (isset($prevElec) && $prevElec && $prevElec->$k !== null && $r->$k !== null) ? $r->$k - $prevElec->$k : ($prev && $prev->$k !== null && $r->$k !== null ? $r->$k - $prev->$k : null);
    $dWater = fn ($k) => (isset($prevWater) && $prevWater && $prevWater->$k !== null && $r->$k !== null) ? $r->$k - $prevWater->$k : ($prev && $prev->$k !== null && $r->$k !== null ? $r->$k - $prev->$k : null);
@endphp

@section('title', 'Detail Logsheet')
@section('page-title', 'Detail Logsheet')
@section('page-subtitle', $r->tanggal->translatedFormat('l, d F Y'))

@section('panel')
<section class="panel">
    <div class="panel-head">
        <div>
            <h2>Crosscheck Stand Meter</h2>
            <p>Dicatat oleh <b>{{ $r->user->name }}</b> · {{ $r->created_at->translatedFormat('d M Y H:i') }}
                @if ($r->verified_at) · <span class="badge badge-ok">✓ Diverifikasi {{ $r->verifier?->name }} ({{ $r->verified_at->format('d/m/Y H:i') }})</span>
                @else · <span class="badge badge-wait">Menunggu verifikasi</span>@endif
            </p>
        </div>
        <div class="btn-group">
            <a href="{{ route('logsheet.index', ['periode' => $r->tanggal->format('Y-m')]) }}" class="btn">← Kembali</a>
            @php
                $canManage = in_array(auth()->user()->role, ['admin', 'eng_admin', 'eng_spv'], true);
            @endphp
            @if ($canManage || (auth()->user()->role !== 'teknisi' && $r->user_id === auth()->id() && $r->tanggal->isToday()))
                <a href="{{ route('logsheet.edit', $r) }}" class="btn">Edit Logsheet</a>
            @endif
            @if ($canManage && ! $r->verified_at)
                <form method="POST" action="{{ route('logsheet.verify', $r) }}" class="inline">@csrf
                    <button class="btn btn-success">✓ Data Sesuai Foto — Verifikasi</button>
                </form>
            @endif
        </div>
    </div>

    <div class="detail-grid">
        <div class="meter-section elec">
            <h3><span style="color:#fbbf24;">⚡</span> kWh Meter Listrik</h3>
            @if ($r->foto_kwh)
                <img class="photo-big" src="{{ asset('storage/'.$r->foto_kwh) }}" alt="Foto kWh meter" data-lightbox="{{ asset('storage/'.$r->foto_kwh) }}" data-caption="kWh meter — {{ $r->tanggal->format('d/m/Y') }}">
            @else
                <div style="background:rgba(255,255,255,0.03); border:1px dashed var(--border); border-radius:var(--radius-sm); padding:28px 16px; text-align:center; color:var(--text-muted); font-size:0.88rem; margin-bottom:16px;">
                    Belum ada foto/input meter listrik untuk tanggal ini.
                    @if (auth()->user()->role !== 'teknisi' || ($r->tanggal->isToday() && $r->stand_lwbp === null))
                    <div style="margin-top:10px;">
                        <a href="{{ route('logsheet.create.listrik') }}" class="btn btn-sm" style="color:#fbbf24; border-color:rgba(245,158,11,0.3);">+ Input Meter Listrik</a>
                    </div>
                    @endif
                </div>
            @endif
            <dl class="kv">
                <dt>Stand LWBP</dt><dd>{{ $r->stand_lwbp !== null ? $n($r->stand_lwbp).' kWh' : '—' }}</dd>
                <dt>Pemakaian LWBP</dt><dd class="use-elec">{{ $dElec('stand_lwbp') !== null ? $n($dElec('stand_lwbp')).' kWh' : '—' }}</dd>
                <dt>Stand WBP</dt><dd>{{ $r->stand_wbp !== null ? $n($r->stand_wbp).' kWh' : '—' }}</dd>
                <dt>Pemakaian WBP</dt><dd class="use-peak">{{ $dElec('stand_wbp') !== null ? $n($dElec('stand_wbp')).' kWh' : '—' }}</dd>
                <dt>Total Pemakaian</dt><dd>{{ ($dElec('stand_lwbp') !== null && $dElec('stand_wbp') !== null) ? $n($dElec('stand_lwbp') + $dElec('stand_wbp')).' kWh' : '—' }}</dd>
            </dl>
        </div>
        <div class="meter-section water">
            <h3><span style="color:#38bdf8;">💧</span> Water Meter</h3>
            @if ($r->foto_air)
                <img class="photo-big" src="{{ asset('storage/'.$r->foto_air) }}" alt="Foto water meter" data-lightbox="{{ asset('storage/'.$r->foto_air) }}" data-caption="Water meter — {{ $r->tanggal->format('d/m/Y') }}">
            @else
                <div style="background:rgba(255,255,255,0.03); border:1px dashed var(--border); border-radius:var(--radius-sm); padding:28px 16px; text-align:center; color:var(--text-muted); font-size:0.88rem; margin-bottom:16px;">
                    Belum ada foto/input water meter untuk tanggal ini.
                    @if (auth()->user()->role !== 'teknisi' || ($r->tanggal->isToday() && $r->stand_air === null))
                    <div style="margin-top:10px;">
                        <a href="{{ route('logsheet.create.air') }}" class="btn btn-sm" style="color:#38bdf8; border-color:rgba(56,189,248,0.3);">+ Input Water Meter</a>
                    </div>
                    @endif
                </div>
            @endif
            <dl class="kv">
                <dt>Stand Air</dt><dd>{{ $r->stand_air !== null ? $n($r->stand_air).' m³' : '—' }}</dd>
                <dt>Pemakaian Air</dt><dd class="use-water">{{ $dWater('stand_air') !== null ? $n($dWater('stand_air')).' m³' : '—' }}</dd>
                <dt>Pencatatan sebelumnya</dt><dd>{{ ($prevWater?->tanggal ?? $prevElec?->tanggal ?? $prev?->tanggal)?->format('d/m/Y') ?? '—' }}</dd>
                <dt>Catatan</dt><dd style="font-weight:400">{{ $r->catatan ?: '—' }}</dd>
            </dl>
        </div>
    </div>
</section>
@endsection
