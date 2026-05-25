<?php
/**
 * Sweets Website
 * =============================================================
 * File: help.php
 * Description: Help Center and FAQ Page
 * =============================================================
 */

require_once 'config/config.php';
require_once 'includes/header.php';
?>

<!-- Help Page Styles -->
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/pages/help.css?v=<?php echo SITE_VERSION; ?>">

<main class="h-page py-5">
    <div class="container">
        
        <!-- Page Header -->
        <header class="h-header mb-5">
            <h1 class="h-title">Need Help ?</h1>
        </header>

        <!-- Help Categories Grid -->
        <div class="row g-4 mb-5 pb-lg-5">
            <div class="col-6 col-lg-3">
                <div class="h-cat-card">
                    <div class="h-cat-icon">
                        <i class="bi bi-box-seam"></i>
                    </div>
                    <h3 class="h-cat-title">Orders</h3>
                    <p class="h-cat-desc">Status Modifications and Cancellations</p>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="h-cat-card">
                    <div class="h-cat-icon">
                        <i class="bi bi-credit-card"></i>
                    </div>
                    <h3 class="h-cat-title">Payments</h3>
                    <p class="h-cat-desc">Invoices, Methods and transaction issues.</p>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="h-cat-card">
                    <div class="h-cat-icon">
                        <i class="bi bi-truck"></i>
                    </div>
                    <h3 class="h-cat-title">Shipping</h3>
                    <p class="h-cat-desc">Tracking delivery times and regions.</p>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="h-cat-card">
                    <div class="h-cat-icon">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </div>
                    <h3 class="h-cat-title">Returns</h3>
                    <p class="h-cat-desc">Policies for perishable confectionery.</p>
                </div>
            </div>
        </div>

        <!-- FAQ Section -->
        <section class="h-faq-section py-5">
            <div class="row align-items-center g-5">
                
                <!-- FAQ Left: Image & Quote -->
                <div class="col-lg-5">
                    <div class="h-faq-image-wrap">
                        <img src="assets/images/homepage/gift-box.png" alt="Sweets Artisan" class="h-faq-img img-fluid rounded-4">
                        <div class="h-faq-quote-box">
                            <p class="h-faq-quote-text">
                                "The secret lies in the 12-hour cooling process and the exact proportion of 24 different dry fruits"
                            </p>
                            <span class="h-faq-quote-author">Master Sweets Artisan</span>
                        </div>
                    </div>
                </div>

                <!-- FAQ Right: Accordion -->
                <div class="col-lg-7">
                    <div class="h-faq-content">
                        <h2 class="h-faq-title mb-2">Frequently Asked Questions</h2>
                        <p class="h-faq-subtitle mb-4">Quick Answers to our most commonly asked questions</p>

                        <div class="h-accordion">
                            <!-- Question 1 -->
                            <div class="h-accordion-item">
                                <button class="h-accordion-toggle">
                                    <span>How can I Track My Order ?</span>
                                    <i class="bi bi-plus-circle-fill"></i>
                                </button>
                                <div class="h-accordion-collapse">
                                    <div class="h-accordion-body">
                                        You can track your order using the 'Track Order' link in your order confirmation email or by visiting the 'My Orders' section in your profile.
                                    </div>
                                </div>
                            </div>

                            <!-- Question 2 -->
                            <div class="h-accordion-item">
                                <button class="h-accordion-toggle">
                                    <span>What Payment Methods do you Accepts ?</span>
                                    <i class="bi bi-plus-circle-fill"></i>
                                </button>
                                <div class="h-accordion-collapse">
                                    <div class="h-accordion-body">
                                        We accept all major credit/debit cards, UPI, Google Pay, PhonePe, and Net Banking.
                                    </div>
                                </div>
                            </div>

                            <!-- Question 3 -->
                            <div class="h-accordion-item">
                                <button class="h-accordion-toggle">
                                    <span>Do You Offer International Shipping ?</span>
                                    <i class="bi bi-plus-circle-fill"></i>
                                </button>
                                <div class="h-accordion-collapse">
                                    <div class="h-accordion-body">
                                        Yes, we currently ship to select international locations. Delivery times and shipping costs vary by region.
                                    </div>
                                </div>
                            </div>

                            <!-- Question 4 -->
                            <div class="h-accordion-item">
                                <button class="h-accordion-toggle">
                                    <span>What is your Return Policy for food items ?</span>
                                    <i class="bi bi-plus-circle-fill"></i>
                                </button>
                                <div class="h-accordion-collapse">
                                    <div class="h-accordion-body">
                                        Due to the perishable nature of our products, we only accept returns if the items are damaged or incorrect upon delivery.
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </section>

        <!-- Contact Info Bar -->
        <div class="h-info-bar py-4 mt-5">
            <div class="row text-center g-4">
                <div class="col-md-4">
                    <div class="h-info-item">
                        <div class="h-info-icon"><i class="bi bi-telephone"></i></div>
                        <div class="h-info-text text-start">
                            <p class="mb-0">+91 - 7259699366</p>
                            <p class="mb-0 small text-muted">91 / 7259699356</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="h-info-item">
                        <div class="h-info-icon"><i class="bi bi-envelope"></i></div>
                        <div class="h-info-text text-start">
                            <p class="mb-0">amingadkaradant@gmail.com</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="h-info-item">
                        <div class="h-info-icon"><i class="bi bi-geo-alt"></i></div>
                        <div class="h-info-text text-start">
                            <p class="mb-0">Main Road, Aminagad,</p>
                            <p class="mb-0 small text-muted">Bagalkot, KA - 587112</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer CTA -->
        <footer class="h-footer text-center py-5 mt-5">
            <h2 class="h-footer-title mb-4">Still have Questions?</h2>
            <a href="contact.php" class="btn h-btn-contact px-5 py-3">Contact Us</a>
        </footer>

    </div><!-- /container -->
</main>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const toggles = document.querySelectorAll('.h-accordion-toggle');
    
    toggles.forEach(toggle => {
        toggle.addEventListener('click', () => {
            const item = toggle.parentElement;
            const icon = toggle.querySelector('i');
            
            // Close other items
            document.querySelectorAll('.h-accordion-item').forEach(otherItem => {
                if (otherItem !== item) {
                    otherItem.classList.remove('is-active');
                    otherItem.querySelector('i').className = 'bi bi-plus-circle-fill';
                }
            });
            
            // Toggle current
            item.classList.toggle('is-active');
            if (item.classList.contains('is-active')) {
                icon.className = 'bi bi-dash-circle-fill';
            } else {
                icon.className = 'bi bi-plus-circle-fill';
            }
        });
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
