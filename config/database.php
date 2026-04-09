<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../src/Models/Database.php';

use src\Models\Database;

function getDB(): PDO
{
    return Database::getInstance();
}
