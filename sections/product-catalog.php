<?php
/**
 * Sweets Website - Product Catalog Section (DB-Driven)
 * =============================================================
 * Fully dynamic: loads all root categories + their products from DB.
 * Fallback static data included for offline/dev use.
 * =============================================================
 */

require_once SERVICES_PATH . '/ProductService.php';
require_once REPOS_PATH   . '/CategoryRepository.php';
require_once 'includes/product-card-template.php';

$productService = new ProductService();
$categoryRepo   = new CategoryRepository();

// --- URL params ---
$isKaradantPage  = strpos($_SERVER['PHP_SELF'], 'karadant.php') !== false;
$activeCategory  = strtolower(trim($_GET['category'] ?? ($isKaradantPage ? 'all' : 'all')));
$currentSort     = $_GET['sort'] ?? 'newest';
$minPrice        = $_GET['min_price'] ?? null;
$maxPrice        = $_GET['max_price'] ?? null;

// --- Load all root categories from DB ---
$rootCategories = [];
try {
    $rootCategories = $categoryRepo->getRootCategories();
} catch (Exception $e) {
    error_log('[ProductCatalog] Category load failed: ' . $e->getMessage());
}

// Fallback if DB is empty
if (empty($rootCategories)) {
    $rootCategories = [
        ['id' => 0, 'name' => 'Karadant', 'slug' => 'karadant', 'product_count' => 0],
        ['id' => 0, 'name' => 'Laddu',    'slug' => 'laddu',    'product_count' => 0],
        ['id' => 0, 'name' => 'Namkeen',  'slug' => 'namkeen',  'product_count' => 0],
        ['id' => 0, 'name' => 'Gifting',  'slug' => 'gifting',  'product_count' => 0],
    ];
}

// --- Custom Sort & Filter for UI ---
// 1. Remove "Gift Box" from the nav pills (per user request)
// 2. Move "Gifting" to the end
$filteredCategories = [];
$giftingCategory = null;

foreach ($rootCategories as $cat) {
    if ($cat['slug'] === 'gift-box') continue; // Remove Gift Box
    if ($cat['slug'] === 'gifting') {
        $giftingCategory = $cat;
        continue;
    }
    $filteredCategories[] = $cat;
}

if ($giftingCategory) {
    $filteredCategories[] = $giftingCategory; // Add Gifting at the end
}
$rootCategories = $filteredCategories;

// --- Load products grouped by category ---
$groupedProducts = [];   // ['slug' => ['category' => [...], 'products' => [...]]]
$allProducts     = [];

foreach ($rootCategories as $cat) {
    $catSlug = $cat['slug'];
    try {
        $catProducts = $productService->getProductsByCategory($catSlug);
    } catch (Exception $e) {
        error_log('[ProductCatalog] Products for ' . $catSlug . ' failed: ' . $e->getMessage());
        $catProducts = [];
    }

    // Apply price filters if set
    if ($minPrice !== null || $maxPrice !== null) {
        $catProducts = array_filter($catProducts, function ($p) use ($minPrice, $maxPrice) {
            $price = (float)($p['sale_price'] ?? $p['base_price'] ?? 0);
            if ($minPrice !== null && $price < (float)$minPrice) return false;
            if ($maxPrice !== null && $price > (float)$maxPrice) return false;
            return true;
        });
        $catProducts = array_values($catProducts);
    }

    // Sort
    usort($catProducts, function ($a, $b) use ($currentSort) {
        $ap = (float)($a['sale_price'] ?? $a['base_price'] ?? 0);
        $bp = (float)($b['sale_price'] ?? $b['base_price'] ?? 0);
        switch ($currentSort) {
            case 'price_low':  return $ap <=> $bp;
            case 'price_high': return $bp <=> $ap;
            case 'name':       return strcmp((string)$a['name'], (string)$b['name']);
            default:           return (int)($b['id'] ?? 0) <=> (int)($a['id'] ?? 0);
        }
    });

    foreach ($catProducts as &$p) {
        $p['ui_category_slug'] = $catSlug;
    }
    unset($p);

    $groupedProducts[$catSlug] = ['category' => $cat, 'products' => $catProducts];
    $allProducts = array_merge($allProducts, $catProducts);
}

