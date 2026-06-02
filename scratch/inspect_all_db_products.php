<?php
require_once __DIR__ . '/../config/config.php';
$db = Database::getInstance();

echo "=== ALL PRODUCTS IN DB ===\n";
$stmt = $db->query("
    SELECT id, name, slug, category_id 
    FROM products 
    WHERE deleted_at IS NULL
    ORDER BY category_id, name
");
$prods = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($prods as $p) {
    echo sprintf("ID: %4d | Name: %-40s | Slug: %-30s | CatID: %d\n",
        $p['id'], $p['name'], $p['slug'], $p['category_id']);
}
