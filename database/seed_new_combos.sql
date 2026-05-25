-- Seed New Combo Offers
-- This script adds the 20 new combo images as products in the combos table.

INSERT INTO combos (name, slug, description, category, price, image, is_active) VALUES
('Premium Traditional Combo 01', 'premium-traditional-combo-01', 'A curated selection of our finest traditional sweets.', 'traditional', 999.00, 'assets/images/combo-offer/combo-offer-01.jpeg', 1),
('Festive Family Pack 02', 'festive-family-pack-02', 'Perfect for sharing with the whole family during celebrations.', 'festive', 1499.00, 'assets/images/combo-offer/combo-offer-02.jpeg', 1),
('Gourmet Sweet Assortment 03', 'gourmet-sweet-assortment-03', 'Indulge in a variety of gourmet sweet flavors.', 'gourmet', 1299.00, 'assets/images/combo-offer/combo-offer-03.jpeg', 1),
('Luxury Celebration Box 04', 'luxury-celebration-box-04', 'Our most luxurious sweets in one elegant package.', 'luxury', 1999.00, 'assets/images/combo-offer/combo-offer-04.jpeg', 1),
('Artisanal Karadant Selection 05', 'artisanal-karadant-selection-05', 'Traditional Karadant made with artisanal care.', 'karadant', 899.00, 'assets/images/combo-offer/combo-offer-05.jpeg', 1),
('Classic Sweet Duo 06', 'classic-sweet-duo-06', 'Two of our most loved sweet treats in one pack.', 'classic', 599.00, 'assets/images/combo-offer/combo-offer-06.jpeg', 1),
('Health-Conscious Sweet Mix 07', 'health-conscious-sweet-mix-07', 'Sweets made with healthy ingredients and minimal sugar.', 'healthy', 1099.00, 'assets/images/combo-offer/combo-offer-07.jpeg', 1),
('Royal Sweet Platter 08', 'royal-sweet-platter-08', 'A platter fit for royalty, featuring assorted delicacies.', 'royal', 2499.00, 'assets/images/combo-offer/combo-offer-08.jpeg', 1),
('Grand Festival Combo 09', 'grand-festival-combo-09', 'Celebrate in grand style with this massive combo pack.', 'festive', 2999.00, 'assets/images/combo-offer/combo-offer-09.jpeg', 1),
('Signature Sweet Box 10', 'signature-sweet-box-10', 'Our signature sweets, hand-picked for perfection.', 'signature', 1199.00, 'assets/images/combo-offer/combo-offer-10.jpeg', 1),
('Traditional Delights 11', 'traditional-delights-11', 'Delightful traditional sweets for every occasion.', 'traditional', 799.00, 'assets/images/combo-offer/combo-offer-11.jpeg', 1),
('Sweet Heritage Pack 12', 'sweet-heritage-pack-12', 'Experience the rich heritage of our traditional recipes.', 'heritage', 1399.00, 'assets/images/combo-offer/combo-offer-12.jpeg', 1),
('Premium Laddu Mix 13', 'premium-laddu-mix-13', 'An assortment of our premium laddus.', 'laddu', 849.00, 'assets/images/combo-offer/combo-offer-13.jpeg', 1),
('Crunchy Namkeen Combo 14', 'crunchy-namkeen-combo-14', 'A mix of our best selling namkeen and snacks.', 'namkeen', 649.00, 'assets/images/combo-offer/combo-offer-14.jpeg', 1),
('Sweet & Spicy Pair 15', 'sweet-and-spicy-pair-15', 'The perfect balance of sweet treats and spicy snacks.', 'mixed', 899.00, 'assets/images/combo-offer/combo-offer-15.jpeg', 1),
('Corporate Gifting Pack 16', 'corporate-gifting-pack-16', 'Elegant packaging and premium taste, ideal for gifting.', 'gifting', 1599.00, 'assets/images/combo-offer/combo-offer-16.jpeg', 1),
('Homecoming Special 17', 'homecoming-special-17', 'A warm welcome with our most nostalgic sweets.', 'special', 1249.00, 'assets/images/combo-offer/combo-offer-17.jpeg', 1),
('Evening Snack Mix 18', 'evening-snack-mix-18', 'Perfect accompaniments for your evening tea.', 'snack', 549.00, 'assets/images/combo-offer/combo-offer-18.jpeg', 1),
('Bestseller Combo 19', 'bestseller-combo-19', 'A collection of our top 5 best selling items.', 'bestseller', 1799.00, 'assets/images/combo-offer/combo-offer-19.jpeg', 1),
('Chef Special Selection 20', 'chef-special-selection-20', 'Hand-crafted selection by our master chefs.', 'special', 2199.00, 'assets/images/combo-offer/combo-offer-20.jpeg', 1);

-- Template for combo_items (You need to fill product_ids after finding them in products table)
-- INSERT INTO combo_items (combo_id, product_id, quantity) VALUES
-- (11, [PRODUCT_ID_HERE], 1),
-- (11, [PRODUCT_ID_HERE], 1),
-- ...
