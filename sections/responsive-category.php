<?php
/**
 * Sweets Website
 * =============================================================
 * File: sections/responsive-category.php
 * Description: Fully responsive category exploration grid
 * =============================================================
 */
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/sections/responsive-category.css">

<section class="c-res-category">
    <div class="c-res-category__container">
        
        <!-- Section Header -->
        <header class="c-res-category__header">
            <h2 class="c-res-category__title expore-heading">Explore by Category</h2>
            <p class="c-res-category__subtitle">Discover handcrafted sweets rooted in tradition</p>
        </header>

        <!-- Category Grid -->
        <div class="c-res-category__grid">
            
            <?php
$categories = [
    [
        'title' => 'Karadant',
        'offer' => '20% OFF',
        'desc' => 'Premium Vijay Karadant',
        'image' => 'assets/images/homepage/Explore (1).png'
    ],
    [
        'title' => 'Stuffed Dates',
        'offer' => 'FLAT 15% OFF',
        'desc' => 'Exotic Dry Fruit Dates',
        'image' => 'assets/images/homepage/Explore (2).png'
    ],
    [
        'title' => 'Assorted Ladu',
        'offer' => 'BUY 2 GET 1',
        'desc' => 'Authentic Besan & Poha Ladu',
        'image' => 'assets/images/homepage/Explore (3).png'
    ],
    [
        'title' => 'Special Gift Box',
        'offer' => 'NEW LAUNCH',
        'desc' => 'Handcrafted Celebration Packs',
        'image' => 'assets/images/homepage/Explore (4).png'
    ]
];

foreach ($categories as $cat):
?>
            <!-- Category Card -->
            <div class="c-cat-card">
                <div class="c-cat-card__img-wrap">
                    <span class="c-cat-card__badge"><?php echo htmlspecialchars($cat['offer']); ?></span>
                    <img src="<?php echo htmlspecialchars($cat['image']); ?>" alt="<?php echo htmlspecialchars($cat['title']); ?>" class="c-cat-card__img" loading="lazy">
                </div>
                <div class="c-cat-card__content">
                    <h3 class="c-cat-card__title"><?php echo htmlspecialchars($cat['title']); ?></h3>
                    <p class="c-cat-card__desc"><?php echo htmlspecialchars($cat['desc']); ?></p>
                    <a href="shop.php?category=<?php echo urlencode($cat['title']); ?>" class="c-cat-card__btn">Shop Now</a>
                </div>
            </div>
            <?php
endforeach; ?>

        </div>
    </div>
</section>
