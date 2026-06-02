<?php
/**
 * Sweets Website
 * =============================================================
 * File: checkout-v2.php
 * Description: Premium Checkout Page with Order Summary
 * =============================================================
 */

require_once 'config/config.php';
require_once SERVICES_PATH . '/CartService.php';
require_once 'includes/header.php';

$cartService = new CartService();
$cartItems   = $cartService->getItems();
$subtotal    = $cartService->getSubtotal();
$shipping    = $cartService->getShippingCharges();
$discount    = $cartService->getCouponDiscount();
$appliedCoupon = $cartService->getCouponTitle();
$total       = $cartService->getTotal();
?>

<!-- Checkout V2 Styles -->
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/pages/checkout-v2.css?v=<?php echo SITE_VERSION; ?>">

<main class="cv2-page">
    <div class="container py-5">

        <!-- ══════════════════════════════════════════
             ORDER SUMMARY  (full-width top card)
        ══════════════════════════════════════════ -->
        <section class="cv2-summary-card mb-5">
            <h2 class="cv2-summary-card__title">Order Summary</h2>

            <!-- Product rows -->
            <?php if (!empty($cartItems)): ?>
                <?php foreach ($cartItems as $item): ?>
                <div class="cv2-summary-card__product">
                    <div class="cv2-summary-card__product-img">
                        <img src="<?php echo htmlspecialchars($item['image']); ?>"
                             alt="<?php echo htmlspecialchars($item['name']); ?>">
                    </div>
                    <div class="cv2-summary-card__product-info">
                        <h5><?php echo htmlspecialchars($item['name']); ?></h5>
                        <p>Weight : <?php echo htmlspecialchars($item['weight'] ?? 'Bundle'); ?></p>
                        <p>Qty: <?php echo $item['quantity']; ?></p>
                    </div>
                    <div class="cv2-summary-card__product-price">
                        ₹<?php echo number_format($item['price']); ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-warning text-center">
                    Your cart is currently empty. <br/>
                    <a href="index.php" class="btn btn-outline-dark mt-3">Return to Shop</a>
                </div>
            <?php endif; ?>

            <!-- Totals -->
            <div class="cv2-summary-card__totals">
                <div class="cv2-totals-line">
                    <span>Subtotal</span>
                    <span>₹<?php echo number_format($subtotal); ?></span>
                </div>
                <div class="cv2-totals-line">
                    <span>Delivery Charges</span>
                    <span>₹<?php echo number_format($shipping); ?></span>
                </div>
                <div class="cv2-totals-line">
                    <span>Discount Applied</span>
                    <span>-₹<?php echo number_format($discount); ?></span>
                </div>
                <div class="cv2-totals-line cv2-totals-line--total">
                    <span>Total Amount</span>
                    <span>₹<?php echo number_format($total); ?></span>
                </div>
            </div>

            <!-- Secure confirmation note -->
            <div class="cv2-secure-note">
                <i class="bi bi-patch-check-fill cv2-secure-note__icon"></i>
                <span class="cv2-secure-note__label">Secure Order Confirmation</span>
                <p class="cv2-secure-note__text">A copy of this invoice has been sent to your registered email address.</p>
            </div>
        </section>

        <!-- ══════════════════════════════════════════
             BOTTOM TWO-COLUMN: OFFERS | PAYMENT
        ══════════════════════════════════════════ -->
        <div class="row g-4 g-lg-5">

            <!-- Left: Payable amount, address, offers, security -->
            <div class="col-lg-5">

                <!-- Payable Amount -->
                <div class="cv2-card mb-4">
                    <p class="cv2-card__label">Total Payable Amount</p>
                    <div class="cv2-payable">
                        <span class="cv2-payable__main">₹<?php echo number_format($total); ?></span>
                        <span class="cv2-payable__strike">₹<?php echo number_format($subtotal + $shipping); ?></span>
                    </div>
                    <?php if ($discount > 0): ?>
                    <div class="cv2-savings-tag">
                        <i class="bi bi-check2-square"></i>
                        You saved ₹<?php echo number_format($discount); ?> on this order
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Shipping Address -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 class="cv2-section-heading mb-0">Shipping Address</h3>
                    <a href="checkout.php" class="cv2-view-more">View More</a>
                </div>

                <!-- Offer Coupons -->
                <div class="cv2-coupon">
                    <div>
                        <h6 class="cv2-coupon__title">Flat ₹100 Cashback</h6>
                        <p class="cv2-coupon__desc">On orders above ₹999 via HDFC Bank Cards</p>
                    </div>
                    <?php if ($appliedCoupon === 'Flat ₹100 Cashback'): ?>
                        <button type="button" class="cv2-btn-apply applied" style="background: #166534; color: #fff;" disabled>Applied</button>
                    <?php else: ?>
                        <button type="button" class="cv2-btn-apply">Apply</button>
                    <?php endif; ?>
                </div>

                <div class="cv2-coupon">
                    <div>
                        <h6 class="cv2-coupon__title">15% Off with GPay</h6>
                        <p class="cv2-coupon__desc">Up to ₹150 cashback on your first UPI transaction</p>
                    </div>
                    <?php if ($appliedCoupon === '15% Off with GPay'): ?>
                        <button type="button" class="cv2-btn-apply applied" style="background: #166534; color: #fff;" disabled>Applied</button>
                    <?php else: ?>
                        <button type="button" class="cv2-btn-apply">Apply</button>
                    <?php endif; ?>
                </div>

                <!-- Security Badges -->
                <div class="cv2-security">
                    <div class="cv2-security__top">
                        <i class="bi bi-patch-check-fill"></i>
                        <span>100% Secure &amp; Encrypted Payments</span>
                    </div>
                    <div class="cv2-security__badges">
                        <span>SSL</span>
                        <span>Norton</span>
                    </div>
                </div>
            </div>

            <!-- Right: Checkout / Payment Methods -->
            <div class="col-lg-7">
                <h1 class="cv2-checkout-title">Checkout</h1>

                <!-- Recommended Card (HDFC) -->
                <div class="cv2-payment-row cv2-payment-row--recommended" data-method="card">
                    <div class="d-flex align-items-center gap-3">
                        <div class="cv2-pay-icon-wrap">
                            <i class="bi bi-credit-card"></i>
                        </div>
                        <div>
                            <h6 class="cv2-pay-row__name">HDFC Bank Debit Card</h6>
                            <p class="cv2-pay-row__sub">Ending in 4022 • Exp 09/27</p>
                        </div>
                    </div>
                    <span class="cv2-recommended-badge">Recommended</span>
                </div>

                <!-- UPI Section (Expanded) -->
                <div class="cv2-upi-section active-method" data-method="upi">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="cv2-pay-icon-wrap">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <div>
                            <h6 class="cv2-pay-row__name">UPI (Google Pay, PhonePe, Paytm)</h6>
                            <p class="cv2-upi-cashback">Extra ₹25 cashback on UPI</p>
                        </div>
                    </div>

                    <div class="cv2-upi-tabs">
                        <label class="cv2-upi-tab">
                            <input type="radio" name="upiMethod" checked>
                            <span>Google Pay</span>
                        </label>
                        <label class="cv2-upi-tab">
                            <input type="radio" name="upiMethod">
                            <span>PhonePe</span>
                        </label>
                    </div>

                    <div class="cv2-upi-input-row">
                        <input type="text" class="cv2-upi-input" placeholder="Enter UPI ID (e.g. user@okaxis)">
                        <button type="button" class="cv2-btn-verify">Verify</button>
                    </div>

                    <div id="paymentContainer">
                        <input type="hidden" id="selectedPaymentMethod" value="upi">
                        <button type="button" id="payBtn" class="cv2-btn-pay w-100 border-0">Pay ₹<?php echo number_format($total); ?> via UPI</button>
                    </div>
                </div>

                <!-- Hidden form to hold checkout data for JS to send to Razorpay API -->
                <form class="needs-validation d-none" id="hiddenCheckoutForm">
                    <?php 
                    $checkoutData = $_SESSION['checkout_data'] ?? [];
                    foreach ($checkoutData as $key => $value): ?>
                        <input type="hidden" name="<?php echo htmlspecialchars($key); ?>" value="<?php echo htmlspecialchars($value); ?>">
                    <?php endforeach; ?>
                </form>

                <!-- Credit / Debit Cards (Collapsed) -->
                <div class="cv2-payment-row" data-method="card">
                    <div class="d-flex align-items-center gap-3">
                        <div class="cv2-pay-icon-wrap">
                            <i class="bi bi-credit-card-2-front"></i>
                        </div>
                        <div>
                            <h6 class="cv2-pay-row__name">Credit / Debit Cards</h6>
                            <p class="cv2-pay-row__sub">Save card for faster checkout</p>
                        </div>
                    </div>
                    <i class="bi bi-chevron-down cv2-chevron"></i>
                </div>

                <!-- Net Banking (Collapsed) -->
                <div class="cv2-payment-row" data-method="netbanking">
                    <div class="d-flex align-items-center gap-3">
                        <div class="cv2-pay-icon-wrap">
                            <i class="bi bi-bank"></i>
                        </div>
                        <div>
                            <h6 class="cv2-pay-row__name">Net Banking</h6>
                            <p class="cv2-pay-row__sub">All major Indian banks supported</p>
                        </div>
                    </div>
                    <i class="bi bi-chevron-down cv2-chevron"></i>
                </div>

                <!-- Wallets (Collapsed) -->
                <div class="cv2-payment-row" data-method="wallet">
                    <div class="d-flex align-items-center gap-3">
                        <div class="cv2-pay-icon-wrap">
                            <i class="bi bi-wallet2"></i>
                        </div>
                        <div>
                            <h6 class="cv2-pay-row__name">Wallets (Amazon Pay, Mobikwik)</h6>
                        </div>
                    </div>
                    <i class="bi bi-chevron-down cv2-chevron"></i>
                </div>

            </div><!-- /col -->
        </div><!-- /row -->
    </div><!-- /container -->
