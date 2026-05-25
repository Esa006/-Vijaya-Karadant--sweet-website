<?php
/**
 * Sweets Website
 * =============================================================
 * File: category-products.php
 * Description: Dynamic category product listing page
 *              Accepts: ?slug=karadant  OR  ?category=Karadant
 * =============================================================
 */

require_once 'config/config.php';
require_once SERVICES_PATH . '/ProductService.php';
require_once REPOS_PATH . '/CategoryRepository.php';

$categoryRepo   = new CategoryRepository();
$productService = new ProductService();

// Resolve category from URL parameters
$slug        = trim(strtolower($_GET['slug'] ?? ''));
$catName     = trim($_GET['category'] ?? '');
$currentSort = $_GET['sort'] ?? 'newest';
$minPrice    = $_GET['min_price'] ?? null;
$maxPrice    = $_GET['max_price'] ?? null;
$currentSearch = trim((string)($_GET['search'] ?? ''));

// If slug is not given, try to derive it from category name
if (empty($slug) && !empty($catName)) {
    $slug = strtolower(str_replace([' ', '_'], '-', $catName));
}

// Redirect gifting-related slugs to the specialized premium gifting page
if ($slug === 'gift-box' || $slug === 'gifting') {
    header('Location: gifting.php');
    exit;
}

// Redirect combos-related slug to the specialized combos page
if ($slug === 'combos') {
    header('Location: combos.php');
    exit;
}

// Load category metadata
$category = null;
if (!empty($slug)) {
    $category = $categoryRepo->getBySlug($slug);
}

// 404 fallback if category doesn't exist
if (!$category) {
    $category = [
        'id'          => 0,
        'name'        => $currentSearch !== '' && $slug === '' ? 'Search Results' : ucfirst($slug ?: 'All Products'),
        'slug'        => $slug,
        'description' => ''
    ];
}

// Load all root categories for the pill nav
$allCategories = $categoryRepo->getRootCategories();

// Fetch products
$filters = [
    'category'  => $slug,
    'min_price' => $minPrice,
    'max_price' => $maxPrice,
    'search'    => $currentSearch
];

$products = $productService->getFilteredProducts($filters, $currentSort);

// Sort labels
$sortLabels = [
    'newest'     => 'Newest Arrivals',
    'price_low'  => 'Price: Low to High',
    'price_high' => 'Price: High to Low',
    'name'       => 'Name (A-Z)',
];

$pageTitle = htmlspecialchars($category['name']) . ' Products – ' . SITE_NAME;

$searchQueryString = $currentSearch !== '' ? '&search=' . urlencode($currentSearch) : '';

require_once 'includes/header.php';
?>

<!-- Page-specific styles -->
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/pages/karadant-page.css?v=<?php echo SITE_VERSION; ?>">
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/sections/karadant-art.css?v=<?php echo SITE_VERSION; ?>">

