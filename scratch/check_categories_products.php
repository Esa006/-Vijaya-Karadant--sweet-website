<?php
require_once __DIR__ . '/../config/config.php';
$db = Database::getInstance();

echo "=== CATEGORIES IN SYSTEM ===\n";
$cats = $db->query("SELECT c.id, c.name, c.slug, c.parent_id, (SELECT name FROM categories WHERE id = c.parent_id) as parent_name FROM categories c")->fetchAll(PDO::FETCH_ASSOC);
foreach ($cats as $c) {
    // Count products
    $stmt = $db->prepare("SELECT COUNT(*) FROM products WHERE category_id = :cid AND deleted_at IS NULL");
    $stmt->execute(['cid' => $c['id']]);
    $directCount = $stmt->fetchColumn();

    // Count products including subcategories or parent categories
    $stmt2 = $db->prepare("
        SELECT COUNT(p.id) 
        FROM products p 
        LEFT JOIN categories pc ON p.category_id = pc.id 
        WHERE (p.category_id = :cid OR pc.parent_id = :pid) 
          AND p.deleted_at IS NULL
    ");
    $stmt2->execute(['cid' => $c['id'], 'pid' => $c['id']]);
    $totalCount = $stmt2->fetchColumn();

    echo sprintf("ID: %2d | Name: %-20s | Slug: %-15s | Parent: %-12s | Direct: %d | Total (incl. child cats): %d\n", 
        $c['id'], $c['name'], $c['slug'], $c['parent_name'] ?: 'None', $directCount, $totalCount);
}

echo "\n=== PRODUCT CATEGORIES AUDIT ===\n";
$prods = $db->query("
    SELECT p.id, p.name, p.slug, p.status, c.name as cat_name, c.slug as cat_slug 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.deleted_at IS NULL 
    ORDER BY c.name, p.name
")->fetchAll(PDO::FETCH_ASSOC);

foreach ($prods as $p) {
    echo sprintf("ID: %4d | Name: %-40s | Status: %-10s | Category: %s (%s)\n", 
        $p['id'], $p['name'], $p['status'], $p['cat_name'] ?: 'None', $p['cat_slug'] ?: 'None');
}
