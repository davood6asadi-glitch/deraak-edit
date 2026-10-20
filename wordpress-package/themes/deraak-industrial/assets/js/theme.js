/**
 * Deraak Industrial Theme Frontend JS
 * Native Vanilla JS - Zero dependencies
 */

document.addEventListener('DOMContentLoaded', function () {
  // 1. Online / Offline Status Detection
  function updateOnlineStatus() {
    var offlineBar = document.getElementById('derak-offline-bar');
    var externalLinks = document.querySelectorAll('[data-requires-net="true"]');

    if (navigator.onLine) {
      if (offlineBar) offlineBar.classList.add('hidden');
      externalLinks.forEach(function (el) {
        el.classList.remove('offline-disabled');
        el.removeAttribute('title');
      });
    } else {
      if (offlineBar) offlineBar.classList.remove('hidden');
      externalLinks.forEach(function (el) {
        el.classList.add('offline-disabled');
        el.setAttribute('title', 'این پیوند نیازمند اتصال به شبکه بین‌الملل است و در حالت آفلاین در دسترس نیست.');
      });
    }
  }

  window.addEventListener('online', updateOnlineStatus);
  window.addEventListener('offline', updateOnlineStatus);
  updateOnlineStatus();

  // 2. Technical Search Interaction
  var searchInput = document.querySelector('.derak-search-input');
  if (searchInput) {
    searchInput.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') {
        searchInput.blur();
      }
    });
  }

  // 3. Quick RFQ Add Handler
  document.addEventListener('click', function (e) {
    var btn = e.target.closest('.derak-btn-icon-add');
    if (!btn) return;

    var productId = btn.getAttribute('data-product-id');
    var productTitle = btn.getAttribute('data-product-title') || 'تجهیز';
    var productModel = btn.getAttribute('data-product-model') || '';

    // Dispatch custom event for deraak-b2b-engine RFQ store
    var event = new CustomEvent('derak_add_to_rfq', {
      detail: {
        id: productId,
        title: productTitle,
        model: productModel
      }
    });
    window.dispatchEvent(event);

    // Visual feedback
    btn.innerHTML = '✓';
    btn.style.backgroundColor = '#93A3FC';
    setTimeout(function () {
      btn.innerHTML = '+';
      btn.style.backgroundColor = '';
    }, 1200);
  });
});
