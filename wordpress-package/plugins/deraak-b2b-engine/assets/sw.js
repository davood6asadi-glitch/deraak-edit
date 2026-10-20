/**
 * Deraak Industrial B2B Service Worker
 * Root-scoped, production-ready, safe for WooCommerce & Caching plugins
 */

const CACHE_NAME = 'derak-pwa-v1.0.0';

// Essential Shell Pre-cache
const PRECACHE_ASSETS = [
  '/',
  '/manifest.json',
  '/wp-content/themes/deraak-industrial/style.css',
  '/wp-content/themes/deraak-industrial/rtl.css',
  '/wp-content/themes/deraak-industrial/assets/css/theme.css',
  '/wp-content/themes/deraak-industrial/assets/js/theme.js',
  '/wp-content/themes/deraak-industrial/assets/fonts/vazirmatn/Vazirmatn-Regular.woff2',
  '/wp-content/themes/deraak-industrial/assets/fonts/vazirmatn/Vazirmatn-Bold.woff2',
  '/wp-content/themes/deraak-industrial/assets/fonts/jetbrains-mono/JetBrainsMono-Regular.woff2',
  '/wp-content/themes/deraak-industrial/assets/images/icon.svg'
];

// Install Event
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => {
      return cache.addAll(PRECACHE_ASSETS).catch(err => {
        console.warn('Deraak SW precache note:', err);
      });
    }).then(() => self.skipWaiting())
  );
});

// Activate Event
self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(keys => {
      return Promise.all(
        keys.map(key => {
          if (key !== CACHE_NAME) {
            return caches.delete(key);
          }
        })
      );
    }).then(() => self.clients.claim())
  );
});

// Fetch Event
self.addEventListener('fetch', event => {
  const url = new URL(event.request.url);

  // 1. Never cache non-GET requests
  if (event.request.method !== 'GET') return;

  // 2. Strict exclusions: Admin, Login, Cart, Checkout, AJAX, Nonces
  if (
    url.pathname.includes('/wp-admin') ||
    url.pathname.includes('/wp-login') ||
    url.pathname.includes('/cart') ||
    url.pathname.includes('/checkout') ||
    url.pathname.includes('/my-account') ||
    url.search.includes('wc-ajax=') ||
    url.search.includes('add-to-cart=')
  ) {
    return; // Pass through directly to network
  }

  // 3. Static assets: Cache-First Strategy
  if (
    url.pathname.match(/\.(css|js|woff2|woff|ttf|svg|png|jpg|jpeg|webp)$/i)
  ) {
    event.respondWith(
      caches.match(event.request).then(cachedResponse => {
        if (cachedResponse) {
          return cachedResponse;
        }
        return fetch(event.request).then(networkResponse => {
          if (networkResponse && networkResponse.status === 200) {
            const clone = networkResponse.clone();
            caches.open(CACHE_NAME).then(cache => cache.put(event.request, clone));
          }
          return networkResponse;
        }).catch(() => {});
      })
    );
    return;
  }

  // 4. HTML Navigation: Network-First with Cache Fallback
  if (event.request.mode === 'navigate') {
    event.respondWith(
      fetch(event.request).then(networkResponse => {
        if (networkResponse && networkResponse.status === 200) {
          const clone = networkResponse.clone();
          caches.open(CACHE_NAME).then(cache => cache.put(event.request, clone));
        }
        return networkResponse;
      }).catch(() => {
        return caches.match(event.request).then(cached => {
          if (cached) return cached;
          return caches.match('/'); // Return homepage shell if available
        });
      })
    );
  }
});
