/**
 * Deraak IndexedDB Catalog Engine, Multi-Page Sync (>100 products),
 * Offline Search/Filter Browser, Stale-Data Detector & Recently Viewed Handler
 *
 * Security: All IndexedDB records are rendered via DOM textContent/setAttribute (never innerHTML).
 */

(function () {
  'use strict';

  var DB_NAME = 'derak_catalog_db';
  var DB_VERSION = 2;
  var STORE_NAME = 'products';
  var IMAGE_CACHE = 'derak-pwa-images-v1';
  var MAX_PRODUCTS = 2500;
  var dbInstance = null;

  function openDatabase(callback) {
    if (dbInstance) {
      return callback(null, dbInstance);
    }

    if (!('indexedDB' in window)) {
      return callback(new Error('IndexedDB is not supported'));
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
      callback(e.target.error || new Error('IndexedDB open error'));
    };
  }

  function safeUrl(value, allowExternalHttps) {
    try {
      var raw = String(value || '').trim();
      if (!raw) return '#';
      var url = new URL(raw, window.location.origin);
      if (url.protocol !== 'http:' && url.protocol !== 'https:') return '#';
      if (allowExternalHttps && url.protocol === 'https:') return url.href;
      if (url.origin === window.location.origin) return url.href;
    } catch (e) {}
    return '#';
  }

  function textOf(value, fallback, maxLen) {
    if (typeof value !== 'string' && typeof value !== 'number') {
      return fallback || '';
    }
    return String(value).replace(/[\u0000-\u001F\u007F]/g, ' ').trim().slice(0, maxLen || 240);
  }

  function sanitizeRecord(raw) {
    if (!raw || typeof raw !== 'object') return null;
    var id = textOf(raw.id, '', 64);
    if (!/^[A-Za-z0-9_-]+$/.test(id)) return null;

    var cats = Array.isArray(raw.categories)
      ? raw.categories.slice(0, 10).map(function (c) {
          return {
            slug: textOf(c && c.slug, '', 80),
            name: textOf(c && c.name, '', 80)
          };
        }).filter(function (c) { return c.name !== ''; })
      : [];

    var specs = Array.isArray(raw.specs)
      ? raw.specs.slice(0, 12).map(function (s) {
          return {
            label: textOf(s && s.label, '', 80),
            value: textOf(s && s.value, '', 140)
          };
        }).filter(function (s) { return s.label !== '' && s.value !== ''; })
      : [];

    return {
      id: id,
      sku: textOf(raw.sku, '', 80),
      title: textOf(raw.title, 'تجهیز صنعتی', 200),
      excerpt: textOf(raw.excerpt, '', 400),
      brand: textOf(raw.brand, '', 80),
      categories: cats,
      statusBadge: textOf(raw.statusBadge, '', 60),
      tag: textOf(raw.tag, '', 40),
      leadTime: textOf(raw.leadTime, '', 120),
      isDemo: raw.isDemo === true,
      param1: {
        label: textOf(raw.param1 && raw.param1.label, '', 60),
        value: textOf(raw.param1 && raw.param1.value, '', 100)
      },
      param2: {
        label: textOf(raw.param2 && raw.param2.label, '', 60),
        value: textOf(raw.param2 && raw.param2.value, '', 100)
      },
      specs: specs,
      datasheetUrl: safeUrl(raw.datasheetUrl, false),
      destinationUrl: safeUrl(raw.destinationUrl, true),
      permalink: safeUrl(raw.permalink, false),
      image: safeUrl(raw.image, false)
    };
  }

  function fetchAllCatalogPages(baseUrl, page, accumulated, onDone) {
    var sep = baseUrl.indexOf('?') === -1 ? '?' : '&';
    var url = baseUrl + sep + 'per_page=100&page=' + page;

    fetch(url, { credentials: 'same-origin' })
      .then(function (res) {
        if (!res.ok) throw new Error('HTTP ' + res.status);
        return res.json();
      })
      .then(function (data) {
        if (!data || !Array.isArray(data.items)) {
          return onDone(new Error('Invalid catalog payload'), []);
        }
        var nextList = accumulated.concat(data.items).slice(0, MAX_PRODUCTS);
        var totalPages = parseInt(data.totalPages, 10) || 1;
        if (page < totalPages && nextList.length < MAX_PRODUCTS) {
          fetchAllCatalogPages(baseUrl, page + 1, nextList, onDone);
        } else {
          onDone(null, nextList, data.version || '1.1.0');
        }
      })
      .catch(function (err) {
        if (accumulated.length > 0) {
          onDone(null, accumulated, '1.1.0');
        } else {
          onDone(err, []);
        }
      });
  }

  function cacheProductImages(items) {
    if (!('caches' in window) || !Array.isArray(items)) return;
    caches.open(IMAGE_CACHE).then(function (cache) {
      items.slice(0, 150).forEach(function (item) {
        if (item.image && item.image !== '#') {
          cache.add(item.image).catch(function () {});
        }
      });
    }).catch(function () {});
  }

  window.DeraakCatalogDB = {
    syncWithServer: function (callback) {
      if (!window.deraakPWA || !window.deraakPWA.catalogApi) {
        if (callback) callback(false, 0);
        return;
      }

      fetchAllCatalogPages(window.deraakPWA.catalogApi, 1, [], function (err, rawItems, version) {
        if (err || !Array.isArray(rawItems)) {
          if (callback) callback(false, 0);
          return;
        }

        var validItems = rawItems.map(sanitizeRecord).filter(Boolean);

        openDatabase(function (dbErr, db) {
          if (dbErr || !db) {
            if (callback) callback(false, 0);
            return;
          }

          var tx = db.transaction(STORE_NAME, 'readwrite');
          var store = tx.objectStore(STORE_NAME);
          store.clear();

          validItems.forEach(function (item) {
            store.put(item);
          });

          tx.oncomplete = function () {
            var now = Date.now();
            try {
              localStorage.setItem('derak_catalog_last_sync', String(now));
              localStorage.setItem('derak_catalog_count', String(validItems.length));
              localStorage.setItem('derak_catalog_version', String(version || '1.1.0'));
            } catch (e) {}

            cacheProductImages(validItems);
            updateSyncIndicators();
            populateOfflineFilters(validItems);
            renderOfflineCatalogResults();
            renderRecentlyViewed();

            if (callback) callback(true, validItems.length);
          };

          tx.onerror = function () {
            if (callback) callback(false, 0);
          };
        });
      });
    },

    getProduct: function (id, callback) {
      openDatabase(function (err, db) {
        if (err || !db) return callback(null);
        var tx = db.transaction(STORE_NAME, 'readonly');
        var store = tx.objectStore(STORE_NAME);
        var req = store.get(String(id));
        req.onsuccess = function () {
          callback(req.result || null);
        };
        req.onerror = function () {
          callback(null);
        };
      });
    },

    getAllProducts: function (callback) {
      openDatabase(function (err, db) {
        if (err || !db) return callback([]);
        var tx = db.transaction(STORE_NAME, 'readonly');
        var store = tx.objectStore(STORE_NAME);
        var req = store.getAll();
        req.onsuccess = function () {
          callback(Array.isArray(req.result) ? req.result : []);
        };
        req.onerror = function () {
          callback([]);
        };
      });
    },

    searchProducts: function (filters, callback) {
      var q = textOf(filters && filters.query, '', 100).toLowerCase();
      var brand = textOf(filters && filters.brand, '', 80);
      var cat = textOf(filters && filters.category, '', 80);

      window.DeraakCatalogDB.getAllProducts(function (all) {
        var matched = all.filter(function (p) {
          if (brand && p.brand !== brand) return false;
          if (cat) {
            var hasCat = (p.categories || []).some(function (c) {
              return c.slug === cat || c.name === cat;
            });
            if (!hasCat) return false;
          }
          if (!q) return true;
          var haystack = [
            p.title,
            p.sku,
            p.brand,
            p.tag,
            p.excerpt,
            p.param1 && p.param1.value,
            p.param2 && p.param2.value
          ].join(' ').toLowerCase();
          return haystack.indexOf(q) !== -1;
        });
        callback(matched);
      });
    }
  };

  function formatSyncTimestamp(ms) {
    var num = parseInt(ms, 10);
    if (!num || !isFinite(num)) return 'ثبت نشده';
    try {
      return new Date(num).toLocaleString('fa-IR', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
      });
    } catch (e) {
      return new Date(num).toISOString().slice(0, 16).replace('T', ' ');
    }
  }

  function updateSyncIndicators() {
    var lastSync = localStorage.getItem('derak_catalog_last_sync');
    var count = parseInt(localStorage.getItem('derak_catalog_count') || '0', 10);
    var labelEl = document.getElementById('derak-pwa-sync-label');
    var timeEl = document.getElementById('derak-pwa-last-sync');
    var staleEl = document.getElementById('derak-pwa-stale-warning');
    var offlineBarSync = document.getElementById('derak-offline-last-sync');

    var formattedTime = formatSyncTimestamp(lastSync);
    if (timeEl) {
      timeEl.textContent = 'آخرین همگام‌سازی: ' + formattedTime;
    }
    if (offlineBarSync) {
      offlineBarSync.textContent = lastSync ? ' (آخرین همگام‌سازی: ' + formattedTime + ')' : '';
    }

    if (labelEl) {
      if (lastSync) {
        labelEl.textContent = count + ' تجهیز در حافظه آفلاین آماده است';
      } else {
        labelEl.textContent = 'هنوز همگام‌سازی نشده است';
      }
    }

    var staleHours = (window.deraakPWA && window.deraakPWA.staleHours) || 24;
    var isStale = !lastSync || (Date.now() - parseInt(lastSync, 10)) > (staleHours * 3600 * 1000);
    if (staleEl) {
      staleEl.style.display = (!navigator.onLine || (lastSync && isStale)) ? 'block' : 'none';
    }
  }

  function node(tag, className, text, ltr) {
    var n = document.createElement(tag);
    if (className) n.className = className;
    if (text !== undefined && text !== null) n.textContent = String(text);
    if (ltr) n.dir = 'ltr';
    return n;
  }

  function buildProductCardDOM(product) {
    var card = node('article', 'derak-product-card');
    var link = safeUrl(product.permalink, false);
    var title = textOf(product.title, 'تجهیز صنعتی');
    var p1 = product.param1 || {};
    var p2 = product.param2 || {};

    var top = node('div', 'derak-card-top');
    top.appendChild(node('span', 'derak-card-brand', textOf(product.brand, 'DERAAK'), true));
    if (product.statusBadge) {
      top.appendChild(node('span', 'derak-card-badge', textOf(product.statusBadge)));
    } else if (product.isDemo) {
      top.appendChild(node('span', 'derak-card-badge', 'نمونه آزمایشی'));
    }

    var media = node('a', 'derak-card-media');
    media.href = link;
    var img = node('img', 'derak-card-img');
    img.src = product.image && product.image !== '#'
      ? product.image
      : ((window.deraakPWA && window.deraakPWA.fallbackImg) || '');
    img.alt = title;
    img.loading = 'lazy';
    media.appendChild(img);
    if (product.tag) {
      media.appendChild(node('span', 'derak-card-tag', textOf(product.tag), true));
    }

    var titleLink = node('a', 'derak-card-title', title);
    titleLink.href = link;

    var modelWrap = node('div');
    if (product.sku) {
      modelWrap.appendChild(node('span', 'derak-card-model', textOf(product.sku), true));
    }

    card.appendChild(top);
    card.appendChild(media);
    card.appendChild(titleLink);
    card.appendChild(modelWrap);

    if (p1.value || p2.value) {
      var matrix = node('div', 'derak-param-matrix');
      if (p1.value) {
        matrix.appendChild(node('span', 'derak-param-label', textOf(p1.label, 'مشخصه ۱:')));
        matrix.appendChild(node('span', 'derak-param-val', textOf(p1.value), true));
      }
      if (p2.value) {
        matrix.appendChild(node('span', 'derak-param-label', textOf(p2.label, 'مشخصه ۲:')));
        matrix.appendChild(node('span', 'derak-param-val', textOf(p2.value), true));
      }
      card.appendChild(matrix);
    }

    var actions = node('div', 'derak-card-actions');
    var add = node('button', 'derak-btn-icon-add', '+');
    add.type = 'button';
    add.setAttribute('aria-label', 'افزودن به لیست استعلام');
    add.setAttribute('data-product-id', textOf(product.id));
    add.setAttribute('data-product-title', title);
    add.setAttribute('data-product-model', textOf(product.sku));

    var detail = node('a', 'derak-btn-card-detail', 'مشاهده جزئیات');
    detail.href = link;

    actions.appendChild(add);
    actions.appendChild(detail);
    card.appendChild(actions);

    return card;
  }

  function populateOfflineFilters(items) {
    var brandSelect = document.getElementById('derak-offline-brand-filter');
    var catSelect = document.getElementById('derak-offline-cat-filter');
    if (!brandSelect && !catSelect) return;

    var brands = {};
    var cats = {};
    (items || []).forEach(function (p) {
      if (p.brand) brands[p.brand] = true;
      (p.categories || []).forEach(function (c) {
        if (c.name) cats[c.name] = true;
      });
    });

    if (brandSelect) {
      var currentB = brandSelect.value;
      brandSelect.replaceChildren(node('option', '', 'همه برندها'));
      brandSelect.firstChild.value = '';
      Object.keys(brands).sort().forEach(function (b) {
        var opt = node('option', '', b);
        opt.value = b;
        brandSelect.appendChild(opt);
      });
      brandSelect.value = currentB;
    }

    if (catSelect) {
      var currentC = catSelect.value;
      catSelect.replaceChildren(node('option', '', 'همه دسته‌ها'));
      catSelect.firstChild.value = '';
      Object.keys(cats).sort().forEach(function (c) {
        var opt = node('option', '', c);
        opt.value = c;
        catSelect.appendChild(opt);
      });
      catSelect.value = currentC;
    }
  }

  function renderOfflineCatalogResults() {
    var resultsEl = document.getElementById('derak-offline-catalog-results');
    if (!resultsEl) return;

    var searchInput = document.getElementById('derak-offline-search-input');
    var brandSelect = document.getElementById('derak-offline-brand-filter');
    var catSelect = document.getElementById('derak-offline-cat-filter');

    window.DeraakCatalogDB.searchProducts({
      query: searchInput ? searchInput.value : '',
      brand: brandSelect ? brandSelect.value : '',
      category: catSelect ? catSelect.value : ''
    }, function (items) {
      resultsEl.replaceChildren();
      if (!items || items.length === 0) {
        var empty = node('p', '', 'تجهیزی با این مشخصات در حافظه آفلاین یافت نشد.');
        empty.style.fontSize = '13px';
        empty.style.color = 'var(--blue-300)';
        resultsEl.appendChild(empty);
        return;
      }
      items.forEach(function (item) {
        resultsEl.appendChild(buildProductCardDOM(item));
      });
    });
  }

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

    if (!Array.isArray(recentIds) || recentIds.length === 0) {
      container.replaceChildren();
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

    container.replaceChildren();
    recentIds.slice(0, 12).forEach(function (id) {
      window.DeraakCatalogDB.getProduct(id, function (product) {
        if (!product) return;
        var card = buildProductCardDOM(product);
        card.style.minWidth = '260px';
        card.style.maxWidth = '270px';
        card.style.flexShrink = '0';
        container.appendChild(card);
      });
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    updateSyncIndicators();
    renderRecentlyViewed();

    window.DeraakCatalogDB.getAllProducts(function (items) {
      populateOfflineFilters(items);
      renderOfflineCatalogResults();
    });

    // Auto-sync when online if never synced or version changed or >24h old
    var lastSync = localStorage.getItem('derak_catalog_last_sync');
    var savedVer = localStorage.getItem('derak_catalog_version');
    var targetVer = window.deraakPWA && window.deraakPWA.version;
    var needsSync = !lastSync
      || (targetVer && savedVer !== targetVer)
      || (Date.now() - parseInt(lastSync, 10)) > (24 * 3600 * 1000);

    if (needsSync && navigator.onLine) {
      setTimeout(function () {
        window.DeraakCatalogDB.syncWithServer();
      }, 1200);
    }

    var searchInput = document.getElementById('derak-offline-search-input');
    var brandSelect = document.getElementById('derak-offline-brand-filter');
    var catSelect = document.getElementById('derak-offline-cat-filter');

    if (searchInput) searchInput.addEventListener('input', renderOfflineCatalogResults);
    if (brandSelect) brandSelect.addEventListener('change', renderOfflineCatalogResults);
    if (catSelect) catSelect.addEventListener('change', renderOfflineCatalogResults);
  });

  window.addEventListener('online', function () {
    updateSyncIndicators();
    window.DeraakCatalogDB.syncWithServer();
  });

  window.addEventListener('offline', function () {
    updateSyncIndicators();
  });

  document.addEventListener('click', function (e) {
    var target = e.target instanceof Element ? e.target : null;
    if (!target) return;

    if (target.id === 'derak-clear-recent-btn') {
      localStorage.removeItem('derak_recently_viewed');
      renderRecentlyViewed();
    }
  });
})();
