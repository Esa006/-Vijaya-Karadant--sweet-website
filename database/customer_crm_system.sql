/**
 * Sweets Website - Customer CRM Schema
 * =============================================================
 * File: database/customer_crm_system.sql
 * Description: Production-grade schema for customer data, 
 *              addresses, tags, and activity tracking.
 * =============================================================
 */

USE sweets_db;

-- 1. Customers Table (Business Data - Linked to Users)
CREATE TABLE IF NOT EXISTS `customers` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `name` varchar(100) NOT NULL,
    `phone` varchar(20) DEFAULT NULL,
    `dob` date DEFAULT NULL,
    `status` enum('active', 'suspended', 'inactive') DEFAULT 'active',
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_customer_user` (`user_id`),
    CONSTRAINT `fk_customer_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Customer Addresses
CREATE TABLE IF NOT EXISTS `customer_addresses` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `type` enum('billing', 'shipping') NOT NULL,
    `address_line` text NOT NULL,
    `city` varchar(100) NOT NULL,
    `state` varchar(100) NOT NULL,
    `pincode` varchar(10) NOT NULL,
    `phone` varchar(20) DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_addr_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Customer Notes (Internal Admin Notes)
CREATE TABLE IF NOT EXISTS `customer_notes` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `note` text NOT NULL,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_note_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Customer Tags
CREATE TABLE IF NOT EXISTS `customer_tags` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `tag` varchar(50) NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_tag_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Activity Logs (Audit Trail)
CREATE TABLE IF NOT EXISTS `activity_logs` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `entity_type` enum('customer', 'order', 'account') NOT NULL,
    `entity_id` int(11) NOT NULL,
    `action` varchar(100) NOT NULL, -- e.g., 'note_added', 'status_changed'
    `meta` json DEFAULT NULL,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_log_entity` (`entity_type`, `entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed initial customer data for existing users (Demo purposes)
INSERT IGNORE INTO customers (user_id, name, phone, status)
SELECT id, full_name, phone, 'active' FROM users WHERE role = 'customer';
