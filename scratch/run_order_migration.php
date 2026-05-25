<?php
require_once 'config/config.php';
require_once 'config/Database.php';

try {
    $db = Database::getInstance();
    $sql = file_get_contents('database/update_orders_table.sql');
    
    // Split SQL by semicolon to execute one by one if needed, 
    // but ALTER TABLE can often be run as one block if supported.
    // However, some PDO drivers prefer single statements.
    
    $db->exec($sql);
    echo "Orders table updated successfully.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
