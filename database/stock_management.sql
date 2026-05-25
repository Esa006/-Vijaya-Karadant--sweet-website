-- ============================================================
-- Sweets Website
-- File: database/stock_management.sql
-- Description: Stock management schema additions
--              Safe to run on existing databases (IF NOT EXISTS / IF NOT EXISTS)
-- ============================================================

-- 1. Ensure products table has stock_quantity column
ALTER TABLE `products`
    ADD COLUMN IF NOT EXISTS `stock_quantity` INT NOT NULL DEFAULT 0 COMMENT 'Available units';

-- 2. Ensure products.status supports out_of_stock
--    (If status is ENUM, alter it; if VARCHAR just ensure correct values are used)
-- ALTER TABLE `products`
--     MODIFY COLUMN `status` ENUM('published','draft','out_of_stock','inactive') NOT NULL DEFAULT 'published';

-- 3. stock_notify table — created automatically by StockRepository::saveNotifyRequest()
--    but can be pre-created here for clarity:
CREATE TABLE IF NOT EXISTS `stock_notify` (
    `id`          INT UNSIGNED     AUTO_INCREMENT PRIMARY KEY,
    `product_id`  INT UNSIGNED     NOT NULL,
    `email`       VARCHAR(255)     NOT NULL,
    `notified`    TINYINT(1)       NOT NULL DEFAULT 0 COMMENT '1 = notification email sent',
    `created_at`  DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_product_email` (`product_id`, `email`),
    INDEX `idx_notified` (`notified`),
    INDEX `idx_product`  (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Useful index for inventory table (if it exists)
-- CREATE INDEX IF NOT EXISTS idx_inv_product ON inventory (product_id);

-- ============================================================
-- Example: Seed test stock data
-- ============================================================
-- UPDATE products SET stock_quantity = 0  WHERE id = 1;  -- out_of_stock
-- UPDATE products SET stock_quantity = 3  WHERE id = 2;  -- low_stock
-- UPDATE products SET stock_quantity = 50 WHERE id = 3;  -- in_stock
