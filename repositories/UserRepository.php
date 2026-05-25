<?php
/**
 * Sweets Website
 * =============================================================
 * File: UserRepository.php
 * Description: Data access layer for User accounts and Identity
 * Author: Antigravity - Principal Security Architect
 * Version: 2.1.0
 * =============================================================
 */

require_once 'BaseRepository.php';

class UserRepository extends BaseRepository {

    /**
     * Fetch user by email (Audit-safe)
     */
    public function getByEmail(string $email): ?array {
        return $this->fetchOne(
            "SELECT * FROM users WHERE email = :email LIMIT 1", 
            ['email' => $email]
        );
    }

    /**
     * Update user password hash
     */
    public function updatePassword(int $userId, string $hash): bool {
        $stmt = $this->db->prepare("UPDATE users SET password = :hash WHERE id = :id");
        return $stmt->execute(['hash' => $hash, 'id' => $userId]);
    }

    /**
     * Get user by ID (Standardized)
     */
    public function getById(int $id): ?array {
        return $this->fetchOne("SELECT * FROM users WHERE id = :id", ['id' => $id]);
    }

    /**
     * Update user profile data
     */
    public function updateProfile(int $id, array $data): bool {
        $fields = [];
        $params = [':id' => $id];

        $allowedFields = ['full_name', 'email', 'phone', 'language', 'timezone', 'avatar'];
        
        foreach ($data as $key => $value) {
            if (in_array($key, $allowedFields)) {
                $fields[] = "{$key} = :{$key}";
                $params[":{$key}"] = $value;
            }
        }

        if (empty($fields)) return false;

        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
}
