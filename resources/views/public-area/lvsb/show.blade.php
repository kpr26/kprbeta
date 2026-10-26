@extends('layouts.panel')

@php
    $n = fn ($v, $d = 2) => ($v !== null && $v !== '') ? number_format((float)$v, $d, ',', '.') : '—';
    $canManage = auth()->user()->isSuperAdmin() || auth()->user()->isSupervisor() || auth()->user()->isAdmin();
@endphp

@section('title', 'Detail Logsheet LVSB — ' . $reading->tanggal->format('d/m/Y'))
@section('page-title', 'Detail Pembacaan Panel LVSB')
@section('page-subtitle', 'Tanggal: ' . $reading->tanggal->translatedFormat('l, d F Y'))

@section('panel')
<section class="panel" style="max-width:1200px; margin:0 auto;">
    {{-- Header & Aksi --}}
    <div class="panel-head" style="align-items:center; flex-wrap:wrap; gap:12px;">
        <div style="display:flex; align-items:center; gap:12px;">
            <a href="{{ route('public-area.lvsb.index') }}" class="btn btn-sm">
                ← Kembali ke Daftar LVSB
            </a>
            <h2 style="margin:0; font-size:1.25rem;">
                Logsheet LVSB — {{ $reading->tanggal->format('d/m/Y') }}
            </h2>
        </div>

        <div style="display:flex; gap:8px; align-items:center;">
            @if ($reading->isVerified())
                <span class="badge badge-ok" style="font-size:0.8rem; padding:6px 12px;">
                    ✓ Terverifikasi {{ $reading->verified_at ? 'oleh ' . ($reading->verifier?->name ?? 'Supervisor') . ' (' . $reading->verified_at->format('d/m/Y H:i') . ')' : '(Input Admin / Backdate)' }}
                </span>
            @else
                <span class="badge badge-wait" style="font-size:0.8rem; padding:6px 12px;">Menunggu Verifikasi</span>
                @if (auth()->user()->isSuperAdmin() || auth()->user()->isSupervisor())
                    <form method="POST" action="{{ route('public-area.lvsb.verify', $reading) }}">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-primary" style="background:#10b981; border:none; color:#fff; font-weight:700;">
                            ✓ Verifikasi Sekarang
                        </button>
                    </form>
                @endif
            @endif

            @if (auth()->user()->isSuperAdmin())
                <form method="POST" action="{{ route('public-area.lvsb.destroy', $reading) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data logsheet LVSB tanggal {{ $reading->tanggal->format('d/m/Y') }}?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger">
                        Hapus
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- Info Petugas & Waktu Simpan --}}
    <div style="background:var(--bg-card); border:1px solid var(--border); border-radius:10px; padding:12px 18px; margin-bottom:20px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; font-size:0.85rem; box-shadow:var(--shadow-sm);">
        <div>
            <span style="color:var(--text-muted);">Petugas Pencatat:</span>
            <b style="color:var(--text-primary); margin-left:6px;">{{ $reading->user?->name ?? 'User Terhapus' }}</b>
            <span style="color:var(--text-muted); font-size:0.75rem;">({{ $reading->user?->role ?? 'teknisi' }})</span>
        </div>
        <div style="color:var(--text-muted);">
            Tersimpan pada: <span style="color:var(--text-secondary);">{{ $reading->created_at->format('d/m/Y H:i:s') }}</span>
        </div>
    </div>

    {{-- Detail 6 Grup LVSB --}}
    <div style="display:flex; flex-direction:column; gap:20px;">
        @foreach ($structure as $gKey => $group)
            <div style="background:rgba(20,28,45,0.7); border:1px solid {{ $group['color'] }}33; border-radius:10px; overflow:hidden;">
                {{-- Header Grup --}}
                <div style="background:rgba(0,0,0,0.35); padding:10px 16px; border-bottom:1px solid {{ $group['color'] }}25; display:flex; align-items:center; gap:10px;">
                    <span style="background:{{ $group['color'] }}20; color:{{ $group['color'] }}; border:1px solid {{ $group['color'] }}40; font-size:0.72rem; padding:2px 8px; border-radius:6px; font-weight:700; text-transform:uppercase;">
                        {{ $group['badge'] }}
                    </span>
                    <span style="font-weight:700; color:var(--text-primary); font-size:0.98rem;">
                        {{ $group['title'] }}
                    </span>
                </div>

                {{-- Isi Grup --}}
                <div style="padding:14px 16px;">
                    @if ($group['has_subgroups'])
                        <div style="display:flex; flex-direction:column; gap:14px;">
                            @foreach ($group['subgroups'] as $sKey => $sub)
                                <div style="background:rgba(10,14,23,0.5); border:1px solid rgba(255,255,255,0.06); border-radius:8px; padding:12px 14px;">
                                    <div style="font-weight:600; font-size:0.88rem; color:#e2e8f0; margin-bottom:10px; display:flex; align-items:center; gap:6px;">
                                        <span style="color:{{ $group['color'] }};">▸</span>
                                        <span>{{ $sub['label'] }}</span>
                                    </div>

                                    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(160px, 1fr)); gap:10px;">
                                        @foreach ($sub['fields'] as $fKey => $fMeta)
                                            @php
                                                $v = $reading->val($gKey, $sKey, $fKey);
                                                $dec = $fMeta['step'] === '0.0001' ? 4 : ($fMeta['step'] === '0.1' ? 1 : 2);
                                            @endphp
                                            <div style="background:rgba(0,0,0,0.25); border:1px solid rgba(255,255,255,0.04); border-radius:6px; padding:8px 12px;">
                                                <span style="font-size:0.72rem; color:var(--text-muted); display:block; text-transform:uppercase; letter-spacing:0.04em;">{{ $fMeta['label'] }}</span>
                                                <div style="font-size:1.15rem; font-weight:700; color:var(--text-primary); margin-top:2px;">
                                                    {{ $n($v, $dec) }}
                                                    <small style="font-size:0.72rem; color:var(--text-muted); font-weight:normal;">{{ $fMeta['unit'] }}</small>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(170px, 1fr)); gap:12px;">
                            @foreach ($group['fields'] as $fKey => $fMeta)
                                @php
                                    $v = $reading->val($gKey, $fKey);
                                    $dec = $fMeta['step'] === '0.0001' ? 4 : ($fMeta['step'] === '0.1' ? 1 : 2);
                                @endphp
                                <div style="background:rgba(0,0,0,0.25); border:1px solid rgba(255,255,255,0.04); border-radius:6px; padding:10px 14px;">
                                    <span style="font-size:0.74rem; color:var(--text-muted); display:block; text-transform:uppercase; letter-spacing:0.04em;">{{ $fMeta['label'] }}</span>
                                    <div style="font-size:1.3rem; font-weight:700; color:var(--text-primary); margin-top:4px;">
                                        {{ $n($v, $dec) }}
                                        <small style="font-size:0.76rem; color:var(--text-muted); font-weight:normal;">{{ $fMeta['unit'] }}</small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    {{-- Lampiran Foto Per-Grup (6 Grup) & Catatan --}}
    @php
        $allFotos = $reading->getAllFotos();
    @endphp
    <div style="background:var(--bg-card); border:1px solid var(--border); border-radius:10px; padding:16px; margin-top:24px; box-shadow:var(--shadow-sm);">
        <span style="font-size:0.78rem; text-transform:uppercase; letter-spacing:0.06em; color:var(--text-muted); font-weight:700; display:block; margin-bottom:12px;">
            📷 Lampiran Foto Fisik Panel LVSB ({{ count($allFotos) }} / 6 Terlampir)
        </span>

        @if(!empty($allFotos))
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:14px;">
                @foreach ($structure as $gKey => $group)
                    @php
                        $foto = $reading->getFoto($gKey);
                    @endphp
                    <div style="background:rgba(0,0,0,0.25); border:1px solid rgba(255,255,255,0.05); border-radius:8px; padding:10px; display:flex; flex-direction:column; gap:8px;">
                        <div style="font-size:0.75rem; font-weight:700; color:var(--text-secondary); text-transform:uppercase;">
                            {{ $group['title'] }}
                        </div>
                        @if($foto)
                            <a href="{{ asset('storage/'.$foto) }}" data-lightbox="{{ asset('storage/'.$foto) }}" data-caption="Foto: {{ $group['title'] }} — {{ $reading->tanggal->format('d/m/Y') }}" style="display:block; border-radius:6px; overflow:hidden; border:1px solid rgba(251,191,36,0.3); aspect-ratio:4/3; background:#000;">
                                <img src="{{ asset('storage/'.$foto) }}" alt="{{ $group['title'] }}" style="width:100%; height:100%; object-fit:cover; display:block; transition:transform 0.2s;" onmouseover="this.style.transform='scale(1.03)'" onmouseout="this.style.transform='scale(1)'">
                            </a>
                            <span style="font-size:0.68rem; color:var(--text-muted); text-align:center;">Klik untuk perbesar</span>
                        @else
                            <div style="height:110px; display:flex; align-items:center; justify-content:center; background:rgba(255,255,255,0.02); border:1px dashed rgba(255,255,255,0.1); border-radius:6px; color:var(--text-muted); font-size:0.74rem;">
                                Tidak ada foto
                            </div>
                        @endif
                    </div>
                @endforeach

                {{-- Fallback jika ada foto umum lama --}}
                @if(isset($allFotos['general']))
                    <div style="background:rgba(0,0,0,0.25); border:1px solid rgba(255,255,255,0.05); border-radius:8px; padding:10px; display:flex; flex-direction:column; gap:8px;">
                        <div style="font-size:0.75rem; font-weight:700; color:var(--text-secondary); text-transform:uppercase;">
                            Foto Keseluruhan (Legacy)
                        </div>
                        <a href="{{ asset('storage/'.$allFotos['general']) }}" data-lightbox="{{ asset('storage/'.$allFotos['general']) }}" data-caption="Foto LVSB — {{ $reading->tanggal->format('d/m/Y') }}" style="display:block; border-radius:6px; overflow:hidden; border:1px solid rgba(251,191,36,0.3); aspect-ratio:4/3; background:#000;">
                            <img src="{{ asset('storage/'.$allFotos['general']) }}" alt="Foto Legacy" style="width:100%; height:100%; object-fit:cover;">
                        </a>
                    </div>
                @endif
            </div>
        @else
            <p style="color:var(--text-muted); font-size:0.84rem; margin:0;">Tidak ada lampiran foto untuk pencatatan ini.</p>
        @endif
    </div>

    {{-- Catatan --}}
    <div style="background:var(--bg-card); border:1px solid var(--border); border-radius:10px; padding:16px; margin-top:16px; box-shadow:var(--shadow-sm);">
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
</section>
@endsection
