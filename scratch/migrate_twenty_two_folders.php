<?php
require_once __DIR__ . '/../config/config.php';
$db = Database::getInstance();

echo "=== MIGRATION AND INTEGRATION OF TWENTY-TWO FOLDERS ===\n";

$db->beginTransaction();
try {
    // 1. Restore Moong Dal Laddu (ID: 2013) if soft-deleted
    echo "Checking Moong Dal Laddu (ID: 2013)...\n";
    $stmtCheckMoong = $db->prepare("SELECT COUNT(*) FROM products WHERE id = 2013");
    $stmtCheckMoong->execute();
    if ($stmtCheckMoong->fetchColumn() > 0) {
        $stmtRestore = $db->prepare("UPDATE products SET deleted_at = NULL, status = 'published' WHERE id = 2013");
        $stmtRestore->execute();
        echo "  Restored Moong Dal Laddu (ID: 2013) to active status.\n";
    } else {
        throw new Exception("Moong Dal Laddu product ID 2013 not found in database.");
    }

    // 2. Create Dry Fruit Honey if it doesn't exist
    $stmtCheckHoney = $db->prepare("SELECT id FROM products WHERE slug = 'dry-fruit-honey'");
    $stmtCheckHoney->execute();
    $honeyId = $stmtCheckHoney->fetchColumn();
    if (!$honeyId) {
        echo "Creating product: Dry Fruit Honey...\n";
        $stmtInsHoney = $db->prepare("
            INSERT INTO products (category_id, name, slug, short_description, description, base_price, sale_price, tax_rate, sku, status, featured, stock_quantity)
            VALUES (2, 'Dry Fruit Honey', 'dry-fruit-honey', 'Premium dry fruits soaked in organic honey.', 'Premium dry fruits soaked in organic honey for a healthy, delicious treat.', 450.00, NULL, 5.00, 'DF-HONEY-500G', 'published', 0, 100)
        ");
        $stmtInsHoney->execute();
        $honeyId = (int)$db->lastInsertId();
        echo "  Created Dry Fruit Honey with ID: $honeyId\n";

        // Add inventory
        $stmtInvHoney = $db->prepare("INSERT INTO inventory (product_id, stock) VALUES (:pid, 100)");
        $stmtInvHoney->execute(['pid' => $honeyId]);

        // Add variant
        $stmtVarHoney = $db->prepare("
            INSERT INTO product_variants (product_id, weight, label, price, stock)
            VALUES (:pid, '500g', '500g Jar Pack', 450.00, 100)
        ");
        $stmtVarHoney->execute(['pid' => $honeyId]);
        echo "  Initialized inventory and variants for Dry Fruit Honey.\n";
    } else {
        $honeyId = (int)$honeyId;
        echo "Product: Dry Fruit Honey already exists with ID: $honeyId\n";
    }

    // 3. Create Marvel Dates if it doesn't exist
    $stmtCheckDates = $db->prepare("SELECT id FROM products WHERE slug = 'marvel-dates'");
    $stmtCheckDates->execute();
    $datesId = $stmtCheckDates->fetchColumn();
    if (!$datesId) {
        echo "Creating product: Marvel Dates...\n";
        $stmtInsDates = $db->prepare("
            INSERT INTO products (category_id, name, slug, short_description, description, base_price, sale_price, tax_rate, sku, status, featured, stock_quantity)
            VALUES (2, 'Marvel Dates', 'marvel-dates', 'Exotic dry fruit stuffed dates.', 'Exotic premium dates stuffed with a rich assortment of dry fruits and nuts.', 480.00, NULL, 5.00, 'MARVEL-DATES', 'published', 0, 100)
        ");
        $stmtInsDates->execute();
        $datesId = (int)$db->lastInsertId();
        echo "  Created Marvel Dates with ID: $datesId\n";

        // Add inventory
        $stmtInvDates = $db->prepare("INSERT INTO inventory (product_id, stock) VALUES (:pid, 100)");
        $stmtInvDates->execute(['pid' => $datesId]);

        // Add variant
        $stmtVarDates = $db->prepare("
            INSERT INTO product_variants (product_id, weight, label, price, stock)
            VALUES (:pid, '500g', '500g Box Pack', 480.00, 100)
        ");
        $stmtVarDates->execute(['pid' => $datesId]);
        echo "  Initialized inventory and variants for Marvel Dates.\n";
    } else {
        $datesId = (int)$datesId;
        echo "Product: Marvel Dates already exists with ID: $datesId\n";
    }

    // 4. Define Folders and Products Group Mapping
    $productMappings = [
        1001 => [
            'name' => 'Premium Vijaya Karadant',
            'folders' => ['Premium-250g', 'Premium-500g']
        ],
        1003 => [
            'name' => 'Supreme Vijaya Karadant',
            'folders' => ['Supreme-0', 'Supreme-250g', 'Supreme-500g', 'Supreme-tub']
        ],
        1004 => [
            'name' => 'Regal Anjeer Karadant',
            'folders' => ['regal-anjeer', 'Regal-anjeer-samll-tub', 'Regal-anjeer-tub']
        ],
        1010 => [
            'name' => 'Ragi Laddu',
            'folders' => ['Raagi-laddu']
        ],
        1013 => [
            'name' => 'Til Laddu',
            'folders' => ['Till-laddu']
        ],
        1014 => [
            'name' => 'Premium Otts Laddu',
            'folders' => ['Oats-laddu']
        ],
        1015 => [
            'name' => 'Peanut Laddu',
            'folders' => ['Peanut-laddu']
        ],
        1017 => [
            'name' => 'Gandahagiri Laddu',
            'folders' => ['Gandhgiri-laddu-box', 'Gandhagiri-laddu-small-pink-pack']
        ],
        2035 => [
            'name' => 'Gandahagiri Laddu (1kg Bucket)',
            'folders' => ['Gandhgiri-laddu-bucket']
        ],
        2033 => [
            'name' => 'Traditional Lagdi Pak (500g)',
            'folders' => ['Lagadi-pak-premium-laddu', 'Lagdipak-cubes', 'Lagdipak-laddu']
        ],
        2013 => [
            'name' => 'Moong Dal Laddu',
            'folders' => ['Moong-dal-laddu']
        ],
        $honeyId => [
            'name' => 'Dry Fruit Honey',
            'folders' => ['Dry-fruit-honey']
        ],
        $datesId => [
            'name' => 'Marvel Dates',
            'folders' => ['Marvel-dates']
        ]
    ];

    $singalDir = __DIR__ . '/../assets/images/Singal';
    $destDir = __DIR__ . '/../assets/images/products';
    if (!is_dir($destDir)) {
        mkdir($destDir, 0755, true);
    }

    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

    foreach ($productMappings as $pid => $mapping) {
        echo "\nProcessing Product ID: $pid ({$mapping['name']})...\n";
        
        $images = [];
        foreach ($mapping['folders'] as $folder) {
            $srcDir = $singalDir . '/' . $folder;
            if (!is_dir($srcDir)) {
                echo "  Warning: Source directory $srcDir does not exist! Skipping folder.\n";
                continue;
            }
            
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
        }
        
        if (empty($images)) {
            echo "  Warning: No images found for Product ID: $pid! Skipping.\n";
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
        
        $insertedCount = 0;
        $seenHashes = [];
        
        foreach ($images as $img) {
            $md5 = md5_file($img['src_path']);
            if (in_array($md5, $seenHashes)) {
                // Deduplicate identical files
                continue;
            }
            $seenHashes[] = $md5;
            
            $newFilename = "product-" . $pid . "-" . substr($md5, 0, 10) . ".jpg";
            $destPath = $destDir . '/' . $newFilename;
            
            if (!copy($img['src_path'], $destPath)) {
                throw new Exception("Failed to copy " . $img['filename'] . " to " . $destPath);
            }
            
            $dbPath = "assets/images/products/" . $newFilename;
            $isMain = ($insertedCount === 0) ? 1 : 0;
            
            $stmtIns->execute([
                'pid' => $pid,
                'path' => $dbPath,
                'is_main' => $isMain
            ]);
            
            $insertedCount++;
            echo sprintf("  Mapped: %s -> %s (is_main: %d)\n", 
                $img['filename'], $newFilename, $isMain);
        }
        echo "  Successfully mapped $insertedCount unique images for Product ID: $pid.\n";
    }

    $db->commit();
    echo "\nAll migrations succeeded!\n";
} catch (Exception $e) {
    $db->rollBack();
    echo "\nMigration failed: " . $e->getMessage() . "\n";
}
