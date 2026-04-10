'use strict';

const MAX_SIZE_BYTES = 10 * 1024 * 1024; // 10 MB
const ALLOWED_TYPES  = ['image/jpeg', 'image/png', 'image/webp'];

// Queue of File objects selected by the user
let uploadQueue = [];

// ─── ENTRY POINT ────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
  initUpload();
  initPriceCalculator();
  initOrderForm();
  initMobileMenu();
  initModals();
  initPaypal();
});

// ─── UPLOAD PAGE ────────────────────────────────────────────────
function initUpload() {
  const dropZone  = document.getElementById('drop-zone');
  const fileInput = document.getElementById('file-input');
  const uploadBtn = document.getElementById('upload-btn');

  if (!dropZone || !fileInput) return;

  // Drag events
  ['dragenter', 'dragover'].forEach(evt => {
    dropZone.addEventListener(evt, e => {
      e.preventDefault();
      dropZone.classList.add('border-indigo-500', 'bg-indigo-100');
    });
  });
  ['dragleave', 'drop'].forEach(evt => {
    dropZone.addEventListener(evt, e => {
      e.preventDefault();
      dropZone.classList.remove('border-indigo-500', 'bg-indigo-100');
    });
  });
  dropZone.addEventListener('drop', e => {
    if (e.dataTransfer.files.length) handleFilesSelect(Array.from(e.dataTransfer.files));
  });

  fileInput.addEventListener('change', () => {
    if (fileInput.files.length) handleFilesSelect(Array.from(fileInput.files));
  });

  if (uploadBtn) {
    uploadBtn.addEventListener('click', () => uploadAllFiles());
  }
}

function handleFilesSelect(files) {
  showError('');

  const valid  = [];
  const errors = [];
  for (const file of files) {
    if (!ALLOWED_TYPES.includes(file.type)) {
      errors.push('"' + file.name + '": invalid file type.');
    } else if (file.size > MAX_SIZE_BYTES) {
      errors.push('"' + file.name + '": exceeds 10 MB.');
    } else {
      valid.push(file);
    }
  }

  if (errors.length) showError('Skipped: ' + errors.join(' '));
  if (!valid.length) return;

  uploadQueue = valid;
  showPreviews(valid);

  const uploadBtn = document.getElementById('upload-btn');
  if (uploadBtn) {
    uploadBtn.classList.remove('hidden');
    uploadBtn.disabled = false;
    uploadBtn.textContent = valid.length === 1 ? 'Upload Photo' : 'Upload ' + valid.length + ' Photos';
  }
}

function showPreviews(files) {
  const dropContent      = document.getElementById('drop-zone-content');
  const previewContainer = document.getElementById('preview-container');
  const previewGrid      = document.getElementById('preview-grid');
  const previewFilename  = document.getElementById('preview-filename');

  if (previewGrid) {
    previewGrid.innerHTML = '';
    files.forEach(file => {
      const wrapper = document.createElement('div');
      wrapper.className = 'flex flex-col items-center';

      const img = document.createElement('img');
      img.className = 'w-20 h-20 object-cover rounded-lg shadow-sm';
      img.alt = file.name;

      const label = document.createElement('p');
      label.className = 'text-xs text-gray-500 mt-1 w-20 truncate text-center';
      label.textContent = file.name;

      wrapper.appendChild(img);
      wrapper.appendChild(label);
      previewGrid.appendChild(wrapper);

      const reader = new FileReader();
      reader.onload = e => { img.src = e.target.result; };
      reader.readAsDataURL(file);
    });
  }

  if (previewFilename) {
    const totalSize = files.reduce((sum, f) => sum + f.size, 0);
    previewFilename.textContent = files.length === 1
      ? files[0].name + ' (' + formatFileSize(files[0].size) + ')'
      : files.length + ' photos selected (' + formatFileSize(totalSize) + ' total)';
  }

  if (dropContent) dropContent.classList.add('hidden');
  if (previewContainer) previewContainer.classList.remove('hidden');
}

