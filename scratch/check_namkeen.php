<?php
require_once 'config/config.php';
require_once 'config/Database.php';

try {
    $db = Database::getInstance();
    $stmt = $db->query("SELECT * FROM categories WHERE slug = 'namkeen' OR name LIKE '%namkeen%'");
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Results:\n";
    print_r($results);
    
    $stmt2 = $db->query("SELECT COUNT(*) FROM products WHERE category_id IN (SELECT id FROM categories WHERE slug = 'namkeen')");
    echo "\nProducts count for namkeen: " . $stmt2->fetchColumn() . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
