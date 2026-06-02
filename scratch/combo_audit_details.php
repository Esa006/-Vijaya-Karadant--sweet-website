<?php
require_once __DIR__ . '/../config/config.php';
$db = Database::getInstance();

$laddu_combos = $db->query("
    SELECT c.id, c.name, c.category, c.description
    FROM combos c
    WHERE c.category = 'laddu'
")->fetchAll();

foreach ($laddu_combos as $combo) {
    echo "==================================================\n";
    echo "Combo #{$combo['id']}: {$combo['name']} (Category: {$combo['category']})\n";
    echo "Description: {$combo['description']}\n";
    echo "Items:\n";
    
    $items = $db->query("
        SELECT ci.product_id, p.name as product_name, p.slug as product_slug, pc.name as cat_name, pc.slug as cat_slug, ci.quantity
        FROM combo_items ci
        LEFT JOIN products p ON ci.product_id = p.id
        LEFT JOIN categories pc ON p.category_id = pc.id
        WHERE ci.combo_id = {$combo['id']}
    ")->fetchAll();
    
    foreach ($items as $item) {
        echo "  - Product ID: {$item['product_id']} | Name: {$item['product_name']} | Slug: {$item['product_slug']} | Category: {$item['cat_name']} ({$item['cat_slug']}) | Qty: {$item['quantity']}\n";
    }
}
