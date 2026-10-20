/**
 * Deraak RFQ (Request For Quotation) Cart Manager
 *
 * All stored values are treated as untrusted: they are validated on read
 * and rendered with textContent / setAttribute only (never innerHTML),
 * because localStorage can be tampered with by any script on the origin.
 */

(function () {
  'use strict';

  var RFQ_KEY = 'derak_rfq_items';
  var MAX_ITEMS = 100;
  var MAX_QTY = 9999;
  var MAX_TEXT = 300;
  var config = window.deraakRFQ || {};

  function cleanText(value, max) {
    if (typeof value !== 'string' && typeof value !== 'number') return '';
    return String(value).replace(/[\u0000-\u001F\u007F]/g, ' ').trim().slice(0, max || MAX_TEXT);
  }

  function cleanQty(value) {
    var n = parseInt(value, 10);
    if (!isFinite(n) || n < 1) return 1;
    return Math.min(n, MAX_QTY);
  }

  function normalizeItem(raw) {
    if (!raw || typeof raw !== 'object') return null;
    var id = cleanText(raw.id, 64);
    if (!/^[A-Za-z0-9_-]+$/.test(id)) return null;
    return {
      id: id,
      title: cleanText(raw.title, 200),
      model: cleanText(raw.model, 80),
      quantity: cleanQty(raw.quantity),
      notes: cleanText(raw.notes, MAX_TEXT),
      urgent: raw.urgent === true
    };
  }

  function getRFQItems() {
    try {
      var parsed = JSON.parse(localStorage.getItem(RFQ_KEY) || '[]');
      if (!Array.isArray(parsed)) return [];
      return parsed.map(normalizeItem).filter(Boolean).slice(0, MAX_ITEMS);
    } catch (e) {
      return [];
    }
  }

  function saveRFQItems(items) {
    try {
      localStorage.setItem(RFQ_KEY, JSON.stringify(items.slice(0, MAX_ITEMS)));
    } catch (e) {}
    updateDockBadge();
  }

  function updateDockBadge() {
    var count = getRFQItems().reduce(function (sum, item) {
      return sum + item.quantity;
    }, 0);

    var badge = document.getElementById('derak-rfq-count-badge');
    if (!badge) return;
    if (count > 0) {
      badge.textContent = String(count);
      badge.style.display = 'flex';
    } else {
      badge.style.display = 'none';
    }
  }

  window.addEventListener('derak_add_to_rfq', function (e) {
    var incoming = normalizeItem(e.detail && Object.assign({ quantity: 1 }, e.detail));
    if (!incoming) return;

    var items = getRFQItems();
    var existing = items.find(function (item) { return item.id === incoming.id; });

    if (existing) {
      existing.quantity = cleanQty(existing.quantity + 1);
    } else if (items.length < MAX_ITEMS) {
      incoming.quantity = 1;
      incoming.notes = '';
      incoming.urgent = false;
      items.push(incoming);
    }

    saveRFQItems(items);
    renderRFQTable();
  });

  function el(tag, className, text) {
    var node = document.createElement(tag);
    if (className) node.className = className;
    if (text !== undefined) node.textContent = text;
    return node;
  }

  function button(className, id, label, extra) {
    var btn = el('button', className, label);
    btn.type = 'button';
    btn.setAttribute('data-id', id);
    if (extra) {
      Object.keys(extra).forEach(function (k) { btn.setAttribute(k, extra[k]); });
    }
    return btn;
  }

  function buildRow(item, idx) {
    var row = el('div', 'derak-rfq-row' + (item.urgent ? ' is-urgent' : ''));

    var info = el('div', 'derak-rfq-row-info');
    var head = el('div', 'derak-rfq-row-head');
    head.appendChild(el('span', 'derak-rfq-row-title', (idx + 1) + '. ' + item.title));

    if (item.model) {
      var model = el('span', 'derak-card-model', item.model);
      model.dir = 'ltr';
      head.appendChild(model);
    }
    if (item.urgent) {
      head.appendChild(el('span', 'derak-rfq-urgent-tag', 'فوری / توقف خط'));
    }
    info.appendChild(head);

    var note = document.createElement('input');
    note.type = 'text';
    note.className = 'derak-rfq-note';
    note.maxLength = MAX_TEXT;
    note.placeholder = 'یادداشت فنی / متریال دیافراگم / گواهی تست...';
    note.value = item.notes;
    note.setAttribute('data-id', item.id);
    note.setAttribute('aria-label', 'یادداشت فنی برای ' + item.title);
    info.appendChild(note);

    var controls = el('div', 'derak-rfq-row-controls');
    var stepper = el('div', 'derak-rfq-stepper');
    stepper.appendChild(button('derak-qty-btn', item.id, '−', { 'data-delta': '-1', 'aria-label': 'کاهش تعداد' }));
    stepper.appendChild(el('span', 'derak-rfq-qty', String(item.quantity)));
    stepper.appendChild(button('derak-qty-btn', item.id, '+', { 'data-delta': '1', 'aria-label': 'افزایش تعداد' }));
    controls.appendChild(stepper);

    controls.appendChild(button('derak-urgent-btn', item.id, item.urgent ? 'عادی‌سازی' : 'فوری', {
      title: 'علامت‌گذاری فوریت توقف خط',
      'aria-pressed': item.urgent ? 'true' : 'false'
    }));
    controls.appendChild(button('derak-remove-rfq-btn', item.id, '✕', { 'aria-label': 'حذف ' + item.title }));

    row.appendChild(info);
    row.appendChild(controls);
    return row;
  }

  function renderRFQTable() {
    var list = document.getElementById('derak-rfq-items-list');
    var empty = document.getElementById('derak-rfq-empty');
    var content = document.getElementById('derak-rfq-content');
    if (!list) return;

    var items = getRFQItems();

    if (items.length === 0) {
      if (empty) empty.style.display = 'block';
      if (content) content.style.display = 'none';
      return;
    }

    if (empty) empty.style.display = 'none';
    if (content) content.style.display = 'block';

    list.replaceChildren();
    items.forEach(function (item, idx) {
      list.appendChild(buildRow(item, idx));
    });
  }

  function whatsappNumber() {
    var digits = String(config.whatsapp || '').replace(/\D/g, '');
    return digits.length >= 8 && digits.length <= 15 ? digits : '';
  }

  document.addEventListener('click', function (e) {
    var target = e.target instanceof Element ? e.target : null;
    if (!target) return;

    var qtyBtn = target.closest('.derak-qty-btn');
    if (qtyBtn) {
      var id = qtyBtn.getAttribute('data-id');
      var delta = parseInt(qtyBtn.getAttribute('data-delta'), 10) === -1 ? -1 : 1;
      var items = getRFQItems();
      var item = items.find(function (it) { return it.id === id; });
      if (item) {
        var next = item.quantity + delta;
        if (next <= 0) {
          items = items.filter(function (it) { return it.id !== id; });
        } else {
          item.quantity = cleanQty(next);
        }
        saveRFQItems(items);
        renderRFQTable();
      }
      return;
    }

    var removeBtn = target.closest('.derak-remove-rfq-btn');
    if (removeBtn) {
      var rmId = removeBtn.getAttribute('data-id');
      saveRFQItems(getRFQItems().filter(function (it) { return it.id !== rmId; }));
      renderRFQTable();
      return;
    }

    var urgentBtn = target.closest('.derak-urgent-btn');
    if (urgentBtn) {
      var urgId = urgentBtn.getAttribute('data-id');
      var list = getRFQItems();
      var found = list.find(function (it) { return it.id === urgId; });
      if (found) {
        found.urgent = !found.urgent;
        saveRFQItems(list);
        renderRFQTable();
      }
      return;
    }

    if (target.closest('#derak-rfq-clear-btn')) {
      if (window.confirm('آیا از پاکسازی کل لیست استعلام اطمینان دارید؟')) {
        saveRFQItems([]);
        renderRFQTable();
      }
      return;
    }

    if (target.closest('#derak-rfq-print-btn')) {
      window.print();
      return;
    }

    if (target.closest('#derak-rfq-copy-btn')) {
      if (!navigator.clipboard) return;
      navigator.clipboard.writeText(generateRFQReportText()).then(function () {
        window.alert('متن رسمی پیش‌فاکتور استعلام در کلیپ‌بورد کپی گردید.');
      });
      return;
    }

    if (target.closest('#derak-rfq-wa-btn')) {
      var phone = whatsappNumber();
      if (!phone) {
        window.alert('شماره واتس‌اپ فروش در تنظیمات افزونه ثبت نشده است.');
        return;
      }
      var waUrl = 'https://api.whatsapp.com/send?phone=' + phone + '&text=' + encodeURIComponent(generateRFQReportText());
      window.open(waUrl, '_blank', 'noopener,noreferrer');
    }
  });

  document.addEventListener('input', function (e) {
    var target = e.target;
    if (!(target instanceof HTMLInputElement) || !target.classList.contains('derak-rfq-note')) return;
    var nId = target.getAttribute('data-id');
    var items = getRFQItems();
    var item = items.find(function (it) { return it.id === nId; });
    if (item) {
      item.notes = cleanText(target.value, MAX_TEXT);
      saveRFQItems(items);
    }
  });

  function fieldValue(id) {
    var node = document.getElementById(id);
    var value = node ? cleanText(node.value, 120) : '';
    return value || 'اعلام نشده';
  }

  function generateRFQReportText() {
    var output = 'استعلام رسمی قیمت و موجودی کالا - دراک ابزار دقیق\n';
    output += 'سایت پروژه / تحویل: ' + fieldValue('derak-rfq-site') + '\n';
    output += 'نام شرکت / پروژه: ' + fieldValue('derak-rfq-company') + '\n';
    output += 'شماره تماس خریدار: ' + fieldValue('derak-rfq-phone') + '\n\n';
    output += 'اقلام درخواستی:\n';

    getRFQItems().forEach(function (it, idx) {
      output += (idx + 1) + '. مدل: ' + (it.model || 'نامشخص') + ' | ' + it.title + '\n';
      output += '   تعداد: ' + it.quantity + ' عدد\n';
      if (it.notes) output += '   مشخصات/یادداشت: ' + it.notes + '\n';
      if (it.urgent) output += '   وضعیت فوریت: توقف خط / بحرانی\n';
    });

    output += '\nدرخواست ارسال پیش‌فاکتور رسمی و زمان تحویل به انبار تهران یا سایت پروژه.';
    return output;
  }

  document.addEventListener('DOMContentLoaded', function () {
    updateDockBadge();
    renderRFQTable();
  });
})();
