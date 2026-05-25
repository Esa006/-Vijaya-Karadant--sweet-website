/**
 * Sweets Website - Coupon System Upgrade
 * =============================================================
 * File: database/upgrade_coupon_system.sql
 * Description: Enhances coupons table and adds usage tracking
 * =============================================================
 */

USE sweets_db;

-- 1. Upgrade Coupons Table
ALTER TABLE `coupons` 
ADD COLUMN `description` text DEFAULT NULL AFTER `code`,
ADD COLUMN `limit_per_user` int(11) DEFAULT 1 AFTER `usage_limit`,
ADD COLUMN `applicable_categories` json DEFAULT NULL AFTER `limit_per_user`,
ADD COLUMN `created_by` int(11) DEFAULT NULL AFTER `is_active`,
ADD CONSTRAINT `fk_coupon_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

-- 2. Create Coupon Usages Table (Tracking for performance insights)
CREATE TABLE IF NOT EXISTS `coupon_usages` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `coupon_id` int(11) NOT NULL,
    `user_id` int(11) NOT NULL,
    `order_id` int(11) NOT NULL,
    `discount_amount` decimal(10,2) NOT NULL,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_usage_coupon` (`coupon_id`),
    KEY `idx_usage_user` (`user_id`),
    KEY `idx_usage_order` (`order_id`),
    CONSTRAINT `fk_usage_coupon` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_usage_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_usage_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Seed a Demo Coupon for Analysis (Matches screenshot)
INSERT INTO coupons (code, description, type, value, min_cart_total, usage_limit, limit_per_user, applicable_categories, is_active, created_by)
VALUES (
    'DIWALI20', 
    'Diwali Dhamaka Sale - 20% off on festive hampers', 
    'percentage', 
    20.00, 
    1000.00, 
    500, 
    1, 
    '["Karadant Special", "Festive Hampers", "Premium Sweets", "Dry Fruits"]',
    1,
    (SELECT id FROM users WHERE role = 'admin' LIMIT 1)
);

-- 4. Seed Dummy Usage (for Rajiv Sharma ID=1)
INSERT INTO coupon_usages (coupon_id, user_id, order_id, discount_amount)
SELECT 
    (SELECT id FROM coupons WHERE code = 'DIWALI20'),
    (SELECT id FROM users WHERE email = 'rajiv.sharma@example.com'),
    (SELECT id FROM orders WHERE user_id = (SELECT id FROM users WHERE email = 'rajiv.sharma@example.com') LIMIT 1),
    250.00;
