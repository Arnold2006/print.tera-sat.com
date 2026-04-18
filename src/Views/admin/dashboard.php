<?php
$appUrl   = APP_URL;
$adminUser = htmlspecialchars($_SESSION['admin_username'] ?? 'Admin', ENT_QUOTES, 'UTF-8');
$statusColors = [
    'pending'    => 'bg-yellow-100 text-yellow-800',
    'processing' => 'bg-blue-100 text-blue-800',
    'completed'  => 'bg-green-100 text-green-800',
];
$flash = $_SESSION['admin_flash'] ?? null;
unset($_SESSION['admin_flash']);
$csrfToken = $_SESSION['csrf_token'] ?? '';
?>
<section class="py-10">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Header bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Orders Dashboard</h1>
        <p class="text-gray-500 text-sm mt-1">Welcome back, <?php echo $adminUser; ?></p>
      </div>
      <div class="flex flex-wrap items-center gap-3">
        <!-- Clean orphaned files form -->
        <form method="post" action="<?php echo htmlspecialchars($appUrl . '/?page=admin&action=clean_orphans', ENT_QUOTES, 'UTF-8'); ?>"
              onsubmit="return confirm('Delete all uploaded files that are not referenced by any order? This cannot be undone.');">
          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
          <button type="submit"
                  class="inline-flex items-center gap-2 text-sm font-medium text-gray-600 hover:text-red-600 border border-gray-300 hover:border-red-300 px-4 py-2 rounded-lg transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
            Clean orphaned files
          </button>
        </form>
        <!-- Logout — POST with CSRF to prevent CSRF-forced logout -->
        <form method="post" action="<?php echo htmlspecialchars($appUrl . '/?page=admin&action=logout', ENT_QUOTES, 'UTF-8'); ?>">
          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
          <button type="submit"
                  class="inline-flex items-center gap-2 text-sm font-medium text-gray-600 hover:text-red-600 border border-gray-300 hover:border-red-300 px-4 py-2 rounded-lg transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
            </svg>
            Logout
          </button>
        </form>
      </div>
    </div>

    <!-- Flash message -->
    <?php if ($flash): ?>
    <div class="mb-6 flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium
                <?php echo $flash['type'] === 'error' ? 'bg-red-50 text-red-700 border border-red-200' : 'bg-green-50 text-green-700 border border-green-200'; ?>">
      <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <?php if ($flash['type'] === 'error'): ?>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
        <?php else: ?>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        <?php endif; ?>
      </svg>
      <?php echo htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8'); ?>
    </div>
    <?php endif; ?>

    <!-- Stats (always show totals across all orders, not just the current page) -->
    <?php
    $pendingOrders   = count(array_filter($orders, fn($o) => $o['status'] === 'pending'));
    $completedOrders = count(array_filter($orders, fn($o) => $o['status'] === 'completed'));
    $pageRevenue     = array_sum(array_column($orders, 'price'));
    ?>
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
      <?php foreach ([
        ['Total Orders', $totalOrders,      'bg-indigo-50 text-indigo-700',  'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
        ['Pending on Page',   $pendingOrders,    'bg-yellow-50 text-yellow-700',  'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['Completed on Page', $completedOrders,  'bg-green-50 text-green-700',    'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['Revenue on Page',   '&euro;' . number_format($pageRevenue, 2), 'bg-purple-50 text-purple-700', 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
      ] as [$label, $val, $cls, $icon]): ?>
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl <?php echo htmlspecialchars($cls, ENT_QUOTES, 'UTF-8'); ?> flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?php echo htmlspecialchars($icon, ENT_QUOTES, 'UTF-8'); ?>"/></svg>
          </div>
          <div>
            <p class="text-xs text-gray-500"><?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?></p>
            <p class="text-xl font-bold text-gray-900"><?php echo $val; ?></p>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Orders table -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
        <h2 class="font-semibold text-gray-900">All Orders</h2>
        <?php if ($totalPages > 1): ?>
        <span class="text-xs text-gray-400">Page <?php echo (int)$page; ?> of <?php echo (int)$totalPages; ?></span>
        <?php endif; ?>
      </div>
      <?php if (empty($orders)): ?>
      <div class="p-12 text-center text-gray-400">
        <svg class="w-12 h-12 mx-auto mb-4 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
        </svg>
        <p>No orders yet.</p>
      </div>
      <?php else: ?>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-100">
          <thead class="bg-gray-50">
            <tr>
              <?php foreach (['Order #', 'Group #', 'Customer', 'Size', 'Qty', 'Price', 'Status', 'Date', 'Actions'] as $th): ?>
              <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                <?php echo htmlspecialchars($th, ENT_QUOTES, 'UTF-8'); ?>
              </th>
              <?php endforeach; ?>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            <?php foreach ($orders as $order): ?>
            <tr class="hover:bg-gray-50 transition">
              <td class="px-4 py-3 font-mono text-sm font-semibold text-indigo-600">
                <?php echo htmlspecialchars($order['order_number'], ENT_QUOTES, 'UTF-8'); ?>
              </td>
              <td class="px-4 py-3 font-mono text-xs text-gray-500">
                <?php echo $order['group_order_number'] ? htmlspecialchars($order['group_order_number'], ENT_QUOTES, 'UTF-8') : '—'; ?>
              </td>
              <td class="px-4 py-3">
                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($order['customer_name'], ENT_QUOTES, 'UTF-8'); ?></div>
                <div class="text-xs text-gray-400"><?php echo htmlspecialchars($order['customer_email'], ENT_QUOTES, 'UTF-8'); ?></div>
              </td>
              <td class="px-4 py-3 text-sm text-gray-700"><?php echo htmlspecialchars($order['size'], ENT_QUOTES, 'UTF-8'); ?></td>
              <td class="px-4 py-3 text-sm text-gray-700 text-center"><?php echo (int)$order['quantity']; ?></td>
              <td class="px-4 py-3 text-sm font-semibold text-gray-900">&euro;<?php echo number_format((float)$order['price'], 2); ?></td>
              <td class="px-4 py-3">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold <?php echo htmlspecialchars($statusColors[$order['status']] ?? 'bg-gray-100 text-gray-700', ENT_QUOTES, 'UTF-8'); ?>">
                  <?php echo htmlspecialchars(ucfirst($order['status']), ENT_QUOTES, 'UTF-8'); ?>
                </span>
              </td>
              <td class="px-4 py-3 text-xs text-gray-400 whitespace-nowrap">
                <?php echo htmlspecialchars(date('d M Y', strtotime($order['created_at'])), ENT_QUOTES, 'UTF-8'); ?>
              </td>
              <td class="px-4 py-3">
                <a href="<?php echo htmlspecialchars($appUrl . '/?page=admin&action=order&id=' . (int)$order['id'], ENT_QUOTES, 'UTF-8'); ?>"
                   class="inline-flex items-center gap-1 text-xs font-medium text-indigo-600 hover:text-indigo-800 border border-indigo-200 hover:border-indigo-400 px-3 py-1 rounded-lg transition">
                  View
                </a>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <?php if ($totalPages > 1): ?>
      <div class="px-6 py-4 border-t border-gray-100 flex items-center justify-between">
        <span class="text-xs text-gray-500">
          Showing orders <?php echo number_format(($page - 1) * $perPage + 1); ?>–<?php echo number_format(min($page * $perPage, $totalOrders)); ?> of <?php echo number_format($totalOrders); ?>
        </span>
        <div class="flex items-center gap-2">
          <?php if ($page > 1): ?>
          <a href="<?php echo htmlspecialchars($appUrl . '/?page=admin&p=' . ($page - 1), ENT_QUOTES, 'UTF-8'); ?>"
             class="inline-flex items-center gap-1 text-xs font-medium text-gray-600 hover:text-indigo-600 border border-gray-300 hover:border-indigo-300 px-3 py-1.5 rounded-lg transition">
            &laquo; Prev
          </a>
          <?php endif; ?>
          <?php if ($page < $totalPages): ?>
          <a href="<?php echo htmlspecialchars($appUrl . '/?page=admin&p=' . ($page + 1), ENT_QUOTES, 'UTF-8'); ?>"
             class="inline-flex items-center gap-1 text-xs font-medium text-gray-600 hover:text-indigo-600 border border-gray-300 hover:border-indigo-300 px-3 py-1.5 rounded-lg transition">
            Next &raquo;
          </a>
          <?php endif; ?>
        </div>
      </div>
      <?php endif; ?>

      <?php endif; ?>
    </div>
  </div>
</section>
