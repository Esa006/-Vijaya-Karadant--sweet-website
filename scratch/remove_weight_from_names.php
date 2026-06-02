<?php
require_once __DIR__ . '/../config/config.php';

$db = Database::getInstance();

// 1. Find all products with weight/packaging in parenthesis
$stmt = $db->query("SELECT id, name FROM products WHERE name LIKE '%(%)%'");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "=== PRODUCTS TO UPDATE ===\n";
foreach ($products as $p) {
    // Remove (xxx) from name
    $newName = preg_replace('/\s*\([^)]+\)/', '', $p['name']);
    $newName = trim($newName);
    echo "ID: {$p['id']} | Old: '{$p['name']}' | New: '{$newName}'\n";
}

// 2. Perform the update
$updateStmt = $db->prepare("UPDATE products SET name = :name WHERE id = :id");
foreach ($products as $p) {
    $newName = preg_replace('/\s*\([^)]+\)/', '', $p['name']);
    $newName = trim($newName);
    $updateStmt->execute([
        ':name' => $newName,
        ':id' => $p['id']
    ]);
    echo "Updated product ID {$p['id']} to '{$newName}'\n";
}

// 3. Let's also check if there are any combos with parenthesis in the name
$stmtCombo = $db->query("SELECT id, name FROM combos WHERE name LIKE '%(%)%'");
$combos = $stmtCombo->fetchAll(PDO::FETCH_ASSOC);

if (!empty($combos)) {
    echo "\n=== COMBOS TO UPDATE ===\n";
    $updateComboStmt = $db->prepare("UPDATE combos SET name = :name WHERE id = :id");
    foreach ($combos as $c) {
        $newName = preg_replace('/\s*\([^)]+\)/', '', $c['name']);
        $newName = trim($newName);
        echo "ID: {$c['id']} | Old: '{$c['name']}' | New: '{$newName}'\n";
        $updateComboStmt->execute([
            ':name' => $newName,
            ':id' => $c['id']
        ]);
        echo "Updated combo ID {$c['id']} to '{$newName}'\n";
    }
} else {
    echo "\nNo combos with parenthesis found.\n";
}
