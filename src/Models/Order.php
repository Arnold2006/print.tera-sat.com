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
                    (order_number, group_order_number, paypal_transaction_id, filename, original_filename, size, quantity, price,
                     customer_name, customer_email, customer_address, status)
                VALUES
                    (:order_number, :group_order_number, :paypal_transaction_id, :filename, :original_filename, :size, :quantity, :price,
                     :customer_name, :customer_email, :customer_address, :status)';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':order_number'          => $orderNumber,
            ':group_order_number'    => $data['group_order_number'] ?? null,
            ':paypal_transaction_id' => $data['paypal_transaction_id'] ?? null,
            ':filename'              => $data['filename'],
            ':original_filename'     => $data['original_filename'],
            ':size'                  => $data['size'],
            ':quantity'              => (int) $data['quantity'],
            ':price'                 => (float) $data['price'],
            ':customer_name'         => $data['customer_name'],
            ':customer_email'        => $data['customer_email'],
            ':customer_address'      => $data['customer_address'],
            ':status'                => 'pending',
        ]);
        return $orderNumber;
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

    public function getOrdersPaginated(int $page, int $perPage): array
    {
        $offset = ($page - 1) * $perPage;
        $stmt   = $this->db->prepare(
            'SELECT * FROM orders ORDER BY created_at DESC LIMIT :limit OFFSET :offset'
        );
        $stmt->bindValue(':limit',  $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset,  \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function countOrders(): int
    {
        $stmt = $this->db->query('SELECT COUNT(*) FROM orders');
        return (int) $stmt->fetchColumn();
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
        if ($status === 'completed') {
            $stmt = $this->db->prepare(
                'UPDATE orders SET status = :status, completed_at = NOW() WHERE id = :id'
            );
        } else {
            // Reset completed_at so the 30-day purge clock restarts if the order
            // is re-completed later. purged_at is intentionally left untouched:
            // once an order has been purged the personal data and image are
            // already deleted and cannot be restored regardless of status.
            $stmt = $this->db->prepare(
                'UPDATE orders SET status = :status, completed_at = NULL WHERE id = :id'
            );
        }
        return $stmt->execute([':status' => $status, ':id' => $id]);
    }

    /**
     * Returns orders that are completed, have not yet been purged, and whose
     * completion (or creation, for legacy rows) was more than 30 days ago.
     */
    public function getOrdersDueForPurge(): array
    {
        $sql = 'SELECT * FROM orders
                WHERE status    = \'completed\'
                  AND purged_at IS NULL
                  AND COALESCE(completed_at, created_at) <= NOW() - INTERVAL 30 DAY';
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Deletes the order image file and replaces all personal-data fields with
     * the placeholder \'[deleted]\', then stamps purged_at.
     */
    public function purgeOrder(int $id, string $filename): bool
    {
        // Delete the image file if it still exists
        if ($filename !== '' && $filename !== '[deleted]') {
            $filePath = PERMANENT_PATH . $filename;
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        $stmt = $this->db->prepare(
            'UPDATE orders
             SET filename          = \'[deleted]\',
                 original_filename = \'[deleted]\',
                 customer_name     = \'[deleted]\',
                 customer_email    = \'[deleted]\',
                 customer_address  = \'[deleted]\',
                 purged_at         = NOW()
             WHERE id = :id'
        );
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Returns all filenames that are currently referenced by an order
     * (excludes the '[deleted]' placeholder used for purged orders).
     */
    public function getOrderedFilenames(): array
    {
        $stmt = $this->db->query(
            "SELECT DISTINCT filename FROM orders WHERE filename != '' AND filename != '[deleted]'"
        );
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
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

    public function generateGroupOrderNumber(): string
    {
        do {
            $number = 'GRP-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
            $stmt = $this->db->prepare('SELECT id FROM orders WHERE group_order_number = :n LIMIT 1');
            $stmt->execute([':n' => $number]);
        } while ($stmt->fetch());
        return $number;
    }
}
