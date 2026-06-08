<?php
/**
 * Sweets Website
 * =============================================================
 * File: SettingService.php
 * Description: Business logic for site settings
 * =============================================================
 */

require_once __DIR__ . '/../repositories/SettingRepository.php';

class SettingService {
    private SettingRepository $repository;

    public function __construct(?SettingRepository $repository = null) {
        $this->repository = $repository ?? new SettingRepository();
    }

    /**
     * Get all settings
     */
    public function getAllSettings(): array {
        return $this->repository->getAll();
    }

    /**
     * Get specific settings for a page
     */
    public function getSettingsByGroup(string $group): array {
        return $this->repository->getByGroup($group);
    }

    /**
     * Save bulk settings
     */
    public function saveSettings(array $data): bool {
        if (empty($data)) {
            throw new InvalidArgumentException("Settings data cannot be empty");
        }
        foreach ($data as $key => $value) {
            $trimmedKey = trim((string)$key);
            if ($trimmedKey === '') {
                throw new InvalidArgumentException("Setting key cannot be empty");
            }
            // Determine group based on key prefix or mapping
            $group = $this->determineGroup($trimmedKey);
            $this->repository->update($trimmedKey, $value, $group);
        }
        return true;
    }

    /**
     * Helper to group settings
     */
    private function determineGroup(string $key): string {
        if (strpos($key, 'notify_') === 0) return 'notifications';
        if (strpos($key, 'pay_') === 0) return 'payments';
        if (strpos($key, 'store_') === 0) return 'store';
        if (strpos($key, 'shipping_') === 0) return 'shipping';
        if (strpos($key, 'ui_') === 0) return 'appearance';
        return 'general';
    }
}
