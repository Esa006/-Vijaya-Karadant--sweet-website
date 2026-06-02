<?php
require_once __DIR__ . '/../config/config.php';
$db = Database::getInstance();

echo "=== CURRENT IMAGES FOR BESAN LADDU (ID: 1011) ===\n";
$stmt = $db->prepare("SELECT * FROM product_images WHERE product_id = 1011");
$stmt->execute();
$dbImages = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($dbImages as $img) {
    echo "ID: " . $img['id'] . " | Path: " . $img['image_path'] . " | Main: " . $img['is_main'] . "\n";
}

$dir = __DIR__ . '/../assets/images/Singal/Besan-laddu';
echo "\n=== IMAGES IN SPECIFIED DIR ($dir) ===\n";
if (is_dir($dir)) {
    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        $fullPath = $dir . '/' . $file;
        echo "File: $file | Size: " . filesize($fullPath) . " bytes\n";
    }
} else {
    echo "Directory does not exist!\n";
}
