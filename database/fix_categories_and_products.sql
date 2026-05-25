-- ================================================================
-- Sweets Website — Full Category + Product Fix
-- File: database/fix_categories_and_products.sql
-- Run this to restore all categories and link products correctly
-- ================================================================

USE sweets_db;

-- ── 1. Insert/update all root categories ─────────────────────────
INSERT INTO categories (id, name, slug, status) VALUES
(1, 'Karadant', 'karadant', 'active'),
(2, 'Laddu',    'laddu',    'active'),
(3, 'Gift Box', 'gift-box', 'active'),
(4, 'Namkeen',  'namkeen',  'active'),
(5, 'Gifting',  'gifting',  'active')
ON DUPLICATE KEY UPDATE
    name   = VALUES(name),
    slug   = VALUES(slug),
    status = VALUES(status);

-- ── 2. Ensure all products exist with correct category_id ─────────
-- Karadant products
INSERT INTO products (id, category_id, name, slug, short_description, base_price, stock_quantity, featured, status) VALUES
(1001, 1, 'Premium Vijaya Karadant', 'premium-vijaya-karadant', 'Our signature Karadant made with premium nuts and jaggery.', 720.00, 100, 1, 'published'),
(1002, 1, 'Classic Vijaya Karadant',  'classic-vijaya-karadant',  'Traditional Vijaya Karadant with authentic taste and texture.', 600.00, 150, 1, 'published'),
(1003, 1, 'Supreme Vijaya Karadant',  'supreme-vijaya-karadant',  'Richer blend of nuts and jaggery for a premium bite.', 420.00, 80,  1, 'published'),
(1005, 1, 'Premium Karadant Pack',    'premium-karadant-pack',    'Premium Karadant family pack for festive moments.', 780.00, 70,  1, 'published'),
(1006, 1, 'Premium Karadant Special', 'premium-karadant-special', 'Special edition premium Karadant with rich dry fruits.', 820.00, 60,  1, 'published'),
(1007, 1, 'Regal Anjeer Karadant',    'regal-anjeer-karadant',    'Anjeer-infused Karadant with a naturally rich sweetness.', 880.00, 90,  1, 'published'),
-- Laddu products
(1009, 2, 'Dink Laddu',         'dink-laddu',         'Traditional dink laddu for daily nourishment.', 480.00, 120, 1, 'published'),
(1010, 2, 'Ragi Laddu',         'ragi-laddu',         'Wholesome ragi laddus with a roasted nutty taste.', 450.00, 130, 1, 'published'),
(1011, 2, 'Besan Laddu',        'besan-laddu',        'Classic besan laddu made with pure ghee and gram flour.', 420.00, 110, 1, 'published'),
(1012, 2, 'Premium Ladagi Laddu','premium-ladagi-laddu','Premium laddu assortment with rich dry fruits.', 550.00, 100, 1, 'published'),
(1013, 2, 'Otts Laddu',         'otts-laddu',         'Soft and flavorful laddus with a traditional finish.', 500.00, 90,  1, 'published'),
(1014, 2, 'Til Laddu',          'til-laddu',          'Sesame laddus with a warm jaggery sweetness.', 400.00, 80,  1, 'published'),
(1015, 2, 'Peanut Laddu',       'peanut-laddu',       'Crunchy peanut laddus with jaggery and sesame.', 350.00, 95,  1, 'published'),
(1016, 2, 'Gandhagiri Laddu',   'gandhagiri-laddu',   'Traditional Gandhagiri laddu with premium ingredients.', 520.00, 85,  1, 'published'),
-- Gift Box products
(1040, 3, 'Premium Gift Box',   'premium-gift-box',   'A luxurious assortment of our finest Karadant varieties.', 950.00, 50, 1, 'published'),
(1041, 3, 'Tilkut Gift Box',    'tilkut-gift-box',    'Traditional Tilkut sweets in a premium festive box.', 950.00, 50, 1, 'published'),
(1042, 3, 'Supreme Gift Box',   'supreme-gift-box',   'Our most popular festive collection in a vibrant gift pack.', 950.00, 50, 1, 'published'),
(1043, 3, 'Anjeer Gift Box',    'anjeer-gift-box',    'Exotic Anjeer sweets beautifully packed for special occasions.', 950.00, 50, 1, 'published'),
(1044, 3, 'Dink Laddu Gift Box','dink-laddu-gift-box','Nutritious and delicious Dink Laddus in a premium gift set.', 950.00, 50, 1, 'published'),
(1045, 3, 'Mawa Gift Box',      'mawa-gift-box',      'Rich Mawa-infused delicacies in a royal presentation box.', 950.00, 50, 1, 'published'),
-- Namkeen products
(1018, 4, 'Spicy Mix Namkeen',  'spicy-mix-namkeen',  'A bold namkeen mix with signature house spices.', 320.00, 200, 1, 'published'),
(1019, 4, 'Golden Sev',         'golden-sev',         'Crispy golden sev, light and perfectly seasoned.', 280.00, 210, 1, 'published'),
(1020, 4, 'Masala Peanuts',     'masala-peanuts',     'Crunchy masala-coated peanuts with balanced heat.', 250.00, 220, 1, 'published'),
(1021, 4, 'Premium Mixture',    'premium-mixture',    'Premium crunchy mixture perfect for tea-time snacking.', 350.00, 180, 1, 'published'),
(1022, 4, 'All-in-One Mix',     'all-in-one-mix',     'A crunchy all-in-one namkeen blend with rich flavors.', 280.00, 150, 1, 'published'),
(1023, 4, 'Bengaluru Mix',      'bengaluru-mix',      'Regional style namkeen mix inspired by Bengaluru flavors.', 250.00, 140, 1, 'published'),
(1024, 4, 'Butter Muruku',      'butter-muruku',      'Traditional butter muruku with a crisp bite.', 320.00, 160, 1, 'published'),
(1025, 4, 'Rice Kodubale',      'rice-kodubale',      'Rice flour kodubale with classic spice blend.', 320.00, 150, 1, 'published'),
(1026, 4, 'Garlic Ribbon',      'garlic-ribbon',      'Ribbon snack with rich garlic flavor and crunch.', 320.00, 140, 1, 'published'),
(1027, 4, 'Nippattu',           'nippattu',           'Crisp nippattu with roasted spice notes.', 290.00, 130, 1, 'published'),
(1028, 4, 'Onion Kodubale',     'onion-kodubale',     'Onion-flavored kodubale with spicy crisp texture.', 320.00, 120, 1, 'published'),
(1029, 4, 'Ribbon Pakoda',      'ribbon-pakoda',      'Classic ribbon pakoda with crunchy savory finish.', 320.00, 110, 1, 'published')
ON DUPLICATE KEY UPDATE
    category_id       = VALUES(category_id),
    name              = VALUES(name),
    short_description = VALUES(short_description),
    base_price        = VALUES(base_price),
    stock_quantity    = VALUES(stock_quantity),
    featured          = VALUES(featured),
    status            = VALUES(status);

