<?php
require_once __DIR__ . '/../config/config.php';
require_once SERVICES_PATH . '/ProductService.php';

$productService = new ProductService();
$p = $productService->getProductBySlug('regal-anjeer-karadant');
$related = $productService->getRelatedProducts($p, 4);

echo "=== RELATED PRODUCTS FOR regal-anjeer-karadant ===\n";
foreach ($related as $index => $item) {
    echo "Index $index | ID: {$item['id']} | Name: {$item['name']} | Slug: {$item['slug']} | Image Path: '{$item['image_path']}'\n";
}
