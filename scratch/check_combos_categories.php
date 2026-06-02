<?php
require_once __DIR__ . '/../config/config.php';
$db = Database::getInstance();

echo "=== COMBOS BY CATEGORY IN DB ===\n";
$stmt = $db->query("SELECT id, name, category, is_active FROM combos ORDER BY category, name");
$combos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$counts = [];
foreach ($combos as $c) {
    $cat = $c['category'] ?: '(empty)';
    if (!isset($counts[$cat])) $counts[$cat] = 0;
    $counts[$cat]++;
    
    // Fetch items
    $stmtItems = $db->prepare("
        SELECT ci.product_id, ci.quantity, p.name as product_name, c.slug as category_slug
        FROM combo_items ci
        JOIN products p ON ci.product_id = p.id AND p.deleted_at IS NULL
        JOIN categories c ON p.category_id = c.id
        WHERE ci.combo_id = :cid
    ");
    $stmtItems->execute(['cid' => $c['id']]);
    $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);
    
    $itemDesc = [];
    foreach ($items as $item) {
        $itemDesc[] = $item['product_name'] . " (qty: " . $item['quantity'] . ", cat: " . $item['category_slug'] . ")";
    }
    
    echo sprintf("ID: %3d | Name: %-35s | Cat: %-10s | Active: %d | Items: %s\n", 
        $c['id'], $c['name'], $c['category'], $c['is_active'], implode(', ', $itemDesc));
}

echo "\nSummary of counts:\n";
foreach ($counts as $cat => $count) {
    echo "  Category '{$cat}': {$count} combos\n";
}
