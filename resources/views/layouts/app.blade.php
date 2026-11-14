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

        // ══════════════════════════════════════════════════════════════════════
        // GLOBAL KPR BETA PAGINATION CONTROLLER (WEB & PWA MOBILE)
        // Membatasi tampilan data maksimal 10 per halaman dengan navigasi instan
        // ══════════════════════════════════════════════════════════════════════
        window.initKprTablePagination = function(config) {
            const rowSelector = config.rowSelector;
            const cardSelector = config.cardSelector || null;
            const topContainer = config.containerTopId 
                ? document.getElementById(config.containerTopId) 
                : (typeof config.topContainer === 'string' ? document.querySelector(config.topContainer) : config.topContainer);
            const bottomContainer = config.containerBottomId 
                ? document.getElementById(config.containerBottomId) 
                : (typeof config.bottomContainer === 'string' ? document.querySelector(config.bottomContainer) : config.bottomContainer);
            const defaultPerPage = config.defaultPerPage || 10;
            const perPageOptions = config.perPageOptions || [10, 20, 30, 'all'];
            const storageKey = config.storageKey || 'kpr_table_per_page';
            const paramName = config.paramName || 'page';
            const scrollToTarget = config.scrollToTarget || null;

            if (!rowSelector && !cardSelector) return null;

            // Load saved per-page preference (default 10)
            let perPage = defaultPerPage;
            try {
                const saved = localStorage.getItem(storageKey);
                if (saved === 'all') {
                    perPage = 'all';
                } else if (saved && !isNaN(parseInt(saved, 10)) && parseInt(saved, 10) > 0) {
                    perPage = parseInt(saved, 10);
                }
            } catch(e) {}

            // Load page from URL query
            let currentPage = 1;
            try {
                const urlParams = new URLSearchParams(window.location.search);
                const p = parseInt(urlParams.get(paramName), 10);
                if (!isNaN(p) && p > 0) currentPage = p;
            } catch(e) {}

            function getRows() {
                return rowSelector ? Array.from(document.querySelectorAll(rowSelector)) : [];
            }

            function getCards() {
                return cardSelector ? Array.from(document.querySelectorAll(cardSelector)) : [];
            }

            function render() {
                const rows = getRows();
                const cards = getCards();
                const totalItems = Math.max(rows.length, cards.length);

                if (totalItems === 0) {
                    if (topContainer) topContainer.style.display = 'none';
                    if (bottomContainer) bottomContainer.style.display = 'none';
                    return;
                }

                if (topContainer) topContainer.style.display = '';
                if (bottomContainer) bottomContainer.style.display = '';

                const limit = (perPage === 'all' || perPage >= totalItems) ? totalItems : perPage;
                const totalPages = (perPage === 'all' || limit <= 0) ? 1 : Math.ceil(totalItems / limit);

                if (currentPage > totalPages) currentPage = totalPages;
                if (currentPage < 1) currentPage = 1;

                const startIndex = (currentPage - 1) * limit;
                const endIndex = Math.min(startIndex + limit, totalItems);

                // Show / hide table rows
                rows.forEach((row, idx) => {
                    if (idx >= startIndex && idx < endIndex) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });

                // Show / hide mobile cards
                cards.forEach((card, idx) => {
                    if (idx >= startIndex && idx < endIndex) {
                        card.style.display = '';
                    } else {
                        card.style.display = 'none';
                    }
                });

                // Render Top Bar
                if (topContainer) {
                    const rangeText = totalItems > 0 
                        ? `Menampilkan data <strong>${startIndex + 1} – ${endIndex}</strong> dari <strong>${totalItems}</strong> data`
                        : 'Belum ada data';

                    let optionsHtml = '';
                    perPageOptions.forEach(opt => {
                        const val = opt === 'all' ? 'all' : opt;
                        const label = opt === 'all' ? 'Tampilkan Semua' : `${opt} Data / Halaman`;
                        const isSelected = (perPage === val) ? 'selected' : '';
                        optionsHtml += `<option value="${val}" ${isSelected}>${label}</option>`;
                    });

                    topContainer.innerHTML = `
                        <div class="kpr-pagination-info">
                            <svg viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M2 4.75A.75.75 0 0 1 2.75 4h14.5a.75.75 0 0 1 0 1.5H2.75A.75.75 0 0 1 2 4.75ZM2 10a.75.75 0 0 1 .75-.75h14.5a.75.75 0 0 1 0 1.5H2.75A.75.75 0 0 1 2 10Zm0 5.25a.75.75 0 0 1 .75-.75h14.5a.75.75 0 0 1 0 1.5H2.75a.75.75 0 0 1-.75-.75Z" clip-rule="evenodd"/>
                            </svg>
                            <span>${rangeText} ${totalPages > 1 ? `(Halaman ${currentPage} dari ${totalPages})` : ''}</span>
                        </div>
                        <div class="kpr-pagination-controls-top">
                            <label class="kpr-pagination-label">Per Halaman:</label>
                            <select class="kpr-pagination-select" id="${topContainer.id}-select-per-page" aria-label="Batas Data Per Halaman">
                                ${optionsHtml}
                            </select>
                        </div>
                    `;

                    const selectEl = topContainer.querySelector('.kpr-pagination-select');
                    if (selectEl) {
                        selectEl.addEventListener('change', function() {
                            perPage = this.value === 'all' ? 'all' : parseInt(this.value, 10);
                            try {
                                localStorage.setItem(storageKey, perPage);
                            } catch(e) {}
                            currentPage = 1;
                            updateUrlParam();
                            render();
                        });
                    }
                }

                // Render Bottom Bar
                if (bottomContainer) {
                    if (totalPages <= 1) {
                        bottomContainer.innerHTML = `
                            <div class="kpr-pagination-info" style="font-size:0.8rem; color:var(--text-muted); width:100%; text-align:center; justify-content:center;">
                                ✓ Menampilkan seluruh data pada periode ini (${totalItems} data)
                            </div>
                        `;
                    } else {
                        let pagesHtml = '';

                        // Navigation Buttons: First & Prev
                        pagesHtml += `
                            <button type="button" class="kpr-page-btn kpr-page-btn--nav-text" data-page="1" ${currentPage === 1 ? 'disabled' : ''} title="Halaman Pertama">
                                <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M15.79 14.77a.75.75 0 0 1-1.06.02l-4.5-4.25a.75.75 0 0 1 0-1.08l4.5-4.25a.75.75 0 1 1 1.04 1.08L11.832 10l3.938 3.71a.75.75 0 0 1 .02 1.06Zm-6 0a.75.75 0 0 1-1.06.02l-4.5-4.25a.75.75 0 0 1 0-1.08l4.5-4.25a.75.75 0 1 1 1.04 1.08L5.832 10l3.938 3.71a.75.75 0 0 1 .02 1.06Z" clip-rule="evenodd"/></svg>
                                <span>Pertama</span>
                            </button>
                            <button type="button" class="kpr-page-btn kpr-page-btn--nav-text" data-page="${currentPage - 1}" ${currentPage === 1 ? 'disabled' : ''} title="Halaman Sebelumnya">
                                <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 0 1-.02 1.06L8.832 10l3.938 3.71a.75.75 0 1 1-1.04 1.08l-4.5-4.25a.75.75 0 0 1 0-1.08l4.5-4.25a.75.75 0 0 1 1.06.02Z" clip-rule="evenodd"/></svg>
                                <span>Sebelumnya</span>
                            </button>
                        `;

                        // Numeric Page Buttons with Sliding Window
                        const maxButtons = 5;
                        let startPage = Math.max(1, currentPage - Math.floor(maxButtons / 2));
                        let endPage = Math.min(totalPages, startPage + maxButtons - 1);
                        if (endPage - startPage + 1 < maxButtons) {
                            startPage = Math.max(1, endPage - maxButtons + 1);
                        }

                        if (startPage > 1) {
                            pagesHtml += `<button type="button" class="kpr-page-btn" data-page="1">1</button>`;
                            if (startPage > 2) pagesHtml += `<span class="kpr-page-ellipsis">…</span>`;
                        }

                        for (let p = startPage; p <= endPage; p++) {
                            const isActive = (p === currentPage) ? 'active' : '';
                            pagesHtml += `<button type="button" class="kpr-page-btn ${isActive}" data-page="${p}">${p}</button>`;
                        }

                        if (endPage < totalPages) {
                            if (endPage < totalPages - 1) pagesHtml += `<span class="kpr-page-ellipsis">…</span>`;
                            pagesHtml += `<button type="button" class="kpr-page-btn" data-page="${totalPages}">${totalPages}</button>`;
                        }

                        // Navigation Buttons: Next & Last
                        pagesHtml += `
                            <button type="button" class="kpr-page-btn kpr-page-btn--nav-text" data-page="${currentPage + 1}" ${currentPage === totalPages ? 'disabled' : ''} title="Halaman Berikutnya">
                                <span>Berikutnya</span>
                                <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Z" clip-rule="evenodd"/></svg>
                            </button>
                            <button type="button" class="kpr-page-btn kpr-page-btn--nav-text" data-page="${totalPages}" ${currentPage === totalPages ? 'disabled' : ''} title="Halaman Terakhir">
                                <span>Terakhir</span>
                                <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10.21 14.77a.75.75 0 0 1 .02-1.06L14.168 10 10.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Zm-6 0a.75.75 0 0 1 .02-1.06L8.168 10 4.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Z" clip-rule="evenodd"/></svg>
                            </button>
                        `;

                        bottomContainer.innerHTML = `
                            <nav class="kpr-pagination-nav" aria-label="Navigasi Halaman Data">
                                ${pagesHtml}
                            </nav>
                            <div class="kpr-pagination-status-text">
                                Halaman <strong>${currentPage}</strong> dari <strong>${totalPages}</strong>
                            </div>
                        `;

                        // Add click listeners to page buttons
                        bottomContainer.querySelectorAll('.kpr-page-btn').forEach(btn => {
                            btn.addEventListener('click', function(e) {
                                e.preventDefault();
                                const targetPage = parseInt(this.dataset.page, 10);
                                if (!isNaN(targetPage) && targetPage >= 1 && targetPage <= totalPages && targetPage !== currentPage) {
                                    currentPage = targetPage;
                                    updateUrlParam();
                                    render();

                                    // Scroll to table smoothly if user scrolled down
                                    if (scrollToTarget) {
                                        const el = typeof scrollToTarget === 'string' ? document.querySelector(scrollToTarget) : scrollToTarget;
                                        if (el) {
                                            const rect = el.getBoundingClientRect();
                                            if (rect.top < 0 || rect.top > window.innerHeight) {
                                                el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                                            }
                                        }
                                    }
                                }
                            });
                        });
                    }
                }

                if (typeof config.onPageChange === 'function') {
                    config.onPageChange(currentPage, totalPages, startIndex, endIndex);
                }
            }

            function updateUrlParam() {
                try {
                    const url = new URL(window.location);
                    if (currentPage > 1) {
                        url.searchParams.set(paramName, currentPage);
                    } else {
                        url.searchParams.delete(paramName);
                    }
                    window.history.replaceState({}, '', url);
                } catch(e) {}
            }

            // Initial render
            render();

            return {
                refresh: render,
                setPage: function(p) {
                    currentPage = p;
                    updateUrlParam();
                    render();
                },
                getPage: function() { return currentPage; },
                getPerPage: function() { return perPage; },
                getCurrentRange: function() {
                    const totalItems = Math.max(getRows().length, getCards().length);
                    const limit = (perPage === 'all' || perPage >= totalItems) ? totalItems : perPage;
                    const startIndex = (currentPage - 1) * limit;
                    const endIndex = Math.min(startIndex + limit, totalItems);
                    return { startIndex, endIndex, totalItems, currentPage };
                }
            };
        };
    </script>

    @stack('scripts')
</body>
</html>
