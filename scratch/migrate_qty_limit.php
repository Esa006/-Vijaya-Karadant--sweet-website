<?php
require_once __DIR__ . '/../config/config.php';
require_once SERVICES_PATH . '/SettingService.php';

try {
    $db = Database::getInstance();
    
    // Check if store_max_qty_limit already exists
    $stmt = $db->prepare("SELECT COUNT(*) FROM site_settings WHERE setting_key = 'store_max_qty_limit'");
    $stmt->execute();
    $exists = (int)$stmt->fetchColumn();
    
    if (!$exists) {
        // Insert with default value of 10
        $stmt = $db->prepare("INSERT INTO site_settings (setting_key, setting_value, group_name) VALUES ('store_max_qty_limit', '10', 'store')");
        $stmt->execute();
        echo "Inserted store_max_qty_limit = 10 successfully.\n";
    } else {
        echo "store_max_qty_limit already exists in database.\n";
    }
} catch (Throwable $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
