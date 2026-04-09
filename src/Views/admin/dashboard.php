<?php
$appUrl   = APP_URL . '/public';
$adminUser = htmlspecialchars($_SESSION['admin_username'] ?? 'Admin', ENT_QUOTES, 'UTF-8');
$statusColors = [
    'pending'    => 'bg-yellow-100 text-yellow-800',
    'processing' => 'bg-blue-100 text-blue-800',
    'completed'  => 'bg-green-100 text-green-800',
];
?>
<section class="py-10">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Header bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Orders Dashboard</h1>
        <p class="text-gray-500 text-sm mt-1">Welcome back, <?php echo $adminUser; ?></p>
      </div>
      <a href="<?php echo htmlspecialchars($appUrl . '/?page=admin&action=logout', ENT_QUOTES, 'UTF-8'); ?>"
         class="inline-flex items-center gap-2 text-sm font-medium text-gray-600 hover:text-red-600 border border-gray-300 hover:border-red-300 px-4 py-2 rounded-lg transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
        </svg>
        Logout
      </a>
    </div>

    <!-- Stats -->
    <?php
    $totalOrders     = count($orders);
    $pendingOrders   = count(array_filter($orders, fn($o) => $o['status'] === 'pending'));
    $completedOrders = count(array_filter($orders, fn($o) => $o['status'] === 'completed'));
    $totalRevenue    = array_sum(array_column($orders, 'price'));
    ?>
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
      <?php foreach ([
        ['Total Orders', $totalOrders,      'bg-indigo-50 text-indigo-700',  'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
        ['Pending',      $pendingOrders,    'bg-yellow-50 text-yellow-700',  'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['Completed',    $completedOrders,  'bg-green-50 text-green-700',    'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['Revenue',      '&euro;' . number_format($totalRevenue, 2), 'bg-purple-50 text-purple-700', 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
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
      <div class="px-6 py-4 border-b border-gray-100">
        <h2 class="font-semibold text-gray-900">All Orders</h2>
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
              <?php foreach (['Order #', 'Customer', 'Size', 'Qty', 'Price', 'Status', 'Date', 'Actions'] as $th): ?>
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
      <?php endif; ?>
    </div>
  </div>
</section>
