/**
 * Deraak PWA Install, Root-Scoped Service Worker Registration & Offline Catalog Toggle
 */

(function () {
  'use strict';

  var deferredPrompt = null;
  var isInstalled = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
  var isIOS = /iphone|ipad|ipod/.test(window.navigator.userAgent.toLowerCase());

  // 1. Register Root-Scoped Service Worker
  if ('serviceWorker' in navigator && window.deraakPWA && window.deraakPWA.swUrl) {
    window.addEventListener('load', function () {
      navigator.serviceWorker
        .register(window.deraakPWA.swUrl, { scope: window.deraakPWA.scope || '/' })
        .catch(function () {});
    });
  }

  // 2. Capture beforeinstallprompt
  window.addEventListener('beforeinstallprompt', function (e) {
    e.preventDefault();
    deferredPrompt = e;
    var btn = document.getElementById('derak-pwa-install-btn');
    if (btn && !isInstalled) {
      btn.style.display = 'inline-flex';
    }
  });

  window.addEventListener('appinstalled', function () {
    deferredPrompt = null;
    isInstalled = true;
    var btn = document.getElementById('derak-pwa-install-btn');
    if (btn) btn.style.display = 'none';
  });

  // 3. Handle PWA Install, Sync & Offline Catalog Browse Buttons
  document.addEventListener('click', function (e) {
    var target = e.target instanceof Element ? e.target : null;
    if (!target) return;

    var installBtn = target.closest('#derak-pwa-install-btn');
    if (installBtn) {
      if (deferredPrompt) {
        deferredPrompt.prompt();
        deferredPrompt.userChoice.then(function (choice) {
          if (choice.outcome === 'accepted') {
            installBtn.style.display = 'none';
          }
          deferredPrompt = null;
        });
      } else {
        var label = document.getElementById('derak-pwa-sync-label');
        if (label) {
          label.textContent = isIOS
            ? 'در سافاری آیفون: دکمه Share و سپس Add to Home Screen را بزنید.'
            : 'از منوی مرورگر گزینه Install App / Add to Home Screen را انتخاب کنید.';
        }
      }
      return;
    }

    var syncBtn = target.closest('#derak-pwa-sync-btn');
    if (syncBtn && window.DeraakCatalogDB) {
      if (!navigator.onLine) {
        var syncLbl = document.getElementById('derak-pwa-sync-label');
        if (syncLbl) {
          syncLbl.textContent = 'در حالت آفلاین امکان دریافت نسخه جدید از سرور وجود ندارد.';
        }
        return;
      }

      syncBtn.disabled = true;
      syncBtn.textContent = 'در حال همگام‌سازی کاتالوگ...';

      window.DeraakCatalogDB.syncWithServer(function (success, count) {
        syncBtn.disabled = false;
        if (success) {
          syncBtn.textContent = 'همگام‌سازی انجام شد (' + count + ' تجهیز)';
        } else {
          syncBtn.textContent = 'تلاش مجدد همگام‌سازی';
        }
      });
      return;
    }

    var browseBtn = target.closest('#derak-pwa-browse-btn');
    if (browseBtn) {
      var panel = document.getElementById('derak-offline-catalog-panel');
      if (panel) {
        var isHidden = panel.style.display === 'none' || !panel.style.display;
        panel.style.display = isHidden ? 'block' : 'none';
      }
    }
  });
})();
