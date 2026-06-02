<?php
/**
 * Sweets Website
 * =============================================================
 * File: sections/category-strip.php
 * Description: Amazon/Flipkart style horizontally scrollable category strip
 * =============================================================
 */

require_once SERVICES_PATH . '/CategoryService.php';

$categoryService = new CategoryService();
$dbCategories = $categoryService->getCategoriesTree();

$stripCategories = [];

// 1. Static: All Categories
$stripCategories[] = [
    'name' => 'All Categories',
    'url' => BASE_URL . 'index.php#products',
    'image' => BASE_URL . 'assets/images/homepage/New folder/karant/bestseeler karadant (1).png',
    'icon' => 'bi-grid'
];

// 2. Dynamic DB Categories
foreach ($dbCategories as $cat) {
    $catSlug = strtolower(trim($cat['slug']));
    $targetFile = ROOT_PATH . '/' . $catSlug . '.php';
    if (file_exists($targetFile)) {
        $url = BASE_URL . $catSlug . '.php';
    } else {
        $url = BASE_URL . 'category-products.php?slug=' . urlencode($catSlug);
    }
    $stripCategories[] = [
        'name' => $cat['name'],
        'url' => $url,
        'image' => BASE_URL . ($cat['image_path'] ?? 'assets/images/placeholder.png'),
    ];
}

// 3. Static: Bestsellers & Combos
$stripCategories[] = [
    'name' => 'Bestsellers',
    'url' => BASE_URL . 'index.php#bestsellers',
    'image' => BASE_URL . 'assets/images/placeholder.png', // Or a suitable icon
];
$stripCategories[] = [
    'name' => 'Combos',
    'url' => BASE_URL . 'combos.php',
    'image' => BASE_URL . 'assets/images/banners/Karadant-banner.png',
    'is_new' => true
];
?>


<style>
/* Hide scrollbar for Chrome, Safari and Opera */
.c-category-strip__scroll::-webkit-scrollbar {
    display: none;
}
.c-category-strip__img-wrap {
    width: 65px;
    height: 65px;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.c-category-strip__item:hover .c-category-strip__img-wrap {
    transform: scale(1.05);
    box-shadow: 0 4px 10px rgba(0,0,0,0.15) !important;
}
.c-category-strip__name {
    color: #4a4a4a;
    transition: color 0.2s ease;
}
.c-category-strip__item:hover .c-category-strip__name {
    color: #7b1d1d;
}

@media (min-width: 768px) {
    .c-category-strip__img-wrap {
        width: 80px;
        height: 80px;
    }
    .c-category-strip__name {
        font-size: 0.85rem !important;
    }
    /* Center items on desktop if they don't overflow */
    .c-category-strip__scroll {
        justify-content: center;
    }
}
</style>
