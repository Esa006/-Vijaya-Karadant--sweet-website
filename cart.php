<?php
declare(strict_types=1);
/**
 * Sweets Website
 * =============================================================
 * File: cart.php
 * Description: Shopping cart page for managing selected items
 * Author: Sweets Website Team
 * Version: 1.0.0
 * =============================================================
 */

require_once 'config/config.php';
require_once SERVICES_PATH . '/ProductService.php';
require_once SERVICES_PATH . '/CartService.php';

require_once SERVICES_PATH . '/ComboService.php';

$productService = new ProductService();
$comboService = new ComboService();
$cartService = new CartService();

// Handle Add to Cart / Buy Now actions
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

        // Check if it's a regular product
        $product = $productService->getProductBySlug($slug);
        if ($product) {
            $cartService->addItem($product, $quantity, $weight);
            $_SESSION['cart_success'] = htmlspecialchars($product['name']) . " added to cart!";
            $redirectTo = $action === 'buy_now' ? 'checkout.php' : 'shopping-cart.php';
            header('Location: ' . $redirectTo);
            exit;
        }

        // Check if it's a combo
        $combo = $comboService->getComboBySlug($slug);
        if ($combo) {
            $cartService->addCombo($combo, $quantity);
            $_SESSION['cart_success'] = htmlspecialchars($combo['name']) . " added to cart!";
            $redirectTo = $action === 'buy_now' ? 'checkout.php' : 'shopping-cart.php';
            header('Location: ' . $redirectTo);
            exit;
        }
    }
}

// Fetch product details based on slug
$slug = isset($_GET['slug']) ? (string)$_GET['slug'] : 'premium-vijaya-karadant';

// Try product first
$currentProduct = $productService->getProductBySlug($slug);

// If not a product, try combo
if (!$currentProduct) {
    $combo = $comboService->getComboBySlug($slug);
    if ($combo) {
        // Normalize combo to product structure for the template
        $currentProduct = [
            'id' => $combo['id'],
            'name' => $combo['name'],
            'slug' => $combo['slug'],
            'sale_price' => $combo['final_price'],
            'base_price' => $combo['original_price'],
            'image_path' => $combo['image'],
            'short_description' => $combo['description'],
            'detailed_description' => $combo['description'],
            'ingredients' => 'Combo Pack: Includes multiple items. See items below.',
            'category_slug' => 'combo',
            'is_combo' => true,
            'items' => $combo['items']
        ];
    }
}

// If product is not found in the DB, abort rendering and show 404/Empty State
if (!$currentProduct) {
    http_response_code(404);
    require_once 'includes/header.php';
    echo "<div class='container' style='text-align:center; padding: 100px 20px; min-height: 50vh;'>
            <i class='bi bi-basket' style='font-size: 4rem; color: #ccc;'></i>
            <h1 class='mt-4'>Product Not Found</h1>
            <p class='text-muted'>The product you are looking for does not exist or has been removed.</p>
            <a href='index.php' class='btn mt-3' style='background:#7b1d1d; color:#fff; font-weight:bold; border-radius:5px;'>Return to Home</a>
          </div>";
    require_once 'includes/footer.php';
    exit;
}

$salePrice = (float)($currentProduct['sale_price'] ?? $currentProduct['price'] ?? $currentProduct['base_price'] ?? 0);
$oldPrice = $currentProduct['base_price'] ?? $currentProduct['original_price'] ?? null;
$oldPrice = $oldPrice !== null ? (float)$oldPrice : null;

$productVariants = [];
if (empty($currentProduct['is_combo']) && !empty($currentProduct['id'])) {
    $rawVariants = $productService->getProductVariants((int)$currentProduct['id']);
    foreach ($rawVariants as $variant) {
        $stock = (int)($variant['stock'] ?? 0);
        $status = $stock <= 0 ? 'out_of_stock' : ($stock <= 10 ? 'low_stock' : 'in_stock');
        $productVariants[] = [
            'id' => (int)($variant['id'] ?? 0),
            'weight' => (string)($variant['weight'] ?? ''),
            'label' => (string)($variant['label'] ?? $variant['weight'] ?? ''),
            'price' => (float)($variant['price'] ?? $salePrice),
            'stock' => $stock,
            'status' => $status,
            'sku' => (string)($currentProduct['sku'] ?? '') . '-' . strtoupper(str_replace(' ', '', (string)($variant['weight'] ?? 'VAR'))),
            'restock_eta' => $stock <= 0 ? date('M d', strtotime('+5 days')) : null,
            'preorder_enabled' => $stock <= 0
        ];
    }
}

if (empty($productVariants) && empty($currentProduct['is_combo'])) {
    $fallbackStock = (int)($currentProduct['stock_quantity'] ?? 0);
    $fallbackStatus = $fallbackStock <= 0 ? 'out_of_stock' : ($fallbackStock <= 10 ? 'low_stock' : 'in_stock');
    $productVariants = [[
        'id' => 0,
        'weight' => '500g',
        'label' => '500g',
        'price' => $salePrice,
        'stock' => $fallbackStock,
        'status' => $fallbackStatus,
        'sku' => (string)($currentProduct['sku'] ?? ''),
        'restock_eta' => $fallbackStock <= 0 ? date('M d', strtotime('+5 days')) : null,
        'preorder_enabled' => $fallbackStock <= 0
    ]];
}

