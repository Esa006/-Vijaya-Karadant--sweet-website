<?php
require_once __DIR__ . '/../config/config.php';
require_once SERVICES_PATH . '/ProductService.php';

$productService = new ProductService();
$db = Database::getInstance();

// Gather all product slugs (both database and fallbacks)
$slugs = $db->query("SELECT slug FROM products WHERE status = 'published'")->fetchAll(PDO::FETCH_COLUMN);
$fallbackSlugs = [
    'premium-vijaya-karadant',
    'classic-vijaya-karadant',
    'supreme-vijaya-karadant',
    'supreme-vijaya-karadant-offer',
    'premium-karadant-pack',
    'premium-karadant-special',
    'regal-anjeer-karadant',
    'gandahagiri-laddu-premium',
    'dink-laddu',
    'ragi-laddu',
    'besan-laddu',
    'premium-ladagi-laddu',
    'otts-laddu',
    'til-laddu',
    'peanut-laddu',
    'gandahagiri-laddu',
    'gandhagiri-laddu',
    'spicy-mix-namkeen',
    'golden-sev',
    'masala-peanuts',
    'premium-mixture',
    'all-in-one-mix',
    'bengaluru-mix',
    'butter-muruku',
    'rice-kodubale',
    'garlic-ribbon',
    'nippattu',
    'onion-kodubale',
    'ribbon-pakoda',
    'tilkut-vijaya-karadant',
    'raga-anjeer-karadant',
    'premium-dink-laddu',
    'mawa-vijaya-karadant',
    'royal-vijaya-karadant',
    'premium-gift-box',
    'tilkut-gift-box',
    'supreme-gift-box',
    'anjeer-gift-box',
    'dink-laddu-gift-box',
    'mawa-gift-box'
];
$allSlugs = array_values(array_unique(array_merge($slugs, $fallbackSlugs)));

foreach ($allSlugs as $slug) {
    $pData = $productService->getProductBySlug($slug);
    if (!$pData) continue;
    $related = $productService->getRelatedProducts($pData, 4);
    
    $names = array_column($related, 'name');
    if (in_array('Supreme Vijaya Karadant', $names)) {
        echo "Source: {$pData['name']} (Slug: {$pData['slug']})\n";
        foreach ($related as $index => $item) {
            echo "  - Related $index | ID: {$item['id']} | Name: {$item['name']} | Slug: {$item['slug']} | Image Path: '{$item['image_path']}'\n";
        }
        echo "\n";
    }
}
