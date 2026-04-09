<?php
$appUrl    = APP_URL . '/public';
$csrfToken = $_SESSION['csrf_token'] ?? '';
$filename  = $_SESSION['upload_filename'] ?? '';
$error     = $_SESSION['order_error'] ?? null;
unset($_SESSION['order_error']);
$previewUrl = $appUrl . '/?page=image&file=' . urlencode($filename);
$sizes = PRINT_SIZES;
?>
<section class="py-16">
  <div class="max-w-3xl mx-auto px-4 sm:px-6">
    <!-- Progress steps -->
    <div class="flex items-center justify-center gap-2 mb-10 text-sm font-medium">
      <span class="flex items-center gap-1 text-green-600">
        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
        Upload
      </span>
      <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
      <span class="text-indigo-600 font-semibold">Options</span>
      <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
      <span class="text-gray-400">Summary</span>
      <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
      <span class="text-gray-400">Confirm</span>
    </div>

    <?php if ($error): ?>
    <div class="mb-6 bg-red-50 border border-red-200 text-red-700 rounded-xl p-4 flex items-start gap-3">
      <svg class="w-5 h-5 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      <span><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></span>
    </div>
    <?php endif; ?>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-0">
        <!-- Image preview -->
        <div class="bg-gray-50 flex items-center justify-center p-8">
          <img src="<?php echo htmlspecialchars($previewUrl, ENT_QUOTES, 'UTF-8'); ?>"
               alt="Your photo" class="max-h-64 max-w-full rounded-xl object-contain shadow-md">
        </div>

        <!-- Form -->
        <div class="p-8">
          <form id="order-form" method="POST" action="<?php echo htmlspecialchars($appUrl . '/?page=order&action=summary', ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">

            <!-- Print size -->
            <div class="mb-6">
              <label class="block text-sm font-semibold text-gray-700 mb-3">Print Size</label>
              <div class="space-y-2">
                <?php foreach ($sizes as $key => $s): ?>
                <label class="flex items-center justify-between p-3 border border-gray-200 rounded-xl cursor-pointer hover:border-indigo-400 hover:bg-indigo-50 transition">
                  <div class="flex items-center gap-3">
                    <input type="radio" name="size" value="<?php echo htmlspecialchars($key, ENT_QUOTES, 'UTF-8'); ?>"
                           class="w-4 h-4 text-indigo-600 size-radio" <?php echo $key === '10x15' ? 'checked' : ''; ?>>
                    <span class="font-medium text-gray-800"><?php echo htmlspecialchars($s['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                  </div>
                  <span class="text-indigo-600 font-bold">&euro;<?php echo number_format($s['price'], 2); ?></span>
                </label>
                <?php endforeach; ?>
              </div>
            </div>

            <!-- Quantity -->
            <div class="mb-6">
              <label for="quantity" class="block text-sm font-semibold text-gray-700 mb-2">Quantity</label>
              <div class="flex items-center gap-3">
                <button type="button" id="qty-minus"
                        class="w-10 h-10 rounded-lg bg-gray-100 hover:bg-gray-200 font-bold text-gray-700 flex items-center justify-center transition">&minus;</button>
                <input type="number" id="quantity" name="quantity" value="1" min="1" max="100"
                       class="w-20 text-center border border-gray-300 rounded-lg py-2 font-semibold text-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                <button type="button" id="qty-plus"
                        class="w-10 h-10 rounded-lg bg-gray-100 hover:bg-gray-200 font-bold text-gray-700 flex items-center justify-center transition">+</button>
              </div>
            </div>

            <!-- Price display -->
            <div class="mb-6 p-4 bg-indigo-50 rounded-xl flex items-center justify-between">
              <span class="text-gray-600 font-medium">Total Price</span>
              <span id="total-price" class="text-2xl font-extrabold text-indigo-600">&euro;2.99</span>
            </div>

            <!-- Customer info -->
            <h3 class="text-sm font-semibold text-gray-700 mb-3 mt-4">Your Details</h3>
            <div class="space-y-3">
              <div>
                <label for="customer_name" class="block text-xs text-gray-500 mb-1">Full Name *</label>
                <input type="text" id="customer_name" name="customer_name" required maxlength="100"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                       placeholder="Jane Doe">
              </div>
              <div>
                <label for="customer_email" class="block text-xs text-gray-500 mb-1">Email Address *</label>
                <input type="email" id="customer_email" name="customer_email" required maxlength="100"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                       placeholder="jane@example.com">
              </div>
              <div>
                <label for="customer_address" class="block text-xs text-gray-500 mb-1">Delivery Address *</label>
                <textarea id="customer_address" name="customer_address" required rows="3"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 resize-none"
                          placeholder="Street, City, Postcode, Country"></textarea>
              </div>
            </div>

            <button type="submit"
                    class="mt-6 w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-xl shadow-md hover:shadow-lg transition">
              Continue to Summary &rarr;
            </button>
          </form>
        </div>
      </div>
    </div>

    <!-- Price config for JS -->
    <script>
      window.PRINT_SIZES = <?php echo json_encode(array_map(fn($v) => $v['price'], PRINT_SIZES)); ?>;
    </script>
  </div>
</section>
