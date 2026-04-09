<?php
declare(strict_types=1);

/**
 * upgrade.php — Apply any pending database migrations.
 *
 * Called automatically by deploy.sh after every git pull.
 * It reads every *.sql file inside database/migrations/ in alphabetical
 * order, executes each one, and then deletes the file so it is not
 * applied again on the next deployment.
 *
 * On a brand-new installation it also imports database/schema.sql when
 * neither of the core tables (orders, admins) exist yet.
 *
 * Run from the project root:
 *   php upgrade.php
 */

// ---------------------------------------------------------------------------
// Bootstrap
// ---------------------------------------------------------------------------
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

use src\Models\Database;

$pdo = Database::getInstance();

// ---------------------------------------------------------------------------
// Helper
// ---------------------------------------------------------------------------
function info(string $msg): void
{
    echo "  --> {$msg}" . PHP_EOL;
}

// ---------------------------------------------------------------------------
// 1. Fresh-install check — import schema.sql if tables are missing
// ---------------------------------------------------------------------------
$tablesExist = false;
try {
    $pdo->query('SELECT 1 FROM orders LIMIT 1');
    $pdo->query('SELECT 1 FROM admins LIMIT 1');
    $tablesExist = true;
} catch (PDOException) {
    // At least one table is missing — treat as fresh install.
}

if (!$tablesExist) {
    $schemaFile = __DIR__ . '/database/schema.sql';
    if (!file_exists($schemaFile)) {
        echo 'ERROR: database/schema.sql not found.' . PHP_EOL;
        exit(1);
    }

    info('Fresh installation detected — importing database/schema.sql …');

    // PDO does not support multi-statement exec directly, so split on ";"
    // and execute each statement individually.
    $sql = file_get_contents($schemaFile);
    foreach (array_filter(array_map('trim', explode(';', $sql))) as $statement) {
        $pdo->exec($statement);
    }

    info('Schema imported successfully.');
}

// ---------------------------------------------------------------------------
// 2. Incremental migrations — run every *.sql file in database/migrations/
// ---------------------------------------------------------------------------
$migrationsDir = __DIR__ . '/database/migrations';

if (!is_dir($migrationsDir)) {
    // Nothing to do yet.
    echo '==> No migrations directory found — nothing to migrate.' . PHP_EOL;
    exit(0);
}

$files = glob($migrationsDir . '/*.sql');
sort($files); // alphabetical / chronological order

if (empty($files)) {
    echo '==> No pending migrations.' . PHP_EOL;
    exit(0);
}

foreach ($files as $file) {
    $name = basename($file);
    info("Applying migration: {$name} …");

    $sql = file_get_contents($file);
    foreach (array_filter(array_map('trim', explode(';', $sql))) as $statement) {
        $pdo->exec($statement);
    }

    // Remove the file so it is not applied again on the next run.
    unlink($file);
    info("Migration applied and removed: {$name}");
}

echo PHP_EOL . '==> Database upgrade complete.' . PHP_EOL;
