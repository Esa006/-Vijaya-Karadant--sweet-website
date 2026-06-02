<?php
require_once __DIR__ . '/../config/config.php';
$db = Database::getInstance();

echo "=== COMBO ITEMS FOR ALL COMBOS ===\n";
$stmt = $db->query("
    SELECT c.id, c.name, c.category, ci.product_id, p.name as product_name, p.slug as product_slug, ci.quantity
    FROM combos c
    LEFT JOIN combo_items ci ON c.id = ci.combo_id
    LEFT JOIN products p ON ci.product_id = p.id
    ORDER BY c.id, p.id
");
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "Combo #{$r['id']} ({$r['name']}) [Cat: {$r['category']}] -> Product #{$r['product_id']} ({$r['product_name']}) Qty: {$r['quantity']}\n";
}