async function uploadAllFiles() {
  if (!uploadQueue.length) return;

  const uploadBtn         = document.getElementById('upload-btn');
  const progressContainer = document.getElementById('progress-container');
  const progressBar       = document.getElementById('progress-bar');
  const progressText      = document.getElementById('progress-text');
  const progressLabel     = document.getElementById('progress-label');
  const csrfEl            = document.getElementById('csrf-token');
  const urlEl             = document.getElementById('upload-url');

  const csrfToken = csrfEl ? csrfEl.dataset.token : '';
  const uploadUrl = urlEl  ? urlEl.dataset.url    : '?page=upload&action=process';
  const total     = uploadQueue.length;

  if (uploadBtn) uploadBtn.disabled = true;
  if (progressContainer) progressContainer.classList.remove('hidden');

  for (let i = 0; i < total; i++) {
    if (uploadBtn) {
      uploadBtn.textContent = total === 1 ? 'Uploading\u2026' : 'Uploading ' + (i + 1) + ' of ' + total + '\u2026';
    }
    if (progressLabel) {
      progressLabel.textContent = total === 1 ? 'Uploading\u2026' : 'Uploading photo ' + (i + 1) + ' of ' + total + '\u2026';
    }

    try {
      await uploadSingleFile(uploadQueue[i], csrfToken, uploadUrl, i, total, progressBar, progressText);
    } catch (err) {
      showError('Failed to upload "' + uploadQueue[i].name + '": ' + err);
      resetUploadBtn();
      return;
    }
  }

  if (progressBar)  progressBar.style.width  = '100%';
  if (progressText) progressText.textContent = '100%';
  if (uploadBtn) {
    uploadBtn.textContent = '\u2713 ' + (total === 1 ? 'Uploaded!' : 'All ' + total + ' photos uploaded!');
    uploadBtn.classList.remove('bg-indigo-600', 'hover:bg-indigo-700');
    uploadBtn.classList.add('bg-green-600');
  }

  // Redirect to order options page
  setTimeout(() => {
    const orderUrl = new URL(uploadUrl);
    orderUrl.searchParams.set('page', 'order');
    orderUrl.searchParams.delete('action');
    window.location.href = orderUrl.toString();
  }, 800);
}

function uploadSingleFile(file, csrfToken, uploadUrl, index, total, progressBar, progressText) {
  return new Promise((resolve, reject) => {
    const formData = new FormData();
    formData.append('image', file);
    formData.append('csrf_token', csrfToken);

    const xhr = new XMLHttpRequest();
    xhr.open('POST', uploadUrl, true);

    xhr.upload.addEventListener('progress', e => {
      if (e.lengthComputable && progressBar && progressText) {
        const overall = ((index + e.loaded / e.total) / total) * 100;
        progressBar.style.width  = overall.toFixed(0) + '%';
        progressText.textContent = overall.toFixed(0) + '%';
      }
    });

    xhr.addEventListener('load', () => {
      if (xhr.status === 200) {
        try {
          const data = JSON.parse(xhr.responseText);
          if (data.success) { resolve(data); } else { reject(data.error || 'Upload failed.'); }
        } catch (_) { reject('Unexpected server response.'); }
      } else {
        try {
          const data = JSON.parse(xhr.responseText);
          reject(data.error || 'HTTP ' + xhr.status);
        } catch (_) { reject('HTTP ' + xhr.status); }
      }
    });

    xhr.addEventListener('error', () => reject('Network error. Please check your connection.'));
    xhr.send(formData);
  });
}

function resetUploadBtn() {
  const uploadBtn         = document.getElementById('upload-btn');
  const progressContainer = document.getElementById('progress-container');
  if (uploadBtn) {
    uploadBtn.disabled = false;
    uploadBtn.textContent = uploadQueue.length === 1 ? 'Upload Photo' : 'Upload ' + uploadQueue.length + ' Photos';
    uploadBtn.classList.add('bg-indigo-600', 'hover:bg-indigo-700');
    uploadBtn.classList.remove('bg-green-600');
  }
  if (progressContainer) progressContainer.classList.add('hidden');
}