// Sort all products the same way for the "All" view
usort($allProducts, function ($a, $b) use ($currentSort) {
    $ap = (float)($a['sale_price'] ?? $a['base_price'] ?? 0);
    $bp = (float)($b['sale_price'] ?? $b['base_price'] ?? 0);
    switch ($currentSort) {
        case 'price_low':  return $ap <=> $bp;
        case 'price_high': return $bp <=> $ap;
        case 'name':       return strcmp((string)$a['name'], (string)$b['name']);
        default:           return (int)($b['id'] ?? 0) <=> (int)($a['id'] ?? 0);
    }
});



// Calculate initial count
$initialCount = 0;
if ($activeCategory === 'all') {
    $initialCount = count($allProducts);
} else {
    foreach ($allProducts as $p) {
        if (($p['ui_category_slug'] ?? '') === $activeCategory) {
            $initialCount++;
        }
    }
}

$sortLabels = [
    'newest'     => 'Newest Arrivals',
    'price_low'  => 'Price: Low to High',
    'price_high' => 'Price: High to Low',
    'name'       => 'Name (A-Z)',
];
?>

<section class="c-product-catalog" id="product-catalog">
<div class="container">

  <!-- ── Filter Bar (Karadant page only) ─────────────────────── -->
  <?php if (basename($_SERVER['PHP_SELF']) === 'karadant.php'): ?>
  <div class="c-catalog-filters">
    <div class="c-catalog-filters__top">
      <div class="c-category-pills">
        <a href="?category=all&sort=<?php echo $currentSort; ?>#product-catalog"
           data-scroll-target="product-catalog"
           class="c-pill js-category-pill <?php echo $activeCategory === 'all' ? 'active' : ''; ?>">
          All Sweets
        </a>
        <?php foreach ($rootCategories as $rc):
          // "Gifting" category links to the dedicated gifting page
          if (strtolower($rc['slug']) === 'gifting'):
        ?>
          <a href="gifting.php" class="c-pill js-category-pill">
            <?php echo htmlspecialchars($rc['name']); ?>
          </a>
        <?php else: ?>
          <a href="?category=<?php echo urlencode($rc['slug']); ?>&sort=<?php echo $currentSort; ?>#product-catalog"
             data-scroll-target="product-catalog"
             class="c-pill js-category-pill <?php echo ($activeCategory === $rc['slug']) ? 'active' : ''; ?>">
            <?php echo htmlspecialchars($rc['name']); ?>
          </a>
        <?php endif; endforeach; ?>
      </div>
    </div>

    <div class="c-catalog-filters__bottom">
      <div class="c-filter-dropdowns">
        <div class="c-dropdown">
          <button class="c-dropdown__btn js-dropdown-toggle">Price Range <i class="bi bi-chevron-down"></i></button>
          <div class="c-dropdown__menu js-dropdown-menu">
            <a href="?category=<?php echo $activeCategory; ?>&sort=<?php echo $currentSort; ?>&max_price=500#product-catalog" class="c-dropdown__item">Under ₹500</a>
            <a href="?category=<?php echo $activeCategory; ?>&sort=<?php echo $currentSort; ?>&min_price=500&max_price=1000#product-catalog" class="c-dropdown__item">₹500 – ₹1000</a>
            <a href="?category=<?php echo $activeCategory; ?>&sort=<?php echo $currentSort; ?>&min_price=1000#product-catalog" class="c-dropdown__item">Above ₹1000</a>
            <a href="?category=<?php echo $activeCategory; ?>&sort=<?php echo $currentSort; ?>#product-catalog" class="c-dropdown__item">Reset</a>
          </div>
        </div>
        <div class="c-dropdown">
          <button class="c-dropdown__btn js-dropdown-toggle">Sort By <i class="bi bi-chevron-down"></i></button>
          <div class="c-dropdown__menu js-dropdown-menu">
            <?php foreach ($sortLabels as $val => $lbl): ?>
              <a href="?category=<?php echo $activeCategory; ?>&sort=<?php echo $val; ?><?php echo $minPrice ? '&min_price=' . urlencode($minPrice) : ''; ?><?php echo $maxPrice ? '&max_price=' . urlencode($maxPrice) : ''; ?>#product-catalog"
                 class="c-dropdown__item <?php echo $currentSort === $val ? 'active' : ''; ?>">
                <?php echo $lbl; ?>
              </a>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
      <div class="c-sort-selector">
        <span>SHOWING:</span>
        <strong id="catalogProductCount"><?php echo $initialCount; ?></strong>
        <span>products</span>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- ── Section Heading ─────────────────────────────────────── -->
  <h2 class="c-catalog-title mb-5">Our Collection</h2>

  <!-- ── All Products Grid (Hidden logic for JS filtering) ─────────────────── -->
  <?php if (!empty($allProducts)): ?>
    <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-6 g-2 g-md-3" id="catalogProductGrid">
      <?php foreach ($allProducts as $product): 
        $productCat = $product['ui_category_slug'] ?? 'all';
        $isHidden = ($activeCategory !== 'all' && $activeCategory !== $productCat);
      ?>
        <div class="col js-catalog-item"
             data-category="<?php echo htmlspecialchars($productCat); ?>"
             style="<?php echo $isHidden ? 'display: none;' : ''; ?>">
          <?php renderProductCard($product); ?>
        </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <div class="text-center py-5">
      <i class="bi bi-box-seam" style="font-size:3rem;color:#ccc"></i>
      <h3 class="fw-bold mt-3" style="color:#7B1F1F">No products available</h3>
      <p class="text-muted">Check back later for new arrivals.</p>
    </div>
  <?php endif; ?>

