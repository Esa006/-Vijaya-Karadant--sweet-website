-- Custom Gift Box Ecommerce Schema Definition
-- Creates tables for Master Data, Cart State, and Order State

-- 1. Master Data: Gift Boxes
CREATE TABLE IF NOT EXISTS `gift_boxes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `type` ENUM('pre_curated', 'custom') NOT NULL DEFAULT 'custom',
    `base_price` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `max_capacity` INT NOT NULL DEFAULT 6,
    `image_url` VARCHAR(255) DEFAULT NULL,
    `is_active` BOOLEAN NOT NULL DEFAULT TRUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Master Data: Gift Box Defaults (For pre-curated boxes)
CREATE TABLE IF NOT EXISTS `gift_box_defaults` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `gift_box_id` INT NOT NULL,
    `product_id` INT NOT NULL,
    `default_quantity` INT NOT NULL DEFAULT 1,
    FOREIGN KEY (`gift_box_id`) REFERENCES `gift_boxes`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Cart State: Cart Gift Boxes
CREATE TABLE IF NOT EXISTS `cart_gift_boxes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `cart_id` INT NOT NULL,
    `gift_box_id` INT NOT NULL,
    `personal_message` TEXT DEFAULT NULL,
    `delivery_date` DATE DEFAULT NULL,
    `delivery_time_slot` VARCHAR(100) DEFAULT NULL,
    `delivery_type` ENUM('standard', 'express') NOT NULL DEFAULT 'standard',
    `quantity` INT NOT NULL DEFAULT 1,
    `total_price` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`cart_id`) REFERENCES `carts`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`gift_box_id`) REFERENCES `gift_boxes`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Cart State: Cart Gift Box Items
CREATE TABLE IF NOT EXISTS `cart_gift_box_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `cart_gift_box_id` INT NOT NULL,
    `product_id` INT NOT NULL,
    `quantity` INT NOT NULL DEFAULT 1,
    FOREIGN KEY (`cart_gift_box_id`) REFERENCES `cart_gift_boxes`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Order State: Order Gift Boxes
CREATE TABLE IF NOT EXISTS `order_gift_boxes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT NOT NULL,
    `gift_box_id` INT NOT NULL,
    `personal_message` TEXT DEFAULT NULL,
    `delivery_date` DATE DEFAULT NULL,
    `delivery_time_slot` VARCHAR(100) DEFAULT NULL,
    `delivery_type` ENUM('standard', 'express') NOT NULL DEFAULT 'standard',
    `quantity` INT NOT NULL DEFAULT 1,
    `box_base_price` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `total_price` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`gift_box_id`) REFERENCES `gift_boxes`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Order State: Order Gift Box Items
CREATE TABLE IF NOT EXISTS `order_gift_box_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_gift_box_id` INT NOT NULL,
    `product_id` INT NOT NULL,
    `quantity` INT NOT NULL DEFAULT 1,
    `price_per_unit` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    FOREIGN KEY (`order_gift_box_id`) REFERENCES `order_gift_boxes`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
