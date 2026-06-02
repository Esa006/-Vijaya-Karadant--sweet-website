<?php
require_once __DIR__ . '/../config/config.php';
$db = Database::getInstance();

// Check if demo product is in any combo
$stmt = $db->prepare("SELECT COUNT(*) FROM combo_items WHERE product_id = 2014");
$stmt->execute();
$count = $stmt->fetchColumn();

if ($count > 0) {
    echo "Warning: Demo product is used in $count combos. Skipping status update.\n";
} else {
    $stmtUpd = $db->prepare("UPDATE products SET status = 'draft' WHERE id = 2014");
    $stmtUpd->execute();
    echo "Demo product (ID 2014) status successfully updated to 'draft'.\n";
}
