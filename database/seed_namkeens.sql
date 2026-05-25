-- Add Namkeen Category
INSERT IGNORE INTO categories (id, name, slug) VALUES (3, 'Namkeen', 'namkeen');

-- Insert 4 Namkeen Products
INSERT IGNORE INTO products (id, category_id, name, slug, short_description, description, base_price, status, featured) VALUES
(10, 3, 'Spiced Bikaneri Bhujia', 'spiced-bikaneri-bhujia', 'Authentic Rajasthani recipe with moth beans and a secret spice blend', '', 150.00, 'published', 0),
(11, 3, 'Royal Dal Moth', 'royal-dal-moth', 'Crispy fried lentils mixed with fine sev and aromatic dry spices', '', 180.00, 'published', 0),
(12, 3, 'Khatta Meetha Mix', 'khatta-meetha-mix', 'A perfect balance of sweet and tangy flavors with peanuts and sago', '', 160.00, 'published', 0),
(13, 3, 'Spicy Ratlami Sev', 'spicy-ratlami-sev', 'Robust clove-flavored thick sev, al Malwa specialty for spice lovers', '', 170.00, 'published', 0);

-- Insert Images
DELETE FROM product_images WHERE product_id IN (10, 11, 12, 13);
INSERT IGNORE INTO product_images (product_id, image_path, is_main) VALUES
(10, 'assets/images/products/Crispy (1).png', 1),
(11, 'assets/images/products/Crispy (2).png', 1),
(12, 'assets/images/products/Crispy (3).png', 1),
(13, 'assets/images/products/Crispy (4).png', 1);
