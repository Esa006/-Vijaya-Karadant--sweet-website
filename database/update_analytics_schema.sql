/**
 * Sweets Website - Analytics Schema Alignment
 * =============================================================
 * File: database/update_analytics_schema.sql
 * Description: Aligns the database with strict analytics requirements
 * =============================================================
 */

USE sweets_db;

-- 1. Create product_variants table
CREATE TABLE IF NOT EXISTS `product_variants` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `label` varchar(50) NOT NULL, -- e.g., '250g', '500g', '1kg'
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `product_variants_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Update order_items to include variant_id
ALTER TABLE `order_items` ADD COLUMN `variant_id` int(11) DEFAULT NULL AFTER `product_id`;
ALTER TABLE `order_items` ADD CONSTRAINT `order_items_ibfk_3` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE SET NULL;

-- 3. Add Indexes for performance as requested
ALTER TABLE `orders` ADD INDEX `idx_orders_created_at` (`created_at`);
ALTER TABLE `orders` ADD INDEX `idx_orders_status` (`status`);
ALTER TABLE `order_items` ADD INDEX `idx_oi_product_revenue` (`product_id`, `quantity`, `price_at_time`);

-- 4. Seed some sample variants for existing products if empty
INSERT IGNORE INTO `product_variants` (product_id, label, price, stock)
SELECT id, '500g', base_price, stock_quantity FROM products WHERE status = 'published';

INSERT IGNORE INTO `product_variants` (product_id, label, price, stock)
SELECT id, '1kg', base_price * 1.8, stock_quantity / 2 FROM products WHERE status = 'published';
