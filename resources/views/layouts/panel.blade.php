@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/logsheet.css') }}?v={{ file_exists(public_path('css/logsheet.css')) ? filemtime(public_path('css/logsheet.css')) : time() }}">
@endpush

@section('content')
@php
    $authUser = auth()->user();
    $isTeknisi = $authUser->role === 'teknisi';
    $isGm = in_array($authUser->role, ['gm', 'general_manager'], true);
    $canManage = in_array($authUser->role, ['admin', 'eng_admin', 'eng_spv'], true);
    $roleDisplayNames = [
        'admin'     => 'Super Admin',
        'superadmin'=> 'Super Admin',
        'user'      => 'User',
        'teknisi'   => 'Teknisi',
        'eng_admin' => 'Eng Admin',
        'eng_spv'   => 'Eng Spv',
        'gm'        => 'General Manager',
    ];
    $currentRoleLabel = $roleDisplayNames[$authUser->role] ?? ucfirst($authUser->role);
@endphp
<div class="dashboard-wrapper">

    <!-- Mobile Topbar for HP & PWA -->
    <header class="mobile-topbar">
        <div class="mobile-topbar-brand">
            <button type="button" class="mobile-sidebar-toggle" id="mobileSidebarToggle" onclick="window.openMobileSidebar ? window.openMobileSidebar() : null" aria-label="Buka Menu Navigasi" title="Menu Navigasi">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="3" y1="12" x2="21" y2="12"></line>
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <line x1="3" y1="18" x2="21" y2="18"></line>
                </svg>
            </button>
            <div class="logo-icon logo-icon--xs">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M11.47 3.841a.75.75 0 0 1 1.06 0l8.69 8.69a.75.75 0 1 0 1.06-1.061l-8.689-8.69a2.25 2.25 0 0 0-3.182 0l-8.69 8.69a.75.75 0 1 0 1.061 1.06l8.69-8.689Z"/>
                    <path d="m12 5.432 8.159 8.159c.03.03.06.058.091.086v6.198c0 1.035-.84 1.875-1.875 1.875H15a.75.75 0 0 1-.75-.75v-4.5a.75.75 0 0 0-.75-.75h-3a.75.75 0 0 0-.75.75V21a.75.75 0 0 1-.75.75H5.625a1.875 1.875 0 0 1-1.875-1.875v-6.198a2.29 2.29 0 0 0 .091-.086L12 5.432Z"/>
                </svg>
            </div>
            <div class="mobile-topbar-info">
                <div style="display:flex; align-items:center; gap:6px;">
                    <span class="mobile-brand-title">KPR Beta</span>
                    <span class="badge-role-pill">{{ $currentRoleLabel }}</span>
                </div>
                <div class="mobile-topbar-user" style="font-size:0.75rem; color:var(--text-secondary); font-weight:500; margin-top:2px;">
                    <span style="color:var(--text-muted);">Akun:</span> <strong style="color:var(--accent-light);">{{ $authUser->name }}</strong>
                </div>
            </div>
        </div>

        <div class="mobile-topbar-actions">
            <!-- Mobile Theme Switcher Button -->
            <button type="button" class="theme-toggle-btn theme-toggle-btn--mobile" onclick="toggleKprTheme()" aria-label="Ganti Mode Tema (Terang / Gelap)" title="Ganti Tema">
                <span class="theme-icon-dark" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M7.455 2.004a.75.75 0 0 1 .26.77 7 7 0 0 0 9.958 7.967.75.75 0 0 1 1.067.853A8.5 8.5 0 1 1 6.647 1.921a.75.75 0 0 1 .808.083Z" clip-rule="evenodd"/>
                    </svg>
                </span>
                <span class="theme-icon-light" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10 2a.75.75 0 0 1 .75.75v1.5a.75.75 0 0 1-1.5 0v-1.5A.75.75 0 0 1 10 2ZM10 15a.75.75 0 0 1 .75.75v1.5a.75.75 0 0 1-1.5 0v-1.5A.75.75 0 0 1 10 15ZM10 7a3 3 0 1 0 0 6 3 3 0 0 0 0-6ZM15.657 5.404a.75.75 0 1 0-1.06-1.06l-1.061 1.06a.75.75 0 0 0 1.06 1.06l1.06-1.06ZM6.464 14.596a.75.75 0 1 0-1.06-1.06l-1.06 1.06a.75.75 0 0 0 1.06 1.06l1.06-1.06ZM18 10a.75.75 0 0 1-.75.75h-1.5a.75.75 0 0 1 0-1.5h1.5A.75.75 0 0 1 18 10ZM5 10a.75.75 0 0 1-.75.75h-1.5a.75.75 0 0 1 0-1.5h1.5A.75.75 0 0 1 5 10ZM14.596 15.657a.75.75 0 0 0 1.06-1.06l-1.06-1.061a.75.75 0 1 0-1.06 1.06l1.06 1.06ZM5.404 6.464a.75.75 0 0 0 1.06-1.06L5.404 4.343a.75.75 0 1 0-1.06 1.06l1.06 1.06Z"/>
                    </svg>
                </span>
            </button>

            <!-- User Avatar with Dropdown (Posisi Kanan Atas) -->
            <div class="user-dropdown-container">
                <button type="button" class="user-avatar-btn" id="mobileUserBtn" onclick="window.toggleMobileUserMenu ? window.toggleMobileUserMenu(event) : null" aria-expanded="false" aria-haspopup="true" title="Menu Akun">
                    <div class="user-avatar-img">
                        @if($authUser->avatar_url)
                            <img src="{{ $authUser->avatar_url }}" alt="{{ $authUser->name }}">
                        @else
                            <span>{{ strtoupper(substr($authUser->name, 0, 1)) }}</span>
                        @endif
                    </div>
                    <svg class="dropdown-chevron" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd"/>
                    </svg>
                </button>

                <!-- Mobile Dropdown Menu -->
                <div class="user-dropdown-menu" id="mobileUserMenu" role="menu">
                    <div class="user-dropdown-header">
                        <div class="user-dropdown-avatar">
                            @if($authUser->avatar_url)
                                <img src="{{ $authUser->avatar_url }}" alt="{{ $authUser->name }}">
                            @else
                                <span>{{ strtoupper(substr($authUser->name, 0, 1)) }}</span>
                            @endif
                        </div>
                        <div class="user-dropdown-info">
                            <div class="user-dropdown-name">{{ $authUser->name }}</div>
                            <div class="user-dropdown-role">{{ $currentRoleLabel }} · {{ '@'.$authUser->username }}</div>
                        </div>
                    </div>

                    <div class="user-dropdown-divider"></div>

                    <a href="{{ route('profile.edit') }}" class="user-dropdown-item" role="menuitem">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-5.5-2.5a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0ZM10 12a5.99 5.99 0 0 0-4.793 2.39A6.483 6.483 0 0 0 10 16.5a6.483 6.483 0 0 0 4.793-2.11A5.99 5.99 0 0 0 10 12Z" clip-rule="evenodd"/></svg>
                        <span>Pengaturan Profil</span>
                    </a>

                    @if($isGm || $authUser->isSuperAdmin())
                        <a href="{{ route('gm.monitoring') }}" class="user-dropdown-item" role="menuitem">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="color:#facc15;"><path d="M2 11a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v5a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1v-5Zm6-4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v9a1 1 0 0 1-1 1H9a1 1 0 0 1-1-1V7Zm6-3a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1h-2a1 1 0 0 1-1-1V4Z"/></svg>
                            <span>Executive Summary</span>
                        </a>
                    @endif

                    @if($authUser->isSuperAdmin())
                        <div class="user-dropdown-divider"></div>
                        <a href="{{ route('admin.whatsapp.settings') }}" class="user-dropdown-item" role="menuitem">
                            <span style="color:#22c55e; font-size:1.05rem; line-height:1;">📱</span>
                            <span style="font-weight:600; color:var(--text-primary);">Pengaturan WhatsApp</span>
                        </a>
                        <div class="user-dropdown-divider"></div>
                        <div style="padding:6px 16px 2px; font-size:0.68rem; font-weight:700; color:var(--warning); text-transform:uppercase; letter-spacing:0.04em;">Input Manual (Super Admin)</div>
                        <a href="{{ route('logsheet.manual.listrik') }}" class="user-dropdown-item" role="menuitem">
                            <span style="color:var(--warning); font-size:1.05rem; line-height:1;">⚡</span>
                            <span>Input Manual Listrik</span>
                        </a>
                        <a href="{{ route('logsheet.manual.air') }}" class="user-dropdown-item" role="menuitem">
                            <span style="color:var(--info); font-size:1.05rem; line-height:1;">💧</span>
                            <span>Input Manual Air</span>
                        </a>
                        <a href="{{ route('logsheet.bulk.air') }}" class="user-dropdown-item" role="menuitem">
                            <span style="color:#38bdf8; font-size:1.05rem; line-height:1;">📤</span>
                            <span>Bulk Air Gedung (Excel)</span>
                        </a>
                        <a href="{{ route('public-area.manual.water') }}" class="user-dropdown-item" role="menuitem">
                            <span style="color:var(--info); font-size:1.05rem; line-height:1;">💧</span>
                            <span>Manual Water PA</span>
                        </a>
                        <a href="{{ route('public-area.bulk.water') }}" class="user-dropdown-item" role="menuitem">
                            <span style="color:#34d399; font-size:1.05rem; line-height:1;">📤</span>
                            <span>Bulk Water PA (Excel)</span>
                        </a>
                        <a href="{{ route('public-area.manual.lvsb') }}" class="user-dropdown-item" role="menuitem">
                            <span style="color:var(--warning); font-size:1.05rem; line-height:1;">⚡</span>
                            <span>Manual LVSB</span>
                        </a>
                        <a href="{{ route('public-area.manual.mvsb') }}" class="user-dropdown-item" role="menuitem">
                            <span style="color:var(--purple); font-size:1.05rem; line-height:1;">⚡</span>
                            <span>Manual MVSB 20kV</span>
                        </a>
                    @endif

                    <div class="user-dropdown-divider"></div>

                    <form method="POST" action="{{ route('logout') }}" onsubmit="return confirm('Apakah Anda yakin ingin keluar?')">
                        @csrf
                        <button type="submit" class="user-dropdown-item user-dropdown-item--danger" role="menuitem">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 4.25A2.25 2.25 0 0 1 5.25 2h5.5A2.25 2.25 0 0 1 13 4.25v2a.75.75 0 0 1-1.5 0v-2a.75.75 0 0 0-.75-.75h-5.5a.75.75 0 0 0-.75.75v11.5c0 .414.336.75.75.75h5.5a.75.75 0 0 0 .75-.75v-2a.75.75 0 0 1 1.5 0v2A2.25 2.25 0 0 1 10.75 18h-5.5A2.25 2.25 0 0 1 3 15.75V4.25Z" clip-rule="evenodd"/><path fill-rule="evenodd" d="M19 10a.75.75 0 0 0-.75-.75H8.704l1.048-1.08a.75.75 0 1 0-1.004-1.11l-2.5 2.567a.75.75 0 0 0 0 1.086l2.5 2.567a.75.75 0 1 0 1.004-1.11l-1.048-1.08h9.546A.75.75 0 0 0 19 10Z" clip-rule="evenodd"/></svg>
                            <span>Keluar / Logout</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <!-- Sidebar Backdrop for Mobile Drawer -->
    <div class="sidebar-backdrop" id="sidebarBackdrop" onclick="window.closeMobileSidebar ? window.closeMobileSidebar() : null" aria-hidden="true"></div>

    <!-- Sidebar Desktop & Mobile Off-Canvas -->
    <aside class="sidebar" id="mainSidebar">
        <div class="sidebar-logo">
            <div style="display:flex; align-items:center; gap:12px; flex:1; min-width:0;">
                <div class="logo-icon logo-icon--sm">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M11.47 3.841a.75.75 0 0 1 1.06 0l8.69 8.69a.75.75 0 1 0 1.06-1.061l-8.689-8.69a2.25 2.25 0 0 0-3.182 0l-8.69 8.69a.75.75 0 1 0 1.061 1.06l8.69-8.689Z"/>
                        <path d="m12 5.432 8.159 8.159c.03.03.06.058.091.086v6.198c0 1.035-.84 1.875-1.875 1.875H15a.75.75 0 0 1-.75-.75v-4.5a.75.75 0 0 0-.75-.75h-3a.75.75 0 0 0-.75.75V21a.75.75 0 0 1-.75.75H5.625a1.875 1.875 0 0 1-1.875-1.875v-6.198a2.29 2.29 0 0 0 .091-.086L12 5.432Z"/>
                    </svg>
                </div>
                <div style="overflow:hidden;">
                    <span style="font-size:1.15rem; font-family:var(--font-serif); font-weight:600; color:var(--accent-light); letter-spacing:0.03em; display:block; white-space:nowrap; text-overflow:ellipsis;">KPR Beta</span>
                    <div style="font-size:0.7rem; color:var(--accent); font-weight:600; text-transform:uppercase; letter-spacing:0.06em; white-space:nowrap; text-overflow:ellipsis;">Role {{ $currentRoleLabel }}</div>
                </div>
            </div>
            <button type="button" class="sidebar-close-btn" id="sidebarCloseBtn" onclick="window.closeMobileSidebar ? window.closeMobileSidebar() : null" aria-label="Tutup Menu">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z"/>
                </svg>
            </button>
        </div>

        <nav class="sidebar-nav">
            @if(! $isTeknisi)
            <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'nav-item--active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9.293 2.293a1 1 0 0 1 1.414 0l7 7A1 1 0 0 1 17 11h-1v6a1 1 0 0 1-1 1h-2a1 1 0 0 1-1-1v-3a1 1 0 0 0-1-1H9a1 1 0 0 0-1 1v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-6H3a1 1 0 0 1-.707-1.707l7-7Z" clip-rule="evenodd"/></svg>
                Dashboard
            </a>
            @endif

            @if($isGm || $authUser->isSuperAdmin())
            <a href="{{ route('gm.monitoring') }}" class="nav-item {{ request()->routeIs('gm.monitoring') ? 'nav-item--active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="color:#facc15;"><path d="M2 11a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v5a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1v-5Zm6-4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v9a1 1 0 0 1-1 1H9a1 1 0 0 1-1-1V7Zm6-3a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1h-2a1 1 0 0 1-1-1V4Z"/></svg>
                Executive Summary
            </a>
            @endif

            {{-- Menu Input Logsheet --}}
            <div class="sidebar-section-title" style="padding:10px 12px 4px; font-size:0.7rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.06em;">
                Logsheet
            </div>

            @if(! $isGm)
            <a href="{{ route('logsheet.hub') }}" class="nav-item {{ request()->routeIs('logsheet.hub', 'logsheet.create.listrik', 'logsheet.create.air') ? 'nav-item--active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="color:var(--accent-light);"><path d="M5.433 13.917l1.262-3.155A4 4 0 0 1 7.58 9.42l6.92-6.918a2.121 2.121 0 0 1 3 3l-6.92 6.918c-.383.383-.84.685-1.343.886l-3.154 1.262a.5.5 0 0 1-.65-.65Z"/><path d="M3.5 5.75c0-.69.56-1.25 1.25-1.25H10A.75.75 0 0 0 10 3H4.75A2.75 2.75 0 0 0 2 5.75v9.5A2.75 2.75 0 0 0 4.75 18h9.5A2.75 2.75 0 0 0 17 15.25V10a.75.75 0 0 0-1.5 0v5.25c0 .69-.56 1.25-1.25 1.25h-9.5c-.69 0-1.25-.56-1.25-1.25v-9.5Z"/></svg>
                Input Logsheet
            </a>
            @endif

            @if($canManage)
            <a href="{{ route('logsheet.create') }}" class="nav-item {{ request()->routeIs('logsheet.create') && !request()->routeIs('logsheet.create.listrik') && !request()->routeIs('logsheet.create.air') ? 'nav-item--active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M10.75 4.75a.75.75 0 0 0-1.5 0v4.5h-4.5a.75.75 0 0 0 0 1.5h4.5v4.5a.75.75 0 0 0 1.5 0v-4.5h4.5a.75.75 0 0 0 0-1.5h-4.5v-4.5Z"/></svg>
                Input Sekaligus (Admin)
            </a>
            @endif

            <a href="{{ route('logsheet.index') }}" class="nav-item {{ request()->routeIs('logsheet.index', 'logsheet.show', 'logsheet.edit') ? 'nav-item--active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.5 2A1.5 1.5 0 0 0 3 3.5v13A1.5 1.5 0 0 0 4.5 18h11a1.5 1.5 0 0 0 1.5-1.5V7.621a1.5 1.5 0 0 0-.44-1.06l-4.12-4.122A1.5 1.5 0 0 0 11.378 2H4.5Zm2.25 8.5a.75.75 0 0 0 0 1.5h6.5a.75.75 0 0 0 0-1.5h-6.5Zm0 3a.75.75 0 0 0 0 1.5h6.5a.75.75 0 0 0 0-1.5h-6.5Z" clip-rule="evenodd"/></svg>
                Riwayat Logsheet
            </a>

            {{-- Menu Logsheet Public Area --}}
            <div class="sidebar-section-title" style="padding:14px 12px 4px; font-size:0.7rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.06em; display:flex; align-items:center; gap:6px;">
                <span>Logsheet Public Area</span>
            </div>

            <a href="{{ route('public-area.index') }}" class="nav-item {{ request()->routeIs('public-area.index') ? 'nav-item--active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="color:var(--accent-light);"><path fill-rule="evenodd" d="M9.293 2.293a1 1 0 0 1 1.414 0l7 7A1 1 0 0 1 17 11h-1v6a1 1 0 0 1-1 1h-2a1 1 0 0 1-1-1v-3a1 1 0 0 0-1-1H9a1 1 0 0 0-1 1v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-6H3a1 1 0 0 1-.707-1.707l7-7Z" clip-rule="evenodd"/></svg>
                Menu Public Area
            </a>

            <a href="{{ route('public-area.water.index') }}" class="nav-item {{ request()->routeIs('public-area.water.*') ? 'nav-item--active' : '' }}" style="padding-left:24px;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="color:var(--info); width:16px; height:16px;"><path fill-rule="evenodd" d="M10 2.5c.3 0 .58.14.77.37C12.4 4.87 16 9.45 16 12.5a6 6 0 0 1-12 0c0-3.05 3.6-7.63 5.23-9.63A1 1 0 0 1 10 2.5Z" clip-rule="evenodd"/></svg>
                Water Meter Public Area
            </a>

            <a href="{{ route('public-area.lvsb.index') }}" class="nav-item {{ request()->routeIs('public-area.lvsb.*', 'public-area.electricity.*') ? 'nav-item--active' : '' }}" style="padding-left:24px;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="color:var(--warning); width:16px; height:16px;"><path d="M11.983 1.907a.75.75 0 0 0-1.292-.657l-8.5 9.5A.75.75 0 0 0 2.75 12h6.572l-1.305 6.093a.75.75 0 0 0 1.292.657l8.5-9.5A.75.75 0 0 0 17.25 8h-6.572l1.305-6.093Z"/></svg>
                LVSB
            </a>

            <a href="{{ route('public-area.mvsb.index') }}" class="nav-item {{ request()->routeIs('public-area.mvsb.*') ? 'nav-item--active' : '' }}" style="padding-left:24px;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="color:var(--purple); width:16px; height:16px;"><path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0 1 12 2v5h4a1 1 0 0 1 .82 1.573l-7 10A1 1 0 0 1 8 18v-5H4a1 1 0 0 1-.82-1.573l7-10a1 1 0 0 1 1.12-.381Z" clip-rule="evenodd"/></svg>
                MVSB
            </a>

            @if($authUser->isSuperAdmin())
            {{-- Menu Khusus Super Admin: Notifikasi Otomatis WhatsApp Pulse --}}
            <div class="sidebar-section-title" style="padding:14px 12px 4px; font-size:0.7rem; font-weight:700; color:#22c55e; text-transform:uppercase; letter-spacing:0.06em; display:flex; align-items:center; gap:6px;">
                <span>WhatsApp Gateway</span>
            </div>

            <a href="{{ route('admin.whatsapp.settings') }}" class="nav-item {{ request()->routeIs('admin.whatsapp.*') ? 'nav-item--active' : '' }}">
                <svg viewBox="0 0 24 24" fill="currentColor" style="width:18px; height:18px; color:#22c55e;"><path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0 0 12.04 2m.01 1.67c2.2 0 4.26.86 5.82 2.42a8.225 8.225 0 0 1 2.41 5.83c0 4.54-3.7 8.24-8.24 8.24-1.48 0-2.93-.4-4.2-1.15l-.3-.18-3.12.82.83-3.04-.2-.31a8.196 8.196 0 0 1-1.26-4.38c0-4.54 3.7-8.24 8.24-8.24m4.52 11.66c-.25-.13-1.47-.72-1.7-.81-.23-.08-.39-.13-.56.13-.17.25-.64.81-.79.97-.14.17-.29.19-.54.06-.25-.13-1.06-.39-2.02-1.24-.75-.67-1.26-1.5-1.4-1.75-.15-.25-.02-.39.11-.51.11-.11.25-.29.38-.44.13-.14.17-.25.25-.42.08-.17.04-.31-.02-.44-.06-.13-.56-1.35-.77-1.85-.2-.49-.41-.42-.56-.43h-.48c-.17 0-.44.06-.67.31-.23.25-.88.86-.88 2.1 0 1.24.9 2.44 1.03 2.61.13.17 1.77 2.7 4.29 3.79.6.26 1.07.41 1.43.53.6.19 1.15.16 1.58.1.48-.07 1.47-.6 1.68-1.18.21-.58.21-1.07.15-1.18-.07-.1-.23-.17-.48-.29"/></svg>
                Pengaturan WhatsApp
            </a>

            {{-- Menu Khusus Super Admin: Input Manual Tanggal Terlewat --}}
            <div class="sidebar-section-title" style="padding:14px 12px 4px; font-size:0.7rem; font-weight:700; color:var(--warning); text-transform:uppercase; letter-spacing:0.06em; display:flex; align-items:center; gap:6px;">
                <span>Input Manual (Super Admin)</span>
            </div>

            <a href="{{ route('logsheet.manual.listrik') }}" class="nav-item {{ request()->routeIs('logsheet.manual.listrik*') ? 'nav-item--active' : '' }}">
                <span style="display:inline-flex; width:18px; height:18px; margin-right:4px;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" style="width:18px; height:18px;">
                        <polygon points="32,3 61,32 32,61 3,32" fill="#FFC90E" stroke="#D32F2F" stroke-width="2.5"/>
                        <polygon points="36,11 23,33 31,33 26,53 43,28 34,28" fill="#D32F2F"/>
                    </svg>
                </span>
                Manual AMR PLN (Terlewat)
            </a>

            <a href="{{ route('logsheet.manual.air') }}" class="nav-item {{ request()->routeIs('logsheet.manual.air*') ? 'nav-item--active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="color:var(--info);"><path fill-rule="evenodd" d="M10 2.5c.3 0 .58.14.77.37C12.4 4.87 16 9.45 16 12.5a6 6 0 0 1-12 0c0-3.05 3.6-7.63 5.23-9.63A1 1 0 0 1 10 2.5Z" clip-rule="evenodd"/></svg>
                Manual Air (Terlewat)
            </a>

            <a href="{{ route('logsheet.bulk.air') }}" class="nav-item {{ request()->routeIs('logsheet.bulk.air*') ? 'nav-item--active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="color:#38bdf8;"><path fill-rule="evenodd" d="M10 17a.75.75 0 0 1-.75-.75V5.612L5.29 9.77a.75.75 0 0 1-1.08-1.04l5.25-5.5a.75.75 0 0 1 1.08 0l5.25 5.5a.75.75 0 1 1-1.08 1.04l-3.96-4.158V16.25A.75.75 0 0 1 10 17Z" clip-rule="evenodd"/></svg>
                Bulk Air Gedung (Excel)
            </a>

            <a href="{{ route('public-area.manual.water') }}" class="nav-item {{ request()->routeIs('public-area.manual.water*') ? 'nav-item--active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="color:var(--info);"><path fill-rule="evenodd" d="M10 2.5c.3 0 .58.14.77.37C12.4 4.87 16 9.45 16 12.5a6 6 0 0 1-12 0c0-3.05 3.6-7.63 5.23-9.63A1 1 0 0 1 10 2.5Z" clip-rule="evenodd"/></svg>
                Manual Water PA (Terlewat)
            </a>

            <a href="{{ route('public-area.bulk.water') }}" class="nav-item {{ request()->routeIs('public-area.bulk.water*') ? 'nav-item--active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="color:#34d399;"><path fill-rule="evenodd" d="M10 17a.75.75 0 0 1-.75-.75V5.612L5.29 9.77a.75.75 0 0 1-1.08-1.04l5.25-5.5a.75.75 0 0 1 1.08 0l5.25 5.5a.75.75 0 1 1-1.08 1.04l-3.96-4.158V16.25A.75.75 0 0 1 10 17Z" clip-rule="evenodd"/></svg>
                Bulk Water PA (Excel)
            </a>

            <a href="{{ route('public-area.manual.lvsb') }}" class="nav-item {{ request()->routeIs('public-area.manual.lvsb*') ? 'nav-item--active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="color:var(--warning);"><path d="M11.983 1.907a.75.75 0 0 0-1.292-.657l-8.5 9.5A.75.75 0 0 0 2.75 12h6.572l-1.305 6.093a.75.75 0 0 0 1.292.657l8.5-9.5A.75.75 0 0 0 17.25 8h-6.572l1.305-6.093Z"/></svg>
                Manual LVSB (Terlewat)
            </a>

            <a href="{{ route('public-area.manual.mvsb') }}" class="nav-item {{ request()->routeIs('public-area.manual.mvsb*') ? 'nav-item--active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="color:var(--purple);"><path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0 1 12 2v5h4a1 1 0 0 1 .82 1.573l-7 10A1 1 0 0 1 8 18v-5H4a1 1 0 0 1-.82-1.573l7-10a1 1 0 0 1 1.12-.381Z" clip-rule="evenodd"/></svg>
                Manual MVSB (Terlewat)
            </a>
            @endif

            {{-- Menu Daily Log Book (Pekerjaan Harian Teknisi) --}}
            <div class="sidebar-section-title" style="padding:14px 12px 4px; font-size:0.7rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.06em;">
                Daily Log Book
            </div>

            @if($isTeknisi || $canManage)
            <a href="{{ route('daily-log.create') }}" class="nav-item {{ request()->routeIs('daily-log.create') ? 'nav-item--active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="color:var(--success);"><path d="M10.75 4.75a.75.75 0 0 0-1.5 0v4.5h-4.5a.75.75 0 0 0 0 1.5h4.5v4.5a.75.75 0 0 0 1.5 0v-4.5h4.5a.75.75 0 0 0 0-1.5h-4.5v-4.5Z"/></svg>
                + Input Daily Log
            </a>
            @endif

            <a href="{{ route('daily-log.index') }}" class="nav-item {{ request()->routeIs('daily-log.index', 'daily-log.show', 'daily-log.edit') ? 'nav-item--active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="color:var(--info);"><path fill-rule="evenodd" d="M6 3.75A2.75 2.75 0 0 1 8.75 1h2.5A2.75 2.75 0 0 1 14 3.75v.443c.572.055 1.14.122 1.706.2 1.298.18 2.294 1.29 2.294 2.607v10.5A2.5 2.5 0 0 1 15.5 20h-11A2.5 2.5 0 0 1 2 17.5V7c0-1.317.996-2.427 2.294-2.607.566-.078 1.134-.145 1.706-.2V3.75ZM8.75 2.5c-.69 0-1.25.56-1.25 1.25v.25h5V3.75c0-.69-.56-1.25-1.25-1.25h-2.5ZM14.28 9.03a.75.75 0 0 0-1.06-1.06l-4.5 4.5-1.97-1.97a.75.75 0 1 0-1.06 1.06l2.5 2.5a.75.75 0 0 0 1.06 0l5.03-5.03Z" clip-rule="evenodd"/></svg>
                Riwayat Daily Log
            </a>

            <!-- Tombol install PWA di sidebar desktop -->
            <button type="button" class="nav-item pwa-install-btn" style="display:none; color:var(--info); margin-top:8px;">
                <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a.75.75 0 0 1 .75.75v7.69l2.22-2.22a.75.75 0 1 1 1.06 1.06l-3.5 3.5a.75.75 0 0 1-1.06 0l-3.5-3.5a.75.75 0 1 1 1.06-1.06l2.22 2.22V3.75A.75.75 0 0 1 10 3ZM3.75 14.5a.75.75 0 0 1 .75.75 3.25 3.25 0 0 0 3.25 3.25h4.5a3.25 3.25 0 0 0 3.25-3.25.75.75 0 0 1 1.5 0 4.75 4.75 0 0 1-4.75 4.75h-4.5A4.75 4.75 0 0 1 2.25 15.25a.75.75 0 0 1 .75-.75Z" clip-rule="evenodd"/></svg>
                Pasang PWA
            </button>
        </nav>

        <div class="sidebar-bottom">
            <button type="button" class="nav-item nav-item--theme-toggle" onclick="toggleKprTheme()" aria-label="Ganti Mode Tema">
                <span class="theme-icon-dark" aria-hidden="true" style="display:inline-flex;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width:18px;height:18px;"><path fill-rule="evenodd" d="M7.455 2.004a.75.75 0 0 1 .26.77 7 7 0 0 0 9.958 7.967.75.75 0 0 1 1.067.853A8.5 8.5 0 1 1 6.647 1.921a.75.75 0 0 1 .808.083Z" clip-rule="evenodd"/></svg>
                </span>
                <span class="theme-icon-light" aria-hidden="true" style="display:inline-flex;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width:18px;height:18px;"><path d="M10 2a.75.75 0 0 1 .75.75v1.5a.75.75 0 0 1-1.5 0v-1.5A.75.75 0 0 1 10 2ZM10 15a.75.75 0 0 1 .75.75v1.5a.75.75 0 0 1-1.5 0v-1.5A.75.75 0 0 1 10 15ZM10 7a3 3 0 1 0 0 6 3 3 0 0 0 0-6ZM15.657 5.404a.75.75 0 1 0-1.06-1.06l-1.061 1.06a.75.75 0 0 0 1.06 1.06l1.06-1.06ZM6.464 14.596a.75.75 0 1 0-1.06-1.06l-1.06 1.06a.75.75 0 0 0 1.06 1.06l1.06-1.06ZM18 10a.75.75 0 0 1-.75.75h-1.5a.75.75 0 0 1 0-1.5h1.5A.75.75 0 0 1 18 10ZM5 10a.75.75 0 0 1-.75.75h-1.5a.75.75 0 0 1 0-1.5h1.5A.75.75 0 0 1 5 10ZM14.596 15.657a.75.75 0 0 0 1.06-1.06l-1.06-1.061a.75.75 0 1 0-1.06 1.06l1.06 1.06ZM5.404 6.464a.75.75 0 0 0 1.06-1.06L5.404 4.343a.75.75 0 1 0-1.06 1.06l1.06 1.06Z"/></svg>
                </span>
                <span>Mode Tema</span>
            </button>
            <a href="{{ route('profile.edit') }}" class="nav-item {{ request()->routeIs('profile.*') ? 'nav-item--active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-5.5-2.5a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0ZM10 12a5.99 5.99 0 0 0-4.793 2.39A6.483 6.483 0 0 0 10 16.5a6.483 6.483 0 0 0 4.793-2.11A5.99 5.99 0 0 0 10 12Z" clip-rule="evenodd"/></svg>
                Pengaturan Akun
            </a>
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
                <!-- Desktop Theme Switcher Button -->
                <button type="button" class="theme-toggle-btn" onclick="toggleKprTheme()" aria-label="Ganti Mode Tema (Terang / Gelap)" title="Ganti Tema">
                    <span class="theme-icon-dark" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M7.455 2.004a.75.75 0 0 1 .26.77 7 7 0 0 0 9.958 7.967.75.75 0 0 1 1.067.853A8.5 8.5 0 1 1 6.647 1.921a.75.75 0 0 1 .808.083Z" clip-rule="evenodd"/>
                        </svg>
                    </span>
                    <span class="theme-icon-light" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M10 2a.75.75 0 0 1 .75.75v1.5a.75.75 0 0 1-1.5 0v-1.5A.75.75 0 0 1 10 2ZM10 15a.75.75 0 0 1 .75.75v1.5a.75.75 0 0 1-1.5 0v-1.5A.75.75 0 0 1 10 15ZM10 7a3 3 0 1 0 0 6 3 3 0 0 0 0-6ZM15.657 5.404a.75.75 0 1 0-1.06-1.06l-1.061 1.06a.75.75 0 0 0 1.06 1.06l1.06-1.06ZM6.464 14.596a.75.75 0 1 0-1.06-1.06l-1.06 1.06a.75.75 0 0 0 1.06 1.06l1.06-1.06ZM18 10a.75.75 0 0 1-.75.75h-1.5a.75.75 0 0 1 0-1.5h1.5A.75.75 0 0 1 18 10ZM5 10a.75.75 0 0 1-.75.75h-1.5a.75.75 0 0 1 0-1.5h1.5A.75.75 0 0 1 5 10ZM14.596 15.657a.75.75 0 0 0 1.06-1.06l-1.06-1.061a.75.75 0 1 0-1.06 1.06l1.06 1.06ZM5.404 6.464a.75.75 0 0 0 1.06-1.06L5.404 4.343a.75.75 0 1 0-1.06 1.06l1.06 1.06Z"/>
                        </svg>
                    </span>
                </button>

                <div class="user-dropdown-container">
                    <button type="button" class="user-badge user-badge--clickable" id="desktopUserBtn" aria-expanded="false" aria-haspopup="true" title="Menu Akun">
                        <div class="avatar" style="overflow:hidden; display:flex; align-items:center; justify-content:center;">
                            @if($authUser->avatar_url)
                                <img src="{{ $authUser->avatar_url }}" alt="{{ $authUser->name }}" style="width:100%; height:100%; object-fit:cover; border-radius:50%;">
                            @else
                                {{ strtoupper(substr($authUser->name, 0, 1)) }}
                            @endif
                        </div>
                        <div class="user-info">
                            <span class="user-name">{{ $authUser->name }}</span>
                            <span class="user-role">{{ $currentRoleLabel }} · {{ '@'.$authUser->username }}</span>
                        </div>
                        <svg class="dropdown-chevron" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd"/>
                        </svg>
                    </button>

                    <!-- Desktop Dropdown Menu -->
                    <div class="user-dropdown-menu" id="desktopUserMenu" role="menu">
                        <div class="user-dropdown-header">
                            <div class="user-dropdown-avatar">
                                @if($authUser->avatar_url)
                                    <img src="{{ $authUser->avatar_url }}" alt="{{ $authUser->name }}">
                                @else
                                    <span>{{ strtoupper(substr($authUser->name, 0, 1)) }}</span>
                                @endif
                            </div>
                            <div class="user-dropdown-info">
                                <div class="user-dropdown-name">{{ $authUser->name }}</div>
                                <div class="user-dropdown-role">{{ $currentRoleLabel }} · {{ '@'.$authUser->username }}</div>
                            </div>
                        </div>

                        <div class="user-dropdown-divider"></div>

                        <a href="{{ route('profile.edit') }}" class="user-dropdown-item" role="menuitem">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-5.5-2.5a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0ZM10 12a5.99 5.99 0 0 0-4.793 2.39A6.483 6.483 0 0 0 10 16.5a6.483 6.483 0 0 0 4.793-2.11A5.99 5.99 0 0 0 10 12Z" clip-rule="evenodd"/></svg>
                            <span>Pengaturan Profil</span>
                        </a>

                        @if($isGm || $authUser->isSuperAdmin())
                            <a href="{{ route('gm.monitoring') }}" class="user-dropdown-item" role="menuitem">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="color:#facc15;"><path d="M2 11a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v5a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1v-5Zm6-4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v9a1 1 0 0 1-1 1H9a1 1 0 0 1-1-1V7Zm6-3a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1h-2a1 1 0 0 1-1-1V4Z"/></svg>
                                <span>Executive Summary</span>
                            </a>
                        @endif

                        @if($authUser->isSuperAdmin())
                            <div class="user-dropdown-divider"></div>
                            <a href="{{ route('admin.whatsapp.settings') }}" class="user-dropdown-item" role="menuitem">
                                <span style="color:#22c55e; font-size:1.05rem; line-height:1;">📱</span>
                                <span style="font-weight:600; color:var(--text-primary);">Pengaturan WhatsApp</span>
                            </a>
                            <div class="user-dropdown-divider"></div>
                            <div style="padding:6px 16px 2px; font-size:0.68rem; font-weight:700; color:var(--warning); text-transform:uppercase; letter-spacing:0.04em;">Input Manual (Super Admin)</div>
                            <a href="{{ route('logsheet.manual.listrik') }}" class="user-dropdown-item" role="menuitem">
                                <span style="color:var(--warning); font-size:1.05rem; line-height:1;">⚡</span>
                                <span>Input Manual Listrik</span>
                            </a>
                            <a href="{{ route('logsheet.manual.air') }}" class="user-dropdown-item" role="menuitem">
                                <span style="color:var(--info); font-size:1.05rem; line-height:1;">💧</span>
                                <span>Input Manual Air</span>
                            </a>
                            <a href="{{ route('logsheet.bulk.air') }}" class="user-dropdown-item" role="menuitem">
                                <span style="color:#38bdf8; font-size:1.05rem; line-height:1;">📤</span>
                                <span>Bulk Air Gedung (Excel)</span>
                            </a>
                            <a href="{{ route('public-area.manual.water') }}" class="user-dropdown-item" role="menuitem">
                                <span style="color:var(--info); font-size:1.05rem; line-height:1;">💧</span>
                                <span>Manual Water PA</span>
                            </a>
                            <a href="{{ route('public-area.bulk.water') }}" class="user-dropdown-item" role="menuitem">
                                <span style="color:#34d399; font-size:1.05rem; line-height:1;">📤</span>
                                <span>Bulk Water PA (Excel)</span>
                            </a>
                            <a href="{{ route('public-area.manual.lvsb') }}" class="user-dropdown-item" role="menuitem">
                                <span style="color:var(--warning); font-size:1.05rem; line-height:1;">⚡</span>
                                <span>Manual LVSB</span>
                            </a>
                            <a href="{{ route('public-area.manual.mvsb') }}" class="user-dropdown-item" role="menuitem">
                                <span style="color:var(--purple); font-size:1.05rem; line-height:1;">⚡</span>
                                <span>Manual MVSB 20kV</span>
                            </a>
                        @endif

                        <div class="user-dropdown-divider"></div>

                        <form method="POST" action="{{ route('logout') }}" onsubmit="return confirm('Apakah Anda yakin ingin keluar?')">
                            @csrf
                            <button type="submit" class="user-dropdown-item user-dropdown-item--danger" role="menuitem">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 4.25A2.25 2.25 0 0 1 5.25 2h5.5A2.25 2.25 0 0 1 13 4.25v2a.75.75 0 0 1-1.5 0v-2a.75.75 0 0 0-.75-.75h-5.5a.75.75 0 0 0-.75.75v11.5c0 .414.336.75.75.75h5.5a.75.75 0 0 0 .75-.75v-2a.75.75 0 0 1 1.5 0v2A2.25 2.25 0 0 1 10.75 18h-5.5A2.25 2.25 0 0 1 3 15.75V4.25Z" clip-rule="evenodd"/><path fill-rule="evenodd" d="M19 10a.75.75 0 0 0-.75-.75H8.704l1.048-1.08a.75.75 0 1 0-1.004-1.11l-2.5 2.567a.75.75 0 0 0 0 1.086l2.5 2.567a.75.75 0 1 0 1.004-1.11l-1.048-1.08h9.546A.75.75 0 0 0 19 10Z" clip-rule="evenodd"/></svg>
                                <span>Keluar / Logout</span>
                            </button>
                        </form>
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

        @if (session('warning'))
            <div class="alert alert-warning" role="alert" style="background:rgba(245,158,11,0.15); border:1px solid rgba(245,158,11,0.3); color:#fbbf24;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495ZM10 5a.75.75 0 0 1 .75.75v3.5a.75.75 0 0 1-1.5 0v-3.5A.75.75 0 0 1 10 5Zm0 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd"/></svg>
                {{ session('warning') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger" role="alert" style="background:rgba(239,68,68,0.15); border:1px solid rgba(239,68,68,0.3); color:#f87171;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16ZM8.28 7.22a.75.75 0 0 0-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 1 0 1.06 1.06L10 11.06l1.72 1.72a.75.75 0 1 0 1.06-1.06L11.06 10l1.72-1.72a.75.75 0 0 0-1.06-1.06L10 8.94 8.28 7.22Z" clip-rule="evenodd"/></svg>
                {{ session('error') }}
            </div>
        @endif

        {{-- Container Banner Offline & Auto-Sync Khusus Role Teknisi --}}
        @if($isTeknisi)
            <div id="offline-sync-banner-container"></div>
        @endif

        @yield('panel')
    </main>

    <!-- Bottom Navigation Bar for HP / Mobile Screens -->
    <nav class="mobile-bottom-nav">
        @if(! $isTeknisi)
        {{-- Tab Dashboard (Khusus Non-Teknisi) --}}
        <a href="{{ route('dashboard') }}" class="mobile-nav-link {{ request()->routeIs('dashboard') ? 'mobile-nav-link--active' : '' }}">
            <div class="mobile-nav-icon-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9.293 2.293a1 1 0 0 1 1.414 0l7 7A1 1 0 0 1 17 11h-1v6a1 1 0 0 1-1 1h-2a1 1 0 0 1-1-1v-3a1 1 0 0 0-1-1H9a1 1 0 0 0-1 1v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-6H3a1 1 0 0 1-.707-1.707l7-7Z" clip-rule="evenodd"/></svg>
            </div>
            <span>Dashboard</span>
        </a>
        @endif

        @if($isGm)
        {{-- Tab Executive Summary (Khusus GM) --}}
        <a href="{{ route('gm.monitoring') }}" class="mobile-nav-link {{ request()->routeIs('gm.monitoring') ? 'mobile-nav-link--active' : '' }}">
            <div class="mobile-nav-icon-wrap" style="color:#facc15;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M2 11a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v5a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1v-5Zm6-4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v9a1 1 0 0 1-1 1H9a1 1 0 0 1-1-1V7Zm6-3a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1h-2a1 1 0 0 1-1-1V4Z"/></svg>
            </div>
            <span>Summary GM</span>
        </a>
        @else
        {{-- 1. TAB LOGSHEET (Membuka Pilihan Logsheet Listrik & Air) --}}
        <a href="{{ route('logsheet.hub') }}" class="mobile-nav-link {{ request()->routeIs('logsheet.hub', 'logsheet.create', 'logsheet.create.listrik', 'logsheet.create.air') ? 'mobile-nav-link--active' : '' }}">
            <div class="mobile-nav-icon-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                </svg>
            </div>
            <span>Logsheet</span>
        </a>
        @endif

        {{-- 2. TAB DAILY LOG BOOK (Pekerjaan Harian) --}}
        <a href="{{ route('daily-log.index') }}" class="mobile-nav-link {{ request()->routeIs('daily-log.*') ? 'mobile-nav-link--active' : '' }}">
            <div class="mobile-nav-icon-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
                    <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
                    <path d="M9 12l2 2 4-4"></path>
                </svg>
            </div>
            <span>Daily Log</span>
        </a>

        {{-- 2. TAB RIWAYAT --}}
        <a href="{{ route('logsheet.index') }}" class="mobile-nav-link {{ request()->routeIs('logsheet.index', 'logsheet.show', 'logsheet.edit') ? 'mobile-nav-link--active' : '' }}">
            <div class="mobile-nav-icon-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1-2.5-2.5Z"></path>
                    <path d="M6 6h10"></path>
                    <path d="M6 10h10"></path>
                </svg>
            </div>
            <span>Riwayat</span>
        </a>

        {{-- 3. TAB PROFIL (Untuk Semua Role) --}}
        <a href="{{ route('profile.edit') }}" class="mobile-nav-link {{ request()->routeIs('profile.*') ? 'mobile-nav-link--active' : '' }}">
            <div class="mobile-nav-icon-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
            </div>
            <span>Profil</span>
        </a>

        @if($isTeknisi)
        {{-- 4. TAB KELUAR (Khusus Layar HP Teknisi) --}}
        <form method="POST" action="{{ route('logout') }}" class="mobile-nav-link" style="margin:0;padding:0;border:none;background:none;width:auto;" onsubmit="return confirm('Keluar dari aplikasi?')">
            @csrf
            <button type="submit" class="mobile-nav-btn-action" style="background:none;border:none;color:inherit;font:inherit;display:flex;flex-direction:column;align-items:center;cursor:pointer;width:100%;">
                <div class="mobile-nav-icon-wrap">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                        <polyline points="16 17 21 12 16 7"></polyline>
                        <line x1="21" y1="12" x2="9" y2="12"></line>
                    </svg>
                </div>
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
(function() {
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

    // User Dropdown (Mobile & Desktop)
    function setupUserDropdown(triggerId, menuId) {
        const trigger = document.getElementById(triggerId);
        const menu = document.getElementById(menuId);
        if (!trigger || !menu) return;
        const container = trigger.closest('.user-dropdown-container');
        if (!container) return;

        trigger.addEventListener('click', function(e) {
            e.stopPropagation();
            const wasActive = container.classList.contains('is-active');
            // Close other dropdowns
            document.querySelectorAll('.user-dropdown-container').forEach(c => {
                c.classList.remove('is-active');
                const btn = c.querySelector('button');
                if (btn) btn.setAttribute('aria-expanded', 'false');
            });
            if (!wasActive) {
                container.classList.add('is-active');
                trigger.setAttribute('aria-expanded', 'true');
            }
        });
    }

    setupUserDropdown('mobileUserBtn', 'mobileUserMenu');
    setupUserDropdown('desktopUserBtn', 'desktopUserMenu');

    // Global toggle function for inline calls or touch events
    window.toggleMobileUserMenu = function(e) {
        if (e) e.stopPropagation();
        const btn = document.getElementById('mobileUserBtn');
        if (!btn) return;
        const container = btn.closest('.user-dropdown-container');
        if (!container) return;
        const wasActive = container.classList.contains('is-active');
        document.querySelectorAll('.user-dropdown-container').forEach(c => {
            c.classList.remove('is-active');
            const b = c.querySelector('button');
            if (b) b.setAttribute('aria-expanded', 'false');
        });
        if (!wasActive) {
            container.classList.add('is-active');
            btn.setAttribute('aria-expanded', 'true');
        }
    };

    // Close on outside click
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.user-dropdown-container')) {
            document.querySelectorAll('.user-dropdown-container').forEach(c => {
                c.classList.remove('is-active');
                const btn = c.querySelector('button');
                if (btn) btn.setAttribute('aria-expanded', 'false');
            });
        }
    });

    // Mobile Sidebar Drawer Toggle
    const mobileSidebarToggle = document.getElementById('mobileSidebarToggle');
    const sidebarCloseBtn = document.getElementById('sidebarCloseBtn');
    const sidebarBackdrop = document.getElementById('sidebarBackdrop');
    const mainSidebar = document.getElementById('mainSidebar');

    function openMobileSidebar() {
        const sb = document.getElementById('mainSidebar');
        const bd = document.getElementById('sidebarBackdrop');
        const tg = document.getElementById('mobileSidebarToggle');
        if (!sb) return;
        sb.classList.add('is-mobile-open');
        if (bd) bd.classList.add('is-active');
        document.body.classList.add('sidebar-open-locked');
        if (tg) tg.setAttribute('aria-expanded', 'true');
    }

    function closeMobileSidebar() {
        const sb = document.getElementById('mainSidebar');
        const bd = document.getElementById('sidebarBackdrop');
        const tg = document.getElementById('mobileSidebarToggle');
        if (!sb) return;
        sb.classList.remove('is-mobile-open');
        if (bd) bd.classList.remove('is-active');
        document.body.classList.remove('sidebar-open-locked');
        if (tg) tg.setAttribute('aria-expanded', 'false');
    }

    window.openMobileSidebar = openMobileSidebar;
    window.closeMobileSidebar = closeMobileSidebar;

    if (mobileSidebarToggle) mobileSidebarToggle.addEventListener('click', openMobileSidebar);
    if (sidebarCloseBtn) sidebarCloseBtn.addEventListener('click', closeMobileSidebar);
    if (sidebarBackdrop) sidebarBackdrop.addEventListener('click', closeMobileSidebar);

    if (mainSidebar) {
        mainSidebar.querySelectorAll('a.nav-item').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= 768) closeMobileSidebar();
            });
        });
    }

    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeMobileSidebar();
            document.querySelectorAll('.user-dropdown-container').forEach(c => {
                c.classList.remove('is-active');
                const btn = c.querySelector('button');
                if (btn) btn.setAttribute('aria-expanded', 'false');
            });
            if (typeof lb !== 'undefined' && lb) lb.hidden = true;
        }
    });
})();
</script>

@if($isTeknisi)
    <script src="{{ asset('js/offline-logsheet.js') }}?v={{ file_exists(public_path('js/offline-logsheet.js')) ? filemtime(public_path('js/offline-logsheet.js')) : time() }}"></script>
@endif
@endpush
