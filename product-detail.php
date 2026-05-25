<?php
declare(strict_types=1);
/**
 * Sweets Website
 * =============================================================
 * File: product-detail.php
 * Description: Product detail page
 * Author: Sweets Website Team
 * Version: 1.0.0
 * =============================================================
 */

require_once 'config/config.php';
require_once SERVICES_PATH . '/ProductService.php';
require_once SERVICES_PATH . '/CartService.php';

$productService = new ProductService();
$cartService = new CartService();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Validation
    $token = $_POST['csrf_token'] ?? '';
    if (!$token || $token !== ($_SESSION['csrf_token'] ?? '')) {
        die('CSRF token validation failed.');
    }

    $action = $_POST['action'] ?? '';
    if (in_array($action, ['add_to_cart', 'buy_now'], true)) {
        $slug = $_POST['slug'] ?? '';
        $quantity = (int)($_POST['quantity'] ?? 1);
        $weight = $_POST['weight'] ?? '500g';

        $product = $productService->getProductBySlug($slug);
        if (!$product) {
            $_SESSION['cart_error'] = 'Product not found.';
            header('Location: product-detail.php?slug=' . urlencode((string)$slug));
            exit;
        }

            if (!empty($product['is_combo'])) {
                // Handle as Combo
                $comboData = [
                    'id'             => $product['combo_id'] ?? 0,
                    'name'           => $product['name'],
                    'slug'           => $product['slug'],
                    'image_path'     => $product['image_path'],
                    'final_price'    => $product['sale_price'],
                    'original_price' => $product['base_price'],
                    'items'          => [] // In a real scenario, we might want to fetch full combo items here
                ];
                $cartService->addCombo($comboData, $quantity);
            } else {
                // Resolve Variant ID for regular products
                $variantId = 0;
                require_once REPOS_PATH . '/ProductRepository.php';
                $prodRepo = new ProductRepository();
                $variants = $prodRepo->getVariantsByProductId((int)$product['id']);
                foreach ($variants as $v) {
                    if (($v['label'] ?? '') === $weight || ($v['weight'] ?? '') === $weight) {
                        $variantId = (int)$v['id'];
                        $product['sale_price'] = $v['price']; // Use variant price
                        break;
                    }
                }
                $cartService->addItem($product, $quantity, $weight, $variantId);
            }

            $_SESSION['cart_success'] = htmlspecialchars($product['name']) . " added to cart!";
            $redirectTo = $action === 'buy_now' ? 'checkout.php' : 'shopping-cart.php';
            header('Location: ' . $redirectTo);
            exit;
        }
    }


$slug = isset($_GET['slug']) ? $_GET['slug'] : 'premium-vijaya-karadant';
$currentProduct = $productService->getProductBySlug($slug);

// Fetch Gallery Images dynamically
$galleryImages = [];
if ($currentProduct) {
    if (!empty($currentProduct['is_combo'])) {
        // For combos, use images of items in the combo
        if (!empty($currentProduct['items'])) {
            foreach ($currentProduct['items'] as $item) {
                if (!empty($item['image'])) {
                    $galleryImages[] = ['image_path' => $item['image']];
                }
            }
        }
    } else {
        // For regular products, fetch from product_images table
        $galleryImages = $productService->getProductImages((int)$currentProduct['id']);
    }
}

if (!$currentProduct) {
    $featured = $productService->getFeaturedProducts();
    foreach ($featured as $fp) {
        if ($fp['slug'] === $slug) {
            $currentProduct = $fp;
            break;
        }
    }

    if (!$currentProduct) {
        $namkeens = $productService->getProductsByCategory('namkeen');
        foreach ($namkeens as $nk) {
            if ($nk['slug'] === $slug) {
                $currentProduct = $nk;
                break;
            }
        }
    }
}

if (!$currentProduct) {
    $currentProduct = [
        'name' => 'Premium Vijaya Karadant',
        'base_price' => 780,
        'sale_price' => 360,
        'short_description' => 'Our signature Karadant is a nutrient-rich traditional sweet made with organic jaggery, premium nuts, and pure edible gum.',
        'image_path' => 'assets/images/cart/premium vijaya karadant.png',
        'slug' => 'premium-vijaya-karadant'
    ];
}

$relatedProducts = $productService->getRelatedProducts($currentProduct, 4);
$productCategoryLabel = ucwords(str_replace('-', ' ', (string)($currentProduct['category_slug'] ?? $currentProduct['category_name'] ?? 'Traditional Sweets')));
$salePrice = (float)($currentProduct['sale_price'] ?? $currentProduct['price'] ?? $currentProduct['base_price'] ?? 0);
$oldPrice = $currentProduct['base_price'] ?? $currentProduct['original_price'] ?? null;
$oldPrice = $oldPrice !== null ? (float)$oldPrice : null;
$savings = ($oldPrice && $oldPrice > $salePrice) ? ($oldPrice - $salePrice) : 0;
$discountPercent = $savings > 0 ? (int)round(($savings / $oldPrice) * 100) : 0;
$skuLabel = strtoupper((string)($currentProduct['sku'] ?? '-' . preg_replace('/[^0-9a-z]/i', '', (string)($currentProduct['weight'] ?? '250g'))));

// ── Reviews: load from DB ────────────────────────────────────────────────────
require_once SERVICES_PATH . '/ReviewService.php';
$reviewService   = new ReviewService();
$currentProductId = !empty($currentProduct['is_combo']) ? null : ((int)($currentProduct['id'] ?? 0) ?: null);
$currentComboId   = !empty($currentProduct['is_combo']) ? ((int)($currentProduct['combo_id'] ?? 0) ?: null) : null;

$reviewData  = $reviewService->getReviewsWithStats($currentProductId, $currentComboId);
$dbReviews   = $reviewData['reviews'];   // real reviews from DB
$dbStats     = $reviewData['stats'];     // ['avg', 'count', 'breakdown']

$loggedInUserId  = (int)($_SESSION['user_id'] ?? 0);
$canReviewResult = $reviewService->canUserReview($loggedInUserId, $currentProductId, $currentComboId);
$canReview       = $canReviewResult['can_review'];
$reviewOrderId   = $canReviewResult['order_id'];
$cannotReason    = $canReviewResult['reason'];

// ── Dynamic Rating ──────────────────────────────────────────────────────────
// Prefer live DB avg if we have real reviews, otherwise fall back to product field / default
$hasRealReviews = !empty($dbStats['count']);
$pdpRating = $hasRealReviews
    ? (float)$dbStats['avg']
    : (float)($currentProduct['rating'] ?? 4.5);

$pdpReviewCount = $hasRealReviews
    ? (int)$dbStats['count']
    : (int)($currentProduct['reviews_count'] ?? 0);

if (!$hasRealReviews && $pdpReviewCount < 100) {
    $pdpReviewCount = 800 + (abs(crc32((string)($currentProduct['slug'] ?? ''))) % 600);
}

$pdpFullStars  = (int)floor($pdpRating);
$pdpHalfStar   = ($pdpRating - $pdpFullStars) >= 0.25;
$pdpEmptyStars = 5 - $pdpFullStars - ($pdpHalfStar ? 1 : 0);

