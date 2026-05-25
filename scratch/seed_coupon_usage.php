<?php
require_once 'config/config.php';
require_once 'config/Database.php';
require_once 'repositories/CouponRepository.php';

$db = Database::getInstance();
$repo = new CouponRepository($db);

$coupons = $repo->getAllCoupons();

foreach ($coupons as $coupon) {
    // Add 10-50 random usage entries for each coupon
    $count = rand(10, 50);
    for ($i = 0; $i < $count; $i++) {
        // Random user (1-10) and random order (1-100)
        // Note: These might fail if foreign keys are strict and IDs don't exist,
        // but we'll try it anyway for the sake of dummy data.
        try {
            $repo->addUsage($coupon['id'], rand(1, 10), rand(1, 100), $coupon['value'] * 0.1);
        } catch (Exception $e) {
            // Ignore errors for missing foreign keys
        }
    }
    echo "Added dummy usage for Coupon: {$coupon['code']}\n";
}
