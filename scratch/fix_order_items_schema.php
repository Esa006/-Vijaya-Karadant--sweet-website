<?php
require_once 'config/config.php';
require_once 'config/Database.php';

try {
    $db = Database::getInstance();
    
    echo "Updating order_items table...\n";
    
    // Add variant_id if it doesn't exist
    try {
        $db->exec("ALTER TABLE order_items ADD COLUMN variant_id INT(11) DEFAULT 0 AFTER product_id");
        echo "Added variant_id column.\n";
    } catch (Exception $e) {
        echo "variant_id column already exists or error: " . $e->getMessage() . "\n";
    }
    
    // Add price if it doesn't exist
    try {
        $db->exec("ALTER TABLE order_items ADD COLUMN price DECIMAL(10,2) DEFAULT 0.00 AFTER quantity");
        echo "Added price column.\n";
    } catch (Exception $e) {
        echo "price column already exists or error: " . $e->getMessage() . "\n";
    }
    
    // Copy price_at_time to price if price was just added and is 0
    $db->exec("UPDATE order_items SET price = price_at_time WHERE price = 0 AND price_at_time > 0");
    echo "Synchronized price_at_time to price.\n";

    echo "Update complete.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
