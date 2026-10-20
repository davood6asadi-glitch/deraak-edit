/**
 * Deraak Industrial B2B Fallback Service Worker Asset
 * Primary root-scoped Service Worker is dynamically served at /?deraak_sw=1 with Service-Worker-Allowed: /
 * Safe cache namespacing: Never deletes caches belonging to other plugins or applications.
 */

const CACHE_PREFIX = 'derak-pwa-';
const SHELL_CACHE = CACHE_PREFIX + 'shell-v1.1.0';
const ASSET_CACHE = CACHE_PREFIX + 'assets-v1.1.0';
const ACTIVE_CACHES = [SHELL_CACHE, ASSET_CACHE];

const PRECACHE_ASSETS = [
  '/',
  '/wp-content/themes/deraak-industrial/style.css',
  '/wp-content/themes/deraak-industrial/rtl.css',
  '/wp-content/themes/deraak-industrial/assets/css/theme.css',
  '/wp-content/themes/deraak-industrial/assets/js/theme.js',
  '/wp-content/themes/deraak-industrial/assets/fonts/ibm-plex-sans-arabic/IBMPlexSansArabic-Regular.woff2',
  '/wp-content/themes/deraak-industrial/assets/fonts/ibm-plex-sans-arabic/IBMPlexSansArabic-Medium.woff2',
  '/wp-content/themes/deraak-industrial/assets/fonts/ibm-plex-sans-arabic/IBMPlexSansArabic-SemiBold.woff2',
  '/wp-content/themes/deraak-industrial/assets/fonts/ibm-plex-sans-arabic/IBMPlexSansArabic-Bold.woff2',
  '/wp-content/themes/deraak-industrial/assets/images/icon.svg'
];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(SHELL_CACHE).then(cache => {
      return Promise.allSettled(
        PRECACHE_ASSETS.map(url => cache.add(url).catch(() => {}))
      );
    }).then(() => self.skipWaiting())
  );
});

self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(keys => {
      return Promise.all(
        keys.map(key => {
          // Only delete outdated Deraak caches; preserve all unrelated origin caches
          if (key.indexOf(CACHE_PREFIX) === 0 && ACTIVE_CACHES.indexOf(key) === -1) {
            return caches.delete(key);
          }
          return Promise.resolve();
        })
      );
    }).then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', event => {
  if (event.request.method !== 'GET') return;

  const url = new URL(event.request.url);
  if (url.origin !== self.location.origin) return;

  if (
    url.pathname.includes('/wp-admin') ||
    url.pathname.includes('/wp-login') ||
    url.pathname.includes('/wp-json/') ||
    url.search.includes('wc-ajax=')
  ) {
    return;
  }

  if (/\.(woff2|woff|ttf|css|js|svg|png|jpg|jpeg|webp)$/i.test(url.pathname)) {
    event.respondWith(
      caches.match(event.request).then(cachedResponse => {
        if (cachedResponse) return cachedResponse;
        return fetch(event.request).then(networkResponse => {
          if (networkResponse && networkResponse.status === 200) {
            const clone = networkResponse.clone();
            caches.open(ASSET_CACHE).then(cache => cache.put(event.request, clone));
          }
          return networkResponse;
        }).catch(() => {});
      })
    );
    return;
  }

  if (event.request.mode === 'navigate') {
    event.respondWith(
      fetch(event.request).then(networkResponse => {
        if (networkResponse && networkResponse.status === 200) {
          const clone = networkResponse.clone();
          caches.open(SHELL_CACHE).then(cache => cache.put(event.request, clone));
        }
        return networkResponse;
      }).catch(() => {
        return caches.match(event.request).then(cached => cached || caches.match('/'));
      })
    );
  }
});
