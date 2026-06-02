<?php
require_once __DIR__ . '/../config/config.php';
$db = Database::getInstance();

echo "=== CATEGORIES ===\n";
$cats = $db->query("SELECT id, name, slug FROM categories")->fetchAll();
foreach ($cats as $cat) {
    echo "ID: {$cat['id']} | Name: {$cat['name']} | Slug: {$cat['slug']}\n";
}

echo "\n=== PRODUCTS IN LADDU CATEGORY ===\n";
$products = $db->query("
    SELECT p.id, p.name, p.slug, c.name as category_name, c.slug as category_slug
    FROM products p
    JOIN categories c ON p.category_id = c.id
    WHERE c.slug = 'laddu' OR p.slug LIKE '%laddu%'
")->fetchAll();
foreach ($products as $p) {
    echo "ID: {$p['id']} | Name: {$p['name']} | Slug: {$p['slug']} | Cat Name: {$p['category_name']} | Cat Slug: {$p['category_slug']}\n";
}

echo "\n=== ALL PRODUCTS WITH CATEGORIES ===\n";
$all_products = $db->query("
    SELECT p.id, p.name, p.slug, c.name as category_name, c.slug as category_slug
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
")->fetchAll();
foreach ($all_products as $p) {
    echo "ID: {$p['id']} | Name: {$p['name']} | Slug: {$p['slug']} | Cat Name: {$p['category_name']}\n";
}
