<?php
/**
 * Sweets Website
 * =============================================================
 * File: SettingRepository.php
 * Description: Data access layer for site settings
 * =============================================================
 */

require_once __DIR__ . '/BaseRepository.php';

class SettingRepository extends BaseRepository {

    /**
     * Get all settings as an associative array
     */
    public function getAll(): array {
        $sql = "SELECT setting_key, setting_value FROM site_settings";
        $results = $this->fetchAll($sql);
        
        $settings = [];
        foreach ($results as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return $settings;
    }

    /**
     * Get settings for a specific group
     */
    public function getByGroup(string $group): array {
        $sql = "SELECT setting_key, setting_value FROM site_settings WHERE group_name = :group";
        $results = $this->fetchAll($sql, ['group' => $group]);
        
        $settings = [];
        foreach ($results as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return $settings;
    }

    /**
     * Update or Insert a setting
     */
    public function update(string $key, $value, string $group = 'general'): bool {
        // We use REPLACE INTO or INSERT ... ON DUPLICATE KEY UPDATE
        // Since setting_key is PRIMARY KEY, this works well.
        $sql = "INSERT INTO site_settings (setting_key, setting_value, group_name) 
                VALUES (:key, :val, :group) 
                ON DUPLICATE KEY UPDATE 
                setting_value = VALUES(setting_value), 
                group_name = VALUES(group_name)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'key' => $key,
            'val' => (string)$value,
            'group' => $group
        ]);
    }
}
