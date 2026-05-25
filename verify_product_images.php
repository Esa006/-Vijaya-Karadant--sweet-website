<?php
require_once 'c:/xampp/htdocs/sweet-website/config/config.php';
require_once 'c:/xampp/htdocs/sweet-website/config/Database.php';

$db = Database::getInstance();
$stmt = $db->query("SELECT name, image_path FROM products");

$broken = 0;
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $rawPath = $row['image_path'];
    if (!$rawPath) {
        echo "Missing path for: " . $row['name'] . "\n";
        $broken++;
        continue;
    }
    $path = ROOT_PATH . '/' . ltrim(str_replace('\\', '/', $rawPath), '/');
    if(!is_file($path)) {
        echo "Broken Product: " . $row['name'] . " (Path: " . $rawPath . ")\n";
        $broken++;
    }
}

if ($broken === 0) {
    echo "All product images are VALID!\n";
} else {
    echo "Found $broken broken product images.\n";
}
