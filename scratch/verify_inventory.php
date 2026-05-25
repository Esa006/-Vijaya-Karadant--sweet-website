<?php
require_once __DIR__ . '/../config/config.php';
require_once ROOT_PATH . '/config/Database.php';
$db = Database::getInstance();

$stmt = $db->query("SELECT COUNT(*) FROM inventory WHERE product_id = 1018");
echo "Inventory count for ID 1018: " . $stmt->fetchColumn() . "\n";

$stmt = $db->query("SELECT id, stock_quantity FROM products WHERE id = 1018");
$prod = $stmt->fetch(PDO::FETCH_ASSOC);
echo "Product ID 1018 stock_quantity: " . ($prod['stock_quantity'] ?? 'NULL') . "\n";

$stmt = $db->query("SELECT COUNT(*) FROM inventory");
echo "Total inventory records: " . $stmt->fetchColumn() . "\n";

$stmt = $db->query("SELECT COUNT(*) FROM products");
echo "Total products records: " . $stmt->fetchColumn() . "\n";
