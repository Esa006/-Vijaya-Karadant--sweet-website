<?php
require_once 'config/config.php';
require_once SERVICES_PATH . '/CartService.php';
require_once SERVICES_PATH . '/CustomerService.php';
require_once __DIR__ . '/src/Validator/CheckoutValidator.php';

// Flipkart-style Auth Redirect
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'customer') {
    header('Location: login.php?redirect=checkout.php');
    exit;
}

use App\Validator\CheckoutValidator;

$userId = (int)$_SESSION['user_id'];
$customerService = new CustomerService();
$profileData = $customerService->getProfileData($userId);
$user = $profileData['profile'] ?? null;

$cartService = new CartService();
$cartItems = $cartService->getItems();

// Stock Validation: Prevent checkout if any item is out of stock
$hasStockIssue = false;
foreach ($cartItems as $item) {
    if (isset($item['error'])) {
        $hasStockIssue = true;
        break;
    }
}

if ($hasStockIssue) {
    $_SESSION['cart_error'] = 'One or more items in your cart are out of stock. Please remove them to proceed.';
    header('Location: shopping-cart.php');
    exit;
}

$subtotal  = $cartService->getSubtotal();
$shipping  = $cartService->getShippingCharges();
$discount  = $subtotal > 1500 ? 100 : 0;
$total     = $cartService->getTotal() - $discount;

// ✅ Initialize variables
$errors = [];
$formData = $_SESSION['checkout_data'] ?? [];

// Pre-fill from profile if empty
if (empty($formData) && $user) {
    $nameParts = explode(' ', $user['name'], 2);
    $formData['email'] = $user['email'];
    $formData['first_name'] = $nameParts[0] ?? '';
    $formData['last_name'] = $nameParts[1] ?? '';
    $formData['phone'] = $user['phone'];
    
    // Also try to pre-fill from default address if exists
    if (!empty($profileData['addresses'])) {
        $selectedAddr = null;
        foreach ($profileData['addresses'] as $addr) {
            if ($addr['is_default']) {
                $selectedAddr = $addr;
                break;
            }
        }
        
        // Fallback: if no default address is found, pick the most recent one
        if (!$selectedAddr) {
            $selectedAddr = end($profileData['addresses']);
        }
        
        if ($selectedAddr) {
            $formData['address'] = $selectedAddr['address_line1'] . (!empty($selectedAddr['address_line2']) ? ', ' . $selectedAddr['address_line2'] : '');
            $formData['city'] = $selectedAddr['city'];
            $formData['state'] = $selectedAddr['state'];
            $formData['pin_code'] = $selectedAddr['zip_code'];
        }
    }
}

// ✅ Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once SERVICES_PATH . '/CacheService.php';
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    if (!RateLimiter::check("rate:checkout:{$ip}", 10, 60)) {
        $errors['general'] = 'Too many checkout attempts. Please try again in a minute.';
        $formData = $_POST;
    } else {
        try {
            $validator = new CheckoutValidator();
            $validatedData = $validator->validate($_POST);
            
            // Handle Newsletter Opt-In from checkout checkbox
            if (!empty($_POST['email_subscribe'])) {
                try {
                    $subEmail = trim((string)($validatedData['email'] ?? $_POST['email'] ?? ''));
                    if ($subEmail && filter_var($subEmail, FILTER_VALIDATE_EMAIL)) {
                        $db = Database::getInstance();
                        $stmtSub = $db->prepare(
                            "INSERT INTO newsletter_subscribers (email, source, is_active)
                             VALUES (:email, 'checkout', 1)
                             ON DUPLICATE KEY UPDATE is_active = 1, source = 'checkout'"
                        );
                        $stmtSub->execute([':email' => $subEmail]);
                    }
                } catch (Throwable $subEx) {
                    error_log('[Checkout] Newsletter subscribe failed: ' . $subEx->getMessage());
                    // Non-fatal — don't block checkout
                }
            }

            // If validation passes, redirect to payment
            $_SESSION['checkout_data'] = $validatedData;
            header('Location: checkout-v2.php');
            exit;
            
        } catch (InvalidArgumentException $e) {
            $errors = json_decode($e->getMessage(), true);
            $formData = $_POST; // Keep entered data on error
        } catch (Exception $e) {
            $errors['general'] = 'An unexpected error occurred. Please try again.';
        }
    }
}

if (empty($cartItems)) {
    header('Location: shopping-cart.php');
    exit;
}

