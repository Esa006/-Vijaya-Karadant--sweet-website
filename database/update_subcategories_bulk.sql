/**
 * Sweets Website - Subcategory Bulk Update Alignment
 * =============================================================
 * File: database/update_subcategories_bulk.sql
 * Description: Adds soft delete support and performance indexes
 * =============================================================
 */

USE sweets_db;

-- 1. Add is_deleted column if it doesn't exist
ALTER TABLE `subcategories` 
ADD COLUMN `is_deleted` tinyint(1) DEFAULT 0 AFTER `status`;

-- 2. Add performance indexes for bulk operations
ALTER TABLE `subcategories` ADD INDEX `idx_subcat_bulk` (`id`, `category_id`, `status`, `is_deleted`);

-- 3. Update existing records to default 0
UPDATE `subcategories` SET `is_deleted` = 0 WHERE `is_deleted` IS NULL;
