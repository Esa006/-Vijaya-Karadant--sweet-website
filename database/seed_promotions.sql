INSERT IGNORE INTO promotions (
    section_id, title, description, image_path,
    btn1_text, btn1_link, btn2_text, btn2_link,
    stat1_val, stat1_label, stat2_val, stat2_label, stat3_val, stat3_label, is_active
) VALUES (
    'curated-combos',
    'Curated Combos for Every Celebration',
    'Thoughtfully crafted selections designed for gifting and festive moments. Discover the perfect harmony of traditional flavors and modern luxury.',
    'assets/images/homepage/Celebration.png',
    'View Offers', '#',
    'View Catalogue', '#',
    '50+', 'Varieties',
    'SINCE 1907', 'Handcrafted with Care',
    '4.9/5', 'rating',
    1
);
