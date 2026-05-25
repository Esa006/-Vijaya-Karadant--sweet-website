-- Restore Namkeen Signature Collection Products and Images
USE sweets_db;

-- Ensure Namkeen Category exists
INSERT IGNORE INTO categories (id, name, slug, status) VALUES (3, 'Namkeen', 'namkeen', 1);

-- Insert/Update Namkeen Products (IDs 10-18)
INSERT INTO products (id, category_id, name, slug, short_description, base_price, status, featured) VALUES
(10, 3, 'Spiced Bikaneri Bhujia', 'spiced-bikaneri-bhujia', 'Authentic Rajasthani recipe with moth beans and a secret spice blend', 150.00, 'published', 1),
(11, 3, 'Royal Dal Moth', 'royal-dal-moth', 'Crispy fried lentils mixed with fine sev and aromatic dry spices', 180.00, 'published', 1),
(12, 3, 'Khatta Meetha Mix', 'khatta-meetha-mix', 'A perfect balance of sweet and tangy flavors with peanuts and sago', 160.00, 'published', 1),
(13, 3, 'Spicy Ratlami Sev', 'spicy-ratlami-sev', 'Robust clove-flavored thick sev, al Malwa specialty for spice lovers', 170.00, 'published', 1),
(14, 3, 'All-in-One Mix', 'all-in-one-mix', 'A signature blend of various namkeens for the ultimate snacking experience', 200.00, 'published', 1),
(15, 3, 'Corn Flakes Mix', 'corn-flakes-mix', 'Crispy corn flakes mixed with roasted nuts and mild spices', 190.00, 'published', 1),
(16, 3, 'Garlic Sev', 'garlic-sev', 'Crunchy sev infused with fresh garlic and traditional spices', 175.00, 'published', 1),
(17, 3, 'Masala Peanuts', 'masala-peanuts', 'Zesty and spicy peanuts, perfectly roasted for a great crunch', 140.00, 'published', 1),
(18, 3, 'Moong Dal', 'moong-dal', 'Classic salted moong dal, light and protein-rich snack', 130.00, 'published', 1)
ON DUPLICATE KEY UPDATE 
    featured = 1,
    status = 'published';

-- Restore Correct Signature Images
DELETE FROM product_images WHERE product_id BETWEEN 10 AND 18;
INSERT INTO product_images (product_id, image_path, is_main) VALUES
(10, 'assets/images/banners/namkeen-page/our signature  (1).png', 1),
(11, 'assets/images/banners/namkeen-page/our signature  (5).png', 1),
(12, 'assets/images/banners/namkeen-page/our signature  (6).png', 1),
(13, 'assets/images/banners/namkeen-page/our signature  (7).png', 1),
(14, 'assets/images/banners/namkeen-page/our signature  (8).png', 1),
(15, 'assets/images/banners/namkeen-page/our signature  (9).png', 1),
(16, 'assets/images/banners/namkeen-page/our signature  (10).png', 1),
(17, 'assets/images/banners/namkeen-page/our signature  (11).png', 1),
(18, 'assets/images/products/Crispy (1).png', 1); -- Fallback for 18 as I have 11 signature images but need to map correctly
