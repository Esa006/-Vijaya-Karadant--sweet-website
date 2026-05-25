<?php
/**
 * Sweets Website
 * =============================================================
 * File: sections/festival-offers.php
 * Description: Dynamic festival offers section with countdown
 * =============================================================
 */

require_once __DIR__ . '/../services/PromotionService.php';
$promoService = new PromotionService();
$festivalPromo = $promoService->getPromotion('festival-offers');
?>

<section class="c-festival-offers py-5 overflow-hidden position-relative">
    <!-- Decorative Elements -->
    <div class="c-festival-offers__bg-shape"></div>
    
    <div class="container position-relative z-1">
        <div class="row align-items-center g-5">
            
            <!-- Content Side -->
            <div class="col-lg-6 order-2 order-lg-1">
                <div class="pe-lg-5">
                    <span class="badge rounded-pill px-3 py-2 mb-3 text-uppercase fw-bold" 
                          style="background: rgba(123, 29, 29, 0.1); color: #7b1d1d; border: 1px solid rgba(123, 29, 29, 0.2); letter-spacing: 1px;">
                        <?php echo htmlspecialchars($festivalPromo['discount_badge']); ?>
                    </span>
                    
                    <h2 class="display-4 fw-bold mb-1" style="color: #6C2C23; font-family: 'Quando', serif;">
                        <?php echo htmlspecialchars($festivalPromo['title']); ?>
                    </h2>
                    
                    <h5 class="fw-bold mb-3" style="color: #d67a18; letter-spacing: 1px;">
                        <?php echo htmlspecialchars($festivalPromo['subtitle'] ?? ''); ?>
                    </h5>
                    
                    <p class="lead text-muted mb-4">
                        <?php echo htmlspecialchars($festivalPromo['description']); ?>
                    </p>
                    
                    <!-- Countdown Timer -->
                    <div class="d-flex flex-wrap gap-2 gap-md-3 mb-5 js-festival-timer" data-end="<?php echo $festivalPromo['timer_end']; ?>">
                        <div class="text-center p-3 rounded-3 shadow-sm" style="background: #7b1d1d; color: #fff; min-width: 80px; flex: 1;">
                            <div class="h2 fw-bold mb-0 days">00</div>
                            <small class="text-uppercase opacity-75 fw-bold" style="font-size: 0.65rem;">Days</small>
                        </div>
                        <div class="text-center p-3 rounded-3 shadow-sm" style="background: #7b1d1d; color: #fff; min-width: 80px; flex: 1;">
                            <div class="h2 fw-bold mb-0 hours">00</div>
                            <small class="text-uppercase opacity-75 fw-bold" style="font-size: 0.65rem;">Hrs</small>
                        </div>
                        <div class="text-center p-3 rounded-3 shadow-sm" style="background: #7b1d1d; color: #fff; min-width: 80px; flex: 1;">
                            <div class="h2 fw-bold mb-0 minutes">00</div>
                            <small class="text-uppercase opacity-75 fw-bold" style="font-size: 0.65rem;">Mins</small>
                        </div>
                        <div class="text-center p-3 rounded-3 shadow-sm" style="background: #7b1d1d; color: #fff; min-width: 80px; flex: 1;">
                            <div class="h2 fw-bold mb-0 seconds">00</div>
                            <small class="text-uppercase opacity-75 fw-bold" style="font-size: 0.65rem;">Sec</small>
                        </div>
                    </div>
                    
                    <div class="d-flex flex-wrap gap-3">
                        <a href="<?php echo $festivalPromo['btn1_link'] ?? '#'; ?>" 
                           class="btn btn-lg rounded-pill px-5 fw-bold text-white shadow-sm" 
                           style="background: linear-gradient(90deg, #7b1d1d 0%, #d67a18 100%);">
                            <?php echo htmlspecialchars($festivalPromo['btn1_text'] ?? 'Learn More'); ?>
                        </a>
                        <a href="category-products.php" class="btn btn-lg btn-outline-dark rounded-pill px-5 fw-bold">
                            View All Products
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Image Side -->
            <div class="col-lg-6 order-1 order-lg-2">
                <div class="position-relative">
                    <div class="c-festival-offers__img-blob"></div>
                    <img src="<?php echo BASE_URL . $festivalPromo['image_path']; ?>" 
                         alt="Festival Offer" 
                         class="img-fluid position-relative z-1 rounded-4 shadow-lg festival-zoom"
                         onerror="this.src='<?php echo BASE_URL; ?>assets/images/placeholders/offer-placeholder.png'">
                    
                    <!-- Floating Tag -->
                    <div class="position-absolute bottom-0 start-0 mb-4 ms-n4 z-2 bg-white p-3 rounded-3 shadow-sm d-none d-md-block" 
                         style="border-left: 4px solid #7b1d1d;">
                        <div class="fw-bold mb-0" style="color: #7b1d1d;">Freshly Made</div>
                        <small class="text-muted">Handcrafted Daily</small>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</section>

<style>
.c-festival-offers {
    background-color: #fdfaf5;
}

.c-festival-offers__bg-shape {
    position: absolute;
    top: -10%;
    right: -5%;
    width: 40%;
    height: 120%;
    background: radial-gradient(circle, rgba(214, 122, 24, 0.05) 0%, transparent 70%);
    transform: rotate(-15deg);
}

.c-festival-offers__img-blob {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 110%;
    height: 110%;
    background: #7b1d1d08;
    border-radius: 40% 60% 70% 30% / 40% 50% 60% 50%;
    z-index: 0;
    animation: blob-morph 15s ease-in-out infinite;
}

.festival-zoom {
    transition: transform 0.5s ease;
}

.festival-zoom:hover {
    transform: scale(1.02);
}

@keyframes blob-morph {
    0%, 100% { border-radius: 40% 60% 70% 30% / 40% 50% 60% 50%; }
    33% { border-radius: 70% 30% 50% 50% / 30% 30% 70% 70%; }
    66% { border-radius: 30% 60% 70% 40% / 50% 60% 30% 40%; }
}

@media (max-width: 991px) {
    .c-festival-offers__img-blob { width: 90%; height: 90%; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const timerEl = document.querySelector('.js-festival-timer');
    if (!timerEl) return;
    
    const endTime = new Date(timerEl.dataset.end).getTime();
    
    const updateTimer = () => {
        const now = new Date().getTime();
        const diff = endTime - now;
        
        if (diff <= 0) {
            timerEl.innerHTML = '<div class="h4 fw-bold text-danger">Offer Expired!</div>';
            return;
        }
        
        const days = Math.floor(diff / (1000 * 60 * 60 * 24));
        const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const mins = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        const secs = Math.floor((diff % (1000 * 60)) / 1000);
        
        timerEl.querySelector('.days').textContent = days.toString().padStart(2, '0');
        timerEl.querySelector('.hours').textContent = hours.toString().padStart(2, '0');
        timerEl.querySelector('.minutes').textContent = mins.toString().padStart(2, '0');
        timerEl.querySelector('.seconds').textContent = secs.toString().padStart(2, '0');
    };
    
    setInterval(updateTimer, 1000);
    updateTimer();
});
</script>
