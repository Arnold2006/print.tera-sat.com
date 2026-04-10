<?php
declare(strict_types=1);

namespace src\Controllers;

use src\Models\Order;

class OrderController
{
    public function form(): void
    {
        if (empty($_SESSION['upload_files'])) {
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
        if (empty($_SESSION['upload_files'])) {
            header('Location: ' . APP_URL . '/?page=upload');
            exit;
        }

        if (!csrfVerify($_POST['csrf_token'] ?? '')) {
            $_SESSION['order_error'] = 'Invalid security token.';
            header('Location: ' . APP_URL . '/?page=order');
            exit;
        }

        $name    = trim(filter_input(INPUT_POST, 'customer_name',    FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
        $email   = trim(filter_input(INPUT_POST, 'customer_email',   FILTER_SANITIZE_EMAIL) ?? '');
        $address = trim(filter_input(INPUT_POST, 'customer_address', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');

        $sizesPost     = $_POST['size']     ?? [];
        $quantitiesPost = $_POST['quantity'] ?? [];

        $errors = [];
        if ($name === '')    $errors[] = 'Name is required.';
        if ($name !== '' && strlen($name) > 100) $errors[] = 'Name is too long (max 100 chars).';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email is required.';
        if ($address === '') $errors[] = 'Address is required.';

        $uploadFiles = $_SESSION['upload_files'];
        $items       = [];

        foreach ($uploadFiles as $i => $fileInfo) {
            $size     = trim($sizesPost[$i] ?? '');
            $quantity = (int) ($quantitiesPost[$i] ?? 1);

            if (!array_key_exists($size, PRINT_SIZES)) {
                $errors[] = 'Invalid print size for image ' . ($i + 1) . '.';
                continue;
            }
            if ($quantity < 1 || $quantity > 100) {
                $errors[] = 'Quantity for image ' . ($i + 1) . ' must be between 1 and 100.';
                continue;
            }

            $pricePerUnit = PRINT_SIZES[$size]['price'];
            $items[] = [
                'filename'          => $fileInfo['filename'],
                'original_filename' => $fileInfo['original_filename'],
                'size'              => $size,
                'quantity'          => $quantity,
                'price_per_unit'    => $pricePerUnit,
                'total_price'       => round($pricePerUnit * $quantity, 2),
            ];
        }

        if ($errors) {
            $_SESSION['order_error'] = implode(' ', $errors);
            header('Location: ' . APP_URL . '/?page=order');
            exit;
        }

        $grandTotal = round((float) array_sum(array_column($items, 'total_price')), 2);

        $_SESSION['order_data'] = [
            'customer_name'    => $name,
            'customer_email'   => $email,
            'customer_address' => $address,
            'items'            => $items,
            'grand_total'      => $grandTotal,
        ];

        csrfGenerate();
        require_once __DIR__ . '/../Views/layout/header.php';
        require_once __DIR__ . '/../Views/order/summary.php';
        require_once __DIR__ . '/../Views/layout/footer.php';
    }

    public function place(): void
    {
        if (empty($_SESSION['upload_files']) || empty($_SESSION['order_data'])) {
            header('Location: ' . APP_URL . '/?page=upload');
            exit;
        }

        if (!csrfVerify($_POST['csrf_token'] ?? '')) {
            $_SESSION['order_error'] = 'Invalid security token.';
            header('Location: ' . APP_URL . '/?page=order');
            exit;
        }

        $orderData = $_SESSION['order_data'];
        $items     = $orderData['items'] ?? [];

        if (empty($items)) {
            header('Location: ' . APP_URL . '/?page=upload');
            exit;
        }

        // Verify all source files exist and have a safe name before moving any
        foreach ($items as $item) {
            if (!preg_match('/^[a-f0-9]{32}\.(jpg|jpeg|png|webp)$/i', $item['filename'])) {
                $_SESSION['order_error'] = 'Invalid image filename detected. Please upload again.';
                header('Location: ' . APP_URL . '/?page=upload');
                exit;
            }
            if (!file_exists(UPLOADS_PATH . $item['filename'])) {
                $_SESSION['order_error'] = 'One or more uploaded images were not found. Please upload again.';
                header('Location: ' . APP_URL . '/?page=upload');
                exit;
            }
        }

        $model            = new Order();
        $groupOrderNumber = $model->generateGroupOrderNumber();

        foreach ($items as $item) {
            $src  = UPLOADS_PATH  . $item['filename'];
            $dest = PERMANENT_PATH . $item['filename'];

            if (!rename($src, $dest)) {
                $_SESSION['order_error'] = 'Failed to process image "' . htmlspecialchars($item['original_filename'], ENT_QUOTES, 'UTF-8') . '". Please try again.';
                header('Location: ' . APP_URL . '/?page=order');
                exit;
            }

            $model->createOrder([
                'group_order_number' => $groupOrderNumber,
                'filename'           => $item['filename'],
                'original_filename'  => $item['original_filename'],
                'size'               => $item['size'],
                'quantity'           => $item['quantity'],
                'price'              => $item['total_price'],
                'customer_name'      => $orderData['customer_name'],
                'customer_email'     => $orderData['customer_email'],
                'customer_address'   => $orderData['customer_address'],
            ]);
        }

        unset($_SESSION['upload_files'], $_SESSION['order_data'], $_SESSION['order_error']);

        $_SESSION['success_order_number'] = $groupOrderNumber;
        header('Location: ' . APP_URL . '/?page=order&action=success');
        exit;
    }

    public function success(): void
    {
        if (empty($_SESSION['success_order_number'])) {
            header('Location: ' . APP_URL . '/');
            exit;
        }
        $orderNumber         = $_SESSION['success_order_number'];
        $paypalTransactionId = $_SESSION['success_paypal_transaction_id'] ?? '';
        unset($_SESSION['success_order_number'], $_SESSION['success_paypal_transaction_id']);
        require_once __DIR__ . '/../Views/layout/header.php';
        require_once __DIR__ . '/../Views/order/success.php';
        require_once __DIR__ . '/../Views/layout/footer.php';
    }
}
