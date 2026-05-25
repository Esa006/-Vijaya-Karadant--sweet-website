<?php
/**
 * Sweets Website
 * =============================================================
 * File: contact.php
 * Description: Premium Contact Page for Franchisee & Support
 * Author: Sweets Website Team
 * Version: 2.0.0
 * =============================================================
 */

require_once 'config/config.php';
require_once 'includes/header.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/pages/contact.css?v=<?php echo SITE_VERSION; ?>">

<main class="p-contact-page">

    <!-- ═══════════════════════════════════════════════════ -->
    <!--  Hero Section                                       -->
    <!-- ═══════════════════════════════════════════════════ -->
    <section class="c-contact-hero">
        <div class="container">
            <div class="row align-items-center g-5">
                <!-- Content Left -->
                <div class="col-lg-6" data-aos="fade-right">
                    <h1 class="c-contact-hero__title">
                        Get in Touch <br>
                        <span class="c-contact-hero__title--highlight">with Us</span>
                    </h1>
                    <p class="c-contact-hero__desc">
                        Whether it's a special request, a wholesale inquiry, or just to say hello, We'd love to hear from you.
                    </p>

                    <div class="c-contact-hero__pill-group">
                        <a href="tel:+917259699366" class="c-contact-hero__pill">
                            <span class="c-contact-hero__pill-icon">
                                <img src="assets/images/icon/contact-icon (3).png" alt="Phone">
                            </span>
                            +91 72596 99366
                        </a>
                        <a href="mailto:support@vijayakaradant.in" class="c-contact-hero__pill c-contact-hero__pill--light">
                            <span class="c-contact-hero__pill-icon">
                                <img src="assets/images/icon/contact-icon (2).png" alt="Email">
                            </span>
                            support@vijayakaradant.in
                        </a>
                    </div>
                </div>

                <!-- Image Right -->
                <div class="col-lg-6" data-aos="fade-left">
                    <div class="c-contact-hero__image-wrap">
                        <img src="assets/images/contackus.png" alt="Vijaya Karadant Products" class="c-contact-hero__img img-fluid">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ═══════════════════════════════════════════════════ -->
    <!--  Contact Highlights                                 -->
    <!-- ═══════════════════════════════════════════════════ -->
    <section class="c-contact-highlights">
        <div class="container">
            <h2 class="c-contact-highlights__heading" data-aos="fade-up">Contact highlights</h2>
            <div class="row g-4">
                <!-- Card 1: Call -->
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="0">
                    <div class="c-contact-highlight-card">
                        <div class="c-contact-highlight-card__icon">
                            <img src="assets/images/icon/contact-icon (3).png" alt="Call Icon">
                        </div>
                        <h3 class="c-contact-highlight-card__title">Call Us Directly</h3>
                        <p class="c-contact-highlight-card__text">
                            <a href="tel:+917259699383">+91 72596 99383</a><br>
                            <a href="tel:+917259699355">+91 72596 99355</a>
                        </p>
                    </div>
                </div>

                <!-- Card 2: Email -->
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                    <div class="c-contact-highlight-card">
                        <div class="c-contact-highlight-card__icon">
                            <img src="assets/images/icon/contact-icon (2).png" alt="Email Icon">
                        </div>
                        <h3 class="c-contact-highlight-card__title">Email Support</h3>
                        <p class="c-contact-highlight-card__text">
                            <a href="mailto:support@vijayakaradant.in">support@vijayakaradant.in</a>
                        </p>
                    </div>
                </div>

                <!-- Card 3: Hours -->
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="c-contact-highlight-card">
                        <div class="c-contact-highlight-card__icon">
                            <img src="assets/images/icon/contact-icon (1).png" alt="Hours Icon">
                        </div>
                        <h3 class="c-contact-highlight-card__title">Working hours</h3>
                        <p class="c-contact-highlight-card__text">
                            Mon - Sat, 10 AM to 6 PM
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ═══════════════════════════════════════════════════ -->
    <!--  Send a Message Form                                -->
    <!-- ═══════════════════════════════════════════════════ -->
    <section class="c-contact-form-section">
        <div class="container">
            <h2 class="c-contact-form-section__heading" data-aos="fade-up">Send u a Message</h2>
            <div class="row g-5 align-items-start">
                <!-- Form Left -->
                <div class="col-lg-7" data-aos="fade-right">
                    <form class="c-contact-form" id="secureContactForm" action="#" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="form_loaded_at" value="<?php echo time(); ?>">
                        
                        <!-- Anti-Bot Honeypot Field -->
                        <div style="display: none; position: absolute; left: -9999px;">
                            <label for="website_url">Leave this empty if human</label>
                            <input type="text" name="website_url" id="website_url" tabindex="-1" autocomplete="off">
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <input type="text" name="full_name" class="c-contact-form__input" placeholder="Full Name" required>
                            </div>
                            <div class="col-md-6">
                                <input type="email" name="email" class="c-contact-form__input" placeholder="Email Address" required>
                            </div>
                            <div class="col-12">
                                <input type="tel" name="phone" class="c-contact-form__input" placeholder="Phone Number">
                            </div>
                            <div class="col-12">
                                <textarea name="message" class="c-contact-form__textarea" rows="4" placeholder="How can we help?" required></textarea>
                            </div>
                            
                            <!-- Container for Displaying System API Status -->
                            <div class="col-12" id="formMessageResponse"></div>

                            <div class="col-12">
                                <button type="submit" id="submitBtn" class="c-contact-form__btn">Send Message</button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Side Panel Right -->
                <div class="col-lg-5" data-aos="fade-left">
                    <div class="c-contact-side-panel">
                        <div class="c-contact-side-panel__img-wrap">
                            <img src="assets/images/aboutpage/The First Storefront.png" alt="Our Store" class="c-contact-side-panel__img img-fluid">
                        </div>
                        <div class="c-contact-side-panel__body">
                            <h3 class="c-contact-side-panel__title">A Century of Sweetness</h3>
                            <p class="c-contact-side-panel__desc">
                                At Vijaya Karadant, We Bring Joy to Every Bite with Our Wide Range of Delicious, Handcrafted Sweets Made Using Time-Honored Recipes and The Finest Ingredients.
                            </p>
                            <ul class="c-contact-side-panel__list">
                                <li>
                                    <span class="c-contact-side-panel__check">
                                        <i class="bi bi-check-circle-fill"></i>
                                    </span>
                                    <div>
                                        <strong>Pure Ingredients</strong><br>
                                        <small>Handpicked and dry fruits and pure ghee</small>
                                    </div>
                                </li>
                                <li>
                                    <span class="c-contact-side-panel__check">
                                        <i class="bi bi-check-circle-fill"></i>
                                    </span>
                                    <div>
                                        <strong>Global Delivery</strong><br>
                                        <small>We ship our heritage products from Gokak</small>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ═══════════════════════════════════════════════════ -->
    <!--  Map Section                                        -->
    <!-- ═══════════════════════════════════════════════════ -->
    <section class="c-contact-map-section">
        <div class="container">
            <h2 class="c-contact-map-section__heading" data-aos="fade-up">Find us in Gokak</h2>
            <div class="c-contact-map-wrap" data-aos="fade-up" data-aos-delay="100">
                <a href="https://maps.google.com/?q=Amingad+Vijaya+Karadant" target="_blank" rel="noopener noreferrer" class="c-contact-map-link" title="Open in Google Maps">
                    <img src="assets/images/cart/map.png" 
                         alt="Amingad's Vijaya Karadant location on Google Maps" 
                         class="c-contact-map-img">
                    <span class="c-contact-map-overlay">
                        <i class="bi bi-box-arrow-up-right"></i> Open in Google Maps
                    </span>
                </a>
            </div>
        </div>
    </section>


    <!-- ═══════════════════════════════════════════════════ -->
    <!--  CTA Banner                                         -->
    <!-- ═══════════════════════════════════════════════════ -->
    <section class="c-contact-cta">
        <div class="container">
            <div class="c-contact-cta__inner" data-aos="fade-up">
                <div class="row align-items-center g-4">
                    <div class="col-lg-6">
                        <h2 class="c-contact-cta__title">Visit our store or Contact us Today</h2>
                        <p class="c-contact-cta__desc">Ready to taste the original heritage? Explore our full Catalog of authentic Gokak Karadant and other specialities</p>
                    </div>
                    <div class="col-lg-6 d-flex gap-3 justify-content-lg-end flex-wrap">
                        <a href="branches.php" class="c-contact-cta__btn c-contact-cta__btn--outline">Find a Branch</a>
                        <a href="index.php#bestsellers" class="c-contact-cta__btn c-contact-cta__btn--solid">Explore Products</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

