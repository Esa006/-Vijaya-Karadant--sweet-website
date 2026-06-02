<?php
require_once __DIR__ . '/../config/config.php';
require_once SERVICES_PATH . '/ProductService.php';

$productService = new ProductService();
$db = Database::getInstance();
$stmt = $db->query("SELECT slug FROM products WHERE status = 'published'");
$slugs = $stmt->fetchAll(PDO::FETCH_COLUMN);

foreach ($slugs as $slug) {
    $p = $productService->getProductBySlug($slug);
    if (!$p) continue;
    $related = $productService->getRelatedProducts($p, 4);
    
    // Check if the related list contains both Supreme Vijaya Karadant and Supreme Vijaya Karadant Special
    $hasSupreme = false;
    $hasSpecial = false;
    foreach ($related as $r) {
        if ($r['name'] === 'Supreme Vijaya Karadant') $hasSupreme = true;
        if ($r['name'] === 'Supreme Vijaya Karadant Special') $hasSpecial = true;
    }
    
    if ($hasSupreme && $hasSpecial) {
        echo "Source Product: {$p['name']} (Slug: {$p['slug']})\n";
        foreach ($related as $index => $item) {
            echo "  Index $index | Name: {$item['name']} | Slug: {$item['slug']} | Image Path: '{$item['image_path']}'\n";
        }
        echo "\n";
    }
}
