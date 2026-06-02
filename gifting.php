<?php
/**
 * Sweets Website
 * =============================================================
 * File: gifting.php
 * Description: Gifting Collection page — fully DB-driven
 * =============================================================
 */

require_once 'config/config.php';
require_once SERVICES_PATH . '/ProductService.php';
require_once REPOS_PATH    . '/CategoryRepository.php';
require_once 'includes/product-card-template.php';

$productService = new ProductService();
$categoryRepo   = new CategoryRepository();

// Load Gift Box products from DB (consolidated)
$giftProducts = $productService->getGiftBoxes();

// Also pull karadant products for the specials section if gift-box is empty
if (empty($giftProducts)) {
    $giftProducts = $productService->getFeaturedProducts(8);
}

// Sort options (Default)
$currentSort = 'newest';

$seoContext = [
    'title' => 'Premium Gifting Collection & Gift Boxes | ' . SITE_NAME,
    'description' => 'Discover our handcrafted, premium gift boxes for every occasion. Authentic traditional sweets and namkeens packed with love and heritage.',
    'canonical' => BASE_URL . 'gifting.php',
    'type' => 'website'
];

require_once 'includes/header.php';
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/pages/karadant-page.css?v=<?php echo SITE_VERSION; ?>">
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/pages/gifting-page.css?v=<?php echo SITE_VERSION; ?>">

<style>
/* ── Gifting Page Premium Overrides ───────────────────────── */
.gift-hero-badge {
  display: inline-block;
  background: rgba(255,255,255,.15);
  backdrop-filter: blur(6px);
  border: 1px solid rgba(255,255,255,.3);
  border-radius: 30px;
  padding: .35rem 1.1rem;
  font-size: .78rem;
  font-weight: 700;
  letter-spacing: 1px;
  text-transform: uppercase;
  color: #fff;
  margin-bottom: 1rem;
}

/* Price filter pills */
.gift-price-pills { display: flex; gap: .4rem; flex-wrap: wrap; }
.gift-price-pill {
  padding: .3rem .85rem; border-radius: 20px;
  border: 1.5px solid #e8ddd2; font-size: .78rem; font-weight: 600;
  color: #6a4a2a; text-decoration: none; background: #fdf8f4;
  transition: all .2s;
}
.gift-price-pill:hover, .gift-price-pill.active {
  background: #7B1F1F; border-color: #7B1F1F; color: #fff;
}

/* Section heading */
.gift-section-title {
  font-size: clamp(1.6rem, 3vw, 2.4rem);
  font-weight: 800;
  color: #6C2C23;
  font-family: Georgia, 'Times New Roman', serif;
}
.gift-section-subtitle {
  color: #7a6050; font-size: 1rem; max-width: 520px;
}

/* Empty state */
.gift-empty {
  text-align: center; padding: 3rem 1rem;
  border: 2px dashed #e8ddd2; border-radius: 16px;
  background: #fffbf6;
}

