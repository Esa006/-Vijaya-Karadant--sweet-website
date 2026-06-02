<?php
require_once __DIR__ . '/../config/config.php';
$db = Database::getInstance();

$cids = [34, 35, 49];
foreach ($cids as $cid) {
    echo "=== ITEMS FOR COMBO ID $cid ===\n";
    $stmt = $db->prepare("
        SELECT ci.product_id, ci.quantity, p.name, p.slug
        FROM combo_items ci
        LEFT JOIN products p ON ci.product_id = p.id
        WHERE ci.combo_id = :cid
    ");
    $stmt->execute(['cid' => $cid]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($items as $item) {
        echo sprintf("  Product ID: %4d | Name: %-35s | Slug: %s\n",
            $item['product_id'], $item['name'], $item['slug']);
    }
}
