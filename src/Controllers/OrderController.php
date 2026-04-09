<?php
declare(strict_types=1);

namespace src\Controllers;

use src\Models\Order;

class OrderController
{
    public function form(): void
    {
        if (empty($_SESSION['upload_filename'])) {
            header('Location: ' . APP_URL . '/?page=upload');
            exit;
        }
        csrfGenerate();
        require_once __DIR__ . '/../Views/layout/header.php';
        require_once __DIR__ . '/../Views/order/form.php';
        require_once __DIR__ . '/../Views/layout/footer.php';
    }

    public function summary(): void
    {
        if (empty($_SESSION['upload_filename'])) {
            header('Location: ' . APP_URL . '/?page=upload');
            exit;
        }

        if (!csrfVerify($_POST['csrf_token'] ?? '')) {
            $_SESSION['order_error'] = 'Invalid security token.';
            header('Location: ' . APP_URL . '/?page=order');
            exit;
        }

        $name     = trim(filter_input(INPUT_POST, 'customer_name',    FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
        $email    = trim(filter_input(INPUT_POST, 'customer_email',   FILTER_SANITIZE_EMAIL) ?? '');
        $address  = trim(filter_input(INPUT_POST, 'customer_address', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
        $size     = trim($_POST['size'] ?? '');
        $quantity = (int) ($_POST['quantity'] ?? 1);

        $errors = [];
        if ($name === '')    $errors[] = 'Name is required.';
        if ($name !== '' && strlen($name) > 100) $errors[] = 'Name is too long (max 100 chars).';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email is required.';
        if ($address === '') $errors[] = 'Address is required.';
        if (!array_key_exists($size, PRINT_SIZES)) $errors[] = 'Invalid print size selected.';
        if ($quantity < 1 || $quantity > 100) $errors[] = 'Quantity must be between 1 and 100.';

        if ($errors) {
            $_SESSION['order_error'] = implode(' ', $errors);
            header('Location: ' . APP_URL . '/?page=order');
            exit;
        }

        $pricePerUnit = PRINT_SIZES[$size]['price'];
        $totalPrice   = round($pricePerUnit * $quantity, 2);

        $_SESSION['order_data'] = [
            'customer_name'    => $name,
            'customer_email'   => $email,
            'customer_address' => $address,
            'size'             => $size,
            'quantity'         => $quantity,
            'price_per_unit'   => $pricePerUnit,
            'total_price'      => $totalPrice,
        ];

        csrfGenerate();
        require_once __DIR__ . '/../Views/layout/header.php';
        require_once __DIR__ . '/../Views/order/summary.php';
        require_once __DIR__ . '/../Views/layout/footer.php';
    }

    public function place(): void
    {
        if (empty($_SESSION['upload_filename']) || empty($_SESSION['order_data'])) {
            header('Location: ' . APP_URL . '/?page=upload');
            exit;
        }

        if (!csrfVerify($_POST['csrf_token'] ?? '')) {
            $_SESSION['order_error'] = 'Invalid security token.';
            header('Location: ' . APP_URL . '/?page=order');
            exit;
        }

        $filename         = $_SESSION['upload_filename'];
        $originalFilename = $_SESSION['upload_original_filename'] ?? $filename;
        $orderData        = $_SESSION['order_data'];

        // Move file to permanent storage
        $src  = UPLOADS_PATH  . $filename;
        $dest = PERMANENT_PATH . $filename;

        if (!file_exists($src)) {
            $_SESSION['order_error'] = 'Uploaded image not found. Please upload again.';
            header('Location: ' . APP_URL . '/?page=upload');
            exit;
        }

        if (!rename($src, $dest)) {
            $_SESSION['order_error'] = 'Failed to process image. Please try again.';
            header('Location: ' . APP_URL . '/?page=order');
            exit;
        }

        $model = new Order();
        $orderNumber = $model->createOrder([
            'filename'          => $filename,
            'original_filename' => $originalFilename,
            'size'              => $orderData['size'],
            'quantity'          => $orderData['quantity'],
            'price'             => $orderData['total_price'],
            'customer_name'     => $orderData['customer_name'],
            'customer_email'    => $orderData['customer_email'],
            'customer_address'  => $orderData['customer_address'],
        ]);

        // Clear upload/order session data
        unset($_SESSION['upload_filename'], $_SESSION['upload_original_filename'], $_SESSION['order_data'], $_SESSION['order_error']);

        $_SESSION['success_order_number'] = $orderNumber;
        header('Location: ' . APP_URL . '/?page=order&action=success');
        exit;
    }

    public function success(): void
    {
        if (empty($_SESSION['success_order_number'])) {
            header('Location: ' . APP_URL . '/');
            exit;
        }
        $orderNumber = $_SESSION['success_order_number'];
        unset($_SESSION['success_order_number']);
        require_once __DIR__ . '/../Views/layout/header.php';
        require_once __DIR__ . '/../Views/order/success.php';
        require_once __DIR__ . '/../Views/layout/footer.php';
    }
}
