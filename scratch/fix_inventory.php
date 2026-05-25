<?php
require_once __DIR__ . '/../config/config.php';
require_once ROOT_PATH . '/config/Database.php';

try {
    $db = Database::getInstance();
    
    $sql = "INSERT INTO inventory (product_id, stock)
            SELECT id, stock_quantity FROM products
            WHERE id NOT IN (SELECT product_id FROM inventory)";
            
    $count = $db->exec($sql);
    
    echo "Successfully inserted $count missing inventory records.\n";
} catch (Exception $e) {
    echo "Error fixing inventory data: " . $e->getMessage() . "\n";
    exit(1);
}