$requestedWeight = (string)($_GET['weight'] ?? ($_POST['weight'] ?? ''));
$selectedVariant = $productVariants[0] ?? null;
if (!empty($requestedWeight)) {
    foreach ($productVariants as $variant) {
        if (strcasecmp((string)$variant['weight'], $requestedWeight) === 0 || strcasecmp((string)$variant['label'], $requestedWeight) === 0) {
            $selectedVariant = $variant;
            break;
        }
    }
}

$selectedVariant = $selectedVariant ?: [
    'id' => 0,
    'weight' => '500g',
    'label' => '500g',
    'price' => $salePrice,
    'stock' => (int)($currentProduct['stock_quantity'] ?? 0),
    'status' => ((int)($currentProduct['stock_quantity'] ?? 0) > 0 ? 'in_stock' : 'out_of_stock'),
    'sku' => (string)($currentProduct['sku'] ?? ''),
    'restock_eta' => null,
    'preorder_enabled' => false
];

$salePrice = (float)($selectedVariant['price'] ?? $salePrice);
$discountPercent = ($oldPrice && $oldPrice > $salePrice) ? (int)round((($oldPrice - $salePrice) / $oldPrice) * 100) : 0;
$savingsAmount = ($oldPrice && $oldPrice > $salePrice) ? ($oldPrice - $salePrice) : 0.0;

// Gather all images for the product gallery
$sliderImages = $productService->getSliderImages($slug);
$allImages = [];

$isValidImagePath = static function (string $path): bool {
    $path = trim($path);
    if ($path === '') {
        return false;
    }

    if (preg_match('/^https?:\/\//i', $path)) {
        return true;
    }

    $absolutePath = ROOT_PATH . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, ltrim($path, '/\\'));
    return is_file($absolutePath);
};

$primaryImage = trim((string)($currentProduct['image_path'] ?? ''));
if ($isValidImagePath($primaryImage)) {
    $allImages[] = $primaryImage;
}

foreach ($sliderImages as $img) {
    $imagePath = trim((string)($img['image_path'] ?? ''));
    if ($isValidImagePath($imagePath)) {
        $allImages[] = $imagePath;
    }
}

$allImages = array_values(array_unique($allImages));
if (empty($allImages)) {
    $allImages[] = 'assets/images/placeholder.png';
}

// Phase 1: Build a server-provided variant payload for JS atomic state
$jsVariants = array_map(function($v) use ($currentProduct, $oldPrice) {
    return [
        'id' => $v['id'],
        'label' => $v['label'],
        'weight' => $v['weight'],
        'sku' => $v['sku'],
        'price' => $v['price'],
        'compare_at' => $oldPrice, // Using shared old price for simplicity, or v['compare_at'] if it exists
        'stock' => $v['stock'],
        'status' => $v['status'],
        'image' => $currentProduct['image_path'] ?? null, // Can be overridden by variant images if implemented
        'restock_eta' => $v['restock_eta'],
        'preorder_enabled' => $v['preorder_enabled']
    ];
}, $productVariants);
?>
<?php require_once 'includes/header.php'; ?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/pages/cart.css?v=<?php echo SITE_VERSION; ?>">

<main class="p-cart-page py-5">
    <div class="c-custom-hero-container mb-5 px-3">
        <!-- New Custom Hero Section -->
        <div class="ch-breadcrumb">
            <span>Home</span>
            <span class="sep">/</span>
            <span>Traditional Sweets</span>
            <span class="sep">/</span>
            <span class="current"><?php echo htmlspecialchars($currentProduct['name']); ?></span>
        </div>

        <section class="ch-product-section" id="product-details">
            <div class="ch-product-grid">
                <div class="ch-thumbs" id="thumbs">
                    <?php foreach ($allImages as $index => $imgSrc): ?>
                    <button class="ch-thumb <?php echo $index === 0 ? 'active' : ''; ?>" type="button" aria-label="Product image <?php echo $index + 1; ?>">
                        <span class="ch-thumb-art">
                            <img src="<?php echo htmlspecialchars($imgSrc); ?>" alt="Thumb" style="width:100%; height:100%; object-fit:cover; border-radius: 6px;">
                        </span>
                    </button>
                    <?php
