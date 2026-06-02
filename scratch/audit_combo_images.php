<?php
require_once __DIR__ . '/../config/config.php';

echo "=== COMBOS with images ===\n";
$db = Database::getInstance();
$stmt = $db->query("SELECT id, name, slug, category, price, image, is_active FROM combos ORDER BY id");
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $img = $r['image'] ?? '(none)';
    echo "ID:{$r['id']} | active:{$r['is_active']} | {$r['name']}\n    img: {$img}\n";
}

echo "\n=== COMBO image files in assets/images/combos/ ===\n";
$dir = __DIR__ . '/../assets/images/combos/';
$files = glob($dir . '*.{jpg,png,jpeg,gif,webp}', GLOB_BRACE);
foreach ($files as $f) {
    echo '  ' . basename($f) . "\n";
}

echo "\n=== Combo Subfolders in Combo/ ===\n";
$subfolders = glob($dir . 'Combo/*', GLOB_ONLYDIR);
foreach ($subfolders as $sf) {
    $name = basename($sf);
    $images = glob($sf . '/*.{jpg,png,jpeg,gif,webp}', GLOB_BRACE);
    echo "  [{$name}] - " . count($images) . " images\n";
    foreach ($images as $img) {
        echo "    " . basename($img) . " (" . round(filesize($img)/1024) . "KB)\n";
    }
}