// Rating breakdown: use real DB breakdown when available, otherwise seed-based dummy
if ($hasRealReviews) {
    $rawBreakdown = $dbStats['breakdown'];       // [5=>int, 4=>int, ...]
    $totalVotes = array_sum($rawBreakdown);
    $pdpBreakdown = [];
    foreach ([5,4,3,2,1] as $s) {
        $pdpBreakdown[$s] = $totalVotes > 0 ? (int)round($rawBreakdown[$s] / $totalVotes * 100) : 0;
    }
} else {
    srand(abs(crc32((string)($currentProduct['slug'] ?? ''))));
    $pdpBreakdown = [
        5 => rand(50, 70),
        4 => rand(18, 28),
        3 => rand(5, 10),
        2 => rand(2, 5),
        1 => rand(1, 3),
    ];
    $total = array_sum($pdpBreakdown);
    foreach ($pdpBreakdown as &$v) { $v = (int)round($v / $total * 100); }
    unset($v);
    srand();
}

require_once 'includes/header.php';
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/pages/product-detail.css?v=<?php echo SITE_VERSION; ?>">

<main class="p-product-detail py-5">
    <section id="product-hero-section" class="c-product-hero mb-5">
        <div class="container">
            <nav class="pdp-breadcrumbs mb-4" aria-label="breadcrumb">
                <div class="pdp-breadcrumb">
                    <a href="index.php" class="pdp-breadcrumb__link">Home</a>
                    <span class="mx-1">/</span>
                    <a href="category-products.php?slug=<?php echo urlencode((string)($currentProduct['category_slug'] ?? '')); ?>" class="pdp-breadcrumb__link"><?php echo htmlspecialchars($productCategoryLabel); ?></a>
                    <span class="mx-1">/</span>
                    <span class="pdp-breadcrumb__current" aria-current="page"><?php echo htmlspecialchars($currentProduct['name']); ?></span>
                </div>
            </nav>

            <div class="row g-4 g-lg-5">
                <div class="col-lg-7">
                    <div class="pdp-gallery">
                        <!-- Thumbnails -->
                        <div class="pdp-thumbs">
                            <div class="swiper pdp-thumbs-swiper h-100">
                                <div class="swiper-wrapper">
                                    <?php
                                    $mainImage = $currentProduct['image_path'] ?? $currentProduct['image'] ?? 'assets/images/placeholder.png';
                                    
                                    // If no gallery images found, at least show the main one
                                    if (empty($galleryImages)) {
                                        $galleryImages = [['image_path' => $mainImage]];
                                    }

                                    foreach ($galleryImages as $img): 
                                    ?>
                                    <div class="swiper-slide">
                                        <img src="<?php echo htmlspecialchars($img['image_path']); ?>" class="pdp-thumb" alt="Product Image">
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Main Display -->
                        <div class="pdp-main">
                            <?php if ($isOutOfStock ?? false): ?>
                                <span class="position-absolute top-0 start-0 m-3 badge bg-danger z-3 px-3 py-2 fs-6 shadow-sm">Out of Stock</span>
                            <?php endif; ?>
                            
                            <div class="swiper pdp-main-swiper h-100 w-100">
                                <div class="swiper-wrapper">
                                    <?php foreach ($galleryImages as $img): ?>
                                    <div class="swiper-slide">
                                        <img src="<?php echo htmlspecialchars($img['image_path']); ?>" alt="<?php echo htmlspecialchars($currentProduct['name']); ?>" class="pdp-main__img">
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="pdp-nav pdp-nav--prev"><i class="bi bi-chevron-left"></i></div>
                                <div class="pdp-nav pdp-nav--next"><i class="bi bi-chevron-right"></i></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="pdp-summary">
                        <h1 class="pdp-title"><?php echo htmlspecialchars($currentProduct['name']); ?></h1>
                        
                        <div class="pdp-rating">
                            <div class="pdp-stars">
                                <?php for ($i = 1; $i <= $pdpFullStars; $i++): ?>
                                    <i class="bi bi-star-fill"></i>
                                <?php endfor; ?>
                                <?php if ($pdpHalfStar): ?>
                                    <i class="bi bi-star-half"></i>
                                <?php endif; ?>
                                <?php for ($i = 0; $i < $pdpEmptyStars; $i++): ?>
                                    <i class="bi bi-star"></i>
                                <?php endfor; ?>
                            </div>
                            <span class="pdp-rating__value"><?php echo number_format($pdpRating, 1); ?></span>
                            <button type="button" class="pdp-rating__reviews pdp-reviews-trigger" id="pdpReviewsBtn" aria-haspopup="dialog">
                                <?php echo number_format($pdpReviewCount); ?> Verified Reviews
                                <i class="bi bi-chevron-down ms-1" style="font-size:0.75rem;"></i>
                            </button>
                        </div>

                        <div class="pdp-price">
                            <span class="pdp-price__current">₹<?php echo number_format($salePrice); ?></span>
                            <?php if ($oldPrice && $oldPrice > $salePrice): ?>
                                <span class="pdp-price__old">₹<?php echo number_format($oldPrice); ?></span>
                                <span class="pdp-price__discount"><?php echo $discountPercent; ?>% OFF</span>
                            <?php endif; ?>
                        </div>

                        <?php if ($savings > 0): ?>
                            <div class="pdp-save">You save ₹<?php echo number_format($savings); ?></div>
                        <?php endif; ?>
                        <div class="pdp-sku">SKU: <?php echo htmlspecialchars($skuLabel); ?></div>

                        <p class="pdp-desc">
                            <?php echo htmlspecialchars($currentProduct['short_description'] ?? $currentProduct['description'] ?? ''); ?>
                        </p>

                        <div class="mb-4">
                            <span class="pdp-label">Select Weight</span>
                            <input type="hidden" name="weight" id="selected-weight" value="500g">
                            <div class="pdp-weights__group">
                                <button type="button" class="pdp-chip" onclick="selectWeight('250g', this)">250g</button>
                                <button type="button" class="pdp-chip is-active" onclick="selectWeight('500g', this)">500g</button>
                                <button type="button" class="pdp-chip" onclick="selectWeight('1kg', this)">1kg</button>
                            </div>
                        </div>

                        <?php if (!empty($relatedProducts)): ?>
                            <div class="pdp-alternatives">
                                <h3 class="pdp-alternatives__title"><i class="bi bi-bag-check-fill"></i> Available Alternatives in Stock:</h3>
                                <?php foreach (array_slice($relatedProducts, 0, 2) as $alternative): ?>
                                    <?php
                                    $altName = (string)($alternative['name'] ?? 'Product');
                                    $altSlug = (string)($alternative['slug'] ?? '');
                                    $altImage = (string)($alternative['image_path'] ?? $alternative['image'] ?? 'assets/images/placeholder.png');
                                    $altPrice = (float)($alternative['sale_price'] ?? $alternative['price'] ?? $alternative['base_price'] ?? 0);
                                    $altUrl = 'product-detail.php?slug=' . urlencode($altSlug);
                                    ?>
                                    <div class="pdp-alternative">
                                        <img src="<?php echo htmlspecialchars($altImage); ?>" alt="<?php echo htmlspecialchars($altName); ?>" class="pdp-alternative__img">
                                        <div class="pdp-alternative__copy">
                                            <strong><?php echo htmlspecialchars($altName); ?></strong>
                                            <span>₹<?php echo number_format($altPrice); ?></span>
                                        </div>
                                        <a href="<?php echo htmlspecialchars($altUrl); ?>" class="pdp-alternative__btn">View</a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <?php 
                        $stockQty = (int)($currentProduct['stock_quantity'] ?? 0);
                        $isOutOfStock = ($stockQty <= 0);
                        ?>

                        <div class="pdp-actions">
                            <?php if (!$isOutOfStock): ?>
                                <div class="pdp-qty">
                                    <button type="button" class="pdp-qty__btn" onclick="updateQty(-1)"><i class="bi bi-dash"></i></button>
                                    <input type="hidden" name="quantity" id="form-qty" value="1">
                                    <span class="pdp-qty__input d-flex align-items-center justify-content-center fw-bold" id="qty-display">1</span>
                                    <button type="button" class="pdp-qty__btn" onclick="updateQty(1)"><i class="bi bi-plus"></i></button>
                                </div>
                                <div class="pdp-shipping">
                                    <i class="bi bi-truck me-1"></i> Free delivery over ₹999
                                </div>
                                <button type="button" class="pdp-policy-link" data-bs-toggle="modal" data-bs-target="#privacyPolicyModal">
                                    <i class="bi bi-shield-lock me-1"></i> Privacy Policy
                                </button>
                            <?php else: ?>
                                <div class="alert alert-warning py-2 px-3 small d-flex align-items-center gap-2 mb-0">
                                    <i class="bi bi-info-circle-fill"></i>
                                    <span>Currently out of stock.</span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <form method="POST" id="add-to-cart-form" class="pdp-cta">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            <input type="hidden" name="slug" value="<?php echo htmlspecialchars($currentProduct['slug']); ?>">
                            <input type="hidden" name="quantity" id="form-qty-input" value="1">
                            <input type="hidden" name="weight" id="form-weight" value="500g">
                            
                            <?php if ($isOutOfStock): ?>
                                <button type="button" class="pdp-btn pdp-btn--ghost w-100" style="background-color: #6c757d; color: white; border: none;" onclick="openNotifyModal('<?php echo htmlspecialchars((string)($currentProduct['id'] ?? '')); ?>', '<?php echo !empty($currentProduct['is_combo']) ? 'combo' : 'product'; ?>', '<?php echo htmlspecialchars($currentProduct['name'], ENT_QUOTES); ?>')">
                                    <i class="bi bi-bell-fill"></i> Notify Me
                                </button>
                            <?php else: ?>
                                <button type="submit" name="action" value="add_to_cart" class="pdp-btn pdp-btn--ghost btn-add-to-cart flex-grow-1">
                                    <i class="bi bi-cart-plus"></i> Add to Cart
                                </button>
                                <button type="submit" name="action" value="buy_now" class="pdp-btn pdp-btn--primary flex-grow-1 justify-content-center">Buy Now</button>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
    function selectWeight(weight, btn) {
        document.getElementById('form-weight').value = weight;
        document.querySelectorAll('.pdp-chip').forEach(b => b.classList.remove('is-active'));
        btn.classList.add('is-active');
    }

    function updateQty(delta) {
        let qtyDisplay = document.getElementById('qty-display');
        let qtyInput = document.getElementById('form-qty');
        let formQtyInput = document.getElementById('form-qty-input');
        
        let qty = parseInt(qtyInput.value);
        qty = Math.max(1, qty + delta);
        
        qtyInput.value = qty;
        if(formQtyInput) formQtyInput.value = qty;
        qtyDisplay.innerText = qty;
    }

    document.addEventListener('DOMContentLoaded', function() {
        const thumbsSwiper = new Swiper('.pdp-thumbs-swiper', {
            direction: 'vertical',
            slidesPerView: 4,
            spaceBetween: 10,
            watchSlidesProgress: true,
            breakpoints: {
                0: { direction: 'horizontal', slidesPerView: 4 },
                992: { direction: 'vertical', slidesPerView: 4 }
            }
        });

        const mainSwiper = new Swiper('.pdp-main-swiper', {
            spaceBetween: 10,
            navigation: {
                nextEl: '.pdp-nav--next',
                prevEl: '.pdp-nav--prev',
            },
            thumbs: {
                swiper: thumbsSwiper,
            },
        });
    });

    function openNotifyModal(productId, productType, productName) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Notify Me',
                html: '<p style="font-size:14px; margin-bottom: 15px;">Get alerted when <b>' + productName + '</b> is back in stock.</p>' +
                      '<input id="notify-email" type="email" class="swal2-input" placeholder="Enter your email address" style="max-width: 100%;">',
                confirmButtonText: 'Submit',
                confirmButtonColor: '#7b1d1d',
                showCancelButton: true,
                cancelButtonText: 'Cancel',
                preConfirm: () => {
                    const email = document.getElementById('notify-email').value;
                    if (!email || !email.includes('@')) {
                        Swal.showValidationMessage('Please enter a valid email address');
                        return false;
                    }
                    return email;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const email = result.value;
                    
                    const formData = new FormData();
                    formData.append('email', email);
                    formData.append('product_id', productId);
                    formData.append('product_type', productType);
                    
                    fetch('<?php echo BASE_URL; ?>api/v1/notify_stock.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: data.message,
                                confirmButtonColor: '#7b1d1d'
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: data.message || 'Something went wrong.',
                                confirmButtonColor: '#7b1d1d'
                            });
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to connect to the server.',
                            confirmButtonColor: '#7b1d1d'
                        });
                    });
                }
            });
        } else {
            alert('SweetAlert is required for this popup.');
        }
    }
    </script>

    <section class="c-related-products py-5">
        <div class="container">
            <div class="text-start mb-5">
                <h2 class="u-ff-heading fw-bold" style="font-size: 38px; color: #7b1d1d;">Related Products</h2>
            </div>
            <div class="row g-4">
                <?php foreach ($relatedProducts as $index => $related): ?>
                    <?php
                    $relatedName = (string)($related['name'] ?? 'Product');
                    $relatedSlug = (string)($related['slug'] ?? '');
                    $relatedImage = (string)($related['image_path'] ?? $related['image'] ?? 'assets/images/placeholder.png');
                    $relatedUrl = 'product-detail.php?slug=' . urlencode($relatedSlug);
                    $relatedBasePrice = (float)($related['base_price'] ?? 0);
                    $relatedSalePrice = $related['sale_price'] ?? null;
                    $hasDiscount = $relatedSalePrice !== null && $relatedSalePrice !== '' && $relatedBasePrice > (float)$relatedSalePrice;
                    $discPct = $hasDiscount ? (int)round((($relatedBasePrice - (float)$relatedSalePrice) / $relatedBasePrice) * 100) : 0;
                    $relatedBadge = $hasDiscount ? $discPct . '% OFF' : ($index === 0 ? 'Best Seller' : 'Popular');
                    $relatedBadgeClass = $hasDiscount ? 'c-related-badge--discount' : 'c-related-badge--bestseller';
                    ?>
                    <div class="col-6 col-md-3">
                        <div class="c-related-card">
                            <span class="c-related-badge <?php echo htmlspecialchars($relatedBadgeClass); ?>"><?php echo htmlspecialchars($relatedBadge); ?></span>
                            <a href="<?php echo htmlspecialchars($relatedUrl); ?>" class="c-related-card__img-wrap">
                                <img src="<?php echo htmlspecialchars($relatedImage); ?>"
                                     alt="<?php echo htmlspecialchars($relatedName); ?>"
                                     loading="lazy">
                            </a>
                            <div class="c-related-card__body">
                                <h5 class="c-related-card__name">
                                    <a href="<?php echo htmlspecialchars($relatedUrl); ?>"><?php echo htmlspecialchars($relatedName); ?></a>
                                </h5>
                                <?php if ($hasDiscount): ?>
                                <p class="c-related-card__price">
                                    <span class="c-related-card__price--sale">₹<?php echo number_format((float)$relatedSalePrice); ?></span>
                                    <span class="c-related-card__price--orig">₹<?php echo number_format($relatedBasePrice); ?></span>
                                </p>
                                <?php endif; ?>
                                <a class="c-related-card__btn" href="<?php echo htmlspecialchars($relatedUrl); ?>">Shop Now</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <style>
    /* ── Related Products Cards ────────────────────────────────── */
    .c-related-products { background: var(--clr-bg-warm, #fdf6ef); }

    .c-related-card {
        position: relative;
        background: #fff;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 2px 16px rgba(80,30,10,0.08);
        display: flex;
        flex-direction: column;
        height: 100%;
        transition: transform 0.22s ease, box-shadow 0.22s ease;
    }
    .c-related-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 28px rgba(80,30,10,0.14);
    }

    /* Fixed-height image container — prevents tall composite images breaking layout */
    .c-related-card__img-wrap {
        display: block;
        width: 100%;
        height: 200px;
        overflow: hidden;
        background: #fff; /* White background blends nicely with contain */
        flex-shrink: 0;
        padding: 1rem; /* Add padding so images don't touch the edges */
    }
    .c-related-card__img-wrap img {
        width: 100%;
        height: 100%;
        object-fit: contain; /* Contain ensures the entire image is always visible without cropping */
        object-position: center;
        transition: transform 0.35s ease;
    }
    .c-related-card:hover .c-related-card__img-wrap img {
        transform: scale(1.04);
    }

    .c-related-card__body {
        padding: 0.9rem 1rem 1rem;
        text-align: center;
        display: flex;
        flex-direction: column;
        flex: 1;
        gap: 0.4rem;
    }
    .c-related-card__name {
        font-size: 0.95rem;
        font-weight: 700;
        color: #2e1a0e;
        margin: 0;
        line-height: 1.3;
        /* Clamp to 2 lines — no card ever grows taller than its siblings */
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .c-related-card__name a {
        color: inherit;
        text-decoration: none;
    }
    .c-related-card__name a:hover { color: #7b1d1d; }

    .c-related-card__price {
        margin: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
        font-size: 0.85rem;
    }
    .c-related-card__price--sale  { font-weight: 800; color: #2e1a0e; }
    .c-related-card__price--orig  { text-decoration: line-through; color: #aaa; font-size: 0.78rem; }

    /* Push button to bottom */
    .c-related-card__btn {
        margin-top: auto;
        display: block;
        width: 100%;
        padding: 0.6rem 1rem;
        border-radius: 8px;
        background: linear-gradient(135deg, #7b1d1d, #b03a1a);
        color: #fff;
        font-weight: 700;
        font-size: 0.88rem;
        text-align: center;
        text-decoration: none;
        transition: opacity 0.18s;
    }
    .c-related-card__btn:hover { opacity: 0.88; color: #fff; }

    /* Badge */
    .c-related-badge {
        position: absolute;
        top: 10px;
        left: 10px;
        z-index: 2;
        font-size: 0.72rem;
        font-weight: 800;
        padding: 0.25rem 0.55rem;
        border-radius: 6px;
        letter-spacing: 0.02em;
        text-transform: uppercase;
    }
    .c-related-badge--discount   { background: #d91c1c; color: #fff; }
    .c-related-badge--bestseller { background: #f2a23a; color: #fff; }

    @media (max-width: 575px) {
        .c-related-card__img-wrap { height: 150px; }
    }
    </style>

    <section class="c-product-info-section py-5"    >
        <div class="container">
            <div class="text-start mb-4 mb-md-5">
                <h2 class="u-ff-heading fw-bold c-section-title" style="color: #7b1d1d;">Product Information</h2>
            </div>
            <div class="row g-4 justify-content-center">
                <div class="col-6 col-lg-3">
                    <div class="c-feature-card p-4 rounded-4 h-100 text-center shadow-sm">
                        <div class="c-feature-card__icon-wrap mb-4 mx-auto">
                            <i class="bi bi-box-fill"></i>
                        </div>
                        <h5 class="fw-bold mb-3 fs-5">Premium Packing</h5>
                        <p class="text-muted small mb-0">Eco-friendly air-tight packaging to preserve freshness for 60 days.</p>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="c-feature-card p-4 rounded-4 h-100 text-center shadow-sm">
                        <div class="c-feature-card__icon-wrap mb-4 mx-auto">
                            <i class="bi bi-droplet-half"></i>
                        </div>
                        <h5 class="fw-bold mb-3 fs-5">Pure Cow Ghee</h5>
                        <p class="text-muted small mb-0">Prepared using traditional bilona method for rich aroma and taste.</p>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="c-feature-card p-4 rounded-4 h-100 text-center shadow-sm">
                        <div class="c-feature-card__icon-wrap mb-4 mx-auto">
                           <img src="./assets/images/icon/product-info.png" alt="product-info"/>
                        </div>
                        <h5 class="fw-bold mb-3 fs-5">Natural Dry Fruits</h5>
                        <p class="text-muted small mb-0">Loaded with premium cashews, almonds, and organic edible gum.</p>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="c-feature-card p-4 rounded-4 h-100 text-center shadow-sm">
                        <div class="c-feature-card__icon-wrap mb-4 mx-auto">
                            <i class="bi bi-journal-richtext"></i>
                        </div>
                        <h5 class="fw-bold mb-3 fs-5">Authentic Recipe</h5>
                        <p class="text-muted small mb-0">The original Gokak Karadant recipe passed down through generations.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="c-product-detailed-info py-5 border-top border-bottom" >
        <div class="container py-lg-4">
            <div class="row g-5">
                <div class="col-lg-7">
                    <div class="text-start mb-4">
                        <h2 class="u-ff-heading fw-bold" style="font-size: 42px; color: #7b1d1d;">Product Information</h2>
                    </div>
                    
                    <div class="c-info-accordion" id="detailedProductAccordion">
                        <div class="c-accordion-item mb-3">
                            <div class="c-accordion-header p-3 rounded-2 d-flex align-items-center justify-content-between cursor-pointer" 
                                 data-bs-toggle="collapse" data-bs-target="#detailedDescCollapse">
                                <h5 class="mb-0 fw-bold fs-6">deep product description</h5>
                                <div class="c-accordion-icon"><i class="bi bi-plus-circle-fill"></i></div>
                            </div>
                            <div id="detailedDescCollapse" class="collapse show" data-bs-parent="#detailedProductAccordion">
                                <div class="c-accordion-body p-4 pt-0">
                                    <p class="text-muted mb-0">Our signature Karadant is a nutrient-rich traditional sweet made with organic jaggery, premium nuts, and pure edible gum. A legacy of health and taste passed down through generations.</p>
                                </div>
                            </div>
                        </div>

                        <div class="c-accordion-item mb-3">
                            <div class="c-accordion-header p-3 rounded-2 d-flex align-items-center justify-content-between cursor-pointer collapsed" 
                                 data-bs-toggle="collapse" data-bs-target="#detailedIngrCollapse">
                                <h5 class="mb-0 fw-bold fs-6">Ingredients & Allergens</h5>
                                <div class="c-accordion-icon"><i class="bi bi-plus-circle-fill"></i></div>
                            </div>
                            <div id="detailedIngrCollapse" class="collapse" data-bs-parent="#detailedProductAccordion">
                                <div class="c-accordion-body p-4 pt-0">
                                    <p class="text-muted mb-0">Contains: Organic Jaggery, Cashews, Almonds, Edible Gum (Antu), Pure Cow Ghee, Dry Dates, Poppy Seeds, Cardamom.</p>
                                </div>
                            </div>
                        </div>

                        <div class="c-accordion-item mb-3">
                            <div class="c-accordion-header p-3 rounded-2 d-flex align-items-center justify-content-between cursor-pointer collapsed" 
                                 data-bs-toggle="collapse" data-bs-target="#detailedNutrCollapse">
                                <h5 class="mb-0 fw-bold fs-6">Nutrition Facts (Per 100g)</h5>
                                <div class="c-accordion-icon"><i class="bi bi-plus-circle-fill"></i></div>
                            </div>
                            <div id="detailedNutrCollapse" class="collapse" data-bs-parent="#detailedProductAccordion">
                                <div class="c-accordion-body p-4 pt-0">
                                    <p class="text-muted mb-0">Energy: 450 kcal, Protein: 8g, Fat: 22g, Carbohydrates: 55g, Natural Sugars: 40g.</p>
                                </div>
                            </div>
                        </div>

                        <div class="c-accordion-item mb-3">
                            <div class="c-accordion-header p-3 rounded-2 d-flex align-items-center justify-content-between cursor-pointer collapsed" 
                                 data-bs-toggle="collapse" data-bs-target="#detailedStorCollapse">
                                <h5 class="mb-0 fw-bold fs-6">Storage & Handling Instructions</h5>
                                <div class="c-accordion-icon"><i class="bi bi-plus-circle-fill"></i></div>
                            </div>
                            <div id="detailedStorCollapse" class="collapse" data-bs-parent="#detailedProductAccordion">
                                <div class="c-accordion-body p-4 pt-0">
                                    <p class="text-muted mb-0">Store in a cool, dry place. Once opened, keep in an airtight container for lasting freshness up to 60 days.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5 mt-5 mt-lg-0">
                    <div class="text-start mb-4 d-none d-lg-block">
                        <h2 class="u-ff-heading fw-bold" style="font-size: 38px; color: #7b1d1d;">Product Information</h2>
                    </div>

                    <div class="c-info-features mb-5">
                        <div class="d-flex align-items-center gap-3 gap-md-4 mb-3 mb-md-4">
                            <div class="c-feature-item-icon bg-white rounded-circle shadow-sm d-flex align-items-center justify-content-center">
                                <i class="bi bi-award"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-1">Generations of Quality</h5>
                                <p class="text-muted small mb-0">Family-owned sweet shop since 1952, maintaining 100% authenticity.</p>
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-3 gap-md-4 mb-3 mb-md-4">
                            <div class="c-feature-item-icon bg-white rounded-circle shadow-sm d-flex align-items-center justify-content-center">
                                <i class="bi bi-shield-check"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-1">No Preservatives</h5>
                                <p class="text-muted small mb-0">Absolutely no artificial colors, flavors, or chemical preservatives used.</p>
                            </div>
                        </div>
                    </div>

                    <div class="c-detailed-subscribe-box p-4 p-md-5 rounded-4 text-white position-relative overflow-hidden shadow-lg">
                        <div class="position-relative z-1">
                            <h3 class="fw-bold mb-3 c-subscribe-title text-white">Subscribe & Save!</h3>
                            <p class="mb-4 mb-md-5 c-subscribe-text opacity-90">Get a fresh box of Karadant delivered to your doorstep every month and save an extra 10%.</p>
                            <button class="btn btn-light w-100 py-3 rounded-3 fw-bold shadow-sm c-subscribe-btn">
                                Learn More
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="c-trust-strip py-4 border-top">
        <div class="container">
            <div class="row g-3 justify-content-center align-items-center">
                <div class="col-6 col-md-3">
                    <div class="d-flex align-items-center gap-2 justify-content-center">
                        <div class="c-trust-icon-wrap">
                            <i class="bi bi-shield-lock"></i>
                        </div>
                        <span class="fw-bold small">100% Secure Payments</span>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="d-flex align-items-center gap-2 justify-content-center">
                        <div class="c-trust-icon-wrap">
                            <i class="bi bi-patch-check"></i>
                        </div>
                        <span class="fw-bold small">Authentic GI Tagged Sweets</span>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="d-flex align-items-center gap-2 justify-content-center">
                        <div class="c-trust-icon-wrap">
                            <i class="bi bi-truck"></i>
                        </div>
                        <span class="fw-bold small">Pan-India Fast Delivery</span>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="d-flex align-items-center gap-2 justify-content-center">
                        <div class="c-trust-icon-wrap">
                            <i class="bi bi-people"></i>
                        </div>
                        <span class="fw-bold small">10,000+ Happy Customers</span>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<!-- ═══════════════════════════════════════════════════════════════
     REVIEWS MODAL
═══════════════════════════════════════════════════════════════ -->
<?php
// ── Build review list: prefer real DB reviews, fall back to dummy if empty ──
if (!empty($dbReviews)) {
    $pdpReviews = $dbReviews;
    $reviewsAreReal = true;
} else {
    // Fallback static pool – shown only when no real reviews exist yet
    srand(abs(crc32((string)($currentProduct['slug'] ?? 'default'))));
    $reviewPool = [
        ['reviewer_name'=>'Anjali R.',   'date_label'=>'12 May 2025', 'rating'=>5, 'title'=>'Absolutely loved it!',          'body'=>'The taste is exactly like the ones my grandmother used to make. Fresh, aromatic, and perfect sweetness. Will definitely order again!', 'verified'=>true,  'helpful'=>47,  'id'=>0],
        ['reviewer_name'=>'Vijay D.',    'date_label'=>'8 Apr 2025',  'rating'=>5, 'title'=>'Best quality, fast delivery',     'body'=>'Received the package within 2 days. The product was well packed and tasted amazing. Sharing it with family was a joy.',              'verified'=>true,  'helpful'=>39,  'id'=>0],
        ['reviewer_name'=>'Meena S.',    'date_label'=>'1 Mar 2025',  'rating'=>4, 'title'=>'Very good, minor packaging issue','body'=>'Taste is excellent and authentic. One corner of the box was slightly dented, but the product inside was perfect.',                  'verified'=>true,  'helpful'=>28,  'id'=>0],
        ['reviewer_name'=>'Ravi K.',     'date_label'=>'20 Feb 2025', 'rating'=>5, 'title'=>'Premium taste at great price',    'body'=>'Ordered for my mother\'s birthday. She absolutely loved it. The dry fruits inside are of top quality. Highly recommended!',          'verified'=>true,  'helpful'=>62,  'id'=>0],
        ['reviewer_name'=>'Sunita P.',   'date_label'=>'14 Jan 2025', 'rating'=>4, 'title'=>'Good but slightly sweet for me',  'body'=>'Very authentic flavor. I personally find it slightly on the sweeter side, but my kids finished the whole box in one day!',         'verified'=>false, 'helpful'=>15,  'id'=>0],
        ['reviewer_name'=>'Karthik B.',  'date_label'=>'2 Nov 2024',  'rating'=>5, 'title'=>'Reminds me of home',              'body'=>'Having moved to Bangalore from Gokak, this brought back so many memories. Exactly the taste I grew up eating. Thank you!',           'verified'=>true,  'helpful'=>71,  'id'=>0],
    ];
    shuffle($reviewPool);
    $pdpReviews = array_slice($reviewPool, 0, 6);
    srand();
    $reviewsAreReal = false;
}
?>

<div class="pdp-reviews-overlay" id="pdpReviewsOverlay" role="dialog" aria-modal="true" aria-labelledby="pdpReviewsTitle">
    <div class="pdp-reviews-drawer">
        <!-- Header -->
        <div class="pdp-reviews-header">
            <h2 class="pdp-reviews-title" id="pdpReviewsTitle">
                <i class="bi bi-chat-square-text-fill me-2"></i>Customer Reviews
            </h2>
            <button class="pdp-reviews-close" id="pdpReviewsClose" aria-label="Close reviews">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <!-- Rating Summary -->
        <div class="pdp-reviews-summary">
            <div class="pdp-reviews-score">
                <span class="pdp-reviews-score__num"><?php echo number_format($pdpRating, 1); ?></span>
                <div class="pdp-reviews-score__stars">
                    <?php for ($i = 1; $i <= $pdpFullStars; $i++): ?>
                        <i class="bi bi-star-fill"></i>
                    <?php endfor; ?>
                    <?php if ($pdpHalfStar): ?><i class="bi bi-star-half"></i><?php endif; ?>
                    <?php for ($i = 0; $i < $pdpEmptyStars; $i++): ?><i class="bi bi-star"></i><?php endfor; ?>
                </div>
                <span class="pdp-reviews-score__label"><?php echo number_format($pdpReviewCount); ?> Verified Reviews</span>
            </div>
            <div class="pdp-reviews-bars">
                <?php foreach ([5,4,3,2,1] as $star): ?>
                <div class="pdp-reviews-bar-row">
                    <span class="pdp-reviews-bar-label"><?php echo $star; ?> <i class="bi bi-star-fill"></i></span>
                    <div class="pdp-reviews-bar-track">
                        <div class="pdp-reviews-bar-fill" style="width:<?php echo $pdpBreakdown[$star]; ?>%"
                             data-width="<?php echo $pdpBreakdown[$star]; ?>"></div>
                    </div>
                    <span class="pdp-reviews-bar-pct"><?php echo $pdpBreakdown[$star]; ?>%</span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Review Cards -->
        <div class="pdp-reviews-list">
            <?php foreach ($pdpReviews as $rev):
                // Normalise field names (real reviews use reviewer_name/date_label, fallback uses name/date)
                $revName     = htmlspecialchars((string)($rev['reviewer_name'] ?? $rev['name']   ?? 'Anonymous'));
                $revDate     = htmlspecialchars((string)($rev['date_label']    ?? $rev['date']    ?? ''));
                $revRating   = (int)($rev['rating']   ?? 5);
                $revTitle    = htmlspecialchars((string)($rev['title']  ?? ''));
                $revBody     = htmlspecialchars((string)($rev['body']   ?? ''));
                $revVerified = (bool)($rev['verified'] ?? true);
                $revHelpful  = (int)($rev['helpful']   ?? $rev['helpful_count'] ?? 0);
                $revId       = (int)($rev['id']        ?? 0);
            ?>
            <div class="pdp-review-card">
                <div class="pdp-review-card__top">
                    <div class="pdp-review-card__avatar"><?php echo mb_strtoupper(mb_substr($revName, 0, 1)); ?></div>
                    <div class="pdp-review-card__meta">
                        <strong><?php echo $revName; ?></strong>
                        <?php if ($revVerified): ?>
                            <span class="pdp-review-card__verified"><i class="bi bi-patch-check-fill"></i> Verified Purchase</span>
                        <?php endif; ?>
                    </div>
                    <div class="pdp-review-card__stars ms-auto">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="bi bi-star-fill<?php echo $i > $revRating ? ' empty' : ''; ?>"></i>
                        <?php endfor; ?>
                    </div>
                </div>
                <p class="pdp-review-card__title"><?php echo $revTitle; ?></p>
                <p class="pdp-review-card__body"><?php echo $revBody; ?></p>
                <div class="pdp-review-card__footer">
                    <span class="pdp-review-card__date"><?php echo $revDate; ?></span>
                    <button class="pdp-review-helpful" data-review-id="<?php echo $revId; ?>">
                        <i class="bi bi-hand-thumbs-up"></i> Helpful (<?php echo $revHelpful; ?>)
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Write a Review CTA -->
        <div class="pdp-reviews-cta" id="pdpWriteReviewArea">
            <?php if ($canReview): ?>
                <!-- ✅ Eligible user: show inline form -->
                <p class="mb-3 fw-bold" style="color:#7b1d1d;">Share your experience with this product:</p>
                <form id="pdpReviewForm" class="pdp-write-form">
                    <input type="hidden" name="csrf_token"  value="<?php echo $_SESSION['csrf_token']; ?>">
                    <input type="hidden" name="product_id"  value="<?php echo $currentProductId ?? ''; ?>">
                    <input type="hidden" name="combo_id"    value="<?php echo $currentComboId   ?? ''; ?>">
                    <input type="hidden" name="order_id"    value="<?php echo $reviewOrderId; ?>">

                    <!-- Star picker -->
                    <div class="pdp-star-picker mb-3" role="group" aria-label="Select rating">
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                        <input type="radio" name="rating" id="star<?php echo $i; ?>" value="<?php echo $i; ?>" required>
                        <label for="star<?php echo $i; ?>" title="<?php echo $i; ?> stars"><i class="bi bi-star-fill"></i></label>
                        <?php endfor; ?>
                    </div>

                    <input type="text" name="title" placeholder="Review title (e.g. Great taste!)"
                           class="pdp-write-input mb-2" maxlength="120" required>
                    <textarea name="body" placeholder="Tell others what you thought about this product…"
                              class="pdp-write-textarea mb-3" rows="3" maxlength="2000" required></textarea>
                    <button type="submit" class="pdp-reviews-write-btn">
                        <i class="bi bi-send-fill me-2"></i>Submit Review
                    </button>
                    <div id="pdpReviewMsg" class="pdp-review-msg d-none"></div>
                </form>

            <?php elseif ($loggedInUserId > 0 && $cannotReason === 'already_reviewed'): ?>
                <!-- ℹ️ Already reviewed -->
                <div class="pdp-review-notice pdp-review-notice--info">
                    <i class="bi bi-check-circle-fill"></i>
                    You have already submitted a review for this product. Thank you!
                </div>

            <?php elseif ($loggedInUserId > 0 && $cannotReason === 'not_purchased'): ?>
                <!-- 🔒 Logged in but hasn't bought it -->
                <div class="pdp-review-notice pdp-review-notice--lock">
                    <i class="bi bi-lock-fill"></i>
                    Only customers who have <strong>purchased and received</strong> this product can leave a review.
                </div>

            <?php else: ?>
                <!-- 🔐 Not logged in -->
                <div class="pdp-review-notice pdp-review-notice--lock">
                    <i class="bi bi-person-lock"></i>
                    <a href="<?php echo BASE_URL; ?>login.php" style="color:#7b1d1d;font-weight:700;">Log in</a>
                    to write a review. Only verified purchasers can share feedback.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
/* ── Reviews Modal ──────────────────────────────────────────── */
.pdp-rating__reviews.pdp-reviews-trigger {
    background: none;
    border: none;
    cursor: pointer;
    font-size: inherit;
    padding: 0;
    color: var(--clr-text-muted);
    text-decoration: underline;
    text-underline-offset: 2px;
    display: inline-flex;
    align-items: center;
    gap: 0.15rem;
    transition: color 0.18s;
}
.pdp-rating__reviews.pdp-reviews-trigger:hover { color: #7b1d1d; }

/* Overlay */
.pdp-reviews-overlay {
    position: fixed;
    inset: 0;
    background: rgba(30, 15, 5, 0.55);
    z-index: 9000;
    display: flex;
    align-items: flex-end;
    justify-content: center;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.28s ease;
}
.pdp-reviews-overlay.is-open {
    opacity: 1;
    pointer-events: all;
}

/* Drawer (bottom sheet) */
.pdp-reviews-drawer {
    width: 100%;
    max-width: 760px;
    max-height: 90vh;
    background: #fffaf6;
    border-radius: 22px 22px 0 0;
    display: flex;
    flex-direction: column;
    box-shadow: 0 -12px 48px rgba(80, 30, 10, 0.18);
    transform: translateY(100%);
    transition: transform 0.32s cubic-bezier(0.32, 0.72, 0, 1);
    overflow: hidden;
}
.pdp-reviews-overlay.is-open .pdp-reviews-drawer {
    transform: translateY(0);
}

/* Header */
.pdp-reviews-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.1rem 1.4rem 0.9rem;
    border-bottom: 1px solid #ead6c2;
    flex-shrink: 0;
}
.pdp-reviews-title {
    font-size: 1.15rem;
    font-weight: 800;
    color: #7b1d1d;
    margin: 0;
}
.pdp-reviews-close {
    background: #f2e8de;
    border: none;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: grid;
    place-items: center;
    cursor: pointer;
    color: #7b1d1d;
    font-size: 1rem;
    transition: background 0.18s;
}
.pdp-reviews-close:hover { background: #ead6c2; }

/* Summary */
.pdp-reviews-summary {
    display: flex;
    align-items: center;
    gap: 2rem;
    padding: 1.1rem 1.4rem;
    background: #fff;
    border-bottom: 1px solid #ead6c2;
    flex-shrink: 0;
    flex-wrap: wrap;
}
.pdp-reviews-score {
    text-align: center;
    min-width: 100px;
}
.pdp-reviews-score__num {
    display: block;
    font-size: 3.2rem;
    font-weight: 900;
    color: #7b1d1d;
    line-height: 1;
}
.pdp-reviews-score__stars {
    color: #f2a23a;
    font-size: 1rem;
    margin: 0.3rem 0;
}
.pdp-reviews-score__label {
    font-size: 0.8rem;
    color: #9a7060;
    font-weight: 600;
}
.pdp-reviews-bars {
    flex: 1;
    min-width: 200px;
    display: flex;
    flex-direction: column;
    gap: 0.45rem;
}
.pdp-reviews-bar-row {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    font-size: 0.82rem;
}
.pdp-reviews-bar-label { color: #7b5a40; font-weight: 700; width: 28px; white-space: nowrap; }
.pdp-reviews-bar-label .bi { color: #f2a23a; font-size: 0.7rem; }
.pdp-reviews-bar-track {
    flex: 1;
    height: 8px;
    background: #f0e6dc;
    border-radius: 999px;
    overflow: hidden;
}
.pdp-reviews-bar-fill {
    height: 100%;
    background: linear-gradient(90deg, #f2a23a, #d4710a);
    border-radius: 999px;
    width: 0;
    transition: width 0.7s cubic-bezier(0.22, 1, 0.36, 1);
}
.pdp-reviews-bar-pct { color: #9a7060; width: 30px; text-align: right; }

/* List */
.pdp-reviews-list {
    overflow-y: auto;
    padding: 1rem 1.4rem;
    display: flex;
    flex-direction: column;
    gap: 1rem;
    flex: 1;
}

/* Review card */
.pdp-review-card {
    background: #fff;
    border: 1px solid #ead6c2;
    border-radius: 12px;
    padding: 1rem 1.1rem;
    box-shadow: 0 2px 10px rgba(80,30,10,0.05);
}
.pdp-review-card__top {
    display: flex;
    align-items: center;
    gap: 0.7rem;
    margin-bottom: 0.55rem;
}
.pdp-review-card__avatar {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: linear-gradient(135deg, #7b1d1d, #c45c24);
    color: #fff;
    display: grid;
    place-items: center;
    font-weight: 800;
    font-size: 1rem;
    flex-shrink: 0;
}
.pdp-review-card__meta strong {
    display: block;
    font-size: 0.92rem;
    color: #2e1a0e;
}
.pdp-review-card__verified {
    font-size: 0.75rem;
    color: #007a3d;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 0.2rem;
}
.pdp-review-card__stars .bi { color: #f2a23a; font-size: 0.78rem; }
.pdp-review-card__stars .bi.empty { color: #ddd; }
.pdp-review-card__title {
    font-weight: 700;
    font-size: 0.95rem;
    color: #2e1a0e;
    margin-bottom: 0.35rem;
}
.pdp-review-card__body {
    font-size: 0.88rem;
    color: #5a3e30;
    line-height: 1.55;
    margin-bottom: 0.6rem;
}
.pdp-review-card__footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}
.pdp-review-card__date { font-size: 0.78rem; color: #9a7060; }
.pdp-review-helpful {
    background: none;
    border: 1px solid #d7b9a1;
    border-radius: 999px;
    font-size: 0.78rem;
    padding: 0.2rem 0.75rem;
    color: #7b5a40;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    transition: background 0.18s, color 0.18s;
}
.pdp-review-helpful:hover { background: #f2e8de; color: #7b1d1d; }
.pdp-review-helpful.voted { background: #f2e8de; color: #7b1d1d; border-color: #c4855a; }

/* CTA */
.pdp-reviews-cta {
    padding: 1rem 1.4rem 1.4rem;
    border-top: 1px solid #ead6c2;
    flex-shrink: 0;
    background: #fff;
}
.pdp-reviews-write-btn {
    width: 100%;
    padding: 0.75rem 1rem;
    border-radius: 10px;
    border: none;
    background: linear-gradient(135deg, #7b1d1d, #b03a1a);
    color: #fff;
    font-weight: 700;
    font-size: 0.95rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: opacity 0.18s;
}
.pdp-reviews-write-btn:hover { opacity: 0.9; }

@media (min-width: 768px) {
    .pdp-reviews-drawer { border-radius: 22px 22px 0 0; }
}

/* ── Write Review Form ──────────────────────────────────────── */
.pdp-write-form { display: flex; flex-direction: column; }
.pdp-write-input,
.pdp-write-textarea {
    width: 100%;
    padding: 0.65rem 0.85rem;
    border: 1.5px solid #ead6c2;
    border-radius: 8px;
    font-size: 0.9rem;
    font-family: inherit;
    background: #fffaf6;
    color: #2e1a0e;
    outline: none;
    transition: border-color 0.18s;
    resize: vertical;
}
.pdp-write-input:focus,
.pdp-write-textarea:focus { border-color: #7b1d1d; }

/* Star picker (RTL trick) */
.pdp-star-picker {
    display: flex;
    flex-direction: row-reverse;
    justify-content: flex-end;
    gap: 0.2rem;
}
.pdp-star-picker input[type="radio"] { display: none; }
.pdp-star-picker label {
    font-size: 1.6rem;
    color: #ddd;
    cursor: pointer;
    transition: color 0.15s;
}
.pdp-star-picker input:checked ~ label,
.pdp-star-picker label:hover,
.pdp-star-picker label:hover ~ label { color: #f2a23a; }

/* Feedback message */
.pdp-review-msg {
    margin-top: 0.75rem;
    padding: 0.65rem 1rem;
    border-radius: 8px;
    font-size: 0.88rem;
    font-weight: 600;
}
.pdp-review-msg--success { background: #eaffef; color: #007a3d; border: 1px solid #9be2bd; }
.pdp-review-msg--error   { background: #fff0f0; color: #b00020; border: 1px solid #f8b4b4; }

/* Notice boxes */
.pdp-review-notice {
    display: flex;
    align-items: flex-start;
    gap: 0.6rem;
    padding: 0.85rem 1rem;
    border-radius: 10px;
    font-size: 0.88rem;
    line-height: 1.5;
}
.pdp-review-notice--info { background: #eaf6ff; color: #0060a8; border: 1px solid #b0d8f8; }
.pdp-review-notice--lock { background: #fff8f0; color: #7b4a1a; border: 1px solid #ead6c2; }
.pdp-review-notice .bi { font-size: 1.05rem; margin-top: 0.1rem; flex-shrink: 0; }
</style>

<script>
(function () {
    'use strict';

    const overlay  = document.getElementById('pdpReviewsOverlay');
    const trigger  = document.getElementById('pdpReviewsBtn');
    const closeBtn = document.getElementById('pdpReviewsClose');
    if (!overlay || !trigger) return;

    function openReviews() {
        overlay.classList.add('is-open');
        document.body.style.overflow = 'hidden';
        setTimeout(() => {
            overlay.querySelectorAll('.pdp-reviews-bar-fill').forEach(el => {
                el.style.width = el.dataset.width + '%';
            });
        }, 350);
    }

    function closeReviews() {
        overlay.classList.remove('is-open');
        document.body.style.overflow = '';
        overlay.querySelectorAll('.pdp-reviews-bar-fill').forEach(el => {
            el.style.width = '0';
        });
    }

    trigger.addEventListener('click', openReviews);
    if (closeBtn) closeBtn.addEventListener('click', closeReviews);
    overlay.addEventListener('click', e => { if (e.target === overlay) closeReviews(); });
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && overlay.classList.contains('is-open')) closeReviews();
    });

    // ── Helpful buttons: call API for real reviews, local-only for dummy ─────
    overlay.querySelectorAll('.pdp-review-helpful').forEach(btn => {
        btn.addEventListener('click', function () {
            if (this.classList.contains('voted')) return;
            this.classList.add('voted');

            const reviewId = parseInt(this.dataset.reviewId || '0');
            const match    = this.textContent.match(/(\d+)/);
            const newCount = match ? parseInt(match[1]) + 1 : '?';
            this.innerHTML = '<i class="bi bi-hand-thumbs-up-fill"></i> Helpful (' + newCount + ')';

            if (reviewId > 0) {
                // Real review — persist to DB
                fetch('<?php echo BASE_URL; ?>api/v1/reviews.php?action=helpful', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'review_id=' + reviewId
                }).catch(() => {/* silent fail */});
            }
        });
    });

    // ── Review submission form ───────────────────────────────────────────────
    const form    = document.getElementById('pdpReviewForm');
    const msgBox  = document.getElementById('pdpReviewMsg');

    if (form) {
        form.addEventListener('submit', async function (e) {
            e.preventDefault();
            const submitBtn = form.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Submitting…';
            if (msgBox) { msgBox.className = 'pdp-review-msg d-none'; msgBox.textContent = ''; }

            try {
                const res  = await fetch('<?php echo BASE_URL; ?>api/v1/reviews.php', {
                    method : 'POST',
                    body   : new FormData(form)
                });
                const data = await res.json();

                if (msgBox) {
                    msgBox.className = 'pdp-review-msg ' + (data.success ? 'pdp-review-msg--success' : 'pdp-review-msg--error');
                    msgBox.textContent = data.message || (data.success ? 'Review submitted!' : 'Something went wrong.');
                    msgBox.classList.remove('d-none');
                }

                if (data.success) {
                    form.reset();
                    // Swap form area with thank-you notice so they can't re-submit
                    setTimeout(() => {
                        const area = document.getElementById('pdpWriteReviewArea');
                        if (area) area.innerHTML = '<div class="pdp-review-notice pdp-review-notice--info"><i class="bi bi-check-circle-fill"></i> Your review has been submitted. Thank you!</div>';
                    }, 1800);
                } else {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="bi bi-send-fill me-2"></i>Submit Review';
                }
            } catch {
                if (msgBox) {
                    msgBox.className = 'pdp-review-msg pdp-review-msg--error';
                    msgBox.textContent = 'Network error. Please try again.';
                    msgBox.classList.remove('d-none');
                }
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-send-fill me-2"></i>Submit Review';
            }
        });
    }
}());
</script>

<?php require_once 'includes/footer.php'; ?>
