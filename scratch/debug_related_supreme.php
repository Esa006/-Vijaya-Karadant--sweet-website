<?php
require_once __DIR__ . '/../config/config.php';
require_once SERVICES_PATH . '/ProductService.php';

$productService = new ProductService();
$db = Database::getInstance();
$slugs = $db->query("SELECT slug FROM products WHERE status = 'published'")->fetchAll(PDO::FETCH_COLUMN);

foreach ($slugs as $slug) {
    $pData = $productService->getProductBySlug($slug);
    if (!$pData) continue;
    $related = $productService->getRelatedProducts($pData, 4);
    
    foreach ($related as $item) {
        if (strpos($item['name'], 'Supreme') !== false) {
            echo "Source: {$pData['name']} (Slug: {$pData['slug']}) has related: {$item['name']} | Image Path: '{$item['image_path']}'\n";
        }
    }
}
