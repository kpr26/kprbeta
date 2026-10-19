@extends('layouts.app')

@section('title', 'Masuk — Property Management System')

@section('content')
<div class="login-root">

    {{-- ══ PANEL KIRI : Branding Visual ══ --}}
    <div class="lp-left" aria-hidden="true">

        {{-- SVG Skyline Gedung Bertingkat --}}
        <div class="skyline-wrap">
            <svg class="skyline-svg" viewBox="0 0 900 420" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <!-- Gedung kiri jauh -->
                <rect x="0"   y="200" width="60"  height="220" fill="rgba(200,170,110,0.07)"/>
                <rect x="10"  y="180" width="40"  height="20"  fill="rgba(200,170,110,0.09)"/>
                <!-- Jendela -->
                <rect x="5"   y="210" width="10" height="8" fill="rgba(200,170,110,0.15)" rx="1"/>
                <rect x="20"  y="210" width="10" height="8" fill="rgba(200,170,110,0.18)" rx="1"/>
                <rect x="35"  y="210" width="10" height="8" fill="rgba(200,170,110,0.10)" rx="1"/>
                <rect x="5"   y="228" width="10" height="8" fill="rgba(200,170,110,0.10)" rx="1"/>
                <rect x="20"  y="228" width="10" height="8" fill="rgba(200,170,110,0.20)" rx="1"/>
                <rect x="35"  y="228" width="10" height="8" fill="rgba(200,170,110,0.12)" rx="1"/>
                <rect x="5"   y="246" width="10" height="8" fill="rgba(200,170,110,0.15)" rx="1"/>
                <rect x="20"  y="246" width="10" height="8" fill="rgba(200,170,110,0.08)" rx="1"/>
                <rect x="35"  y="246" width="10" height="8" fill="rgba(200,170,110,0.18)" rx="1"/>
                <rect x="5"   y="264" width="10" height="8" fill="rgba(200,170,110,0.10)" rx="1"/>
                <rect x="20"  y="264" width="10" height="8" fill="rgba(200,170,110,0.16)" rx="1"/>
                <rect x="35"  y="264" width="10" height="8" fill="rgba(200,170,110,0.10)" rx="1"/>

                <!-- Tower kiri tengah -->
                <rect x="70"  y="80"  width="90"  height="340" fill="rgba(200,170,110,0.06)"/>
                <rect x="80"  y="60"  width="70"  height="22"  fill="rgba(200,170,110,0.09)"/>
                <rect x="110" y="40"  width="10"  height="22"  fill="rgba(200,170,110,0.12)"/>
                <!-- Jendela grid -->
                @for ($row = 0; $row < 12; $row++)
                    @for ($col = 0; $col < 3; $col++)
                        <rect x="{{ 82 + $col * 26 }}" y="{{ 90 + $row * 25 }}" width="16" height="14"
                              fill="rgba(200,170,110,{{ 0.05 + ($row + $col) % 3 * 0.06 }})" rx="1"/>
                    @endfor
                @endfor

                <!-- TOWER UTAMA TENGAH (flagship) -->
                <rect x="280" y="20"  width="140" height="400" fill="rgba(200,170,110,0.08)"/>
                <rect x="295" y="5"   width="110" height="18"  fill="rgba(200,170,110,0.11)"/>
                <rect x="344" y="0"   width="12"  height="8"   fill="rgba(200,170,110,0.15)"/>
                <line x1="350" y1="0" x2="350" y2="20" stroke="rgba(200,170,110,0.30)" stroke-width="1"/>
                <!-- Jendela tower utama -->
                @for ($row = 0; $row < 16; $row++)
                    @for ($col = 0; $col < 5; $col++)
                        <rect x="{{ 290 + $col * 26 }}" y="{{ 28 + $row * 23 }}" width="16" height="14"
                              fill="rgba(200,170,110,{{ 0.06 + ($row * 2 + $col) % 5 * 0.05 }})" rx="1"/>
                    @endfor
                @endfor
                <!-- Aksen garis vertikal emas -->
                <line x1="350" y1="20" x2="350" y2="420" stroke="rgba(200,170,110,0.12)" stroke-width="1.5" stroke-dasharray="6,12"/>

                <!-- Tower kanan tengah -->
                <rect x="440" y="100" width="100" height="320" fill="rgba(200,170,110,0.065)"/>
                <rect x="450" y="84"  width="80"  height="18"  fill="rgba(200,170,110,0.09)"/>
                <rect x="486" y="68"  width="8"   height="18"  fill="rgba(200,170,110,0.12)"/>
                @for ($row = 0; $row < 11; $row++)
                    @for ($col = 0; $col < 3; $col++)
                        <rect x="{{ 450 + $col * 30 }}" y="{{ 108 + $row * 26 }}" width="18" height="15"
                              fill="rgba(200,170,110,{{ 0.04 + ($row + $col * 2) % 4 * 0.05 }})" rx="1"/>
                    @endfor
                @endfor

                <!-- Gedung kanan jauh -->
                <rect x="560" y="150" width="80"  height="270" fill="rgba(200,170,110,0.055)"/>
                <rect x="570" y="135" width="60"  height="18"  fill="rgba(200,170,110,0.08)"/>
                @for ($row = 0; $row < 8; $row++)
                    @for ($col = 0; $col < 2; $col++)
                        <rect x="{{ 572 + $col * 34 }}" y="{{ 158 + $row * 28 }}" width="20" height="15"
                              fill="rgba(200,170,110,{{ 0.04 + ($row + $col) % 3 * 0.06 }})" rx="1"/>
                    @endfor
                @endfor

                <!-- Gedung paling kanan -->
                <rect x="660" y="170" width="60"  height="250" fill="rgba(200,170,110,0.05)"/>
                <rect x="668" y="158" width="44"  height="14"  fill="rgba(200,170,110,0.07)"/>

                <!-- Garis horizon tanah -->
                <rect x="0" y="415" width="900" height="5" fill="rgba(200,170,110,0.18)"/>

                <!-- Garis ornamen emas melintang -->
                <line x1="0" y1="420" x2="900" y2="420" stroke="rgba(200,170,110,0.30)" stroke-width="1"/>
            </svg>
        </div>

        {{-- Teks Branding --}}
        <div class="lp-brand">
            <div class="lp-ornament">
                <span></span>
                <svg viewBox="0 0 24 24" fill="currentColor" class="lp-building-ico">
                    <path d="M3 2.25a.75.75 0 0 0 0 1.5v16.5h-.75a.75.75 0 0 0 0 1.5H15v-18a.75.75 0 0 0 0-1.5H3ZM6.75 19.5v-2.25a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75v2.25h-4.5ZM6 6.75A.75.75 0 0 1 6.75 6h.75a.75.75 0 0 1 0 1.5h-.75A.75.75 0 0 1 6 6.75ZM6.75 9h.75a.75.75 0 0 1 0 1.5h-.75a.75.75 0 0 1 0-1.5ZM6 12.75A.75.75 0 0 1 6.75 12h.75a.75.75 0 0 1 0 1.5h-.75a.75.75 0 0 1-.75-.75ZM10.5 6a.75.75 0 0 0 0 1.5h.75a.75.75 0 0 0 0-1.5h-.75Zm-.75 3.75A.75.75 0 0 1 10.5 9h.75a.75.75 0 0 1 0 1.5h-.75a.75.75 0 0 1-.75-.75ZM10.5 12a.75.75 0 0 0 0 1.5h.75a.75.75 0 0 0 0-1.5h-.75ZM16.5 6.75v15h5.25a.75.75 0 0 0 0-1.5H21v-12a.75.75 0 0 0 0-1.5h-4.5Zm1.5 4.5a.75.75 0 0 1 .75-.75h.008a.75.75 0 0 1 0 1.5H18.75a.75.75 0 0 1-.75-.75Zm.75 2.25a.75.75 0 0 0 0 1.5h.008a.75.75 0 0 0 0-1.5H18.75Z"/>
                </svg>
                <span></span>
            </div>
            <h2 class="lp-tagline">Property Management</h2>
            <p class="lp-sub">Building Operations System</p>
            <div class="lp-divider">
                <span></span><span class="lp-diamond">◆</span><span></span>
            </div>
            <p class="lp-desc">Sistem terpadu pengelolaan operasional<br>gedung bertingkat kelas premium</p>
        </div>

        {{-- Overlay gradient --}}
        <div class="lp-left-overlay"></div>
    </div>

    {{-- ══ PANEL KANAN : Form Login ══ --}}
    <div class="lp-right">
        <div class="lp-form-wrap">

            {{-- Logo sistem --}}
            <div class="lp-logo">
                <div class="lp-logo-mark">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M11.47 3.841a.75.75 0 0 1 1.06 0l8.69 8.69a.75.75 0 1 0 1.06-1.061l-8.689-8.69a2.25 2.25 0 0 0-3.182 0l-8.69 8.69a.75.75 0 1 0 1.061 1.06l8.69-8.689Z"/>
                        <path d="m12 5.432 8.159 8.159c.03.03.06.058.091.086v6.198c0 1.035-.84 1.875-1.875 1.875H15a.75.75 0 0 1-.75-.75v-4.5a.75.75 0 0 0-.75-.75h-3a.75.75 0 0 0-.75.75V21a.75.75 0 0 1-.75.75H5.625a1.875 1.875 0 0 1-1.875-1.875v-6.198a2.29 2.29 0 0 0 .091-.086L12 5.432Z"/>
                    </svg>
                </div>
                <div class="lp-logo-text">
                    <span class="lp-logo-name">KPR</span>
                    <span class="lp-logo-tagline">PROPERTY MANAGEMENT</span>
                </div>
            </div>

            {{-- Judul --}}
            <div class="lp-heading">
                <h1 class="lp-title">Welcome Back</h1>
                <p class="lp-subtitle">Silakan masuk ke akun Anda untuk melanjutkan</p>
            </div>

            {{-- Flash Messages --}}
            @if (session('success'))
                <div class="lp-alert lp-alert--success" role="alert">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd"/></svg>
                    {{ session('success') }}
                </div>
            @endif
            @if ($errors->any())
                <div class="lp-alert lp-alert--error" role="alert">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-8-5a.75.75 0 0 1 .75.75v4.5a.75.75 0 0 1-1.5 0v-4.5A.75.75 0 0 1 10 5Zm0 10a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd"/></svg>
                    Username atau password tidak sesuai. Silakan coba kembali.
                </div>
            @endif

            {{-- Form --}}
            <form class="lp-form" method="POST" action="{{ route('login') }}" id="loginForm" novalidate>
                @csrf

                {{-- Username --}}
                <div class="lf-group {{ $errors->has('username') ? 'lf-group--error' : '' }}">
                    <label class="lf-label" for="username">Username</label>
                    <div class="lf-field">
                        <span class="lf-icon">
                            <svg viewBox="0 0 20 20" fill="currentColor"><path d="M10 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6ZM3.465 14.493a1.23 1.23 0 0 0 .41 1.412A9.957 9.957 0 0 0 10 18c2.31 0 4.438-.784 6.131-2.1.43-.333.604-.903.408-1.41a7.002 7.002 0 0 0-13.074.003Z"/></svg>
                        </span>
                        <input type="text" id="username" name="username" class="lf-input"
                               value="{{ old('username') }}" placeholder="Masukkan username Anda"
                               autocomplete="username" autofocus required>
                        <span class="lf-focus-line"></span>
                    </div>
                    @error('username')
                        <p class="lf-err"><svg viewBox="0 0 16 16" fill="currentColor"><path fill-rule="evenodd" d="M8 15A7 7 0 1 0 8 1a7 7 0 0 0 0 14Zm0-4a.75.75 0 0 1-.75-.75v-3a.75.75 0 0 1 1.5 0v3A.75.75 0 0 1 8 11Zm0-7.25a.75.75 0 1 1 0 1.5.75.75 0 0 1 0-1.5Z" clip-rule="evenodd"/></svg>{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password --}}
                <div class="lf-group {{ $errors->has('password') ? 'lf-group--error' : '' }}">
                    <label class="lf-label" for="password">Password</label>
                    <div class="lf-field">
                        <span class="lf-icon">
                            <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 1a4.5 4.5 0 0 0-4.5 4.5V9H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2h-.5V5.5A4.5 4.5 0 0 0 10 1Zm3 8V5.5a3 3 0 1 0-6 0V9h6Z" clip-rule="evenodd"/></svg>
                        </span>
                        <input type="password" id="password" name="password" class="lf-input"
                               placeholder="Masukkan password Anda"
                               autocomplete="current-password" required>
                        <button type="button" class="lf-eye" id="togglePassword" aria-label="Tampilkan password">
                            <svg id="eyeIcon" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M10 12.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z"/>
                                <path fill-rule="evenodd" d="M.664 10.59a1.651 1.651 0 0 1 0-1.186A10.004 10.004 0 0 1 10 3c4.257 0 7.893 2.66 9.336 6.41.147.381.146.804 0 1.186A10.004 10.004 0 0 1 10 17c-4.257 0-7.893-2.66-9.336-6.41ZM14 10a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                        <span class="lf-focus-line"></span>
                    </div>
                    @error('password')
                        <p class="lf-err"><svg viewBox="0 0 16 16" fill="currentColor"><path fill-rule="evenodd" d="M8 15A7 7 0 1 0 8 1a7 7 0 0 0 0 14Zm0-4a.75.75 0 0 1-.75-.75v-3a.75.75 0 0 1 1.5 0v3A.75.75 0 0 1 8 11Zm0-7.25a.75.75 0 1 1 0 1.5.75.75 0 0 1 0-1.5Z" clip-rule="evenodd"/></svg>{{ $message }}</p>
                    @enderror
                </div>

                {{-- Remember Me --}}
                <div class="lf-options">
                    <label class="lf-check" for="remember">
                        <input type="checkbox" id="remember" name="remember" class="lf-check-input">
                        <span class="lf-check-box"></span>
                        <span>Ingat saya</span>
                    </label>
                </div>

                {{-- Submit --}}
                <button type="submit" class="lf-submit" id="submitBtn">
                    <span class="lf-btn-text">
                        <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 4.25A2.25 2.25 0 0 1 5.25 2h5.5A2.25 2.25 0 0 1 13 4.25v2a.75.75 0 0 1-1.5 0v-2a.75.75 0 0 0-.75-.75h-5.5a.75.75 0 0 0-.75.75v11.5c0 .414.336.75.75.75h5.5a.75.75 0 0 0 .75-.75v-2a.75.75 0 0 1 1.5 0v2A2.25 2.25 0 0 1 10.75 18h-5.5A2.25 2.25 0 0 1 3 15.75V4.25Z" clip-rule="evenodd"/><path fill-rule="evenodd" d="M19 10a.75.75 0 0 0-.75-.75H8.704l1.048-1.08a.75.75 0 1 0-1.004-1.11l-2.5 2.567a.75.75 0 0 0 0 1.086l2.5 2.567a.75.75 0 1 0 1.004-1.11l-1.048-1.08h9.546A.75.75 0 0 0 19 10Z" clip-rule="evenodd"/></svg>
                        Sign In
                    </span>
                    <span class="lf-btn-spinner" style="display:none">
                        <svg viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" opacity="0.25"/><path d="M12 2a10 10 0 0 1 10 10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
                        Memproses...
                    </span>
                </button>
            </form>

            {{-- Footer --}}
            <div class="lp-foot">
                <div class="lp-foot-divider"><span></span><span class="lp-diamond">◆</span><span></span></div>
                <p>&copy; {{ date('Y') }} KPR Property Management &mdash; All rights reserved.</p>
                <p class="lp-foot-sub">Building Operations System v1.0</p>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    // Toggle password
    const toggleBtn = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    const eyeIcon = document.getElementById('eyeIcon');
    toggleBtn.addEventListener('click', () => {
        const isPwd = passwordInput.type === 'password';
        passwordInput.type = isPwd ? 'text' : 'password';
        eyeIcon.innerHTML = isPwd
            ? `<path fill-rule="evenodd" d="M3.28 2.22a.75.75 0 0 0-1.06 1.06l14.5 14.5a.75.75 0 1 0 1.06-1.06l-1.745-1.745a10.029 10.029 0 0 0 3.3-4.38 1.651 1.651 0 0 0 0-1.185A10.004 10.004 0 0 0 9.999 3a9.956 9.956 0 0 0-4.744 1.194L3.28 2.22ZM7.752 6.69l1.092 1.092a2.5 2.5 0 0 1 3.374 3.373l1.091 1.092a4 4 0 0 0-5.557-5.557Z" clip-rule="evenodd"/><path d="m10.748 13.93 2.523 2.523a9.987 9.987 0 0 1-3.27.547c-4.258 0-7.894-2.66-9.337-6.41a1.651 1.651 0 0 1 0-1.186A10.007 10.007 0 0 1 2.839 6.02L6.07 9.252a4 4 0 0 0 4.678 4.678Z"/>`
            : `<path d="M10 12.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z"/><path fill-rule="evenodd" d="M.664 10.59a1.651 1.651 0 0 1 0-1.186A10.004 10.004 0 0 1 10 3c4.257 0 7.893 2.66 9.336 6.41.147.381.146.804 0 1.186A10.004 10.004 0 0 1 10 17c-4.257 0-7.893-2.66-9.336-6.41ZM14 10a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z" clip-rule="evenodd"/>`;
    });

    // Loading state
    document.getElementById('loginForm').addEventListener('submit', function () {
        const btn = document.getElementById('submitBtn');
        btn.querySelector('.lf-btn-text').style.display = 'none';
        btn.querySelector('.lf-btn-spinner').style.display = 'flex';
        btn.disabled = true;
    });

    // Auto-dismiss alert
    const alert = document.querySelector('.lp-alert');
    if (alert) setTimeout(() => { alert.style.opacity = '0'; setTimeout(() => alert.remove(), 400); }, 5000);
</script>
@endpush
