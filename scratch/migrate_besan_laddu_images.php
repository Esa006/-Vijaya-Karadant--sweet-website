<?php
require_once __DIR__ . '/../config/config.php';
$db = Database::getInstance();

$srcDir = __DIR__ . '/../assets/images/Singal/Besan-laddu';
$destDir = __DIR__ . '/../assets/images/products';

if (!is_dir($destDir)) {
    mkdir($destDir, 0755, true);
}

if (!is_dir($srcDir)) {
    die("Source directory does not exist: $srcDir\n");
}

echo "=== PREPARING BESAN LADDU IMAGES MIGRATION ===\n";

$allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
$images = [];

$files = scandir($srcDir);
foreach ($files as $file) {
    if ($file === '.' || $file === '..') continue;
    $filePath = $srcDir . '/' . $file;
    if (is_dir($filePath)) continue;
    
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExtensions)) {
        echo "Skipping non-image file: $file\n";
        continue;
    }
    
    $images[] = [
        'src_path' => $filePath,
        'filename' => $file,
        'size' => filesize($filePath)
    ];
}

if (empty($images)) {
    die("No images found in $srcDir to migrate!\n");
}

// Sort images by size descending so the largest file size is first (which is typically the high-res campaign master photo)
usort($images, function($a, $b) {
    return $b['size'] <=> $a['size'];
});

echo "Found " . count($images) . " images. Largest is " . $images[0]['filename'] . " (" . $images[0]['size'] . " bytes).\n";

$db->beginTransaction();
try {
    // 1. Delete old images for product ID 1011
    $stmtDel = $db->prepare("DELETE FROM product_images WHERE product_id = 1011");
    $stmtDel->execute();
    echo "Cleared old product_images database records for Besan Laddu (ID: 1011).\n";
    
    // 2. Copy files and insert into database
    $stmtIns = $db->prepare("INSERT INTO product_images (product_id, image_path, is_main) VALUES (1011, :path, :is_main)");
    
    foreach ($images as $index => $img) {
        $md5 = md5_file($img['src_path']);
        $newFilename = "product-1011-" . substr($md5, 0, 10) . ".jpg";
        $destPath = $destDir . '/' . $newFilename;
        
        // Copy the file
        if (!copy($img['src_path'], $destPath)) {
            throw new Exception("Failed to copy " . $img['filename'] . " to " . $destPath);
        }
        
        $dbPath = "assets/images/products/" . $newFilename;
        $isMain = ($index === 0) ? 1 : 0;
        
        $stmtIns->execute([
            'path' => $dbPath,
            'is_main' => $isMain
        ]);
        
        echo sprintf("Copied and mapped: %s -> %s (is_main: %d)\n", 
            $img['filename'], $newFilename, $isMain);
    }
    
    $db->commit();
    echo "Migration completed successfully!\n";
} catch (Exception $e) {
    $db->rollBack();
    echo "Migration failed: " . $e->getMessage() . "\n";
}
