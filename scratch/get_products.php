<?php
require_once __DIR__ . '/../config/config.php';
$db = Database::getInstance();

try {
    $stmt = $db->query("SELECT id, category_id, name, slug, base_price, sku, status, deleted_at FROM products ORDER BY category_id, id");
    $prods = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($prods as $p) {
        printf("ID: %4d | Cat: %2d | Name: %-35s | Slug: %-25s | Price: %7.2f | SKU: %-15s | Status: %-10s | Deleted: %s\n",
            $p['id'],
            $p['category_id'] ?? 0,
            $p['name'],
            $p['slug'],
            $p['base_price'],
            $p['sku'] ?? 'NULL',
            $p['status'] ?? 'NULL',
            $p['deleted_at'] ? 'YES (' . $p['deleted_at'] . ')' : 'NO'
        );
    }
} catch (Exception $e) {
    echo $e->getMessage() . "\n";
}
