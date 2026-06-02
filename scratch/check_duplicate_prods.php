<?php
require_once __DIR__ . '/../config/config.php';
$db = Database::getInstance();

echo "=== INSPECTING DUPLICATE PRODUCTS ===\n";
$stmt = $db->query("
    SELECT p.id, p.name, p.slug, p.base_price, p.sale_price, p.status, pi.image_path
    FROM products p
    LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_main = 1
    WHERE p.name IN ('Garlic Ribbon', 'Masala Peanuts', 'demo') OR p.id IN (2014, 2017, 2021, 2003, 2029)
");
$prods = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($prods as $p) {
    echo sprintf("ID: %4d | Name: %-20s | Slug: %-30s | Price: %6.2f | Status: %-10s | Image: %s\n",
        $p['id'], $p['name'], $p['slug'], $p['base_price'], $p['status'], $p['image_path'] ?: 'None');
}
