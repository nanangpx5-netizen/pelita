<?php
/**
 * Admin Model Class
 * @package PELITA
 * @version 1.0.0
 */

class Admin {
    private Database $db;
    private string $table = 'admin';

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Find admin by username
     */
    public function findByUsername(string $username): ?array {
        return $this->db->fetch(
            "SELECT * FROM {$this->table} WHERE username = :username AND is_active = 1",
            ['username' => $username]
        );
    }

    /**
     * Find admin by ID
     */
    public function findById(int $id): ?array {
        return $this->db->fetch(
            "SELECT * FROM {$this->table} WHERE id = :id",
            ['id' => $id]
        );
    }

    /**
     * Update last login
     */
    public function updateLastLogin(int $id): void {
        $this->db->update(
            $this->table, 
            ['last_login' => date('Y-m-d H:i:s')], 
            "id = :id", 
            ['id' => $id]
        );
    }

    /**
     * Change password
     */
    public function changePassword(int $id, string $newPassword): bool {
        return $this->db->update(
            $this->table,
            ['password' => password_hash($newPassword, PASSWORD_DEFAULT)],
            "id = :id",
            ['id' => $id]
        ) > 0;
    }

    /**
     * Verify password
     */
    public function verifyPassword(string $password, string $hash): bool {
        return password_verify($password, $hash);
    }
}
