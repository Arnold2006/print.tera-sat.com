<?php
declare(strict_types=1);

namespace src\Controllers;

class AboutController
{
    public function index(): void
    {
        require_once __DIR__ . '/../Views/layout/header.php';
        require_once __DIR__ . '/../Views/about/index.php';
        require_once __DIR__ . '/../Views/layout/footer.php';
    }
}
