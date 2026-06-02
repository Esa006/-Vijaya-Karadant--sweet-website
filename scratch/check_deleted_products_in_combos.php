<?php
require_once __DIR__ . '/../config/config.php';
$db = Database::getInstance();

$comboIds = [15, 16, 24, 25, 26, 28, 29];
echo "=== AUDITING COMBO ITEMS FOR TARGET COMBOS ===\n";
foreach ($comboIds as $cid) {
    echo "Auditing Combo ID: $cid\n";
    $stmt = $db->prepare("SELECT * FROM combo_items WHERE combo_id = :cid");
    $stmt->execute(['cid' => $cid]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($items)) {
        echo "  No entries in combo_items at all!\n";
    } else {
        foreach ($items as $item) {
            $stmtProd = $db->prepare("SELECT name, deleted_at, status FROM products WHERE id = :pid");
            $stmtProd->execute(['pid' => $item['product_id']]);
            $prod = $stmtProd->fetch(PDO::FETCH_ASSOC);
            if ($prod) {
                echo sprintf("  Product ID: %d | Name: %s | Status: %s | Deleted: %s | Qty: %d\n",
                    $item['product_id'], $prod['name'], $prod['status'], $prod['deleted_at'] ?: 'No', $item['quantity']);
            } else {
                echo sprintf("  Product ID: %d | DOES NOT EXIST IN PRODUCTS TABLE! | Qty: %d\n",
                    $item['product_id'], $item['quantity']);
            }
        }
    }
}