</div>
</section>

<style>
/* ── Collection Block Styles ─────────────────────────────────── */
.c-collection-block__icon {
  width: 48px; height: 48px; border-radius: 10px; object-fit: cover;
  border: 1.5px solid #e8ddd2;
}
.c-collection-block__title {
  font-size: clamp(1.1rem, 2vw, 1.4rem);
  font-weight: 800; color: #3b1a0a;
}
.c-collection-block__count {
  font-size: 0.82rem; color: #8a6050; font-weight: 600;
}
.c-collection-block__view-all {
  display: inline-flex; align-items: center;
  font-size: 0.85rem; font-weight: 700;
  color: #7B1F1F; text-decoration: none;
  border: 1.5px solid #e8ddd2; border-radius: 30px;
  padding: 0.4rem 1rem;
  transition: all 0.2s;
}
.c-collection-block__view-all:hover {
  background: #7B1F1F; color: #fff; border-color: #7B1F1F;
}
.c-collection-divider {
  border-color: #ede6de; opacity: 1;
}
#catalogProductCount { color: #7B1F1F; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const storageKey = 'catalogScrollTarget';
    const offset = 90;

    document.querySelectorAll('[data-scroll-target="product-catalog"]').forEach(function (link) {
        link.addEventListener('click', function () {
            sessionStorage.setItem(storageKey, 'product-catalog');
        });
    });

    const targetId = sessionStorage.getItem(storageKey);
    if (targetId) {
        const target = document.getElementById(targetId);
        if (target) {
            const top = target.getBoundingClientRect().top + window.pageYOffset - offset;
            window.scrollTo({ top: Math.max(top, 0), behavior: 'auto' });
        }
        sessionStorage.removeItem(storageKey);
    }

    // Deduplicate section title if included multiple times
    const titleNodes = document.querySelectorAll('.c-product-catalog .c-catalog-title');
    if (titleNodes.length > 1) {
        titleNodes.forEach(function (node, index) { if (index > 0) node.remove(); });
    }
});
</script>
