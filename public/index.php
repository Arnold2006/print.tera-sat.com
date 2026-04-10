<?php
declare(strict_types=1);

// Bootstrap
define('BASE_PATH', dirname(__DIR__));

require_once BASE_PATH . '/config/config.php';

// Require models
require_once BASE_PATH . '/src/Models/Database.php';
require_once BASE_PATH . '/src/Models/Order.php';
require_once BASE_PATH . '/src/Models/Admin.php';

// Require controllers
require_once BASE_PATH . '/src/Controllers/HomeController.php';
require_once BASE_PATH . '/src/Controllers/UploadController.php';
require_once BASE_PATH . '/src/Controllers/OrderController.php';
require_once BASE_PATH . '/src/Controllers/AdminController.php';
require_once BASE_PATH . '/src/Controllers/PayPalController.php';
require_once BASE_PATH . '/src/Controllers/AboutController.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// CSRF helpers (available globally)
function csrfGenerate(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfVerify(string $token): bool
{
    return !empty($_SESSION['csrf_token'])
        && !empty($token)
        && hash_equals($_SESSION['csrf_token'], $token);
}

// Secure image serving route
$page   = $_GET['page']   ?? 'home';
$action = $_GET['action'] ?? null;

if ($page === 'image') {
    $rawFile = $_GET['file'] ?? '';
    $dir     = $_GET['dir']  ?? 'uploads';

    // Sanitize: only allow safe filenames
    if (!preg_match('/^[a-f0-9]{32}\.(jpg|jpeg|png|webp)$/i', $rawFile)) {
        http_response_code(400);
        echo 'Invalid file.';
        exit;
    }

    $dir = $dir === 'permanent' ? 'permanent' : 'uploads';
    $filePath = BASE_PATH . '/storage/' . $dir . '/' . $rawFile;

    if (!file_exists($filePath)) {
        http_response_code(404);
        echo 'File not found.';
        exit;
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($filePath);
    if (!in_array($mime, ALLOWED_MIME_TYPES, true)) {
        http_response_code(403);
        echo 'Forbidden.';
        exit;
    }

    header('Content-Type: ' . $mime);
    header('Content-Length: ' . filesize($filePath));
    header('Cache-Control: private, max-age=3600');
    readfile($filePath);
    exit;
}

// Route
switch ($page) {
    case '':
    case 'home':
        (new src\Controllers\HomeController())->index();
        break;

    case 'about':
        (new src\Controllers\AboutController())->index();
        break;

    case 'upload':
        $ctrl = new src\Controllers\UploadController();
        if ($action === 'process' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $ctrl->process();
        } else {
            $ctrl->index();
        }
        break;

    case 'order':
        $ctrl = new src\Controllers\OrderController();
        if ($action === 'summary' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $ctrl->summary();
        } elseif ($action === 'place' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $ctrl->place();
        } elseif ($action === 'success') {
            $ctrl->success();
        } else {
            $ctrl->form();
        }
        break;

    case 'admin':
        $ctrl = new src\Controllers\AdminController();
        if ($action === 'login') {
            $ctrl->login();
        } elseif ($action === 'logout') {
            $ctrl->logout();
        } elseif ($action === 'order') {
            $ctrl->orderDetail();
        } elseif ($action === 'update_status' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $ctrl->updateStatus();
        } elseif ($action === 'download_image') {
            $ctrl->downloadImage();
        } elseif ($action === 'clean_orphans' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $ctrl->cleanOrphanedFiles();
        } else {
            $ctrl->dashboard();
        }
        break;

    case 'paypal':
        $ctrl = new src\Controllers\PayPalController();
        if ($action === 'create-order' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $ctrl->createOrder();
        } elseif ($action === 'capture-order' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $ctrl->captureOrder();
        } else {
            http_response_code(405);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Method not allowed.']);
        }
        break;

    default:
        http_response_code(404);
        require_once BASE_PATH . '/src/Views/layout/header.php';
        echo '<div class="max-w-xl mx-auto px-4 py-24 text-center"><h1 class="text-4xl font-bold text-gray-900 mb-4">404</h1><p class="text-gray-500 mb-8">Page not found.</p><a href="' . htmlspecialchars(APP_URL . '/', ENT_QUOTES, 'UTF-8') . '" class="text-indigo-600 hover:underline">Go Home</a></div>';
        require_once BASE_PATH . '/src/Views/layout/footer.php';
        break;
}
