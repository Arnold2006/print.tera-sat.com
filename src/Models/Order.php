<?php
declare(strict_types=1);

namespace src\Models;

use PDO;

class Order
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function createOrder(array $data): string
    {
        $orderNumber = $this->generateOrderNumber();
        $sql = 'INSERT INTO orders
                    (order_number, group_order_number, filename, original_filename, size, quantity, price,
                     customer_name, customer_email, customer_address, status)
                VALUES
                    (:order_number, :group_order_number, :filename, :original_filename, :size, :quantity, :price,
                     :customer_name, :customer_email, :customer_address, :status)';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':order_number'       => $orderNumber,
            ':group_order_number' => $data['group_order_number'] ?? null,
            ':filename'           => $data['filename'],
            ':original_filename'  => $data['original_filename'],
            ':size'               => $data['size'],
            ':quantity'           => (int) $data['quantity'],
            ':price'              => (float) $data['price'],
            ':customer_name'      => $data['customer_name'],
            ':customer_email'     => $data['customer_email'],
            ':customer_address'   => $data['customer_address'],
            ':status'             => 'pending',
        ]);
        return $orderNumber;
    }

    public function generateGroupOrderNumber(): string
    {
        return $this->generateOrderNumber();
    }

    public function getOrderByNumber(string $number): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM orders WHERE order_number = :number LIMIT 1');
        $stmt->execute([':number' => $number]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getAllOrders(): array
    {
        $stmt = $this->db->query('SELECT * FROM orders ORDER BY created_at DESC');
        return $stmt->fetchAll();
    }

    public function getOrderById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM orders WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function updateOrderStatus(int $id, string $status): bool
    {
        $allowed = ['pending', 'processing', 'completed'];
        if (!in_array($status, $allowed, true)) {
            return false;
        }
        $stmt = $this->db->prepare('UPDATE orders SET status = :status WHERE id = :id');
        return $stmt->execute([':status' => $status, ':id' => $id]);
    }

    private function generateOrderNumber(): string
    {
        do {
            $number = 'ORD-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
            $stmt = $this->db->prepare('SELECT id FROM orders WHERE order_number = :n LIMIT 1');
            $stmt->execute([':n' => $number]);
        } while ($stmt->fetch());
        return $number;
    }
}
