<?php
require_once __DIR__ . '/../config/config.php';
$db = Database::getInstance();

$ids = [1040, 1041, 1042, 1043];
foreach ($ids as $id) {
    echo "Product #{$id}:\n";
    $p = $db->query("SELECT name, slug FROM products WHERE id = {$id}")->fetch(PDO::FETCH_ASSOC);
    if ($p) {
        echo "  Name: {$p['name']} | Slug: {$p['slug']}\n";
    } else {
        echo "  Product not found.\n";
    }
    
    $imgs = $db->query("SELECT * FROM product_images WHERE product_id = {$id}")->fetchAll(PDO::FETCH_ASSOC);
    echo "  Gallery Images:\n";
    foreach ($imgs as $img) {
        echo "    - ID: {$img['id']} | Path: {$img['image_path']} | Is Main: {$img['is_main']}\n";
    }
}
