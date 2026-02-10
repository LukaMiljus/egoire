const CACHE_NAME = 'victory-shop-v1';
const urlsToCache = [
  '/',
  '/index.php',
  '/css/style.css',
  '/js/main.js',
  '/images/logo-white.png',
  '/images/logo-white.png'
];

// Instalacija
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => cache.addAll(urlsToCache))
  );
});

// Aktivacija
self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(cacheNames =>
      Promise.all(
        cacheNames.map(name => {
          if (name !== CACHE_NAME) {
            return caches.delete(name);
          }
        })
      )
    )
  );
});

// Fetch offline podrška
self.addEventListener('fetch', event => {
  event.respondWith(
    caches.match(event.request).then(response => response || fetch(event.request))
  );
});
