<?php
/**
 * Sweets Website
 * =============================================================
 * File: create-offer.php
 * Description: Dedicated standalone page for creating promotional offers.
 * Includes live preview and advanced configuration options.
 * =============================================================
 */

// Define page-specific assets BEFORE including header.php
$pageStyles = ['assets/css/admin/create-offer.css'];
$pageScripts = ['assets/js/admin/create-offer.js'];

require_once 'includes/header.php';
require_once 'includes/auth.php'; // Security check
require_once 'includes/sidebar.php'; 
?>

<div class="main-content">
    <?php require_once 'includes/topbar.php'; ?>

    <div class="content-body pt-0 px-4 pb-5 create-offer-wrapper">
        
        <!-- Sticky Header Bar -->
        <div class="create-offer-header sticky-top bg-white border-bottom shadow-sm mx-n4 px-4" style="z-index: 100;">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <nav aria-label="breadcrumb" class="mb-1">
                        <ol class="breadcrumb mb-0" style="font-size: 12px;">
                            <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none text-muted">Admin</a></li>
                            <li class="breadcrumb-item"><a href="coupons.php" class="text-decoration-none text-muted">Promotions</a></li>
                            <li class="breadcrumb-item active text-muted" aria-current="page">Create Offer</li>
                        </ol>
                    </nav>
                    <h1 class="fs-4 fw-bold text-dark mb-0">Create Offer</h1>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-light border px-4 py-2 small fw-bold" onclick="cancelCreateOffer()">Cancel</button>
                    <button class="btn btn-dark px-4 py-2 small fw-bold btn-save-offer-publish" onclick="saveNewOffer()">Publish Offer</button>
                </div>
            </div>
        </div>

        <!-- Back Navigation -->
        <div class="pt-3 mb-4">
            <a href="coupons.php" class="btn-nav-back">
                <i class="bi bi-reply-fill"></i> Back to Promotions
            </a>
        </div>

        <!-- Main Dashboard Layout -->
        <div class="container-fluid px-0">
            <div class="row g-5">
                
                <!-- LEFT: Configuration Form -->
                <div class="col-lg-7 col-xl-6">
                    
                    <!-- Basic Information -->
                    <h5 class="offer-section-title">Basic Offer Information</h5>
                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label class="offer-field-label">Offer Name</label>
                            <input type="text" class="offer-input-control" id="offerName" placeholder="e.g., Diwali Dhamaka Sale" value="Diwali Dhamaka Sale">
                            <span class="offer-field-hint">Internal name for reporting and dashboard viewing.</span>
                        </div>
                        <div class="col-md-6">
                            <label class="offer-field-label">Coupon Code</label>
                            <input type="text" class="offer-input-control text-uppercase" id="couponCode" placeholder="DIWALI20" value="DIWALI20">
                            <span class="offer-field-hint">Used by customers at checkout to redeem the discount.</span>
                        </div>
                        <div class="col-12">
                            <label class="offer-field-label">Internal Description</label>
                            <textarea class="offer-input-control shadow-none" id="offerDescription" rows="3" placeholder="Campaign goals, target audience, etc.">Festive offer for premium mithai boxes and gift hampers during the Diwali season.</textarea>
                            <span class="offer-field-hint">A brief summary for your operations team to understand the marketing intent.</span>
                        </div>
                    </div>

                    <!-- Discount Logic -->
                    <h5 class="offer-section-title">Discount Details</h5>
                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label class="offer-field-label">Discount Type</label>
                            <select class="offer-input-control" id="discountType">
                                <option value="percentage">Percentage (%)</option>
                                <option value="fixed">Fixed Amount (₹)</option>
                                <option value="free_delivery">Free Delivery</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="offer-field-label">Discount Value</label>
                            <input type="text" class="offer-input-control" id="discountValue" value="20%" placeholder="e.g., 20%">
                        </div>
                        <div class="col-md-6">
                            <label class="offer-field-label">Maximum Discount Cap</label>
                            <input type="text" class="offer-input-control" id="maxDiscount" value="₹ 500" placeholder="₹ 0">
                            <span class="offer-field-hint">Limit the liability if percentage is chosen.</span>
                        </div>
                    </div>

                    <!-- Usage Conditions -->
                    <h5 class="offer-section-title">Redemption Conditions</h5>
                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label class="offer-field-label">Min. Order Value</label>
                            <input type="text" class="offer-input-control" id="minOrderValue" value="₹ 1,000">
                        </div>
                        <div class="col-md-6">
                            <label class="offer-field-label">Usage Limit Per User</label>
                            <input type="text" class="offer-input-control" id="perUserLimit" value="2 uses">
                        </div>
                        <div class="col-md-6">
                            <label class="offer-field-label">Total Pool Limit</label>
                            <input type="text" class="offer-input-control" id="totalUsageLimit" value="500 redemptions">
                        </div>
                    </div>

                    <!-- Validity Period -->
                    <h5 class="offer-section-title">Validity Window</h5>
                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label class="offer-field-label">Launch Date</label>
                            <input type="date" class="offer-input-control" id="startDate" value="2023-10-20">
                        </div>
                        <div class="col-md-6">
                            <label class="offer-field-label">Expiry Date</label>
                            <input type="date" class="offer-input-control" id="expiryDate" value="2023-11-15">
                        </div>
                    </div>

                    <!-- Applicability Scope -->
                    <h5 class="offer-section-title">Promotional Scope</h5>
                    <label class="offer-field-label mb-2">Target Audience/Catalog</label>
                    <div class="offer-radio-group mb-4">
                        <label class="offer-radio-pill active" data-group="scope">
                            <input type="radio" name="scopeRadio" checked class="d-none">
                            <span class="radio-indicator"></span>
                            Specific Categories
                        </label>
                        <label class="offer-radio-pill" data-group="scope">
                            <input type="radio" name="scopeRadio" class="d-none">
                            <span class="radio-indicator"></span>
                            All Products
                        </label>
                        <label class="offer-radio-pill" data-group="scope">
                            <input type="radio" name="scopeRadio" class="d-none">
                            <span class="radio-indicator"></span>
                            Loyalty Members Only
                        </label>
                    </div>

                    <div id="categoryScopeSection">
                        <label class="offer-field-label">Selected Category Targets</label>
                        <div class="offer-category-tags" id="offerCategoryTagsWrap">
                            <span class="offer-category-tag" data-category="Festive Boxes">Festive Boxes <span class="remove-offer-tag bi bi-x" onclick="removeOfferCategory(this)"></span></span>
                            <span class="offer-category-tag" data-category="Gift Packs">Gift Packs <span class="remove-offer-tag bi bi-x" onclick="removeOfferCategory(this)"></span></span>
                            <span class="offer-category-tag" data-category="Premium Mithai">Premium Mithai <span class="remove-offer-tag bi bi-x" onclick="removeOfferCategory(this)"></span></span>
                            <button class="btn btn-sm btn-link text-decoration-none fw-bold p-0 ms-2 add-category-trigger" onclick="addOfferCategory()">+ Add categories</button>
                        </div>
                    </div>

                    <!-- Final Status -->
                    <h5 class="offer-section-title">Publishing Status</h5>
                    <div class="bg-white border rounded-3 p-3 d-flex align-items-center justify-content-between mb-4 shadow-sm border-light">
                        <div>
                            <div class="fw-bold small text-dark">Active on publish</div>
                            <div class="text-muted smaller">The coupon will be available as soon as the start date arrives.</div>
                        </div>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" id="activeOnPublish" checked style="width: 44px; height: 22px;">
                        </div>
                    </div>

                    <div class="offer-success-note mb-4">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>Validation success: Promotional metadata and discount logic have been verified. Checklist updated.</span>
                    </div>

                    <!-- Bottom Action Mirror -->
                    <div class="d-flex gap-3 justify-content-center pt-3 pb-5">
                        <button class="btn btn-light border px-4" onclick="cancelCreateOffer()">Discard Draft</button>
                        <button class="btn btn-dark px-5 py-2 fw-bold btn-save-offer-publish" onclick="saveNewOffer()">Publish Offer</button>
                    </div>

                </div>

                <!-- RIGHT: Live Preview Panel -->
                <div class="col-lg-5">
                    <div class="sticky-preview-panel">
                        <div class="preview-label-text">Live Preview</div>
                        <p class="preview-sub-text">Visualization of how the coupon will appear to customers.</p>

                        <!-- Promo Visualization -->
                        <div class="promo-visual-card">
                            <div class="promo-card-name" id="previewOfferName">Diwali Dhamaka Sale</div>
                            <div class="promo-card-snippet" id="previewDescription">Celebrate with premium mithai box sets and gift hampers at festive prices.</div>
                            <div class="promo-card-discount-badge" id="previewDiscountBadge">20% OFF</div>
                        </div>

                        <!-- Coupon Code -->
                        <div class="text-center mb-4">
                            <div class="coupon-visual-badge shadow-sm" id="previewCouponBadge">DIWALI20</div>
                        </div>

                        <!-- Mini Detail Card -->
                        <div class="bg-white border rounded-4 p-4 mb-4 shadow-sm">
                            <div class="fw-bold text-dark fs-5 mb-1" id="previewTitleInfo">Save up to ₹500 on festive categories</div>
                            <div class="text-muted small lh-lg" id="previewDescInfo">Applicable on Festive Boxes, Gift Packs, and Mithai for orders above ₹1,000.</div>
                        </div>

                        <!-- Mini Stats Grid -->
                        <div class="row g-3 mb-4">
                            <div class="col-6">
                                <div class="bg-white border rounded-3 p-3 shadow-sm h-100">
                                    <div class="smaller text-muted mb-1">Total Pool</div>
                                    <div class="fw-bold text-dark" id="previewStatUsage">500 redemptions</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="bg-white border rounded-3 p-3 shadow-sm h-100">
                                    <div class="smaller text-muted mb-1">Per User</div>
                                    <div class="fw-bold text-dark" id="previewStatPerUser">2 uses</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="bg-white border rounded-3 p-3 shadow-sm h-100">
                                    <div class="smaller text-muted mb-1">Start Date</div>
                                    <div class="fw-bold text-dark" id="previewStatStart">20 Oct 2023</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="bg-white border rounded-3 p-3 shadow-sm h-100">
                                    <div class="smaller text-muted mb-1">Expiry Date</div>
                                    <div class="fw-bold text-dark" id="previewStatExpiry">15 Nov 2023</div>
                                </div>
                            </div>
                        </div>

                        <!-- Publishing Checklist -->
                        <div class="publishing-checklist-card">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div class="fw-bold text-dark">Publishing checklist</div>
                                <span class="badge border border-dark rounded-pill text-dark fw-bold px-3 py-2 smaller" id="checklistBadge">All ready!</span>
                            </div>
                            
                            <div class="d-flex gap-3 mb-3">
                                <div class="checklist-status-dot dot-ready" id="dotExpiry"></div>
                                <div class="flex-grow-1">
                                    <div class="fw-bold small text-dark">Verify expiry date</div>
                                    <div class="text-muted smaller">The campaign end date should fall after your final dispatch window.</div>
                                </div>
                            </div>

                            <div class="d-flex gap-3 mb-3">
                                <div class="checklist-status-dot dot-ready" id="dotDiscount"></div>
                                <div class="flex-grow-1">
                                    <div class="fw-bold small text-dark">Discount logic validation</div>
                                    <div class="text-muted smaller">Maximum discount and minimum cart values are set firmly.</div>
                                </div>
                            </div>

                            <div class="d-flex gap-3">
                                <div class="checklist-status-dot dot-ready" id="dotScope"></div>
                                <div class="flex-grow-1">
                                    <div class="fw-bold small text-dark">Product/Member scope</div>
                                    <div class="text-muted smaller">Categories have been selected and validated for checkout rules.</div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Add Category Modal -->
    <div class="modal fade" id="addCategoryOfferModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-bottom py-3">
                    <h5 class="modal-title fs-6 fw-bold text-dark">Add New Category Scope</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <label class="offer-field-label">Category Name</label>
                    <input type="text" class="offer-input-control" id="newCategoryInputOffer" placeholder="e.g., Seasonal Special">
                    
                    <div class="mt-4">
                        <label class="offer-field-label mb-2 opacity-50">Quick Suggest</label>
                        <div class="d-flex flex-wrap gap-2">
                            <button class="btn btn-sm btn-light border py-2 px-3 small rounded-pill" onclick="document.getElementById('newCategoryInputOffer').value='Dry Fruits'">Dry Fruits</button>
                            <button class="btn btn-sm btn-light border py-2 px-3 small rounded-pill" onclick="document.getElementById('newCategoryInputOffer').value='Sweets Box'">Sweets Box</button>
                            <button class="btn btn-sm btn-light border py-2 px-3 small rounded-pill" onclick="document.getElementById('newCategoryInputOffer').value='Combo Packs'">Combo Packs</button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 bg-light">
                    <button class="btn btn-sm px-4 fw-bold text-muted btn-link text-decoration-none" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-sm btn-dark px-4 fw-bold rounded-3" onclick="confirmAddOfferCategory()">Add to Offer</button>
                </div>
            </div>
        </div>
    </div>

</div>

<?php require_once 'includes/footer.php'; ?>
