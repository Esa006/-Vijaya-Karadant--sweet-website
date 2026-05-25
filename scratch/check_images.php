<?php
require_once 'config/config.php';
$db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);

echo "--- Products ---\n";
$stmt = $db->query("SELECT id, name, image_path FROM products");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $path = ROOT_PATH . '/' . $row['image_path'];
    if (!file_exists($path)) {
        echo "Broken: " . $row['name'] . " -> " . $row['image_path'] . "\n";
    }
}

echo "\n--- Combos ---\n";
$stmt = $db->query("SELECT id, name, image FROM combos");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $path = ROOT_PATH . '/' . $row['image'];
    if (!file_exists($path)) {
        echo "Broken: " . $row['name'] . " -> " . $row['image'] . "\n";
    }
}
?>
