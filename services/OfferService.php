<?php
/**
 * Sweets Website
 * =============================================================
 * File: OfferService.php
 * Description: Coupon validation and discount logic
 * Author: Antigravity - Senior Backend Engineer
 * Version: 2.1.0
 * =============================================================
 */

require_once REPOS_PATH . '/CouponRepository.php';

class OfferService {
    private CouponRepository $repo;

    public function __construct() {
        $this->repo = new CouponRepository();
    }

    /**
     * Validate coupon code for a given user and cart total
     */
    public function validateCoupon(string $code, int $userId, float $cartTotal): array {
        $coupon = $this->repo->getByCode($code);

        if (!$coupon) {
            return ['success' => false, 'message' => 'Invalid or inactive coupon code.'];
        }

        // 1. Expiry Check
        if ($coupon['expires_at'] && strtotime($coupon['expires_at']) < time()) {
            return ['success' => false, 'message' => 'This coupon has expired.'];
        }

        // 2. Min Cart Total Check
        if ($cartTotal < (float)$coupon['min_cart_total']) {
            return ['success' => false, 'message' => 'Minimum cart total not met.'];
        }

        // 3. User Usage Limit Check
        $usageCount = $this->repo->getUserUsageCount((int)$coupon['id'], $userId);
        if ($usageCount >= (int)$coupon['usage_limit']) {
            return ['success' => false, 'message' => 'You have already used this coupon.'];
        }

        // 4. Calculate Discount
        $discount = $this->calculateDiscount((float)$coupon['value'], $coupon['type'], $cartTotal);

        return [
            'success'  => true,
            'coupon'   => $coupon,
            'discount' => $discount
        ];
    }

    private function calculateDiscount(float $value, string $type, float $total): float {
        if ($type === 'percentage') {
            return ($total * $value) / 100;
        }
        return min($value, $total); // cannot discount more than total
    }

    /**
     * Record coupon usage after order placement
     */
    public function applyCoupon(int $couponId, int $userId, int $orderId): bool {
        return $this->repo->addUsage($couponId, $userId, $orderId);
    }
}
