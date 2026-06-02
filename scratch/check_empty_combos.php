<?php
require_once __DIR__ . '/../config/config.php';
$db = Database::getInstance();

echo "=== COMBOS WITH ZERO ITEMS ===\n";
$stmt = $db->query("
    SELECT c.id, c.name, c.category 
    FROM combos c
    LEFT JOIN combo_items ci ON c.id = ci.combo_id
    WHERE ci.combo_id IS NULL
");
$emptyCombos = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($emptyCombos as $ec) {
    echo "ID: " . $ec['id'] . " | Name: " . $ec['name'] . " | Category: " . $ec['category'] . "\n";
}

echo "\nTotal empty combos: " . count($emptyCombos) . "\n";