-- ── 3. Fix any existing products missing category_id using slug patterns ──
-- Assign karadant category to products with 'karadant' in slug
UPDATE products SET category_id = 1
WHERE category_id IS NULL AND slug LIKE '%karadant%';

-- Assign laddu category
UPDATE products SET category_id = 2
WHERE category_id IS NULL AND slug LIKE '%laddu%';

-- Assign namkeen category
UPDATE products SET category_id = 4
WHERE category_id IS NULL AND (
    slug LIKE '%namkeen%' OR slug LIKE '%sev%' OR slug LIKE '%muruku%' OR
    slug LIKE '%kodubale%' OR slug LIKE '%pakoda%' OR slug LIKE '%mixture%' OR
    slug LIKE '%mix%' OR slug LIKE '%peanut%' OR slug LIKE '%ribbon%' OR slug LIKE '%nippattu%'
);

-- Assign gifting to gift-box category
UPDATE products SET category_id = 3
WHERE category_id IS NULL AND slug LIKE '%gift%';

-- ── 4. Ensure product_images exist for all products ───────────────
INSERT IGNORE INTO product_images (product_id, image_path, is_main) VALUES
-- Karadant
(1001, 'assets/images/homepage/New folder/karant/bestseeler karadant (1).png', 1),
(1002, 'assets/images/homepage/New folder/karant/bestseeler karadant (2).png', 1),
(1003, 'assets/images/homepage/The Karadant Range (1).png', 1),
(1005, 'assets/images/homepage/New folder/karant/bestseeler karadant (5).png', 1),
(1006, 'assets/images/homepage/New folder/karant/bestseeler karadant (6).png', 1),
(1007, 'assets/images/homepage/New folder/karant/bestseeler karadant (7).png', 1),
-- Gift Box
(1040, 'assets/images/banners/gifing/Featured Gifting Specials (1).png', 1),
(1041, 'assets/images/banners/gifing/Featured Gifting Specials (2).png', 1),
(1042, 'assets/images/banners/gifing/Featured Gifting Specials (3).png', 1),
(1043, 'assets/images/banners/gifing/Featured Gifting Specials (4).png', 1),
(1044, 'assets/images/banners/gifing/Featured Gifting Specials (5).png', 1),
(1045, 'assets/images/banners/gifing/Featured Gifting Specials (6).png', 1),
-- Laddu
(1009, 'assets/images/homepage/New folder/bestseller-laddu 1.png', 1),
(1010, 'assets/images/homepage/New folder/bestseller-laddu 2.png', 1),
(1011, 'assets/images/homepage/New folder/bestseller-laddu 3.png', 1),
(1012, 'assets/images/homepage/New folder/bestseller-laddu 4.png', 1),
(1013, 'assets/images/homepage/New folder/bestseller-laddu 5.png', 1),
(1014, 'assets/images/homepage/New folder/bestseller-laddu6.png', 1),
(1015, 'assets/images/homepage/New folder/bestseller-laddu 1.png', 1),
(1016, 'assets/images/homepage/New folder/bestseller-laddu 2.png', 1),
-- Namkeen
(1018, 'assets/images/homepage/Best Sellers (1).png', 1),
(1019, 'assets/images/homepage/Best Sellers (2).png', 1),
(1020, 'assets/images/homepage/Best Sellers (5).png', 1),
(1021, 'assets/images/homepage/Best Sellers (7).png', 1),
(1022, 'assets/images/banners/namkeen-page/our signature  (1).png', 1),
(1023, 'assets/images/banners/namkeen-page/our signature  (5).png', 1),
(1024, 'assets/images/banners/namkeen-page/our signature  (6).png', 1),
(1025, 'assets/images/banners/namkeen-page/our signature  (7).png', 1),
(1026, 'assets/images/banners/namkeen-page/our signature  (8).png', 1),
(1027, 'assets/images/banners/namkeen-page/our signature  (9).png', 1),
(1028, 'assets/images/banners/namkeen-page/our signature  (10).png', 1),
(1029, 'assets/images/banners/namkeen-page/our signature  (11).png', 1);

-- ── 5. Verify ─────────────────────────────────────────────────────
SELECT c.name as category, COUNT(p.id) as product_count
FROM categories c
LEFT JOIN products p ON p.category_id = c.id AND p.status = 'published'
GROUP BY c.id, c.name
ORDER BY c.id;
