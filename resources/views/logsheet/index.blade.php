@extends('layouts.panel')

@php
    $isAdmin = auth()->user()->role === 'admin';
    $canManage = in_array(auth()->user()->role, ['admin', 'eng_admin', 'eng_spv'], true);
    $periode = sprintf('%04d-%02d', $year, $month);
    $n = fn ($v) => $v === null ? '—' : number_format($v, 2, ',', '.');
@endphp

@section('title', 'Logsheet Listrik & Air')
@section('page-title', 'Logsheet Listrik & Air')
@section('page-subtitle', 'Periode ' . \Carbon\Carbon::create($year, $month)->translatedFormat('F Y'))

@section('panel')

{{-- Status hari ini --}}
@if ($today)
    <div class="status-banner ok">
        <span><span class="dot"></span>Logsheet hari ini sudah dicatat oleh <b>{{ $today->user->name }}</b> pukul {{ $today->created_at->format('H:i') }}.</span>
        <a href="{{ route('logsheet.show', $today) }}" class="btn btn-sm">Lihat</a>
    </div>
@else
    <div class="status-banner warn">
        <span><span class="dot"></span>Logsheet hari ini (<b>{{ now()->translatedFormat('d F Y') }}</b>) belum dicatat.</span>
        <a href="{{ route('logsheet.create') }}" class="btn btn-primary btn-sm">+ Input Sekarang</a>
    </div>
@endif

{{-- Ringkasan bulan --}}
<div class="meter-grid">
    <div class="meter-card mc-lwbp">
        <div class="mc-label"><svg viewBox="0 0 20 20" fill="currentColor"><path d="M11.983 1.907a.75.75 0 0 0-1.292-.657l-8.5 9.5A.75.75 0 0 0 2.75 12h6.572l-1.305 6.093a.75.75 0 0 0 1.292.657l8.5-9.5A.75.75 0 0 0 17.25 8h-6.572l1.305-6.093Z"/></svg>kWh LWBP</div>
        <div class="mc-value">{{ $n($summary['lwbp']) }}<small>kWh</small></div>
        <div class="mc-sub">Luar Waktu Beban Puncak</div>
    </div>
    <div class="meter-card mc-wbp">
        <div class="mc-label"><svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M13.5 4.938a7 7 0 1 1-9.006 1.737c.202-.257.59-.218.793.039.278.352.594.672.943.954.332.269.786-.049.773-.476a5.977 5.977 0 0 1 .572-2.759 6.026 6.026 0 0 1 2.486-2.665c.247-.14.55-.016.677.238A6.967 6.967 0 0 0 13.5 4.938ZM14 12a4 4 0 0 1-4 4c-1.913 0-3.52-1.398-3.91-3.182-.093-.429.44-.643.814-.413a4.043 4.043 0 0 0 1.601.564c.303.038.531-.24.51-.544a5.975 5.975 0 0 1 1.315-4.192.447.447 0 0 1 .431-.16A4.001 4.001 0 0 1 14 12Z" clip-rule="evenodd"/></svg>kWh WBP</div>
        <div class="mc-value">{{ $n($summary['wbp']) }}<small>kWh</small></div>
        <div class="mc-sub">Waktu Beban Puncak</div>
    </div>
    <div class="meter-card mc-total">
        <div class="mc-label"><svg viewBox="0 0 20 20" fill="currentColor"><path d="M15.5 2A1.5 1.5 0 0 0 14 3.5v13a1.5 1.5 0 0 0 1.5 1.5h1a1.5 1.5 0 0 0 1.5-1.5v-13A1.5 1.5 0 0 0 16.5 2h-1ZM9.5 6A1.5 1.5 0 0 0 8 7.5v9A1.5 1.5 0 0 0 9.5 18h1a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 10.5 6h-1ZM3.5 10A1.5 1.5 0 0 0 2 11.5v5A1.5 1.5 0 0 0 3.5 18h1A1.5 1.5 0 0 0 6 16.5v-5A1.5 1.5 0 0 0 4.5 10h-1Z"/></svg>Total Listrik</div>
        <div class="mc-value">{{ $n($summary['kwh']) }}<small>kWh</small></div>
        <div class="mc-sub">{{ $summary['jumlah'] }} hari tercatat</div>
    </div>
    <div class="meter-card mc-air">
        <div class="mc-label"><svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 2.5c.3 0 .58.14.77.37C12.4 4.87 16 9.45 16 12.5a6 6 0 0 1-12 0c0-3.05 3.6-7.63 5.23-9.63A1 1 0 0 1 10 2.5Z" clip-rule="evenodd"/></svg>Pemakaian Air</div>
        <div class="mc-value">{{ $n($summary['air']) }}<small>m³</small></div>
        <div class="mc-sub">Water meter gedung</div>
    </div>