endforeach; ?>
                </div>

                <div class="ch-image-wrap">
                    <div class="ch-hero-card">
                        <!-- Desktop Nav Buttons -->
                        <button class="ch-hero-nav prev" id="prevBtn" type="button" aria-label="Previous image">‹</button>
                        <button class="ch-hero-nav next" id="nextBtn" type="button" aria-label="Next image">›</button>

                        <img id="mainHeroImg" src="<?php echo htmlspecialchars($allImages[0]); ?>" alt="<?php echo htmlspecialchars($currentProduct['name']); ?>">
                    </div>
                </div>

                <div class="ch-details">
                    <h1 class="ch-title" id="productTitle"><?php echo htmlspecialchars($currentProduct['name']); ?></h1>

                    <div class="ch-rating-row">
                        <div class="ch-stars">
                            <span>★★★★★</span>
                            <span class="value"><?php echo number_format($currentProduct['rating'] ?? 4.5, 1); ?></span>
                        </div>
                        <div class="ch-reviews">|&nbsp; <?php echo number_format($currentProduct['reviews_count'] ?? 1248); ?> Verified Reviews</div>
                    </div>

                    <div class="ch-price-row">
                        <div class="ch-price-current" id="variantCurrentPrice"><span class="currency">₹</span><?php echo number_format($salePrice); ?></div>
                        <?php if ($oldPrice && $oldPrice > $salePrice): ?>
                            <div class="ch-price-old" id="variantOldPrice"><?php echo number_format($oldPrice); ?></div>
                            <span id="variantDiscountBadge" class="badge ms-2" style="background:#e8f7ed;color:#166534;border:1px solid #b9e7c8;font-weight:700;"> <?php echo $discountPercent; ?>% OFF </span>
                        <?php
