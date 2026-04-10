<?php $appUrl = APP_URL; ?>
<section class="py-24">
  <div class="max-w-md mx-auto px-4 text-center">
    <div class="w-24 h-24 mx-auto mb-6 rounded-full bg-green-100 flex items-center justify-center">
      <svg class="w-12 h-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
    </div>
    <h1 class="text-3xl font-bold text-gray-900 mb-3">
      Order Placed! &#127881;
    </h1>
    <p class="text-gray-500 mb-6">Thank you for your order. We'll start processing it shortly.</p>

    <div class="bg-indigo-50 border border-indigo-100 rounded-2xl p-6 mb-8">
      <p class="text-sm text-gray-500 mb-3">Your order number</p>
      <p class="text-xl font-extrabold text-indigo-600 tracking-wide mb-1">
        <?php echo htmlspecialchars($orderNumber, ENT_QUOTES, 'UTF-8'); ?>
      </p>
      <?php if (!empty($paypalTransactionId)): ?>
      <div class="mt-4 pt-4 border-t border-indigo-100">
        <p class="text-sm text-green-600 font-semibold flex items-center gap-1 mb-2">
          <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
          Payment Accepted
        </p>
        <p class="text-xs text-gray-500">PayPal Transaction ID</p>
        <p class="font-mono text-sm font-semibold text-gray-800 mt-0.5"><?php echo htmlspecialchars($paypalTransactionId, ENT_QUOTES, 'UTF-8'); ?></p>
      </div>
      <?php endif; ?>
      <p class="text-xs text-gray-400 mt-2">Keep this for your records</p>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 mb-8 text-left text-sm text-gray-600">
      <h3 class="font-semibold text-gray-900 mb-3">What happens next?</h3>
      <ul class="space-y-2">
        <li class="flex items-start gap-2">
          <svg class="w-4 h-4 text-indigo-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
          We receive your order and verify the image quality.
        </li>
        <li class="flex items-start gap-2">
          <svg class="w-4 h-4 text-indigo-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
          Your prints are produced within 1&ndash;2 business days.
        </li>
        <li class="flex items-start gap-2">
          <svg class="w-4 h-4 text-indigo-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
          Shipped and delivered within 2&ndash;3 business days.
        </li>
      </ul>
    </div>

    <a href="<?php echo htmlspecialchars($appUrl, ENT_QUOTES, 'UTF-8'); ?>/"
       class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-8 py-3 rounded-xl shadow-md hover:shadow-lg transition">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
      </svg>
      Back to Home
    </a>
  </div>
</section>
