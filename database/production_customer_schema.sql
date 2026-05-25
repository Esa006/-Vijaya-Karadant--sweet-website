/**
 * Sweets Website - Production Customer Schema
 * =============================================================
 * File: database/production_customer_schema.sql
 * Description: Aligns DB with production-grade requirements
 * =============================================================
 */

USE sweets_db;

-- 1. Ensure Customer Profiles Table
CREATE TABLE IF NOT EXISTS `customer_profiles` (
    `customer_id` BIGINT UNSIGNED PRIMARY KEY,
    `full_name` VARCHAR(255) NOT NULL,
    `gender` ENUM('male', 'female', 'other', 'unspecified') DEFAULT 'unspecified',
    `dob` DATE DEFAULT NULL,
    `avatar_url` VARCHAR(255) DEFAULT NULL,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. Create Activity Tracking Table
CREATE TABLE IF NOT EXISTS `customer_activity` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT(11) NOT NULL,
    `action_type` VARCHAR(50) NOT NULL, -- login, profile_update, order_placed
    `description` TEXT,
    `ip_address` VARCHAR(45),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY `idx_activity_user` (`user_id`),
    KEY `idx_activity_time` (`created_at`)
) ENGINE=InnoDB;

-- 3. Data Migration: Move existing profile data
INSERT IGNORE INTO customer_profiles (customer_id, full_name, dob)
SELECT u.id, u.full_name, c.dob
FROM users u
LEFT JOIN customers c ON u.id = c.user_id
WHERE u.role = 'customer';

-- 4. Add status to users if missing (for unified state management)
ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `status` ENUM('active', 'inactive', 'blocked') DEFAULT 'active' AFTER `role`;

-- 5. Create Notes Tracking Table
CREATE TABLE IF NOT EXISTS `customer_notes` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT(11) NOT NULL,
    `note` TEXT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY `idx_notes_user` (`user_id`),
    CONSTRAINT `fk_notes_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 6. Seed some initial activity for Rajiv Sharma (ID 1)
INSERT INTO customer_activity (user_id, action_type, description) VALUES
(1, 'login', 'Logged in from Web Browser'),
(1, 'profile_update', 'Updated delivery address'),
(1, 'order_placed', 'Placed order #ORD-4091');

