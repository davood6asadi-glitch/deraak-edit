/**
 * Deraak PWA Install & Offline Controller
 */

(function () {
  'use strict';

  var deferredPrompt = null;
  var isInstalled = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
  var isIOS = /iphone|ipad|ipod/.test(window.navigator.userAgent.toLowerCase());

  // 1. Register Service Worker
  if ('serviceWorker' in navigator && window.deraakPWA) {
    window.addEventListener('load', function () {
      navigator.serviceWorker.register(window.deraakPWA.swUrl, { scope: window.deraakPWA.scope || '/' })
        .then(function (reg) {
          console.log('Deraak SW registered successfully:', reg.scope);
        })
        .catch(function (err) {
          console.warn('Deraak SW registration note:', err);
        });
    });
  }

  // 2. Capture beforeinstallprompt
  window.addEventListener('beforeinstallprompt', function (e) {
    e.preventDefault();
    deferredPrompt = e;
    var btn = document.getElementById('derak-pwa-install-btn');
    if (btn) btn.style.display = 'inline-flex';
  });

  window.addEventListener('appinstalled', function () {
    deferredPrompt = null;
    isInstalled = true;
    var btn = document.getElementById('derak-pwa-install-btn');
    if (btn) btn.style.display = 'none';
  });

  // 3. Install Button Click Handler
  document.addEventListener('click', function (e) {
    var btn = e.target.closest('#derak-pwa-install-btn');
    if (!btn) return;

    if (deferredPrompt) {
      deferredPrompt.prompt();
      deferredPrompt.userChoice.then(function (choice) {
        if (choice.outcome === 'accepted') {
          btn.style.display = 'none';
        }
        deferredPrompt = null;
      });
    } else if (isIOS) {
      alert('راهنمای نصب در آیفون (iOS Safari):\n\n۱. در پایین مرورگر سافاری روی دکمه اشتراک‌گذاری (Share) بزنید.\n۲. منو را اسکرول کرده و گزینه «Add to Home Screen» (افزودن به صفحه اصلی) را لمس کنید.\n۳. در بالای صفحه روی «Add» کلیک کنید.');
    } else {
      alert('اپلیکیشن دراک آماده کار است. در منوی مرورگر خود گزینه «Install App» یا «Add to Home screen» را انتخاب فرمایید.');
    }
  });

  // 4. Sync Button Handler
  document.addEventListener('click', function (e) {
    var syncBtn = e.target.closest('#derak-pwa-sync-btn');
    if (!syncBtn) return;

    if (window.DeraakCatalogDB) {
      syncBtn.disabled = true;
      syncBtn.innerHTML = '🔄 در حال دریافت کاتالوگ...';

      window.DeraakCatalogDB.syncWithServer(function (success, count) {
        syncBtn.disabled = false;
        if (success) {
          syncBtn.innerHTML = '✓ بروزرسانی شد (' + count + ' تجهیز)';
          var label = document.getElementById('derak-pwa-sync-label');
          if (label) label.textContent = 'کاتالوگ همگام‌سازی شد (' + count + ' مورد)';
        } else {
          syncBtn.innerHTML = 'تلاش مجدد همگام‌سازی';
        }
      });
    }
  });

})();
