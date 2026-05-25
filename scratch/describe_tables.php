<?php
require_once 'config/config.php';
require_once 'config/Database.php';

try {
    $db = Database::getInstance();
    echo "Table: products\n";
    print_r($db->query("DESCRIBE products")->fetchAll(PDO::FETCH_ASSOC));
    
    echo "\nTable: product_images\n";
    print_r($db->query("DESCRIBE product_images")->fetchAll(PDO::FETCH_ASSOC));
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
