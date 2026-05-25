<?php
require_once 'config/config.php';
require_once 'config/Database.php';

try {
    $db = Database::getInstance();
    
    $sql = "ALTER TABLE orders 
            ADD COLUMN IF NOT EXISTS tracking_id VARCHAR(100) NULL AFTER payment_method, 
            ADD COLUMN IF NOT EXISTS delivery_partner VARCHAR(100) NULL AFTER tracking_id, 
            ADD COLUMN IF NOT EXISTS estimated_delivery_date DATE NULL AFTER delivery_partner, 
            ADD COLUMN IF NOT EXISTS admin_notes TEXT NULL AFTER estimated_delivery_date";
            
    $db->exec($sql);
    echo "Migration Successful: Orders table updated with fulfillment fields.\n";
} catch (Exception $e) {
    echo "Migration Failed: " . $e->getMessage() . "\n";
}
