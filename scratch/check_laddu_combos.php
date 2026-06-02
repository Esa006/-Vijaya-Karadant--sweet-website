<?php
require_once __DIR__ . '/../config/config.php';
$db = Database::getInstance();

echo "=== COMBOS IN 'LADDU' CATEGORY ===\n";
$stmt = $db->query("
    SELECT c.id, c.name, c.category,
           GROUP_CONCAT(p.name ORDER BY p.name SEPARATOR ' | ') as products
    FROM combos c
    LEFT JOIN combo_items ci ON c.id = ci.combo_id
    LEFT JOIN products p ON ci.product_id = p.id
    WHERE c.category = 'laddu'
    GROUP BY c.id
");
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "#{$r['id']} | Name: {$r['name']} | Products: {$r['products']}\n";
}
