<?php
declare(strict_types=1);

/**
 * cleanup.php — Delete images and anonymise personal data for completed orders
 *               that are more than 30 days old.
 *
 * Per the Privacy Policy, uploaded photos are automatically deleted from the
 * server within 30 days of order completion, and the associated personal data
 * (name, email, address) is also removed at the same time.
 *
 * Run this script daily from a cron job (as the web-server user so that it
 * has permission to delete files inside storage/permanent/):
 *
 *   # Debian/Ubuntu (www-data), RHEL/AlmaLinux (apache), FreeBSD (www)
 *   0 3 * * * www-data php /var/www/print.tera-sat.com/cleanup.php >> /var/log/printservice-cleanup.log 2>&1
 *
 * It can also be run manually from the project root:
 *   php cleanup.php
 */

// ---------------------------------------------------------------------------
// Bootstrap
// ---------------------------------------------------------------------------
define('BASE_PATH', __DIR__);

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/src/Models/Database.php';
require_once __DIR__ . '/src/Models/Order.php';

use src\Models\Order;

// ---------------------------------------------------------------------------
// Helper
// ---------------------------------------------------------------------------
function info(string $msg): void
{
    echo '[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL;
}

// ---------------------------------------------------------------------------
// Purge loop
// ---------------------------------------------------------------------------
$model  = new Order();
$orders = $model->getOrdersDueForPurge();

if (empty($orders)) {
    info('No orders due for purge.');
    exit(0);
}

$purged = 0;
$failed = 0;

foreach ($orders as $order) {
    $id       = (int) $order['id'];
    $filename = (string) ($order['filename'] ?? '');

    if ($model->purgeOrder($id, $filename)) {
        info("Purged order #{$id} ({$order['order_number']}).");
        $purged++;
    } else {
        info("ERROR: Failed to purge order #{$id} ({$order['order_number']}).");
        $failed++;
    }
}

info("Done. Purged: {$purged}, Failed: {$failed}.");
exit($failed > 0 ? 1 : 0);
