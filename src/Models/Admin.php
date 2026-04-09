<?php
declare(strict_types=1);

namespace src\Models;

use PDO;

class Admin
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findByUsername(string $username): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM admins WHERE username = :username LIMIT 1');
        $stmt->execute([':username' => $username]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function verifyPassword(string $username, string $password): bool
    {
        // First try DB-stored admin
        $admin = $this->findByUsername($username);
        if ($admin) {
            return password_verify($password, $admin['password_hash']);
        }

        // Fallback: env-configured admin
        if ($username === ADMIN_USERNAME) {
            return password_verify($password, ADMIN_PASSWORD_HASH);
        }

        return false;
    }
}
