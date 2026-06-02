<?php
require_once __DIR__ . '/../config/config.php';
$db = Database::getInstance();

$stmt = $db->query("SELECT id, name, slug, base_price, sale_price, status FROM products ORDER BY id ASC");
$prods = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "=== ALL PRODUCTS ===\n";
foreach ($prods as $p) {
    echo "ID: {$p['id']} | Name: {$p['name']} | Slug: {$p['slug']} | Base Price: {$p['base_price']} | Sale Price: {$p['sale_price']} | Status: {$p['status']}\n";
}
