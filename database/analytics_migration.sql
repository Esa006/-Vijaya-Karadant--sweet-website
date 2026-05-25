/**
 * Sweets Website - Analytics Migration v1.0
 * =============================================================
 * File: database/analytics_migration.sql
 * Run this in PHPMyAdmin or via CLI against sweets_db
 * =============================================================
 */

USE sweets_db;

-- ============================================================
-- STEP 1: Extend products with cost_price for profit tracking
-- ============================================================
ALTER TABLE `products`
    ADD COLUMN IF NOT EXISTS `cost_price` DECIMAL(10,2) DEFAULT 0.00 AFTER `sale_price`;

-- ============================================================
-- STEP 2: Refunds Table (for Net Revenue calculation)
-- ============================================================
CREATE TABLE IF NOT EXISTS `refunds` (
    `id`         INT(11)      NOT NULL AUTO_INCREMENT,
    `order_id`   INT(11)      NOT NULL,
    `amount`     DECIMAL(10,2) NOT NULL,
    `reason`     VARCHAR(255) DEFAULT NULL,
    `status`     ENUM('pending','completed','failed') DEFAULT 'completed',
    `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_refund_order`   (`order_id`),
    KEY `idx_refund_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- STEP 3: Analytics Events Table (for Conversion Funnel)
-- ============================================================
CREATE TABLE IF NOT EXISTS `analytics_events` (
    `id`         BIGINT(20)   NOT NULL AUTO_INCREMENT,
    `user_id`    INT(11)      DEFAULT NULL,
    `session_id` VARCHAR(100) NOT NULL,
    `event_type` ENUM('page_view','add_to_cart','begin_checkout','purchase') NOT NULL,
    `product_id` INT(11)      DEFAULT NULL,
    `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_event_funnel`  (`event_type`, `created_at`),
    KEY `idx_session`       (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- STEP 4: Performance Indexes on Orders
-- ============================================================
ALTER TABLE `orders`
    ADD INDEX IF NOT EXISTS `idx_orders_created_status` (`created_at`, `status`);

-- ============================================================
-- STEP 5: Sample Data — Cost Prices (60% of base price)
-- ============================================================
UPDATE `products`
SET `cost_price` = ROUND(`base_price` * 0.60, 2)
WHERE `cost_price` = 0 OR `cost_price` IS NULL;

-- ============================================================
-- STEP 6: Sample Data — Refunds (last 30 days)
-- ============================================================
INSERT IGNORE INTO `refunds` (`order_id`, `amount`, `reason`, `status`, `created_at`)
SELECT
    o.id,
    ROUND(o.total_amount * 0.10, 2),
    'Customer requested refund',
    'completed',
    DATE_SUB(NOW(), INTERVAL FLOOR(RAND() * 30) DAY)
FROM `orders` o
WHERE o.status = 'delivered'
LIMIT 5;

-- ============================================================
-- STEP 7: Sample Data — Analytics Events (30-day funnel)
-- ============================================================
-- Page Views (100 sessions)
INSERT INTO `analytics_events` (`session_id`, `event_type`, `created_at`)
SELECT
    MD5(CONCAT(RAND(), id)),
    'page_view',
    DATE_SUB(NOW(), INTERVAL FLOOR(RAND() * 30) DAY)
FROM `products`
LIMIT 100;

-- Add to Cart (50% of views)
INSERT INTO `analytics_events` (`session_id`, `event_type`, `created_at`)
SELECT
    MD5(CONCAT(RAND(), id)),
    'add_to_cart',
    DATE_SUB(NOW(), INTERVAL FLOOR(RAND() * 30) DAY)
FROM `products`
LIMIT 50;

-- Begin Checkout (25% of views)
INSERT INTO `analytics_events` (`session_id`, `event_type`, `created_at`)
SELECT
    MD5(CONCAT(RAND(), id)),
    'begin_checkout',
    DATE_SUB(NOW(), INTERVAL FLOOR(RAND() * 30) DAY)
FROM `products`
LIMIT 25;

-- Purchases (10% of views)
INSERT INTO `analytics_events` (`session_id`, `event_type`, `created_at`)
SELECT
    MD5(CONCAT(RAND(), id)),
    'purchase',
    DATE_SUB(NOW(), INTERVAL FLOOR(RAND() * 30) DAY)
FROM `products`
LIMIT 10;

-- ============================================================
-- VERIFY: Run these to confirm tables and data exist
-- ============================================================
-- SELECT COUNT(*) FROM refunds;
-- SELECT COUNT(*) FROM analytics_events;
-- SELECT event_type, COUNT(*) FROM analytics_events GROUP BY event_type;
-- SELECT name, base_price, cost_price FROM products LIMIT 5;
