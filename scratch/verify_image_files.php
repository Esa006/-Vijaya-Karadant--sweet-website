<?php
require_once __DIR__ . '/../config/config.php';
$db = Database::getInstance();

$stmt = $db->query("SELECT p.id as product_id, p.name as product_name, pi.id as image_id, pi.image_path, pi.is_main FROM product_images pi JOIN products p ON pi.product_id = p.id ORDER BY p.id, pi.is_main DESC");
$images = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "=== VERIFYING IMAGE FILES ON DISK ===\n";
$missingCount = 0;
foreach ($images as $img) {
    $fullPath = __DIR__ . '/../' . ltrim($img['image_path'], '/');
    if (!file_exists($fullPath)) {
        echo "Missing file: [Product ID: {$img['product_id']} | {$img['product_name']}] Image ID: {$img['image_id']} | Mapped Path: {$img['image_path']} (Is Main: {$img['is_main']})\n";
        $missingCount++;
    }
}

if ($missingCount === 0) {
    echo "All mapped image files exist on disk!\n";
} else {
    echo "Total missing files: $missingCount\n";
}
