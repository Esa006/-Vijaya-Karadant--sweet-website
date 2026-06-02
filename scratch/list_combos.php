<?php
require_once __DIR__ . '/../config/config.php';

$db = Database::getInstance();
$stmt = $db->query("SELECT id, name, category, price, image, is_active FROM combos ORDER BY id");
echo "=== ALL COMBOS ===\n";
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $img = $r['image'] ? $r['image'] : '(none)';
    echo "ID:{$r['id']} | [{$r['is_active']}] {$r['name']} | cat:{$r['category']} | img: {$img}\n";
}
