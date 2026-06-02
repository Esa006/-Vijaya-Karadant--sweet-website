<?php
require_once __DIR__ . '/../config/config.php';
$db = Database::getInstance();

echo "=== COLUMNS IN product_variants ===\n";
$stmt = $db->query("SHOW COLUMNS FROM product_variants");
$cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $col) {
    echo "Field: " . $col['Field'] . " | Type: " . $col['Type'] . "\n";
}
