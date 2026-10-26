<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Offline — KPR Beta Logsheet</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script>
        (function() {
            var theme = localStorage.getItem('kpr_theme') || (window.matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark');
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>
    <style>
        body {
            background: var(--bg-main, #0a0e1a);
            color: var(--text-primary, #f1f5f9);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            text-align: center;
        }
        .offline-card {
            background: var(--bg-card, rgba(15, 23, 42, 0.85));
            border: 1px solid var(--border, rgba(255, 255, 255, 0.1));
            backdrop-filter: blur(16px);
            border-radius: 20px;
            padding: 36px 28px;
            max-width: 420px;
            width: 100%;
            box-shadow: var(--shadow-lg, 0 10px 30px rgba(0,0,0,0.5));
        }
        .offline-icon {
            width: 64px;
            height: 64px;
            background: rgba(245, 158, 11, 0.15);
            color: var(--warning, #fbbf24);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }
        .offline-icon svg { width: 32px; height: 32px; }
        h1 { font-size: 1.35rem; font-weight: 700; margin-bottom: 8px; color: var(--text-primary, #fff); }
        p { font-size: 0.9rem; color: var(--text-secondary, #94a3b8); line-height: 1.5; margin-bottom: 24px; }
        .btn-retry {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            color: #fff;
            padding: 12px 24px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.95rem;
            text-decoration: none;
            cursor: pointer;
            border: none;
        }
        .btn-retry:hover { opacity: 0.95; }
    </style>
</head>
<body>
    <div class="offline-card">
        <div class="offline-icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                <path fill-rule="evenodd" d="M1.371 8.143c5.858-5.857 15.356-5.857 21.213 0a.75.75 0 0 1 0 1.061l-.53.53a.75.75 0 0 1-1.06 0c-5.273-5.272-13.82-5.272-19.093 0a.75.75 0 0 1-1.06 0l-.53-.53a.75.75 0 0 1 0-1.06Zm2.829 2.828c4.296-4.296 11.26-4.296 15.556 0a.75.75 0 0 1 0 1.061l-.53.53a.75.75 0 0 1-1.061 0 8.966 8.966 0 0 0-12.374 0 .75.75 0 0 1-1.06 0l-.53-.53a.75.75 0 0 1 0-1.061Zm2.828 2.829a6.002 6.002 0 0 1 8.486 0 .75.75 0 0 1 0 1.06l-.53.531a.75.75 0 0 1-1.061 0 3.998 3.998 0 0 0-5.324 0 .75.75 0 0 1-1.06 0l-.531-.53a.75.75 0 0 1 0-1.061Zm4.243 4.242a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3Z" clip-rule="evenodd"/>
            </svg>
        </div>
        <h1>Tidak Ada Koneksi Internet</h1>
        <p>Aplikasi Logsheet membutuhkan koneksi internet untuk mengirim data stand meter. Periksa Wi-Fi atau data seluler Anda, lalu coba lagi.</p>
        <button onclick="window.location.reload()" class="btn-retry">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M15.312 11.424a5.5 5.5 0 0 1-9.201 2.466l-.312-.311h2.433a.75.75 0 0 0 0-1.5H3.75A.75.75 0 0 0 3 12.828v4.482a.75.75 0 0 0 1.5 0v-2.196l.513.513a7 7 0 1 0 .973-10.74.75.75 0 1 0-.968 1.144 5.5 5.5 0 0 1 10.294 5.393Z" clip-rule="evenodd"/>
            </svg>
            Muat Ulang Halaman
        </button>
    </div>
</body>
</html>
