<?php
require_once __DIR__ . '/../config/config.php';
require_once SERVICES_PATH . '/ProductService.php';

$productService = new ProductService();
$gifts = $productService->getGiftBoxes();

echo "=== GIFT BOXES ON FRONTEND ===\n";
echo "Total count: " . count($gifts) . "\n";
foreach ($gifts as $g) {
    echo "ID: {$g['id']} | Name: {$g['name']} | Slug: {$g['slug']} | Category Slug: {$g['category_slug']} | Image: {$g['image_path']}\n";
}
