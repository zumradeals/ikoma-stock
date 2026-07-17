const CACHE_SHELL = 'ikoma-shell-v1';
const CACHE_RUNTIME = 'ikoma-runtime-v1';

const SHELL_ASSETS = [
    '/offline.html',
    '/manifest.json',
    '/icons/icon-192.png',
    '/icons/icon-512.png',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_SHELL).then((cache) => cache.addAll(SHELL_ASSETS))
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => Promise.all(
            keys
                .filter((key) => key !== CACHE_SHELL && key !== CACHE_RUNTIME)
                .map((key) => caches.delete(key))
        ))
    );
    self.clients.claim();
});

function isNeverCached(url) {
    return url.pathname.startsWith('/livewire')
        || url.pathname.includes('/telecharger')
        || url.pathname.endsWith('.pdf')
        || url.pathname.startsWith('/login')
        || url.pathname.startsWith('/logout');
}

function isRuntimeCacheable(url) {
    return url.origin === self.location.origin
        && (url.pathname.startsWith('/build/') || url.pathname.startsWith('/icons/'));
}

self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    if (request.method !== 'GET' || isNeverCached(url)) {
        return;
    }

    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request).catch(() =>
                caches.match(request).then((cached) => cached || caches.match('/offline.html'))
            )
        );
        return;
    }

    if (isRuntimeCacheable(url)) {
        event.respondWith(
            caches.open(CACHE_RUNTIME).then((cache) =>
                cache.match(request).then((cached) => {
                    const fetchPromise = fetch(request).then((response) => {
                        cache.put(request, response.clone());
                        return response;
                    }).catch(() => cached);

                    return cached || fetchPromise;
                })
            )
        );
    }
});
