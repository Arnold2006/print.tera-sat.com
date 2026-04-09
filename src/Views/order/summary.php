<?php
$appUrl    = APP_URL;
$csrfToken = $_SESSION['csrf_token'] ?? '';
$data      = $_SESSION['order_data'];
$filename  = $_SESSION['upload_filename'];
$origName  = $_SESSION['upload_original_filename'] ?? $filename;
$sizes     = PRINT_SIZES;
$sizeLabel = $sizes[$data['size']]['label'] ?? $data['size'];
$previewUrl = $appUrl . '/?page=image&file=' . urlencode($filename);
?>
<section class="py-16">
  <div class="max-w-2xl mx-auto px-4 sm:px-6">
    <!-- Steps -->
    <div class="flex items-center justify-center gap-2 mb-10 text-sm font-medium">
      <span class="flex items-center gap-1 text-green-600">
        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
        Upload
      </span>
      <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
      <span class="flex items-center gap-1 text-green-600">
        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
        Options
      </span>
      <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
      <span class="text-indigo-600 font-semibold">Summary</span>
      <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
      <span class="text-gray-400">Confirm</span>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
      <h1 class="text-2xl font-bold text-gray-900 mb-8">Order Summary</h1>

      <div class="flex flex-col sm:flex-row gap-6 mb-8">
        <img src="<?php echo htmlspecialchars($previewUrl, ENT_QUOTES, 'UTF-8'); ?>"
             alt="Your photo" class="w-full sm:w-40 h-40 object-cover rounded-xl shadow-md flex-shrink-0">
        <div class="flex-1">
          <h3 class="font-semibold text-gray-700 mb-1 text-sm">Image</h3>
          <p class="text-gray-900 mb-4 truncate"><?php echo htmlspecialchars($origName, ENT_QUOTES, 'UTF-8'); ?></p>

          <div class="grid grid-cols-2 gap-3 text-sm">
            <div>
              <span class="text-gray-500">Print Size</span>
              <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($sizeLabel, ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
            <div>
              <span class="text-gray-500">Quantity</span>
              <p class="font-semibold text-gray-900"><?php echo (int)$data['quantity']; ?> pcs</p>
            </div>
            <div>
              <span class="text-gray-500">Price / unit</span>
              <p class="font-semibold text-gray-900">&euro;<?php echo number_format($data['price_per_unit'], 2); ?></p>
            </div>
            <div>
              <span class="text-gray-500">Total</span>
              <p class="text-2xl font-extrabold text-indigo-600">&euro;<?php echo number_format($data['total_price'], 2); ?></p>
            </div>
          </div>
        </div>
      </div>

      <div class="border-t border-gray-100 pt-6 mb-8">
        <h3 class="font-semibold text-gray-700 mb-4">Delivery Details</h3>
        <dl class="grid grid-cols-1 gap-2 text-sm">
          <div class="flex gap-2">
            <dt class="w-24 text-gray-500 flex-shrink-0">Name</dt>
            <dd class="text-gray-900 font-medium"><?php echo htmlspecialchars($data['customer_name'], ENT_QUOTES, 'UTF-8'); ?></dd>
          </div>
          <div class="flex gap-2">
            <dt class="w-24 text-gray-500 flex-shrink-0">Email</dt>
            <dd class="text-gray-900 font-medium"><?php echo htmlspecialchars($data['customer_email'], ENT_QUOTES, 'UTF-8'); ?></dd>
          </div>
          <div class="flex gap-2">
            <dt class="w-24 text-gray-500 flex-shrink-0">Address</dt>
            <dd class="text-gray-900 font-medium whitespace-pre-line"><?php echo htmlspecialchars($data['customer_address'], ENT_QUOTES, 'UTF-8'); ?></dd>
          </div>
        </dl>
      </div>

      <div class="flex flex-col sm:flex-row gap-3">
        <a href="<?php echo htmlspecialchars($appUrl . '/?page=order', ENT_QUOTES, 'UTF-8'); ?>"
           class="flex-1 text-center py-3 px-6 border border-gray-300 rounded-xl text-gray-700 font-semibold hover:bg-gray-50 transition">
          &larr; Edit Order
        </a>
        <form method="POST" action="<?php echo htmlspecialchars($appUrl . '/?page=order&action=place', ENT_QUOTES, 'UTF-8'); ?>" class="flex-1">
          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
          <button type="submit"
                  class="w-full py-3 px-6 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-md hover:shadow-lg transition">
            Confirm Order &rarr;
          </button>
        </form>
      </div>
    </div>
  </div>
</section>
