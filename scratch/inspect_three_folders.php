<?php
require_once __DIR__ . '/../config/config.php';
$db = Database::getInstance();

$folders = [
    'Dink-laddu-box' => __DIR__ . '/../assets/images/Singal/Dink-laddu-box',
    'Classic' => __DIR__ . '/../assets/images/Singal/Classic',
    'Dink-laddu-bucket' => __DIR__ . '/../assets/images/Singal/Dink-laddu-bucket'
];

echo "=== FILES IN DIRECTORIES ===\n";
foreach ($folders as $name => $path) {
    echo "\nFolder: $name ($path)\n";
    if (is_dir($path)) {
        $files = scandir($path);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            echo "  File: $file | Size: " . filesize($path . '/' . $file) . " bytes\n";
        }
    } else {
        echo "  Directory does not exist!\n";
    }
}

echo "\n=== ALL PRODUCTS IN DB (INCLUDING DELETED) ===\n";
$stmt = $db->query("
    SELECT id, name, slug, status, category_id, deleted_at 
    FROM products 
    WHERE (name LIKE '%Dink%' OR name LIKE '%Classic%' OR name LIKE '%Bucket%')
");
$prods = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($prods as $p) {
    echo sprintf("ID: %4d | Name: %-40s | Slug: %-25s | CatID: %d | Status: %-10s | Deleted: %s\n",
        $p['id'], $p['name'], $p['slug'], $p['category_id'], $p['status'], $p['deleted_at'] ?: 'No');
}
