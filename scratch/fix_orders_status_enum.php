<?php
require_once 'config/config.php';
require_once 'config/Database.php';

try {
    $db = Database::getInstance();
    
    echo "Updating orders status enum...\n";
    
    $db->exec("ALTER TABLE orders MODIFY COLUMN status ENUM('pending', 'paid', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending'");
    
    echo "Updated orders status enum to include 'paid'.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
