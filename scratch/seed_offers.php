<?php
require_once 'config/config.php';
require_once 'config/Database.php';
require_once 'repositories/CouponRepository.php';

$db = Database::getInstance();
$repo = new CouponRepository($db);

$dummyOffers = [
    [
        'code' => 'DIWALI20',
        'description' => 'Diwali Dhamaka Sale',
        'type' => 'percentage',
        'value' => 20,
        'min_cart_total' => 1000,
        'usage_limit' => 500,
        'limit_per_user' => 1,
        'applicable_categories' => json_encode(['Festive Boxes', 'Gift Packs']),
        'is_active' => 1,
        'created_by' => 1
    ],
    [
        'code' => 'WELCOME10',
        'description' => 'Welcome Bonus for New Users',
        'type' => 'percentage',
        'value' => 10,
        'min_cart_total' => 500,
        'usage_limit' => 1200,
        'limit_per_user' => 1,
        'applicable_categories' => json_encode(['All Categories']),
        'is_active' => 1,
        'created_by' => 1
    ],
    [
        'code' => 'FREESHIP',
        'description' => 'Free Diwali Shipping',
        'type' => 'fixed',
        'value' => 0,
        'min_cart_total' => 800,
        'usage_limit' => 1000,
        'limit_per_user' => 1,
        'applicable_categories' => json_encode(['All Categories']),
        'is_active' => 1,
        'created_by' => 1
    ],
    [
        'code' => 'GANESHA15',
        'description' => 'Ganesha Special Offer',
        'type' => 'percentage',
        'value' => 15,
        'min_cart_total' => 1500,
        'usage_limit' => 500,
        'limit_per_user' => 1,
        'applicable_categories' => json_encode(['Premium Mithai']),
        'is_active' => 1,
        'created_by' => 1
    ],
    [
        'code' => 'CORP500',
        'description' => 'Corporate Gifting Flat Discount',
        'type' => 'fixed',
        'value' => 500,
        'min_cart_total' => 5000,
        'usage_limit' => 500,
        'limit_per_user' => 5,
        'applicable_categories' => json_encode(['Gift Packs']),
        'is_active' => 1,
        'created_by' => 1
    ]
];

foreach ($dummyOffers as $offer) {
    try {
        // Check if code exists to avoid duplicates
        if (!$repo->getByCode($offer['code'])) {
            $id = $repo->create($offer);
            echo "Added Offer: {$offer['code']} (ID: $id)\n";
        } else {
            echo "Offer exists: {$offer['code']}\n";
        }
    } catch (Exception $e) {
        echo "Error adding {$offer['code']}: " . $e->getMessage() . "\n";
    }
}