</main>

<?php require_once 'includes/footer.php'; ?>
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script src="<?php echo BASE_URL; ?>assets/js/pages/checkout-validation.js?v=<?php echo SITE_VERSION; ?>"></script>
<script src="<?php echo BASE_URL; ?>assets/js/pages/checkout-razorpay.js?v=<?php echo SITE_VERSION; ?>"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle Coupon Apply buttons with AJAX
    const applyButtons = document.querySelectorAll('.cv2-btn-apply');
    applyButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const card = this.closest('.cv2-coupon');
            const title = card ? card.querySelector('.cv2-coupon__title').innerText : 'Offer';
            
            // Loading state
            const originalText = this.innerText;
            this.innerText = 'Applying...';
            this.disabled = true;

            fetch('<?php echo BASE_URL; ?>api/cart/apply-coupon.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ coupon: title })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Success feedback
                    this.innerText = 'Applied';
                    this.style.background = '#166534'; // Green
                    this.style.color = '#fff';
                    
                    Swal.fire({
                        title: 'Coupon Applied!',
                        text: data.message,
                        icon: 'success',
                        confirmButtonColor: '#7b1d1d'
                    });

                    // Update UI Totals
                    updateTotalsUI(data);
                } else {
                    this.innerText = originalText;
                    this.disabled = false;
                    Swal.fire({
                        title: 'Error',
                        text: data.message,
                        icon: 'error',
                        confirmButtonColor: '#7b1d1d'
                    });
                }
            })
            .catch(err => {
                console.error(err);
                this.innerText = originalText;
                this.disabled = false;
            });
        });
    });

    function updateTotalsUI(data) {
        const formatter = new Intl.NumberFormat('en-IN');
        
        // 1. Order Summary section
        const summaryTotalLines = document.querySelectorAll('.cv2-totals-line span:last-child');
        if (summaryTotalLines.length >= 4) {
            summaryTotalLines[0].innerText = '₹' + formatter.format(data.subtotal);
            summaryTotalLines[1].innerText = '₹' + formatter.format(data.shipping);
            summaryTotalLines[2].innerText = '-₹' + formatter.format(data.discount);
            summaryTotalLines[3].innerText = '₹' + formatter.format(data.total);
        }

        // 2. Total Payable Amount card
        const payableMain = document.querySelector('.cv2-payable__main');
        const payableStrike = document.querySelector('.cv2-payable__strike');
        if (payableMain) payableMain.innerText = '₹' + formatter.format(data.total);
        if (payableStrike) payableStrike.innerText = '₹' + formatter.format(data.subtotal + data.shipping);

        // 3. Savings tag
        let savingsTag = document.querySelector('.cv2-savings-tag');
        if (data.discount > 0) {
            if (!savingsTag) {
                savingsTag = document.createElement('div');
                savingsTag.className = 'cv2-savings-tag';
                document.querySelector('.cv2-card.mb-4').appendChild(savingsTag);
            }
            savingsTag.innerHTML = `<i class="bi bi-check2-square"></i> You saved ₹${formatter.format(data.discount)} on this order`;
        } else if (savingsTag) {
            savingsTag.remove();
        }

        // 4. Pay Button
        const payBtn = document.getElementById('payBtn');
        if (payBtn) {
            const currentText = payBtn.innerText;
            const methodLabel = currentText.includes('via') ? currentText.split('via')[1].trim() : 'UPI';
            payBtn.innerText = `Pay ₹${formatter.format(data.total)} via ${methodLabel}`;
        }
    }

    // Payment method selection for V2
    const paymentRows = document.querySelectorAll('.cv2-payment-row, .cv2-upi-section');
    const methodInput = document.getElementById('selectedPaymentMethod');
    const payBtn = document.getElementById('payBtn');

    paymentRows.forEach(row => {
        row.addEventListener('click', function() {
            const method = this.getAttribute('data-method') || 'upi';

            if (methodInput) methodInput.value = method;
            
            // Highlight selected row
            paymentRows.forEach(r => r.classList.remove('active-method'));
            this.classList.add('active-method');
            
            // Update button text
            if (payBtn) {
                const amount = '<?php echo number_format($total); ?>';
                const methodLabel = method === 'upi' ? 'UPI' : (method === 'card' ? 'CARD' : method.toUpperCase());
                payBtn.innerText = `Pay ₹${amount} via ${methodLabel}`;
            }
        });
    });
});
</script>
<style>
.cv2-payment-row, .cv2-upi-section {
    cursor: pointer;
    transition: all 0.2s ease;
    border: 1px solid #eee;
    border-radius: 12px;
    margin-bottom: 15px;
    padding: 15px;
}
.cv2-payment-row:hover, .cv2-upi-section:hover {
    border-color: #7b1d1d;
    background-color: rgba(123, 29, 29, 0.02);
}
.active-method {
    border-color: #7b1d1d !important;
    background-color: rgba(123, 29, 29, 0.05) !important;
    box-shadow: 0 4px 12px rgba(123, 29, 29, 0.08);
}
</style>
</body>
</html>
