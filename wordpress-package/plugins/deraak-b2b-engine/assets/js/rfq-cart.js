/**
 * Deraak RFQ (Request For Quotation) & Kavenegar Consultation Handler
 *
 * Security: All localStorage values are treated as untrusted and rendered via DOM textContent/setAttribute.
 * Submission: Verified server-side via WordPress REST API (/wp-json/deraak/v1/inquiry) with nonce protection.
 */

(function () {
  'use strict';

  var RFQ_KEY = 'derak_rfq_items';
  var MAX_ITEMS = 50;
  var MAX_QTY = 9999;
  var MAX_TEXT = 300;
  var config = window.deraakRFQ || {};

  function toEnglishDigits(str) {
    return String(str || '')
      .replace(/[۰-۹]/g, function (d) { return String(d.charCodeAt(0) - 1776); })
      .replace(/[٠-٩]/g, function (d) { return String(d.charCodeAt(0) - 1632); });
  }

  function cleanText(value, max) {
    if (typeof value !== 'string' && typeof value !== 'number') return '';
    return String(value).replace(/[\u0000-\u001F\u007F]/g, ' ').trim().slice(0, max || MAX_TEXT);
  }

  function cleanQty(value) {
    var n = parseInt(toEnglishDigits(value), 10);
    if (!isFinite(n) || n < 1) return 1;
    return Math.min(n, MAX_QTY);
  }

  function isValidIranPhone(raw) {
    var digits = toEnglishDigits(raw).replace(/[\s\-()]/g, '');
    return /^(\+98|0098|98|0)?9\d{9}$|^0\d{10}$/.test(digits);
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
    updateBadges();
  }

  function updateBadges() {
    var count = getRFQItems().reduce(function (sum, item) {
      return sum + item.quantity;
    }, 0);

    var badges = document.querySelectorAll('#derak-rfq-count-badge, .derak-header-rfq-count');
    badges.forEach(function (badge) {
      badge.textContent = String(count);
      badge.style.display = count > 0 ? 'inline-flex' : 'none';
    });
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
      head.appendChild(el('span', 'derak-rfq-urgent-tag', 'فوری'));
    }
    info.appendChild(head);

    var note = document.createElement('input');
    note.type = 'text';
    note.className = 'derak-rfq-note';
    note.maxLength = MAX_TEXT;
    note.placeholder = 'یادداشت فنی (سنسور، اتصال فرآیندی، گواهی مورد نیاز...)';
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

    controls.appendChild(button('derak-urgent-btn', item.id, item.urgent ? 'عادی' : 'فوری', {
      'aria-pressed': item.urgent ? 'true' : 'false'
    }));
    controls.appendChild(button('derak-remove-rfq-btn', item.id, 'حذف', { 'aria-label': 'حذف ' + item.title }));

    row.appendChild(info);
    row.appendChild(controls);
    return row;
  }

  function renderRFQTable() {
    var list = document.getElementById('derak-rfq-items-list');
    var empty = document.getElementById('derak-rfq-empty');
    var content = document.getElementById('derak-rfq-content');
    var offlineNote = document.getElementById('derak-rfq-offline-note');
    var submitBtn = document.getElementById('derak-rfq-submit-btn');

    if (offlineNote) {
      offlineNote.style.display = navigator.onLine ? 'none' : 'block';
    }
    if (submitBtn) {
      submitBtn.disabled = !navigator.onLine;
    }

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

  function setFeedback(msg, isError) {
    var fb = document.getElementById('derak-rfq-feedback');
    if (!fb) return;
    if (!msg) {
      fb.style.display = 'none';
      fb.textContent = '';
      return;
    }
    fb.style.display = 'block';
    fb.style.color = isError ? 'var(--color-danger)' : 'var(--color-primary-active)';
    fb.textContent = msg;
  }

  function fieldValue(id) {
    var node = document.getElementById(id);
    return node ? cleanText(node.value, 160) : '';
  }

  function generateRFQReportText() {
    var output = 'برگ درخواست استعلام قیمت و مشخصات فنی — دراک\n';
    output += 'نام درخواست‌دهنده: ' + (fieldValue('derak-rfq-name') || '-') + '\n';
    output += 'شرکت / پروژه: ' + (fieldValue('derak-rfq-company') || '-') + '\n';
    output += 'شماره تماس: ' + (toEnglishDigits(fieldValue('derak-rfq-phone')) || '-') + '\n';
    output += 'محل تحویل: ' + (fieldValue('derak-rfq-site') || '-') + '\n\n';
    output += 'اقلام درخواستی:\n';

    getRFQItems().forEach(function (it, idx) {
      output += (idx + 1) + '. ' + (it.model || '-') + ' | ' + it.title + ' × ' + it.quantity;
      if (it.urgent) output += ' [فوری]';
      if (it.notes) output += ' — یادداشت: ' + it.notes;
      output += '\n';
    });

    return output;
  }

  function submitRFQToServer(e) {
    if (e) e.preventDefault();
    setFeedback('');

    if (!navigator.onLine) {
      setFeedback('شما در حالت آفلاین هستید. ثبت نهایی استعلام در سرور نیازمند اتصال اینترنت است.', true);
      return;
    }

    var items = getRFQItems();
    if (items.length === 0) {
      setFeedback('لیست استعلام شما خالی است.', true);
      return;
    }

    var name = fieldValue('derak-rfq-name');
    var company = fieldValue('derak-rfq-company');
    var phone = toEnglishDigits(fieldValue('derak-rfq-phone'));
    var site = fieldValue('derak-rfq-site');
    var hp = fieldValue('derak-rfq-hp');

    if (name.length < 2) {
      setFeedback('لطفاً نام و نام خانوادگی را وارد نمایید.', true);
      return;
    }
    if (!isValidIranPhone(phone)) {
      setFeedback('لطفاً شماره تماس معتبر وارد نمایید (مثال: 09123456789).', true);
      return;
    }

    var submitBtn = document.getElementById('derak-rfq-submit-btn');
    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.textContent = 'در حال ثبت در سرور...';
    }

    var payload = {
      name: name,
      company: company,
      phone: phone,
      site: site,
      website_hp: hp,
      inquiryNonce: config.inquiryNonce || '',
      items: items
    };

    var endpoint = config.inquiryApi || '/wp-json/deraak/v1/inquiry';
    fetch(endpoint, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': config.restNonce || ''
      },
      body: JSON.stringify(payload),
      credentials: 'same-origin'
    })
      .then(function (res) {
        return res.json().then(function (data) {
          return { ok: res.ok && data && data.ok, data: data };
        });
      })
      .then(function (result) {
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.textContent = 'ثبت نهایی استعلام در سامانه';
        }

        if (!result.ok) {
          setFeedback((result.data && result.data.error) || 'خطا در ثبت استعلام در سرور.', true);
          return;
        }

        saveRFQItems([]);
        var content = document.getElementById('derak-rfq-content');
        var successBox = document.getElementById('derak-rfq-success');
        var trackingEl = document.getElementById('derak-rfq-tracking-code');
        if (content) content.style.display = 'none';
        if (trackingEl) trackingEl.textContent = result.data.trackingCode || '';
        if (successBox) successBox.style.display = 'block';
      })
      .catch(function () {
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.textContent = 'ثبت نهایی استعلام در سامانه';
        }
        setFeedback('خطا در برقراری ارتباط با سرور. اتصال اینترنت خود را بررسی نمایید.', true);
      });
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
      saveRFQItems([]);
      renderRFQTable();
      return;
    }

    if (target.closest('#derak-rfq-new-btn')) {
      var successBox = document.getElementById('derak-rfq-success');
      if (successBox) successBox.style.display = 'none';
      renderRFQTable();
      return;
    }

    if (target.closest('#derak-rfq-print-btn')) {
      window.print();
      return;
    }

    if (target.closest('#derak-rfq-copy-btn')) {
      if (navigator.clipboard) {
        navigator.clipboard.writeText(generateRFQReportText()).then(function () {
          setFeedback('متن لیست استعلام در حافظه کلیپ‌بورد کپی شد (این عمل به معنای ارسال به سرور نیست).', false);
        });
      }
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

  // Consultation Form with Kavenegar OTP Verification
  var currentOtpToken = '';

  function initConsultationForm() {
    var detailsForm = document.getElementById('derak-consult-step-details');
    var otpForm = document.getElementById('derak-consult-step-otp');
    var successBox = document.getElementById('derak-consult-step-success');
    var editBtn = document.getElementById('derak-consult-edit-phone-btn');
    var resetBtn = document.getElementById('derak-consult-reset-btn');

    if (!detailsForm || !otpForm) return;

    detailsForm.addEventListener('submit', function (e) {
      e.preventDefault();
      var errEl = document.getElementById('derak-consult-err');
      if (errEl) errEl.style.display = 'none';

      if (!navigator.onLine) {
        if (errEl) {
          errEl.textContent = 'در حالت آفلاین امکان ارسال کد تایید پیامکی وجود ندارد.';
          errEl.style.display = 'block';
        }
        return;
      }

      var payload = {
        name: fieldValue('derak-consult-name'),
        company: fieldValue('derak-consult-company'),
        phone: toEnglishDigits(fieldValue('derak-consult-phone')),
        part: fieldValue('derak-consult-part'),
        message: cleanText((document.getElementById('derak-consult-msg') || {}).value || '', 600),
        website_hp: fieldValue('derak-consult-hp')
      };

      var btn = document.getElementById('derak-consult-send-otp-btn');
      if (btn) btn.disabled = true;

      fetch(config.otpSendApi || '/wp-json/deraak/v1/send-otp', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-WP-Nonce': config.restNonce || ''
        },
        body: JSON.stringify(payload)
      })
        .then(function (r) { return r.json(); })
        .then(function (data) {
          if (btn) btn.disabled = false;
          if (!data || !data.ok) {
            if (errEl) {
              errEl.textContent = (data && data.error) || 'خطا در ارسال کد تایید';
              errEl.style.display = 'block';
            }
            return;
          }

          currentOtpToken = data.otpToken || '';
          var phoneTarget = document.getElementById('derak-consult-target-phone');
          if (phoneTarget) phoneTarget.textContent = data.phone || payload.phone;

          var hintEl = document.getElementById('derak-consult-sandbox-hint');
          if (hintEl) {
            if (data.sandboxCode) {
              hintEl.textContent = 'حالت آزمایشی (بدون کلید کاوه‌نگار) — کد تایید آزمایشی شما: ' + data.sandboxCode;
              hintEl.style.display = 'block';
            } else {
              hintEl.style.display = 'none';
            }
          }

          detailsForm.style.display = 'none';
          otpForm.style.display = 'block';
        })
        .catch(function () {
          if (btn) btn.disabled = false;
          if (errEl) {
            errEl.textContent = 'خطا در برقراری ارتباط با سرور';
            errEl.style.display = 'block';
          }
        });
    });

    otpForm.addEventListener('submit', function (e) {
      e.preventDefault();
      var otpErr = document.getElementById('derak-consult-otp-err');
      if (otpErr) otpErr.style.display = 'none';

      var otpInput = document.getElementById('derak-consult-otp-input');
      var code = toEnglishDigits(otpInput ? otpInput.value : '').replace(/\D/g, '');
      if (code.length !== 5) {
        if (otpErr) {
          otpErr.textContent = 'لطفاً کد تایید ۵ رقمی را کامل وارد نمایید.';
          otpErr.style.display = 'block';
        }
        return;
      }

      var vBtn = document.getElementById('derak-consult-verify-btn');
      if (vBtn) vBtn.disabled = true;

      var payload = {
        name: fieldValue('derak-consult-name'),
        company: fieldValue('derak-consult-company'),
        phone: toEnglishDigits(fieldValue('derak-consult-phone')),
        part: fieldValue('derak-consult-part'),
        message: cleanText((document.getElementById('derak-consult-msg') || {}).value || '', 600),
        otpCode: code,
        otpToken: currentOtpToken
      };

      fetch(config.otpVerifyApi || '/wp-json/deraak/v1/verify-otp', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-WP-Nonce': config.restNonce || ''
        },
        body: JSON.stringify(payload)
      })
        .then(function (r) { return r.json(); })
        .then(function (data) {
          if (vBtn) vBtn.disabled = false;
          if (!data || !data.ok) {
            if (otpErr) {
              otpErr.textContent = (data && data.error) || 'کد تایید نامعتبر است';
              otpErr.style.display = 'block';
            }
            return;
          }

          otpForm.style.display = 'none';
          var trk = document.getElementById('derak-consult-tracking');
          if (trk) trk.textContent = data.trackingCode || '';
          if (successBox) successBox.style.display = 'block';
        })
        .catch(function () {
          if (vBtn) vBtn.disabled = false;
          if (otpErr) {
            otpErr.textContent = 'خطا در ارتباط با سرور هنگام تایید کد';
            otpErr.style.display = 'block';
          }
        });
    });

    if (editBtn) {
      editBtn.addEventListener('click', function () {
        otpForm.style.display = 'none';
        detailsForm.style.display = 'block';
      });
    }

    if (resetBtn) {
      resetBtn.addEventListener('click', function () {
        if (successBox) successBox.style.display = 'none';
        detailsForm.reset();
        detailsForm.style.display = 'block';
      });
    }
  }

  document.addEventListener('DOMContentLoaded', function () {
    updateBadges();
    renderRFQTable();
    initConsultationForm();

    var rfqForm = document.getElementById('derak-rfq-submit-form');
    if (rfqForm) {
      rfqForm.addEventListener('submit', submitRFQToServer);
    }
  });

  window.addEventListener('online', renderRFQTable);
  window.addEventListener('offline', renderRFQTable);
})();
