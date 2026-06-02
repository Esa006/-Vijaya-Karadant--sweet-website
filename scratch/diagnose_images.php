<?php
require_once __DIR__ . '/../config/config.php';
$db = Database::getInstance();

echo "=== PRODUCTS IN DATABASE ===\n";
$stmt = $db->query("SELECT id, name, slug, status FROM products WHERE name LIKE '%Karadant%' OR name LIKE '%Lagdi%'");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($products as $p) {
    echo "Product ID: {$p['id']} | Name: {$p['name']} | Slug: {$p['slug']} | Status: {$p['status']}\n";
    $stmtImg = $db->prepare("SELECT * FROM product_images WHERE product_id = :pid");
    $stmtImg->execute(['pid' => $p['id']]);
    $images = $stmtImg->fetchAll(PDO::FETCH_ASSOC);
    echo "  Images:\n";
    if (empty($images)) {
        echo "    (none)\n";
    } else {
        foreach ($images as $img) {
            echo "    - ID: {$img['id']} | Path: {$img['image_path']} | Is Main: {$img['is_main']}\n";
        }
    }
}
