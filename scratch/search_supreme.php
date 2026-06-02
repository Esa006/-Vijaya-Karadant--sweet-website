<?php
require_once __DIR__ . '/../config/config.php';
$db = Database::getInstance();

echo "=== SEARCH PRODUCTS ===\n";
$stmt = $db->query("SELECT id, name, slug FROM products WHERE name LIKE '%Supreme%' OR name LIKE '%Special%'");
$prods = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($prods as $p) {
    echo "Product - ID: {$p['id']} | Name: {$p['name']} | Slug: {$p['slug']}\n";
}

echo "\n=== SEARCH COMBOS ===\n";
$stmt = $db->query("SELECT id, name, slug, image FROM combos WHERE name LIKE '%Supreme%' OR name LIKE '%Special%'");
$combos = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($combos as $c) {
    echo "Combo - ID: {$c['id']} | Name: {$c['name']} | Slug: {$c['slug']} | Image: {$c['image']}\n";
}
