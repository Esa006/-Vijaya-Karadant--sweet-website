-- ================================================================
-- Sweets Website — Comprehensive Product Cleanup & Restoration
-- File: database/restore_original_products.sql
-- ================================================================

USE sweets_db;

-- 1. Clean up everything to start fresh (Avoids ID conflicts)
DELETE FROM product_images;
DELETE FROM products;
DELETE FROM categories;

-- 2. Restore Categories (Root level)
INSERT INTO categories (id, name, slug, status) VALUES
(1, 'Karadant', 'karadant', 'active'),
(2, 'Laddu',    'laddu',    'active'),
(3, 'Gift Box', 'gift-box', 'active'),
(4, 'Namkeen',  'namkeen',  'active'),
(5, 'Gifting',  'gifting',  'active');

-- 3. Restore Products (Unique and carefully curated)
INSERT INTO products (id, category_id, name, slug, short_description, base_price, stock_quantity, featured, status) VALUES
-- Karadant
(1001, 1, 'Premium Vijaya Karadant', 'premium-vijaya-karadant', 'Our signature Karadant made with premium nuts and jaggery.', 720.00, 100, 1, 'published'),
(1002, 1, 'Classic Vijaya Karadant',  'classic-vijaya-karadant',  'Traditional Vijaya Karadant with authentic taste and texture.', 600.00, 150, 1, 'published'),
(1003, 1, 'Supreme Vijaya Karadant',  'supreme-vijaya-karadant',  'Richer blend of nuts and jaggery for a premium bite.', 420.00, 80,  1, 'published'),
(1004, 1, 'Regal Anjeer Karadant',    'regal-anjeer-karadant',    'Anjeer-infused Karadant with a naturally rich sweetness.', 880.00, 90,  1, 'published'),
(1005, 1, 'Dink Karadant',           'dink-karadant',           'Nutritious Karadant with edible gum for extra energy.', 650.00, 70,  1, 'published'),

-- Laddu
(1009, 2, 'Dink Laddu',         'dink-laddu',         'Traditional dink laddu for daily nourishment.', 480.00, 120, 1, 'published'),
(1010, 2, 'Ragi Laddu',         'ragi-laddu',         'Wholesome ragi laddus with a roasted nutty taste.', 450.00, 130, 1, 'published'),
(1011, 2, 'Besan Laddu',        'besan-laddu',        'Classic besan laddu made with pure ghee and gram flour.', 420.00, 110, 1, 'published'),
(1012, 2, 'Premium Ladagi Laddu','premium-ladagi-laddu','Premium laddu assortment with rich dry fruits.', 550.00, 100, 1, 'published'),
(1013, 2, 'Til Laddu',          'til-laddu',          'Sesame laddus with a warm jaggery sweetness.', 400.00, 80,  1, 'published'),

-- Gift Boxes
(1040, 3, 'Premium Gift Box',   'premium-gift-box',   'A luxurious assortment of our finest Karadant varieties.', 950.00, 50, 1, 'published'),
(1041, 3, 'Festive Special Box','festive-special-box','Celebrate with our curated festive collection.', 1200.00, 40, 1, 'published'),
(1042, 3, 'Tilkut Gift Box',    'tilkut-gift-box',    'Traditional Tilkut sweets in a premium festive box.', 950.00, 50, 1, 'published'),
(1043, 3, 'Anjeer Gift Box',    'anjeer-gift-box',    'Exotic Anjeer sweets beautifully packed for special occasions.', 950.00, 50, 1, 'published'),

-- Namkeen
(2001, 4, 'Spicy Mix Namkeen',  'spicy-mix-namkeen',  'A bold namkeen mix with signature house spices.', 320.00, 200, 1, 'published'),
(2002, 4, 'Golden Sev',         'golden-sev',         'Crispy golden sev, light and perfectly seasoned.', 280.00, 210, 1, 'published'),
(2003, 4, 'Masala Peanuts',     'masala-peanuts',     'Crunchy masala-coated peanuts with balanced heat.', 250.00, 220, 1, 'published'),
(2004, 4, 'Premium Mixture',    'premium-mixture',    'Premium crunchy mixture perfect for tea-time snacking.', 350.00, 180, 1, 'published'),
(2005, 4, 'Butter Muruku',      'butter-muruku',      'Traditional butter muruku with a crisp bite.', 320.00, 160, 1, 'published'),
(2006, 4, 'Rice Kodubale',      'rice-kodubale',      'Rice flour kodubale with classic spice blend.', 320.00, 150, 1, 'published');

-- 4. Restore Product Images
INSERT INTO product_images (product_id, image_path, is_main) VALUES
-- Karadant
(1001, 'assets/images/homepage/New folder/karant/bestseeler karadant (1).png', 1),
(1002, 'assets/images/homepage/New folder/karant/bestseeler karadant (2).png', 1),
(1003, 'assets/images/homepage/The Karadant Range (1).png', 1),
(1004, 'assets/images/homepage/New folder/karant/bestseeler karadant (7).png', 1),
(1005, 'assets/images/homepage/New folder/karant/bestseeler karadant (5).png', 1),
-- Laddu
(1009, 'assets/images/homepage/New folder/bestseller-laddu 1.png', 1),
(1010, 'assets/images/homepage/New folder/bestseller-laddu 2.png', 1),
(1011, 'assets/images/homepage/New folder/bestseller-laddu 3.png', 1),
(1012, 'assets/images/homepage/New folder/bestseller-laddu 4.png', 1),
(1013, 'assets/images/homepage/New folder/bestseller-laddu6.png', 1),
-- Gift Boxes
(1040, 'assets/images/banners/gifing/Featured Gifting Specials (1).png', 1),
(1041, 'assets/images/banners/gifing/Featured Gifting Specials (2).png', 1),
(1042, 'assets/images/banners/gifing/Featured Gifting Specials (3).png', 1),
(1043, 'assets/images/banners/gifing/Featured Gifting Specials (4).png', 1),
-- Namkeen
(2001, 'assets/images/homepage/Best Sellers (1).png', 1),
(2002, 'assets/images/homepage/Best Sellers (2).png', 1),
(2003, 'assets/images/homepage/Best Sellers (5).png', 1),
(2004, 'assets/images/homepage/Best Sellers (7).png', 1),
(2005, 'assets/images/banners/namkeen-page/our signature  (6).png', 1),
(2006, 'assets/images/banners/namkeen-page/our signature  (7).png', 1);
