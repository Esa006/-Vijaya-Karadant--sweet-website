<?php
require_once __DIR__ . '/../config/config.php';
require_once ROOT_PATH . '/config/Database.php';
$db = Database::getInstance();

echo "=== TABLES ===\n";
$stmt = $db->query("SHOW TABLES");
while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
    echo "  {$row[0]}\n";
}

if ($db->query("SHOW TABLES LIKE 'inventory'")->fetch()) {
    echo "\n=== INVENTORY TABLE DATA ===\n";
    $stmt = $db->query("SELECT * FROM inventory LIMIT 20");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }
} else {
    echo "\nInventory table does NOT exist.\n";
}

echo "\n=== PRODUCTS STOCK_QUANTITY COLUMN ===\n";
$stmt = $db->query("SELECT id, name, category_id, stock_quantity FROM products LIMIT 20");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    print_r($row);
}
