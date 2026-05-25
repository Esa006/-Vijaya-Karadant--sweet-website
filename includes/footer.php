<?php
/**
 * Sweets Website
 * =============================================================
 * File: footer.php
 * Description: Global footer with BEM styling and Bootstrap JS
 * Author: Sweets Website Team
 * Version: 1.0.0
 * =============================================================
 */
?>
<footer class="c-footer">
    <!-- Top Newsletter Section -->
    <div class="c-footer__newsletter">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <h2 class="c-footer__newsletter-title">Luxury, established, heritage brand</h2>
                </div>
                <div class="col-lg-6">
                    <form class="c-footer__subscribe-form">
                        <input type="email" class="c-footer__subscribe-input" placeholder="Enter your Email"
                            aria-label="Enter your Email" required>
                        <button type="submit" class="c-footer__subscribe-btn">
                            Subscribe Now
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="ms-1">
                                <line x1="22" y1="2" x2="11" y2="13"></line>
                                <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Links Section -->
    <div class="c-footer__main">
        <div class="container">
            <div class="row">
                <!-- Col 1: Brand -->
                <div class="col-lg-4 col-md-12 mb-5 mb-lg-0">
                    <div class="c-footer__logo">
                        <img src="<?php echo BASE_URL . SITE_LOGO; ?>" alt="<?php echo SITE_NAME; ?>"
                            class="c-footer__logo-img">
                    </div>
                    <p class="c-footer__desc">
                        At Vijaya Karadant, We Bring Joy to Every Bite with Our Wide Range of Delicious, Handcrafted
                        Sweets Made Using Time-Honored Recipes and The Finest Ingredients
                    </p>
                    <div class="c-footer__socials">
                        <a href="https://www.facebook.com/AmingadFamousVijayaKaradant" class="c-footer__social-link"
                            aria-label="Facebook" target="_blank" rel="noopener noreferrer">
                            <img src="<?php echo BASE_URL; ?>assets/images/icons/Link.png" alt="Facebook" width="16"
                                height="16">
                        </a>
                        <a href="https://www.youtube.com/@amingadvijayakaradant" class="c-footer__social-link"
                            aria-label="YouTube" target="_blank" rel="noopener noreferrer">
                            <img src="<?php echo BASE_URL; ?>assets/images/icons/Link (3).png" alt="YouTube" width="16"
                                height="16">
                        </a>
                        <a href="https://www.instagram.com/vijaya_karadantu/" class="c-footer__social-link"
                            aria-label="Instagram" target="_blank" rel="noopener noreferrer">
                            <img src="<?php echo BASE_URL; ?>assets/images/icons/Link (2).png" alt="Instagram"
                                width="16" height="16">
                        </a>
                        <a href="https://x.com/VijayaKaradant" class="c-footer__social-link" aria-label="Twitter"
                            target="_blank" rel="noopener noreferrer">
                            <img src="<?php echo BASE_URL; ?>assets/images/icons/Link (1).png" alt="Twitter" width="14"
                                height="14">
                        </a>
                    </div>
                </div>

                <!-- Col 2: Quick Links -->
                <div class="col-lg-2 col-md-4 mb-4 mb-md-0">
                    <h4 class="c-footer__heading">Quick Links</h4>
                    <ul class="c-footer__list">
                        <li><a href="index.php" class="c-footer__link"><i
                                    class="bi bi-chevron-right c-footer__chevron"></i> Home</a></li>
                        <li><a href="about.php" class="c-footer__link"><i
                                    class="bi bi-chevron-right c-footer__chevron"></i> About Us</a></li>
                        <li><a href="karadant.php" class="c-footer__link"><i
                                    class="bi bi-chevron-right c-footer__chevron"></i> Karadant</a></li>
                        <li><a href="namkeen.php" class="c-footer__link"><i
                                    class="bi bi-chevron-right c-footer__chevron"></i> Namkeen</a></li>
                        <li><a href="combos.php" class="c-footer__link"><i
                                    class="bi bi-chevron-right c-footer__chevron"></i> Combos</a></li>
                        <li><a href="gifting.php" class="c-footer__link"><i
                                    class="bi bi-chevron-right c-footer__chevron"></i> Gifting</a></li>
                        <li><a href="global-shipping.php" class="c-footer__link"><i
                                    class="bi bi-chevron-right c-footer__chevron"></i> Global Shipping</a></li>
                        <li><a href="branches.php" class="c-footer__link"><i
                                    class="bi bi-chevron-right c-footer__chevron"></i> Branches</a></li>
                                    
                        <li><a href="profile.php" class="c-footer__link"><i
                                    class="bi bi-chevron-right c-footer__chevron"></i> My Profile</a></li>
                                    
                        <li><a href="my-orders.php" class="c-footer__link"><i
                                    class="bi bi-chevron-right c-footer__chevron"></i> My Orders</a></li>

                        <li><a href="contact.php" class="c-footer__link"><i class="bi bi-chevron-right c-footer__chevron"></i>
                                Contact Us</a></li>
                    </ul>
                </div>

                <!-- Col 3: Support / Help -->
                <div class="col-lg-3 col-md-4 mb-4 mb-md-0">
                    <h4 class="c-footer__heading">Support / Help</h4>
                    <ul class="c-footer__list">
                        <li><a href="javascript:void(0)" class="c-footer__link" data-bs-toggle="modal" data-bs-target="#deliveryPolicyModal"><i
                                    class="bi bi-chevron-right c-footer__chevron"></i> Shipping Policy</a></li>
                        <li><a href="<?php echo BASE_URL; ?>return-refund-policy.php" class="c-footer__link"><i
                                    class="bi bi-chevron-right c-footer__chevron"></i> Returns & Refunds</a></li>
                        <li><a href="<?php echo BASE_URL; ?>privacy-policy.php" class="c-footer__link"><i
                                    class="bi bi-chevron-right c-footer__chevron"></i> Privacy Policy</a></li>
                        <li><a href="<?php echo BASE_URL; ?>terms-and-conditions.php" class="c-footer__link"><i
                                    class="bi bi-chevron-right c-footer__chevron"></i> Terms of Service</a></li>
                    </ul>
                </div>

                <!-- Col 4: Get in touch -->
                <div class="col-lg-3 col-md-4">
                    <h4 class="c-footer__heading">Get in touch</h4>
                    <ul class="c-footer__contact">
                        <li class="c-footer__contact-item">
                            <div class="c-footer__contact-icon">
                                <img src="assets/images/icon/footer-icon (3).png" alt="Phone" width="20" height="20">
                            </div>
                            <div class="c-footer__contact-text">
                                <a href="tel:+917259699366">+91 - 7259699366</a><br>
                                <a href="tel:+917259699366">+91-7259699366</a>
                            </div>
                        </li>
                        <li class="c-footer__contact-item">
                            <div class="c-footer__contact-icon">
                                <img src="assets/images/icon/footer-icon (2).png" alt="Email" width="20" height="20">
                            </div>
                            <div class="c-footer__contact-text">
                                <a href="mailto:support@vijayakaradant.in">support@vijayakaradant.in</a>
                            </div>
                        </li>
                        <li class="c-footer__contact-item">
                            <div class="c-footer__contact-icon">
                                <img src="assets/images/icon/footer-icon (1).png" alt="Address" width="20" height="20">
                            </div>
                            <div class="c-footer__contact-text">
                                <a href="https://maps.google.com/?q=Amingad+Vijaya+Karadant" target="_blank" rel="noopener noreferrer">
                                    Main Road, Aminagad, Bagalkot,<br>
                                    KA - 587112
                                </a>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Copyright -->
    <div class="c-footer__bottom">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-7 text-center text-md-start mb-3 mb-md-0">
                    <p class="c-footer__copyright">&copy; 2026 Vijaya Karadant. All Rights Reserved. Heritage of
                        Authentic Taste.</p>
                </div>
                <div class="col-md-5 text-center text-md-end">
                    <div class="c-footer__payments">
                        <span class="c-footer__we-accept me-3">We Accept</span>
                        <div class="c-footer__payment-list">
                            <img src="<?php echo BASE_URL; ?>assets/images/homepage/cards.png (1).png"
                                alt="Accepted Payments" height="22">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- Floating WhatsApp Button -->
