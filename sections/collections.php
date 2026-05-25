<?php
/**
 * ================================================================
 * File: sections/collections.php
 * Description: Explore by Category — Responsive Grid Layout
 * Features: Skeleton loader, scroll-reveal stagger, 
 *           lazy-loading, ARIA accessibility, fluid BEM architecture
 * ================================================================
 */

require_once SERVICES_PATH . '/CategoryService.php';

$categorySvc = new CategoryService();
$dbCategories = $categorySvc->getRootCategories();

// Map UI fallbacks for specific slugs
$uiFallbacks = [
    'karadant' => [
        'subtitle' => 'Premium Vijay Karadant',
        'image' => 'assets/images/homepage/Explore (1).png',
        'href' => 'karadant.php',
        'badge' => '20% off',
        'badge_type' => 'green',
    ],
    'laddu' => [
        'subtitle' => 'Handcrafted Tradition',
        'image' => 'assets/images/homepage/Explore (4).png',
        'href' => 'karadant.php?category=laddu',
        'badge' => 'Best seller',
        'badge_type' => 'orange',
    ],
    'namkeen' => [
        'subtitle' => 'Crispy & Spicy',
        'image' => 'assets/images/homepage/Explore (3).png',
        'href' => 'namkeen.php',
        'badge' => 'Best seller',
        'badge_type' => 'orange',
    ],
    'gifting' => [
        'subtitle' => 'Joy of Gifting',
        'image' => 'assets/images/homepage/Explore (2).png',
        'href' => 'gifting.php',
        'badge' => '20% off',
        'badge_type' => 'green',
    ],
    'combos' => [
        'subtitle' => 'Joy of Combo',
        'image' => 'assets/images/homepage/expore5.jpg',
        'href' => 'combos.php',
        'badge' => '20% off',
        'badge_type' => 'green',
    ]
];

$categories = [];
foreach ($dbCategories as $cat) {
    $slug = $cat['slug'];
    $fallback = $uiFallbacks[$slug] ?? [
        'subtitle' => $cat['short_description'] ?: 'Pure & Authentic',
        'image' => 'assets/images/homepage/Explore (1).png',
        'href' => 'category-products.php?slug=' . $slug,
        'badge' => 'New',
        'badge_type' => 'orange',
    ];

    $categories[] = [
        'id' => $slug,
        'title' => $cat['name'],
        'subtitle' => $fallback['subtitle'],
        // Prefer DB image if available, otherwise fallback
        'image' => (!empty($cat['image_path']) && file_exists(ROOT_PATH . '/' . $cat['image_path'])) 
                   ? $cat['image_path'] 
                   : $fallback['image'],
        'href' => $fallback['href'],
        'badge' => $fallback['badge'],
        'badge_type' => $fallback['badge_type'],
    ];
}
?>

<!-- Explore by Category Section -->
<section class="c-collections js-reveal" id="explore-category" aria-label="Explore by Category">
    <div class="container">

        <!-- Section Header -->
        <div class="c-collections__header text-center mb-5">
            <h2 class="c-collections__title">
                <img src="assets/images/icon/explorelogo.png" alt="" class="c-collections__flourish d-none d-md-block">
                <span class="c-header-text expore-heading">Explore by Category</span>
                <img src="assets/images/icon/explorelogo.png" alt="" class="c-collections__flourish d-none d-md-block">
            </h2>
        </div>

        <!-- Responsive Grid -->
        <div class="c-collections__grid" id="collectionsGrid" role="region" aria-label="Category grid">
            <div class="row g-3 g-md-4" id="collectionsWrapper">
                <?php foreach ($categories as $i => $cat): ?>
                <div class="col-6 col-lg-3" role="group" aria-label="Category <?php echo $i + 1; ?> of <?php echo count($categories); ?>">

                    <a href="<?php echo htmlspecialchars($cat['href']); ?>" class="c-category-card h-100" id="ec-card-<?php echo $cat['id']; ?>" aria-label="Shop <?php echo htmlspecialchars($cat['title']); ?>">

                        <!-- Skeleton overlay -->
                        <div class="c-category-card__skeleton" aria-hidden="true"></div>

                        <!-- Badge -->
                        <span class="c-category-card__badge c-category-card__badge--<?php echo $cat['badge_type']; ?>">
                            <?php echo htmlspecialchars($cat['badge']); ?>
                        </span>

                        <!-- Image -->
                        <div class="c-category-card__img-wrap">
                            <button class="c-category-card__heart js-wishlist-toggle" 
                                    data-id="<?php echo md5($cat['title']); ?>"
                                    data-name="<?php echo htmlspecialchars($cat['title']); ?>"
                                    data-price="500"
                                    data-image="<?php echo htmlspecialchars($cat['image']); ?>"
                                    data-url="<?php echo htmlspecialchars($cat['href']); ?>"
                                    aria-label="Add to Wishlist">
                                <i class="bi bi-heart"></i>
                            </button>
                            <img
                                src="<?php echo BASE_URL . htmlspecialchars($cat['image']); ?>"
                                alt="<?php echo htmlspecialchars($cat['title']); ?> – Premium Indian Sweets"
                                class="c-category-card__img"
                                width="400" height="320"
                                loading="lazy"
                                onload="var sk=this.closest('.c-category-card').querySelector('.c-category-card__skeleton'); if(sk)sk.remove(); this.closest('.c-category-card').classList.add('ec-loaded');"
                                onerror="var sk=this.closest('.c-category-card').querySelector('.c-category-card__skeleton'); if(sk)sk.remove(); this.closest('.c-category-card').classList.add('ec-loaded');"/>
                        </div>

                        <!-- Content -->
                        <div class="c-category-card__content">
                            <h3 class="c-category-card__title"><?php echo htmlspecialchars($cat['title']); ?></h3>
                            <p class="c-category-card__subtitle"><?php echo htmlspecialchars($cat['subtitle']); ?></p>
                            <span class="c-category-card__btn" role="button" tabindex="0" aria-label="Shop <?php echo htmlspecialchars($cat['title']); ?>">
                                Shop Now
                            </span>
                        </div>
                    </a>
                </div>
                <?php
endforeach; ?>
            </div>
        </div>

    </div>
</section>
