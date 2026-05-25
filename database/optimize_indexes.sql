-- ==========================================================
-- Database Optimization & Indexing Strategy
-- Run this on your Live Server to handle 10k+ concurrent users
-- ==========================================================

-- 1. Optimize Products Table (Read Heavy)
-- Helps with getProductsByCategory(), getBySlug(), and getFeaturedProducts()
ALTER TABLE `products` 
ADD INDEX IF NOT EXISTS `idx_products_slug` (`slug`),
ADD INDEX IF NOT EXISTS `idx_products_status` (`status`),
ADD INDEX IF NOT EXISTS `idx_products_category` (`category_id`),
ADD INDEX IF NOT EXISTS `idx_products_featured` (`featured`);

-- 2. Optimize Product Images Table
-- Helps with getProductImages() 
ALTER TABLE `product_images` 
ADD INDEX IF NOT EXISTS `idx_product_images_pid` (`product_id`),
ADD INDEX IF NOT EXISTS `idx_product_images_main` (`is_main`);

-- 3. Optimize Orders Table (Write & Search Heavy)
-- Helps with CRON job, Admin Dashboard filters, and user order history
-- IMPORTANT: Added idempotency_key for atomic checkout prevention of double charges
ALTER TABLE `orders` 
ADD COLUMN IF NOT EXISTS `idempotency_key` VARCHAR(100) UNIQUE NULL AFTER `order_number`,
ADD INDEX IF NOT EXISTS `idx_orders_user` (`user_id`),
ADD INDEX IF NOT EXISTS `idx_orders_status` (`status`),
ADD INDEX IF NOT EXISTS `idx_orders_created_at` (`created_at`);

-- 4. Optimize Inventory Table (Read/Write Heavy for Transactions)
-- Helps with atomic lock updates and getting available stock
ALTER TABLE `inventory` 
ADD INDEX IF NOT EXISTS `idx_inventory_product` (`product_id`);

-- 5. Optimize Categories Table
ALTER TABLE `categories` 
ADD INDEX IF NOT EXISTS `idx_categories_slug` (`slug`);
