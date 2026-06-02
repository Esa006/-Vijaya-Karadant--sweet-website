<?php
require_once __DIR__ . '/../config/config.php';
$db = Database::getInstance();

echo "=== COMBOS ===\n";
$stmt = $db->query("SELECT id, name, slug, category, price, image, is_active FROM combos ORDER BY id");
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "ID:{$r['id']} | {$r['name']} | cat:{$r['category']} | price:{$r['price']} | active:{$r['is_active']} | img:{$r['image']}\n";
}

echo "\n=== COMBO ITEMS ===\n";
$stmt = $db->query("SELECT ci.combo_id, c.name as combo, ci.product_id, p.name as product, ci.quantity 
                    FROM combo_items ci 
                    JOIN combos c ON c.id = ci.combo_id 
                    LEFT JOIN products p ON p.id = ci.product_id 
                    ORDER BY ci.combo_id");
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "Combo[{$r['combo_id']}:{$r['combo']}] => Product[{$r['product_id']}:{$r['product']}] x{$r['quantity']}\n";
}

echo "\n=== PRODUCTS (active/published) ===\n";
$stmt = $db->query("SELECT id, name, slug, base_price, sale_price, status FROM products WHERE deleted_at IS NULL AND status='published' ORDER BY id LIMIT 40");
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "ID:{$r['id']} | {$r['name']} | base:{$r['base_price']} | sale:{$r['sale_price']}\n";
}
