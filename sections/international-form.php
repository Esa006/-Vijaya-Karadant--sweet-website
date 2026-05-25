<?php
/**
 * Sweets Website
 * =============================================================
 * File: international-form.php
 * Description: Detailed order form for international shipments
 * =============================================================
 */
?>

<section id="international-form" class="c-international-form py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="background-color: #fdfdfd;">
                    <div class="card-body p-4 p-md-5">
                        <form action="checkout.php" method="POST" id="internationalOrderForm" class="needs-validation" novalidate>
                            
                            <!-- Header -->
                            <div class="text-center mb-5">
                                <h2 class="u-ff-heading fw-bold" style="color: #7b1d1d;">For International Customisation</h2>
                                <p class="text-muted">Fill the form below and our team will contact you for shipping details.</p>
                            </div>

                            <!-- Contact Information -->
                            <div class="mb-5">
                                <h5 class="fw-bold mb-3" style="color: #7b1d1d;">Contact Information</h5>
                                <div class="c-form-group">
                                    <input type="email" name="email" class="form-control" placeholder="Enter email" required>
                                    <div class="invalid-feedback px-2">Please enter a valid email address.</div>
                                </div>
                                <div class="form-check mt-3">
                                    <input class="form-check-input" type="checkbox" name="marketing_opt_in" id="news-offers" value="1">
                                    <label class="form-check-label small text-muted" for="news-offers">
                                        Email me with news and exclusive offers
                                    </label>
                                </div>
                            </div>

                            <!-- Shipping Address -->
                            <div class="mb-5">
                                <h5 class="fw-bold mb-3" style="color: #7b1d1d;">Shipping Address</h5>
                                <div class="row g-3">
                                    <div class="col-12">
                                        <div class="c-form-group">
                                            <label class="small text-muted mb-1 px-2">Country/Region</label>
                                            <select class="form-select" name="country" id="shippingCountry">
                                                <option value="India" selected>India</option>
                                                <option value="United States">United States</option>
                                                <option value="United Kingdom">United Kingdom</option>
                                                <option value="Canada">Canada</option>
                                                <option value="Australia">Australia</option>
                                                <option value="Other">Other (International)</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="c-form-group">
                                            <input type="text" name="first_name" class="form-control" placeholder="First Name" required>
                                            <div class="invalid-feedback px-2">First name is required.</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="c-form-group">
                                            <input type="text" name="last_name" class="form-control" placeholder="Last Name" required>
                                            <div class="invalid-feedback px-2">Last name is required.</div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="c-form-group">
                                            <input type="text" name="address" class="form-control" placeholder="Address" required>
                                            <div class="invalid-feedback px-2">Please enter your full address.</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="c-form-group">
                                            <input type="text" name="city" class="form-control" placeholder="City" required>
                                            <div class="invalid-feedback px-2">City is required.</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="c-form-group">
                                            <input type="text" name="state" class="form-control" placeholder="State" required>
                                            <div class="invalid-feedback px-2">State is required.</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="c-form-group">
                                            <input type="text" name="pin_code" class="form-control" placeholder="PIN Code" required>
                                            <div class="invalid-feedback px-2">Postal code is required.</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="c-form-group">
                                            <input type="tel" name="phone" class="form-control" placeholder="Phone Number" required>
                                            <div class="invalid-feedback px-2">Valid phone number is required.</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="save_info" id="save-info" value="1">
                                        <label class="form-check-label small text-muted" for="save-info">Save this information for next time</label>
                                    </div>
                                </div>
                            </div>

                            <!-- Content Example / Info Box -->
                            <div class="mb-5">
                                <h5 class="fw-bold mb-3" style="color: #7b1d1d;">Delivery Estimate</h5>
                                <div class="p-4 rounded-3 text-center border" style="background-color: #fdfaf5; border-color: #e6a371 !important;">
                                    <h6 class="fw-bold mb-1" id="deliveryEstimateHeader">Estimated Delivery : 3–4 Working Days</h6>
                                    <p class="small text-muted mb-0">Our sweets are freshly prepared, so instant delivery is not available</p>
                                </div>
                            </div>

                            <!-- Shipping Method -->
                            <div class="mb-5">
                                <h5 class="fw-bold mb-3" style="color: #7b1d1d;">Shipping Method</h5>
                                <div id="shippingMethodBox" class="p-4 rounded-3 text-center border mb-3" style="background-color: #fdfaf5; border-color: #e6a371 !important;">
                                    <h6 class="fw-bold mb-1">Awaiting Address Information</h6>
                                    <p class="small text-muted mb-0">Please enter your shipping address above to view available shipping methods and delivery timelines for your region.</p>
                                </div>
                                <div id="shippingOptions" class="d-none">
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label class="c-option-card">
                                                <input type="radio" name="delivery" value="standard" checked>
                                                <div class="c-option-card__inner d-flex justify-content-between align-items-center">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <i class="bi bi-truck text-danger"></i>
                                                        <div>
                                                            <div class="fw-bold small">Standard International</div>
                                                            <div class="u-fs-xs text-muted">7-10 Business Days</div>
                                                        </div>
                                                    </div>
                                                    <div class="fw-bold small">Calculated at checkout</div>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Payment Method -->
                            <div class="mb-5">
                                <h5 class="fw-bold mb-3" style="color: #7b1d1d;">Payment Method</h5>
                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label class="c-option-card">
                                            <input type="radio" name="payment_method" value="upi" checked>
                                            <div class="c-option-card__inner">
                                                <div class="d-flex align-items-center gap-2">
                                                    <i class="bi bi-phone text-danger"></i>
                                                    <div>
                                                        <div class="fw-bold small">UPI (Google Pay, PhonePe)</div>
                                                        <div class="u-fs-xs text-muted">Instant payment using any UPI app</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="c-option-card">
                                            <input type="radio" name="payment_method" value="card">
                                            <div class="c-option-card__inner">
                                                <div class="d-flex align-items-center gap-2">
                                                    <i class="bi bi-credit-card text-danger"></i>
                                                    <div>
                                                        <div class="fw-bold small">Credit / Debit Card</div>
                                                        <div class="u-fs-xs text-muted">Visa, Mastercard, RuPay supported</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit -->
                            <div class="text-center">
                                <button type="submit" class="btn btn-lg w-100 text-white rounded-3 shadow py-3 fw-bold" style="background: linear-gradient(90deg, #7b1d1d 0%, #d67a18 100%);">Proceed to Custom Checkout</button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('internationalOrderForm');
    const shippingBox = document.getElementById('shippingMethodBox');
    const shippingOptions = document.getElementById('shippingOptions');
    const estimateHeader = document.getElementById('deliveryEstimateHeader');
    const countrySelect = document.getElementById('shippingCountry');
    
    // Inputs to track for dynamic updates
    const addressInputs = form.querySelectorAll('input[name="address"], input[name="city"], input[name="pin_code"]');
    
    function updateShippingMethod() {
        let allFilled = true;
        addressInputs.forEach(input => {
            if (!input.value.trim()) allFilled = false;
        });
        
        if (allFilled) {
            shippingBox.classList.add('d-none');
            shippingOptions.classList.remove('d-none');
        } else {
            shippingBox.classList.remove('d-none');
            shippingOptions.classList.add('d-none');
        }
    }
    
    addressInputs.forEach(input => {
        input.addEventListener('input', updateShippingMethod);
    });
    
    countrySelect.addEventListener('change', function() {
        if (this.value === 'India') {
            estimateHeader.textContent = 'Estimated Delivery : 3–4 Working Days';
        } else {
            estimateHeader.textContent = 'Estimated Delivery : 7–12 Working Days';
        }
    });

    // Bootstrap Validation
    form.addEventListener('submit', function (event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        form.classList.add('was-validated');
    }, false);
});
</script>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.c-international-form .c-form-group {
    background-color: #fdfaf5;
    border: 1px solid #e6a371;
    border-radius: 8px;
    padding: 0.5rem;
}

.c-international-form .form-control, 
.c-international-form .form-select {
    background: transparent;
    border: none;
    box-shadow: none;
    padding: 0.5rem 1rem;
    font-size: 0.95rem;
}

.c-international-form .form-control::placeholder {
    color: #999;
}

.c-option-card {
    display: block;
    cursor: pointer;
    width: 100%;
}

.c-option-card input {
    display: none;
}

.c-option-card__inner {
    background-color: #fdfaf5;
    border: 1px solid #e6a371;
    border-radius: 8px;
    padding: 1rem;
    transition: all 0.2s ease;
}

.c-option-card input:checked + .c-option-card__inner {
    border-color: #7b1d1d;
    background-color: #fff;
    box-shadow: 0 4px 12px rgba(123, 29, 29, 0.1);
}

.u-fs-xs {
    font-size: 0.75rem;
}
</style>
