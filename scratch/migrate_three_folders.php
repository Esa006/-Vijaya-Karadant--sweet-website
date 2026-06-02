<?php
require_once __DIR__ . '/../config/config.php';
$db = Database::getInstance();

echo "=== MIGRATION AND INTEGRATION OF THREE FOLDERS ===\n";

$db->beginTransaction();
try {
    // 1. Create Dink Laddu (1kg Bucket) if it doesn't exist
    $stmtCheck = $db->prepare("SELECT id FROM products WHERE slug = 'dink-laddu-bucket'");
    $stmtCheck->execute();
    $bucketId = $stmtCheck->fetchColumn();
    
    if (!$bucketId) {
        echo "Creating product: Dink Laddu (1kg Bucket)...\n";
        $stmtInsProd = $db->prepare("
            INSERT INTO products (category_id, name, slug, short_description, description, base_price, sale_price, tax_rate, sku, status, featured, stock_quantity)
            VALUES (2, 'Dink Laddu (1kg Bucket)', 'dink-laddu-bucket', 'Classic Dink Laddus in a heritage tin bucket.', 'Handcrafted Dink Laddus made with organic jaggery, pure ghee, and dry fruits, packed in a premium heritage bucket.', 960.00, NULL, 5.00, 'DL-BUCKET-1KG', 'published', 0, 100)
        ");
        $stmtInsProd->execute();
        $bucketId = (int)$db->lastInsertId();
        echo "  Created product with ID: $bucketId\n";
        
        // Add inventory
        $stmtInv = $db->prepare("INSERT INTO inventory (product_id, stock) VALUES (:pid, 100)");
        $stmtInv->execute(['pid' => $bucketId]);
        
        // Add variant
        $stmtVar = $db->prepare("
            INSERT INTO product_variants (product_id, weight, label, price, stock)
            VALUES (:pid, '1kg', '1kg Bucket Pack', 960.00, 100)
        ");
        $stmtVar->execute(['pid' => $bucketId]);
        echo "  Initialized inventory and variants for the new bucket product.\n";
    } else {
        echo "Product: Dink Laddu (1kg Bucket) already exists with ID: $bucketId\n";
    }
    
    // 2. Update Combos to use the new bucket ID instead of 1009
    // Combo 35: Dink Laddu Bucket & Premium Karadant
    $stmtUpd35 = $db->prepare("UPDATE combo_items SET product_id = :bucketId, quantity = 1 WHERE combo_id = 35 AND product_id = 1009");
    $stmtUpd35->execute(['bucketId' => $bucketId]);
    
    // Combo 49: Regal Anjeer Tub & Dink Laddu Bucket
    $stmtUpd49 = $db->prepare("UPDATE combo_items SET product_id = :bucketId, quantity = 1 WHERE combo_id = 49 AND product_id = 1009");
    $stmtUpd49->execute(['bucketId' => $bucketId]);
    
    echo "Updated Combo 35 and Combo 49 to use Dink Laddu (1kg Bucket) (ID: $bucketId) instead of regular Dink Laddu.\n";

    // 3. Define Folder Mapping
    $foldersMapping = [
        'Classic' => [
            'src_dir' => __DIR__ . '/../assets/images/Singal/Classic',
            'product_id' => 1002,
            'name' => 'Classic Vijaya Karadant'
        ],
        'Dink-laddu-box' => [
            'src_dir' => __DIR__ . '/../assets/images/Singal/Dink-laddu-box',
            'product_id' => 1009,
            'name' => 'Dink Laddu'
        ],
        'Dink-laddu-bucket' => [
            'src_dir' => __DIR__ . '/../assets/images/Singal/Dink-laddu-bucket',
            'product_id' => $bucketId,
            'name' => 'Dink Laddu (1kg Bucket)'
        ]
    ];
    
    $destDir = __DIR__ . '/../assets/images/products';
    if (!is_dir($destDir)) {
        mkdir($destDir, 0755, true);
    }
    
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
    
    foreach ($foldersMapping as $folderName => $mapping) {
        $pid = $mapping['product_id'];
        $srcDir = $mapping['src_dir'];
        
        echo "\nProcessing folder: $folderName for Product ID: $pid ({$mapping['name']})...\n";
        
        if (!is_dir($srcDir)) {
            echo "  Warning: Source directory $srcDir does not exist! Skipping.\n";
            continue;
        }
        
        // Scan images
        $images = [];
        $files = scandir($srcDir);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            $filePath = $srcDir . '/' . $file;
            if (is_dir($filePath)) continue;
            
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedExtensions)) {
                continue;
            }
            
            $images[] = [
                'src_path' => $filePath,
                'filename' => $file,
                'size' => filesize($filePath)
            ];
        }
        
        if (empty($images)) {
            echo "  Warning: No images found in $srcDir! Skipping.\n";
            continue;
        }
        
        // Sort by size descending
        usort($images, function($a, $b) {
            return $b['size'] <=> $a['size'];
        });
        
        // Clear old database entries
        $stmtDel = $db->prepare("DELETE FROM product_images WHERE product_id = :pid");
        $stmtDel->execute(['pid' => $pid]);
        echo "  Cleared old product_images records for ID: $pid.\n";
        
        // Copy files and insert into database
        $stmtIns = $db->prepare("INSERT INTO product_images (product_id, image_path, is_main) VALUES (:pid, :path, :is_main)");
        
        foreach ($images as $index => $img) {
            $md5 = md5_file($img['src_path']);
            $newFilename = "product-" . $pid . "-" . substr($md5, 0, 10) . ".jpg";
            $destPath = $destDir . '/' . $newFilename;
            
            if (!copy($img['src_path'], $destPath)) {
                throw new Exception("Failed to copy " . $img['filename'] . " to " . $destPath);
            }
            
            $dbPath = "assets/images/products/" . $newFilename;
            $isMain = ($index === 0) ? 1 : 0;
            
            $stmtIns->execute([
                'pid' => $pid,
                'path' => $dbPath,
                'is_main' => $isMain
            ]);
            
            echo sprintf("  Mapped: %s -> %s (is_main: %d)\n", 
                $img['filename'], $newFilename, $isMain);
        }
    }
    
    $db->commit();
    echo "\nAll migrations succeeded!\n";
} catch (Exception $e) {
    $db->rollBack();
    echo "\nMigration failed: " . $e->getMessage() . "\n";
}
