@extends('layouts.panel')

@php
    $n = fn ($v) => $v === null ? '—' : number_format($v, 2, ',', '.');
    $d = fn ($k) => $prev ? $r->$k - $prev->$k : null;
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
            <h3>kWh Meter Listrik</h3>
            <img class="photo-big" src="{{ asset('storage/'.$r->foto_kwh) }}" alt="Foto kWh meter" data-lightbox="{{ asset('storage/'.$r->foto_kwh) }}" data-caption="kWh meter — {{ $r->tanggal->format('d/m/Y') }}">
            <dl class="kv">
                <dt>Stand LWBP</dt><dd>{{ $n($r->stand_lwbp) }} kWh</dd>
                <dt>Pemakaian LWBP</dt><dd class="use-elec">{{ $n($d('stand_lwbp')) }} kWh</dd>
                <dt>Stand WBP</dt><dd>{{ $n($r->stand_wbp) }} kWh</dd>
                <dt>Pemakaian WBP</dt><dd class="use-peak">{{ $n($d('stand_wbp')) }} kWh</dd>
                <dt>Total Pemakaian</dt><dd>{{ $prev ? $n($d('stand_lwbp') + $d('stand_wbp')) : '—' }} kWh</dd>
            </dl>
        </div>
        <div class="meter-section water">
            <h3>Water Meter</h3>
            <img class="photo-big" src="{{ asset('storage/'.$r->foto_air) }}" alt="Foto water meter" data-lightbox="{{ asset('storage/'.$r->foto_air) }}" data-caption="Water meter — {{ $r->tanggal->format('d/m/Y') }}">
            <dl class="kv">
                <dt>Stand Air</dt><dd>{{ $n($r->stand_air) }} m³</dd>
                <dt>Pemakaian Air</dt><dd class="use-water">{{ $n($d('stand_air')) }} m³</dd>
                <dt>Pencatatan sebelumnya</dt><dd>{{ $prev?->tanggal->format('d/m/Y') ?? '—' }}</dd>
                <dt>Catatan</dt><dd style="font-weight:400">{{ $r->catatan ?: '—' }}</dd>
            </dl>
        </div>
    </div>
</section>
@endsection
