<?php
require_once __DIR__ . '/../config/config.php';
$db = Database::getInstance();

$ids = [1002, 1005, 1009, 2034];
foreach ($ids as $id) {
    echo "=== CURRENT IMAGES FOR ID: $id ===\n";
    $stmt = $db->prepare("SELECT * FROM product_images WHERE product_id = :id");
    $stmt->execute(['id' => $id]);
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($images as $img) {
        echo "ID: " . $img['id'] . " | Path: " . $img['image_path'] . " | Main: " . $img['is_main'] . "\n";
    }
}