/* Sort bar */
.gift-sort-bar {
  background: #fff;
  border-bottom: 1px solid #ede6de;
  padding: .75rem 0;
  position: sticky;
  top: 0;
  z-index: 900;
  box-shadow: 0 2px 12px rgba(0,0,0,.04);
}
.gift-sort-select {
  border: 1.5px solid #e8ddd2; border-radius: 8px;
  padding: .45rem .9rem; font-size: .82rem; font-weight: 600;
  color: #4a3020; outline: none; cursor: pointer;
  background: #fff; min-width: 180px;
}
.gift-count-label { font-size: .82rem; color: #7a6050; font-weight: 600; }
</style>

<main class="page-global-shipping">

  <!-- ── HERO ─────────────────────────────────────────────── -->
  <?php require_once 'sections/gifting-hero.php'; ?>

  <!-- ── SORT / FILTER BAR ─────────────────────────────────── -->
  <div class="gift-sort-bar">
    <div class="container">
      <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="gift-price-pills" id="dynamicPriceFilters">
          <span class="small fw-bold text-muted me-1">Price:</span>
          <button class="gift-price-pill active" data-min="0" data-max="999999">All</button>
          <button class="gift-price-pill" data-min="0" data-max="500">Under ₹500</button>
          <button class="gift-price-pill" data-min="500" data-max="1000">₹500–₹1000</button>
          <button class="gift-price-pill" data-min="1000" data-max="999999">Above ₹1000</button>
        </div>
        <div class="d-flex align-items-center gap-3">
          <div class="position-relative">
            <input type="text" id="giftSearch" class="form-control form-control-sm border-1" placeholder="Search gifts..." style="min-width: 200px; padding-left: 30px;">
            <i class="bi bi-search position-absolute text-muted" style="left: 10px; top: 50%; transform: translateY(-50%); font-size: 0.85rem;"></i>
          </div>
          <span class="gift-count-label" id="giftVisibleCount"><?php echo count($giftProducts); ?> products</span>
          <select class="gift-sort-select" id="dynamicSortSelect">
            <option value="newest">Newest First</option>
            <option value="price_low">Price: Low to High</option>
            <option value="price_high">Price: High to Low</option>
            <option value="name">Name (A-Z)</option>
          </select>
        </div>
      </div>
    </div>
  </div>

  <!-- ── CURATED GIFTING ───────────────────────────────────── -->
  <?php require_once 'sections/gifting-curated.php'; ?>

  <!-- ── FEATURED GIFT PRODUCTS (DB-Driven) ───────────────── -->
  <section class="py-5" style="background: #f8f4ef;" id="gift-boxes">
    <div class="container">
      <div class="d-flex flex-wrap justify-content-between align-items-end mb-5 gap-3">
        <div>
          <span class="gift-hero-badge" style="background: rgba(122,31,31,.1); color: #7a1f1f; border-color: rgba(122,31,31,.2);">
            <i class="bi bi-gift me-1"></i> Premium Selection
          </span>
          <h2 class="gift-section-title mt-2 mb-1">Featured Gifting Specials</h2>
          <p class="gift-section-subtitle">Handcrafted gift boxes for every celebration — made with love since 1907.</p>
        </div>
        <div class="d-none d-md-block">
          <!-- View All link removed as it leads to an empty category page -->
        </div>
      </div>

      <?php if (!empty($giftProducts)): ?>
        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-6 g-2 g-md-3" id="giftProductGrid">
          <?php foreach ($giftProducts as $product): 
            $price = (float)($product['sale_price'] ?? $product['base_price'] ?? 0);
          ?>
            <div class="col gift-card-item"
                 data-id="<?php echo (int)($product['id'] ?? 0); ?>"
                 data-price="<?php echo $price; ?>"
                 data-name="<?php echo strtolower(htmlspecialchars($product['name'] ?? '')); ?>">
              <?php renderProductCard($product); ?>
            </div>
          <?php endforeach; ?>
        </div>
        <div class="d-md-none">
          <!-- View All button removed as it leads to an empty category page -->
        </div>
      <?php else: ?>
        <div class="gift-empty">
          <i class="bi bi-gift" style="font-size: 3rem; color: #e8c9b0;"></i>
          <h3 class="fw-bold mt-3" style="color: #7B1F1F;">Gift Boxes Coming Soon</h3>
          <p class="text-muted">Our special gift collection is being curated. Check back soon!</p>
          <a href="karadant.php" class="btn btn-outline-danger rounded-pill px-4 mt-2 fw-bold">
            <i class="bi bi-arrow-left me-2"></i>Browse All Products
          </a>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <!-- ── GIFTING FEATURES BANNER ──────────────────────────── -->
  <?php require_once 'sections/gifting-features.php'; ?>

  <!-- ── SPECIAL COLLECTIONS ──────────────────────────────── -->
  <?php require_once 'sections/special-collections.php'; ?>

  <!-- ── BOX OF JOY ───────────────────────────────────────── -->
  <?php require_once 'sections/box-of-joy.php'; ?>

  <!-- ── CORPORATE & BULK GIFTING ─────────────────────────── -->
  <?php require_once 'sections/gifting-corporate.php'; ?>

  <!-- ── CTA SECTION ──────────────────────────────────────── -->
  <?php require_once 'sections/gifting-cta.php'; ?>

</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('giftSearch');
    const pricePills = document.querySelectorAll('.gift-price-pill');
    const sortSelect = document.getElementById('dynamicSortSelect');
    const grid = document.getElementById('giftProductGrid');
    const visibleCountLabel = document.getElementById('giftVisibleCount');
    
    if (!grid) return;
    
    let items = Array.from(grid.querySelectorAll('.gift-card-item'));
    
    let currentMinPrice = 0;
    let currentMaxPrice = 999999;
    let currentSearch = '';
    
    function applyFiltersAndSort() {
        let visibleCount = 0;
        
        items.forEach(item => {
            const price = parseFloat(item.getAttribute('data-price')) || 0;
            const name = item.getAttribute('data-name') || '';
            
            const matchesPrice = (price >= currentMinPrice && price <= currentMaxPrice);
            const matchesSearch = !currentSearch || name.includes(currentSearch);
            
            if (matchesPrice && matchesSearch) {
                item.style.display = '';
                visibleCount++;
            } else {
                item.style.display = 'none';
            }
        });
        
        if (visibleCountLabel) {
            visibleCountLabel.textContent = visibleCount + ' products';
        }
        
        // Sorting
        const sortVal = sortSelect ? sortSelect.value : 'newest';
        
        // We only sort the DOM elements that are visible to avoid unnecessary reflows, 
        // actually sorting all of them is fine too.
        items.sort((a, b) => {
            const priceA = parseFloat(a.getAttribute('data-price')) || 0;
            const priceB = parseFloat(b.getAttribute('data-price')) || 0;
            const nameA = a.getAttribute('data-name') || '';
            const nameB = b.getAttribute('data-name') || '';
            const idA = parseInt(a.getAttribute('data-id')) || 0;
            const idB = parseInt(b.getAttribute('data-id')) || 0;
            
            if (sortVal === 'price_low') return priceA - priceB;
            if (sortVal === 'price_high') return priceB - priceA;
            if (sortVal === 'name') return nameA.localeCompare(nameB);
            
            // default to newest (highest id first)
            return idB - idA;
        });
        
        // Re-append to grid in sorted order
        items.forEach(item => grid.appendChild(item));
    }
    
    // Event: Search
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            currentSearch = this.value.toLowerCase().trim();
            applyFiltersAndSort();
        });
    }
    
    // Event: Price Filter
    pricePills.forEach(pill => {
        pill.addEventListener('click', function(e) {
            e.preventDefault();
            pricePills.forEach(p => p.classList.remove('active'));
            this.classList.add('active');
            
            currentMinPrice = parseFloat(this.getAttribute('data-min')) || 0;
            currentMaxPrice = parseFloat(this.getAttribute('data-max')) || 999999;
            
            applyFiltersAndSort();
        });
    });
    
    // Event: Sort
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            applyFiltersAndSort();
        });
    }
    
    // Initial apply to ensure newest sort
    applyFiltersAndSort();
});
</script>

<?php require_once 'includes/footer.php'; ?>
