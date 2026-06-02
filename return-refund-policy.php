<?php
/**
 * Sweets Website
 * =============================================================
 * File: return-refund-policy.php
 * Description: Returns & Refunds Policy page (converted from return-refund-policy.html)
 * =============================================================
 */

require_once 'config/config.php';

$seoContext = [
    'title'       => 'Return & Refund Policy | ' . SITE_NAME,
    'description' => 'Read the Return and Refund Policy of ' . SITE_NAME . '. Learn about our cancellation, return window, and refund process for your orders.',
    'canonical'   => BASE_URL . 'return-refund-policy.php',
];

require_once 'includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <h1 class="mb-4 text-center">Returns & Refunds</h1>

            <h3 class="mt-4">Cancellation Policy</h3>
            <p>1. In Case in case there is any cancellation request, it should be done before the product is shipped.</p>

            <h3 class="mt-4">Return Policy</h3>
            <p>1. Returns and exchanges are available only for select products, with detailed eligibility mentioned on the respective product pages. The standard return window for most products is 3 day</p>
            <p>2. The return policy for any product is subject to change without prior notice .</p>
            <p>3. In case we do not have pick up service available at your location, you would have to self ship the product to our office Address</p>
            <p>4. Return Exchange charges may apply on case to case basis.</p>

            <h3 class="mt-4">Refund Policy</h3>
            <ul class="list-unstyled ms-3">
                <li><i class="bi bi-dot me-2 text-danger"></i>We accept the refund request if there is a mismatch in quality, size, color or design or in case an item is missing wrong damage in a combo order</li>
                <li><i class="bi bi-dot me-2 text-danger"></i>Once the product has been picked up, the Refund is processed on the next 5-7 working days with the same transaction mode</li>
            </ul>

            <h3 class="mt-4 text-uppercase">Note for Return</h3>
            <p>The items should be unused and unwashed for hygiene reasons. The product should have the original packaging and tags in place, items without the original tags will not be accepted Customized products cannot be returned or exchanged Return Exchange requests that are not raised within the product pages the product would not be accepted</p>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
