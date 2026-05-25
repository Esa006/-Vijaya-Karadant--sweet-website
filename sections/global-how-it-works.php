<?php
/**
 * Sweets Website
 * =============================================================
 * File: sections/global-how-it-works.php
 * Description: Animated sequential step timeline for global shipping
 * =============================================================
 */
?>

<section class="global-steps py-5 my-0" style=" overflow: hidden;">
    <div class="container py-3">

        <!-- Header -->
        <div class="row mb-5">
            <div class="col-12">
                <h2 class="fw-bold text-center text-lg-start steps-title"
                    style="color: #6c2719; font-family: 'Quando', Georgia, serif; font-size: clamp(2rem, 3.5vw, 2.5rem); letter-spacing: -0.01em;">
                    How It Works
                </h2>
            </div>
        </div>

        <!-- Animated Timeline Wrapper -->
        <div class="row position-relative mt-2 mb-4 text-center steps-wrapper">

            <!-- Horizontal Line (Animated) -->
            <div class="d-none d-lg-block position-absolute steps-line"
                style="top: 40px; left: 12.5%; width: 75%; height: 1px; background-color: #d9bd8d; z-index: 1;">
            </div>

            <!-- Step 1 -->
            <div class="col-12 col-lg-3 mb-5 mb-lg-0 position-relative step-item">
                <div class="d-flex justify-content-center mb-3 position-relative" style="z-index: 2;">
                    <div class="rounded-circle bg-white shadow-sm d-flex align-items-center justify-content-center"
                        style="width: 80px; height: 80px; border: 1px solid #d9bd8d;">
                        <i class="bi bi-box-seam" style="font-size: 2rem; color: #bea772;"></i>
                    </div>
                </div>
                <div class="fw-semibold mb-1" style="color: #f5a625; font-size: 0.95rem;">Step 01</div>
                <h3 class="fw-bold"
                    style="color: #2a2a2a; font-size: 1.15rem; font-family: Inter, system-ui, sans-serif;">Place Order
                    Online</h3>
            </div>

            <!-- Step 2 -->
            <div class="col-12 col-lg-3 mb-5 mb-lg-0 position-relative step-item">
                <div class="d-flex justify-content-center mb-3 position-relative" style="z-index: 2;">
                    <div class="rounded-circle bg-white shadow-sm d-flex align-items-center justify-content-center"
                        style="width: 80px; height: 80px; border: 1px solid #d9bd8d;">
                        <i class="bi bi-archive" style="font-size: 2rem; color: #bea772;"></i>
                    </div>
                </div>
                <div class="fw-semibold mb-1" style="color: #f5a625; font-size: 0.95rem;">Step 02</div>
                <h3 class="fw-bold"
                    style="color: #2a2a2a; font-size: 1.15rem; font-family: Inter, system-ui, sans-serif;">Packaging &
                    Processing</h3>
            </div>

            <!-- Step 3 -->
            <div class="col-12 col-lg-3 mb-5 mb-lg-0 position-relative step-item">
                <div class="d-flex justify-content-center mb-3 position-relative" style="z-index: 2;">
                    <div class="rounded-circle bg-white shadow-sm d-flex align-items-center justify-content-center"
                        style="width: 80px; height: 80px; border: 1px solid #d9bd8d;">
                        <i class="bi bi-airplane-fill"
                            style="transform: rotate(45deg); font-size: 2rem; color: #bea772;"></i>
                    </div>
                </div>
                <div class="fw-semibold mb-1" style="color: #f5a625; font-size: 0.95rem;">Step 03</div>
                <h3 class="fw-bold"
                    style="color: #2a2a2a; font-size: 1.15rem; font-family: Inter, system-ui, sans-serif;">International
                    Shipping</h3>
            </div>

            <!-- Step 4 -->
            <div class="col-12 col-lg-3 position-relative step-item">
                <div class="d-flex justify-content-center mb-3 position-relative" style="z-index: 2;">
                    <div class="rounded-circle bg-white shadow-sm d-flex align-items-center justify-content-center"
                        style="width: 80px; height: 80px; border: 1px solid #d9bd8d;">
                        <i class="bi bi-door-closed-fill" style="font-size: 2rem; color: #bea772;"></i>
                    </div>
                </div>
                <div class="fw-semibold mb-1" style="color: #f5a625; font-size: 0.95rem;">Step 04</div>
                <h3 class="fw-bold"
                    style="color: #2a2a2a; font-size: 1.15rem; font-family: Inter, system-ui, sans-serif;">Delivery at
                    Your Doorstep</h3>
            </div>

        </div>
    </div>
</section>

<!-- CSS Animations -->
<style>
    /* Base states before animation */
    .step-item {
        opacity: 0;
        transform: translateY(30px);
    }

    .steps-line {
        transform-origin: left;
        transform: scaleX(0);
    }

    /* Animations Triggered */
    .step-item {
        animation: fadeUpStep 0.65s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
    }

    /* Sequencing */
    .step-item:nth-child(2) {
        animation-delay: 0.1s;
    }

    .step-item:nth-child(3) {
        animation-delay: 0.3s;
    }

    .step-item:nth-child(4) {
        animation-delay: 0.5s;
    }

    .step-item:nth-child(5) {
        animation-delay: 0.7s;
    }

    /* Line Draw Animation */
    .steps-line {
        animation: drawLineStep 1s ease-in-out 0.8s forwards;
    }

    @keyframes fadeUpStep {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes drawLineStep {
        to {
            transform: scaleX(1);
        }
    }
</style>