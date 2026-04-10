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
        <button type="button" id="open-privacy-modal" class="hover:text-indigo-600 transition cursor-pointer">Privacy Policy</button>
        <button type="button" id="open-tos-modal" class="hover:text-indigo-600 transition cursor-pointer">Terms of Service</button>
      </div>
    </div>
  </div>
</footer>

<!-- Privacy Policy Modal -->
<div id="privacy-modal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true" aria-labelledby="privacy-modal-title">
  <div class="absolute inset-0 bg-black/50" id="privacy-modal-backdrop"></div>
  <div class="relative flex items-center justify-center min-h-screen p-4">
    <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-2xl max-h-[80vh] flex flex-col">
      <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
        <h2 id="privacy-modal-title" class="text-lg font-semibold text-gray-900">Privacy Policy</h2>
        <button type="button" id="close-privacy-modal" class="text-gray-400 hover:text-gray-600 transition" aria-label="Close">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>
      <div class="overflow-y-auto px-6 py-4 text-sm text-gray-700 space-y-4">
        <p class="text-xs text-gray-400">Last updated: <?php echo date('F j, Y'); ?></p>

        <h3 class="font-semibold text-gray-800">1. Information We Collect</h3>
        <p>We collect information you provide directly to us when you place an order, including your name, email address, postal address, and the photos you upload for printing. We also automatically collect certain technical information such as your IP address, browser type, and pages visited.</p>

        <h3 class="font-semibold text-gray-800">2. How We Use Your Information</h3>
        <p>We use the information we collect to process and fulfil your print orders, send order confirmations and shipping updates, respond to your comments and questions, and improve our services. We do not sell or share your personal information with third parties except as necessary to process your orders (e.g., payment processors, shipping carriers).</p>

        <h3 class="font-semibold text-gray-800">3. Photo Storage</h3>
        <p>Photos you upload are stored securely and used solely for the purpose of fulfilling your order. Uploaded photos are automatically deleted from our servers within 30 days of order completion.</p>

        <h3 class="font-semibold text-gray-800">4. Cookies</h3>
        <p>We use session cookies to maintain your shopping session. These cookies are essential for the service to function and are deleted when you close your browser. We do not use tracking or advertising cookies.</p>

        <h3 class="font-semibold text-gray-800">5. Data Security</h3>
        <p>We implement appropriate technical and organisational measures to protect your personal information against unauthorised access, alteration, disclosure, or destruction. All data is transmitted over encrypted HTTPS connections.</p>

        <h3 class="font-semibold text-gray-800">6. Your Rights</h3>
        <p>You have the right to access, correct, or delete the personal information we hold about you. To exercise these rights, please contact us at <a href="mailto:print@tera-sat.com" class="text-indigo-600 hover:underline">print@tera-sat.com</a>.</p>

        <h3 class="font-semibold text-gray-800">7. Changes to This Policy</h3>
        <p>We may update this Privacy Policy from time to time. We will notify you of any significant changes by posting the new policy on this page with an updated date.</p>

        <h3 class="font-semibold text-gray-800">8. Contact Us</h3>
        <p>If you have any questions about this Privacy Policy, please contact us at <a href="mailto:print@tera-sat.com" class="text-indigo-600 hover:underline">print@tera-sat.com</a>.</p>
      </div>
      <div class="px-6 py-4 border-t border-gray-200 text-right">
        <button type="button" id="close-privacy-modal-btn" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Terms of Service Modal -->
<div id="tos-modal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true" aria-labelledby="tos-modal-title">
  <div class="absolute inset-0 bg-black/50" id="tos-modal-backdrop"></div>
  <div class="relative flex items-center justify-center min-h-screen p-4">
    <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-2xl max-h-[80vh] flex flex-col">
      <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
        <h2 id="tos-modal-title" class="text-lg font-semibold text-gray-900">Terms of Service</h2>
        <button type="button" id="close-tos-modal" class="text-gray-400 hover:text-gray-600 transition" aria-label="Close">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>
      <div class="overflow-y-auto px-6 py-4 text-sm text-gray-700 space-y-4">
        <p class="text-xs text-gray-400">Last updated: <?php echo date('F j, Y'); ?></p>

        <h3 class="font-semibold text-gray-800">1. Acceptance of Terms</h3>
        <p>By accessing or using PrintService, you agree to be bound by these Terms of Service. If you do not agree to these terms, please do not use our service.</p>

        <h3 class="font-semibold text-gray-800">2. Service Description</h3>
        <p>PrintService provides an online platform that allows users to upload photos and order high-quality printed products. We reserve the right to modify, suspend, or discontinue the service at any time with reasonable notice.</p>

        <h3 class="font-semibold text-gray-800">3. User Responsibilities</h3>
        <p>You are solely responsible for the photos and content you upload. By uploading content, you confirm that you own the rights to that content or have obtained all necessary permissions. You agree not to upload content that is illegal, obscene, defamatory, or infringes upon the rights of any third party.</p>

        <h3 class="font-semibold text-gray-800">4. Intellectual Property</h3>
        <p>You retain ownership of all photos you upload. By submitting photos for printing, you grant PrintService a limited, non-exclusive licence to reproduce your photos solely for the purpose of fulfilling your order.</p>

        <h3 class="font-semibold text-gray-800">5. Orders and Payment</h3>
        <p>All orders are subject to acceptance and availability. Prices are displayed in Euros and include applicable taxes. Payment must be completed before your order is processed. We reserve the right to refuse or cancel any order.</p>

        <h3 class="font-semibold text-gray-800">6. Refunds and Returns</h3>
        <p>If your print order arrives damaged or does not match your specifications, please contact us within 14 days of receipt. We will assess each case individually and, where appropriate, offer a reprint or refund at our discretion.</p>

        <h3 class="font-semibold text-gray-800">7. Limitation of Liability</h3>
        <p>To the fullest extent permitted by applicable law, PrintService shall not be liable for any indirect, incidental, special, or consequential damages arising out of your use of the service. Our total liability to you for any claim shall not exceed the amount you paid for the specific order giving rise to the claim.</p>

        <h3 class="font-semibold text-gray-800">8. Governing Law</h3>
        <p>These Terms of Service shall be governed by and construed in accordance with the laws of the European Union and the jurisdiction in which PrintService operates, without regard to conflict of law principles.</p>

        <h3 class="font-semibold text-gray-800">9. Changes to Terms</h3>
        <p>We reserve the right to update these Terms of Service at any time. Continued use of the service following notification of changes constitutes your acceptance of the updated terms.</p>

        <h3 class="font-semibold text-gray-800">10. Contact Us</h3>
        <p>If you have any questions about these Terms of Service, please contact us at <a href="mailto:print@tera-sat.com" class="text-indigo-600 hover:underline">print@tera-sat.com</a>.</p>
      </div>
      <div class="px-6 py-4 border-t border-gray-200 text-right">
        <button type="button" id="close-tos-modal-btn" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">Close</button>
      </div>
    </div>
  </div>
</div>

<script src="<?php echo htmlspecialchars(APP_URL . '/assets/js/app.js', ENT_QUOTES, 'UTF-8'); ?>"></script>
</body>
</html>
