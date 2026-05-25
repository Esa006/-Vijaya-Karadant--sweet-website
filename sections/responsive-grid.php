<?php
/**
 * Sweets Website
 * =============================================================
 * File: sections/responsive-grid.php
 * Description: Fully responsive product catalog grid
 * =============================================================
 */
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/sections/responsive-grid.css">

<section class="c-responsive-grid">
    <div class="c-responsive-grid__container">
        
        <!-- Grid Structure -->
        <div class="c-responsive-grid__grid">
            
            <?php 
            // Demonstrative products for the grid
            $gridProducts = [
                ['name' => 'Premium Vijaya Karadant', 'price' => '₹450.00', 'image' => 'assets/images/homepage/The Karadant Range (1).png'],
                ['name' => 'Signature Anjeer Karadant', 'price' => '₹550.00', 'image' => 'assets/images/homepage/The Karadant Range (2).png'],
                ['name' => 'Authentic Mixed Sweets Box', 'price' => '₹899.00', 'image' => 'assets/images/homepage/The Karadant Range (3).png'],
                ['name' => 'Royal Heritage Laddu', 'price' => '₹320.00', 'image' => 'assets/images/homepage/The Karadant Range (4).png'],
                ['name' => 'Crispy Special Namkeen', 'price' => '₹180.00', 'image' => 'assets/images/homepage/Explore (1).png'],
                ['name' => 'Diet Friendly Dry Fruit Bite', 'price' => '₹650.00', 'image' => 'assets/images/homepage/Explore (2).png'],
                ['name' => 'Golden Turmeric Karadant', 'price' => '₹480.00', 'image' => 'assets/images/homepage/Explore (3).png'],
                ['name' => 'Wedding Special Gift Pack', 'price' => '₹1,250.00', 'image' => 'assets/images/homepage/Explore (4).png']
            ];

            foreach ($gridProducts as $item): 
            ?>
            <!-- Product Card -->
            <article class="c-res-card">
                <div class="c-res-card__img-wrap">
                    <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="c-res-card__img" loading="lazy">
                </div>
                <div class="c-res-card__content">
                    <h3 class="c-res-card__title"><?php echo htmlspecialchars($item['name']); ?></h3>
                    <p class="c-res-card__price"><?php echo htmlspecialchars($item['price']); ?></p>
                    <button class="c-res-card__btn">Add to Cart</button>
                </div>
            </article>
            <?php endforeach; ?>

        </div>
    </div>
</section>
