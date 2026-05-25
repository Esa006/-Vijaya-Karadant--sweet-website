<?php
/**
 * Sweets Website
 * =============================================================
 * File: franchise.php
 * Description: Franchise opportunities and inquiry page.
 * =============================================================
 */

require_once 'config/config.php';

$pageTitle = "Franchise Opportunities - " . SITE_NAME;
require_once 'includes/header.php';
?>

<main class="franchise-page">
    <!-- Hero Section -->
    <section class="franchise-hero py-5 text-white" style="background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('assets/images/about/hero.png'); background-size: cover; background-position: center;">
        <div class="container py-5 text-center">
            <h1 class="display-4 fw-bold mb-4">Partner with a Legacy</h1>
            <p class="lead mb-0">Join the Vijaya Karadant family and bring the authentic taste of Karnataka to your city.</p>
        </div>
    </section>

    <!-- Franchise Info -->
    <section class="franchise-info py-5">
        <div class="container py-4">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-5 mb-lg-0">
                    <h2 class="fw-bold mb-4" style="color: var(--primary);">Why Franchise with Us?</h2>
                    <p class="text-muted mb-4">With over 75 years of heritage, Vijaya Karadant is a household name for authentic dry fruit sweets. Our franchise model is designed for partners who value quality, tradition, and customer satisfaction.</p>
                    
                    <ul class="list-unstyled">
                        <li class="mb-3 d-flex align-items-start gap-3">
                            <i class="bi bi-check-circle-fill text-success fs-5"></i>
                            <div>
                                <h5 class="fw-bold mb-1">Established Brand</h5>
                                <p class="small text-muted mb-0">High brand recall and trust across Karnataka and beyond.</p>
                            </div>
                        </li>
                        <li class="mb-3 d-flex align-items-start gap-3">
                            <i class="bi bi-check-circle-fill text-success fs-5"></i>
                            <div>
                                <h5 class="fw-bold mb-1">Authentic Recipes</h5>
                                <p class="small text-muted mb-0">Original recipes preserved and perfected over three generations.</p>
                            </div>
                        </li>
                        <li class="mb-3 d-flex align-items-start gap-3">
                            <i class="bi bi-check-circle-fill text-success fs-5"></i>
                            <div>
                                <h5 class="fw-bold mb-1">Operational Support</h5>
                                <p class="small text-muted mb-0">End-to-end support for store setup, supply chain, and marketing.</p>
                            </div>
                        </li>
                    </ul>
                </div>
                <div class="col-lg-6 ps-lg-5">
                    <div class="card border-0 shadow-lg p-4 p-md-5 rounded-4">
                        <h3 class="fw-bold mb-4 text-center">Franchise Inquiry</h3>
                        <form id="franchiseForm">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Full Name</label>
                                    <input type="text" class="form-control" name="name" required placeholder="John Doe">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Phone Number</label>
                                    <input type="tel" class="form-control" name="phone" required placeholder="+91 98765 43210">
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-bold">Email Address</label>
                                    <input type="email" class="form-control" name="email" required placeholder="john@example.com">
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-bold">Target City / Location</label>
                                    <input type="text" class="form-control" name="location" required placeholder="e.g. Bengaluru, KA">
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-bold">Investment Budget</label>
                                    <select class="form-select" name="budget" required>
                                        <option value="" disabled selected>Select Budget Range</option>
                                        <option value="10-20L">₹10 Lakhs - ₹20 Lakhs</option>
                                        <option value="20-50L">₹20 Lakhs - ₹50 Lakhs</option>
                                        <option value="50L+">Above ₹50 Lakhs</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-bold">Additional Comments</label>
                                    <textarea class="form-control" name="message" rows="3" placeholder="Tell us more about your interest..."></textarea>
                                </div>
                                <div class="col-12 mt-4">
                                    <button type="submit" class="btn w-100 py-3 fw-bold" style="background: var(--primary); color: #fff;">Submit Inquiry</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<style>
:root {
    --primary: #7b1d1d;
}
.franchise-hero {
    min-height: 400px;
    display: flex;
    align-items: center;
}
.form-control:focus, .form-select:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 0.25rem rgba(123, 29, 29, 0.1);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('franchiseForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = this.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.textContent = 'Submitting...';

            // Simulate API call
            setTimeout(() => {
                Swal.fire({
                    title: 'Inquiry Submitted!',
                    text: 'Thank you for your interest. Our franchise team will contact you shortly.',
                    icon: 'success',
                    confirmButtonColor: '#7b1d1d'
                });
                form.reset();
                btn.disabled = false;
                btn.textContent = 'Submit Inquiry';
            }, 1500);
        });
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
