<?php
require_once __DIR__ . '/../config/config.php';
$db = Database::getInstance();

$stmt = $db->query("
    SELECT c.id, c.name, c.category,
           GROUP_CONCAT(p.name ORDER BY p.name SEPARATOR ' | ') as products
    FROM combos c
    LEFT JOIN combo_items ci ON ci.combo_id = c.id
    LEFT JOIN products p ON p.id = ci.product_id
    WHERE c.is_active = 1
    GROUP BY c.id
    ORDER BY c.id
");

echo "ID  | Category   | Name + Products\n";
echo str_repeat('-', 90) . "\n";
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    printf("#%-3d | %-10s | %s\n       |            |   ► %s\n",
        $r['id'], $r['category'], $r['name'], $r['products'] ?? '(no items)');
}
