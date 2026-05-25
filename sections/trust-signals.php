<?php
/**
 * Sweets Website
 * =============================================================
 * File: sections/trust-signals.php
 * Description: Trust signal strip (Free Shipping, Top Rated, etc)
 * =============================================================
 */
?>

<section class="c-trust-signals py-4" aria-label="Trust Signals">
    <div class="container">
        <div class="row g-4 justify-content-center">
            <!-- Signal 1 -->
            <div class="col-6 col-md-4 col-lg-3">
                <div class="c-trust-item">
                    <i data-lucide="truck"></i>
                    <div class="c-trust-content">
                        <h5>Free Shipping</h5>
                        <p>Orders above ₹999</p>
                    </div>
                </div>
            </div>

            <!-- Signal 2 -->
            <div class="col-6 col-md-4 col-lg-3">
                <div class="c-trust-item">
                    <i data-lucide="star"></i>
                    <div class="c-trust-content">
                        <h5>Top Rated</h5>
                        <p>4.8/5 Star Rating</p>
                    </div>
                </div>
            </div>

            <!-- Signal 3 -->
            <div class="col-6 col-md-4 col-lg-3">
                <div class="c-trust-item">
                    <i data-lucide="shield-check"></i>
                    <div class="c-trust-content">
                        <h5>Secure Pay</h5>
                        <p>100% Safe Payments</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Ensure Lucide Icons are initialized -->
<script>
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
</script>
