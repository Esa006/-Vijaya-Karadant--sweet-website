<?php
require_once 'config/config.php';
require_once 'config/Database.php';

try {
    $db = Database::getInstance();
    echo "--- COMBOS TABLE ---\n";
    $stmt = $db->query("DESCRIBE combos");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC), JSON_PRETTY_PRINT);
    
    echo "\n--- COMBO_ITEMS TABLE ---\n";
    $stmt = $db->query("DESCRIBE combo_items");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC), JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo $e->getMessage();
}
