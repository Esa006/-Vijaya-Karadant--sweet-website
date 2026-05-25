-- =============================================================
-- Hero Slides Table
-- =============================================================

CREATE TABLE IF NOT EXISTS `hero_slides` (
    `id`              INT(11)       NOT NULL AUTO_INCREMENT,
    `title_line1`     VARCHAR(255)  NOT NULL DEFAULT '',
    `title_accent`    VARCHAR(255)  NOT NULL DEFAULT '',
    `tagline`         VARCHAR(255)  NOT NULL DEFAULT '',
    `button_text`     VARCHAR(100)  NOT NULL DEFAULT 'Shop Now',
    `button_url`      VARCHAR(500)  NOT NULL DEFAULT '#bestsellers',
    `desktop_image`   VARCHAR(500)  NOT NULL DEFAULT '',
    `mobile_image`    VARCHAR(500)  NOT NULL DEFAULT '',
    `sort_order`      INT(11)       NOT NULL DEFAULT 0,
    `is_active`       TINYINT(1)    NOT NULL DEFAULT 1,
    `created_at`      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed default slides matching existing hardcoded hero
INSERT INTO `hero_slides` (`title_line1`, `title_accent`, `tagline`, `button_text`, `button_url`, `desktop_image`, `mobile_image`, `sort_order`, `is_active`) VALUES
('Celebrate Every Occasion', 'Timeless Sweetness', 'ARTISANAL & TRADITIONAL', 'Explore Karadant', '#bestsellers', 'assets/images/banners/home-banner  (5).png', 'assets/images/banners/demo-phone-screen-img/Property 1=Frame 2147228075 (2).png', 1, 1),
('Experience the Taste of', 'Traditional Laddus', 'ARTISANAL & TRADITIONAL', 'Explore Laddu', '#bestsellers', 'assets/images/banners/home-banner  (2).png', 'assets/images/banners/demo-phone-screen-img/Property 1=Frame 2147228127 (1).png', 2, 1),
('Made with Pure Ghee', 'Handcrafted with Love', '100% NATURAL & PURE', 'Shop Now', '#bestsellers', 'assets/images/banners/home-banner  (3).png', 'assets/images/banners/demo-phone-screen-img/Property 1=Frame 2147228128.png', 3, 1),
('Savor the Crunch of', 'Delicious Namkeens', 'ARTISANAL & TRADITIONAL', 'Explore Namkeen', '#bestsellers', 'assets/images/banners/home-banner  (4).png', 'assets/images/banners/demo-phone-screen-img/Property 1=Frame 2147228075 (2).png', 4, 1),
('Pure and Natural', 'Traditional Karadant', '100% PURE & NATURAL', 'Browse Collection', '#bestsellers', 'assets/images/banners/home-banner  (1).png', 'assets/images/banners/demo-phone-screen-img/Property 1=Frame 2147228127 (1).png', 5, 1);