</div>

<section class="panel">
    <div class="panel-head">
        <div>
            <h2>Rekap Logsheet Harian</h2>
            <p>Pemakaian = stand hari ini − stand pencatatan sebelumnya</p>
        </div>
        <div class="btn-group">
            <form class="filter-form" method="GET">
                <input type="month" name="periode" value="{{ $periode }}" max="{{ now()->format('Y-m') }}" onchange="this.form.submit()" aria-label="Periode">
            </form>
            @if ($canManage)
                <a href="{{ route('logsheet.excel', ['periode' => $periode]) }}" class="btn btn-excel" id="btn-export-excel">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path d="M10.75 2.75a.75.75 0 0 0-1.5 0v8.614L6.295 8.235a.75.75 0 1 0-1.09 1.03l4.25 4.5a.75.75 0 0 0 1.09 0l4.25-4.5a.75.75 0 0 0-1.09-1.03l-2.955 3.129V2.75Z"/><path d="M3.5 12.75a.75.75 0 0 0-1.5 0v2.5A2.75 2.75 0 0 0 4.75 18h10.5A2.75 2.75 0 0 0 18 15.25v-2.5a.75.75 0 0 0-1.5 0v2.5c0 .69-.56 1.25-1.25 1.25H4.75c-.69 0-1.25-.56-1.25-1.25v-2.5Z"/></svg>Excel
                </a>
                <a href="{{ route('logsheet.pdf', ['periode' => $periode]) }}" target="_blank" class="btn btn-pdf" id="btn-export-pdf">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5 2.75C5 1.784 5.784 1 6.75 1h6.5c.966 0 1.75.784 1.75 1.75v3.552c.377.046.752.097 1.126.153A2.212 2.212 0 0 1 18 8.653v4.097A2.25 2.25 0 0 1 15.75 15h-.241l.305 1.984A1.75 1.75 0 0 1 14.084 19H5.915a1.75 1.75 0 0 1-1.73-2.016L4.492 15H4.25A2.25 2.25 0 0 1 2 12.75V8.653c0-1.082.775-2.034 1.874-2.198.374-.056.75-.107 1.127-.153L5 6.25v-3.5Zm8.5 3.397a41.533 41.533 0 0 0-7 0V2.75a.25.25 0 0 1 .25-.25h6.5a.25.25 0 0 1 .25.25v3.397ZM6.608 12.5a.25.25 0 0 0-.247.212l-.693 4.5a.25.25 0 0 0 .247.288h8.17a.25.25 0 0 0 .246-.288l-.692-4.5a.25.25 0 0 0-.247-.212H6.608Z" clip-rule="evenodd"/></svg>PDF
                </a>
            @endif
            <a href="{{ route('logsheet.create') }}" class="btn btn-primary" id="btn-input-logsheet">
                <svg viewBox="0 0 20 20" fill="currentColor"><path d="M10.75 4.75a.75.75 0 0 0-1.5 0v4.5h-4.5a.75.75 0 0 0 0 1.5h4.5v4.5a.75.75 0 0 0 1.5 0v-4.5h4.5a.75.75 0 0 0 0-1.5h-4.5v-4.5Z"/></svg>Input Logsheet
            </a>
        </div>
    </div>

    <div class="table-wrap">
        <table class="log-table">
            <thead>
                <tr>
                    <th rowspan="2" class="l">Tanggal</th>
                    <th colspan="5" class="g-elec">Listrik (kWh)</th>
                    <th colspan="2" class="g-water">Air (m³)</th>
                    <th rowspan="2" class="c">Foto</th>
                    <th rowspan="2" class="l">Teknisi</th>
                    <th rowspan="2" class="c">Status</th>
                    <th rowspan="2" class="c">Aksi</th>
                </tr>
                <tr>
                    <th>Stand LWBP</th><th>Pakai LWBP</th>
                    <th>Stand WBP</th><th>Pakai WBP</th>
                    <th>Total</th>
                    <th>Stand</th><th>Pakai</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($rows as $r)
                <tr>
                    <td class="l">{{ $r->tanggal->translatedFormat('D, d M') }}</td>
                    <td>{{ $n($r->stand_lwbp) }}</td>
                    <td class="use use-elec">{{ $n($r->pakai_lwbp) }}</td>
                    <td>{{ $n($r->stand_wbp) }}</td>
                    <td class="use use-peak">{{ $n($r->pakai_wbp) }}</td>
                    <td class="use">{{ $n($r->pakai_kwh) }}</td>
                    <td>{{ $n($r->stand_air) }}</td>
                    <td class="use use-water">{{ $n($r->pakai_air) }}</td>
                    <td class="c">
                        <span class="thumbs">
                            <img src="{{ asset('storage/'.$r->foto_kwh) }}" alt="kWh" data-lightbox="{{ asset('storage/'.$r->foto_kwh) }}" data-caption="kWh meter — {{ $r->tanggal->format('d/m/Y') }}">
                            <img src="{{ asset('storage/'.$r->foto_air) }}" alt="Air" data-lightbox="{{ asset('storage/'.$r->foto_air) }}" data-caption="Water meter — {{ $r->tanggal->format('d/m/Y') }}">
                        </span>
                    </td>
                    <td class="l">{{ $r->user->name }}</td>
                    <td class="c">
                        @if ($r->verified_at)
                            <span class="badge badge-ok" title="Oleh {{ $r->verifier?->name }}">✓ Terverifikasi</span>
                        @else
                            <span class="badge badge-wait">Menunggu</span>
                        @endif
                    </td>
                    <td class="c">
                        <div class="btn-group" style="justify-content:center">
                            <a href="{{ route('logsheet.show', $r) }}" class="btn btn-sm">Detail</a>
                            @if ($canManage || (auth()->user()->role !== 'teknisi' && $r->user_id === auth()->id() && $r->tanggal->isToday()))
                                <a href="{{ route('logsheet.edit', $r) }}" class="btn btn-sm">Edit</a>
                            @endif
                            @if ($canManage)
                                @unless ($r->verified_at)
                                    <form method="POST" action="{{ route('logsheet.verify', $r) }}" class="inline">@csrf
                                        <button class="btn btn-sm btn-success">Verifikasi</button>
                                    </form>
                                @endunless
                            @endif
                            @if ($isAdmin)
                                <form method="POST" action="{{ route('logsheet.destroy', $r) }}" class="inline" onsubmit="return confirm('Hapus logsheet {{ $r->tanggal->format('d/m/Y') }}?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-danger">Hapus</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="12" class="empty">Belum ada data logsheet pada periode ini.</td></tr>
            @endforelse
            </tbody>
            @if ($rows->count())
            <tfoot>
                <tr>
                    <td class="l">TOTAL</td>
                    <td></td><td class="use-elec">{{ $n($summary['lwbp']) }}</td>
                    <td></td><td class="use-peak">{{ $n($summary['wbp']) }}</td>
                    <td>{{ $n($summary['kwh']) }}</td>
                    <td></td><td class="use-water">{{ $n($summary['air']) }}</td>
                    <td colspan="4"></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>

    {{-- ── Tampilan Khusus Layar HP / Mobile Card List ── --}}
    <div class="mobile-logsheet-list">
        @forelse ($rows as $r)
            <div class="mobile-log-card">
                <div class="mlc-header">
                    <div>
                        <span class="mlc-date">{{ $r->tanggal->translatedFormat('D, d M Y') }}</span>
                        <div class="mlc-tech">Oleh <b>{{ $r->user->name }}</b></div>
                    </div>
                    <div>
                        @if ($r->verified_at)
                            <span class="badge badge-ok">✓ Terverifikasi</span>
                        @else
                            <span class="badge badge-wait">Menunggu</span>
                        @endif
                    </div>
                </div>

                <div class="mlc-grid">
                    <div class="mlc-stat-box mlc-stat-elec">
                        <div class="mlc-stat-label">
                            <svg viewBox="0 0 20 20" fill="currentColor"><path d="M11.983 1.907a.75.75 0 0 0-1.292-.657l-8.5 9.5A.75.75 0 0 0 2.75 12h6.572l-1.305 6.093a.75.75 0 0 0 1.292.657l8.5-9.5A.75.75 0 0 0 17.25 8h-6.572l1.305-6.093Z"/></svg>
                            Listrik
                        </div>
                        <div class="mlc-stat-val">{{ $n($r->pakai_kwh) }} <small>kWh</small></div>
                        <div class="mlc-stat-sub">LWBP {{ $n($r->pakai_lwbp) }} · WBP {{ $n($r->pakai_wbp) }}</div>
                    </div>

                    <div class="mlc-stat-box mlc-stat-water">
                        <div class="mlc-stat-label">
                            <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 2.5c.3 0 .58.14.77.37C12.4 4.87 16 9.45 16 12.5a6 6 0 0 1-12 0c0-3.05 3.6-7.63 5.23-9.63A1 1 0 0 1 10 2.5Z" clip-rule="evenodd"/></svg>
                            Air
                        </div>
                        <div class="mlc-stat-val">{{ $n($r->pakai_air) }} <small>m³</small></div>
                        <div class="mlc-stat-sub">Stand: {{ $n($r->stand_air) }}</div>
                    </div>
                </div>

                <div class="mlc-footer">
                    <div class="thumbs">
                        <img src="{{ asset('storage/'.$r->foto_kwh) }}" alt="kWh" data-lightbox="{{ asset('storage/'.$r->foto_kwh) }}" data-caption="kWh meter — {{ $r->tanggal->format('d/m/Y') }}">
                        <img src="{{ asset('storage/'.$r->foto_air) }}" alt="Air" data-lightbox="{{ asset('storage/'.$r->foto_air) }}" data-caption="Water meter — {{ $r->tanggal->format('d/m/Y') }}">
                    </div>
                    <div class="btn-group">
                        <a href="{{ route('logsheet.show', $r) }}" class="btn btn-sm">Lihat Detail</a>
                        @if ($canManage || (auth()->user()->role !== 'teknisi' && $r->user_id === auth()->id() && $r->tanggal->isToday()))
                            <a href="{{ route('logsheet.edit', $r) }}" class="btn btn-sm">Edit</a>
                        @endif
                        @if ($canManage && ! $r->verified_at)
                            <form method="POST" action="{{ route('logsheet.verify', $r) }}" class="inline">@csrf
                                <button class="btn btn-sm btn-success">Verifikasi</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="empty" style="text-align:center;padding:30px 10px;color:var(--text-muted)">
                Belum ada data logsheet pada periode ini.
            </div>
        @endforelse
    </div>
</section>
@endsection
