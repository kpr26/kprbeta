@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/logsheet.css') }}">
@endpush

@section('content')
@php
    $authUser = auth()->user();
    $isTeknisi = $authUser->role === 'teknisi';
    $roleDisplayNames = [
        'admin'     => 'Administrator',
        'user'      => 'User',
        'teknisi'   => 'Teknisi',
        'eng_admin' => 'Eng Admin',
        'eng_spv'   => 'Eng Spv',
    ];
    $currentRoleLabel = $roleDisplayNames[$authUser->role] ?? ucfirst($authUser->role);
@endphp
<div class="dashboard-wrapper">

    <!-- Mobile Topbar for HP & PWA -->
    <header class="mobile-topbar">
        <div class="mobile-topbar-brand">
            <div class="logo-icon logo-icon--xs">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M11.47 3.841a.75.75 0 0 1 1.06 0l8.69 8.69a.75.75 0 1 0 1.06-1.061l-8.689-8.69a2.25 2.25 0 0 0-3.182 0l-8.69 8.69a.75.75 0 1 0 1.061 1.06l8.69-8.689Z"/>
                    <path d="m12 5.432 8.159 8.159c.03.03.06.058.091.086v6.198c0 1.035-.84 1.875-1.875 1.875H15a.75.75 0 0 1-.75-.75v-4.5a.75.75 0 0 0-.75-.75h-3a.75.75 0 0 0-.75.75V21a.75.75 0 0 1-.75.75H5.625a1.875 1.875 0 0 1-1.875-1.875v-6.198a2.29 2.29 0 0 0 .091-.086L12 5.432Z"/>
                </svg>
            </div>
            <div class="mobile-topbar-info">
                <span class="mobile-brand-title">KPR Beta</span>
                @if($isTeknisi)
                    <span class="badge-role-pill">Teknisi</span>
                @else
                    <span class="badge-role-pill badge-role-pill--{{ $authUser->role }}">{{ $currentRoleLabel }}</span>
                @endif
            </div>
        </div>

        <div class="mobile-topbar-actions">
            <!-- PWA Install Button (auto-shown if installable) -->
            <button type="button" class="btn-pwa-install pwa-install-btn" style="display:none;" title="Pasang aplikasi di layar utama HP">
                <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a.75.75 0 0 1 .75.75v7.69l2.22-2.22a.75.75 0 1 1 1.06 1.06l-3.5 3.5a.75.75 0 0 1-1.06 0l-3.5-3.5a.75.75 0 1 1 1.06-1.06l2.22 2.22V3.75A.75.75 0 0 1 10 3ZM3.75 14.5a.75.75 0 0 1 .75.75 3.25 3.25 0 0 0 3.25 3.25h4.5a3.25 3.25 0 0 0 3.25-3.25.75.75 0 0 1 1.5 0 4.75 4.75 0 0 1-4.75 4.75h-4.5A4.75 4.75 0 0 1 2.25 15.25a.75.75 0 0 1 .75-.75Z" clip-rule="evenodd"/></svg>
                <span>Pasang</span>
            </button>

            <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit" class="mobile-btn-logout" title="Keluar">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 4.25A2.25 2.25 0 0 1 5.25 2h5.5A2.25 2.25 0 0 1 13 4.25v2a.75.75 0 0 1-1.5 0v-2a.75.75 0 0 0-.75-.75h-5.5a.75.75 0 0 0-.75.75v11.5c0 .414.336.75.75.75h5.5a.75.75 0 0 0 .75-.75v-2a.75.75 0 0 1 1.5 0v2A2.25 2.25 0 0 1 10.75 18h-5.5A2.25 2.25 0 0 1 3 15.75V4.25Z" clip-rule="evenodd"/><path fill-rule="evenodd" d="M19 10a.75.75 0 0 0-.75-.75H8.704l1.048-1.08a.75.75 0 1 0-1.004-1.11l-2.5 2.567a.75.75 0 0 0 0 1.086l2.5 2.567a.75.75 0 1 0 1.004-1.11l-1.048-1.08h9.546A.75.75 0 0 0 19 10Z" clip-rule="evenodd"/></svg>
                </button>
            </form>
        </div>
    </header>

    <!-- Sidebar Desktop -->
    <aside class="sidebar">
        <div class="sidebar-logo">
            <div class="logo-icon logo-icon--sm">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M11.47 3.841a.75.75 0 0 1 1.06 0l8.69 8.69a.75.75 0 1 0 1.06-1.061l-8.689-8.69a2.25 2.25 0 0 0-3.182 0l-8.69 8.69a.75.75 0 1 0 1.061 1.06l8.69-8.689Z"/>
                    <path d="m12 5.432 8.159 8.159c.03.03.06.058.091.086v6.198c0 1.035-.84 1.875-1.875 1.875H15a.75.75 0 0 1-.75-.75v-4.5a.75.75 0 0 0-.75-.75h-3a.75.75 0 0 0-.75.75V21a.75.75 0 0 1-.75.75H5.625a1.875 1.875 0 0 1-1.875-1.875v-6.198a2.29 2.29 0 0 0 .091-.086L12 5.432Z"/>
                </svg>
            </div>
            <div>
                <span>KPR Beta</span>
                @if($isTeknisi)
                    <div style="font-size:0.75rem; color:#f59e0b; font-weight:600; text-transform:uppercase; letter-spacing:0.05em;">Role Teknisi</div>
                @elseif($authUser->role === 'eng_admin')
                    <div style="font-size:0.75rem; color:#38bdf8; font-weight:600; text-transform:uppercase; letter-spacing:0.05em;">Role Eng Admin</div>
                @elseif($authUser->role === 'eng_spv')
                    <div style="font-size:0.75rem; color:#c084fc; font-weight:600; text-transform:uppercase; letter-spacing:0.05em;">Role Eng Spv</div>
                @endif
            </div>
        </div>

        <nav class="sidebar-nav">
            @if(! $isTeknisi)
            <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'nav-item--active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9.293 2.293a1 1 0 0 1 1.414 0l7 7A1 1 0 0 1 17 11h-1v6a1 1 0 0 1-1 1h-2a1 1 0 0 1-1-1v-3a1 1 0 0 0-1-1H9a1 1 0 0 0-1 1v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-6H3a1 1 0 0 1-.707-1.707l7-7Z" clip-rule="evenodd"/></svg>
                Dashboard
            </a>
            @endif

            <a href="{{ route('logsheet.create') }}" class="nav-item {{ request()->routeIs('logsheet.create') ? 'nav-item--active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M10.75 4.75a.75.75 0 0 0-1.5 0v4.5h-4.5a.75.75 0 0 0 0 1.5h4.5v4.5a.75.75 0 0 0 1.5 0v-4.5h4.5a.75.75 0 0 0 0-1.5h-4.5v-4.5Z"/></svg>
                Input Logsheet
            </a>

            <a href="{{ route('logsheet.index') }}" class="nav-item {{ request()->routeIs('logsheet.index', 'logsheet.show', 'logsheet.edit') ? 'nav-item--active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.5 2A1.5 1.5 0 0 0 3 3.5v13A1.5 1.5 0 0 0 4.5 18h11a1.5 1.5 0 0 0 1.5-1.5V7.621a1.5 1.5 0 0 0-.44-1.06l-4.12-4.122A1.5 1.5 0 0 0 11.378 2H4.5Zm2.25 8.5a.75.75 0 0 0 0 1.5h6.5a.75.75 0 0 0 0-1.5h-6.5Zm0 3a.75.75 0 0 0 0 1.5h6.5a.75.75 0 0 0 0-1.5h-6.5Z" clip-rule="evenodd"/></svg>
                Riwayat Logsheet
            </a>

            <!-- Tombol install PWA di sidebar desktop -->
            <button type="button" class="nav-item pwa-install-btn" style="display:none; color:#38bdf8; margin-top:8px;">
                <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a.75.75 0 0 1 .75.75v7.69l2.22-2.22a.75.75 0 1 1 1.06 1.06l-3.5 3.5a.75.75 0 0 1-1.06 0l-3.5-3.5a.75.75 0 1 1 1.06-1.06l2.22 2.22V3.75A.75.75 0 0 1 10 3ZM3.75 14.5a.75.75 0 0 1 .75.75 3.25 3.25 0 0 0 3.25 3.25h4.5a3.25 3.25 0 0 0 3.25-3.25.75.75 0 0 1 1.5 0 4.75 4.75 0 0 1-4.75 4.75h-4.5A4.75 4.75 0 0 1 2.25 15.25a.75.75 0 0 1 .75-.75Z" clip-rule="evenodd"/></svg>
                Pasang PWA
            </button>
        </nav>

        <div class="sidebar-bottom">
            @if(! $isTeknisi)
            <a href="{{ route('profile.edit') }}" class="nav-item {{ request()->routeIs('profile.*') ? 'nav-item--active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-5.5-2.5a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0ZM10 12a5.99 5.99 0 0 0-4.793 2.39A6.483 6.483 0 0 0 10 16.5a6.483 6.483 0 0 0 4.793-2.11A5.99 5.99 0 0 0 10 12Z" clip-rule="evenodd"/></svg>
                Pengaturan Akun
            </a>
            @endif
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="nav-item nav-item--logout">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 4.25A2.25 2.25 0 0 1 5.25 2h5.5A2.25 2.25 0 0 1 13 4.25v2a.75.75 0 0 1-1.5 0v-2a.75.75 0 0 0-.75-.75h-5.5a.75.75 0 0 0-.75.75v11.5c0 .414.336.75.75.75h5.5a.75.75 0 0 0 .75-.75v-2a.75.75 0 0 1 1.5 0v2A2.25 2.25 0 0 1 10.75 18h-5.5A2.25 2.25 0 0 1 3 15.75V4.25Z" clip-rule="evenodd"/><path fill-rule="evenodd" d="M19 10a.75.75 0 0 0-.75-.75H8.704l1.048-1.08a.75.75 0 1 0-1.004-1.11l-2.5 2.567a.75.75 0 0 0 0 1.086l2.5 2.567a.75.75 0 1 0 1.004-1.11l-1.048-1.08h9.546A.75.75 0 0 0 19 10Z" clip-rule="evenodd"/></svg>
                    Keluar
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <header class="topbar">
            <div class="topbar-left">
                <h1 class="page-title">@yield('page-title')</h1>
                <p class="page-subtitle">@yield('page-subtitle', now()->translatedFormat('l, d F Y'))</p>
            </div>
            <div class="topbar-right">
                <div class="user-badge">
                    <div class="avatar">{{ strtoupper(substr($authUser->name, 0, 1)) }}</div>
                    <div class="user-info">
                        <span class="user-name">{{ $authUser->name }}</span>
                        <span class="user-role">{{ $currentRoleLabel }} · {{ '@'.$authUser->username }}</span>
                    </div>
                </div>
            </div>
        </header>

        @if (session('success'))
            <div class="alert alert-success" role="alert">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd"/></svg>
                {{ session('success') }}
            </div>
        @endif

        @yield('panel')
    </main>

    <!-- Bottom Navigation Bar for HP / Mobile Screens -->
    <nav class="mobile-bottom-nav">
        @if(! $isTeknisi)
        <a href="{{ route('dashboard') }}" class="mobile-nav-link {{ request()->routeIs('dashboard') ? 'mobile-nav-link--active' : '' }}">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9.293 2.293a1 1 0 0 1 1.414 0l7 7A1 1 0 0 1 17 11h-1v6a1 1 0 0 1-1 1h-2a1 1 0 0 1-1-1v-3a1 1 0 0 0-1-1H9a1 1 0 0 0-1 1v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-6H3a1 1 0 0 1-.707-1.707l7-7Z" clip-rule="evenodd"/></svg>
            <span>Dashboard</span>
        </a>
        @endif

        <a href="{{ route('logsheet.create') }}" class="mobile-nav-link {{ request()->routeIs('logsheet.create') ? 'mobile-nav-link--active' : '' }}">
            <div class="mobile-nav-icon-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M10.75 4.75a.75.75 0 0 0-1.5 0v4.5h-4.5a.75.75 0 0 0 0 1.5h4.5v4.5a.75.75 0 0 0 1.5 0v-4.5h4.5a.75.75 0 0 0 0-1.5h-4.5v-4.5Z"/></svg>
            </div>
            <span>Input Log</span>
        </a>

        <a href="{{ route('logsheet.index') }}" class="mobile-nav-link {{ request()->routeIs('logsheet.index', 'logsheet.show', 'logsheet.edit') ? 'mobile-nav-link--active' : '' }}">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.5 2A1.5 1.5 0 0 0 3 3.5v13A1.5 1.5 0 0 0 4.5 18h11a1.5 1.5 0 0 0 1.5-1.5V7.621a1.5 1.5 0 0 0-.44-1.06l-4.12-4.122A1.5 1.5 0 0 0 11.378 2H4.5Zm2.25 8.5a.75.75 0 0 0 0 1.5h6.5a.75.75 0 0 0 0-1.5h-6.5Zm0 3a.75.75 0 0 0 0 1.5h6.5a.75.75 0 0 0 0-1.5h-6.5Z" clip-rule="evenodd"/></svg>
            <span>Riwayat</span>
        </a>

        @if(! $isTeknisi)
        <a href="{{ route('profile.edit') }}" class="mobile-nav-link {{ request()->routeIs('profile.*') ? 'mobile-nav-link--active' : '' }}">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-5.5-2.5a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0ZM10 12a5.99 5.99 0 0 0-4.793 2.39A6.483 6.483 0 0 0 10 16.5a6.483 6.483 0 0 0 4.793-2.11A5.99 5.99 0 0 0 10 12Z" clip-rule="evenodd"/></svg>
            <span>Profil</span>
        </a>
        @else
        <form method="POST" action="{{ route('logout') }}" class="mobile-nav-link" style="margin:0;padding:0;border:none;background:none;width:auto;" onsubmit="return confirm('Keluar dari aplikasi?')">
            @csrf
            <button type="submit" class="mobile-nav-btn-action" style="background:none;border:none;color:inherit;font:inherit;display:flex;flex-direction:column;align-items:center;cursor:pointer;width:100%;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 4.25A2.25 2.25 0 0 1 5.25 2h5.5A2.25 2.25 0 0 1 13 4.25v2a.75.75 0 0 1-1.5 0v-2a.75.75 0 0 0-.75-.75h-5.5a.75.75 0 0 0-.75.75v11.5c0 .414.336.75.75.75h5.5a.75.75 0 0 0 .75-.75v-2a.75.75 0 0 1 1.5 0v2A2.25 2.25 0 0 1 10.75 18h-5.5A2.25 2.25 0 0 1 3 15.75V4.25Z" clip-rule="evenodd"/><path fill-rule="evenodd" d="M19 10a.75.75 0 0 0-.75-.75H8.704l1.048-1.08a.75.75 0 1 0-1.004-1.11l-2.5 2.567a.75.75 0 0 0 0 1.086l2.5 2.567a.75.75 0 1 0 1.004-1.11l-1.048-1.08h9.546A.75.75 0 0 0 19 10Z" clip-rule="evenodd"/></svg>
                <span>Keluar</span>
            </button>
        </form>
        @endif
    </nav>
</div>

<!-- Lightbox foto bukti -->
<div class="lightbox" id="lightbox" hidden>
    <button type="button" class="lightbox-close" aria-label="Tutup">&times;</button>
    <img src="" alt="Foto bukti meter" id="lightbox-img">
    <p id="lightbox-caption"></p>
</div>
@endsection

@push('scripts')
<script>
    const flash = document.querySelector('.alert');
    if (flash) setTimeout(() => { flash.style.opacity = '0'; flash.style.transform = 'translateY(-10px)'; setTimeout(() => flash.remove(), 400); }, 4000);

    // Lightbox
    const lb = document.getElementById('lightbox');
    document.addEventListener('click', e => {
        const t = e.target.closest('[data-lightbox]');
        if (t) {
            e.preventDefault();
            document.getElementById('lightbox-img').src = t.dataset.lightbox;
            document.getElementById('lightbox-caption').textContent = t.dataset.caption || '';
            lb.hidden = false;
        } else if (e.target === lb || e.target.classList.contains('lightbox-close')) {
            lb.hidden = true;
        }
    });
    document.addEventListener('keydown', e => { if (e.key === 'Escape') lb.hidden = true; });
</script>
@endpush
