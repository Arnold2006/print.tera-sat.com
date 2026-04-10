<?php
$appUrl    = APP_URL;
$csrfToken = $_SESSION['csrf_token'] ?? '';
$files     = $_SESSION['upload_files'] ?? [];
$error     = $_SESSION['order_error'] ?? null;
unset($_SESSION['order_error']);
$sizes       = PRINT_SIZES;
$savedData   = $_SESSION['order_data'] ?? [];
$savedItems  = $savedData['items'] ?? [];
?>
<style>
  .size-label:has(.size-radio:checked) {
    border-color: #6366f1;
    background-color: #eef2ff;
  }
</style>
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

    <form id="order-form" method="POST" action="<?php echo htmlspecialchars($appUrl . '/?page=order&action=summary', ENT_QUOTES, 'UTF-8'); ?>">
      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">

      <!-- Per-image print option cards -->
      <div class="space-y-4 mb-6">
        <h2 class="text-lg font-bold text-gray-900">
          Print Options
          <span class="text-sm font-normal text-gray-500 ml-2"><?php echo count($files); ?> photo<?php echo count($files) !== 1 ? 's' : ''; ?></span>
        </h2>

        <?php foreach ($files as $i => $file): ?>
        <?php
          $previewUrl   = $appUrl . '/?page=image&file=' . urlencode($file['filename']) . '&dir=uploads';
          $savedItem    = $savedItems[$i] ?? [];
          $defaultSize  = array_key_first(PRINT_SIZES);
          $savedSize    = (isset($savedItem['size']) && array_key_exists($savedItem['size'], PRINT_SIZES)) ? $savedItem['size'] : $defaultSize;
          $savedQty     = (int) ($savedItem['quantity'] ?? 1);
          $defaultPrice = PRINT_SIZES[$savedSize]['price'];
        ?>
        <div class="order-item bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
          <div class="flex gap-4">
            <!-- Thumbnail -->
            <div class="flex-shrink-0">
              <img src="<?php echo htmlspecialchars($previewUrl, ENT_QUOTES, 'UTF-8'); ?>"
                   alt="<?php echo htmlspecialchars($file['original_filename'], ENT_QUOTES, 'UTF-8'); ?>"
                   class="w-24 h-24 object-cover rounded-xl shadow-sm">
            </div>
            <div class="flex-1 min-w-0">
              <!-- Filename -->
              <p class="text-sm font-semibold text-gray-700 mb-3 truncate">
                <?php echo htmlspecialchars($file['original_filename'], ENT_QUOTES, 'UTF-8'); ?>
              </p>

              <!-- Size selector -->
              <p class="text-xs font-semibold text-gray-500 mb-2">Print Size</p>
              <div class="flex flex-wrap gap-2 mb-3">
                <?php foreach ($sizes as $key => $s): ?>
                <label class="size-label flex items-center gap-1.5 px-3 py-1.5 border border-gray-200 rounded-lg cursor-pointer hover:border-indigo-400 hover:bg-indigo-50 transition text-sm select-none">
                  <input type="radio" name="size[<?php echo $i; ?>]" value="<?php echo htmlspecialchars($key, ENT_QUOTES, 'UTF-8'); ?>"
                         class="size-radio w-3.5 h-3.5 text-indigo-600"
                         <?php echo $key === $savedSize ? 'checked' : ''; ?>>
                  <span class="font-medium text-gray-800"><?php echo htmlspecialchars($s['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                  <span class="text-indigo-600 font-semibold">&euro;<?php echo number_format($s['price'], 2); ?></span>
                </label>
                <?php endforeach; ?>
              </div>

              <!-- Quantity + item total -->
              <div class="flex items-center gap-2 flex-wrap">
                <span class="text-xs font-semibold text-gray-500">Qty</span>
                <button type="button"
                        class="qty-minus w-8 h-8 rounded-lg bg-gray-100 hover:bg-gray-200 font-bold text-gray-700 flex items-center justify-center transition text-lg leading-none">&minus;</button>
                <input type="number" name="quantity[<?php echo $i; ?>]" value="<?php echo $savedQty; ?>" min="1" max="100"
                       class="item-qty w-16 text-center border border-gray-300 rounded-lg py-1.5 font-semibold text-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-400 text-sm">
                <button type="button"
                        class="qty-plus w-8 h-8 rounded-lg bg-gray-100 hover:bg-gray-200 font-bold text-gray-700 flex items-center justify-center transition text-lg leading-none">+</button>
                <span class="ml-auto text-lg font-extrabold text-indigo-600 item-total">&euro;<?php echo number_format($defaultPrice * $savedQty, 2); ?></span>
              </div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Grand total -->
      <div class="bg-indigo-50 rounded-2xl p-4 mb-6 flex items-center justify-between">
        <span class="font-semibold text-gray-700">Grand Total</span>
        <?php
          $defaultSize  = array_key_first(PRINT_SIZES);
          $initialTotal = 0;
          foreach (array_keys($files) as $idx) {
              $savedSz       = $savedItems[$idx]['size'] ?? null;
              $size          = ($savedSz !== null && array_key_exists($savedSz, PRINT_SIZES)) ? $savedSz : $defaultSize;
              $pricePerUnit  = PRINT_SIZES[$size]['price'];
              $qty           = (int) ($savedItems[$idx]['quantity'] ?? 1);
              $initialTotal += $pricePerUnit * $qty;
          }
        ?>
        <span id="grand-total" class="text-2xl font-extrabold text-indigo-600">&euro;<?php echo number_format($initialTotal, 2); ?></span>
      </div>

      <!-- Customer details -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
        <h2 class="font-semibold text-gray-700 mb-4">Your Details</h2>
        <div class="space-y-4">
          <div>
            <label for="customer_name" class="block text-xs font-medium text-gray-500 mb-1">Full Name *</label>
            <input type="text" id="customer_name" name="customer_name" required maxlength="100"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                   placeholder="Jane Doe"
                   value="<?php echo htmlspecialchars($savedData['customer_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
          </div>
          <div>
            <label for="customer_email" class="block text-xs font-medium text-gray-500 mb-1">Email Address *</label>
            <input type="email" id="customer_email" name="customer_email" required maxlength="100"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                   placeholder="jane@example.com"
                   value="<?php echo htmlspecialchars($savedData['customer_email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
          </div>
          <div>
            <label for="customer_address" class="block text-xs font-medium text-gray-500 mb-1">Delivery Address *</label>
            <textarea id="customer_address" name="customer_address" required rows="3"
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 resize-none"
                      placeholder="Street, City, Postcode, Country"><?php echo htmlspecialchars($savedData['customer_address'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
          </div>
        </div>
      </div>

      <button type="submit"
              class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-xl shadow-md hover:shadow-lg transition">
        Continue to Summary &rarr;
      </button>
    </form>

    <!-- Price config for JS -->
    <script>
      window.PRINT_SIZES = <?php echo json_encode(
          array_map(fn($v) => $v['price'], PRINT_SIZES),
          JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR
      ); ?>;
    </script>
  </div>
</section>
