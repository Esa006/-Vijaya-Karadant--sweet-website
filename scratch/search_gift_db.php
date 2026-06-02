<?php
require_once __DIR__ . '/../config/config.php';
$db = Database::getInstance();

echo "=== PRODUCTS MATCHING GIFT OR BOX ===\n";
$stmt = $db->query("SELECT id, name, slug, category_id FROM products WHERE name LIKE '%gift%' OR name LIKE '%box%' OR slug LIKE '%gift%' OR slug LIKE '%box%'");
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "ID: {$r['id']} | Name: {$r['name']} | Slug: {$r['slug']} | Cat ID: {$r['category_id']}\n";
}

echo "\n=== COMBOS MATCHING GIFT OR BOX ===\n";
$stmt = $db->query("SELECT id, name, slug, category FROM combos WHERE name LIKE '%gift%' OR name LIKE '%box%' OR slug LIKE '%gift%' OR slug LIKE '%box%'");
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "ID: {$r['id']} | Name: {$r['name']} | Slug: {$r['slug']} | Category: {$r['category']}\n";
}
