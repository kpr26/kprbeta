/* KPR Beta PWA Service Worker */
const CACHE_NAME = 'kprbeta-logsheet-v6';

const STATIC_ASSETS = [
  '/',
  '/?source=pwa',
  '/manifest.json',
  '/offline',
  '/css/app.css',
  '/css/logsheet.css',
  '/js/offline-logsheet.js',
  '/icons/icon.svg',
  '/icons/icon-192.png',
  '/icons/icon-512.png',
  '/screenshots/screenshot-desktop.png',
  '/screenshots/screenshot-mobile.png',
  '/favicon.ico',
  'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap'
];

// Install: Cache static app shell with resilient error handling
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then(async (cache) => {
      await Promise.all(
        STATIC_ASSETS.map((url) =>
          cache.add(url).catch((err) => {
            console.warn('PWA: Precache skipped for ' + url, err);
          })
        )
      );
    }).then(() => self.skipWaiting())
  );
});

// Activate: Clean up old caches
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) => {
      return Promise.all(
        keys.map((key) => {
          if (key !== CACHE_NAME) {
            return caches.delete(key);
          }
        })
      );
    }).then(() => self.clients.claim())
  );
});

// Fetch: Network-first for navigation/HTML, Cache-first / Stale-while-revalidate for static assets
self.addEventListener('fetch', (event) => {
  const req = event.request;

  // Don't intercept non-GET requests (e.g. form submissions, POST/PUT/DELETE)
  if (req.method !== 'GET') {
    return;
  }

  // Handle navigation requests (HTML pages)
  if (req.mode === 'navigate' || (req.headers.get('accept') && req.headers.get('accept').includes('text/html'))) {
    event.respondWith(
      fetch(req).then((networkRes) => {
        // If network returns 200 OK HTML response, cache it for offline viewing
        if (networkRes && networkRes.status === 200) {
          const resClone = networkRes.clone();
          caches.open(CACHE_NAME).then((cache) => cache.put(req, resClone));
        }
        return networkRes;
      }).catch(async () => {
        // 1. Try matching the exact requested URL from cache
        const cachedPage = await caches.match(req);
        if (cachedPage) {
          return cachedPage;
        }

        // 2. Try matching without query string if any
        try {
          const urlObj = new URL(req.url);
          const cleanUrl = urlObj.origin + urlObj.pathname;
          const cachedClean = await caches.match(cleanUrl);
          if (cachedClean) {
            return cachedClean;
          }
        } catch (e) {
          // ignore url parse error
        }

        // 3. Try the dedicated offline fallback page
        const offlinePage = await caches.match('/offline');
        if (offlinePage) {
          return offlinePage;
        }

        // 4. Resilient inline offline HTML fallback
        return new Response(
          `<!DOCTYPE html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Offline - KPR Beta</title><style>body{background:#0a0e1a;color:#fff;font-family:sans-serif;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;padding:20px;text-align:center}.box{max-width:400px;background:#0f172a;padding:30px;border-radius:16px;border:1px solid #1e293b}h2{color:#f59e0b}p{color:#94a3b8}.btn{display:inline-block;margin-top:16px;background:#4f46e5;color:#fff;padding:10px 20px;border-radius:8px;text-decoration:none;cursor:pointer}</style></head><body><div class="box"><h2>Mode Offline</h2><p>Koneksi internet tidak tersedia. Data formulir yang sudah tersimpan di HP akan otomatis disinkronkan saat sinyal internet kembali normal.</p><a href="/logsheet/hub" class="btn">Kembali ke Menu Logsheet</a></div></body></html>`,
          {
            status: 200,
            headers: { 'Content-Type': 'text/html; charset=utf-8' }
          }
        );
      })
    );
    return;
  }

  // Static assets (CSS, JS, Fonts, Images): Cache-first with stale-while-revalidate
  event.respondWith(
    caches.match(req).then((cached) => {
      const fetchPromise = fetch(req).then((networkRes) => {
        if (networkRes && networkRes.status === 200 && networkRes.type === 'basic') {
          const resClone = networkRes.clone();
          caches.open(CACHE_NAME).then((cache) => cache.put(req, resClone));
        }
        return networkRes;
      }).catch(() => null);

      return cached || fetchPromise;
    })
  );
});
