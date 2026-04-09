<?php
$csrfToken = $_SESSION['csrf_token'] ?? '';
$appUrl    = APP_URL . '/public';
?>
<section class="min-h-[70vh] flex items-center justify-center py-16 px-4">
  <div class="w-full max-w-md">
    <div class="text-center mb-8">
      <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-indigo-100 flex items-center justify-center">
        <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
        </svg>
      </div>
      <h1 class="text-2xl font-bold text-gray-900">Admin Login</h1>
      <p class="text-gray-500 text-sm mt-1">PrintService Admin Panel</p>
    </div>

    <?php if (!empty($error)): ?>
    <div class="mb-6 bg-red-50 border border-red-200 text-red-700 rounded-xl p-4 flex items-center gap-3">
      <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
    </div>
    <?php endif; ?>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
      <form method="POST" action="<?php echo htmlspecialchars($appUrl . '/?page=admin&action=login', ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">

        <div class="mb-5">
          <label for="username" class="block text-sm font-semibold text-gray-700 mb-2">Username</label>
          <input type="text" id="username" name="username" required autocomplete="username"
                 class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-400 text-gray-800"
                 placeholder="admin">
        </div>
        <div class="mb-6">
          <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">Password</label>
          <input type="password" id="password" name="password" required autocomplete="current-password"
                 class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-400 text-gray-800"
                 placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;">
        </div>
        <button type="submit"
                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-xl shadow-md hover:shadow-lg transition">
          Sign In
        </button>
      </form>
    </div>
  </div>
</section>
