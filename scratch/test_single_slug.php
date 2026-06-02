<?php
require_once __DIR__ . '/../config/config.php';
require_once SERVICES_PATH . '/ProductService.php';

$productService = new ProductService();
$pData = $productService->getProductBySlug('premium-vijaya-karadant');
if ($pData) {
    echo "Successfully fetched premium-vijaya-karadant:\n";
    echo "Name: {$pData['name']}\n";
    echo "Category Slug: {$pData['category_slug']}\n";
} else {
    echo "Failed to fetch premium-vijaya-karadant\n";
}
