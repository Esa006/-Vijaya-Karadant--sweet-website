<?php
require_once 'config/config.php';
require_once 'config/Database.php';

try {
    $db = Database::getInstance();
    
    echo "--- orders table ---\n";
    $stmt = $db->query("DESCRIBE orders");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['Field'] . " (" . $row['Type'] . ")\n";
    }
    
    echo "\n--- order_items table ---\n";
    $stmt = $db->query("DESCRIBE order_items");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['Field'] . " (" . $row['Type'] . ")\n";
    }

    echo "\n--- product_variants table ---\n";
    try {
        $stmt = $db->query("DESCRIBE product_variants");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo $row['Field'] . " (" . $row['Type'] . ")\n";
        }
    } catch (Exception $e) {
        echo "product_variants table does not exist.\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
