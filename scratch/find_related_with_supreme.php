<?php
require_once __DIR__ . '/../config/config.php';
require_once SERVICES_PATH . '/ProductService.php';

$productService = new ProductService();
$db = Database::getInstance();
$stmt = $db->query("SELECT slug, name FROM products WHERE status = 'published'");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($products as $p) {
    $pData = $productService->getProductBySlug($p['slug']);
    if (!$pData) continue;
    
    // Simulate cart.php related products logic
    $relatedProducts = [];
    if (!empty($pData['category_slug'])) {
        $categoryProducts = $productService->getProductsByCategory($pData['category_slug']);
        // Filter out the current product and limit to 4
        $relatedProducts = array_filter($categoryProducts, function($rp) use ($pData) {
            return (string)$rp['slug'] !== (string)$pData['slug'];
        });
        $relatedProducts = array_slice($relatedProducts, 0, 4);
    }
    
    $names = array_column($relatedProducts, 'name');
    if (in_array('Supreme Vijaya Karadant', $names) || in_array('Supreme Vijaya Karadant Special', $names)) {
        echo "Product (cart.php): {$p['name']} (Slug: {$p['slug']})\n";
        foreach ($relatedProducts as $index => $item) {
            echo "  - Related $index | ID: {$item['id']} | Name: {$item['name']} | Slug: {$item['slug']} | Image Path: '{$item['image_path']}'\n";
        }
        echo "\n";
    }
}
