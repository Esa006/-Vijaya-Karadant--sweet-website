<?php
require_once __DIR__ . '/../config/config.php';
$db = Database::getInstance();

$combos = $db->query("SELECT id, name, category, description FROM combos ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);

echo "╔════════════════════════════════════════════════════════════════════════════════════════════════════╗\n";
echo "║ ID  ║ Combo Name                               ║ Current Cat ║ Proposed Cat ║ Items Category Match  ║\n";
echo "╠════════════════════════════════════════════════════════════════════════════════════════════════════╣\n";

foreach ($combos as $combo) {
    $comboId = $combo['id'];
    $name = $combo['name'];
    $currentCat = $combo['category'];
    $desc = $combo['description'];
    
    // Get all items in this combo
    $items = $db->query("
        SELECT ci.product_id, p.name as product_name, pc.slug as cat_slug
        FROM combo_items ci
        LEFT JOIN products p ON ci.product_id = p.id
        LEFT JOIN categories pc ON p.category_id = pc.id
        WHERE ci.combo_id = {$comboId}
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    // If no items, look at current category or default to mixed
    if (empty($items)) {
        printf("║ %-3d ║ %-40s ║ %-11s ║ %-12s ║ No items (empty)      ║\n", $comboId, substr($name, 0, 40), $currentCat, $currentCat);
        continue;
    }
    
    // Check if the combo name or description implies gifting (Bucket, Tub, Gift Box, Celebration Box)
    $isGiftingText = false;
    $giftingWords = ['gift', 'box', 'bucket', 'tub', 'celebration', 'festive special'];
    foreach ($giftingWords as $word) {
        if (stripos($name, $word) !== false || stripos($desc, $word) !== false) {
            $isGiftingText = true;
            break;
        }
    }
    
    $itemCats = [];
    foreach ($items as $item) {
        if ($item['cat_slug']) {
            $itemCats[] = $item['cat_slug'];
        }
    }
    $itemCats = array_unique($itemCats);
    
    $proposedCat = 'mixed';
    
    // Classification rules
    if ($isGiftingText || in_array('gifting', $itemCats) || in_array('gift-box', $itemCats)) {
        $proposedCat = 'gifting';
    } elseif (count($itemCats) === 1) {
        $proposedCat = $itemCats[0];
    } else {
        $proposedCat = 'mixed';
    }
    
    $itemsStr = implode(', ', $itemCats);
    printf("║ %-3d ║ %-40s ║ %-11s ║ %-12s ║ %-21s ║\n", $comboId, substr($name, 0, 40), $currentCat, $proposedCat, substr($itemsStr, 0, 21));
}

echo "╚════════════════════════════════════════════════════════════════════════════════════════════════════╝\n";
