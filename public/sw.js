const CACHE_NAME = 'yoriinapp-v1.1';
const OFFLINE_URL = '/offline.html';

const urlsToCache = [
  '/',
  '/offline.html',
  '/img/logo-small.png',
  '/css/app.css',
  '/js/app.js'
];

// Installation - Met en cache les ressources essentielles
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        console.log('Cache ouvert');
        return cache.addAll(urlsToCache);
      })
      .then(() => self.skipWaiting())
  );
});

// Activation - Nettoie les anciens caches
self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cacheName => {
          if (cacheName !== CACHE_NAME) {
            console.log('Suppression ancien cache:', cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    }).then(() => self.clients.claim())
  );
});

// Fetch - Stratégie Network First avec fallback
self.addEventListener('fetch', event => {
  // Ignorer les requêtes non-GET
  if (event.request.method !== 'GET') return;
  
  event.respondWith(
    fetch(event.request)
      .then(response => {
        // Si la réponse est valide, la mettre en cache
        if (response && response.status === 200) {
          const responseToCache = response.clone();
          caches.open(CACHE_NAME).then(cache => {
            cache.put(event.request, responseToCache);
          });
        }
        return response;
      })
      .catch(() => {
        // En cas d'erreur réseau, chercher dans le cache
        return caches.match(event.request)
          .then(response => {
            if (response) {
              return response;
            }
            // Si navigation et pas en cache, afficher page offline
            if (event.request.mode === 'navigate') {
              return caches.match(OFFLINE_URL);
            }
          });
      })
  );
});
