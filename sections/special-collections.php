<?php
/**
 * Sweets Website
 * =============================================================
 * File: sections/special-collections.php
 * Description: Discover Our Special Collections card strip
 * =============================================================
 */
?>
<section class="c-special-collections" id="special-collections">
    <div class="container">

        <div class="c-special-collections__header ingredients-img">
            <img src="assets/images/icon/ingredients.png" alt="ingredients">
            <h2 class="c-special-collections__title">Discover Our Special Collections</h2>
            <img src="assets/images/icon/ingredients.png" alt="ingredients">
        </div>

        <?php
$specialCollections = [

    [
        'image' => 'assets/images/homepage/Frame 2147228090.png',
        'alt' => 'Pure Taste, Naturally Sweet',
        'slug' => 'supreme-vijaya-karadant'
    ],
    [
        'image' => 'assets/images/homepage/Frame 2147228089.png',
        'alt' => 'Festive Joy In Every Bite',
        'slug' => 'premium-vijaya-karadant'
    ],
    [
        'image' => 'assets/images/homepage/Frame 2147228088.png',
        'alt' => 'Elegant Gifts For Every Occasion',
        'slug' => 'regal-anjeer-karadant'
    ]
];
?>

        <div class="row g-2 g-md-3 c-special-collections__grid">
            <?php foreach ($specialCollections as $collection): ?>
            <div class="col-12 col-md-4">
                <a href="product-detail.php?slug=<?php echo htmlspecialchars($collection['slug']); ?>" class="c-special-card">
                    <img src="<?php echo htmlspecialchars($collection['image']); ?>"
                         alt="<?php echo htmlspecialchars($collection['alt']); ?>"
                         class="c-special-card__bg"
                         loading="lazy">
                </a>
            </div>
            <?php
endforeach; ?>
        </div>
    </div><!-- /.container -->
</section>
