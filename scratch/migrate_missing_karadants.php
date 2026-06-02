<?php
require_once __DIR__ . '/../config/config.php';
$db = Database::getInstance();

echo "=== MIGRATING MISSING IMAGES FOR 2032, 2034 AND FIXING 2033 ===\n";

$db->beginTransaction();
try {
    $destDir = __DIR__ . '/../assets/images/products';
    if (!is_dir($destDir)) {
        mkdir($destDir, 0755, true);
    }

    // ----------------------------------------------------
    // 1. Dink Karadant (1kg Bucket) - Product ID 2034
    // ----------------------------------------------------
    echo "Processing Dink Karadant (1kg Bucket) [ID: 2034]...\n";
    $dinkSrcDir = __DIR__ . '/../assets/images/combos/Combo/Dink-bucket-Gandhagiri-bucket';
    if (is_dir($dinkSrcDir)) {
        // Clear existing product_images for 2034
        $db->prepare("DELETE FROM product_images WHERE product_id = 2034")->execute();
        
        $files = scandir($dinkSrcDir);
        $candidates = [];
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            // Only process studio advertising files, skip raw ingredients and temp files
            if (strpos($file, 'Create_an_ultra-premium_studio_advertising_') === 0 || strpos($file, 'remove_2K_') === 0) {
                $filePath = $dinkSrcDir . '/' . $file;
                if (is_file($filePath)) {
                    $candidates[] = [
                        'src' => $filePath,
                        'name' => $file,
                        'size' => filesize($filePath)
                    ];
                }
            }
        }
        
        // Sort candidates by size descending
        usort($candidates, function($a, $b) {
            return $b['size'] <=> $a['size'];
        });

        $insertedCount = 0;
        $seenHashes = [];
        
        foreach ($candidates as $c) {
            $md5 = md5_file($c['src']);
            if (in_array($md5, $seenHashes)) continue;
            $seenHashes[] = $md5;

            $newFilename = "product-2034-" . substr($md5, 0, 10) . ".jpg";
            $destPath = $destDir . '/' . $newFilename;
            
            if (!copy($c['src'], $destPath)) {
                throw new Exception("Failed to copy {$c['name']} to $destPath");
            }

            $dbPath = "assets/images/products/" . $newFilename;
            // Set Create_an_ultra-premium_studio_advertising_202605181937 (1).jpg or largest as main
            $isMain = (strpos($c['name'], '202605181937') !== false && $insertedCount === 0) || ($insertedCount === 0) ? 1 : 0;
            
            $stmt = $db->prepare("INSERT INTO product_images (product_id, image_path, is_main) VALUES (2034, :path, :is_main)");
            $stmt->execute(['path' => $dbPath, 'is_main' => $isMain]);
            
            echo "  Mapped Dink Bucket: {$c['name']} -> {$newFilename} (is_main: $isMain)\n";
            $insertedCount++;
        }
        echo "  Successfully mapped $insertedCount images for Dink Karadant (1kg Bucket).\n";
    } else {
        echo "  Error: Source directory $dinkSrcDir does not exist!\n";
    }

    // ----------------------------------------------------
    // 2. Marvel Karadant (500g) - Product ID 2032
    // ----------------------------------------------------
    echo "\nProcessing Marvel Karadant (500g) [ID: 2032]...\n";
    $marvelSrcFolders = [
        __DIR__ . '/../assets/images/combos/Combo/Premium-lagdipak-marvel',
        __DIR__ . '/../assets/images/combos/Combo/Gandh-supreme-marvel-dink',
        __DIR__ . '/../assets/images/combos/Combo/Mrvel-oats-raagi',
        __DIR__ . '/../assets/images/combos/Combo/ragi-dink-marvel-suprem-oats-gandhagiri'
    ];
    
    // Clear existing product_images for 2032
    $db->prepare("DELETE FROM product_images WHERE product_id = 2032")->execute();

    $marvelCandidates = [];
    foreach ($marvelSrcFolders as $folder) {
        if (!is_dir($folder)) continue;
        $files = scandir($folder);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            // We want files that match the Marvel Karadant studio photo date 20260518
            if (strpos($file, 'Create_an_ultra-premium_studio_advertising_') === 0 && strpos($file, '20260518') !== false) {
                $filePath = $folder . '/' . $file;
                if (is_file($filePath)) {
                    $marvelCandidates[] = [
                        'src' => $filePath,
                        'name' => $file,
                        'size' => filesize($filePath)
                    ];
                }
            }
        }
    }

    // Sort by size descending to make the largest the main one
    usort($marvelCandidates, function($a, $b) {
        return $b['size'] <=> $a['size'];
    });

    $insertedCount = 0;
    $seenHashes = [];
    
    foreach ($marvelCandidates as $c) {
        $md5 = md5_file($c['src']);
        if (in_array($md5, $seenHashes)) continue;
        $seenHashes[] = $md5;

        $newFilename = "product-2032-" . substr($md5, 0, 10) . ".jpg";
        $destPath = $destDir . '/' . $newFilename;
        
        if (!copy($c['src'], $destPath)) {
            throw new Exception("Failed to copy {$c['name']} to $destPath");
        }

        $dbPath = "assets/images/products/" . $newFilename;
        $isMain = ($insertedCount === 0) ? 1 : 0;
        
        $stmt = $db->prepare("INSERT INTO product_images (product_id, image_path, is_main) VALUES (2032, :path, :is_main)");
        $stmt->execute(['path' => $dbPath, 'is_main' => $isMain]);
        
        echo "  Mapped Marvel Karadant: {$c['name']} -> {$newFilename} (is_main: $isMain)\n";
        $insertedCount++;
    }
    echo "  Successfully mapped $insertedCount images for Marvel Karadant (500g).\n";

    // ----------------------------------------------------
    // 3. Traditional Lagdi Pak (500g) - Product ID 2033
    // ----------------------------------------------------
    echo "\nCorrecting Traditional Lagdi Pak (500g) [ID: 2033] main image...\n";
    // Check if the target image product-2033-14744eaaf0.jpg exists for 2033 in the DB
    $stmtCheck = $db->prepare("SELECT id FROM product_images WHERE product_id = 2033 AND image_path LIKE '%product-2033-14744eaaf0%'");
    $stmtCheck->execute();
    $imgId = $stmtCheck->fetchColumn();
    
    if ($imgId) {
        // Set all images for 2033 to not main
        $db->prepare("UPDATE product_images SET is_main = 0 WHERE product_id = 2033")->execute();
        // Set this specific one to main
        $db->prepare("UPDATE product_images SET is_main = 1 WHERE id = :id")->execute(['id' => $imgId]);
        echo "  Successfully set image ID $imgId (product-2033-14744eaaf0.jpg) as main for Product 2033.\n";
    } else {
        echo "  Warning: Target cube image product-2033-14744eaaf0.jpg not found for Product 2033. Searching by file...\n";
        // Let's find any image from Lagdipak-cubes
        $stmtFind = $db->prepare("SELECT id, image_path FROM product_images WHERE product_id = 2033 AND image_path LIKE '%product-2033-%'");
        $stmtFind->execute();
        $allImgs = $stmtFind->fetchAll(PDO::FETCH_ASSOC);
        foreach ($allImgs as $img) {
            // Let's search if the file md5 corresponds to any file in Lagdipak-cubes
            // Actually let's just look at the one that is product-2033-14744eaaf0.jpg or similar
            if (strpos($img['image_path'], 'product-2033-14744eaaf0') !== false) {
                $db->prepare("UPDATE product_images SET is_main = 0 WHERE product_id = 2033")->execute();
                $db->prepare("UPDATE product_images SET is_main = 1 WHERE id = :id")->execute(['id' => $img['id']]);
                echo "  Found and set {$img['image_path']} as main.\n";
                break;
            }
        }
    }

    $db->commit();
    echo "\nAll changes successfully applied to database!\n";
} catch (Exception $e) {
    $db->rollBack();
    echo "\nMigration failed: " . $e->getMessage() . "\n";
}
