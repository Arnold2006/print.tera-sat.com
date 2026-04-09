</main>
<footer class="bg-white border-t border-gray-200 mt-auto">
  <div class="max-w-6xl mx-auto px-4 py-8 sm:px-6 lg:px-8">
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
      <div class="flex items-center gap-2">
        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
        <span class="text-sm font-semibold text-gray-700">PrintService</span>
      </div>
      <p class="text-sm text-gray-500">&copy; <?php echo date('Y'); ?> PrintService. All rights reserved.</p>
      <div class="flex gap-4 text-sm text-gray-500">
        <a href="#" class="hover:text-indigo-600 transition">Privacy Policy</a>
        <a href="#" class="hover:text-indigo-600 transition">Terms of Service</a>
      </div>
    </div>
  </div>
</footer>

<script src="<?php echo htmlspecialchars(APP_URL . '/public/assets/js/app.js', ENT_QUOTES, 'UTF-8'); ?>"></script>
<script>
  // Mobile menu toggle
  const btn  = document.getElementById('mobile-menu-btn');
  const menu = document.getElementById('mobile-menu');
  const open = document.getElementById('menu-icon-open');
  const close= document.getElementById('menu-icon-close');
  if (btn) {
    btn.addEventListener('click', () => {
      menu.classList.toggle('hidden');
      open.classList.toggle('hidden');
      close.classList.toggle('hidden');
    });
  }
</script>
</body>
</html>
