/**
 * Deraak Industrial B2B Theme Interactions
 * - تغییر حالت روشن و تیره (Light / Dark Mode)
 * - منوی موبایل
 * - نشانگر وضعیت آنلاین/آفلاین
 * - فیلتر آنی دسته‌بندی، برند و جستجوی کاتالوگ
 * - مودال مشاهده سریع مشخصات فنی محصول (Quick View)
 */
(function () {
	'use strict';

	document.addEventListener('DOMContentLoaded', function () {
		initThemeModeToggle();
		initMobileMenu();
		initNetworkStatusBadge();
		initCatalogFilters();
		initQuickViewModal();
	});

	function initThemeModeToggle() {
		var toggleBtn = document.getElementById('deraak-theme-toggle');
		var iconEl = document.getElementById('deraak-theme-toggle-icon');
		var root = document.documentElement;

		function syncIcon() {
			var isDark = root.classList.contains('dark') || root.getAttribute('data-theme') === 'dark';
			if (iconEl) {
				iconEl.textContent = isDark ? '☀' : '☾';
			}
			if (toggleBtn) {
				toggleBtn.setAttribute('aria-pressed', isDark ? 'true' : 'false');
			}
		}

		syncIcon();

		if (!toggleBtn) return;

		toggleBtn.addEventListener('click', function () {
			var isCurrentlyDark = root.classList.contains('dark');
			if (isCurrentlyDark) {
				root.classList.remove('dark');
				root.setAttribute('data-theme', 'light');
				try { localStorage.setItem('deraak-theme-mode', 'light'); } catch (e) {}
			} else {
				root.classList.add('dark');
				root.setAttribute('data-theme', 'dark');
				try { localStorage.setItem('deraak-theme-mode', 'dark'); } catch (e) {}
			}
			syncIcon();
		});
	}

	function initMobileMenu() {
		var toggleBtn = document.getElementById('deraak-mobile-menu-toggle');
		var drawer = document.getElementById('deraak-mobile-nav-drawer');
		if (!toggleBtn || !drawer) return;

		toggleBtn.addEventListener('click', function () {
			var isOpen = drawer.classList.toggle('is-open');
			toggleBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
		});

		drawer.querySelectorAll('a').forEach(function (link) {
			link.addEventListener('click', function () {
				drawer.classList.remove('is-open');
				toggleBtn.setAttribute('aria-expanded', 'false');
			});
		});
	}

	function initNetworkStatusBadge() {
		var badge = document.getElementById('deraak-network-badge');
		if (!badge) return;

		function updateStatus() {
			if (navigator.onLine) {
				badge.textContent = '● کاتالوگ آنلاین | آماده استعلام';
				badge.classList.remove('is-offline');
			} else {
				badge.textContent = '⚡ حالت آفلاین فعال (جستجو از حافظه مرورگر)';
				badge.classList.add('is-offline');
			}
		}

		window.addEventListener('online', updateStatus);
		window.addEventListener('offline', updateStatus);
		updateStatus();
	}

	function initCatalogFilters() {
		var grid = document.getElementById('deraak-catalog-grid');
		if (!grid) return;

		var searchInput = document.getElementById('deraak-catalog-search-input');
		var catButtons = document.querySelectorAll('[data-filter-category]');
		var brandButtons = document.querySelectorAll('[data-filter-brand]');
		var countEl = document.getElementById('deraak-catalog-visible-count');

		var activeCat = 'all';
		var activeBrand = 'all';
		var searchQuery = '';

		function normalizeText(str) {
			return String(str || '')
				.replace(/[۰-۹]/g, function (d) { return '۰۱۲۳۴۵۶۷۸۹'.indexOf(d); })
				.replace(/[٠-٩]/g, function (d) { return '٠١٢٣٤٥٦٧٨٩'.indexOf(d); })
				.toLowerCase()
				.trim();
		}

		function applyFilters() {
			var cards = grid.querySelectorAll('.deraak-product-card');
			var visible = 0;

			cards.forEach(function (card) {
				var cardCats = normalizeText(card.getAttribute('data-category') || '');
				var cardBrand = normalizeText(card.getAttribute('data-brand') || '');
				var cardSearch = normalizeText(card.getAttribute('data-search') || card.textContent || '');

				var matchCat = activeCat === 'all' || cardCats.indexOf(normalizeText(activeCat)) !== -1;
				var matchBrand = activeBrand === 'all' || cardBrand.indexOf(normalizeText(activeBrand)) !== -1;
				var matchSearch = !searchQuery || cardSearch.indexOf(searchQuery) !== -1;

				if (matchCat && matchBrand && matchSearch) {
					card.style.display = '';
					visible++;
				} else {
					card.style.display = 'none';
				}
			});

			if (countEl) {
				countEl.textContent = visible;
			}
		}

		catButtons.forEach(function (btn) {
			btn.addEventListener('click', function () {
				catButtons.forEach(function (b) { b.classList.remove('is-active'); });
				btn.classList.add('is-active');
				activeCat = btn.getAttribute('data-filter-category') || 'all';
				applyFilters();
			});
		});

		brandButtons.forEach(function (btn) {
			btn.addEventListener('click', function () {
				brandButtons.forEach(function (b) { b.classList.remove('is-active'); });
				btn.classList.add('is-active');
				activeBrand = btn.getAttribute('data-filter-brand') || 'all';
				applyFilters();
			});
		});

		if (searchInput) {
			searchInput.addEventListener('input', function (e) {
				searchQuery = normalizeText(e.target.value);
				applyFilters();
			});
		}
	}

	function initQuickViewModal() {
		var modal = document.getElementById('deraak-quick-view-modal');
		if (!modal) return;

		var closeBtn = document.getElementById('deraak-quick-view-close');
		var backdrop = document.getElementById('deraak-quick-view-backdrop');
		var titleEl = document.getElementById('deraak-quick-view-title');
		var brandEl = document.getElementById('deraak-quick-view-brand');
		var modelEl = document.getElementById('deraak-quick-view-model');
		var imgEl = document.getElementById('deraak-quick-view-img');
		var excerptEl = document.getElementById('deraak-quick-view-excerpt');
		var specsTable = document.getElementById('deraak-quick-view-specs');
		var linkEl = document.getElementById('deraak-quick-view-link');
		var rfqBtn = document.getElementById('deraak-quick-view-rfq-btn');

		function closeModal() {
			modal.hidden = true;
			document.body.style.overflow = '';
		}

		if (closeBtn) closeBtn.addEventListener('click', closeModal);
		if (backdrop) backdrop.addEventListener('click', closeModal);

		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape' && !modal.hidden) {
				closeModal();
			}
		});

		document.addEventListener('click', function (e) {
			var trigger = e.target.closest('.deraak-quick-view-btn');
			if (!trigger) return;

			e.preventDefault();
			var id = trigger.getAttribute('data-id') || '';
			var title = trigger.getAttribute('data-title') || '';
			var brand = trigger.getAttribute('data-brand') || 'Deraak';
			var model = trigger.getAttribute('data-model') || '';
			var sku = trigger.getAttribute('data-sku') || model;
			var category = trigger.getAttribute('data-category') || '';
			var img = trigger.getAttribute('data-image') || '';
			var excerpt = trigger.getAttribute('data-excerpt') || '';
			var url = trigger.getAttribute('data-url') || '#';
			var specsRaw = trigger.getAttribute('data-specs') || '[]';

			if (titleEl) titleEl.textContent = title;
			if (brandEl) brandEl.textContent = brand;
			if (modelEl) modelEl.textContent = model || sku;
			if (imgEl) {
				imgEl.src = img;
				imgEl.alt = title;
			}
			if (excerptEl) excerptEl.textContent = excerpt;
			if (linkEl) linkEl.href = url;

			if (rfqBtn) {
				rfqBtn.setAttribute('data-id', id);
				rfqBtn.setAttribute('data-title', title);
				rfqBtn.setAttribute('data-sku', sku);
				rfqBtn.setAttribute('data-model', model);
				rfqBtn.setAttribute('data-brand', brand);
				rfqBtn.setAttribute('data-category', category);
				rfqBtn.setAttribute('data-image', img);
				rfqBtn.setAttribute('data-url', url);
			}

			if (specsTable) {
				specsTable.innerHTML = '';
				try {
					var specs = JSON.parse(specsRaw);
					if (Array.isArray(specs) && specs.length > 0) {
						specs.forEach(function (row) {
							if (!row || !row.key) return;
							var tr = document.createElement('tr');
							var th = document.createElement('th');
							th.textContent = row.key;
							var td = document.createElement('td');
							td.className = 'font-mono';
							td.setAttribute('dir', 'ltr');
							td.textContent = row.value || '';
							tr.appendChild(th);
							tr.appendChild(td);
							specsTable.appendChild(tr);
						});
					}
				} catch (err) {
					// ignore parse error
				}
			}

			modal.hidden = false;
			document.body.style.overflow = 'hidden';
		});
	}
})();
