-- Add missing columns to orders table
ALTER TABLE `orders` 
ADD COLUMN `subtotal` DECIMAL(10,2) DEFAULT 0.00 AFTER `total_amount`,
ADD COLUMN `discount_amount` DECIMAL(10,2) DEFAULT 0.00 AFTER `subtotal`,
ADD COLUMN `shipping_charges` DECIMAL(10,2) DEFAULT 0.00 AFTER `discount_amount`,
ADD COLUMN `tax_rate` DECIMAL(5,2) DEFAULT 5.00 AFTER `shipping_charges`,
ADD COLUMN `tax_amount` DECIMAL(10,2) DEFAULT 0.00 AFTER `tax_rate`,
ADD COLUMN `notes` TEXT NULL AFTER `billing_address_id`,
ADD COLUMN `tracking_id` VARCHAR(100) NULL AFTER `notes`,
ADD COLUMN `delivery_partner` VARCHAR(100) NULL AFTER `tracking_id`,
ADD COLUMN `estimated_delivery_date` DATE NULL AFTER `delivery_partner`,
ADD COLUMN `admin_notes` TEXT NULL AFTER `estimated_delivery_date`,
ADD COLUMN `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;
