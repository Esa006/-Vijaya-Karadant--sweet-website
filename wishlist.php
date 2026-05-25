<?php
/**
 * Sweets Website
 * =============================================================
 * File: wishlist.php
 * Description: Premium Wishlist Page
 * =============================================================
 */

require_once 'config/config.php';
require_once 'includes/header.php';
?>

<!-- Wishlist Styles -->
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/pages/wishlist.css?v=<?php echo SITE_VERSION; ?>">

<main class="p-wishlist py-5">
    <div class="container">
        
        <!-- Breadcrumb -->
        <nav class="c-breadcrumb mb-4" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">My Wishlist</li>
            </ol>
        </nav>

        <!-- Page Header -->
        <header class="p-wishlist__header mb-5">
            <h1 class="p-wishlist__title">My Wishlist</h1>
            <p class="p-wishlist__subtitle">Carry your favorites wherever you go</p>
        </header>

        <!-- Wishlist Content -->
        <div id="wishlistContainer" class="row g-4">
            <!-- Loading State -->
            <div class="col-12 text-center py-5" id="wishlistLoading">
                <div class="spinner-border text-maroon" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>

        <!-- Empty State (hidden by default) -->
        <div id="wishlistEmpty" class="text-center py-5 d-none">
            <div class="mb-4">
                <i class="bi bi-heart text-muted display-1"></i>
            </div>
            <h3 class="fw-bold">Your wishlist is empty</h3>
            <p class="text-muted mb-4">Explore our sweets and add your favorites to your wishlist!</p>
            <a href="karadant.php" class="btn btn-maroon btn-lg px-5 rounded-pill">Explore Sweets</a>
        </div>

    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('wishlistContainer');
    const emptyState = document.getElementById('wishlistEmpty');
    const loading = document.getElementById('wishlistLoading');
    
    const renderWishlist = () => {
        const wishlist = JSON.parse(localStorage.getItem('wishlist')) || [];
        
        loading.classList.add('d-none');
        container.innerHTML = '';
        
        if (wishlist.length === 0) {
            emptyState.classList.remove('d-none');
            return;
        }
        
        emptyState.classList.add('d-none');
        
        wishlist.forEach(item => {
            const col = document.createElement('div');
            col.className = 'col-md-6 col-lg-4 col-xl-3';
            col.innerHTML = `
                <div class="c-wishlist-card">
                    <button class="c-wishlist-remove js-remove-wishlist" data-id="${item.id}" aria-label="Remove">
                        <i class="bi bi-trash"></i>
                    </button>
                    <div class="c-wishlist-card__img">
                        <img src="${item.image}" alt="${item.name}">
                    </div>
                    <div class="c-wishlist-card__content">
                        <h4 class="c-wishlist-card__name">${item.name}</h4>
                        <div class="c-wishlist-card__price">₹${item.price}</div>
                        <div class="c-wishlist-card__actions">
                            <a href="${item.url || 'karadant.php'}" class="btn btn-maroon-outline w-100">View Product</a>
                        </div>
                    </div>
                </div>
            `;
            container.appendChild(col);
        });

        // Add listeners to remove buttons
        document.querySelectorAll('.js-remove-wishlist').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const id = btn.dataset.id;
                let currentWishlist = JSON.parse(localStorage.getItem('wishlist')) || [];
                currentWishlist = currentWishlist.filter(item => item.id !== id);
                localStorage.setItem('wishlist', JSON.stringify(currentWishlist));
                renderWishlist();
                
                // Dispatch event for header update
                document.dispatchEvent(new Event('wishlistUpdated'));
            });
        });
    };

    renderWishlist();
    document.addEventListener('wishlistUpdated', renderWishlist);
});
</script>

<style>
/* Local overrides if needed, but primary styles in wishlist.css */
.btn-maroon {
    background: #7b1d1d;
    color: #fff;
    border: none;
}
.btn-maroon:hover {
    background: #601414;
    color: #fff;
}
.btn-maroon-outline {
    background: transparent;
    border: 1.5px solid #7b1d1d;
    color: #7b1d1d;
}
.btn-maroon-outline:hover {
    background: #7b1d1d;
    color: #fff;
}
.text-maroon {
    color: #7b1d1d;
}
</style>

<?php require_once 'includes/footer.php'; ?>