function showError(msg) {
  const box  = document.getElementById('upload-error');
  const text = document.getElementById('upload-error-text');
  if (!box) return;
  if (msg) {
    if (text) text.textContent = msg;
    box.classList.remove('hidden');
  } else {
    box.classList.add('hidden');
  }
}

function formatFileSize(bytes) {
  if (bytes < 1024) return bytes + ' B';
  if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
  return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
}

// ─── PER-IMAGE PRICE CALCULATOR (order options page) ────────────
function initPriceCalculator() {
  const items = document.querySelectorAll('.order-item');
  if (!items.length) return;

  // Price data is injected via a data attribute (no inline script needed)
  const pricesEl = document.getElementById('print-sizes-data');
  const PRICES = pricesEl ? JSON.parse(pricesEl.dataset.prices || '{}') : {};

  function getItemTotal(item) {
    const selectedSize = item.querySelector('.size-radio:checked');
    const qtyInput     = item.querySelector('.item-qty');
    if (!selectedSize || !qtyInput) return 0;
    const qty  = Math.max(1, Math.min(100, parseInt(qtyInput.value, 10) || 1));
    const unit = PRICES[selectedSize.value] || 0;
    return unit * qty;
  }

  function updateItemDisplay(item) {
    const totalEl = item.querySelector('.item-total');
    if (totalEl) totalEl.textContent = '\u20AC' + getItemTotal(item).toFixed(2);
    updateGrandTotal();
  }

  function updateGrandTotal() {
    const grandTotalEl = document.getElementById('grand-total');
    if (!grandTotalEl) return;
    let total = 0;
    items.forEach(item => { total += getItemTotal(item); });
    grandTotalEl.textContent = '\u20AC' + total.toFixed(2);
  }

  items.forEach(item => {
    const sizeRadios = item.querySelectorAll('.size-radio');
    const qtyInput   = item.querySelector('.item-qty');
    const minusBtn   = item.querySelector('.qty-minus');
    const plusBtn    = item.querySelector('.qty-plus');

    sizeRadios.forEach(r => r.addEventListener('change', () => updateItemDisplay(item)));
    if (qtyInput) qtyInput.addEventListener('input', () => updateItemDisplay(item));

    if (minusBtn && qtyInput) {
      minusBtn.addEventListener('click', () => {
        const v = parseInt(qtyInput.value, 10) || 1;
        if (v > 1) { qtyInput.value = v - 1; qtyInput.dispatchEvent(new Event('input')); }
      });
    }
    if (plusBtn && qtyInput) {
      plusBtn.addEventListener('click', () => {
        const v = parseInt(qtyInput.value, 10) || 1;
        if (v < 100) { qtyInput.value = v + 1; qtyInput.dispatchEvent(new Event('input')); }
      });
    }

    updateItemDisplay(item);
  });
}

// ─── ORDER FORM VALIDATION ────────────────────────────────────────
function initOrderForm() {
  const form = document.getElementById('order-form');
  if (!form) return;

  form.addEventListener('submit', e => {
    const name    = form.querySelector('[name="customer_name"]');
    const email   = form.querySelector('[name="customer_email"]');
    const address = form.querySelector('[name="customer_address"]');
    const errors  = [];

    if (name && name.value.trim() === '')           errors.push('Name is required.');
    if (email && !isValidEmail(email.value.trim())) errors.push('A valid email is required.');
    if (address && address.value.trim() === '')      errors.push('Address is required.');

    // Validate each item's quantity
    form.querySelectorAll('.item-qty').forEach((qtyEl, i) => {
      const q = parseInt(qtyEl.value, 10);
      if (isNaN(q) || q < 1 || q > 100) errors.push('Quantity for photo ' + (i + 1) + ' must be between 1 and 100.');
    });

    if (errors.length) {
      e.preventDefault();
      alert(errors.join('\n'));
    }
  });
}