<a href="https://wa.me/917259699366" class="c-floating-whatsapp" target="_blank" rel="noopener noreferrer" aria-label="Chat with us on WhatsApp">
    <i class="bi bi-whatsapp"></i>
</a>

<style>
.c-floating-whatsapp {
    position: fixed;
    bottom: 30px;
    right: 30px;
    width: 60px;
    height: 60px;
    background-color: #25d366;
    color: #fff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 35px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    z-index: 1000;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.c-floating-whatsapp:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
    color: #fff;
}
@media (max-width: 768px) {
    .c-floating-whatsapp {
        bottom: 20px;
        right: 20px;
        width: 50px;
        height: 50px;
        font-size: 30px;
    }
}
</style>

<!-- Modal Templates -->
<?php require_once 'sections/interaction-modal.php'; ?>
<?php require_once 'includes/store-policy-modal.php'; ?>
<?php require_once 'includes/delivery-policy-modal.php'; ?>
<?php require_once 'includes/privacy-policy-modal.php'; ?>

<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Swiper 11 JS -->
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Custom JS -->
<script src="<?php echo BASE_URL; ?>assets/js/main.js?v=<?php echo SITE_VERSION; ?>" defer></script>
<!-- Stock UI -->
<script src="<?php echo BASE_URL; ?>assets/js/pages/stock-ui.js?v=<?php echo SITE_VERSION; ?>" defer></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // 1. Newsletter Subscription — real AJAX save to DB
        const newsletterForm = document.querySelector('.c-footer__subscribe-form');
        if (newsletterForm) {
            newsletterForm.addEventListener('submit', function (e) {
                e.preventDefault();
                const emailInput = this.querySelector('input[type="email"]');
                const email = emailInput.value.trim();
                const btn   = this.querySelector('button[type="submit"]');

                if (!email) return;

                btn.disabled = true;
                btn.textContent = 'Subscribing…';

                fetch('<?php echo BASE_URL; ?>api/v1/newsletter-subscribe.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email, source: 'footer' })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Subscribed!',
                            text: data.message,
                            icon: 'success',
                            confirmButtonColor: '#7b1d1d'
                        });
                        newsletterForm.reset();
                    } else {
                        Swal.fire({
                            title: 'Oops!',
                            text: data.message,
                            icon: 'error',
                            confirmButtonColor: '#7b1d1d'
                        });
                    }
                })
                .catch(() => {
                    Swal.fire({ title: 'Error', text: 'Network error. Please try again.', icon: 'error', confirmButtonColor: '#7b1d1d' });
                })
                .finally(() => {
                    btn.disabled = false;
                    btn.innerHTML = 'Subscribe Now <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="ms-1"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>';
                });
            });
        }

        // 2. Global Session Alerts (SweetAlert2)
        const toastConfig = {
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            padding: '0.5rem 1rem', // Reduced padding
            customClass: {
                popup: 'c-swal-toast',
                title: 'c-swal-toast__title',
                htmlContainer: 'c-swal-toast__text'
            }
        };

        <?php if (isset($_SESSION['cart_success'])): ?>
            Swal.fire({
                ...toastConfig,
                title: 'Success!',
                text: '<?php echo $_SESSION['cart_success']; ?>',
                icon: 'success'
            });
            <?php unset($_SESSION['cart_success']); ?>
            <?php
        endif; ?>

        <?php if (isset($_SESSION['cart_error'])): ?>
            Swal.fire({
                ...toastConfig,
                title: 'Error!',
                text: '<?php echo addslashes($_SESSION['cart_error']); ?>',
                icon: 'error',
                timer: 5000
            });
            <?php unset($_SESSION['cart_error']); ?>
            <?php
        endif; ?>

        <?php if (isset($_SESSION['cart_stock_error'])): ?>
            Swal.fire({
                ...toastConfig,
                title: 'Stock Updated',
                text: '<?php echo addslashes($_SESSION['cart_stock_error']); ?>',
                icon: 'warning',
                timer: 6000
            });
            <?php unset($_SESSION['cart_stock_error']); ?>
            <?php
        endif; ?>
    });
</script>

<style>
    /* Custom "Tight" Styling for SweetAlert2 Toasts */
    .c-swal-toast {
        width: auto !important;
        min-width: 250px;
        border-radius: 8px !important;
    }

    .c-swal-toast__title {
        font-size: 14px !important;
        font-weight: 700 !important;
        margin: 0 !important;
        padding: 0 !important;
        color: #7b1d1d !important;
    }

    .c-swal-toast__text {
        font-size: 13px !important;
        margin: 2px 0 0 0 !important;
        padding: 0 !important;
    }
</style>

</body>

</html>
