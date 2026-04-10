<?php
declare(strict_types=1);

namespace src\Controllers;

use src\Models\Admin;
use src\Models\Order;

class AdminController
{
    private function requireAuth(): void
    {
        if (empty($_SESSION['admin_logged_in'])) {
            header('Location: ' . APP_URL . '/?page=admin&action=login');
            exit;
        }
    }

    public function cleanOrphanedFiles(): void
    {
        $this->requireAuth();
        if (!csrfVerify($_POST['csrf_token'] ?? '')) {
            header('Location: ' . APP_URL . '/?page=admin');
            exit;
        }

        $orderModel      = new Order();
        $orderedFilenames = $orderModel->getOrderedFilenames();

        $deleted = 0;
        $errors  = 0;

        foreach ([UPLOADS_PATH, PERMANENT_PATH] as $dir) {
            if (!is_dir($dir)) {
                continue;
            }
            $files = scandir($dir);
            if ($files === false) {
                continue;
            }
            foreach ($files as $file) {
                if ($file === '.' || $file === '..') {
                    continue;
                }
                if (!preg_match('/^[a-f0-9]{32}\.(jpg|jpeg|png|webp)$/i', $file)) {
                    continue;
                }
                if (!in_array($file, $orderedFilenames, true)) {
                    $path = $dir . $file;
                    if (file_exists($path)) {
                        if (unlink($path)) {
                            $deleted++;
                        } else {
                            $errors++;
                        }
                    }
                }
            }
        }

        $_SESSION['admin_flash'] = [
            'type'    => $errors > 0 ? 'error' : 'success',
            'message' => $errors > 0
                ? "Deleted {$deleted} orphaned file(s); {$errors} could not be removed."
                : ($deleted > 0
                    ? "Successfully deleted {$deleted} orphaned file(s)."
                    : 'No orphaned files found.'),
        ];

        header('Location: ' . APP_URL . '/?page=admin');
        exit;
    }

