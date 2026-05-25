/**
 * Sweets Website
 * =============================================================
 * File: seed_product_images.sql
 * Description: Data population script for the new dynamically 
 *              utilized product_images table to ensure 0-downtime.
 * Author: Sweets Website Team
 * =============================================================
 */

USE sweets_db;

-- Clear previous test images to avoid duplicates if re-running
DELETE FROM product_images;

-- Reset Auto Increment Counter
ALTER TABLE product_images AUTO_INCREMENT = 1;

-- Seed Single "is_main" images (Thumbnail/Card images)
INSERT INTO product_images (product_id, image_path, is_main) VALUES
-- Best Sellers (IDs 1-10)
(1, 'assets/images/homepage/Best Sellers (1).png', 1),
(2, 'assets/images/homepage/Best Sellers (2).png', 1),
(3, 'assets/images/homepage/Best Sellers (3).png', 1),
(4, 'assets/images/homepage/Best Sellers (4).png', 1),
(5, 'assets/images/homepage/Best Sellers (5).png', 1),
(6, 'assets/images/homepage/Best Sellers (6).png', 1),
(7, 'assets/images/homepage/Best Sellers (7).png', 1),
(8, 'assets/images/homepage/Best Sellers (8).png', 1),
(9, 'assets/images/Karadant/giftpack 1 (1).png', 1),
(10, 'assets/images/Karadant/giftpack 1 (2).png', 1),

-- Namkeens (IDs 11-18 matching standard incrementation)
(11, 'assets/images/banners/namkeen-page/our signature  (7).png', 1),
(12, 'assets/images/banners/namkeen-page/our signature  (8).png', 1),
(13, 'assets/images/banners/namkeen-page/our signature  (9).png', 1),
(14, 'assets/images/banners/namkeen-page/our signature  (10).png', 1),
(15, 'assets/images/banners/namkeen-page/our signature  (11).png', 1),
(16, 'assets/images/banners/namkeen-page/our signature  (1).png', 1),
(17, 'assets/images/banners/namkeen-page/our signature  (5).png', 1),
(18, 'assets/images/banners/namkeen-page/our signature  (6).png', 1);

-- Seed Slider/Gallery Images (is_main = 0)
-- Testing on Product ID 1 (Premium Vijaya Karadant) which shows on cart.php
INSERT INTO product_images (product_id, image_path, is_main) VALUES
(1, 'assets/images/cart/Traditional (1).png', 0),
(1, 'assets/images/cart/Traditional (2).png', 0),
(1, 'assets/images/cart/Traditional (3).png', 0),
(1, 'assets/images/cart/Traditional (4).png', 0);

-- Note: Ensure your `products` table has IDs 1 through 18 
-- to safely accept these foreign keys without throwing a constraint error.
