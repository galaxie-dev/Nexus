const CACHE_NAME = 'nexus-cache-v1';
const urlsToCache = ['/', '/index_after.php', '/style.css', '/assets/js/app.js'];
self.addEventListener('install', event => {
    event.waitUntil(caches.open(CACHE_NAME).then(cache => cache.addAll(urlsToCache)));
});
self.addEventListener('activate', event => {
    event.waitUntil(caches.keys().then(names => Promise.all(names.filter(name => name !== CACHE_NAME).map(name => caches.delete(name)))));
});
self.addEventListener('fetch', event => {
    event.respondWith(caches.match(event.request).then(response => response || fetch(event.request).catch(() => {
        if (event.request.url.includes('/index_after.php')) {
            return caches.match('/index_after.php') || new Response('Offline page not found', { status: 404 });
        }
        return new Response('Resource not available offline', { status: 404 });
    })));
});