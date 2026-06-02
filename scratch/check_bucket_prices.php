<?php
require_once __DIR__ . '/../config/config.php';
$db = Database::getInstance();

$ids = [1009, 2034, 2035];
echo "=== AUDITING PRICES OF REFERENCE PRODUCTS ===\n";
foreach ($ids as $id) {
    $stmt = $db->prepare("SELECT id, name, slug, base_price, sale_price, short_description, description FROM products WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $p = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($p) {
        echo sprintf("ID: %d | Name: %-30s | Base Price: %.2f | Sale Price: %.2f | Desc: %s\n",
            $p['id'], $p['name'], $p['base_price'], $p['sale_price'], $p['short_description']);
    }
}
