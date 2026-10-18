@extends('layouts.panel')

@section('title', 'Pengaturan Akun')
@section('page-title', 'Pengaturan Akun')
@section('page-subtitle', 'Kelola informasi pribadi dan keamanan akun Anda')

@section('panel')
@php
    $user = $user ?? auth()->user();
    $roleNames = [
        'admin'     => 'Administrator',
        'user'      => 'User',
        'teknisi'   => 'Teknisi',
        'eng_admin' => 'Eng Admin',
        'eng_spv'   => 'Eng Spv',
    ];
@endphp

<div class="profile-layout">

    {{-- ── Sidebar Profile ── --}}
    <aside class="profile-sidebar">
        <div class="profile-card">
            <div class="profile-avatar-wrap">
                @if($user->avatar_url)
                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="profile-avatar" style="width:72px; height:72px; border-radius:50%; object-fit:cover;">
                @else
                    <div class="profile-avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                @endif
                <div class="profile-avatar-ring"></div>
            </div>
            <div class="profile-meta">
                <p class="profile-meta-name">{{ $user->name }}</p>
                <p class="profile-meta-role">{{ $roleNames[$user->role] ?? ucfirst($user->role ?? 'user') }}</p>
                <span class="profile-meta-badge">{{ '@' . $user->username }}</span>
            </div>
        </div>

        <nav class="profile-nav">
            <a href="#info" class="profile-nav-item profile-nav-item--active" data-tab="info">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 9a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm-7 9a7 7 0 1 1 14 0H3Z" clip-rule="evenodd"/></svg>
                Informasi Akun
            </a>
            <a href="#photo" class="profile-nav-item" data-tab="photo">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M1 8a2 2 0 0 1 2-2h.93a2 2 0 0 0 1.664-.89l.812-1.22A2 2 0 0 1 8.07 3h3.86a2 2 0 0 1 1.664.89l.812 1.22A2 2 0 0 0 16.07 6H17a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8Zm13.5 3a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM10 14a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z" clip-rule="evenodd"/></svg>
                Foto Profil
            </a>
            <a href="#signature" class="profile-nav-item" data-tab="signature">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="m5.433 13.917 1.262-3.155A4 4 0 0 1 7.58 9.42l6.92-6.918a2.121 2.121 0 0 1 3 3l-6.92 6.918c-.383.383-.84.685-1.343.886l-3.154 1.262a.5.5 0 0 1-.65-.65Z"/><path d="M3.5 5.75c0-.414.336-.75.75-.75h4.5a.75.75 0 0 1 0 1.5h-4.5a.75.75 0 0 1-.75-.75Z"/></svg>
                Tanda Tangan Digital
            </a>
            <a href="#password" class="profile-nav-item" data-tab="password">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 1a4.5 4.5 0 0 0-4.5 4.5V9H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2h-.5V5.5A4.5 4.5 0 0 0 10 1Zm3 8V5.5a3 3 0 1 0-6 0V9h6Z" clip-rule="evenodd"/></svg>
                Ubah Password
            </a>
            @if(auth()->user()->isSuperAdmin())
            <a href="#signatures" class="profile-nav-item" data-tab="signatures">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 2a3 3 0 0 0-3 3v1H6a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-1V5a3 3 0 0 0-3-3Zm1 4V5a1 1 0 1 0-2 0v1h2Zm-3 6a1 1 0 1 1 2 0v2a1 1 0 1 1-2 0v-2Z" clip-rule="evenodd"/></svg>
                Reset Tanda Tangan
                <span class="nav-badge-admin" style="background:rgba(239,68,68,0.2);color:#f87171;">Superadmin</span>
            </a>
            <a href="#users" class="profile-nav-item" data-tab="users">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M11 5a3 3 0 1 1-6 0 3 3 0 0 1 6 0ZM2.046 15.253c-.18.295-.083.658.207.84A7.978 7.978 0 0 0 8 18c1.168 0 2.279-.25 3.279-.7a.75.75 0 0 0-.145-1.377 7.992 7.992 0 0 1-2.405.533 6.484 6.484 0 0 0 .25-.978.75.75 0 0 0-.467-.838A5.5 5.5 0 0 0 5.5 15c-.66 0-1.29.12-1.87.338a.75.75 0 0 0-.421.498 6.01 6.01 0 0 0-.163.417ZM15.5 2a3.5 3.5 0 1 0 0 7 3.5 3.5 0 0 0 0-7ZM13 9.5a3.986 3.986 0 0 0-1.928 3.42 6.5 6.5 0 0 1 4.428 0A3.988 3.988 0 0 0 13.5 9.5H13ZM11 13.5A5 5 0 0 1 21 13.5v.25a.75.75 0 0 1-.75.75h-8.5A.75.75 0 0 1 11 13.75v-.25Z"/></svg>
                Kelola Akun
                <span class="nav-badge-admin">Admin</span>
            </a>
            <a href="{{ route('admin.migrate') }}" class="profile-nav-item" onclick="return confirm('Jalankan migrasi database dan bersihkan cache?')">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M15.312 11.424a5.5 5.5 0 0 1-9.201 2.466l-.312-.311h2.433a.75.75 0 0 0 0-1.5H3.75a.75.75 0 0 0-.75.75v4.482a.75.75 0 0 0 1.5 0v-2.09l.343.342a7 7 0 1 0 1.258-7.904.75.75 0 1 0 1.085 1.034 5.5 5.5 0 0 1 8.126 2.781Z" clip-rule="evenodd"/></svg>
                Migrasi Database
                <span class="nav-badge-admin" style="background:rgba(56,189,248,0.2);color:#38bdf8;">Sync</span>
            </a>
            @endif
        </nav>
    </aside>

    {{-- ── Main Content ── --}}
    <div class="profile-main">

        {{-- Tab: Informasi Akun --}}
        <div class="profile-tab" id="tab-info">
            <div class="profile-section-card">
                <div class="profile-section-header">
                    <div class="profile-section-icon" style="background:rgba(99,102,241,0.15);color:var(--accent-light)">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 9a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm-7 9a7 7 0 1 1 14 0H3Z" clip-rule="evenodd"/></svg>
                    </div>
                    <div>
                        <h2 class="profile-section-title">Informasi Akun</h2>
                        <p class="profile-section-desc">Perbarui nama, username, dan email akun Anda.</p>
                    </div>
                </div>

                @if(session('success'))
                    <div class="alert alert-success" role="alert">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd"/></svg>
                        {{ session('success') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('profile.info') }}" class="profile-form">
                    @csrf
                    @method('PATCH')

                    <div class="form-row">
                        <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                            <label class="form-label">Nama Lengkap</label>
                            <div class="input-wrapper">
                                <span class="input-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M10 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6ZM3.465 14.493a1.23 1.23 0 0 0 .41 1.412A9.957 9.957 0 0 0 10 18c2.31 0 4.438-.784 6.131-2.1.43-.333.604-.903.408-1.41a7.002 7.002 0 0 0-13.074.003Z"/></svg>
                                </span>
                                <input type="text" name="name" class="form-input" value="{{ old('name', $user->name) }}" placeholder="Nama lengkap Anda" autocomplete="name">
                            </div>
                            @error('name')
                                <p class="form-error">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-8-5a.75.75 0 0 1 .75.75v4.5a.75.75 0 0 1-1.5 0v-4.5A.75.75 0 0 1 10 5Zm0 10a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd"/></svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div class="form-group {{ $errors->has('username') ? 'has-error' : '' }}">
                            <label class="form-label">Username</label>
                            <div class="input-wrapper">
                                <span class="input-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M1 8a7 7 0 1 1 14 0A7 7 0 0 1 1 8Zm7-4a1 1 0 0 0-1 1v3H4a1 1 0 0 0 0 2h3v3a1 1 0 1 0 2 0v-3h3a1 1 0 1 0 0-2H9V5a1 1 0 0 0-1-1Zm9 4a.75.75 0 0 1 .75.75v6.5a.75.75 0 0 1-1.5 0v-6.5A.75.75 0 0 1 17 8Z" clip-rule="evenodd"/></svg>
                                </span>
                                <input type="text" name="username" class="form-input" value="{{ old('username', $user->username) }}" placeholder="username_anda" autocomplete="username">
                            </div>
                            @error('username')
                                <p class="form-error">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-8-5a.75.75 0 0 1 .75.75v4.5a.75.75 0 0 1-1.5 0v-4.5A.75.75 0 0 1 10 5Zm0 10a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd"/></svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group {{ $errors->has('email') ? 'has-error' : '' }}">
                        <label class="form-label">Alamat Email</label>
                        <div class="input-wrapper">
                            <span class="input-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M3 4a2 2 0 0 0-2 2v1.161l8.441 4.221a1.25 1.25 0 0 0 1.118 0L19 7.161V6a2 2 0 0 0-2-2H3Z"/><path d="m19 8.839-7.77 3.885a2.75 2.75 0 0 1-2.46 0L1 8.839V14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V8.839Z"/></svg>
                            </span>
                            <input type="email" name="email" class="form-input" value="{{ old('email', $user->email) }}" placeholder="email@contoh.com" autocomplete="email">
                        </div>
                        @error('email')
                            <p class="form-error">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-8-5a.75.75 0 0 1 .75.75v4.5a.75.75 0 0 1-1.5 0v-4.5A.75.75 0 0 1 10 5Zm0 10a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd"/></svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div class="profile-form-footer">
                        <button type="submit" class="btn-profile-save">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd"/></svg>
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Tab: Foto Profil --}}
        <div class="profile-tab" id="tab-photo" hidden>
            <div class="profile-section-card">
                <div class="profile-section-header">
                    <div class="profile-section-icon" style="background:rgba(236,72,153,0.15);color:#f472b6">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M1 8a2 2 0 0 1 2-2h.93a2 2 0 0 0 1.664-.89l.812-1.22A2 2 0 0 1 8.07 3h3.86a2 2 0 0 1 1.664.89l.812 1.22A2 2 0 0 0 16.07 6H17a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8Zm13.5 3a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM10 14a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="profile-section-title">Foto Profil Akun</h2>
                        <p class="profile-section-desc">Unggah foto profil Anda agar mudah dikenali oleh seluruh anggota tim di aplikasi dan logsheet.</p>
                    </div>
                </div>

                <div class="avatar-manager-grid" style="display:flex; flex-direction:column; gap:20px; align-items:center; text-align:center; padding:12px 0;">
                    <div style="position:relative; width:120px; height:120px;">
                        <div id="avatarPreviewContainer" style="width:120px; height:120px; border-radius:50%; overflow:hidden; border:3px solid var(--accent); box-shadow:0 0 24px rgba(99,102,241,0.25); display:flex; align-items:center; justify-content:center; background:#1e293b;">
                            @if($user->avatar_url)
                                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" id="avatarPreviewImg" style="width:100%; height:100%; object-fit:cover;">
                            @else
                                <div id="avatarPreviewInitial" style="font-size:3rem; font-weight:800; color:#fff;">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <img src="" alt="" id="avatarPreviewImg" style="width:100%; height:100%; object-fit:cover; display:none;">
                            @endif
                        </div>
                    </div>

                    <div style="max-width:440px; width:100%;">
                        <h3 style="font-size:1.05rem; color:var(--text-primary); margin-bottom:4px;">{{ $user->name }}</h3>
                        <p style="font-size:0.83rem; color:var(--text-muted); margin-bottom:16px;">
                            Pilih file foto dari perangkat Anda (Maksimal 4 MB, format JPG, PNG, atau WEBP).
                        </p>

                        <form method="POST" action="{{ route('profile.photo.update') }}" enctype="multipart/form-data" id="photoForm" style="display:flex; flex-direction:column; gap:12px; align-items:center;">
                            @csrf
                            <input type="file" name="foto_profil" id="inputFotoProfil" accept="image/png,image/jpeg,image/jpg,image/webp" style="display:none;" onchange="handleAvatarSelected(this)">
                            
                            <div style="display:flex; gap:10px; flex-wrap:wrap; justify-content:center;">
                                <button type="button" class="btn" onclick="document.getElementById('inputFotoProfil').click()" style="background:rgba(255,255,255,0.06); border:1px solid var(--border);">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="16" height="16"><path fill-rule="evenodd" d="M1 8a2 2 0 0 1 2-2h.93a2 2 0 0 0 1.664-.89l.812-1.22A2 2 0 0 1 8.07 3h3.86a2 2 0 0 1 1.664.89l.812 1.22A2 2 0 0 0 16.07 6H17a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8Zm13.5 3a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM10 14a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z" clip-rule="evenodd"/></svg>
                                    Pilih Foto
                                </button>
                                <button type="submit" id="btnSaveAvatar" class="btn btn-primary" style="display:none;">
                                    ✓ Simpan Foto
                                </button>
                            </div>
                            @error('foto_profil')
                                <p class="form-error" style="margin-top:6px;">{{ $message }}</p>
                            @enderror
                        </form>

                        @if($user->foto_profil)
                            <form method="POST" action="{{ route('profile.photo.destroy') }}" onsubmit="return confirm('Hapus foto profil dan gunakan inisial huruf?')" style="margin-top:12px;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm" style="background:none; border:none; color:#ef4444; font-size:0.8rem; text-decoration:underline; cursor:pointer;">
                                    Hapus Foto Profil
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Tab: Tanda Tangan Digital --}}
        <div class="profile-tab" id="tab-signature" hidden>
            <div class="profile-section-card">
                <div class="profile-section-header">
                    <div class="profile-section-icon" style="background:rgba(56,189,248,0.15);color:#38bdf8">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path d="m5.433 13.917 1.262-3.155A4 4 0 0 1 7.58 9.42l6.92-6.918a2.121 2.121 0 0 1 3 3l-6.92 6.918c-.383.383-.84.685-1.343.886l-3.154 1.262a.5.5 0 0 1-.65-.65Z"/>
                            <path d="M3.5 5.75c0-.414.336-.75.75-.75h4.5a.75.75 0 0 1 0 1.5h-4.5a.75.75 0 0 1-.75-.75Z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="profile-section-title">Tanda Tangan Digital</h2>
                        <p class="profile-section-desc">Goreskan tanda tangan langsung di layar sentuh HP / komputer atau unggah file gambar tanda tangan Anda.</p>
                    </div>
                </div>

                @if($user->signature_url)
                    {{-- KONDISI 1: SUDAH ADA TANDA TANGAN (TERKUNCI SECARA PERMANEN) --}}
                    <div style="background:rgba(255,255,255,0.02); border:1px solid rgba(255,255,255,0.08); border-radius:14px; padding:22px; margin-bottom:20px;">
                        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px; flex-wrap:wrap; gap:10px;">
                            <div style="display:flex; align-items:center; gap:8px;">
                                <span class="badge badge-ok" style="font-size:0.8rem; padding:4px 10px;">✓ Tanda Tangan Aktif</span>
                                <span style="font-size:0.82rem; color:var(--text-muted);">Tersimpan pada akun Anda</span>
                            </div>
                            @if(auth()->user()->isSuperAdmin())
                            <form method="POST" action="{{ route('profile.signature.destroy') }}" onsubmit="return confirm('Apakah Anda yakin ingin mereset tanda tangan digital Anda sendiri? Anda harus menandatangani ulang setelahnya.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Reset Tanda Tangan Saya</button>
                            </form>
                            @endif
                        </div>

                        <div style="background:#ffffff; border-radius:12px; padding:20px; text-align:center; max-width:340px; margin:0 auto; box-shadow:0 4px 16px rgba(0,0,0,0.25); border:1px solid rgba(0,0,0,0.08);">
                            <img src="{{ $user->signature_url }}" alt="Tanda Tangan {{ $user->name }}" style="max-height:100px; max-width:100%; object-fit:contain;">
                        </div>

                        {{-- Kotak Peringatan & Status Terkunci --}}
                        <div style="background:rgba(16,185,129,0.06); border:1px solid rgba(16,185,129,0.25); border-radius:12px; padding:16px; margin-top:20px; display:flex; align-items:flex-start; gap:12px;">
                            <span style="font-size:1.5rem; line-height:1;">🔒</span>
                            <div>
                                <strong style="color:#34d399; font-size:0.92rem; display:block; margin-bottom:4px;">Tanda Tangan Digital Terkunci & Sah</strong>
                                <p style="font-size:0.84rem; color:var(--text-secondary); line-height:1.5; margin:0;">
                                    Tanda tangan digital Anda telah tersimpan dan <strong>tidak dapat diubah atau diedit secara mandiri</strong> untuk menjaga integritas dan keabsahan dokumen logsheet operasional.
                                    <br>
                                    Jika Anda harus mengganti atau memperbarui tanda tangan, <strong>silakan lapor terlebih dahulu ke role Superadmin</strong> untuk melakukan permohonan reset tanda tangan akun Anda.
                                </p>
                            </div>
                        </div>
                    </div>
                @else
                    {{-- KONDISI 2: BELUM ADA TANDA TANGAN (ATAU TELAH DI-RESET OLEH SUPERADMIN) --}}
                    <div style="background:rgba(245,158,11,0.08); border:1px dashed rgba(245,158,11,0.25); border-radius:12px; padding:16px; margin-bottom:24px; display:flex; align-items:flex-start; gap:12px;">
                        <span style="font-size:1.5rem; line-height:1;">✍️</span>
                        <div>
                            <strong style="color:#fbbf24; font-size:0.9rem; display:block; margin-bottom:3px;">Belum Ada Tanda Tangan Digital</strong>
                            <p style="font-size:0.82rem; color:var(--text-secondary); margin:0; line-height:1.45;">
                                Silakan goreskan tanda tangan Anda pada kanvas di bawah ini.
                                <strong style="color:#f8fafc;">Penting:</strong> Setelah Anda menekan tombol simpan, tanda tangan akan otomatis terkunci secara permanen dan tidak dapat diedit tanpa reset dari Superadmin.
                            </p>
                        </div>
                    </div>

                    {{-- Opsi 1: Gambar Tanda Tangan di Layar Kanvas --}}
                    <div style="margin-bottom:28px;">
                        <label class="form-label" style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px;">
                            <span>✍️ Goreskan Tanda Tangan (Touchscreen / Mouse):</span>
                            <small style="color:var(--text-muted); font-weight:normal;">Gunakan jari di layar HP</small>
                        </label>

                        <div style="border:2px dashed rgba(99,102,241,0.4); border-radius:14px; background:#ffffff; overflow:hidden; position:relative; box-shadow:0 4px 16px rgba(0,0,0,0.25);">
                            <canvas id="signatureCanvas" style="width:100%; height:180px; display:block; cursor:crosshair; touch-action:none;"></canvas>
                            <div id="signaturePlaceholder" style="position:absolute; inset:0; display:flex; align-items:center; justify-content:center; pointer-events:none; color:#94a3b8; font-size:0.95rem; font-style:italic;">
                                Goreskan tanda tangan di sini...
                            </div>
                        </div>

                        <div style="display:flex; align-items:center; justify-content:space-between; margin-top:12px; flex-wrap:wrap; gap:10px;">
                            <button type="button" class="btn btn-sm" id="btnClearSignature" style="background:rgba(255,255,255,0.06); border:1px solid var(--border); color:var(--text-secondary);">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="15" height="15"><path fill-rule="evenodd" d="M15.312 11.424a5.5 5.5 0 0 1-9.201 2.466l-.312-.311h2.433a.75.75 0 0 0 0-1.5H3.75a.75.75 0 0 0-.75.75v4.482a.75.75 0 0 0 1.5 0v-2.09l.343.342a7 7 0 1 0 1.258-7.904.75.75 0 1 0 1.085 1.034 5.5 5.5 0 0 1 8.126 2.781Z" clip-rule="evenodd"/></svg>
                                Ulangi / Bersihkan
                            </button>

                            <form method="POST" action="{{ route('profile.signature.update') }}" id="canvasSignatureForm">
                                @csrf
                                <input type="hidden" name="signature_data" id="signatureDataInput">
                                <button type="submit" class="btn btn-primary" id="btnSaveCanvasSignature" disabled>
                                    ✓ Simpan & Kunci Tanda Tangan
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Opsi 2: Upload File Tanda Tangan --}}
                    <div style="border-top:1px solid var(--border); padding-top:18px;">
                        <details style="cursor:pointer;">
                            <summary style="font-size:0.88rem; color:var(--text-secondary); font-weight:600; outline:none; user-select:none;">
                                Atau unggah file gambar tanda tangan (Scan / PNG Transparan)
                            </summary>
                            <form method="POST" action="{{ route('profile.signature.update') }}" enctype="multipart/form-data" style="margin-top:14px; display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
                                @csrf
                                <input type="file" name="signature_file" accept="image/png,image/jpeg,image/jpg,image/webp" class="form-input" style="max-width:300px; padding:7px 12px; font-size:0.85rem;" required>
                                <button type="submit" class="btn btn-primary btn-sm">Upload & Kunci Tanda Tangan</button>
                            </form>
                        </details>
                    </div>
                @endif
            </div>
        </div>

        {{-- Tab: Ubah Password --}}
        <div class="profile-tab" id="tab-password" hidden>
            <div class="profile-section-card">
                <div class="profile-section-header">
                    <div class="profile-section-icon" style="background:rgba(245,158,11,0.15);color:#fbbf24">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 1a4.5 4.5 0 0 0-4.5 4.5V9H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2h-.5V5.5A4.5 4.5 0 0 0 10 1Zm3 8V5.5a3 3 0 1 0-6 0V9h6Z" clip-rule="evenodd"/></svg>
                    </div>
                    <div>
                        <h2 class="profile-section-title">Ubah Password</h2>
                        <p class="profile-section-desc">Pastikan password baru Anda minimal 8 karakter.</p>
                    </div>
                </div>

                @if(session('success'))
                    <div class="alert alert-success" role="alert">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd"/></svg>
                        {{ session('success') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('profile.password') }}" class="profile-form">
                    @csrf
                    @method('PATCH')

                    <div class="form-group {{ $errors->has('current_password') ? 'has-error' : '' }}">
                        <label class="form-label">Password Saat Ini</label>
                        <div class="input-wrapper">
                            <span class="input-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 1a4.5 4.5 0 0 0-4.5 4.5V9H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2h-.5V5.5A4.5 4.5 0 0 0 10 1Zm3 8V5.5a3 3 0 1 0-6 0V9h6Z" clip-rule="evenodd"/></svg>
                            </span>
                            <input type="password" name="current_password" id="current_password" class="form-input" placeholder="••••••••" autocomplete="current-password">
                            <button type="button" class="toggle-password" data-target="current_password" aria-label="Tampilkan password">
                                <svg class="icon-eye" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M10 12.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z"/><path fill-rule="evenodd" d="M.664 10.59a1.651 1.651 0 0 1 0-1.186A10.004 10.004 0 0 1 10 3c4.257 0 7.893 2.66 9.336 6.41.147.381.146.804 0 1.186A10.004 10.004 0 0 1 10 17c-4.257 0-7.893-2.66-9.336-6.41ZM14 10a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z" clip-rule="evenodd"/></svg>
                            </button>
                        </div>
                        @error('current_password')
                            <p class="form-error">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-8-5a.75.75 0 0 1 .75.75v4.5a.75.75 0 0 1-1.5 0v-4.5A.75.75 0 0 1 10 5Zm0 10a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd"/></svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div class="form-row">
                        <div class="form-group {{ $errors->has('password') ? 'has-error' : '' }}">
                            <label class="form-label">Password Baru</label>
                            <div class="input-wrapper">
                                <span class="input-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8 7a5 5 0 1 1 3.61 4.804l-1.903 1.903A1 1 0 0 1 9 14H8v1a1 1 0 0 1-1 1H6v1a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1v-2a1 1 0 0 1 .293-.707L7.196 10.39A5.002 5.002 0 0 1 8 7Zm5-3a.75.75 0 0 0 0 1.5A1.5 1.5 0 0 1 14.5 7 .75.75 0 0 0 16 7a3 3 0 0 0-3-3Z" clip-rule="evenodd"/></svg>
                                </span>
                                <input type="password" name="password" id="new_password" class="form-input" placeholder="••••••••" autocomplete="new-password">
                                <button type="button" class="toggle-password" data-target="new_password" aria-label="Tampilkan password">
                                    <svg class="icon-eye" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M10 12.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z"/><path fill-rule="evenodd" d="M.664 10.59a1.651 1.651 0 0 1 0-1.186A10.004 10.004 0 0 1 10 3c4.257 0 7.893 2.66 9.336 6.41.147.381.146.804 0 1.186A10.004 10.004 0 0 1 10 17c-4.257 0-7.893-2.66-9.336-6.41ZM14 10a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z" clip-rule="evenodd"/></svg>
                                </button>
                            </div>
                            @error('password')
                                <p class="form-error">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-8-5a.75.75 0 0 1 .75.75v4.5a.75.75 0 0 1-1.5 0v-4.5A.75.75 0 0 1 10 5Zm0 10a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd"/></svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div class="form-group {{ $errors->has('password_confirmation') ? 'has-error' : '' }}">
                            <label class="form-label">Konfirmasi Password</label>
                            <div class="input-wrapper">
                                <span class="input-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd"/></svg>
                                </span>
                                <input type="password" name="password_confirmation" id="confirm_password" class="form-input" placeholder="••••••••" autocomplete="new-password">
                                <button type="button" class="toggle-password" data-target="confirm_password" aria-label="Tampilkan password">
                                    <svg class="icon-eye" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M10 12.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z"/><path fill-rule="evenodd" d="M.664 10.59a1.651 1.651 0 0 1 0-1.186A10.004 10.004 0 0 1 10 3c4.257 0 7.893 2.66 9.336 6.41.147.381.146.804 0 1.186A10.004 10.004 0 0 1 10 17c-4.257 0-7.893-2.66-9.336-6.41ZM14 10a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z" clip-rule="evenodd"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="password-strength" id="password-strength" hidden>
                        <div class="strength-bar">
                            <div class="strength-fill" id="strength-fill"></div>
                        </div>
                        <span class="strength-label" id="strength-label"></span>
                    </div>

                    <div class="profile-form-footer">
                        <button type="submit" class="btn-profile-save btn-profile-save--warning">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 1a4.5 4.5 0 0 0-4.5 4.5V9H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2h-.5V5.5A4.5 4.5 0 0 0 10 1Zm3 8V5.5a3 3 0 1 0-6 0V9h6Z" clip-rule="evenodd"/></svg>
                            Perbarui Password
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Tab: Buat Akun Baru (Admin only) --}}
        @if(auth()->user()->role === 'admin')
        <div class="profile-tab" id="tab-users" hidden>

            {{-- Form buat akun baru --}}
            <div class="profile-section-card">
                <div class="profile-section-header">
                    <div class="profile-section-icon" style="background:rgba(16,185,129,0.15);color:#34d399">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M11 5a3 3 0 1 1-6 0 3 3 0 0 1 6 0ZM2.046 15.253c-.18.295-.083.658.207.84A7.978 7.978 0 0 0 8 18c1.168 0 2.279-.25 3.279-.7a.75.75 0 0 0-.145-1.377 7.992 7.992 0 0 1-2.405.533 6.484 6.484 0 0 0 .25-.978.75.75 0 0 0-.467-.838A5.5 5.5 0 0 0 5.5 15c-.66 0-1.29.12-1.87.338a.75.75 0 0 0-.421.498 6.01 6.01 0 0 0-.163.417ZM15.5 2a3.5 3.5 0 1 0 0 7 3.5 3.5 0 0 0 0-7ZM13 9.5a3.986 3.986 0 0 0-1.928 3.42 6.5 6.5 0 0 1 4.428 0A3.988 3.988 0 0 0 13.5 9.5H13ZM11 13.5A5 5 0 0 1 21 13.5v.25a.75.75 0 0 1-.75.75h-8.5A.75.75 0 0 1 11 13.75v-.25Z"/></svg>
                    </div>
                    <div>
                        <h2 class="profile-section-title">Buat Akun Baru</h2>
                        <p class="profile-section-desc">Tambahkan pengguna baru ke dalam sistem.</p>
                    </div>
                </div>

                @if(session('success') && session('active_tab') === 'users')
                    <div class="alert alert-success" role="alert">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd"/></svg>
                        {{ session('success') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('profile.storeUser') }}" class="profile-form">
                    @csrf

                    <div class="form-row">
                        <div class="form-group {{ $errors->has('new_name') ? 'has-error' : '' }}">
                            <label class="form-label">Nama Lengkap</label>
                            <div class="input-wrapper">
                                <span class="input-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M10 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6ZM3.465 14.493a1.23 1.23 0 0 0 .41 1.412A9.957 9.957 0 0 0 10 18c2.31 0 4.438-.784 6.131-2.1.43-.333.604-.903.408-1.41a7.002 7.002 0 0 0-13.074.003Z"/></svg>
                                </span>
                                <input type="text" name="new_name" class="form-input" value="{{ old('new_name') }}" placeholder="Nama lengkap" autocomplete="off">
                            </div>
                            @error('new_name')
                                <p class="form-error"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-8-5a.75.75 0 0 1 .75.75v4.5a.75.75 0 0 1-1.5 0v-4.5A.75.75 0 0 1 10 5Zm0 10a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd"/></svg>{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group {{ $errors->has('new_username') ? 'has-error' : '' }}">
                            <label class="form-label">Username</label>
                            <div class="input-wrapper">
                                <span class="input-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M1 8a7 7 0 1 1 14 0A7 7 0 0 1 1 8Zm7-4a1 1 0 0 0-1 1v3H4a1 1 0 0 0 0 2h3v3a1 1 0 1 0 2 0v-3h3a1 1 0 1 0 0-2H9V5a1 1 0 0 0-1-1Zm9 4a.75.75 0 0 1 .75.75v6.5a.75.75 0 0 1-1.5 0v-6.5A.75.75 0 0 1 17 8Z" clip-rule="evenodd"/></svg>
                                </span>
                                <input type="text" name="new_username" class="form-input" value="{{ old('new_username') }}" placeholder="username_baru" autocomplete="off">
                            </div>
                            @error('new_username')
                                <p class="form-error"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-8-5a.75.75 0 0 1 .75.75v4.5a.75.75 0 0 1-1.5 0v-4.5A.75.75 0 0 1 10 5Zm0 10a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd"/></svg>{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group {{ $errors->has('new_email') ? 'has-error' : '' }}">
                            <label class="form-label">Alamat Email</label>
                            <div class="input-wrapper">
                                <span class="input-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M3 4a2 2 0 0 0-2 2v1.161l8.441 4.221a1.25 1.25 0 0 0 1.118 0L19 7.161V6a2 2 0 0 0-2-2H3Z"/><path d="m19 8.839-7.77 3.885a2.75 2.75 0 0 1-2.46 0L1 8.839V14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V8.839Z"/></svg>
                                </span>
                                <input type="email" name="new_email" class="form-input" value="{{ old('new_email') }}" placeholder="email@contoh.com" autocomplete="off">
                            </div>
                            @error('new_email')
                                <p class="form-error"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-8-5a.75.75 0 0 1 .75.75v4.5a.75.75 0 0 1-1.5 0v-4.5A.75.75 0 0 1 10 5Zm0 10a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd"/></svg>{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group {{ $errors->has('new_role') ? 'has-error' : '' }}">
                            <label class="form-label">Role</label>
                            <div class="input-wrapper">
                                <span class="input-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-5.5-2.5a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0ZM10 12a5.99 5.99 0 0 0-4.793 2.39A6.483 6.483 0 0 0 10 16.5a6.483 6.483 0 0 0 4.793-2.11A5.99 5.99 0 0 0 10 12Z" clip-rule="evenodd"/></svg>
                                </span>
                                <select name="new_role" class="form-input form-select">
                                    <option value="" disabled {{ old('new_role') ? '' : 'selected' }}>Pilih role...</option>
                                    <option value="user"      {{ old('new_role') === 'user'      ? 'selected' : '' }}>User (Staf Operasional)</option>
                                    <option value="teknisi"   {{ old('new_role') === 'teknisi'   ? 'selected' : '' }}>Teknisi (Hanya Input Logsheet / HP PWA)</option>
                                    <option value="eng_admin" {{ old('new_role') === 'eng_admin' ? 'selected' : '' }}>Eng Admin (Lihat, Edit & Verifikasi Logsheet)</option>
                                    <option value="eng_spv"   {{ old('new_role') === 'eng_spv'   ? 'selected' : '' }}>Eng Spv (Supervisor - Lihat, Edit & Verifikasi)</option>
                                    <option value="admin"     {{ old('new_role') === 'admin'     ? 'selected' : '' }}>Admin (Akses Penuh)</option>
                                </select>
                            </div>
                            @error('new_role')
                                <p class="form-error"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-8-5a.75.75 0 0 1 .75.75v4.5a.75.75 0 0 1-1.5 0v-4.5A.75.75 0 0 1 10 5Zm0 10a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd"/></svg>{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group {{ $errors->has('new_password') ? 'has-error' : '' }}">
                            <label class="form-label">Password</label>
                            <div class="input-wrapper">
                                <span class="input-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 1a4.5 4.5 0 0 0-4.5 4.5V9H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2h-.5V5.5A4.5 4.5 0 0 0 10 1Zm3 8V5.5a3 3 0 1 0-6 0V9h6Z" clip-rule="evenodd"/></svg>
                                </span>
                                <input type="password" name="new_password" id="create_password" class="form-input" placeholder="Min. 8 karakter" autocomplete="new-password">
                                <button type="button" class="toggle-password" data-target="create_password" aria-label="Tampilkan">
                                    <svg class="icon-eye" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M10 12.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z"/><path fill-rule="evenodd" d="M.664 10.59a1.651 1.651 0 0 1 0-1.186A10.004 10.004 0 0 1 10 3c4.257 0 7.893 2.66 9.336 6.41.147.381.146.804 0 1.186A10.004 10.004 0 0 1 10 17c-4.257 0-7.893-2.66-9.336-6.41ZM14 10a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z" clip-rule="evenodd"/></svg>
                                </button>
                            </div>
                            @error('new_password')
                                <p class="form-error"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-8-5a.75.75 0 0 1 .75.75v4.5a.75.75 0 0 1-1.5 0v-4.5A.75.75 0 0 1 10 5Zm0 10a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd"/></svg>{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Konfirmasi Password</label>
                            <div class="input-wrapper">
                                <span class="input-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd"/></svg>
                                </span>
                                <input type="password" name="new_password_confirmation" id="create_password_confirm" class="form-input" placeholder="Ulangi password" autocomplete="new-password">
                                <button type="button" class="toggle-password" data-target="create_password_confirm" aria-label="Tampilkan">
                                    <svg class="icon-eye" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M10 12.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z"/><path fill-rule="evenodd" d="M.664 10.59a1.651 1.651 0 0 1 0-1.186A10.004 10.004 0 0 1 10 3c4.257 0 7.893 2.66 9.336 6.41.147.381.146.804 0 1.186A10.004 10.004 0 0 1 10 17c-4.257 0-7.893-2.66-9.336-6.41ZM14 10a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z" clip-rule="evenodd"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="profile-form-footer">
                        <button type="submit" class="btn-profile-save btn-profile-save--green">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M11 5a3 3 0 1 1-6 0 3 3 0 0 1 6 0ZM2.046 15.253c-.18.295-.083.658.207.84A7.978 7.978 0 0 0 8 18c1.168 0 2.279-.25 3.279-.7a.75.75 0 0 0-.145-1.377 7.992 7.992 0 0 1-2.405.533 6.484 6.484 0 0 0 .25-.978.75.75 0 0 0-.467-.838A5.5 5.5 0 0 0 5.5 15c-.66 0-1.29.12-1.87.338a.75.75 0 0 0-.421.498 6.01 6.01 0 0 0-.163.417ZM15.5 2a3.5 3.5 0 1 0 0 7 3.5 3.5 0 0 0 0-7ZM13 9.5a3.986 3.986 0 0 0-1.928 3.42 6.5 6.5 0 0 1 4.428 0A3.988 3.988 0 0 0 13.5 9.5H13ZM11 13.5A5 5 0 0 1 21 13.5v.25a.75.75 0 0 1-.75.75h-8.5A.75.75 0 0 1 11 13.75v-.25Z"/></svg>
                            Buat Akun
                        </button>
                    </div>
                </form>
            </div>

            {{-- Daftar akun pengguna --}}
            <div class="profile-section-card">
                <div class="profile-section-header" style="margin-bottom:20px;padding-bottom:16px">
                    <div class="profile-section-icon" style="background:rgba(99,102,241,0.12);color:var(--accent-light)">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M10 9a3 3 0 1 0 0-6 3 3 0 0 0 0 6ZM6 8a2 2 0 1 1-4 0 2 2 0 0 1 4 0ZM1.49 15.326a.78.78 0 0 1-.358-.442 3 3 0 0 1 4.308-3.516 6.484 6.484 0 0 0-1.905 3.959c-.023.222-.014.442.025.654a4.97 4.97 0 0 1-2.07-.655ZM16.44 15.98a4.97 4.97 0 0 0 2.07-.654.78.78 0 0 0 .357-.442 3 3 0 0 0-4.308-3.517 6.484 6.484 0 0 1 1.907 3.96 2.32 2.32 0 0 1-.026.654ZM18 8a2 2 0 1 1-4 0 2 2 0 0 1 4 0ZM5.304 16.19a.844.844 0 0 1-.277-.71 5 5 0 0 1 9.947 0 .843.843 0 0 1-.277.71A6.975 6.975 0 0 1 10 18a6.974 6.974 0 0 1-4.696-1.81Z"/></svg>
                    </div>
                    <div>
                        <h2 class="profile-section-title">Daftar Pengguna</h2>
                        <p class="profile-section-desc">Semua akun terdaftar, status aktivasi, dan kontrol hak akses.</p>
                    </div>
                </div>

                @if($errors->has('edit_name') || $errors->has('edit_username') || $errors->has('edit_email') || $errors->has('edit_role') || $errors->has('edit_password') || $errors->has('error'))
                    <div class="alert alert-danger" role="alert" style="background:rgba(239,68,68,0.12);border:1px solid rgba(239,68,68,0.25);color:#f87171;margin-bottom:16px">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-8-5a.75.75 0 0 1 .75.75v4.5a.75.75 0 0 1-1.5 0v-4.5A.75.75 0 0 1 10 5Zm0 10a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd"/></svg>
                        <span>Gagal memperbarui akun: {{ $errors->first() }}</span>
                    </div>
                @endif

                <div class="user-table-wrap">
                    <table class="user-table">
                        <thead>
                            <tr>
                                <th>Pengguna</th>
                                <th>Username & Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Dibuat</th>
                                <th style="text-align: right;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse(\App\Models\User::orderBy('created_at','desc')->get() as $u)
                            <tr class="{{ $u->id === auth()->id() ? 'user-table__row--self' : '' }} {{ ! $u->is_active ? 'user-table__row--inactive' : '' }}">
                                <td>
                                    <div class="user-table-name">
                                        <div class="user-table-avatar {{ ! $u->is_active ? 'user-table-avatar--inactive' : '' }}">
                                            {{ strtoupper(substr($u->name,0,1)) }}
                                        </div>
                                        <div>
                                            <div style="font-weight: 600; color: var(--text-primary);">
                                                {{ $u->name }}
                                                @if($u->id === auth()->id())
                                                    <span class="you-badge">Anda</span>
                                                @endif
                                            </div>
                                            <small class="text-muted">ID: #{{ $u->id }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-weight: 500;">{{ '@' . $u->username }}</div>
                                    <small class="text-muted">{{ $u->email }}</small>
                                </td>
                                <td>
                                    <span class="role-badge role-badge--{{ $u->role ?? 'user' }}">
                                        {{ $roleNames[$u->role] ?? ucfirst($u->role ?? 'user') }}
                                    </span>
                                </td>
                                <td>
                                    @if($u->is_active)
                                        <span class="status-badge status-badge--active">
                                            <span class="status-dot status-dot--active"></span>
                                            Aktif
                                        </span>
                                    @else
                                        <span class="status-badge status-badge--inactive">
                                            <span class="status-dot status-dot--inactive"></span>
                                            Nonaktif
                                        </span>
                                    @endif
                                </td>
                                <td class="text-muted" style="font-size: 0.8rem;">
                                    {{ $u->created_at ? $u->created_at->translatedFormat('d M Y') : '-' }}
                                </td>
                                <td style="text-align: right;">
                                    <div class="table-actions">
                                        {{-- Tombol Edit Modal --}}
                                        <button type="button" 
                                            class="btn-action btn-action--edit"
                                            title="Edit Akun & Hak Akses"
                                            data-action="edit-user"
                                            data-id="{{ $u->id }}"
                                            data-name="{{ $u->name }}"
                                            data-username="{{ $u->username }}"
                                            data-email="{{ $u->email }}"
                                            data-role="{{ $u->role }}"
                                            data-is-active="{{ $u->is_active ? '1' : '0' }}"
                                            data-is-self="{{ $u->id === auth()->id() ? '1' : '0' }}"
                                            data-update-url="{{ route('profile.updateUser', $u) }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="m5.433 13.917 1.262-3.155A4 4 0 0 1 7.58 9.42l6.92-6.918a2.121 2.121 0 0 1 3 3l-6.92 6.918c-.383.383-.84.685-1.343.886l-3.154 1.262a.5.5 0 0 1-.65-.65Z"/><path d="M3.5 5.75c0-.69.56-1.25 1.25-1.25H10A.75.75 0 0 0 10 3H4.75A2.75 2.75 0 0 0 2 5.75v9.5A2.75 2.75 0 0 0 4.75 18h9.5A2.75 2.75 0 0 0 17 15.25V10a.75.75 0 0 0-1.5 0v5.25c0 .69-.56 1.25-1.25 1.25h-9.5c-.69 0-1.25-.56-1.25-1.25v-9.5Z"/></svg>
                                            <span>Edit</span>
                                        </button>

                                        {{-- Tombol Toggle Status (Aktifkan / Nonaktifkan) & Hapus --}}
                                        @if($u->id !== auth()->id())
                                            <form method="POST" action="{{ route('profile.toggleUserStatus', $u) }}" style="display: inline-block;">
                                                @csrf
                                                @method('PATCH')
                                                @if($u->is_active)
                                                    <button type="submit" 
                                                        class="btn-action btn-action--deactivate" 
                                                        title="Nonaktifkan Akun"
                                                        onclick="return confirm('Apakah Anda yakin ingin menonaktifkan akun {{ $u->name }}? User ini tidak akan bisa login.')">
                                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16ZM8.28 7.22a.75.75 0 0 0-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 1 0 1.06 1.06L10 11.06l1.72 1.72a.75.75 0 1 0 1.06-1.06L11.06 10l1.72-1.72a.75.75 0 0 0-1.06-1.06L10 8.94 8.28 7.22Z" clip-rule="evenodd"/></svg>
                                                        <span>Nonaktifkan</span>
                                                    </button>
                                                @else
                                                    <button type="submit" 
                                                        class="btn-action btn-action--activate" 
                                                        title="Aktifkan Akun"
                                                        onclick="return confirm('Aktifkan kembali akun {{ $u->name }}?')">
                                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd"/></svg>
                                                        <span>Aktifkan</span>
                                                    </button>
                                                @endif
                                            </form>

                                            {{-- Tombol Hapus Akun Permanen --}}
                                            <form method="POST" action="{{ route('profile.destroyUser', $u) }}" style="display: inline-block;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                    class="btn-action btn-action--delete" 
                                                    title="Hapus Akun Selamanya"
                                                    onclick="return confirm('PERINGATAN: Apakah Anda yakin ingin menghapus akun {{ $u->name }} ({{ '@' . $u->username }}) secara permanen? Akun ini akan dihapus selamanya.')">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 0 0 6 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 1 0 .23 1.482l.149-.022.841 10.518A2.75 2.75 0 0 0 7.596 19h4.807a2.75 2.75 0 0 0 2.742-2.53l.841-10.52.149.023a.75.75 0 0 0 .23-1.482A41.03 41.03 0 0 0 14 4.193V3.75A2.75 2.75 0 0 0 11.25 1h-2.5ZM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4ZM8.58 7.72a.75.75 0 0 1 .7.798l-.5 6a.75.75 0 1 1-1.496-.124l.5-6a.75.75 0 0 1 .796-.674Zm3.54 0a.75.75 0 0 1 .796.674l.5 6a.75.75 0 1 1-1.496.124l-.5-6a.75.75 0 0 1 .7-.798Z" clip-rule="evenodd"/></svg>
                                                    <span>Hapus</span>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="user-table-empty">Belum ada pengguna.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Modal Edit Pengguna --}}
            <div class="user-modal-overlay" id="editUserModal" hidden>
                <div class="user-modal-card">
                    <div class="user-modal-header">
                        <div class="user-modal-header-left">
                            <div class="profile-section-icon" style="background:rgba(99,102,241,0.15);color:var(--accent-light);width:38px;height:38px;">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="m5.433 13.917 1.262-3.155A4 4 0 0 1 7.58 9.42l6.92-6.918a2.121 2.121 0 0 1 3 3l-6.92 6.918c-.383.383-.84.685-1.343.886l-3.154 1.262a.5.5 0 0 1-.65-.65Z"/></svg>
                            </div>
                            <div>
                                <h3 class="user-modal-title">Edit Akun Pengguna</h3>
                                <p class="user-modal-subtitle" id="editModalSubtitle">Perbarui data, role hak akses, dan status akun.</p>
                            </div>
                        </div>
                        <button type="button" class="user-modal-close" id="closeEditModalBtn" aria-label="Tutup">&times;</button>
                    </div>

                    <form method="POST" id="editUserForm" action="" class="profile-form" style="margin-top: 16px;">
                        @csrf
                        @method('PUT')

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Nama Lengkap</label>
                                <div class="input-wrapper">
                                    <span class="input-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M10 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6ZM3.465 14.493a1.23 1.23 0 0 0 .41 1.412A9.957 9.957 0 0 0 10 18c2.31 0 4.438-.784 6.131-2.1.43-.333.604-.903.408-1.41a7.002 7.002 0 0 0-13.074.003Z"/></svg>
                                    </span>
                                    <input type="text" name="edit_name" id="modal_edit_name" class="form-input" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Username</label>
                                <div class="input-wrapper">
                                    <span class="input-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M1 8a7 7 0 1 1 14 0A7 7 0 0 1 1 8Zm7-4a1 1 0 0 0-1 1v3H4a1 1 0 0 0 0 2h3v3a1 1 0 1 0 2 0v-3h3a1 1 0 1 0 0-2H9V5a1 1 0 0 0-1-1Zm9 4a.75.75 0 0 1 .75.75v6.5a.75.75 0 0 1-1.5 0v-6.5A.75.75 0 0 1 17 8Z" clip-rule="evenodd"/></svg>
                                    </span>
                                    <input type="text" name="edit_username" id="modal_edit_username" class="form-input" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Alamat Email</label>
                            <div class="input-wrapper">
                                <span class="input-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M3 4a2 2 0 0 0-2 2v1.161l8.441 4.221a1.25 1.25 0 0 0 1.118 0L19 7.161V6a2 2 0 0 0-2-2H3Z"/><path d="m19 8.839-7.77 3.885a2.75 2.75 0 0 1-2.46 0L1 8.839V14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V8.839Z"/></svg>
                                </span>
                                <input type="email" name="edit_email" id="modal_edit_email" class="form-input" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Role Hak Akses</label>
                                <div class="input-wrapper">
                                    <span class="input-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-5.5-2.5a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0ZM10 12a5.99 5.99 0 0 0-4.793 2.39A6.483 6.483 0 0 0 10 16.5a6.483 6.483 0 0 0 4.793-2.11A5.99 5.99 0 0 0 10 12Z" clip-rule="evenodd"/></svg>
                                    </span>
                                    <select name="edit_role" id="modal_edit_role" class="form-input form-select" required>
                                        <option value="user">User (Staf Operasional)</option>
                                        <option value="teknisi">Teknisi (Hanya Input Logsheet / HP PWA)</option>
                                        <option value="eng_admin">Eng Admin (Lihat, Edit & Verifikasi Logsheet)</option>
                                        <option value="eng_spv">Eng Spv (Supervisor - Lihat, Edit & Verifikasi)</option>
                                        <option value="admin">Admin (Akses Penuh)</option>
                                    </select>
                                </div>
                                <small class="text-muted" id="selfRoleNotice" style="display:none; color:#f59e0b !important; margin-top:4px;">*Role akun sendiri tidak dapat diubah di sini.</small>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Status Akun</label>
                                <div class="input-wrapper">
                                    <span class="input-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd"/></svg>
                                    </span>
                                    <select name="edit_is_active" id="modal_edit_is_active" class="form-input form-select" required>
                                        <option value="1">Aktif (Bisa Login)</option>
                                        <option value="0">Nonaktif (Akses Diblokir)</option>
                                    </select>
                                </div>
                                <small class="text-muted" id="selfStatusNotice" style="display:none; color:#f59e0b !important; margin-top:4px;">*Akun Anda sendiri tidak dapat dinonaktifkan.</small>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Reset Password (Opsional)</label>
                            <div class="input-wrapper">
                                <span class="input-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 1a4.5 4.5 0 0 0-4.5 4.5V9H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2h-.5V5.5A4.5 4.5 0 0 0 10 1Zm3 8V5.5a3 3 0 1 0-6 0V9h6Z" clip-rule="evenodd"/></svg>
                                </span>
                                <input type="password" name="edit_password" id="modal_edit_password" class="form-input" placeholder="Kosongkan jika tidak ingin mengubah password">
                                <button type="button" class="toggle-password" data-target="modal_edit_password" aria-label="Tampilkan">
                                    <svg class="icon-eye" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M10 12.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z"/><path fill-rule="evenodd" d="M.664 10.59a1.651 1.651 0 0 1 0-1.186A10.004 10.004 0 0 1 10 3c4.257 0 7.893 2.66 9.336 6.41.147.381.146.804 0 1.186A10.004 10.004 0 0 1 10 17c-4.257 0-7.893-2.66-9.336-6.41ZM14 10a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z" clip-rule="evenodd"/></svg>
                                </button>
                            </div>
                            <small class="text-muted" style="margin-top:4px;">Isi minimal 8 karakter bila admin ingin mereset password akun ini.</small>
                        </div>

                        <div class="user-modal-actions" style="display:flex; justify-content:space-between; align-items:center;">
                            <div>
                                <button type="button" class="btn-action btn-action--delete" id="modalDeleteBtn" style="padding:9px 14px; font-size:0.85rem;" title="Hapus akun ini selamanya">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 0 0 6 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 1 0 .23 1.482l.149-.022.841 10.518A2.75 2.75 0 0 0 7.596 19h4.807a2.75 2.75 0 0 0 2.742-2.53l.841-10.52.149.023a.75.75 0 0 0 .23-1.482A41.03 41.03 0 0 0 14 4.193V3.75A2.75 2.75 0 0 0 11.25 1h-2.5ZM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4ZM8.58 7.72a.75.75 0 0 1 .7.798l-.5 6a.75.75 0 1 1-1.496-.124l.5-6a.75.75 0 0 1 .796-.674Zm3.54 0a.75.75 0 0 1 .796.674l.5 6a.75.75 0 1 1-1.496.124l-.5-6a.75.75 0 0 1 .7-.798Z" clip-rule="evenodd"/></svg>
                                    <span>Hapus Akun</span>
                                </button>
                            </div>
                            <div style="display:flex; gap:10px; align-items:center;">
                                <button type="button" class="btn-modal-cancel" id="cancelEditModalBtn">Batal</button>
                                <button type="submit" class="btn-profile-save">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd"/></svg>
                                    Simpan Perubahan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Form Hapus Akun dari Modal --}}
            <form id="modalDeleteUserForm" method="POST" action="" style="display:none;">
                @csrf
                @method('DELETE')
            </form>

        </div>{{-- /tab-users --}}

        {{-- Tab: Reset Tanda Tangan Pengguna (Superadmin Only) --}}
        <div class="profile-tab" id="tab-signatures" hidden>
            <div class="profile-section-card">
                <div class="profile-section-header">
                    <div class="profile-section-icon" style="background:rgba(239,68,68,0.15);color:#f87171">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 2a3 3 0 0 0-3 3v1H6a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-1V5a3 3 0 0 0-3-3Zm1 4V5a1 1 0 1 0-2 0v1h2Zm-3 6a1 1 0 1 1 2 0v2a1 1 0 1 1-2 0v-2Z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="profile-section-title">Reset Tanda Tangan Digital Pengguna</h2>
                        <p class="profile-section-desc">Daftar pengguna yang telah memiliki tanda tangan digital aktif. Superadmin dapat mereset tanda tangan jika pengguna memerlukan pembaruan tanda tangan.</p>
                    </div>
                </div>

                @if(session('success') && session('active_tab') === 'signatures')
                    <div class="alert alert-success" role="alert">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd"/></svg>
                        {{ session('success') }}
                    </div>
                @endif

                @php
                    $signedUsers = \App\Models\User::whereNotNull('tanda_tangan')->orderBy('name')->get();
                @endphp

                <div class="user-table-wrap">
                    <table class="user-table">
                        <thead>
                            <tr>
                                <th>Pengguna</th>
                                <th>Role & Username</th>
                                <th>Pratinjau Tanda Tangan</th>
                                <th>Status</th>
                                <th style="text-align: right;">Aksi Superadmin</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($signedUsers as $su)
                            <tr>
                                <td>
                                    <div class="user-table-name">
                                        <div class="user-table-avatar">
                                            @if($su->avatar_url)
                                                <img src="{{ $su->avatar_url }}" alt="{{ $su->name }}" style="width:100%; height:100%; object-fit:cover; border-radius:50%;">
                                            @else
                                                {{ strtoupper(substr($su->name, 0, 1)) }}
                                            @endif
                                        </div>
                                        <div>
                                            <div style="font-weight: 600; color: var(--text-primary);">
                                                {{ $su->name }}
                                                @if($su->id === auth()->id())
                                                    <span class="you-badge">Anda</span>
                                                @endif
                                            </div>
                                            <small class="text-muted">{{ $su->email }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div style="margin-bottom:4px;">
                                        <span class="role-badge role-badge--{{ $su->role ?? 'user' }}">
                                            {{ $roleNames[$su->role] ?? ucfirst($su->role ?? 'user') }}
                                        </span>
                                    </div>
                                    <small class="text-muted">{{ '@' . $su->username }}</small>
                                </td>
                                <td>
                                    <div style="background:#ffffff; border-radius:8px; padding:6px 12px; display:inline-block; border:1px solid rgba(255,255,255,0.15); box-shadow:0 2px 6px rgba(0,0,0,0.2);">
                                        <img src="{{ $su->signature_url }}" alt="Tanda Tangan {{ $su->name }}" style="max-height:42px; max-width:140px; object-fit:contain; display:block;">
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-ok" style="font-size:0.75rem; padding:3px 8px; background:rgba(16,185,129,0.12); color:#34d399; border:1px solid rgba(16,185,129,0.25);">
                                        🔒 Terkunci & Sah
                                    </span>
                                </td>
                                <td style="text-align: right;">
                                    <form method="POST" action="{{ route('admin.signatures.reset', $su) }}" onsubmit="return confirm('Apakah Anda yakin ingin MERESET tanda tangan milik {{ $su->name }} ({{ '@'.$su->username }})? Setelah di-reset, tanda tangan lama akan terhapus dan pengguna dapat membuat tanda tangan baru.')" style="display:inline-block;">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-danger" style="display:inline-flex; align-items:center; gap:5px;" title="Reset Tanda Tangan Pengguna">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="14" height="14">
                                                <path fill-rule="evenodd" d="M15.312 11.424a5.5 5.5 0 0 1-9.201 2.466l-.312-.311h2.433a.75.75 0 0 0 0-1.5H3.75a.75.75 0 0 0-.75.75v4.482a.75.75 0 0 0 1.5 0v-2.09l.343.342a7 7 0 1 0 1.258-7.904.75.75 0 1 0 1.085 1.034 5.5 5.5 0 0 1 8.126 2.781Z" clip-rule="evenodd"/>
                                            </svg>
                                            Reset Tanda Tangan
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" style="text-align:center; padding:36px; color:var(--text-muted);">
                                    <div style="font-size:2rem; margin-bottom:8px;">✍️</div>
                                    <strong style="color:var(--text-secondary); display:block; margin-bottom:4px;">Belum Ada Tanda Tangan Digital</strong>
                                    <span style="font-size:0.85rem;">Saat ini belum ada pengguna yang menyimpan tanda tangan digital di sistem.</span>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>{{-- /tab-signatures --}}
        @endif

    </div>{{-- /profile-main --}}
</div>{{-- /profile-layout --}}
@endsection

@push('scripts')
<script>
// ── Tab switching ──────────────────────────────────────
const navItems = document.querySelectorAll('.profile-nav-item');
const tabs     = document.querySelectorAll('.profile-tab');

function activateTab(target) {
    navItems.forEach(n => n.classList.remove('profile-nav-item--active'));
    tabs.forEach(t => t.hidden = true);

    const targetNav = document.querySelector(`.profile-nav-item[data-tab="${target}"]`);
    const targetTab = document.getElementById('tab-' + target);
    if (targetNav) targetNav.classList.add('profile-nav-item--active');
    if (targetTab) targetTab.hidden = false;
}

navItems.forEach(item => {
    item.addEventListener('click', e => {
        if (!item.dataset.tab) return;
        e.preventDefault();
        activateTab(item.dataset.tab);
    });
});

// Auto-switch ke tab tertentu jika diarahkan server (setelah buat akun)
@if(session('active_tab'))
activateTab('{{ session('active_tab') }}');
@endif

// ── Password visibility toggle ─────────────────────────
document.querySelectorAll('.toggle-password').forEach(btn => {
    btn.addEventListener('click', () => {
        const input = document.getElementById(btn.dataset.target);
        input.type = input.type === 'password' ? 'text' : 'password';
        btn.querySelector('svg').style.opacity = input.type === 'text' ? '0.5' : '1';
    });
});

// ── Password strength meter ────────────────────────────
const newPwdInput = document.getElementById('new_password');
const strengthWrap = document.getElementById('password-strength');
const strengthFill = document.getElementById('strength-fill');
const strengthLabel = document.getElementById('strength-label');

if (newPwdInput) {
    newPwdInput.addEventListener('input', () => {
        const val = newPwdInput.value;
        if (!val) { strengthWrap.hidden = true; return; }
        strengthWrap.hidden = false;

        let score = 0;
        if (val.length >= 8)  score++;
        if (/[A-Z]/.test(val)) score++;
        if (/[0-9]/.test(val)) score++;
        if (/[^A-Za-z0-9]/.test(val)) score++;

        const levels = [
            { pct: '25%',  color: '#ef4444', label: 'Lemah' },
            { pct: '50%',  color: '#f59e0b', label: 'Cukup' },
            { pct: '75%',  color: '#3b82f6', label: 'Kuat' },
            { pct: '100%', color: '#10b981', label: 'Sangat Kuat' },
        ];
        const lv = levels[score - 1] || levels[0];
        strengthFill.style.width = lv.pct;
        strengthFill.style.background = lv.color;
        strengthLabel.textContent = lv.label;
        strengthLabel.style.color = lv.color;
    });
}

// Auto-dismiss flash
const flash = document.querySelector('.alert');
if (flash) setTimeout(() => {
    flash.style.opacity = '0';
    flash.style.transform = 'translateY(-8px)';
    setTimeout(() => flash.remove(), 300);
}, 4000);

// ── Edit User Modal Handlers ───────────────────────────
const editUserModal = document.getElementById('editUserModal');
const editUserForm = document.getElementById('editUserForm');
const closeEditModalBtn = document.getElementById('closeEditModalBtn');
const cancelEditModalBtn = document.getElementById('cancelEditModalBtn');

function openEditModal(btn) {
    if (!editUserModal) return;

    const id = btn.dataset.id;
    const name = btn.dataset.name;
    const username = btn.dataset.username;
    const email = btn.dataset.email;
    const role = btn.dataset.role;
    const isActive = btn.dataset.isActive;
    const isSelf = btn.dataset.isSelf === '1';
    const updateUrl = btn.dataset.updateUrl;

    editUserForm.action = updateUrl;
    document.getElementById('modal_edit_name').value = name;
    document.getElementById('modal_edit_username').value = username;
    document.getElementById('modal_edit_email').value = email;
    document.getElementById('modal_edit_role').value = role;
    document.getElementById('modal_edit_is_active').value = isActive;
    document.getElementById('modal_edit_password').value = '';

    const editSubtitle = document.getElementById('editModalSubtitle');
    if (editSubtitle) {
        editSubtitle.textContent = `Mengubah akun ${name} (@${username})`;
    }

    // Bila akun sendiri, jangan izinkan downgrade role atau nonaktifkan diri sendiri
    const roleSelect = document.getElementById('modal_edit_role');
    const statusSelect = document.getElementById('modal_edit_is_active');
    const selfRoleNotice = document.getElementById('selfRoleNotice');
    const selfStatusNotice = document.getElementById('selfStatusNotice');

    if (isSelf) {
        roleSelect.disabled = true;
        statusSelect.disabled = true;
        if (selfRoleNotice) selfRoleNotice.style.display = 'block';
        if (selfStatusNotice) selfStatusNotice.style.display = 'block';
    } else {
        roleSelect.disabled = false;
        statusSelect.disabled = false;
        if (selfRoleNotice) selfRoleNotice.style.display = 'none';
        if (selfStatusNotice) selfStatusNotice.style.display = 'none';
    }

    const modalDeleteBtn = document.getElementById('modalDeleteBtn');
    if (modalDeleteBtn) {
        if (isSelf) {
            modalDeleteBtn.style.display = 'none';
        } else {
            modalDeleteBtn.style.display = 'inline-flex';
            modalDeleteBtn.onclick = function() {
                if (confirm(`PERINGATAN: Apakah Anda yakin ingin menghapus akun ${name} (@${username}) secara permanen? Akun ini akan dihapus selamanya.`)) {
                    const deleteForm = document.getElementById('modalDeleteUserForm');
                    deleteForm.action = `/profil/user/${id}`;
                    deleteForm.submit();
                }
            };
        }
    }

    editUserModal.hidden = false;
}

function closeEditModal() {
    if (editUserModal) editUserModal.hidden = true;
}

document.querySelectorAll('[data-action="edit-user"]').forEach(btn => {
    btn.addEventListener('click', () => openEditModal(btn));
});

if (closeEditModalBtn) closeEditModalBtn.addEventListener('click', closeEditModal);
if (cancelEditModalBtn) cancelEditModalBtn.addEventListener('click', closeEditModal);

if (editUserModal) {
    editUserModal.addEventListener('click', (e) => {
        if (e.target === editUserModal) closeEditModal();
    });
}

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && editUserModal && !editUserModal.hidden) {
        closeEditModal();
    }
});

