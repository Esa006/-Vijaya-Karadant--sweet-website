<?php
require_once __DIR__ . '/../config/config.php';
$db = Database::getInstance();

echo "=== PRODUCTS MATCHING DINK OR CLASSIC ===\n";
$stmt = $db->query("
    SELECT id, name, slug, status, category_id 
    FROM products 
    WHERE (name LIKE '%Dink%' OR name LIKE '%Classic%' OR name LIKE '%Bucket%') 
      AND deleted_at IS NULL
");
$prods = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($prods as $p) {
    echo sprintf("ID: %4d | Name: %-40s | Slug: %-25s | CatID: %d | Status: %s\n",
        $p['id'], $p['name'], $p['slug'], $p['category_id'], $p['status']);
}
