<?php
/**
 * Sweets Website
 * =============================================================
 * File: payment-methods.php
 * Description: Page to manage saved payment methods
 * =============================================================
 */

require_once 'config/config.php';
require_once 'includes/header.php';
?>

<!-- Payment Methods Styles -->
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/pages/payment-methods.css?v=<?php echo SITE_VERSION; ?>">

<main class="pm-page py-5">
    <div class="container">
        
        <!-- Page Header -->
        <header class="pm-header mb-5">
            <h1 class="pm-title">Payment Methods</h1>
            <p class="pm-subtitle">Secure Checkout Profile</p>
        </header>

        <!-- Section 1: Saved Credit & Debit Cards -->
        <section class="pm-section mb-5">
            <h2 class="pm-section-title mb-4">Saved Credit & Debit Cards</h2>
            <div class="row g-4 align-items-center">
                
                <!-- Card 1 -->
                <div class="col-md-6 col-lg-4">
                    <div class="pm-card-img-wrap">
                        <img src="assets/images/payment/card 3.png" alt="Credit Card 1" class="pm-card-img">
                    </div>
                </div>

                <!-- Card 2 -->
                <div class="col-md-6 col-lg-4">
                    <div class="pm-card-img-wrap">
                        <img src="assets/images/payment/card 11.png" alt="Credit Card 2" class="pm-card-img">
                    </div>
                </div>

                <!-- Add New Card -->
                <div class="col-md-6 col-lg-4">
                    <div class="pm-add-card">
                        <div class="pm-add-content">
                            <div class="pm-add-icon">
                                <i class="bi bi-plus-lg"></i>
                            </div>
                            <h4 class="pm-add-title">Add Payment Card</h4>
                            <p class="pm-add-sub">Supports Visa, MC, Rupay</p>
                        </div>
                    </div>
                </div>

            </div>
        </section>

        <!-- Section 2: UPI & Digital Wallets -->
        <section class="pm-section">
            <h2 class="pm-section-title mb-4">UPI & Digital Wallets</h2>
            <div class="pm-wallets-list">
                
                <!-- Phone Pay -->
                <div class="pm-wallet-item">
                    <div class="pm-wallet-brand">
                        <div class="pm-wallet-icon">
                            <img src="assets/images/payment/Frame 2147228621.png" alt="Phone Pay">
                        </div>
                        <span class="pm-wallet-name">Phone Pay</span>
                    </div>
                    <div class="pm-wallet-check">
                        <div class="pm-custom-check"></div>
                    </div>
                </div>

                <!-- Paytm -->
                <div class="pm-wallet-item">
                    <div class="pm-wallet-brand">
                        <div class="pm-wallet-icon">
                            <img src="assets/images/payment/image 184.png" alt="Paytm">
                        </div>
                        <span class="pm-wallet-name">Paytm</span>
                    </div>
                    <div class="pm-wallet-check">
                        <div class="pm-custom-check"></div>
                    </div>
                </div>

                <!-- Google Pay -->
                <div class="pm-wallet-item">
                    <div class="pm-wallet-brand">
                        <div class="pm-wallet-icon">
                            <img src="assets/images/payment/Frame 2147228622.png" alt="Google Pay">
                        </div>
                        <span class="pm-wallet-name">Google Pay</span>
                    </div>
                    <div class="pm-wallet-check">
                        <div class="pm-custom-check"></div>
                    </div>
                </div>

                <!-- Amazon Pay -->
                <div class="pm-wallet-item">
                    <div class="pm-wallet-brand">
                        <div class="pm-wallet-icon">
                            <img src="assets/images/payment/image 180.png" alt="Amazon Pay">
                        </div>
                        <span class="pm-wallet-name">Amazon Pay</span>
                    </div>
                    <div class="pm-wallet-check">
                        <div class="pm-custom-check"></div>
                    </div>
                </div>

            </div>
        </section>

    </div><!-- /container -->
</main>

<?php require_once 'includes/footer.php'; ?>