$seoContext = [
    'title' => 'Secure Checkout | ' . SITE_NAME,
    'description' => 'Complete your secure purchase at ' . SITE_NAME . '. Fast pan-India delivery for authentic traditional sweets.',
    'canonical' => BASE_URL . 'checkout.php',
    'type' => 'website'
];
require_once 'includes/header.php';
?>

<!-- Custom Checkout Styles -->
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/pages/checkout.css?v=<?php echo SITE_VERSION; ?>">

<main class="c-checkout py-5 u-bg-warm">
    <div class="container py-lg-4">
        <h1 class="visually-hidden">Secure Checkout</h1>
        <form method="POST" action="checkout.php" class="needs-validation" novalidate>
            <div class="row g-5">
                
                <!-- Left Column: Detailed Forms -->
                <div class="col-lg-7">
                    
                    <?php if (isset($errors['general'])): ?>
                    <div class="alert alert-danger"><?php echo $errors['general']; ?></div>
                    <?php endif; ?>

                    <!-- Contact Information -->
                    <section class="mb-5">
                        <h2 class="c-checkout__header-title">Contact Information</h2>
                        <div class="mb-3">
                            <input type="email" name="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" 
                                   required
                                   placeholder="Enter email" value="<?php echo htmlspecialchars($formData['email'] ?? ''); ?>">
                            <?php if (isset($errors['email'])): ?>
                                <div class="invalid-feedback"><?php echo $errors['email']; ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="emailOffers" name="email_subscribe" value="1" checked>
                            <label class="form-check-label small text-muted ms-2" for="emailOffers">
                                Email me with news and exclusive offers
                            </label>
                        </div>
                    </section>

                    <!-- Shipping Address -->
                    <section class="mb-5">
                        <h2 class="c-checkout__header-title">Shipping Address</h2>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="small text-muted mb-1">Country/Region</label>
                                <select class="form-select" name="country" id="shippingCountry" required>
                                    <option value="India" <?php echo ($formData['country'] ?? 'India') === 'India' ? 'selected' : ''; ?>>India</option>
                                    <option value="United States" <?php echo ($formData['country'] ?? '') === 'United States' ? 'selected' : ''; ?>>United States</option>
                                    <option value="United Kingdom" <?php echo ($formData['country'] ?? '') === 'United Kingdom' ? 'selected' : ''; ?>>United Kingdom</option>
                                    <option value="Canada" <?php echo ($formData['country'] ?? '') === 'Canada' ? 'selected' : ''; ?>>Canada</option>
                                    <option value="Australia" <?php echo ($formData['country'] ?? '') === 'Australia' ? 'selected' : ''; ?>>Australia</option>
                                    <option value="Other" <?php echo ($formData['country'] ?? '') === 'Other' ? 'selected' : ''; ?>>Other (International)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <input type="text" name="first_name" class="form-control <?php echo isset($errors['first_name']) ? 'is-invalid' : ''; ?>" 
                                       required
                                       placeholder="First Name" value="<?php echo htmlspecialchars($formData['first_name'] ?? ''); ?>">
                                <?php if (isset($errors['first_name'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['first_name']; ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <input type="text" name="last_name" class="form-control <?php echo isset($errors['last_name']) ? 'is-invalid' : ''; ?>" 
                                       required
                                       placeholder="Last Name" value="<?php echo htmlspecialchars($formData['last_name'] ?? ''); ?>">
                                <?php if (isset($errors['last_name'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['last_name']; ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="col-12">
                                <input type="text" name="address" class="form-control <?php echo isset($errors['address']) ? 'is-invalid' : ''; ?>" 
                                       required
                                       placeholder="Address" value="<?php echo htmlspecialchars($formData['address'] ?? ''); ?>">
                                <?php if (isset($errors['address'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['address']; ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <input type="text" name="city" class="form-control <?php echo isset($errors['city']) ? 'is-invalid' : ''; ?>" 
                                       required
                                       placeholder="City" value="<?php echo htmlspecialchars($formData['city'] ?? ''); ?>">
                                <?php if (isset($errors['city'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['city']; ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <input type="text" name="state" class="form-control <?php echo isset($errors['state']) ? 'is-invalid' : ''; ?>" 
                                       required
                                       placeholder="State" value="<?php echo htmlspecialchars($formData['state'] ?? ''); ?>">
                                <?php if (isset($errors['state'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['state']; ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <input type="text" name="pin_code" class="form-control <?php echo isset($errors['pin_code']) ? 'is-invalid' : ''; ?>" 
                                       required pattern="\d{6}" maxlength="10"
                                       placeholder="PIN Code" value="<?php echo htmlspecialchars($formData['pin_code'] ?? ''); ?>">
                                <?php if (isset($errors['pin_code'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['pin_code']; ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <input type="tel" name="phone" class="form-control <?php echo isset($errors['phone']) ? 'is-invalid' : ''; ?>" 
                                       required pattern="[6-9]\d{9}" maxlength="10"
                                       placeholder="Phone Number" value="<?php echo htmlspecialchars($formData['phone'] ?? ''); ?>">
                                <?php if (isset($errors['phone'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['phone']; ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="col-12 mt-4">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="save_info" id="saveInfo" value="1" checked>
                                    <label class="form-check-label small text-muted ms-2" for="saveInfo">Save this information for next time</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="marketing_opt_in" id="textOffers" value="1" <?php echo ($user && $user['marketing_opt_in']) ? 'checked' : ''; ?>>
                                    <label class="form-check-label small text-muted ms-2" for="textOffers">Text me with news and offers</label>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Content Example / Estimates -->
                    <section class="mb-5">
                        <h2 class="c-checkout__header-title">Content example</h2>
                        <div class="c-checkout__info-block">
                            <h5 class="fw-bold mb-1">Estimated Delivery : Within 7 Days</h5>
                            <p class="small text-muted mb-0">Our sweets are freshly prepared, so instant delivery is not available</p>
                        </div>
                    </section>

                    <!-- Shipping Method -->
                    <section class="mb-5">
                        <h2 class="c-checkout__header-title">Shipping Method</h2>
                        <div class="c-checkout__info-block" style="border-style: solid; border-color: #d67a1866;">
                            <h5 class="fw-bold mb-2">Awaiting Address Information</h5>
                            <p class="small text-muted mb-0">Please enter your shipping address above to view available shipping methods and delivery timelines for your region.</p>
                        </div>
                    </section>

                    <!-- Payment Method -->
                    <section class="mb-5">
                        <h2 class="c-checkout__header-title">Payment Method</h2>
                        <input type="hidden" name="payment_method" id="selectedPaymentMethod" value="upi">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="c-method-card active" data-method="upi">
                                    <i class="bi bi-lightning-charge text-warning"></i>
                                    <div>
                                        <h6 class="fw-bold mb-0" style="font-size: 0.9rem;">UPI (Google Pay, PhonePe)</h6>
                                        <p class="small text-muted mb-0" style="font-size: 0.7rem;">Instant payment using any UPI app</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="c-method-card" data-method="card">
                                    <i class="bi bi-credit-card text-warning"></i>
                                    <div>
                                        <h6 class="fw-bold mb-0" style="font-size: 0.9rem;">Credit / Debit Card</h6>
                                        <p class="small text-muted mb-0" style="font-size: 0.7rem;">Visa, Mastercard, RuPay supported</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="c-method-card" data-method="netbanking">
                                    <i class="bi bi-bank text-warning"></i>
                                    <div>
                                        <h6 class="fw-bold mb-0" style="font-size: 0.9rem;">Net Banking</h6>
                                        <p class="small text-muted mb-0" style="font-size: 0.7rem;">Support for all major Indian banks</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="c-method-card" data-method="cod">
                                    <i class="bi bi-cash-stack text-warning"></i>
                                    <div>
                                        <h6 class="fw-bold mb-0" style="font-size: 0.9rem;">Cash on Delivery</h6>
                                        <p class="small text-muted mb-0" style="font-size: 0.7rem;">Pay when your order arrives</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Delivery Method -->
                    <section class="mb-5">
                        <h2 class="c-checkout__header-title">Delivery Method</h2>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="c-method-card active justify-content-between">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-truck text-warning"></i>
                                        <div>
                                            <h6 class="fw-bold mb-0" style="font-size: 0.9rem;">Standard Delivery</h6>
                                            <p class="small text-muted mb-0" style="font-size: 0.7rem;">3-5 Business Days</p>
                                        </div>
                                    </div>
                                    <span class="fw-bold">₹<?php echo number_format(SHIPPING_RATES['standard']); ?></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="c-method-card justify-content-between">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-lightning text-warning"></i>
                                        <div>
                                            <h6 class="fw-bold mb-0" style="font-size: 0.9rem;">Express Delivery</h6>
                                            <p class="small text-muted mb-0" style="font-size: 0.7rem;">Next Day Delivery</p>
                                        </div>
                                    </div>
                                    <span class="fw-bold">₹<?php echo number_format(SHIPPING_RATES['express']); ?></span>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Action Button -->
                    <div class="mt-4">
                        <button type="button" id="payBtn" class="btn btn-place-order w-100">Pay Now</button>
                    </div>

                    <!-- Footer Links -->
                    <div class="mt-5 pt-4 border-top text-center c-checkout-footer-links">
                        <a href="<?php echo BASE_URL; ?>return-refund-policy.php">Refund Policy</a>
                        <a href="<?php echo BASE_URL; ?>shipping-policy.php">Shipping</a>
                        <a href="<?php echo BASE_URL; ?>privacy-policy.php">Privacy Policy</a>
                        <a href="<?php echo BASE_URL; ?>terms-and-conditions.php">Terms of service</a>
                    </div>
                </div>

            <!-- Right Column: Order Summary (Sticky) -->
            <div class="col-lg-5">
                <div class="sticky-top" style="top: 100px;">
                    <h2 class="c-summary__title h3">Your Order (<?php echo count($cartItems); ?>)</h2>
                    
                    <div style="max-height: 400px; overflow-y: auto; padding-right: 10px; margin-bottom: 20px;">
                        <?php foreach ($cartItems as $item): ?>
                        <!-- Item -->
                        <div class="d-flex gap-3 mb-4 align-items-center">
                            <div class="c-summary__item-img">
                                <img src="<?php echo htmlspecialchars((string)($item['image'] ?? 'assets/images/placeholders/product-placeholder.png')); ?>" alt="item" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px; border: 1px solid #eee;">
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="fw-bold mb-0" style="font-size:0.95rem;"><?php echo htmlspecialchars((string)($item['name'] ?? 'Combo Item')); ?> (x<?php echo $item['quantity']; ?>)</h6>
                                <p class="small text-muted mb-0">Weight : <?php echo htmlspecialchars((string)($item['weight'] ?? 'N/A')); ?></p>
                            </div>
                            <span class="fw-bold">₹<?php echo number_format($item['price'] * $item['quantity']); ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Discount Code -->
                    <div class="input-group mb-4">
                        <input type="text" class="form-control" placeholder="Discount code or gift card">
                        <button class="btn btn-dark px-4">Apply</button>
                    </div>

                    <!-- Totals -->
                    <div class="border-bottom pb-3 mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Subtotal</span>
                            <span class="fw-bold">₹<?php echo number_format($subtotal); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Delivery Charges</span>
                            <span class="fw-bold">₹<?php echo number_format($shipping); ?></span>
                        </div>
                        <?php if ($discount > 0): ?>
                        <div class="d-flex justify-content-between mb-2 text-dark">
                            <span class="text-muted">Discount Applied</span>
                            <span class="fw-bold">-₹<?php echo number_format($discount); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <span class="h4 fw-bold mb-0">Total Amount</span>
                        <span class="h4 fw-bold mb-0" style="color:#7b1d1d;">₹<?php echo number_format($total); ?></span>
                    </div>

                    <!-- Trust Strip -->
                    <div class="c-summary-trust border-top pt-4">
                        <div>
                            <i class="bi bi-shield-check"></i>
                            <span>Secure Payment</span>
                        </div>
                        <div>
                            <i class="bi bi-patch-check"></i>
                            <span>Authentic Sweets</span>
                        </div>
                        <div>
                            <i class="bi bi-truck"></i>
                            <span>Fast Delivery</span>
                        </div>
                    </div>
                </div>
            </div>

            </div>
        </form>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>

<!-- ========== UPI QR PAYMENT MODAL ========== -->
<div class="modal fade" id="upiPaymentModal" tabindex="-1" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered" style="max-width:440px">
    <div class="modal-content border-0 rounded-4 overflow-hidden shadow-lg">

      <!-- Header -->
      <div class="d-flex align-items-center justify-content-between px-4 py-3" style="background:#7a1f1f;">
        <div class="d-flex align-items-center gap-2">
          <span style="font-size:1.4rem;">🔒</span>
          <span class="fw-bold text-white" style="font-size:1rem;">UPI Secure Payment</span>
        </div>
        <span class="badge text-white border border-white" style="font-size:.7rem;opacity:.85;">DEMO ENV</span>
      </div>

      <div class="modal-body p-0">

        <!-- ── SCANNING SCREEN ── -->
        <div id="upi-scanning">
          <div class="text-center px-4 pt-4 pb-2">
            <p class="text-muted small mb-1">Open any UPI app and scan</p>
            <h5 class="fw-bold mb-0" style="color:#7a1f1f;">Amount: <span class="upi-amount-display"></span></h5>
          </div>

          <!-- QR + ring timer -->
          <div class="d-flex justify-content-center align-items-center position-relative my-3" style="height:230px;">
            <svg style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);" width="230" height="230">
              <circle cx="115" cy="115" r="110" fill="none" stroke="#f5e6e6" stroke-width="5"/>
              <circle id="upi-timer-ring" cx="115" cy="115" r="110" fill="none" stroke="#7a1f1f" stroke-width="5"
                stroke-dasharray="691" stroke-dashoffset="0"
                transform="rotate(-90 115 115)" style="transition:stroke-dashoffset .9s linear;"/>
            </svg>
            <div id="upi-qr-container" style="border-radius:16px;padding:8px;background:#fff;box-shadow:0 4px 20px rgba(0,0,0,.12);z-index:1;">
              <div style="width:200px;height:200px;background:#f5f5f5;border-radius:10px;display:flex;align-items:center;justify-content:center;">
                <div class="spinner-border" style="color:#7a1f1f;"></div>
              </div>
            </div>
          </div>

          <!-- Timer + UPI ID -->
          <div class="text-center pb-3">
            <div class="fw-bold" style="font-size:1.5rem;color:#7a1f1f;font-variant-numeric:tabular-nums;" id="upi-timer">05:00</div>
            <div class="text-muted small mt-1">QR expires in ↑ | UPI ID: <strong id="upi-display-id">demo@upi</strong></div>
          </div>

          <!-- UPI App logos -->
          <div class="d-flex justify-content-center gap-3 pb-3">
            <div class="text-center" style="font-size:.65rem;color:#888">
              <div style="width:36px;height:36px;background:#1a73e8;border-radius:50%;margin:0 auto 4px;display:flex;align-items:center;justify-content:center;"><span style="color:#fff;font-size:1rem;">G</span></div>Google Pay
            </div>
            <div class="text-center" style="font-size:.65rem;color:#888">
              <div style="width:36px;height:36px;background:#5f259f;border-radius:50%;margin:0 auto 4px;display:flex;align-items:center;justify-content:center;"><span style="color:#fff;font-size:.9rem;">P</span></div>PhonePe
            </div>
            <div class="text-center" style="font-size:.65rem;color:#888">
              <div style="width:36px;height:36px;background:#00baf2;border-radius:50%;margin:0 auto 4px;display:flex;align-items:center;justify-content:center;"><span style="color:#fff;font-size:.9rem;">P</span></div>Paytm
            </div>
            <div class="text-center" style="font-size:.65rem;color:#888">
              <div style="width:36px;height:36px;background:#f37024;border-radius:50%;margin:0 auto 4px;display:flex;align-items:center;justify-content:center;"><span style="color:#fff;font-size:.9rem;">B</span></div>BHIM
            </div>
          </div>

          <!-- Demo Controls -->
          <div class="border-top px-4 py-3" style="background:#fdf8f6;">
            <p class="text-muted mb-2" style="font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;font-weight:700;">🎮 Demo Controls</p>
            <div class="d-flex gap-2">
              <button type="button" onclick="UpiPayment.force('success')" class="btn btn-sm btn-success rounded-pill px-3 flex-fill">✅ Force Success</button>
              <button type="button" onclick="UpiPayment.force('failed')" class="btn btn-sm btn-danger rounded-pill px-3 flex-fill">❌ Force Fail</button>
              <button type="button" onclick="UpiPayment.regenerate()" class="btn btn-sm btn-outline-secondary rounded-pill px-3 flex-fill">🔄 Regen QR</button>
            </div>
          </div>
        </div>

        <!-- ── PENDING SCREEN ── -->
        <div id="upi-pending" style="display:none;">
          <div class="text-center px-4 py-5">
            <div class="mb-4" style="font-size:3rem;">⏳</div>
            <h5 class="fw-bold mb-2">Waiting for payment…</h5>
            <p class="text-muted small mb-4">We're checking with your bank. This may take a moment.</p>
            <div class="d-flex justify-content-center gap-2 mb-3">
              <span class="upi-dot" style="animation-delay:0s"></span>
              <span class="upi-dot" style="animation-delay:.2s"></span>
              <span class="upi-dot" style="animation-delay:.4s"></span>
            </div>
            <div class="fw-bold" style="color:#7a1f1f;">Amount: <span class="upi-amount-display"></span></div>
          </div>
        </div>

        <!-- ── SUCCESS SCREEN ── -->
        <div id="upi-success" style="display:none;">
          <div class="text-center px-4 py-5">
            <div class="upi-success-icon">✓</div>
            <h4 class="fw-bold mt-3 mb-1" style="color:#198754;">Payment Successful!</h4>
            <p class="text-muted small mb-1"><span class="upi-amount-display"></span> paid via UPI</p>
            <div class="my-3 p-3 rounded-3" style="background:#d1e7dd;">
              <div class="text-muted" style="font-size:.75rem;">Transaction ID</div>
              <div class="fw-bold" style="color:#0a3622;letter-spacing:.05em;" id="upi-success-txn">—</div>
            </div>
            <p class="text-muted small">Redirecting to your order… <span class="spinner-border spinner-border-sm ms-1"></span></p>
          </div>
        </div>

        <!-- ── FAILED SCREEN ── -->
        <div id="upi-failed" style="display:none;">
          <div class="text-center px-4 py-5">
            <div style="font-size:3.5rem;">❌</div>
            <h4 class="fw-bold mt-3 mb-1" style="color:#dc3545;">Payment Failed</h4>
            <div class="my-3 p-3 rounded-3" style="background:#f8d7da;">
              <div class="text-muted small" style="color:#842029;" id="upi-failed-reason">Payment was not completed.</div>
            </div>
            <div class="d-flex gap-2 justify-content-center mt-3">
              <button type="button" onclick="UpiPayment.regenerate()" class="btn rounded-pill px-4 fw-bold text-white" style="background:#7a1f1f;">🔄 Try Again</button>
              <button type="button" data-bs-dismiss="modal" class="btn btn-outline-secondary rounded-pill px-4">Change Method</button>
            </div>
          </div>
        </div>

        <!-- ── EXPIRED SCREEN ── -->
        <div id="upi-expired" style="display:none;">
          <div class="text-center px-4 py-5">
            <div style="font-size:3rem;">⌛</div>
            <h4 class="fw-bold mt-3 mb-1" style="color:#fd7e14;">QR Code Expired</h4>
            <p class="text-muted small mb-4">The QR code has expired. Please generate a new one.</p>
            <button type="button" onclick="UpiPayment.regenerate()" class="btn rounded-pill px-4 fw-bold text-white" style="background:#7a1f1f;">🔄 Generate New QR</button>
          </div>
        </div>

      </div><!-- /modal-body -->

      <!-- Disclaimer -->
      <div class="text-center py-2" style="background:#fff3cd;font-size:.7rem;color:#664d03;">
        🔐 Demo Payment Environment — No real money transfer occurs
      </div>
    </div>
  </div>
</div>

<style>
.upi-success-icon {
  width: 70px; height: 70px; border-radius: 50%;
  background: linear-gradient(135deg,#198754,#20c997);
  color: #fff; font-size: 2.2rem; font-weight: 900;
  display: inline-flex; align-items: center; justify-content: center;
  animation: upi-pop .5s cubic-bezier(.36,.07,.19,.97);
}
@keyframes upi-pop { 0%{transform:scale(0);opacity:0} 80%{transform:scale(1.15)} 100%{transform:scale(1);opacity:1} }
.upi-dot {
  width:10px; height:10px; border-radius:50%; background:#7a1f1f; display:inline-block;
  animation: upi-bounce 1s infinite;
}
@keyframes upi-bounce { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-10px)} }
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
// Pass PHP total to JS
window._upiAmount = <?= json_encode($total) ?>;
</script>
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script src="<?php echo BASE_URL; ?>assets/js/pages/checkout-validation.js?v=<?php echo SITE_VERSION; ?>"></script>
<script src="<?php echo BASE_URL; ?>assets/js/pages/upi-payment.js?v=<?php echo SITE_VERSION; ?>"></script>
<script src="<?php echo BASE_URL; ?>assets/js/pages/checkout-razorpay.js?v=<?php echo SITE_VERSION; ?>"></script>