endif; ?>
                    </div>
                    <?php if ($savingsAmount > 0): ?>
                        <div class="small fw-semibold" id="variantSavings" style="color:#166534;">You save ₹<?php echo number_format($savingsAmount, 0); ?></div>
                    <?php endif; ?>
                    <div class="small text-muted mt-1">SKU: <span id="variantSku"><?php echo htmlspecialchars((string)($selectedVariant['sku'] ?? ($currentProduct['sku'] ?? 'NA'))); ?></span></div>

                    <p class="ch-desc">
                        <?php echo htmlspecialchars($currentProduct['short_description'] ?? 'Our signature karadant is a nutrient-rich traditional sweet made with organic jaggery, premium nuts, and pure edible gum. A legacy of health and taste passed down through generations.'); ?>
                    </p>

                    <?php if (!empty($currentProduct['is_combo']) && !empty($currentProduct['items'])): ?>
                        <div class="ch-combo-items mb-4 p-3 rounded-3" style="background: rgba(123, 29, 29, 0.05); border: 1px dashed #7b1d1d;">
                            <h5 class="fw-bold mb-3" style="color: #7b1d1d; font-size: 1rem;">Included in this Combo:</h5>
                            <div class="row g-2">
                                <?php foreach ($currentProduct['items'] as $item): ?>
                                    <div class="col-12">
                                        <div class="d-flex align-items-center gap-3 p-2 rounded-2 bg-white shadow-sm">
                                            <img src="<?php echo BASE_URL . ($item['image'] ?: 'assets/images/placeholders/product-placeholder.png'); ?>" 
                                                 alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                                 style="width: 45px; height: 45px; object-fit: cover; border-radius: 6px;">
                                            <div class="flex-grow-1">
                                                <div class="fw-bold" style="font-size: 0.9rem; color: #4a3728;"><?php echo htmlspecialchars($item['name']); ?></div>
                                                <div class="text-muted" style="font-size: 0.75rem;">Quantity: <?php echo $item['quantity']; ?></div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (empty($currentProduct['is_combo'])): ?>
                        <div class="ch-field-label">Select Weight</div>
                        <div class="ch-weight-group" id="weights" role="group" aria-label="Choose weight variant">
                            <?php foreach ($productVariants as $variant):
                                $variantStatus = (string)($variant['status'] ?? 'out_of_stock');
                                $stockCount = (int)($variant['stock'] ?? 0);
                                $variantDisabled = $variantStatus === 'out_of_stock' || $stockCount <= 0;
                                $isSelectedVariant = ((int)$variant['id'] === (int)($selectedVariant['id'] ?? 0))
                                    || ((string)$variant['weight'] === (string)($selectedVariant['weight'] ?? ''));
                            ?>
                                <button
                                    class="ch-weight-btn <?php echo $isSelectedVariant ? 'active' : ''; ?>"
                                    type="button"
                                    data-variant-id="<?php echo (int)$variant['id']; ?>"
                                    data-weight="<?php echo htmlspecialchars((string)$variant['weight']); ?>"
                                    data-label="<?php echo htmlspecialchars((string)$variant['label']); ?>"
                                    data-price="<?php echo (float)$variant['price']; ?>"
                                    data-stock="<?php echo $stockCount; ?>"
                                    data-status="<?php echo htmlspecialchars($variantDisabled ? 'out_of_stock' : $variantStatus); ?>"
                                    data-sku="<?php echo htmlspecialchars((string)$variant['sku']); ?>"
                                    data-restock="<?php echo htmlspecialchars((string)($variant['restock_eta'] ?? '')); ?>"
                                    data-preorder="<?php echo !empty($variant['preorder_enabled']) ? '1' : '0'; ?>"
                                    <?php echo $variantDisabled ? 'disabled aria-disabled="true"' : ''; ?>>
                                    <?php echo htmlspecialchars((string)$variant['weight']); ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php
                        $stockQty = (int)($selectedVariant['stock'] ?? ($currentProduct['stock_quantity'] ?? 0));
                        $isComboOutOfStock = isset($currentProduct['stock_status']) && $currentProduct['stock_status'] === 'out_of_stock';
                        $isOutOfStock = $isComboOutOfStock || (empty($currentProduct['is_combo']) && ((string)($selectedVariant['status'] ?? '') === 'out_of_stock' || $stockQty <= 0));
                    ?>

                    <div id="variantRecoveryCard" class="ch-out-of-stock-box mt-4 p-4 rounded-3" style="background-color: #f8f9fa; border: 1px solid #dee2e6; text-align: center;<?php echo $isOutOfStock ? '' : ' display:none;'; ?>">
                            <h4 class="text-danger fw-bold mb-2"><i class="bi bi-exclamation-circle"></i> <span id="variantUnavailableTitle"><?php echo htmlspecialchars((string)($selectedVariant['label'] ?? 'Selected variant')); ?></span> currently unavailable</h4>
                            <div class="small text-muted mb-3">Estimated restock: <span id="variantRestockDate"><?php echo htmlspecialchars((string)($selectedVariant['restock_eta'] ?? 'TBD')); ?></span></div>
                            <p class="text-muted mb-4">This item is in high demand and is temporarily unavailable. Leave your email below and we'll notify you the moment it's back!</p>
                            
                            <form id="notify-form-cart" class="d-flex gap-2 justify-content-center" onsubmit="submitCartNotify(event)">
                                <input type="email" id="cart-notify-email" class="form-control" placeholder="Enter your email address" required style="max-width: 300px;">
                                <button type="submit" class="btn fw-bold px-4" style="background-color: #7b1d1d; color: white;">Notify Me</button>
                            </form>
                            <div class="d-flex flex-wrap justify-content-center gap-2 mt-3">
                                <a id="whatsappAlertLink" href="https://wa.me/?text=<?php echo rawurlencode('Notify me when ' . ($currentProduct['name'] ?? 'this product') . ' is back in stock'); ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline-success">
                                    <i class="bi bi-whatsapp"></i> WhatsApp Alert
                                </a>
                                <button id="preorderBtn" type="button" class="btn btn-sm btn-outline-dark" <?php echo empty($selectedVariant['preorder_enabled']) ? 'style="display:none;"' : ''; ?>>Preorder</button>
                            </div>
                            <div id="notify-cart-msg" class="mt-3 fw-bold" style="display:none;"></div>
                        </div>

                        <?php
                        // Fetch 2 in-stock alternatives from the same category
                        $alternatives = [];
                        if (!empty($currentProduct['category_slug'])) {
                            $catProds = $productService->getProductsByCategory($currentProduct['category_slug']);
                            $alternatives = array_filter($catProds, function($p) use ($currentProduct) {
                                return ((string)$p['slug'] !== (string)$currentProduct['slug']) && ((int)($p['stock_quantity'] ?? 0) > 0);
                            });
                            $alternatives = array_slice($alternatives, 0, 2);
                        }
                        if (!empty($alternatives)):
                        ?>
                        <div class="mt-4 p-3 rounded-3" style="background-color: #fff9f9; border: 1px dashed #e6b8b8; text-align: left;">
                            <h6 class="fw-bold mb-3" style="color: #7b1d1d; font-size: 0.95rem;">
                                <i class="bi bi-bag-check-fill me-1"></i> Available Alternatives in Stock:
                            </h6>
                            <div class="d-flex flex-column gap-2">
                                <?php foreach($alternatives as $alt): ?>
                                    <a href="<?php echo BASE_URL; ?>cart.php?slug=<?php echo htmlspecialchars($alt['slug']); ?>" 
                                       class="d-flex align-items-center text-decoration-none text-dark p-2 rounded-2 bg-white shadow-sm" style="border: 1px solid #f1e0e0; transition: all 0.2s;">
                                        <img src="<?php echo BASE_URL . ($alt['image_path'] ?: 'assets/images/placeholders/product-placeholder.png'); ?>" 
                                             alt="<?php echo htmlspecialchars($alt['name']); ?>" 
                                             style="width:50px; height:50px; object-fit:cover; border-radius:6px;" class="me-3">
                                        <div class="flex-grow-1">
                                            <div class="fw-bold" style="font-size:0.9rem; color:#4a3728;"><?php echo htmlspecialchars($alt['name']); ?></div>
                                            <?php
                                                $altPriceRaw = $alt['sale_price'] ?? $alt['base_price'] ?? 0;
                                                $altPrice = is_numeric($altPriceRaw) ? (float)$altPriceRaw : 0.0;
                                            ?>
                                            <div class="text-danger fw-bold" style="font-size:0.8rem;">₹<?php echo number_format($altPrice, 0); ?></div>
                                        </div>
                                        <div class="ms-auto"><button class="btn btn-sm text-white px-3" style="background-color:#7b1d1d; font-size:0.75rem;">View</button></div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <script>
                        function submitCartNotify(e) {
                            e.preventDefault();
                            const email = document.getElementById('cart-notify-email').value;
                            const btn = e.target.querySelector('button');
                            const msgDiv = document.getElementById('notify-cart-msg');
                            
                            if (!email) return;
                            
                            btn.disabled = true;
                            btn.innerHTML = 'Submitting...';
                            
                            const formData = new FormData();
                            formData.append('email', email);
                            formData.append('product_id', '<?php echo htmlspecialchars((string)($currentProduct['id'] ?? '')); ?>');
                            formData.append('product_type', '<?php echo !empty($currentProduct['is_combo']) ? 'combo' : 'product'; ?>');
                            
                            fetch('<?php echo BASE_URL; ?>api/v1/notify_stock.php', {
                                method: 'POST',
                                body: formData
                            })
                            .then(res => res.json())
                            .then(data => {
                                msgDiv.style.display = 'block';
                                if (data.status === 'success') {
                                    msgDiv.className = 'mt-3 fw-bold text-success';
                                    msgDiv.innerHTML = '<i class="bi bi-check-circle"></i> ' + data.message;
                                    e.target.reset();
                                } else {
                                    msgDiv.className = 'mt-3 fw-bold text-danger';
                                    msgDiv.innerHTML = '<i class="bi bi-x-circle"></i> ' + data.message;
                                    btn.disabled = false;
                                    btn.innerHTML = 'Notify Me';
                                }
                            })
                            .catch(err => {
                                msgDiv.style.display = 'block';
                                msgDiv.className = 'mt-3 fw-bold text-danger';
                                msgDiv.innerHTML = '<i class="bi bi-exclamation-triangle"></i> Network error. Please try again.';
                                btn.disabled = false;
                                btn.innerHTML = 'Notify Me';
                            });
                        }
                        </script>
                    <form method="POST" id="add-to-cart-form" class="m-0" style="<?php echo $isOutOfStock ? 'display:none;' : ''; ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                        <input type="hidden" name="slug" value="<?php echo htmlspecialchars($currentProduct['slug']); ?>">
                        <input type="hidden" name="quantity" id="form-qty" value="1">
                        <input type="hidden" name="weight" id="form-weight" value="<?php echo !empty($currentProduct['is_combo']) ? 'Bundle' : htmlspecialchars((string)($selectedVariant['weight'] ?? '500g')); ?>">
                        <input type="hidden" name="variant_id" id="form-variant-id" value="<?php echo (int)($selectedVariant['id'] ?? 0); ?>">

                        <div class="ch-purchase-meta">
                            <div class="ch-qty" aria-label="Quantity selector">
                                <button type="button" id="minusBtn" aria-label="Decrease quantity">−</button>
                                <span class="ch-qty-value" id="qtyValue">1</span>
                                <button type="button" id="plusBtn" aria-label="Increase quantity">+</button>
                            </div>
                            <a href="javascript:void(0)" class="ch-policy-note" id="cartPolicyTrigger" aria-label="No return and exchange policy">
                                <i class="bi bi-arrow-repeat"></i>
                                <span>No Return | 1 Day Exchange</span>
                            </a>
                            <div class="ch-delivery">Free delivery over ₹999</div>
                        </div>

                        <div class="ch-actions">
                            <button class="ch-btn ch-btn-primary" type="submit" name="action" value="add_to_cart">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path fill="currentColor" d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2Zm10 0c-1.1 0-1.99.9-1.99 2S15.9 22 17 22s2-.9 2-2-.9-2-2-2ZM7.17 14h9.95c.75 0 1.41-.41 1.75-1.03l3.58-6.49A1 1 0 0 0 21.58 5H6.21L5.27 3H2v2h1.99l3.6 7.59-1.35 2.45A1.99 1.99 0 0 0 8 18h12v-2H8l1.17-2Z"/>
                                </svg>
                                Add to Cart
                            </button>

                            <button class="ch-btn ch-btn-outline" type="submit" name="action" value="buy_now">Buy Now</button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>
    
    <!-- Related Products Section -->
    <?php
    $relatedProducts = [];
    if (!empty($currentProduct['category_slug'])) {
        $categoryProducts = $productService->getProductsByCategory($currentProduct['category_slug']);
        // Filter out the current product and limit to 4
        $relatedProducts = array_filter($categoryProducts, function($p) use ($currentProduct) {
            return (string)$p['slug'] !== (string)$currentProduct['slug'];
        });
        $relatedProducts = array_slice($relatedProducts, 0, 4);
    }
    
    // Fallback if no related products in same category
    if (empty($relatedProducts)) {
        $relatedProducts = array_slice($productService->getFeaturedProducts(4), 0, 4);
    }
    ?>
    <section class="c-related-products py-5 border-top">
        <div class="container">
            <div class="text-start mb-5">
                <h2 class="u-ff-heading fw-bold" style="font-size: 38px; color: #7b1d1d;">Related Products</h2>
            </div>
            <div class="row g-4">
                <?php foreach ($relatedProducts as $rp): 
                    $rpName = htmlspecialchars($rp['name']);
                    $rpSlug = htmlspecialchars($rp['slug']);
                    $rpImage = htmlspecialchars($rp['image_path'] ?? 'assets/images/placeholders/product-placeholder.png');
                    $rpPrice = (float)($rp['sale_price'] ?? $rp['base_price'] ?? 0);
                    $rpOldPrice = (float)($rp['base_price'] ?? 0);
                    $rpBadge = ($rp['sale_price'] && $rpOldPrice > $rp['sale_price']) ? round((($rpOldPrice - $rp['sale_price']) / $rpOldPrice) * 100) . '% Off' : 'Best seller';
                    $rpBadgeClass = ($rp['sale_price'] && $rpOldPrice > $rp['sale_price']) ? 'c-related-badge--discount' : 'c-related-badge--bestseller';
                ?>
                <div class="col-6 col-md-3">
                    <div class="c-related-card border-0 rounded-4 overflow-hidden position-relative h-100 shadow-sm">
                        <span class="c-related-badge <?php echo $rpBadgeClass; ?>"><?php echo $rpBadge; ?></span>
                        <a href="<?php echo BASE_URL; ?>cart.php?slug=<?php echo $rpSlug; ?>#product-details" class="c-related-card__img-wrap d-block">
                            <img src="<?php echo BASE_URL . $rpImage; ?>" alt="<?php echo $rpName; ?>" class="img-fluid" style="height: 200px; width: 100%; object-fit: cover;">
                        </a>
                        <div class="p-3 text-center">
                            <h5 class="fw-bold mb-3 fs-6"><a href="<?php echo BASE_URL; ?>cart.php?slug=<?php echo $rpSlug; ?>#product-details" class="text-decoration-none text-dark"><?php echo $rpName; ?></a></h5>
                            <button class="btn c-related-card__btn w-100 py-2 rounded-3 fw-bold" onclick="window.location.href='<?php echo BASE_URL; ?>cart.php?slug=<?php echo $rpSlug; ?>#product-details'">Shop Now</button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
        
  <!-- Best Sellers Section -->
