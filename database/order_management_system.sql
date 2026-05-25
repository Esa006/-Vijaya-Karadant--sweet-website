/**
 * Sweets Website - Order Management System Schema
 * =============================================================
 * File: database/order_management_system.sql
 * Description: Production-grade schema for orders, variants, and payments
 * =============================================================
 */

USE sweets_db;

-- 1. Product Variants (Source of Truth for Stock & Price)
CREATE TABLE IF NOT EXISTS `product_variants` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `product_id` int(11) NOT NULL,
    `label` varchar(50) NOT NULL, -- e.g., '250g', '500g', '1kg'
    `price` decimal(10,2) NOT NULL,
    `stock` int(11) NOT NULL DEFAULT 0,
    `sku` varchar(50) DEFAULT NULL,
    `is_active` tinyint(1) DEFAULT 1,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_variant_product` (`product_id`),
    CONSTRAINT `fk_variant_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Orders Table (Enhanced)
-- Note: Dropping and recreating if necessary or just adding columns
ALTER TABLE `orders` 
ADD COLUMN `payment_id` varchar(100) DEFAULT NULL AFTER `total_amount`,
ADD COLUMN `payment_method` varchar(50) DEFAULT NULL AFTER `payment_id`,
MODIFY COLUMN `status` enum('pending', 'paid', 'processing', 'shipped', 'delivered', 'cancelled', 'failed') DEFAULT 'pending';

-- 3. Order Items (Linked to Variants)
-- If table doesn't exist, create it. If it does, ensure variant_id exists.
CREATE TABLE IF NOT EXISTS `order_items` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `order_id` int(11) NOT NULL,
    `product_id` int(11) NOT NULL,
    `variant_id` int(11) NOT NULL,
    `quantity` int(11) NOT NULL,
    `price` decimal(10,2) NOT NULL, -- Price at the time of order
    PRIMARY KEY (`id`),
    KEY `idx_item_order` (`order_id`),
    KEY `idx_item_variant` (`variant_id`),
    CONSTRAINT `fk_item_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Payments Table (Audit Log)
CREATE TABLE IF NOT EXISTS `payments` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `order_id` int(11) NOT NULL,
    `gateway` varchar(50) NOT NULL, -- e.g., 'Razorpay', 'Stripe'
    `transaction_id` varchar(100) NOT NULL,
    `amount` decimal(10,2) NOT NULL,
    `currency` varchar(10) DEFAULT 'INR',
    `status` enum('initiated', 'success', 'failed', 'refunded') NOT NULL,
    `raw_response` text, -- Store webhook payload for debugging
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_payment_order` (`order_id`),
    KEY `idx_payment_txn` (`transaction_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Performance Indexes
ALTER TABLE `product_variants` ADD INDEX `idx_variant_stock` (`stock`);
ALTER TABLE `orders` ADD INDEX `idx_order_status` (`status`);
ALTER TABLE `orders` ADD INDEX `idx_order_created` (`created_at`);
