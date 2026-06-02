<?php
require_once __DIR__ . '/../config/config.php';
require_once SERVICES_PATH . '/ProductService.php';

$productService = new ProductService();
$ref = new ReflectionClass('ProductService');
$method = $ref->getMethod('getFallbackProductBySlug');
$method->setAccessible(true);

// We want to extract the keys from the hardcoded list in getFallbackProductSlugs
$methodSlugs = $ref->getMethod('getFallbackProductSlugs');
$methodSlugs->setAccessible(true);
$slugs = $methodSlugs->invoke($productService);

echo "=== VERIFYING FALLBACK IMAGE PATHS ===\n";
$missingCount = 0;
foreach ($slugs as $slug) {
    $p = $method->invoke($productService, $slug);
    if ($p && isset($p['image_path'])) {
        $path = $p['image_path'];
        $fullPath = __DIR__ . '/../' . ltrim($path, '/');
        if (!file_exists($fullPath)) {
            echo "Missing: Slug: '$slug' | Name: '{$p['name']}' | Path: '$path'\n";
            $missingCount++;
        }
    }
}

if ($missingCount === 0) {
    echo "All fallback image files exist on disk!\n";
} else {
    echo "Total missing fallback images: $missingCount\n";
}
