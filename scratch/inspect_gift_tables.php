<?php
require_once __DIR__ . '/../config/config.php';
$db = Database::getInstance();

echo "=== schema and rows of gift_boxes ===\n";
try {
    $rows = $db->query("SELECT * FROM gift_boxes")->fetchAll(PDO::FETCH_ASSOC);
    print_r($rows);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "=== schema and rows of gift_box_defaults ===\n";
try {
    $rows = $db->query("SELECT * FROM gift_box_defaults")->fetchAll(PDO::FETCH_ASSOC);
    print_r($rows);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