function isValidEmail(email) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

// ─── MOBILE MENU ─────────────────────────────────────────────────
function initMobileMenu() {
  const btn   = document.getElementById('mobile-menu-btn');
  const menu  = document.getElementById('mobile-menu');
  const open  = document.getElementById('menu-icon-open');
  const close = document.getElementById('menu-icon-close');
  if (btn) {
    btn.addEventListener('click', () => {
      menu.classList.toggle('hidden');
      open.classList.toggle('hidden');
      close.classList.toggle('hidden');
    });
  }
}

// ─── MODALS ───────────────────────────────────────────────────────
function openModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) {
    modal.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
  }
}

function closeModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) {
    modal.classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
  }
}

function initModals() {
  const ids = ['privacy', 'tos'];
  ids.forEach(name => {
    const modalId  = name + '-modal';
    const openBtn  = document.getElementById('open-' + modalId);
    const closeBtn = document.getElementById('close-' + modalId);
    const closeFooterBtn = document.getElementById('close-' + modalId + '-btn');
    const backdrop = document.getElementById(modalId + '-backdrop');
    if (openBtn)        openBtn.addEventListener('click',  () => openModal(modalId));
    if (closeBtn)       closeBtn.addEventListener('click', () => closeModal(modalId));
    if (closeFooterBtn) closeFooterBtn.addEventListener('click', () => closeModal(modalId));
    if (backdrop)       backdrop.addEventListener('click', () => closeModal(modalId));
  });

  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
      ids.forEach(name => {
        const el = document.getElementById(name + '-modal');
        if (el && !el.classList.contains('hidden')) closeModal(name + '-modal');
      });
    }
  });
}

// ─── PAYPAL SMART BUTTONS ────────────────────────────────────────
// Data for PayPal is injected via #paypal-data data attributes (no inline script needed).
// The PayPal SDK <script> tag in order/summary.php loads before app.js so
// window.paypal is guaranteed to be available when this function runs.
function initPaypal() {
  const dataEl    = document.getElementById('paypal-data');
  const container = document.getElementById('paypal-button-container');
  if (!dataEl || !container) return;
  if (typeof paypal === 'undefined') return;

  const csrfToken  = dataEl.dataset.csrf;
  const createUrl  = dataEl.dataset.createUrl;
  const captureUrl = dataEl.dataset.captureUrl;
  const errorEl    = document.getElementById('paypal-error');

  function showPaypalError(msg) {
    if (errorEl) {
      errorEl.textContent = msg;
      errorEl.classList.remove('hidden');
    }
  }

  paypal.Buttons({
    createOrder: function () {
      if (errorEl) errorEl.classList.add('hidden');
      return fetch(createUrl, {
        method:  'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body:    'csrf_token=' + encodeURIComponent(csrfToken),
      })
      .then(function (res) { return res.json(); })
      .then(function (data) {
        if (data.error) { throw new Error(data.error); }
        return data.id;
      })
      .catch(function (err) {
        showPaypalError('Unable to start payment. Please try again or contact support.');
        throw err;
      });
    },

    onApprove: function (data) {
      return fetch(captureUrl, {
        method:  'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body:    'csrf_token=' + encodeURIComponent(csrfToken)
               + '&paypal_order_id=' + encodeURIComponent(data.orderID),
      })
      .then(function (res) { return res.json(); })
      .then(function (result) {
        if (result.error) { throw new Error(result.error); }
        window.location.href = result.redirectUrl;
      });
    },

    onCancel: function () {
      if (errorEl) errorEl.classList.add('hidden');
    },

    onError: function (err) {
      showPaypalError('Payment could not be completed. Please try again or contact support.');
      console.error('PayPal error:', err);
    },
  }).render('#paypal-button-container');
}