// ── Avatar Preview Handler ─────────────────────────────
function handleAvatarSelected(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        if (file.size > 4 * 1024 * 1024) {
            alert('Ukuran file foto maksimal adalah 4 MB.');
            input.value = '';
            return;
        }
        const reader = new FileReader();
        reader.onload = function(e) {
            const previewImg = document.getElementById('avatarPreviewImg');
            const initial = document.getElementById('avatarPreviewInitial');
            const saveBtn = document.getElementById('btnSaveAvatar');
            if (previewImg) {
                previewImg.src = e.target.result;
                previewImg.style.display = 'block';
            }
            if (initial) initial.style.display = 'none';
            if (saveBtn) saveBtn.style.display = 'inline-flex';
        };
        reader.readAsDataURL(file);
    }
}

// ── Digital Signature Canvas ───────────────────────────
const sigCanvas = document.getElementById('signatureCanvas');
const sigPlaceholder = document.getElementById('signaturePlaceholder');
const sigClearBtn = document.getElementById('btnClearSignature');
const sigSaveBtn = document.getElementById('btnSaveCanvasSignature');
const sigInputData = document.getElementById('signatureDataInput');
const sigCanvasForm = document.getElementById('canvasSignatureForm');

if (sigCanvas) {
    const ctx = sigCanvas.getContext('2d');
    let isDrawing = false;
    let hasDrawn = false;

    function resizeSignatureCanvas() {
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        const rect = sigCanvas.getBoundingClientRect();
        if (rect.width === 0) return;
        sigCanvas.width = rect.width * ratio;
        sigCanvas.height = 180 * ratio;
        ctx.scale(ratio, ratio);
        ctx.lineWidth = 2.5;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
        ctx.strokeStyle = '#0f172a';
    }

    window.addEventListener('resize', resizeSignatureCanvas);

    function getCanvasCoordinates(e) {
        const rect = sigCanvas.getBoundingClientRect();
        const clientX = e.touches ? e.touches[0].clientX : e.clientX;
        const clientY = e.touches ? e.touches[0].clientY : e.clientY;
        return {
            x: clientX - rect.left,
            y: clientY - rect.top
        };
    }

    function startSignature(e) {
        isDrawing = true;
        const pos = getCanvasCoordinates(e);
        ctx.beginPath();
        ctx.moveTo(pos.x, pos.y);
        if (sigPlaceholder) sigPlaceholder.style.display = 'none';
    }

    function drawSignature(e) {
        if (!isDrawing) return;
        if (e.cancelable && e.type.startsWith('touch')) e.preventDefault();
        const pos = getCanvasCoordinates(e);
        ctx.lineTo(pos.x, pos.y);
        ctx.stroke();
        hasDrawn = true;
        if (sigSaveBtn) sigSaveBtn.disabled = false;
    }

    function stopSignature() {
        if (isDrawing) {
            isDrawing = false;
            ctx.closePath();
        }
    }

    // Mouse
    sigCanvas.addEventListener('mousedown', startSignature);
    sigCanvas.addEventListener('mousemove', drawSignature);
    window.addEventListener('mouseup', stopSignature);

    // Touch
    sigCanvas.addEventListener('touchstart', (e) => {
        if (e.cancelable) e.preventDefault();
        startSignature(e);
    }, { passive: false });

    sigCanvas.addEventListener('touchmove', (e) => {
        if (e.cancelable) e.preventDefault();
        drawSignature(e);
    }, { passive: false });

    sigCanvas.addEventListener('touchend', stopSignature);
    sigCanvas.addEventListener('touchcancel', stopSignature);

    // Clear
    if (sigClearBtn) {
        sigClearBtn.addEventListener('click', () => {
            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            ctx.clearRect(0, 0, sigCanvas.width / ratio, sigCanvas.height / ratio);
            hasDrawn = false;
            if (sigSaveBtn) sigSaveBtn.disabled = true;
            if (sigPlaceholder) sigPlaceholder.style.display = 'flex';
        });
    }

    // Form submit
    if (sigCanvasForm) {
        sigCanvasForm.addEventListener('submit', (e) => {
            if (!hasDrawn) {
                e.preventDefault();
                alert('Silakan goreskan tanda tangan Anda pada kanvas terlebih dahulu.');
                return;
            }
            sigInputData.value = sigCanvas.toDataURL('image/png');
        });
    }

    // Resize whenever signature tab is opened
    document.querySelectorAll('.profile-nav-item').forEach(item => {
        item.addEventListener('click', () => {
            if (item.dataset.tab === 'signature') {
                setTimeout(resizeSignatureCanvas, 60);
            }
        });
    });

    // Auto-init if tab opened via URL hash
    if (window.location.hash === '#signature') {
        activateTab('signature');
        setTimeout(resizeSignatureCanvas, 60);
    } else if (window.location.hash === '#photo') {
        activateTab('photo');
    }
}
</script>
@endpush
