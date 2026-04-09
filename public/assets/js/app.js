'use strict';

// Price configuration (also set by PHP inline in order/form.php)
const PRICES = window.PRINT_SIZES || { '10x15': 2.99, '13x18': 4.99, '20x30': 8.99 };
const MAX_SIZE_BYTES = 10 * 1024 * 1024; // 10 MB
const ALLOWED_TYPES  = ['image/jpeg', 'image/png', 'image/webp'];

// ─── UPLOAD PAGE ────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
  initUpload();
  initPriceCalculator();
  initOrderForm();
  initQtyButtons();
});

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
    const files = e.dataTransfer.files;
    if (files.length) handleFileSelect(files[0]);
  });

  fileInput.addEventListener('change', () => {
    if (fileInput.files.length) handleFileSelect(fileInput.files[0]);
  });

  if (uploadBtn) {
    uploadBtn.addEventListener('click', () => uploadFile());
  }
}

function handleFileSelect(file) {
  const uploadBtn        = document.getElementById('upload-btn');
  const dropContent      = document.getElementById('drop-zone-content');
  const previewContainer = document.getElementById('preview-container');
  const previewImg       = document.getElementById('image-preview');
  const previewFilename  = document.getElementById('preview-filename');

  showError('');

  // Client-side validation
  if (!ALLOWED_TYPES.includes(file.type)) {
    showError('Invalid file type. Please upload a JPG, PNG, or WebP image.');
    return;
  }
  if (file.size > MAX_SIZE_BYTES) {
    showError('File is too large. Maximum allowed size is 10 MB.');
    return;
  }

  // Show preview
  const reader = new FileReader();
  reader.onload = e => {
    if (previewImg) previewImg.src = e.target.result;
    if (previewFilename) previewFilename.textContent = file.name + ' (' + formatFileSize(file.size) + ')';
    if (dropContent) dropContent.classList.add('hidden');
    if (previewContainer) previewContainer.classList.remove('hidden');
  };
  reader.readAsDataURL(file);

  // Enable upload button
  if (uploadBtn) {
    uploadBtn.classList.remove('hidden');
    uploadBtn.disabled = false;
    uploadBtn._file = file;
  }
}

function uploadFile() {
  const uploadBtn        = document.getElementById('upload-btn');
  const progressContainer = document.getElementById('progress-container');
  const progressBar      = document.getElementById('progress-bar');
  const progressText     = document.getElementById('progress-text');
  const csrfEl           = document.getElementById('csrf-token');
  const urlEl            = document.getElementById('upload-url');

  if (!uploadBtn || !uploadBtn._file) return;

  const file      = uploadBtn._file;
  const csrfToken = csrfEl ? csrfEl.dataset.token : '';
  const uploadUrl = urlEl  ? urlEl.dataset.url    : '?page=upload&action=process';

  const formData = new FormData();
  formData.append('image', file);
  formData.append('csrf_token', csrfToken);

  uploadBtn.disabled = true;
  uploadBtn.textContent = 'Uploading\u2026';
  if (progressContainer) progressContainer.classList.remove('hidden');

  const xhr = new XMLHttpRequest();
  xhr.open('POST', uploadUrl, true);

  xhr.upload.addEventListener('progress', e => {
    if (e.lengthComputable) {
      const pct = Math.round((e.loaded / e.total) * 100);
      if (progressBar)  progressBar.style.width = pct + '%';
      if (progressText) progressText.textContent = pct + '%';
    }
  });

  xhr.addEventListener('load', () => {
    if (xhr.status === 200) {
      try {
        const data = JSON.parse(xhr.responseText);
        if (data.success) {
          if (progressBar) progressBar.style.width = '100%';
          if (progressText) progressText.textContent = '100%';
          uploadBtn.textContent = '\u2713 Uploaded!';
          uploadBtn.classList.remove('bg-indigo-600', 'hover:bg-indigo-700');
          uploadBtn.classList.add('bg-green-600');
          setTimeout(() => {
            window.location.href = data.redirect_url || '?page=order';
          }, 800);
        } else {
          showError(data.error || 'Upload failed. Please try again.');
          resetUploadBtn();
        }
      } catch (_) {
        showError('Unexpected server response. Please try again.');
        resetUploadBtn();
      }
    } else {
      try {
        const data = JSON.parse(xhr.responseText);
        showError(data.error || 'Upload failed (HTTP ' + xhr.status + ').');
      } catch (_) {
        showError('Upload failed (HTTP ' + xhr.status + ').');
      }
      resetUploadBtn();
    }
  });

  xhr.addEventListener('error', () => {
    showError('Network error. Please check your connection and try again.');
    resetUploadBtn();
  });

  xhr.send(formData);
}

function resetUploadBtn() {
  const uploadBtn         = document.getElementById('upload-btn');
  const progressContainer = document.getElementById('progress-container');
  if (uploadBtn) {
    uploadBtn.disabled = false;
    uploadBtn.textContent = 'Upload Photo';
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

// ─── PRICE CALCULATOR ────────────────────────────────────────────
function initPriceCalculator() {
  const qtyInput   = document.getElementById('quantity');
  const totalEl    = document.getElementById('total-price');
  const sizeRadios = document.querySelectorAll('.size-radio');

  if (!qtyInput || !totalEl || !sizeRadios.length) return;

  function recalc() {
    const selectedSize = document.querySelector('.size-radio:checked');
    const size = selectedSize ? selectedSize.value : Object.keys(PRICES)[0];
    const qty  = Math.max(1, Math.min(100, parseInt(qtyInput.value, 10) || 1));
    const unit = PRICES[size] || 0;
    totalEl.textContent = '\u20AC' + (unit * qty).toFixed(2);
  }

  sizeRadios.forEach(r => r.addEventListener('change', recalc));
  qtyInput.addEventListener('input', recalc);
  recalc();
}

// ─── QTY BUTTONS ─────────────────────────────────────────────────
function initQtyButtons() {
  const minus = document.getElementById('qty-minus');
  const plus  = document.getElementById('qty-plus');
  const qty   = document.getElementById('quantity');
  if (!minus || !plus || !qty) return;

  minus.addEventListener('click', () => {
    const v = parseInt(qty.value, 10) || 1;
    if (v > 1) {
      qty.value = v - 1;
      qty.dispatchEvent(new Event('input'));
    }
  });
  plus.addEventListener('click', () => {
    const v = parseInt(qty.value, 10) || 1;
    if (v < 100) {
      qty.value = v + 1;
      qty.dispatchEvent(new Event('input'));
    }
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
    const qty     = form.querySelector('[name="quantity"]');
    const errors  = [];

    if (name && name.value.trim() === '')            errors.push('Name is required.');
    if (email && !isValidEmail(email.value.trim()))  errors.push('A valid email is required.');
    if (address && address.value.trim() === '')       errors.push('Address is required.');
    if (qty) {
      const q = parseInt(qty.value, 10);
      if (isNaN(q) || q < 1 || q > 100) errors.push('Quantity must be between 1 and 100.');
    }

    if (errors.length) {
      e.preventDefault();
      alert(errors.join('\n'));
    }
  });
}

function isValidEmail(email) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}
