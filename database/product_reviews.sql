-- =============================================================
-- Sweets Website: Product Reviews Table
-- Run: mysql -u root sweets_db < database/product_reviews.sql
-- =============================================================
-- NOTE on uniqueness enforcement:
--   MySQL/MariaDB treat NULL != NULL in UNIQUE indexes, so a plain
--   UNIQUE(user_id, product_id) would allow a user to insert multiple
--   rows for the same product as long as combo_id is NULL.
--   We work around this by storing 0 (not NULL) when there is no
--   product or combo, and enforcing uniqueness in the service layer
--   BEFORE the INSERT.  The table still uses NULLable columns so
--   FK references stay valid; the repo's userHasReviewed() check
--   prevents double-insertion at the application level.
-- =============================================================

CREATE TABLE IF NOT EXISTS `product_reviews` (
    `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `product_id`    INT UNSIGNED NULL COMMENT 'NULL for combo reviews',
    `combo_id`      INT UNSIGNED NULL COMMENT 'NULL for product reviews',
    `user_id`       INT UNSIGNED NOT NULL,
    `order_id`      INT UNSIGNED NOT NULL COMMENT 'Must belong to a delivered order',
    `rating`        TINYINT UNSIGNED NOT NULL DEFAULT 5,
    `title`         VARCHAR(120) NOT NULL DEFAULT '',
    `body`          TEXT NOT NULL,
    `status`        ENUM('pending','approved','rejected') NOT NULL DEFAULT 'approved',
    `helpful_count` INT UNSIGNED NOT NULL DEFAULT 0,
    `created_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Composite index speeds up "reviews for product X" queries
    KEY `idx_product_status` (`product_id`, `status`),
    KEY `idx_combo_status`   (`combo_id`,   `status`),
    KEY `idx_order`          (`order_id`),
    KEY `idx_user`           (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Apply the updated schema to the already-created table
-- (safe to run on existing installs)
ALTER TABLE `product_reviews`
    MODIFY `rating` TINYINT UNSIGNED NOT NULL DEFAULT 5;
