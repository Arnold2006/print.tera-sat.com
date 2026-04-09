<?php
$appUrl    = APP_URL;
$csrfToken = $_SESSION['csrf_token'] ?? '';
$sizes     = PRINT_SIZES;
$sizeLabel = $sizes[$order['size']]['label'] ?? $order['size'];
$statusColors = [
    'pending'    => 'bg-yellow-100 text-yellow-800',
    'processing' => 'bg-blue-100 text-blue-800',
    'completed'  => 'bg-green-100 text-green-800',
];

// Check permanent first, then uploads
$imgDir = file_exists(PERMANENT_PATH . $order['filename']) ? 'permanent' : 'uploads';
$previewUrl = $appUrl . '/?page=image&file=' . urlencode($order['filename']) . '&dir=' . $imgDir;
?>
<section class="py-10">
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center gap-3 mb-8">
      <a href="<?php echo htmlspecialchars($appUrl . '/?page=admin', ENT_QUOTES, 'UTF-8'); ?>"
         class="text-gray-400 hover:text-indigo-600 transition">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
      </a>
      <h1 class="text-2xl font-bold text-gray-900">Order Detail</h1>
      <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold <?php echo htmlspecialchars($statusColors[$order['status']] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        <?php echo htmlspecialchars(ucfirst($order['status']), ENT_QUOTES, 'UTF-8'); ?>
      </span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Image -->
      <div class="lg:col-span-1">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 text-center">
          <img src="<?php echo htmlspecialchars($previewUrl, ENT_QUOTES, 'UTF-8'); ?>"
               alt="Order image" class="max-w-full max-h-64 mx-auto rounded-xl object-contain mb-4 shadow-md">
          <p class="text-xs text-gray-400 truncate mb-4">
            <?php echo htmlspecialchars($order['original_filename'], ENT_QUOTES, 'UTF-8'); ?>
          </p>
          <a href="<?php echo htmlspecialchars($appUrl . '/?page=admin&action=download_image&id=' . (int)$order['id'], ENT_QUOTES, 'UTF-8'); ?>"
             class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-4 py-2 rounded-xl transition shadow-sm hover:shadow-md">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            Download Image
          </a>
        </div>
      </div>

      <!-- Details -->
      <div class="lg:col-span-2 space-y-4">
        <!-- Order Info -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
          <h2 class="font-semibold text-gray-700 mb-4">Order Information</h2>
          <dl class="grid grid-cols-2 gap-4 text-sm">
            <div>
              <dt class="text-gray-500">Order Number</dt>
              <dd class="font-mono font-bold text-indigo-600 mt-1"><?php echo htmlspecialchars($order['order_number'], ENT_QUOTES, 'UTF-8'); ?></dd>
            </div>
            <?php if (!empty($order['group_order_number'])): ?>
            <div>
              <dt class="text-gray-500">Group Order #</dt>
              <dd class="font-mono font-bold text-indigo-400 mt-1"><?php echo htmlspecialchars($order['group_order_number'], ENT_QUOTES, 'UTF-8'); ?></dd>
            </div>
            <?php endif; ?>
            <div>
              <dt class="text-gray-500">Date</dt>
              <dd class="font-medium text-gray-900 mt-1"><?php echo htmlspecialchars(date('d M Y, H:i', strtotime($order['created_at'])), ENT_QUOTES, 'UTF-8'); ?></dd>
            </div>
            <div>
              <dt class="text-gray-500">Print Size</dt>
              <dd class="font-medium text-gray-900 mt-1"><?php echo htmlspecialchars($sizeLabel, ENT_QUOTES, 'UTF-8'); ?></dd>
            </div>
            <div>
              <dt class="text-gray-500">Quantity</dt>
              <dd class="font-medium text-gray-900 mt-1"><?php echo (int)$order['quantity']; ?> pcs</dd>
            </div>
            <div>
              <dt class="text-gray-500">Total Price</dt>
              <dd class="text-xl font-extrabold text-indigo-600 mt-1">&euro;<?php echo number_format((float)$order['price'], 2); ?></dd>
            </div>
          </dl>
        </div>

        <!-- Customer Info -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
          <h2 class="font-semibold text-gray-700 mb-4">Customer Details</h2>
          <dl class="grid grid-cols-1 gap-3 text-sm">
            <div class="flex gap-3">
              <dt class="w-20 text-gray-500 flex-shrink-0">Name</dt>
              <dd class="font-medium text-gray-900"><?php echo htmlspecialchars($order['customer_name'], ENT_QUOTES, 'UTF-8'); ?></dd>
            </div>
            <div class="flex gap-3">
              <dt class="w-20 text-gray-500 flex-shrink-0">Email</dt>
              <dd class="font-medium text-gray-900"><?php echo htmlspecialchars($order['customer_email'], ENT_QUOTES, 'UTF-8'); ?></dd>
            </div>
            <div class="flex gap-3">
              <dt class="w-20 text-gray-500 flex-shrink-0">Address</dt>
              <dd class="font-medium text-gray-900 whitespace-pre-line"><?php echo htmlspecialchars($order['customer_address'], ENT_QUOTES, 'UTF-8'); ?></dd>
            </div>
          </dl>
        </div>

        <!-- Status Update -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
          <h2 class="font-semibold text-gray-700 mb-4">Update Status</h2>
          <form method="POST" action="<?php echo htmlspecialchars($appUrl . '/?page=admin&action=update_status', ENT_QUOTES, 'UTF-8'); ?>" class="flex items-center gap-3">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="order_id" value="<?php echo (int)$order['id']; ?>">
            <select name="status"
                    class="flex-1 border border-gray-300 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 text-gray-700">
              <?php foreach (['pending', 'processing', 'completed'] as $s): ?>
              <option value="<?php echo $s; ?>" <?php echo $order['status'] === $s ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars(ucfirst($s), ENT_QUOTES, 'UTF-8'); ?>
              </option>
              <?php endforeach; ?>
            </select>
            <button type="submit"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-6 py-2 rounded-xl transition shadow-sm hover:shadow-md">
              Update
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>
