<?php
/**
 * Dynamic Sitemap Generator
 * Creates an XML sitemap for Google and other search engines.
 */
require_once 'config/config.php';
require_once SERVICES_PATH . '/ProductService.php';
require_once REPOS_PATH . '/CategoryRepository.php';

header("Content-Type: application/xml; charset=utf-8");

$productService = new ProductService();
$categoryRepo = new CategoryRepository();

$baseUrl = BASE_URL;

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

// Static Pages
$staticPages = [
    'index.php',
    'about.php',
    'karadant.php',
    'namkeen.php',
    'combos.php',
    'gifting.php',
    'global-shipping.php',
    'branches.php',
    'contact.php'
];

foreach ($staticPages as $page) {
    echo "  <url>\n";
    echo "    <loc>" . htmlspecialchars($baseUrl . $page) . "</loc>\n";
    echo "    <changefreq>weekly</changefreq>\n";
    echo "    <priority>0.8</priority>\n";
    echo "  </url>\n";
}

// Categories
$categories = $categoryRepo->getAllFlat();
foreach ($categories as $cat) {
    if (empty($cat['slug'])) continue;
    
    // Skip if it's already a static page
    if (in_array($cat['slug'] . '.php', $staticPages) || $cat['slug'] === 'combos' || $cat['slug'] === 'gifting') {
        continue;
    }

    echo "  <url>\n";
    echo "    <loc>" . htmlspecialchars($baseUrl . 'category-products.php?slug=' . $cat['slug']) . "</loc>\n";
    echo "    <changefreq>weekly</changefreq>\n";
    echo "    <priority>0.9</priority>\n";
    echo "  </url>\n";
}

// Products
$products = $productService->getFilteredProducts();
foreach ($products as $prod) {
    if (empty($prod['slug'])) continue;
    echo "  <url>\n";
    echo "    <loc>" . htmlspecialchars($baseUrl . 'product-detail.php?slug=' . $prod['slug']) . "</loc>\n";
    echo "    <changefreq>daily</changefreq>\n";
    echo "    <priority>1.0</priority>\n";
    echo "  </url>\n";
}

echo "</urlset>\n";
