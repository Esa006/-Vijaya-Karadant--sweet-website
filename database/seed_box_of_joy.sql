-- Create a specific product for the Box of Joy
INSERT IGNORE INTO products (id, name, slug, short_description, description, base_price, status, featured) 
VALUES (999, 'Customized Gift Box', 'box-of-joy', 'Personalize your gifting experience.', '', 500.00, 'published', 0);

-- Insert multiple images for the slider
DELETE FROM product_images WHERE product_id = 999;
INSERT INTO product_images (product_id, image_path, is_main) VALUES 
(999, 'assets/images/homepage/gift-box.png', 1),
(999, 'assets/images/homepage/Collections (1).png', 0),
(999, 'assets/images/homepage/Best Sellers (1).png', 0);
