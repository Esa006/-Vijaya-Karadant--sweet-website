<?php
require_once __DIR__ . '/../config/config.php';
$db = Database::getInstance();

echo "=== VERIFYING COMBO IMAGES ===\n";
$stmt = $db->query("SELECT id, name, image FROM combos");
$combos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$missingCount = 0;
foreach ($combos as $c) {
    if (empty($c['image'])) {
        echo "Combo #{$c['id']} ({$c['name']}) has no image field.\n";
        continue;
    }
    $fullPath = __DIR__ . '/../' . ltrim($c['image'], '/');
    if (!file_exists($fullPath)) {
        echo "Missing combo image: [Combo #{$c['id']} | {$c['name']}] Path: {$c['image']}\n";
        $missingCount++;
    }
}

// Also check combo_images table if it exists
try {
    $stmtImg = $db->query("SELECT ci.combo_id, c.name as combo_name, ci.image_path FROM combo_images ci JOIN combos c ON ci.combo_id = c.id");
    $comboImgs = $stmtImg->fetchAll(PDO::FETCH_ASSOC);
    foreach ($comboImgs as $ci) {
        $fullPath = __DIR__ . '/../' . ltrim($ci['image_path'], '/');
        if (!file_exists($fullPath)) {
            echo "Missing gallery image: [Combo #{$ci['combo_id']} | {$ci['combo_name']}] Path: {$ci['image_path']}\n";
            $missingCount++;
        }
    }
} catch (PDOException $e) {
    echo "combo_images table check skipped: " . $e->getMessage() . "\n";
}

if ($missingCount === 0) {
    echo "All combo and combo gallery images exist on disk!\n";
} else {
    echo "Total missing combo images: $missingCount\n";
}
