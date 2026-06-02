<?php
require_once __DIR__ . '/../config/config.php';
$db = Database::getInstance();

// Check what tables exist relating to combos
$stmt = $db->query("SHOW TABLES LIKE '%combo%'");
echo "=== Combo Tables ===\n";
while ($r = $stmt->fetch(PDO::FETCH_NUM)) {
    echo $r[0] . "\n";
}

// Check combos table structure
echo "\n=== COMBOS table structure ===\n";
$stmt = $db->query("DESCRIBE combos");
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $r['Field'] . " | " . $r['Type'] . " | " . $r['Null'] . " | " . $r['Key'] . "\n";
}

echo "\n=== COMBO_ITEMS table structure ===\n";
$stmt = $db->query("DESCRIBE combo_items");
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $r['Field'] . " | " . $r['Type'] . " | " . $r['Null'] . " | " . $r['Key'] . "\n";
}
