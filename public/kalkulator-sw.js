/*
 * Service worker for the /kalkulator app: keeps the calculator working offline.
 * - The page itself: network first (fresh prices), cached copy when offline.
 * - Built assets, fonts, icons, Livewire/Alpine: cache first (file names are versioned).
 */
const CACHE = 'foco-kalkulator-v2';
const PAGE = '/kalkulator';

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE)
            .then((cache) => cache.addAll([PAGE, '/images/logo-160.png', '/images/logo.png', '/images/icon-192.png']))
            .then(() => self.skipWaiting()),
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(keys.filter((k) => k.startsWith('foco-kalkulator-') && k !== CACHE).map((k) => caches.delete(k))))
            .then(() => self.clients.claim()),
    );
});

self.addEventListener('fetch', (event) => {
    const request = event.request;
    if (request.method !== 'GET') return;

    const url = new URL(request.url);
    const sameOrigin = url.origin === self.location.origin;

    // The calculator page: try the network, fall back to the saved copy.
    if (request.mode === 'navigate' && sameOrigin && url.pathname.startsWith(PAGE)) {
        event.respondWith(
            fetch(request)
                .then((response) => {
                    const copy = response.clone();
                    caches.open(CACHE).then((cache) => cache.put(PAGE, copy));
                    return response;
                })
                .catch(() => caches.match(PAGE)),
        );
        return;
    }

    // Static files the page needs.
    const isAsset = sameOrigin && (/^\/(build|images|storage|livewire[^/]*)\//.test(url.pathname) || url.pathname === '/favicon.ico');
    if (isAsset) {
        event.respondWith(
            caches.match(request).then((cached) => cached || fetch(request).then((response) => {
                if (response.ok) {
                    const copy = response.clone();
                    caches.open(CACHE).then((cache) => cache.put(request, copy));
                }
                return response;
            })),
        );
    }
});
