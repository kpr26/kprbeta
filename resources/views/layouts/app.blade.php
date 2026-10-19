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

    @stack('styles')
</head>
<body>
    @yield('content')

    <!-- PWA Service Worker Registration -->
    <script>
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
    </script>

    @stack('scripts')
</body>
</html>
