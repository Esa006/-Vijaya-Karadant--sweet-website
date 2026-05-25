<?php
require_once 'config/config.php';
require_once 'config/Database.php';

try {
    $db = Database::getInstance();
    $sql = "SELECT p.id, p.name, 
                   (SELECT COUNT(*) FROM product_images WHERE product_id = p.id) as total_images,
                   (SELECT COUNT(*) FROM product_images WHERE product_id = p.id AND is_main = 1) as main_count,
                   (SELECT image_path FROM product_images WHERE product_id = p.id AND is_main = 1 LIMIT 1) as main_img_path
            FROM products p 
            WHERE p.category_id = 4"; 
            
    $stmt = $db->query($sql);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Namkeen Products Image Audit:\n";
    foreach ($results as $row) {
        echo "ID: {$row['id']} | Name: {$row['name']}\n";
        echo "  - Total Images in DB: {$row['total_images']}\n";
        echo "  - How many are marked 'Main': {$row['main_count']}\n";
        echo "  - Path of first Main image: " . ($row['main_img_path'] ?: 'NONE') . "\n";
        echo "---------------------------------\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
