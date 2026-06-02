<?php
require_once __DIR__ . '/../config/config.php';
$db = Database::getInstance();

$mappings = [
    // ID 15: Artisanal Karadant Selection 05 (karadant)
    15 => [
        ['product_id' => 1001, 'quantity' => 1], // Premium Vijaya Karadant
        ['product_id' => 1005, 'quantity' => 1], // Dink Karadant
    ],
    // ID 16: Classic Sweet Duo 06 (karadant)
    16 => [
        ['product_id' => 1002, 'quantity' => 1], // Classic Vijaya Karadant
        ['product_id' => 1005, 'quantity' => 1], // Dink Karadant
    ],
    // ID 24: Crunchy Namkeen Combo 14 (namkeen)
    24 => [
        ['product_id' => 2001, 'quantity' => 1], // Spicy Mix Namkeen
        ['product_id' => 2002, 'quantity' => 1], // Golden Sev
    ],
    // ID 25: Sweet & Spicy Pair 15 (gifting)
    25 => [
        ['product_id' => 1001, 'quantity' => 1], // Premium Vijaya Karadant
        ['product_id' => 2001, 'quantity' => 1], // Spicy Mix Namkeen
    ],
    // ID 26: Corporate Gifting Pack 16 (gifting)
    26 => [
        ['product_id' => 1040, 'quantity' => 1], // Premium Gift Box
        ['product_id' => 1042, 'quantity' => 1], // Tilkut Gift Box
    ],
    // ID 28: Evening Snack Mix 18 (namkeen)
    28 => [
        ['product_id' => 2005, 'quantity' => 1], // Butter Muruku
        ['product_id' => 2003, 'quantity' => 1], // Masala Peanuts
    ],
    // ID 29: Bestseller Combo 19 (karadant)
    29 => [
        ['product_id' => 1001, 'quantity' => 1], // Premium Vijaya Karadant
        ['product_id' => 1003, 'quantity' => 1], // Supreme Vijaya Karadant
    ]
];

$db->beginTransaction();
try {
    foreach ($mappings as $cid => $items) {
        echo "Populating combo ID: $cid...\n";
        // Clean out any existing items first
        $stmtDel = $db->prepare("DELETE FROM combo_items WHERE combo_id = :cid");
        $stmtDel->execute(['cid' => $cid]);
        
        // Insert new ones
        $stmtIns = $db->prepare("INSERT INTO combo_items (combo_id, product_id, quantity) VALUES (:cid, :pid, :qty)");
        foreach ($items as $item) {
            $stmtIns->execute([
                'cid' => $cid,
                'pid' => $item['product_id'],
                'qty' => $item['quantity']
            ]);
            echo "  Added product ID " . $item['product_id'] . " x " . $item['quantity'] . "\n";
        }
    }
    $db->commit();
    echo "Successfully populated all empty combos!\n";
} catch (Exception $e) {
    $db->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
