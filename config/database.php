<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

use src\Models\Database;

function getDB(): PDO
{
    return Database::getInstance();
}
