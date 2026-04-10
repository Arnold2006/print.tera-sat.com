<?php
$currentPage = $_GET['page'] ?? 'home';
$appUrl = APP_URL;
?>
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PrintService &ndash; Quality Photo Prints</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(APP_URL . '/assets/css/app.css', ENT_QUOTES, 'UTF-8'); ?>">
</head>
<body class="h-full bg-gray-50 flex flex-col">

<!-- Navigation -->
<nav class="bg-white shadow-sm sticky top-0 z-50">
  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between h-16">
      <div class="flex items-center">
        <a href="<?php echo htmlspecialchars($appUrl, ENT_QUOTES, 'UTF-8'); ?>/" class="flex items-center gap-2">
          <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
          </svg>
          <span class="text-xl font-bold text-gray-900">PrintService</span>
        </a>
      </div>
      <!-- Desktop nav -->
      <div class="hidden sm:flex sm:items-center sm:gap-4">
        <a href="<?php echo htmlspecialchars($appUrl, ENT_QUOTES, 'UTF-8'); ?>/"
           class="px-3 py-2 rounded-md text-sm font-medium <?php echo $currentPage === 'home' ? 'text-indigo-600 bg-indigo-50' : 'text-gray-600 hover:text-indigo-600 hover:bg-gray-100'; ?> transition">Home</a>
        <a href="<?php echo htmlspecialchars($appUrl, ENT_QUOTES, 'UTF-8'); ?>/?page=about"
           class="px-3 py-2 rounded-md text-sm font-medium <?php echo $currentPage === 'about' ? 'text-indigo-600 bg-indigo-50' : 'text-gray-600 hover:text-indigo-600 hover:bg-gray-100'; ?> transition">About</a>
        <a href="<?php echo htmlspecialchars($appUrl, ENT_QUOTES, 'UTF-8'); ?>/?page=upload"
           class="px-3 py-2 rounded-md text-sm font-medium <?php echo $currentPage === 'upload' ? 'text-indigo-600 bg-indigo-50' : 'text-gray-600 hover:text-indigo-600 hover:bg-gray-100'; ?> transition">Upload</a>
      </div>
      <!-- Mobile hamburger -->
      <div class="flex items-center sm:hidden">
        <button id="mobile-menu-btn" class="p-2 rounded-md text-gray-600 hover:text-gray-900 hover:bg-gray-100 focus:outline-none">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path id="menu-icon-open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            <path id="menu-icon-close" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>
    </div>
  </div>
  <!-- Mobile menu -->
  <div id="mobile-menu" class="hidden sm:hidden border-t border-gray-100">
    <div class="px-4 pt-2 pb-3 space-y-1">
      <a href="<?php echo htmlspecialchars($appUrl, ENT_QUOTES, 'UTF-8'); ?>/"
         class="block px-3 py-2 rounded-md text-base font-medium <?php echo $currentPage === 'home' ? 'text-indigo-600 bg-indigo-50' : 'text-gray-600 hover:text-indigo-600'; ?>">Home</a>
      <a href="<?php echo htmlspecialchars($appUrl, ENT_QUOTES, 'UTF-8'); ?>/?page=about"
         class="block px-3 py-2 rounded-md text-base font-medium <?php echo $currentPage === 'about' ? 'text-indigo-600 bg-indigo-50' : 'text-gray-600 hover:text-indigo-600'; ?>">About</a>
      <a href="<?php echo htmlspecialchars($appUrl, ENT_QUOTES, 'UTF-8'); ?>/?page=upload"
         class="block px-3 py-2 rounded-md text-base font-medium <?php echo $currentPage === 'upload' ? 'text-indigo-600 bg-indigo-50' : 'text-gray-600 hover:text-indigo-600'; ?>">Upload</a>
    </div>
  </div>
</nav>
<main class="flex-1">
