<?php
require_once 'c:/xampp/htdocs/sweet-website/config/config.php';
require_once 'c:/xampp/htdocs/sweet-website/config/Database.php';

$db = Database::getInstance();
$stmt = $db->query("SELECT name, image_path, hero_image FROM categories");

while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "Category: " . $row['name'] . "\n";
    if($row['image_path']) {
        $path = ROOT_PATH . '/' . ltrim($row['image_path'], '/');
        echo "  Thumb: " . $row['image_path'] . " -> " . (is_file($path) ? "EXISTS" : "MISSING") . "\n";
    } else {
        echo "  Thumb: NULL\n";
    }
    if($row['hero_image']) {
        $path = ROOT_PATH . '/' . ltrim($row['hero_image'], '/');
        echo "  Hero: " . $row['hero_image'] . " -> " . (is_file($path) ? "EXISTS" : "MISSING") . "\n";
    } else {
        echo "  Hero: NULL\n";
    }
}