<?php

$bestSellers = $productService->getFeaturedProducts();

require_once 'sections/bestsellers.php';

?>
    
    <!-- Shop our Range Section -->
    
    <?php require_once 'sections/shop-range.php'; ?>

   

    <!-- Product Information Section (Detailed Layout) -->
    <section class="c-product-detailed-info py-5 border-top border-bottom" >
        <div class="container py-lg-4">
            <div class="row g-5">
                <!-- Left Column: Accordions (7/12) -->
                <div class="col-lg-7">
                    <div class="text-start mb-4">
                        <h2 class="u-ff-heading fw-bold" style="font-size: 42px; color: #7b1d1d;">Product Information</h2>
                    </div>
                    
                    <div class="c-info-accordion" id="detailedProductAccordion">
                        <!-- Accordion 1: Deep Description -->
                        <div class="c-accordion-item mb-3">
                            <div class="c-accordion-header p-3 rounded-2 d-flex align-items-center justify-content-between cursor-pointer" 
                                 data-bs-toggle="collapse" data-bs-target="#detailedDescCollapse">
                                <h5 class="mb-0 fw-bold fs-6">deep product description</h5>
                                <div class="c-accordion-icon"><i class="bi bi-plus-circle-fill"></i></div>
                            </div>
                            <div id="detailedDescCollapse" class="collapse show" data-bs-parent="#detailedProductAccordion">
                                <div class="c-accordion-body p-4 pt-0">
                                    <p class="text-muted mb-0"><?php echo htmlspecialchars($currentProduct['detailed_description'] ?? 'Our signature Karadant is a nutrient-rich traditional sweet made with organic jaggery, premium nuts, and pure edible gum. A legacy of health and taste passed down through generations.'); ?></p>
                                </div>
                            </div>
                        </div>

                        <!-- Accordion 2: Ingredients -->
                        <div class="c-accordion-item mb-3">
                            <div class="c-accordion-header p-3 rounded-2 d-flex align-items-center justify-content-between cursor-pointer collapsed" 
                                 data-bs-toggle="collapse" data-bs-target="#detailedIngrCollapse">
                                <h5 class="mb-0 fw-bold fs-6">Ingredients & Allergens</h5>
                                <div class="c-accordion-icon"><i class="bi bi-plus-circle-fill"></i></div>
                            </div>
                            <div id="detailedIngrCollapse" class="collapse" data-bs-parent="#detailedProductAccordion">
                                <div class="c-accordion-body p-4 pt-0">
                                    <p class="text-muted mb-0"><?php echo htmlspecialchars($currentProduct['ingredients'] ?? 'Contains: Organic Jaggery, Cashews, Almonds, Edible Gum (Antu), Pure Cow Ghee, Dry Dates, Poppy Seeds, Cardamom.'); ?></p>
                                </div>
                            </div>
                        </div>

                        <!-- Accordion 3: Nutrition -->
                        <div class="c-accordion-item mb-3">
                            <div class="c-accordion-header p-3 rounded-2 d-flex align-items-center justify-content-between cursor-pointer collapsed" 
                                 data-bs-toggle="collapse" data-bs-target="#detailedNutrCollapse">
                                <h5 class="mb-0 fw-bold fs-6">Nutrition Facts (Per 100g)</h5>
                                <div class="c-accordion-icon"><i class="bi bi-plus-circle-fill"></i></div>
                            </div>
                            <div id="detailedNutrCollapse" class="collapse" data-bs-parent="#detailedProductAccordion">
                                <div class="c-accordion-body p-4 pt-0">
                                    <p class="text-muted mb-0"><?php echo htmlspecialchars($currentProduct['nutrition'] ?? 'Energy: 450 kcal, Protein: 8g, Fat: 22g, Carbohydrates: 55g, Natural Sugars: 40g.'); ?></p>
                                </div>
                            </div>
                        </div>

                        <!-- Accordion 4: Storage -->
                        <div class="c-accordion-item mb-3">
                            <div class="c-accordion-header p-3 rounded-2 d-flex align-items-center justify-content-between cursor-pointer collapsed" 
                                 data-bs-toggle="collapse" data-bs-target="#detailedStorCollapse">
                                <h5 class="mb-0 fw-bold fs-6">Storage & Handling Instructions</h5>
                                <div class="c-accordion-icon"><i class="bi bi-plus-circle-fill"></i></div>
                            </div>
                            <div id="detailedStorCollapse" class="collapse" data-bs-parent="#detailedProductAccordion">
                                <div class="c-accordion-body p-4 pt-0">
                                    <p class="text-muted mb-0"><?php echo htmlspecialchars($currentProduct['storage'] ?? 'Store in a cool, dry place. Once opened, keep in an airtight container for lasting freshness up to 60 days.'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Features & Subscribe (5/12) -->
                <div class="col-lg-5 mt-5 mt-lg-0">
                    <div class="text-start mb-4 d-none d-lg-block">
                        <h2 class="u-ff-heading fw-bold" style="font-size: 38px; color: #7b1d1d;">More Features</h2>
                    </div>

                    <!-- Highlight Features -->
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

                    <!-- Subscribe Box -->
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

    
    
    <!-- Trust Feature Strip -->
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const mainHeroImg = document.getElementById('mainHeroImg');
    const thumbs = [...document.querySelectorAll('.ch-thumb')];
    
    if (mainHeroImg && thumbs.length > 0) {
        const imageSources = thumbs.map(t => t.querySelector('img').src);
        let sceneIndex = 0;

        function setScene(index){
            sceneIndex = (index + imageSources.length) % imageSources.length;
            
            mainHeroImg.style.opacity = 0.5;
            setTimeout(() => {
                mainHeroImg.src = imageSources[sceneIndex];
                mainHeroImg.style.opacity = 1;
            }, 150);

            thumbs.forEach((thumb, i) => thumb.classList.toggle('active', i === sceneIndex));
        }

        thumbs.forEach((thumb, index) => {
          thumb.addEventListener('click', () => setScene(index));
        });

        document.getElementById('prevBtn')?.addEventListener('click', () => setScene(sceneIndex - 1));
        document.getElementById('nextBtn')?.addEventListener('click', () => setScene(sceneIndex + 1));
    }

    // --- Phase 1: Variant State Model & Atomic Sync ---
    const variantData = <?php echo json_encode($jsVariants); ?>;
    let selectedVariant = null;

    function formatINR(amount) {
        return '₹' + Math.round(amount).toLocaleString('en-IN');
    }

    function renderVariantState(variant) {
        if (!variant) return;
        selectedVariant = variant;

        // 1. Update Hidden Form Fields
        const formWeight = document.getElementById('form-weight');
        const formVariantId = document.getElementById('form-variant-id');
        if (formWeight) formWeight.value = variant.weight;
        if (formVariantId) formVariantId.value = String(variant.id || 0);

        // 2. Update Pricing Display
        const currentPriceEl = document.getElementById('variantCurrentPrice');
        const oldPriceEl = document.getElementById('variantOldPrice');
        const discountBadgeEl = document.getElementById('variantDiscountBadge');
        const savingsEl = document.getElementById('variantSavings');
        
        if (currentPriceEl) {
            currentPriceEl.innerHTML = `<span class="currency">₹</span>${Math.round(variant.price).toLocaleString('en-IN')}`;
        }

        const hasDiscount = variant.compare_at && variant.compare_at > variant.price;
        if (oldPriceEl) {
            oldPriceEl.style.display = hasDiscount ? '' : 'none';
            oldPriceEl.textContent = hasDiscount ? Math.round(variant.compare_at).toLocaleString('en-IN') : '';
        }
        if (discountBadgeEl) {
            if (hasDiscount) {
                const pct = Math.round(((variant.compare_at - variant.price) / variant.compare_at) * 100);
                discountBadgeEl.style.display = '';
                discountBadgeEl.textContent = `${pct}% OFF`;
            } else {
                discountBadgeEl.style.display = 'none';
            }
        }
        if (savingsEl) {
            if (hasDiscount) {
                const savings = variant.compare_at - variant.price;
                savingsEl.style.display = '';
                savingsEl.textContent = `You save ${formatINR(savings)}`;
            } else {
                savingsEl.style.display = 'none';
            }
        }

        // 3. Update Meta (SKU, Stock)
        const skuEl = document.getElementById('variantSku');
        if (skuEl) skuEl.textContent = variant.sku || 'NA';

        const isOut = variant.status === 'out_of_stock' || variant.stock <= 0;
        const recoveryCard = document.getElementById('variantRecoveryCard');
        const purchaseForm = document.getElementById('add-to-cart-form');
        
        if (recoveryCard) recoveryCard.style.display = isOut ? '' : 'none';
        if (purchaseForm) purchaseForm.style.display = isOut ? 'none' : '';

        if (isOut) {
            const recoveryTitle = document.getElementById('variantUnavailableTitle');
            const recoveryDate = document.getElementById('variantRestockDate');
            const preorderBtn = document.getElementById('preorderBtn');
            if (recoveryTitle) recoveryTitle.textContent = `${variant.label} currently unavailable`;
            if (recoveryDate) recoveryDate.textContent = variant.restock_eta || 'TBD';
            if (preorderBtn) preorderBtn.style.display = variant.preorder_enabled ? '' : 'none';
        } else {
            // Update quantity constraints based on stock
            const qtyValueDisplay = document.getElementById('qtyValue');
            const formQty = document.getElementById('form-qty');
            let currentQty = parseInt(qtyValueDisplay?.textContent || '1', 10);
            if (currentQty > variant.stock) {
                currentQty = variant.stock;
                if (qtyValueDisplay) qtyValueDisplay.textContent = String(currentQty);
                if (formQty) formQty.value = String(currentQty);
            }
        }

        // 4. Update Button Visual States (active class)
        const variantButtons = document.querySelectorAll('.ch-weight-btn');
        variantButtons.forEach(btn => {
            const btnWeight = btn.dataset.weight;
            btn.classList.toggle('active', btnWeight === String(variant.weight));
            
            // Phase 2 Hint: You could also update the stock chips here
        });
    }

    // Initialize variant selection listeners
    document.querySelectorAll('.ch-weight-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const variantWeight = this.dataset.weight;
            const variant = variantData.find(v => String(v.weight) === variantWeight);
            renderVariantState(variant);
        });
    });

    // Initial render
    const initialWeight = document.querySelector('.ch-weight-btn.active')?.dataset.weight || variantData[0]?.weight;
    const initialVariant = variantData.find(v => String(v.weight) === initialWeight) || variantData[0];
    renderVariantState(initialVariant);

    let customQty = 1;
    const qtyValueDisplay = document.getElementById('qtyValue');
    const formQty = document.getElementById('form-qty');

    const maxAllowedLimit = 10; // Retail cap like Flipkart
    
    document.getElementById('minusBtn')?.addEventListener('click', () => {
      customQty = Math.max(1, customQty - 1);
      if (qtyValueDisplay) qtyValueDisplay.textContent = customQty;
      if (formQty) formQty.value = customQty;
    });

    document.getElementById('plusBtn')?.addEventListener('click', () => {
        // Find the actual ceiling for this item
        const variantStock = selectedVariant && selectedVariant.stock > 0 ? selectedVariant.stock : maxAllowedLimit;
        const actualLimit = Math.min(maxAllowedLimit, variantStock);
        
        if (customQty >= actualLimit) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Limit Reached',
                    text: `We're sorry! Only ${actualLimit} unit(s) allowed for this item per order.`,
                    confirmButtonColor: '#7b1d1d'
                });
            } else {
                alert(`We're sorry! Only ${actualLimit} unit(s) allowed for this item per order.`);
            }
            return;
        }
        
        customQty++;
        if (qtyValueDisplay) qtyValueDisplay.textContent = customQty;
        if (formQty) formQty.value = customQty;
    });

    // --- Policy Popup Logic ---
    document.getElementById('cartPolicyTrigger')?.addEventListener('click', function(e) {
        e.preventDefault();
        const template = document.getElementById('template-policy');
        if (!template) {
            console.error('Template not found: template-policy');
            return;
        }

        const tempDiv = document.createElement('div');
        tempDiv.appendChild(template.content.cloneNode(true));
        const htmlContent = tempDiv.innerHTML;

        if (typeof Swal === 'undefined') {
            console.error('SweetAlert2 (Swal) is not defined');
            return;
        }

        Swal.fire({
            title: 'Store Policy',
            html: htmlContent,
            showCloseButton: true,
            showConfirmButton: false,
            width: 'min(95%, 600px)',
            color: '#4a3728',
            background: '#fdf8f2',
            customClass: {
                container: 'c-swal-container',
                popup: 'c-swal-popup--premium',
                title: 'c-swal-title--heading',
                closeButton: 'c-swal-close-btn'
            },
            showClass: {
                popup: 'animate__animated animate__fadeInDown animate__faster'
            },
            hideClass: {
                popup: 'animate__animated animate__fadeOutUp animate__faster'
            }
        });
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