<style>
/* ── Category Products Page ─────────────────────────────────── */
.cp-hero {
    background: linear-gradient(135deg, rgba(123, 31, 31, 0.8) 0%, rgba(184, 92, 0, 0.8) 100%)<?php echo !empty($category['hero_image']) ? ", url('" . BASE_URL . $category['hero_image'] . "')" : ""; ?>;
    background-size: cover;
    background-position: center;
    background-blend-mode: overlay;
    padding: clamp(3rem, 6vw, 5rem) 1rem clamp(2rem, 4vw, 3.5rem);
    text-align: center;
    color: #fff;
}
.cp-hero__badge {
    display: inline-block;
    background: rgba(255,255,255,0.15);
    backdrop-filter: blur(6px);
    border: 1px solid rgba(255,255,255,0.3);
    border-radius: 30px;
    padding: 0.35rem 1.1rem;
    font-size: 0.78rem;
    font-weight: 700;
    letter-spacing: 1px;
    text-transform: uppercase;
    margin-bottom: 1rem;
}
.cp-hero__title {
    font-size: clamp(2rem, 5vw, 3.2rem);
    font-weight: 800;
    margin-bottom: 0.75rem;
}
.cp-hero__desc {
    max-width: 600px;
    margin: 0 auto;
    opacity: 0.85;
    font-size: 1rem;
}
.cp-breadcrumb {
    font-size: 0.8rem;
    opacity: 0.7;
    margin-bottom: 0.5rem;
}
.cp-breadcrumb a { color: #fff; text-decoration: none; }
.cp-breadcrumb a:hover { text-decoration: underline; }

/* ── Filter Bar ─────────────────────────────────────────────── */
.cp-filter-bar {
    background: #fff;
    border-bottom: 1px solid #ede6de;
    padding: 1rem 0;
    position: sticky;
    top: 0;
    z-index: 900;
    box-shadow: 0 2px 12px rgba(0,0,0,0.04);
}
.cp-category-pills {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
    align-items: center;
}
.cp-pill {
    display: inline-block;
    padding: 0.45rem 1.1rem;
    border-radius: 30px;
    border: 1.5px solid #e8ddd2;
    font-size: 0.82rem;
    font-weight: 600;
    color: #7a523a;
    text-decoration: none;
    transition: all 0.2s;
    white-space: nowrap;
    background: #fff;
}
.cp-pill:hover, .cp-pill.active {
    background: #7B1F1F;
    border-color: #7B1F1F;
    color: #fff;
}
.cp-sort-select {
    border: 1.5px solid #e8ddd2;
    border-radius: 8px;
    padding: 0.45rem 0.9rem;
    font-size: 0.82rem;
    font-weight: 600;
    color: #4a3020;
    outline: none;
    cursor: pointer;
    background: #fff;
    min-width: 180px;
}
.cp-sort-select:focus { border-color: #7B1F1F; }

/* ── Price filter badges ─────────────────────────────────────── */
.cp-price-pills {
    display: flex;
    gap: 0.4rem;
    flex-wrap: wrap;
    align-items: center;
}
.cp-price-pill {
    display: inline-block;
    padding: 0.3rem 0.85rem;
    border-radius: 20px;
    border: 1.5px solid #e8ddd2;
    font-size: 0.76rem;
    font-weight: 600;
    color: #6a4a2a;
    text-decoration: none;
    transition: all 0.2s;
    white-space: nowrap;
    background: #fdf8f4;
}
.cp-price-pill:hover, .cp-price-pill.active {
    background: #7B1F1F;
    border-color: #7B1F1F;
    color: #fff;
}

/* ── Product Grid ──────────────────────────────────────────── */
.cp-section {
    padding: clamp(2rem, 4vw, 4rem) 0 4rem;
    background: #f8f4ef;
    min-height: 50vh;
}
.cp-count-label { font-size: 0.82rem; color: #7a6050; font-weight: 600; }

/* ── Search bar ─────────────────────────────────────────────── */
.cp-search-wrap { position: relative; max-width: 260px; }
.cp-search-wrap i {
    position: absolute; left: 12px; top: 50%;
    transform: translateY(-50%); color: #7B1F1F;
    font-size: 0.9rem; pointer-events: none;
}
.cp-search-input {
    width: 100%;
    padding: 0.5rem 1rem 0.5rem 2.5rem;
    border: 1.5px solid #e8ddd2;
    border-radius: 30px;
    font-size: 0.83rem;
    font-weight: 500;
    outline: none;
    transition: border-color 0.2s;
    background: #fff;
}
.cp-search-input:focus { border-color: #7B1F1F; }
</style>

<main class="page-karadant">

    <!-- ── HERO ───────────────────────────────────────────────── -->
    <div class="cp-hero">
        <p class="cp-breadcrumb">
            <a href="<?php echo BASE_URL; ?>index.php">Home</a> &rsaquo;
            <a href="<?php echo BASE_URL; ?>index.php#shop">Shop</a> &rsaquo;
            <?php echo htmlspecialchars($category['name']); ?>
        </p>
        <div class="cp-hero__badge">
            <i class="bi bi-grid-3x3-gap me-1"></i> Category
        </div>
        <h1 class="cp-hero__title"><?php echo htmlspecialchars($category['name']); ?></h1>
        <?php if (!empty($category['description'])): ?>
            <p class="cp-hero__desc"><?php echo htmlspecialchars($category['description']); ?></p>
        <?php else: ?>
            <p class="cp-hero__desc">Discover our handcrafted selection of <?php echo htmlspecialchars($category['name']); ?> — made with love and tradition.</p>
        <?php endif; ?>
    </div>

    <!-- ── STICKY FILTER BAR ─────────────────────────────────── -->
    <div class="cp-filter-bar">
        <div class="container">
            <div class="d-flex flex-wrap align-items-center gap-3">

                <!-- Category Pills -->
                <div class="cp-category-pills">
                    <a href="category-products.php?sort=<?php echo urlencode($currentSort); ?><?php echo $searchQueryString; ?>" class="cp-pill <?php echo empty($slug) ? 'active' : ''; ?>">All Sweets</a>
                    <?php foreach ($allCategories as $cat): 
                        $catSlug = $cat['slug'];
                        $href = "category-products.php?slug=" . urlencode($catSlug) . "&sort=" . urlencode($currentSort) . $searchQueryString;
                        if ($catSlug === 'combos') {
                            $href = "combos.php";
                        } elseif ($catSlug === 'gifting' || $catSlug === 'gift-box') {
                            $href = "gifting.php";
                        }
                    ?>
                        <a href="<?php echo $href; ?>"
                           class="cp-pill <?php echo ($cat['slug'] === $slug) ? 'active' : ''; ?>">
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </a>
                    <?php endforeach; ?>
                </div>

                <!-- Sort + Search -->
                <div class="d-flex align-items-center gap-2 ms-md-auto flex-wrap">
                    <div class="cp-search-wrap">
                        <i class="bi bi-search"></i>
                        <input type="text" id="cpSearchInput" class="cp-search-input" placeholder="Search products…" value="<?php echo htmlspecialchars($currentSearch, ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                    <select class="cp-sort-select" onchange="location.href=this.value">
                        <?php foreach ($sortLabels as $val => $label): ?>
                            <option value="category-products.php?slug=<?php echo urlencode($slug); ?>&sort=<?php echo $val; ?><?php if ($minPrice) echo '&min_price=' . urlencode($minPrice); ?><?php if ($maxPrice) echo '&max_price=' . urlencode($maxPrice); ?><?php echo $searchQueryString; ?>"
                                <?php echo ($currentSort === $val) ? 'selected' : ''; ?>>
                                <?php echo $label; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Price filter row -->
            <div class="mt-2 d-flex align-items-center gap-2 flex-wrap">
                <span class="small fw-bold text-muted">Price:</span>
                <div class="cp-price-pills">
                    <a href="category-products.php?slug=<?php echo urlencode($slug); ?>&sort=<?php echo $currentSort; ?><?php echo $searchQueryString; ?>"
                       class="cp-price-pill <?php echo (!$minPrice && !$maxPrice) ? 'active' : ''; ?>">All</a>
                    <a href="category-products.php?slug=<?php echo urlencode($slug); ?>&sort=<?php echo $currentSort; ?>&max_price=500<?php echo $searchQueryString; ?>"
                       class="cp-price-pill <?php echo ($maxPrice == 500 && !$minPrice) ? 'active' : ''; ?>">Under ₹500</a>
                    <a href="category-products.php?slug=<?php echo urlencode($slug); ?>&sort=<?php echo $currentSort; ?>&min_price=500&max_price=1000<?php echo $searchQueryString; ?>"
                       class="cp-price-pill <?php echo ($minPrice == 500 && $maxPrice == 1000) ? 'active' : ''; ?>">₹500–₹1000</a>
                    <a href="category-products.php?slug=<?php echo urlencode($slug); ?>&sort=<?php echo $currentSort; ?>&min_price=1000<?php echo $searchQueryString; ?>"
                       class="cp-price-pill <?php echo ($minPrice == 1000 && !$maxPrice) ? 'active' : ''; ?>">Above ₹1000</a>
                </div>
            </div>
        </div>
    </div>

    <!-- ── PRODUCT GRID ──────────────────────────────────────── -->
    <section class="cp-section">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <p class="cp-count-label mb-0">
                    <span id="cpVisibleCount"><?php echo count($products); ?></span> product<?php echo count($products) !== 1 ? 's' : ''; ?> found
                </p>
            </div>

            <?php if (!empty($products)): ?>
                <?php require_once 'includes/product-card-template.php'; ?>
                <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-5 g-2 g-md-3" id="cpProductGrid">
                    <?php foreach ($products as $product): ?>
                        <div class="col cp-product-item"
                             data-name="<?php echo strtolower(htmlspecialchars($product['name'])); ?>"
                             data-search="<?php echo strtolower(htmlspecialchars(trim(($product['name'] ?? '') . ' ' . ($product['slug'] ?? '') . ' ' . ($product['short_description'] ?? '') . ' ' . ($product['category_name'] ?? '') . ' ' . ($product['category_slug'] ?? '')), ENT_QUOTES, 'UTF-8')); ?>">
                            <?php renderProductCard($product); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <img src="<?php echo BASE_URL; ?>assets/images/placeholders/product-placeholder.png"
                         alt="No products" style="width: 120px; opacity: 0.4; margin-bottom: 1.5rem;">
                    <h3 class="fw-bold" style="color: #7B1F1F;">No Products Found</h3>
                    <p class="text-muted">We couldn't find any products in "<?php echo htmlspecialchars($category['name']); ?>".<br>Try a different filter or check back later.</p>
                    <a href="index.php" class="btn btn-outline-danger rounded-pill px-4 mt-2 fw-bold">
                        <i class="bi bi-arrow-left me-2"></i>Browse All
                    </a>
                </div>
            <?php endif; ?>

            <div id="cpEmptySearch" class="text-center py-5 d-none">
                <i class="bi bi-search" style="font-size: 3rem; color: #ccc;"></i>
                <h4 class="mt-3 fw-bold" style="color: #7B1F1F;">No results for this search</h4>
                <p class="text-muted">Try a different keyword.</p>
            </div>
        </div>
    </section>

</main>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const searchInput  = document.getElementById('cpSearchInput');
    const productItems = document.querySelectorAll('.cp-product-item');
    const emptySearch  = document.getElementById('cpEmptySearch');
    const countEl      = document.getElementById('cpVisibleCount');

    if (!searchInput) return;

    searchInput.addEventListener('input', () => {
        const q = searchInput.value.toLowerCase().trim();
        let visible = 0;

        productItems.forEach(item => {
            const searchableText = item.getAttribute('data-search') || item.getAttribute('data-name') || '';
            const match = !q || searchableText.includes(q);
            item.style.display = match ? '' : 'none';
            if (match) visible++;
        });

        if (countEl) countEl.innerText = visible;
        if (emptySearch) {
            emptySearch.classList.toggle('d-none', visible > 0 || !q);
        }
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