</main>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('secureContactForm');
    if (!form) return;

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        const btn = document.getElementById('submitBtn');
        const msgBox = document.getElementById('formMessageResponse');
        
        // UI Loading State Prevent multiple clicks
        btn.disabled = true;
        btn.innerHTML = 'Sending securely... <span class="spinner-border spinner-border-sm ms-2" role="status" aria-hidden="true"></span>';
        msgBox.innerHTML = ''; // Reset UI
        
        try {
            const formData = new FormData(this);
            const response = await fetch('api/contact.php', {
                method: 'POST',
                body: formData,
                headers: { 'Accept': 'application/json' }
            });
            
            const data = await response.json();
            
            if (response.ok || response.status === 202 || response.status === 200) {
                msgBox.innerHTML = `<div class="alert alert-success mt-3" style="font-size: 0.9rem;">${data.message}</div>`;
                this.reset();
                setTimeout(() => { msgBox.innerHTML = ''; }, 6000);
            } else {
                msgBox.innerHTML = `<div class="alert alert-danger mt-3" style="font-size: 0.9rem;">${data.message}</div>`;
            }
        } catch (err) {
            console.error("AJAX Error:", err);
            msgBox.innerHTML = `<div class="alert alert-danger mt-3" style="font-size: 0.9rem;">Network error. Please make sure you are online and try again.</div>`;
        } finally {
            btn.disabled = false;
            btn.innerHTML = 'Send Message';
        }
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
