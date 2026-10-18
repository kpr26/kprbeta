/* KPR Beta PWA Service Worker */
const CACHE_NAME = 'kprbeta-logsheet-v1';
const STATIC_ASSETS = [
  '/',
  '/manifest.json',
  '/css/app.css',
  '/css/logsheet.css',
  '/icons/icon.svg',
  '/icons/icon-192.png',
  '/icons/icon-512.png',
  '/offline',
  'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap'
];

// Install: Cache static app shell
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      return cache.addAll(STATIC_ASSETS).catch((err) => {
        console.warn('PWA: Some assets failed to precache', err);
      });
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

// Fetch: Network-first for navigation/pages, Cache-first/Stale-while-revalidate for static assets
self.addEventListener('fetch', (event) => {
  const req = event.request;

  // Don't intercept POST/PUT/DELETE requests (like submitting logsheet or logout)
  if (req.method !== 'GET') {
    return;
  }

  const url = new URL(req.url);

  // If HTML document request: Network first, fall back to offline page if network fails
  if (req.mode === 'navigate' || (req.headers.get('accept') && req.headers.get('accept').includes('text/html'))) {
    event.respondWith(
      fetch(req).catch(() => {
        return caches.match('/offline').then((res) => {
          return res || new Response(
            `<!DOCTYPE html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Offline - KPR Beta</title><style>body{background:#0a0e1a;color:#fff;font-family:sans-serif;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;padding:20px;text-align:center}.box{max-width:400px;background:#0f172a;padding:30px;border-radius:16px;border:1px solid #1e293b}h2{color:#f59e0b}p{color:#94a3b8}.btn{display:inline-block;margin-top:16px;background:#4f46e5;color:#fff;padding:10px 20px;border-radius:8px;text-decoration:none}</style></head><body><div class="box"><h2>Mode Offline</h2><p>Koneksi internet tidak tersedia. Pastikan perangkat Anda terhubung ke Wi-Fi atau data seluler untuk mengakses logsheet.</p><a href="/logsheet/create" class="btn" onclick="location.reload();return false;">Coba Lagi</a></div></body></html>`,
            { headers: { 'Content-Type': 'text/html; charset=utf-8' } }
          );
        });
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
