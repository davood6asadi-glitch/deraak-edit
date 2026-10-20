/**
 * Deraak IndexedDB Catalog Engine & Recently Viewed Handler
 */

(function () {
  'use strict';

  var DB_NAME = 'derak_catalog_db';
  var DB_VERSION = 1;
  var STORE_NAME = 'products';
  var dbInstance = null;

  function openDatabase(callback) {
    if (dbInstance) {
      return callback(null, dbInstance);
    }

    if (!('indexedDB' in window)) {
      return callback('IndexedDB is not supported');
    }

    var request = indexedDB.open(DB_NAME, DB_VERSION);

    request.onupgradeneeded = function (e) {
      var db = e.target.result;
      if (!db.objectStoreNames.contains(STORE_NAME)) {
        var store = db.createObjectStore(STORE_NAME, { keyPath: 'id' });
        store.createIndex('sku', 'sku', { unique: false });
        store.createIndex('brand', 'brand', { unique: false });
      }
    };

    request.onsuccess = function (e) {
      dbInstance = e.target.result;
      callback(null, dbInstance);
    };

    request.onerror = function (e) {
      callback(e.target.error);
    };
  }

  window.DeraakCatalogDB = {
    syncWithServer: function (callback) {
      if (!window.deraakPWA || !window.deraakPWA.catalogApi) {
        if (callback) callback(false, 0);
        return;
      }

      fetch(window.deraakPWA.catalogApi, { credentials: 'same-origin' })
        .then(function (res) {
          if (!res.ok) throw new Error('catalog ' + res.status);
          return res.json();
        })
        .then(function (data) {
          if (!data || !Array.isArray(data.items)) {
            if (callback) callback(false, 0);
            return;
          }

          openDatabase(function (err, db) {
            if (err) {
              if (callback) callback(false, 0);
              return;
            }

            var tx = db.transaction(STORE_NAME, 'readwrite');
            var store = tx.objectStore(STORE_NAME);

            data.items.slice(0, 500).forEach(function (item) {
              if (item && typeof item.id === 'string' && /^\d+$/.test(item.id)) {
                store.put(item);
              }
            });

            tx.oncomplete = function () {
              localStorage.setItem('derak_catalog_last_sync', Date.now().toString());
              if (callback) callback(true, data.items.length);
            };

            tx.onerror = function () {
              if (callback) callback(false, 0);
            };
          });
        })
        .catch(function () {
          if (callback) callback(false, 0);
        });
    },

    getProduct: function (id, callback) {
      openDatabase(function (err, db) {
        if (err) return callback(null);
        var tx = db.transaction(STORE_NAME, 'readonly');
        var store = tx.objectStore(STORE_NAME);
        var req = store.get(id);
        req.onsuccess = function () {
          callback(req.result);
        };
        req.onerror = function () {
          callback(null);
        };
      });
    }
  };

  // Auto-sync catalog in background if older than 24 hours
  var lastSync = localStorage.getItem('derak_catalog_last_sync');
  if (!lastSync || (Date.now() - parseInt(lastSync, 10)) > (24 * 60 * 60 * 1000)) {
    if (navigator.onLine) {
      setTimeout(function () {
        window.DeraakCatalogDB.syncWithServer();
      }, 2500);
    }
  }

  // Cached catalog data is rendered as text only; URLs must be same-origin http(s).
  function safeUrl(value) {
    try {
      var url = new URL(String(value || ''), window.location.origin);
      if ((url.protocol === 'http:' || url.protocol === 'https:') && url.origin === window.location.origin) {
        return url.href;
      }
    } catch (e) {}
    return '#';
  }

  function textOf(value, fallback) {
    return typeof value === 'string' || typeof value === 'number' ? String(value).slice(0, 200) : (fallback || '');
  }

  function node(tag, className, text, ltr) {
    var n = document.createElement(tag);
    if (className) n.className = className;
    if (text !== undefined) n.textContent = text;
    if (ltr) n.dir = 'ltr';
    return n;
  }

  function buildRecentCard(card, product) {
    var link = safeUrl(product.permalink);
    var title = textOf(product.title);
    var p1 = product.param1 || {};
    var p2 = product.param2 || {};

    var top = node('div', 'derak-card-top');
    top.appendChild(node('span', 'derak-card-brand', textOf(product.brand), true));
    top.appendChild(node('span', 'derak-card-badge', textOf(product.statusBadge, 'موجود')));

    var media = node('a', 'derak-card-media');
    media.href = link;
    var img = node('img', 'derak-card-img');
    img.src = safeUrl(product.image);
    img.alt = title;
    img.loading = 'lazy';
    media.appendChild(img);
    media.appendChild(node('span', 'derak-card-tag', textOf(product.tag, 'INDUSTRIAL'), true));

    var titleLink = node('a', 'derak-card-title', title);
    titleLink.href = link;

    var modelWrap = node('div');
    modelWrap.appendChild(node('span', 'derak-card-model', textOf(product.sku), true));

    var matrix = node('div', 'derak-param-matrix');
    matrix.appendChild(node('span', 'derak-param-label', textOf(p1.label)));
    matrix.appendChild(node('span', 'derak-param-val', textOf(p1.value), true));
    matrix.appendChild(node('span', 'derak-param-label', textOf(p2.label)));
    matrix.appendChild(node('span', 'derak-param-val', textOf(p2.value), true));

    var actions = node('div', 'derak-card-actions');
    var add = node('button', 'derak-btn-icon-add', '+');
    add.type = 'button';
    add.setAttribute('aria-label', 'افزودن به استعلام');
    add.setAttribute('data-product-id', textOf(product.id));
    add.setAttribute('data-product-title', title);
    add.setAttribute('data-product-model', textOf(product.sku));
    var detail = node('a', 'derak-btn-card-detail', 'مشاهده دیتاشیت');
    detail.href = link;
    actions.appendChild(add);
    actions.appendChild(detail);

    [top, media, titleLink, modelWrap, matrix, actions].forEach(function (child) {
      card.appendChild(child);
    });
  }

  // 2. Recently Viewed Products Widget Renderer
  function renderRecentlyViewed() {
    var container = document.getElementById('derak-recent-container');
    var emptyState = document.getElementById('derak-recent-empty');
    var badge = document.getElementById('derak-recent-badge');
    var clearBtn = document.getElementById('derak-clear-recent-btn');

    if (!container) return;

    var recentIds = [];
    try {
      recentIds = JSON.parse(localStorage.getItem('derak_recently_viewed') || '[]');
    } catch (e) {
      recentIds = [];
    }

    if (!recentIds || recentIds.length === 0) {
      container.innerHTML = '';
      if (emptyState) emptyState.style.display = 'block';
      if (badge) badge.style.display = 'none';
      if (clearBtn) clearBtn.style.display = 'none';
      return;
    }

    if (emptyState) emptyState.style.display = 'none';
    if (badge) {
      badge.textContent = recentIds.length + ' مورد';
      badge.style.display = 'inline-block';
    }
    if (clearBtn) clearBtn.style.display = 'inline-block';

    container.innerHTML = '';
    recentIds.forEach(function (id) {
      window.DeraakCatalogDB.getProduct(id, function (product) {
        if (!product) return;

        var card = document.createElement('article');
        card.className = 'derak-product-card';
        card.style.minWidth = '260px';
        card.style.maxWidth = '270px';
        card.style.flexShrink = '0';

        buildRecentCard(card, product);
        container.appendChild(card);
      });
    });
  }

  document.addEventListener('DOMContentLoaded', renderRecentlyViewed);

  // Clear button
  document.addEventListener('click', function (e) {
    if (e.target && e.target.id === 'derak-clear-recent-btn') {
      localStorage.removeItem('derak_recently_viewed');
      renderRecentlyViewed();
    }
  });

})();
