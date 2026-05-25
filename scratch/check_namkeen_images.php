<?php
require_once 'config/config.php';
require_once 'config/Database.php';

try {
    $db = Database::getInstance();
    $sql = "SELECT p.id, p.name, p.image_path, 
                   (SELECT COUNT(*) FROM product_images WHERE product_id = p.id) as gallery_count,
                   (SELECT image_path FROM product_images WHERE product_id = p.id AND is_main = 1 LIMIT 1) as main_gallery_img
            FROM products p 
            WHERE p.category_id = 4"; // Namkeen category ID is 4
            
    $stmt = $db->query($sql);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Namkeen Products Detailed Status:\n";
    foreach ($results as $row) {
        echo "ID: {$row['id']} | Name: {$row['name']}\n";
        echo "  - Main Image Path (DB): " . ($row['image_path'] ?: 'NULL') . "\n";
        echo "  - Gallery Images Count: {$row['gallery_count']}\n";
        echo "  - Main Gallery Image: " . ($row['main_gallery_img'] ?: 'None') . "\n";
        echo "---------------------------------\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
