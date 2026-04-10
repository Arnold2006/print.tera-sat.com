<?php
$appUrl = APP_URL;
$sizes  = PRINT_SIZES;
?>
<!-- Hero -->
<section class="relative bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-500 text-white overflow-hidden">
  <div class="absolute inset-0 opacity-10">
    <svg class="w-full h-full" viewBox="0 0 800 600" preserveAspectRatio="xMidYMid slice">
      <circle cx="200" cy="200" r="200" fill="white"/>
      <circle cx="600" cy="400" r="250" fill="white"/>
    </svg>
  </div>
  <div class="relative max-w-6xl mx-auto px-4 py-24 sm:px-6 lg:px-8 text-center">
    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold mb-6 leading-tight">
      Print Your<br class="hidden sm:block"> Memories
    </h1>
    <p class="text-xl sm:text-2xl text-indigo-100 mb-10 max-w-2xl mx-auto">
      High-quality photo prints delivered straight to your door. Fast, easy, beautiful.
    </p>
    <a href="<?php echo htmlspecialchars($appUrl, ENT_QUOTES, 'UTF-8'); ?>/?page=upload"
       class="inline-flex items-center gap-2 bg-white text-indigo-600 font-bold px-8 py-4 rounded-full text-lg shadow-xl hover:shadow-2xl hover:bg-indigo-50 transition transform hover:-translate-y-0.5">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
      </svg>
      Upload &amp; Print Now
    </a>
  </div>
</section>

<!-- Features -->
<section class="py-20 bg-white">
  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
    <h2 class="text-3xl font-bold text-center text-gray-900 mb-4">Why Choose PrintService?</h2>
    <p class="text-center text-gray-500 mb-14 max-w-xl mx-auto">We make photo printing simple, affordable and reliable.</p>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
      <!-- Card 1 -->
      <div class="flex flex-col items-center text-center p-8 rounded-2xl bg-indigo-50 hover:shadow-lg transition">
        <div class="w-16 h-16 rounded-2xl bg-indigo-100 flex items-center justify-center mb-6">
          <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
          </svg>
        </div>
        <h3 class="text-xl font-bold text-gray-900 mb-3">Premium Quality</h3>
        <p class="text-gray-600">Professional-grade photo paper and inks for vibrant, long-lasting prints.</p>
      </div>
      <!-- Card 2 -->
      <div class="flex flex-col items-center text-center p-8 rounded-2xl bg-purple-50 hover:shadow-lg transition">
        <div class="w-16 h-16 rounded-2xl bg-purple-100 flex items-center justify-center mb-6">
          <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
          </svg>
        </div>
        <h3 class="text-xl font-bold text-gray-900 mb-3">Easy Upload</h3>
        <p class="text-gray-600">Drag &amp; drop your photos. JPG, PNG, WebP supported. Up to 10 MB.</p>
      </div>
      <!-- Card 3 -->
      <div class="flex flex-col items-center text-center p-8 rounded-2xl bg-pink-50 hover:shadow-lg transition">
        <div class="w-16 h-16 rounded-2xl bg-pink-100 flex items-center justify-center mb-6">
          <svg class="w-8 h-8 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
          </svg>
        </div>
        <h3 class="text-xl font-bold text-gray-900 mb-3">Fast Delivery</h3>
        <p class="text-gray-600">Processed and shipped within 2-3 business days straight to your door.</p>
      </div>
    </div>
  </div>
</section>

<!-- Pricing -->
<section class="py-20 bg-gray-50">
  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
    <h2 class="text-3xl font-bold text-center text-gray-900 mb-4">Simple Pricing</h2>
    <p class="text-center text-gray-500 mb-4 max-w-xl mx-auto">No hidden fees. Pay only for what you print.</p>
    <div class="flex items-center justify-center gap-2 mb-14">
      <span class="inline-flex items-center gap-2 bg-amber-50 border border-amber-200 text-amber-800 text-sm font-medium px-4 py-2 rounded-full">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
        </svg>
        Shipping: &euro;<?php echo number_format(SHIPPING_COST, 2); ?> per order (flat rate, any number of prints)
      </span>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-4xl mx-auto">
      <?php
      $btnClasses = ['bg-indigo-600', 'bg-gray-900', 'bg-purple-600'];
      $i = 0;
      foreach ($sizes as $key => $s):
        $featured  = ($i === 0);
        $btnClass  = $btnClasses[$i % count($btnClasses)];
        $badge     = $featured ? 'Most Popular' : '';
        $i++;
      ?>
      <div class="relative bg-white rounded-2xl shadow-md hover:shadow-xl transition p-8 flex flex-col items-center text-center <?php echo $featured ? 'ring-2 ring-indigo-500' : ''; ?>">
        <?php if ($badge): ?>
        <span class="absolute -top-3 left-1/2 -translate-x-1/2 bg-indigo-500 text-white text-xs font-bold px-3 py-1 rounded-full"><?php echo htmlspecialchars($badge, ENT_QUOTES, 'UTF-8'); ?></span>
        <?php endif; ?>
        <div class="w-20 h-20 bg-gray-100 rounded-xl flex items-center justify-center mb-6">
          <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
          </svg>
        </div>
        <h3 class="text-2xl font-bold text-gray-900 mb-1"><?php echo htmlspecialchars($s['label'], ENT_QUOTES, 'UTF-8'); ?></h3>
        <p class="text-4xl font-extrabold text-indigo-600 my-4">&euro;<?php echo number_format($s['price'], 2); ?></p>
        <p class="text-gray-500 text-sm mb-6">per print</p>
        <a href="<?php echo htmlspecialchars($appUrl, ENT_QUOTES, 'UTF-8'); ?>/?page=upload"
           class="w-full py-3 px-6 rounded-xl text-white font-semibold <?php echo htmlspecialchars($btnClass, ENT_QUOTES, 'UTF-8'); ?> hover:opacity-90 transition">
          Order Now
        </a>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="py-20 bg-gradient-to-r from-indigo-600 to-purple-600 text-white text-center">
  <div class="max-w-3xl mx-auto px-4">
    <h2 class="text-3xl font-bold mb-4">Ready to print your memories?</h2>
    <p class="text-indigo-200 mb-8 text-lg">Upload your photo now and get stunning prints delivered to you.</p>
    <a href="<?php echo htmlspecialchars($appUrl, ENT_QUOTES, 'UTF-8'); ?>/?page=upload"
       class="inline-flex items-center gap-2 bg-white text-indigo-600 font-bold px-8 py-4 rounded-full text-lg shadow-xl hover:bg-indigo-50 transition">
      Get Started Free
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
      </svg>
    </a>
  </div>
</section>
