<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Aplikasi Input Logsheet Harian Listrik & Air KPR Beta untuk Teknisi">
    <title>@yield('title', config('app.name')) — KPR Beta</title>

    <!-- PWA & Mobile Web App Meta -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#090d15">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Logsheet">
    <meta name="application-name" content="Logsheet">
    <meta name="msapplication-TileColor" content="#090d15">
    <meta name="msapplication-TileImage" content="{{ asset('icons/icon-192.png') }}">

    <!-- Icons & Favicons -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('icons/icon.svg') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('icons/icon-192.png') }}">
    <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('icons/icon-512.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('icons/icon-192.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('icons/icon-192.png') }}">
    <link rel="apple-touch-icon" sizes="512x512" href="{{ asset('icons/icon-512.png') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&display=swap" rel="stylesheet">

    <!-- App CSS -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ file_exists(public_path('css/app.css')) ? filemtime(public_path('css/app.css')) : time() }}">

    <!-- Theme Initialization Script (Prevent FOUC) -->
    <script>
        (function() {
            try {
                const savedTheme = localStorage.getItem('kpr_theme') || 'dark';
                document.documentElement.setAttribute('data-theme', savedTheme);
                const metaThemeColor = document.querySelector('meta[name="theme-color"]');
                if (metaThemeColor) {
                    metaThemeColor.setAttribute('content', savedTheme === 'light' ? '#f4f6fa' : '#0d131f');
                }
            } catch (e) {}
        })();
    </script>

    <style>
        /* Minimalist Global Loading Spinner & Anti-Double-Click Styles (Inline Fallback) */
        .kpr-loading-overlay {
            position: fixed;
            inset: 0;
            width: 100vw;
            width: 100dvw;
            height: 100vh;
            height: 100dvh;
            background: rgba(9, 13, 21, 0.45);
            backdrop-filter: blur(2px);
            -webkit-backdrop-filter: blur(2px);
            z-index: 999999;
            display: none;
            align-items: center;
            justify-content: center;
            pointer-events: all;
            touch-action: none;
            user-select: none;
            -webkit-user-select: none;
            opacity: 0;
            transition: opacity 0.16s ease-in-out;
        }
        [data-theme="light"] .kpr-loading-overlay {
            background: rgba(15, 23, 42, 0.28);
        }
        .kpr-loading-overlay.is-active {
            display: flex;
            opacity: 1;
        }
        .kpr-loading-card {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            padding: 10px 20px;
            background: var(--bg-card, #141c2c);
            color: var(--text-primary, #f8fafc);
            border: 1px solid rgba(212, 175, 55, 0.38);
            border-radius: 9999px;
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.45), 0 0 16px rgba(212, 175, 55, 0.12);
            font-size: 0.88rem;
            font-weight: 600;
            letter-spacing: 0.015em;
            transform: scale(0.96);
            transition: transform 0.18s cubic-bezier(0.16, 1, 0.3, 1);
        }
        [data-theme="light"] .kpr-loading-card {
            background: #ffffff;
            color: #0f172a;
            border-color: rgba(212, 175, 55, 0.5);
            box-shadow: 0 10px 28px rgba(15, 23, 42, 0.12), 0 0 12px rgba(212, 175, 55, 0.16);
        }
        .kpr-loading-overlay.is-active .kpr-loading-card {
            transform: scale(1);
        }
        .kpr-spinner-wrap {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 22px;
            height: 22px;
            flex-shrink: 0;
        }
        .kpr-loading-spinner {
            width: 22px;
            height: 22px;
            animation: kprSpinnerSpin 0.75s linear infinite;
        }
        .kpr-spinner-track {
            color: rgba(255, 255, 255, 0.16);
        }
        [data-theme="light"] .kpr-spinner-track {
            color: rgba(15, 23, 42, 0.15);
        }
        .kpr-spinner-head {
            color: #fbbf24;
        }
        [data-theme="light"] .kpr-spinner-head {
            color: #d97706;
        }
        @keyframes kprSpinnerSpin {
            to { transform: rotate(360deg); }
        }
        .kpr-loading-text {
            white-space: nowrap;
            font-size: 0.86rem;
            font-weight: 600;
            line-height: 1;
        }
        .btn.is-submitting,
        button.is-submitting,
        input[type="submit"].is-submitting {
            opacity: 0.65 !important;
            pointer-events: none !important;
            cursor: not-allowed !important;
        }
    </style>

    @stack('styles')
</head>
<body>
    @yield('content')

    <!-- Global Minimalist Loading Spinner & Anti-Double-Click Overlay -->
    <div id="kprGlobalLoading" class="kpr-loading-overlay" aria-hidden="true" role="status" aria-live="polite">
        <div class="kpr-loading-card">
            <div class="kpr-spinner-wrap">
                <svg class="kpr-loading-spinner" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="12" cy="12" r="9.5" stroke="currentColor" stroke-width="2.75" class="kpr-spinner-track"></circle>
                    <path d="M12 2.5a9.5 9.5 0 0 1 9.5 9.5" stroke="currentColor" stroke-width="2.75" stroke-linecap="round" class="kpr-spinner-head"></path>
                </svg>
            </div>
            <span id="kprLoadingText" class="kpr-loading-text">Memproses data...</span>
        </div>
    </div>

    <!-- PWA Service Worker Registration -->
    <script>
        // Global Theme Toggle Function
        window.toggleKprTheme = function() {
            try {
                const current = document.documentElement.getAttribute('data-theme') || 'dark';
                const next = current === 'dark' ? 'light' : 'dark';
                document.documentElement.setAttribute('data-theme', next);
                localStorage.setItem('kpr_theme', next);
                const metaThemeColor = document.querySelector('meta[name="theme-color"]');
                if (metaThemeColor) {
                    metaThemeColor.setAttribute('content', next === 'light' ? '#f4f6fa' : '#0d131f');
                }
                window.dispatchEvent(new CustomEvent('kpr-theme-changed', { detail: { theme: next } }));
            } catch (e) {
                console.error('Theme toggle error:', e);
            }
        };

        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(reg => console.log('PWA Service Worker registered:', reg.scope))
                    .catch(err => console.warn('PWA Service Worker registration failed:', err));
            });
        }

        // Global PWA install prompt handler
        let deferredPrompt;
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            const installBtns = document.querySelectorAll('.pwa-install-btn');
            installBtns.forEach(btn => {
                btn.style.display = 'inline-flex';
                btn.addEventListener('click', async () => {
                    if (deferredPrompt) {
                        deferredPrompt.prompt();
                        const { outcome } = await deferredPrompt.userChoice;
                        console.log('PWA install outcome:', outcome);
                        deferredPrompt = null;
                        btn.style.display = 'none';
                    }
                });
            });
        });

        // ── Global Anti-Double-Click & Minimalist Loading Spinner Controller ──
        (function() {
            const overlay = document.getElementById('kprGlobalLoading');
            const textEl  = document.getElementById('kprLoadingText');
            let safetyTimer = null;
            let isSubmitting = false;

            window.showKprLoading = function(message = 'Memproses data... Mohon tunggu') {
                if (!overlay) return;
                if (textEl && message) textEl.textContent = message;
                overlay.classList.add('is-active');
                overlay.setAttribute('aria-hidden', 'false');
                isSubmitting = true;

                // Safety timeout fallback (15 detik jika tidak ada perpindahan halaman)
                clearTimeout(safetyTimer);
                safetyTimer = setTimeout(() => {
                    window.hideKprLoading();
                }, 15000);
            };

            window.hideKprLoading = function() {
                if (!overlay) return;
                overlay.classList.remove('is-active');
                overlay.setAttribute('aria-hidden', 'true');
                isSubmitting = false;
                clearTimeout(safetyTimer);

                document.querySelectorAll('.is-submitting').forEach(el => {
                    el.classList.remove('is-submitting');
                    if (el.tagName === 'BUTTON' || el.tagName === 'INPUT') el.disabled = false;
                });
                document.querySelectorAll('form[data-submitting="true"]').forEach(form => {
                    delete form.dataset.submitting;
                });
            };

            // Intercept klik ganda pada tombol submit dalam fase capture
            document.addEventListener('click', function(e) {
                const btn = e.target.closest('button[type="submit"], input[type="submit"], .btn-prevent-double');
                if (!btn) return;

                if (isSubmitting || btn.classList.contains('is-submitting')) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    return false;
                }
            }, true);

            // Global Form Submit Handler (Bubbling: dijalankan setelah confirm/inline validation lolos)
            document.addEventListener('submit', function(e) {
                const form = e.target;
                if (!form || form.tagName !== 'FORM') return;

                // Jika event dibatalkan (misal klik "Batal" pada window.confirm)
                if (e.defaultPrevented) return;

                // Abaikan form yang membuka di tab baru (misal cetak PDF target="_blank")
                if (form.target === '_blank') return;

                // Abaikan form opt-out
                if (form.hasAttribute('data-no-loading')) return;

                // Cek validasi HTML5 form
                if (form.checkValidity && !form.checkValidity()) return;

                // Cegah double submit
                if (isSubmitting || form.dataset.submitting === 'true') {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    return false;
                }
                form.dataset.submitting = 'true';

                const customMsg = form.getAttribute('data-loading-text') || 'Menyimpan data... Mohon tunggu';
                window.showKprLoading(customMsg);

                // Nonaktifkan tombol submit
                const submitBtns = form.querySelectorAll('button[type="submit"], input[type="submit"]');
                submitBtns.forEach(btn => {
                    btn.classList.add('is-submitting');
                    btn.disabled = true;
                });
            }, false);

            // Hook pemanggilan programmatic form.submit() (seperti pada bulk action)
            const nativeFormSubmit = HTMLFormElement.prototype.submit;
            HTMLFormElement.prototype.submit = function() {
                if (!this.hasAttribute('data-no-loading') && this.target !== '_blank') {
                    const customMsg = this.getAttribute('data-loading-text') || 'Memproses data... Mohon tunggu';
                    if (typeof window.showKprLoading === 'function') {
                        window.showKprLoading(customMsg);
                    }
                    const submitBtns = this.querySelectorAll('button[type="submit"], input[type="submit"]');
                    submitBtns.forEach(btn => {
                        btn.classList.add('is-submitting');
                        btn.disabled = true;
                    });
                }
                return nativeFormSubmit.apply(this, arguments);
            };

            // BFCache (Back/Forward Cache): pulihkan kondisi saat navigasi kembali
            window.addEventListener('pageshow', function() {
                window.hideKprLoading();
            });
        })();
    </script>

    @stack('scripts')
</body>
</html>
