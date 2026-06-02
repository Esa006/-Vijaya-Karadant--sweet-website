<?php
require_once __DIR__ . '/../config/config.php';
require_once SERVICES_PATH . '/ProductService.php';

$productService = new ProductService();
$db = Database::getInstance();
$stmt = $db->query("SELECT slug, name, id FROM products WHERE status = 'published'");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "=== RELATED PRODUCTS FOR ALL ACTIVE PRODUCTS ===\n";
foreach ($products as $p) {
    echo "Product: {$p['name']} (ID: {$p['id']}, Slug: {$p['slug']})\n";
    $pData = $productService->getProductBySlug($p['slug']);
    if (!$pData) {
        echo "  (Could not fetch via service)\n";
        continue;
    }
    $related = $productService->getRelatedProducts($pData, 4);
    if (empty($related)) {
        echo "  (none)\n";
        continue;
    }
    foreach ($related as $index => $item) {
        echo "  - Related $index | ID: {$item['id']} | Name: {$item['name']} | Slug: {$item['slug']} | Image Path: '{$item['image_path']}'\n";
    }
    echo "\n";
}
