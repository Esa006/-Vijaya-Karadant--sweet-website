<?php
/**
 * Dynamic upload/migration of gift category images.
 * Scans subfolders of assets/images/Gift/ and maps them to product IDs,
 * copying images and updating the product_images gallery table.
 */
require_once __DIR__ . '/../config/config.php';
$db = Database::getInstance();

$baseDir = "C:/xampp/htdocs/sweet-website/assets/images/Gift";
$destDir = __DIR__ . '/../assets/images/products/';

// Ensure destination directory exists
if (!is_dir($destDir)) {
    mkdir($destDir, 0777, true);
}

try {
    $db->beginTransaction();

    // 1. Ensure "small-gift-01" product exists in DB
    $stmt = $db->prepare("SELECT id FROM products WHERE slug = ?");
    $stmt->execute(['small-gift-01']);
    $smallGiftId = $stmt->fetchColumn();

    if (!$smallGiftId) {
        // Insert new product
        $insertProd = $db->prepare("
            INSERT INTO products (category_id, name, slug, short_description, description, base_price, status, featured, sku)
            VALUES (5, 'Small Gift Box 01', 'small-gift-01', 
                    'A beautiful small gift box containing our traditional sweets.', 
                    'Our Small Gift Box 01 is perfect for small gatherings and return gifts. Contains an assortment of our classic sweets, made with love.', 
                    450.00, 'published', 1, 'GIFT-01')
        ");
        $insertProd->execute();
        $smallGiftId = (int)$db->lastInsertId();
        echo "Created new product 'Small Gift Box 01' with ID: {$smallGiftId}\n";

        // Insert inventory
        $insertInv = $db->prepare("INSERT INTO inventory (product_id, stock) VALUES (?, 100)");
        $insertInv->execute([$smallGiftId]);

        // Insert variant
        $insertVar = $db->prepare("
            INSERT INTO product_variants (product_id, weight, label, price, stock, low_stock_threshold)
            VALUES (?, '500g', '500g Standard Pack', 450.00, 100, 10)
        ");
        $insertVar->execute([$smallGiftId]);
    } else {
        $smallGiftId = (int)$smallGiftId;
        echo "Product 'Small Gift Box 01' already exists with ID: {$smallGiftId}\n";
    }

    // 2. Explicit mapping of folder names to product IDs
    $mapping = [
        'premium gift'              => 1040, // Premium Gift Box
        'White-gift-box'            => 1041, // Festive Special Box
        'Blue-gift-box'             => 1042, // Tilkut Gift Box
        'Small-gift-anjeer-supreme' => 1043, // Anjeer Gift Box
        'Small-gift-01'             => $smallGiftId // Small Gift Box 01
    ];

    $totalCopied = 0;

    foreach ($mapping as $folderName => $productId) {
        $folderPath = $baseDir . '/' . $folderName;
        if (!is_dir($folderPath)) {
            echo "⚠ Directory not found: {$folderPath} -- skipping\n";
            continue;
        }

        // Get all image files in this folder
        $images = glob($folderPath . '/*.{jpg,jpeg,png,webp}', GLOB_BRACE);
        if (empty($images)) {
            echo "⚠ No images found in: {$folderPath} -- skipping\n";
            continue;
        }

        // Sort by file size descending (largest first)
        usort($images, function($a, $b) {
            return filesize($b) - filesize($a);
        });

        echo "\nProcessing folder '{$folderName}' (mapped to Product #{$productId}):\n";

        // Delete existing product images to avoid clutter and duplicates
        $db->prepare("DELETE FROM product_images WHERE product_id = ?")->execute([$productId]);
        echo "  - Cleared old gallery images in DB\n";

        $isMain = 1; // Largest image is set as main/primary
        foreach ($images as $img) {
            $ext = strtolower(pathinfo($img, PATHINFO_EXTENSION));
            $md5 = substr(md5_file($img), 0, 10);
            $fileName = "product-{$productId}-{$md5}.{$ext}";
            $destPath = $destDir . $fileName;
            $webPath = "assets/images/products/" . $fileName;

            // Copy file to products assets folder
            if (!file_exists($destPath)) {
                if (copy($img, $destPath)) {
                    $totalCopied++;
                } else {
                    echo "  ✗ Failed to copy " . basename($img) . "\n";
                    continue;
                }
            }

            // Insert into product_images table
            $db->prepare("
                INSERT INTO product_images (product_id, image_path, is_main)
                VALUES (?, ?, ?)
            ")->execute([$productId, $webPath, $isMain]);

            if ($isMain === 1) {
                echo "  ★ MAIN: {$fileName}\n";
                $isMain = 0; // remaining images are set as secondary
            } else {
                echo "  + gallery: {$fileName}\n";
            }
        }
    }

    $db->commit();
    echo "\n============================================\n";
    echo "Dynamic Gifting category upload successful!\n";
    echo "Total files copied/verified: {$totalCopied}\n";
    echo "============================================\n";

} catch (Exception $e) {
    $db->rollBack();
    echo "Error during migration: " . $e->getMessage() . "\n";
}
