<?php
$appUrl    = APP_URL;
$csrfToken = $_SESSION['csrf_token'] ?? '';
?>
<section class="py-16">
  <div class="max-w-2xl mx-auto px-4 sm:px-6">
    <div class="text-center mb-10">
      <h1 class="text-3xl font-bold text-gray-900 mb-3">Upload Your Photo</h1>
      <p class="text-gray-500">Supported formats: JPG, PNG, WebP &bull; Max 10 MB</p>
    </div>

    <!-- Error message -->
    <div id="upload-error" class="hidden mb-6 bg-red-50 border border-red-200 text-red-700 rounded-xl p-4 flex items-start gap-3">
      <svg class="w-5 h-5 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      <span id="upload-error-text"></span>
    </div>

    <!-- Drop zone -->
    <div id="drop-zone"
         class="border-2 border-dashed border-indigo-300 rounded-2xl bg-indigo-50 hover:bg-indigo-100 hover:border-indigo-400 transition cursor-pointer p-12 flex flex-col items-center justify-center gap-4 text-center"
         onclick="document.getElementById('file-input').click()">
      <div id="drop-zone-content">
        <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-indigo-100 flex items-center justify-center">
          <svg class="w-8 h-8 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
          </svg>
        </div>
        <p class="text-lg font-semibold text-indigo-700">Drag &amp; drop your image here</p>
        <p class="text-sm text-indigo-500 mt-1">or <span class="underline font-medium">click to browse</span></p>
      </div>
      <!-- Preview -->
      <div id="preview-container" class="hidden w-full">
        <img id="image-preview" src="" alt="Preview" class="max-h-64 mx-auto rounded-xl object-contain shadow-md">
        <p id="preview-filename" class="text-sm text-gray-500 mt-3"></p>
      </div>
    </div>

    <!-- File input (hidden) -->
    <input type="file" id="file-input" class="hidden" accept=".jpg,.jpeg,.png,.webp" name="image">

    <!-- Progress bar -->
    <div id="progress-container" class="hidden mt-6">
      <div class="flex justify-between text-sm text-gray-600 mb-2">
        <span>Uploading&hellip;</span>
        <span id="progress-text">0%</span>
      </div>
      <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
        <div id="progress-bar" class="bg-indigo-500 h-3 rounded-full transition-all duration-300" style="width:0%"></div>
      </div>
    </div>

    <!-- Upload button -->
    <div class="mt-6 text-center">
      <button id="upload-btn"
              class="hidden w-full sm:w-auto bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-10 py-3 rounded-xl shadow-md hover:shadow-lg transition disabled:opacity-50 disabled:cursor-not-allowed"
              disabled>
        Upload Photo
      </button>
    </div>

    <!-- Hidden CSRF token for JS -->
    <span id="csrf-token" data-token="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>" class="hidden"></span>
    <span id="upload-url" data-url="<?php echo htmlspecialchars($appUrl . '/?page=upload&action=process', ENT_QUOTES, 'UTF-8'); ?>" class="hidden"></span>
  </div>
</section>
