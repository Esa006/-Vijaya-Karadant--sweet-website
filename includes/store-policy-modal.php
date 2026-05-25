<!-- Store Policy Modal -->
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/components/store-policy-modal.css">

<div class="modal fade c-policy-modal" id="storePolicyModal" tabindex="-1" aria-labelledby="storePolicyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-body position-relative">
                
                <!-- Close Button (Top right X) -->
                <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Close"></button>

                <!-- Header -->
                <div class="c-policy-header">
                    <h2 class="c-policy-header__title">No Return 1 day Exchange</h2>
                    
                    <div class="c-policy-header__icons">
                        <div class="c-policy-icon-box">
                            <div class="c-policy-icon-wrapper">
                                <i class="bi bi-arrow-repeat"></i>
                                <div class="c-policy-icon-badge">
                                    <i class="bi bi-check"></i>
                                </div>
                            </div>
                            <span class="c-policy-icon-text">Return within 1 days</span>
                        </div>
                        <div class="c-policy-icon-box">
                            <div class="c-policy-icon-wrapper">
                                <i class="bi bi-arrow-left-right"></i>
                                <div class="c-policy-icon-badge">
                                    <i class="bi bi-check"></i>
                                </div>
                            </div>
                            <span class="c-policy-icon-text">Return within 1 days</span>
                        </div>
                    </div>
                </div>

                <!-- Conditions Section -->
                <div class="c-policy-conditions px-lg-3">
                    <h4 class="c-policy-section-title">What are the conditions for return/exchange?</h4>
                    <ul class="c-policy-conditions__list">
                        <li class="c-policy-conditions__item">
                            <i class="bi bi-check-lg"></i>
                            Wrong/Damaged Items
                        </li>
                        <li class="c-policy-conditions__item">
                            <i class="bi bi-x-lg"></i>
                            All other reasons
                        </li>
                    </ul>
                </div>

                <div class="c-policy-divider mx-lg-3"></div>

                <!-- Timeline Section -->
                <div class="c-policy-timeline px-lg-3">
                    <h4 class="c-policy-section-title">How to place a return/exchange?</h4>
                    
                    <div class="c-policy-timeline__step">
                        <h5 class="c-policy-timeline__title">Select a method</h5>
                        <p class="c-policy-timeline__desc">- Go to My orders >> Order Details >> Return/Exchange</p>
                        <p class="c-policy-timeline__desc">- Select your preferred method of Return or Exchange</p>
                    </div>

                    <div class="c-policy-timeline__step">
                        <h5 class="c-policy-timeline__title">Submit return request</h5>
                        <p class="c-policy-timeline__desc">- Submit the reason</p>
                        <p class="c-policy-timeline__desc">- Submit Product Images and Unboxing Video</p>
                    </div>

                    <div class="c-policy-timeline__step">
                        <h5 class="c-policy-timeline__title">Submit Exchange request</h5>
                        <p class="c-policy-timeline__desc">- Submit Product Images and Unboxing Video</p>
                        <p class="c-policy-timeline__desc">- Buy a new product or replace with the same variant of product and process checkout in case of any positive delta amount</p>
                    </div>

                    <div class="c-policy-timeline__step">
                        <h5 class="c-policy-timeline__title">Product pickup</h5>
                        <p class="c-policy-timeline__desc">- The product will be picked up only if it is found to be in original condition with tags and packaging</p>
                    </div>

                    <div class="c-policy-timeline__step">
                        <h5 class="c-policy-timeline__title">Return</h5>
                        <p class="c-policy-timeline__desc">- Refund will be initiated after the product is picked by us</p>
                        <p class="c-policy-timeline__desc">- The full amount will be refunded after deducting Rs. 100 for shipping charges.</p>
                        <p class="c-policy-timeline__desc">- No refund shall be issued in case the product is not found in its original condition with tags and packaging</p>
                    </div>

                    <div class="c-policy-timeline__step">
                        <h5 class="c-policy-timeline__title">Exchange</h5>
                        <p class="c-policy-timeline__desc">- Exchanged product will be shipped after return pickup</p>
                        <p class="c-policy-timeline__desc">- Any positive refund will be initiated once the exchanged product is delivered to you</p>
                        <p class="c-policy-timeline__desc">- No refund/exchange shall be issued in case the returned product is not found in its original condition with tags and packaging</p>
                    </div>
                </div>

                <!-- Footer Action -->
                <div class="c-policy-footer px-lg-3">
                    <button class="c-policy-btn-goback" data-bs-dismiss="modal">
                        OKAY, GO BACK
                    </button>
                </div>

            </div>
        </div>
    </div>
</div>