    public function login(): void
    {
        if (!empty($_SESSION['admin_logged_in'])) {
            header('Location: ' . APP_URL . '/?page=admin');
            exit;
        }

        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!csrfVerify($_POST['csrf_token'] ?? '')) {
                $error = 'Invalid security token.';
            } else {
                // Rate limiting: max 5 failed attempts per 15 minutes, keyed by IP
                $rateLimitError = $this->checkLoginRateLimit();
                if ($rateLimitError !== null) {
                    $error = $rateLimitError;
                } else {
                    $username = trim($_POST['username'] ?? '');
                    $password = $_POST['password'] ?? '';
                    $adminModel = new Admin();

                    if ($username !== '' && $adminModel->verifyPassword($username, $password)) {
                        $this->resetLoginRateLimit();
                        session_regenerate_id(true);
                        $_SESSION['admin_logged_in'] = true;
                        $_SESSION['admin_username']  = $username;
                        header('Location: ' . APP_URL . '/?page=admin');
                        exit;
                    }
                    $this->recordFailedLogin();
                    $error = 'Invalid username or password.';
                }
            }
        }

        csrfGenerate();
        require_once __DIR__ . '/../Views/layout/header.php';
        require_once __DIR__ . '/../Views/admin/login.php';
        require_once __DIR__ . '/../Views/layout/footer.php';
    }

    public function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        session_destroy();
        header('Location: ' . APP_URL . '/?page=admin&action=login');
        exit;
    }

    public function dashboard(): void
    {
        $this->requireAuth();
        $orderModel = new Order();
        $orders     = $orderModel->getAllOrders();
        require_once __DIR__ . '/../Views/layout/header.php';
        require_once __DIR__ . '/../Views/admin/dashboard.php';
        require_once __DIR__ . '/../Views/layout/footer.php';
    }

    public function orderDetail(): void
    {
        $this->requireAuth();
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            header('Location: ' . APP_URL . '/?page=admin');
            exit;
        }
        $orderModel = new Order();
        $order      = $orderModel->getOrderById($id);
        if (!$order) {
            header('Location: ' . APP_URL . '/?page=admin');
            exit;
        }
        csrfGenerate();
        require_once __DIR__ . '/../Views/layout/header.php';
        require_once __DIR__ . '/../Views/admin/order_detail.php';
        require_once __DIR__ . '/../Views/layout/footer.php';
    }

    public function updateStatus(): void
    {
        $this->requireAuth();
        if (!csrfVerify($_POST['csrf_token'] ?? '')) {
            header('Location: ' . APP_URL . '/?page=admin');
            exit;
        }
        $id     = (int) ($_POST['order_id'] ?? 0);
        $status = trim($_POST['status'] ?? '');
        if ($id > 0 && in_array($status, ['pending', 'processing', 'completed'], true)) {
            $orderModel = new Order();
            $orderModel->updateOrderStatus($id, $status);
        }
        header('Location: ' . APP_URL . '/?page=admin&action=order&id=' . $id);
        exit;
    }

    public function downloadImage(): void
    {
        $this->requireAuth();
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            header('Location: ' . APP_URL . '/?page=admin');
            exit;
        }
        $orderModel = new Order();
        $order      = $orderModel->getOrderById($id);
        if (!$order) {
            header('Location: ' . APP_URL . '/?page=admin');
            exit;
        }

        $filename = $order['filename'];
        if (!preg_match('/^[a-f0-9]{32}\.(jpg|jpeg|png|webp)$/i', $filename)) {
            http_response_code(400);
            echo 'Invalid file.';
            exit;
        }

        if (file_exists(PERMANENT_PATH . $filename)) {
            $filePath = PERMANENT_PATH . $filename;
        } elseif (file_exists(UPLOADS_PATH . $filename)) {
            $filePath = UPLOADS_PATH . $filename;
        } else {
            http_response_code(404);
            echo 'File not found.';
            exit;
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($filePath);
        if (!in_array($mime, ALLOWED_MIME_TYPES, true)) {
            http_response_code(403);
            echo 'Forbidden.';
            exit;
        }

        $downloadName = $order['original_filename'] !== '' ? $order['original_filename'] : $filename;
        $downloadName = preg_replace('/[^\w.\-]/', '_', $downloadName);

        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($filePath));
        header('Content-Disposition: attachment; filename="' . $downloadName . '"');
        header('Cache-Control: private, no-cache');
        readfile($filePath);
        exit;
    }

    // -------------------------------------------------------------------------
    // Login rate limiting – IP-based, backed by a file in storage/security/
    //
    // NOTE: REMOTE_ADDR reflects the direct TCP peer. If the app runs behind
    // a trusted reverse proxy that sets X-Forwarded-For, replace REMOTE_ADDR
    // with the validated client IP from that header. Using X-Forwarded-For
    // without proxy trust verification would allow attackers to spoof IPs.
    // -------------------------------------------------------------------------

    private function rateLimitFile(): string
    {
        $ip  = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $dir = rtrim((string) STORAGE_PATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'security';
        if (!is_dir($dir)) {
            mkdir($dir, 0750, true);
        }
        return $dir . DIRECTORY_SEPARATOR . 'login_' . md5($ip) . '.json';
    }

    private function readRateLimitData(): array
    {
        $file = $this->rateLimitFile();
        if (!file_exists($file)) {
            return ['count' => 0, 'last' => 0];
        }
        $data = json_decode((string) file_get_contents($file), true);
        return is_array($data) ? $data : ['count' => 0, 'last' => 0];
    }

    private function checkLoginRateLimit(): ?string
    {
        $data = $this->readRateLimitData();
        // Reset counter if the 15-minute window has expired
        if ((int) $data['last'] > 0 && (time() - (int) $data['last']) > 900) {
            return null;
        }
        if ((int) $data['count'] >= 5) {
            return 'Too many failed login attempts. Please try again in 15 minutes.';
        }
        return null;
    }

    private function recordFailedLogin(): void
    {
        $data = $this->readRateLimitData();
        // Reset counter if the window has expired
        if ((int) $data['last'] > 0 && (time() - (int) $data['last']) > 900) {
            $data = ['count' => 0, 'last' => 0];
        }
        $data['count'] = (int) $data['count'] + 1;
        $data['last']  = time();
        file_put_contents($this->rateLimitFile(), json_encode($data), LOCK_EX);
    }

    private function resetLoginRateLimit(): void
    {
        $file = $this->rateLimitFile();
        if (file_exists($file)) {
            unlink($file);
        }
    }
}
