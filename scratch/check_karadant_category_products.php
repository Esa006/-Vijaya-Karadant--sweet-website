<?php
require_once __DIR__ . '/../config/config.php';
require_once SERVICES_PATH . '/ProductService.php';

$productService = new ProductService();
$products = $productService->getProductsByCategory('karadant');

echo "=== PRODUCTS IN 'karadant' CATEGORY ===\n";
foreach ($products as $p) {
    echo "ID: {$p['id']} | Name: {$p['name']} | Slug: {$p['slug']} | Category Slug: {$p['category_slug']} | Image: {$p['image_path']}\n";
}
