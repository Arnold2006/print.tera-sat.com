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
            header('Location: ' . APP_URL . '/public/?page=admin&action=login');
            exit;
        }
    }

    public function login(): void
    {
        if (!empty($_SESSION['admin_logged_in'])) {
            header('Location: ' . APP_URL . '/public/?page=admin');
            exit;
        }

        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!csrfVerify($_POST['csrf_token'] ?? '')) {
                $error = 'Invalid security token.';
            } else {
                $username = trim($_POST['username'] ?? '');
                $password = $_POST['password'] ?? '';
                $adminModel = new Admin();

                if ($username !== '' && $adminModel->verifyPassword($username, $password)) {
                    session_regenerate_id(true);
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_username']  = $username;
                    header('Location: ' . APP_URL . '/public/?page=admin');
                    exit;
                }
                $error = 'Invalid username or password.';
            }
        }

        csrfGenerate();
        require_once __DIR__ . '/../Views/layout/header.php';
        require_once __DIR__ . '/../Views/admin/login.php';
        require_once __DIR__ . '/../Views/layout/footer.php';
    }

    public function logout(): void
    {
        session_destroy();
        header('Location: ' . APP_URL . '/public/?page=admin&action=login');
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
            header('Location: ' . APP_URL . '/public/?page=admin');
            exit;
        }
        $orderModel = new Order();
        $order      = $orderModel->getOrderById($id);
        if (!$order) {
            header('Location: ' . APP_URL . '/public/?page=admin');
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
            header('Location: ' . APP_URL . '/public/?page=admin');
            exit;
        }
        $id     = (int) ($_POST['order_id'] ?? 0);
        $status = trim($_POST['status'] ?? '');
        if ($id > 0 && in_array($status, ['pending', 'processing', 'completed'], true)) {
            $orderModel = new Order();
            $orderModel->updateOrderStatus($id, $status);
        }
        header('Location: ' . APP_URL . '/public/?page=admin&action=order&id=' . $id);
        exit;
    }
}
