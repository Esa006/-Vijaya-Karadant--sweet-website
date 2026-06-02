<?php
require_once __DIR__ . '/../config/config.php';
require_once SERVICES_PATH . '/ProductService.php';
require_once SERVICES_PATH . '/ComboService.php';

$productService = new ProductService();
$comboService = new ComboService();

$bestSellers = $productService->getFeaturedProducts();
$namkeens = $productService->getProductsByCategory('namkeen');
$karadants = $productService->getProductsByCategory('karadant');
$laddus = $productService->getProductsByCategory('laddu');
$combos = $comboService->getAllCombos();

$normalizedCombos = array_map(function($c) {
    return [
        'id' => 'combo_' . $c['id'],
        'name' => $c['name'],
        'slug' => $c['slug'],
        'sale_price' => $c['final_price'] ?? $c['price'] ?? 0,
        'base_price' => $c['original_price'] ?? $c['price'] ?? 0,
        'image_path' => $c['image'] ?? 'assets/images/placeholders/product-placeholder.png',
        'short_description' => $c['description'] ?? '',
        'effective_category_slug' => 'combo',
        'stock_quantity' => (isset($c['stock_status']) && $c['stock_status'] === 'out_of_stock' ? 0 : 10),
        'is_combo' => true
    ];
}, $combos);

$allProductsRaw = array_merge($karadants, $laddus, $namkeens, $normalizedCombos, $bestSellers);

$bestsellersToDisplay = [];
foreach ($allProductsRaw as $p) {
    if (!isset($bestsellersToDisplay[$p['id']])) {
        $bestsellersToDisplay[$p['id']] = $p;
    }
}
$bestsellersToDisplay = array_values($bestsellersToDisplay);

echo "=== BESTSELLERS PRODUCT SLUGS AND CATEGORIES ===\n";
foreach ($bestsellersToDisplay as $product) {
    $catSlug = strtolower((string)(
        $product['effective_category_slug']
        ?? $product['parent_category_slug']
        ?? $product['category_slug']
        ?? ''
    ));
    echo sprintf("ID: %-8s | Name: %-35s | CatSlug: %s\n", 
        $product['id'], $product['name'], $catSlug);
}
