<?php
declare(strict_types=1);

$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            $key = trim($parts[0]);
            $value = trim($parts[1]);
            if (!array_key_exists($key, $_ENV)) {
                $_ENV[$key] = $value;
                if (function_exists('putenv')) {
                    putenv("{$key}={$value}");
                }
            }
        }
    }
}

define('APP_URL',    $_ENV['APP_URL']    ?? 'https://print.tera-sat.com');
define('DB_HOST',    $_ENV['DB_HOST']    ?? 'localhost');
define('DB_NAME',    $_ENV['DB_NAME']    ?? 'print_service');
define('DB_USER',    $_ENV['DB_USER']    ?? 'root');
define('DB_PASS',    $_ENV['DB_PASS']    ?? '');

// Default hash for 'admin123' - override via .env
define('ADMIN_PASSWORD_HASH', $_ENV['ADMIN_PASSWORD_HASH'] ?? '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
define('ADMIN_USERNAME',      $_ENV['ADMIN_USERNAME']      ?? 'admin');

define('UPLOAD_MAX_SIZE', 10 * 1024 * 1024); // 10 MB
define('ALLOWED_MIME_TYPES', ['image/jpeg', 'image/png', 'image/webp']);
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'webp']);

define('SHIPPING_COST', (float) ($_ENV['SHIPPING_COST'] ?? 15.00));

define('PRINT_SIZES', [
    '30x40'  => ['label' => '30x40 cm',  'price' => (float) ($_ENV['PRINT_PRICE_30x40']  ?? 14.75)],
    '50x70'  => ['label' => '50x70 cm',  'price' => (float) ($_ENV['PRINT_PRICE_50x70']  ?? 24.10)],
    '60x80'  => ['label' => '60x80 cm',  'price' => (float) ($_ENV['PRINT_PRICE_60x80']  ?? 40.25)],
]);

define('STORAGE_PATH',   realpath(__DIR__ . '/../storage') . DIRECTORY_SEPARATOR);
define('UPLOADS_PATH',   STORAGE_PATH . 'uploads'   . DIRECTORY_SEPARATOR);
define('PERMANENT_PATH', STORAGE_PATH . 'permanent' . DIRECTORY_SEPARATOR);

// PayPal configuration
define('PAYPAL_CLIENT_ID',     $_ENV['PAYPAL_CLIENT_ID']     ?? '');
define('PAYPAL_CLIENT_SECRET', $_ENV['PAYPAL_CLIENT_SECRET'] ?? '');
define('PAYPAL_ENV',           $_ENV['PAYPAL_ENV']           ?? 'sandbox'); // 'sandbox' or 'live'
