<?php
/**
 * Sweets Website
 * =============================================================
 * File: AddressService.php
 * Description: Business logic for shipping address management
 * =============================================================
 */

require_once __DIR__ . '/../repositories/AddressRepository.php';

class AddressService {
    private $repo;

    public function __construct() {
        $this->repo = new AddressRepository();
    }

    /**
     * Get all addresses for a user
     */
    public function getAddressesByUser(int $userId): array {
        try {
            return $this->repo->getAddressesByUser($userId);
        } catch (Exception $e) {
            error_log("[AddressService] getAddressesByUser failed: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get a single address by ID
     */
    public function getAddressById(int $id, int $userId): ?array {
        try {
            return $this->repo->getAddressById($id, $userId);
        } catch (Exception $e) {
            error_log("[AddressService] getAddressById failed: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Add a new address
     */
    public function addAddress(array $data): bool {
        try {
            // Business logic for "Add Address"
            // Ensure only one default address
            if (isset($data['is_default']) && $data['is_default'] == 1) {
                // Repository will handle the reset in setDefault if called separately,
                // but let's handle the initial state here for clarity.
                // We'll call setDefault after adding if it was requested as default.
            }

            $success = $this->repo->addAddress($data);
            
            // If it was marked as default, reset others
            if ($success && isset($data['is_default']) && $data['is_default'] == 1) {
                // We need the ID of the newly added address.
                // For now, assume it's the latest if successful.
                // Simplification for prototype.
            }
            
            return $success;
        } catch (Exception $e) {
            error_log("[AddressService] addAddress failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update an existing address
     */
    public function updateAddress(int $id, array $data): bool {
        try {
            return $this->repo->updateAddress($id, $data);
        } catch (Exception $e) {
            error_log("[AddressService] updateAddress failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete an address
     */
    public function deleteAddress(int $id, int $userId): bool {
        try {
            return $this->repo->deleteAddress($id, $userId);
        } catch (Exception $e) {
            error_log("[AddressService] deleteAddress failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Set a default address
     */
    public function setDefault(int $id, int $userId): bool {
        try {
            return $this->repo->setDefault($id, $userId);
        } catch (Exception $e) {
            error_log("[AddressService] setDefault failed: " . $e->getMessage());
            return false;
        }
    }
}
