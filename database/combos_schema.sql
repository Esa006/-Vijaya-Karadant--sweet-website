-- Combo Offers System Schema Update

-- 1. Create Combos Table
CREATE TABLE IF NOT EXISTS `combos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL UNIQUE,
  `description` text DEFAULT NULL,
  `category` varchar(100) DEFAULT 'combo',
  `price` decimal(10,2) DEFAULT NULL COMMENT 'Fixed price. If NULL, derived dynamically',
  `image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 2. Create Combo Items Table (Child Products)
CREATE TABLE IF NOT EXISTS `combo_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `combo_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  CONSTRAINT `combo_items_ibfk_1` FOREIGN KEY (`combo_id`) REFERENCES `combos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `combo_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 3. Modify Order Items Table to natively support Combos
-- Drop the existing product_id foreign key because it will now be nullable
ALTER TABLE order_items DROP FOREIGN KEY IF EXISTS order_items_ibfk_2;

-- Make product_id nullable
ALTER TABLE order_items MODIFY product_id int(11) NULL;

-- Re-add the product_id foreign key with SET NULL on delete, or keep CASCADE if we want order items deleted when products are deleted
ALTER TABLE order_items ADD CONSTRAINT order_items_ibfk_2 FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE;

-- Add new columns for combo support
ALTER TABLE order_items ADD COLUMN item_type ENUM('product', 'combo') DEFAULT 'product' AFTER order_id;
ALTER TABLE order_items ADD COLUMN combo_id INT NULL AFTER product_id;

-- Add foreign key for combo_id
ALTER TABLE order_items ADD CONSTRAINT order_items_ibfk_3 FOREIGN KEY (combo_id) REFERENCES combos (id) ON DELETE SET NULL;
