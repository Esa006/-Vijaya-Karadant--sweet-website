<?php
/**
 * Sweets Website
 * =============================================================
 * File: AddressRepository.php
 * Description: Data access layer for shipping addresses
 * =============================================================
 */

require_once __DIR__ . '/../config/Database.php';

class AddressRepository {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    /**
     * Fetch all addresses for a specific user
     */
    public function getAddressesByUser(int $userId): array {
        $stmt = $this->pdo->prepare("SELECT * FROM addresses WHERE user_id = :user_id ORDER BY is_default DESC, id DESC");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Fetch a single address by ID
     */
    public function getAddressById(int $id, int $userId): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM addresses WHERE id = :id AND user_id = :user_id");
        $stmt->execute([':id' => $id, ':user_id' => $userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Add a new address
     */
    public function addAddress(array $data): bool {
        $sql = "INSERT INTO addresses 
                (user_id, recipient_name, type, address_line1, address_line2, city, state, zip_code, country, phone, is_default)
                VALUES (:user_id, :recipient_name, :type, :address_line1, :address_line2, :city, :state, :zip_code, :country, :phone, :is_default)";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':user_id' => $data['user_id'],
            ':recipient_name' => $data['recipient_name'],
            ':type' => $data['type'] ?? 'shipping',
            ':address_line1' => $data['address_line1'],
            ':address_line2' => $data['address_line2'] ?? '',
            ':city' => $data['city'],
            ':state' => $data['state'],
            ':zip_code' => $data['zip_code'],
            ':country' => $data['country'] ?? 'India',
            ':phone' => $data['phone'],
            ':is_default' => $data['is_default'] ?? 0
        ]);
    }

    /**
     * Update an address
     */
    public function updateAddress(int $id, array $data): bool {
        $sql = "UPDATE addresses SET 
                recipient_name = :recipient_name,
                type = :type,
                address_line1 = :address_line1,
                address_line2 = :address_line2,
                city = :city,
                state = :state,
                zip_code = :zip_code,
                country = :country,
                phone = :phone,
                is_default = :is_default
                WHERE id = :id AND user_id = :user_id";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':id' => $id,
            ':user_id' => $data['user_id'],
            ':recipient_name' => $data['recipient_name'],
            ':type' => $data['type'] ?? 'shipping',
            ':address_line1' => $data['address_line1'],
            ':address_line2' => $data['address_line2'] ?? '',
            ':city' => $data['city'],
            ':state' => $data['state'],
            ':zip_code' => $data['zip_code'],
            ':country' => $data['country'] ?? 'India',
            ':phone' => $data['phone'],
            ':is_default' => $data['is_default'] ?? 0
        ]);
    }

    /**
     * Delete an address
     */
    public function deleteAddress(int $id, int $userId): bool {
        $stmt = $this->pdo->prepare("DELETE FROM addresses WHERE id = :id AND user_id = :user_id");
        return $stmt->execute([':id' => $id, ':user_id' => $userId]);
    }

    /**
     * Set default address and reset others
     */
    public function setDefault(int $id, int $userId): bool {
        try {
            $this->pdo->beginTransaction();
            
            // Reset all for user
            $stmt1 = $this->pdo->prepare("UPDATE addresses SET is_default = 0 WHERE user_id = :user_id");
            $stmt1->execute([':user_id' => $userId]);
            
            // Set new default
            $stmt2 = $this->pdo->prepare("UPDATE addresses SET is_default = 1 WHERE id = :id AND user_id = :user_id");
            $stmt2->execute([':id' => $id, ':user_id' => $userId]);
            
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }
}
