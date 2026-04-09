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
                putenv("{$key}={$value}");
            }
        }
    }
}

define('APP_URL',    $_ENV['APP_URL']    ?? 'http://localhost');
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

define('PRINT_SIZES', [
    '10x15' => ['label' => '10x15 cm', 'price' => 2.99],
    '13x18' => ['label' => '13x18 cm', 'price' => 4.99],
    '20x30' => ['label' => '20x30 cm', 'price' => 8.99],
]);

define('STORAGE_PATH',   realpath(__DIR__ . '/../storage') . DIRECTORY_SEPARATOR);
define('UPLOADS_PATH',   STORAGE_PATH . 'uploads'   . DIRECTORY_SEPARATOR);
define('PERMANENT_PATH', STORAGE_PATH . 'permanent' . DIRECTORY_SEPARATOR);
