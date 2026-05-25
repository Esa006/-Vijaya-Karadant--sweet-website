<?php
require_once 'config/config.php';
require_once 'config/Database.php';

try {
    $db = Database::getInstance();
    
    // Add columns if they don't exist
    $columns = [
        'subtitle' => "ALTER TABLE promotions ADD COLUMN subtitle VARCHAR(255) AFTER title",
        'discount_badge' => "ALTER TABLE promotions ADD COLUMN discount_badge VARCHAR(50) AFTER description",
        'timer_end' => "ALTER TABLE promotions ADD COLUMN timer_end DATETIME AFTER btn2_link"
    ];
    
    foreach ($columns as $col => $sql) {
        $check = $db->query("SHOW COLUMNS FROM promotions LIKE '$col'")->fetch();
        if (!$check) {
            $db->exec($sql);
            echo "Added column: $col\n";
        } else {
            echo "Column $col already exists.\n";
        }
    }
    
    // Seed the Festival Offer
    $festivalData = [
        'section_id' => 'festival-offers',
        'title' => 'Vibrant Festival Offers',
        'subtitle' => 'Celebrate with Sweet Savings',
        'description' => 'Experience the joy of gifting with our exclusive festival discounts. Handcrafted sweets, premium packaging, and timeless traditions delivered to your doorstep.',
        'discount_badge' => 'UP TO 30% OFF',
        'image_path' => 'assets/images/homepage/FestivalOffer.png',
        'btn1_text' => 'Explore Offers',
        'btn1_link' => 'category-products.php?slug=gifting',
        'timer_end' => date('Y-m-d H:i:s', strtotime('+7 days')),
        'is_active' => 1
    ];
    
    $checkPromo = $db->prepare("SELECT id FROM promotions WHERE section_id = ?");
    $checkPromo->execute(['festival-offers']);
    $existing = $checkPromo->fetch();
    
    if ($existing) {
        $sql = "UPDATE promotions SET title=?, subtitle=?, description=?, discount_badge=?, image_path=?, btn1_text=?, btn1_link=?, timer_end=?, is_active=? WHERE section_id=?";
        $db->prepare($sql)->execute([
            $festivalData['title'], $festivalData['subtitle'], $festivalData['description'], $festivalData['discount_badge'],
            $festivalData['image_path'], $festivalData['btn1_text'], $festivalData['btn1_link'], $festivalData['timer_end'],
            $festivalData['is_active'], 'festival-offers'
        ]);
        echo "Updated Festival Offer data.\n";
    } else {
        $sql = "INSERT INTO promotions (section_id, title, subtitle, description, discount_badge, image_path, btn1_text, btn1_link, timer_end, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $db->prepare($sql)->execute([
            $festivalData['section_id'], $festivalData['title'], $festivalData['subtitle'], $festivalData['description'],
            $festivalData['discount_badge'], $festivalData['image_path'], $festivalData['btn1_text'], $festivalData['btn1_link'],
            $festivalData['timer_end'], $festivalData['is_active']
        ]);
        echo "Inserted Festival Offer data.\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
