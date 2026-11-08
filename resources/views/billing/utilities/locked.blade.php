@extends('layouts.panel')

@section('title', 'Sesi Utilitas Terkunci - Billing System')
@section('page-title', 'Billing System: Akses Dibatasi')
@section('page-subtitle', 'Sesi Penginputan Meteran Tenant Sedang Ditutup oleh Super Admin')

@section('panel')
<div class="panel" style="max-width:640px; margin:30px auto; text-align:center; padding:40px 24px;">
    <!-- Icon Gembok Besar -->
    <div style="width:72px; height:72px; border-radius:50%; background:rgba(239,68,68,0.15); border:2px solid rgba(239,68,68,0.3); display:inline-flex; align-items:center; justify-content:center; color:#f87171; font-size:2.2rem; margin-bottom:20px;">
        🔒
    </div>

    <h2 style="font-size:1.4rem; font-weight:800; color:var(--text-primary); margin-bottom:8px;">
        Sesi Penginputan Utilitas Ditutup
    </h2>

    <p style="font-size:0.92rem; color:var(--text-secondary); line-height:1.6; margin-bottom:24px;">
        Untuk mencegah kesalahan dan penginputan data di luar waktu yang ditentukan, penginputan stand kWh listrik &amp; air tenant hanya dapat dilakukan <strong>1 kali sebulan</strong> ketika <strong>Super Admin</strong> telah membuka jadwal pencatatan.
    </p>

    <!-- Info Box Status -->
    <div style="background:var(--bg-main); border:1px solid var(--border-color); border-radius:var(--radius-md); padding:16px 20px; text-align:left; margin-bottom:24px; font-size:0.85rem;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; padding-bottom:8px; border-bottom:1px solid var(--border-color);">
            <span style="color:var(--text-muted);">Status Penginputan:</span>
            <span class="badge" style="background:rgba(239,68,68,0.15); color:#f87171; font-weight:700; padding:4px 10px; font-size:0.8rem;">
                🔒 TERKUNCI (DITUTUP)
            </span>
        </div>

        @if($setting->keterangan)
            <div style="margin-bottom:8px;">
                <span style="color:var(--text-muted); display:block; font-size:0.75rem; text-transform:uppercase; font-weight:700; margin-bottom:2px;">Catatan Manajemen:</span>
                <span style="color:var(--text-primary); font-style:italic;">"{{ $setting->keterangan }}"</span>
            </div>
        @endif

        @if($setting->closed_at)
            <div style="font-size:0.78rem; color:var(--text-muted); margin-top:8px;">
                Ditutup pada: <strong style="color:var(--text-secondary);">{{ $setting->closed_at->translatedFormat('d F Y H:i') }} WIB</strong>
                @if($setting->closedByUser)
                    oleh {{ $setting->closedByUser->name }}
                @endif
            </div>
        @endif
    </div>

    <!-- Tombol Aksi -->
    <div style="display:flex; justify-content:center; gap:12px; flex-wrap:wrap;">
        <a href="{{ route('billing.utilities.hub') }}" class="btn btn-sm">
            📊 Lihat Ringkasan Utilitas
        </a>
        <a href="{{ route('dashboard') }}" class="btn btn-sm">
            🏠 Kembali ke Dashboard
        </a>

        @if(auth()->user()->isSuperAdmin())
            <form method="POST" action="{{ route('billing.utilities.toggle-session') }}" style="display:inline;">
                @csrf
                <input type="hidden" name="action" value="open">
                <input type="hidden" name="active_year" value="{{ now()->year }}">
                <input type="hidden" name="active_month" value="{{ now()->month }}">
                <button type="submit" class="btn btn-sm btn-primary" style="background:#22c55e; border-color:#22c55e; font-weight:700;">
                    🔓 Buka Sesi Penginputan Sekarang (Super Admin)
                </button>
            </form>
        @endif
    </div>
</div>
@endsection
