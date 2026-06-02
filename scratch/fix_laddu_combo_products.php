<?php
/**
 * Fix combos in the laddu category and resolve product mismatches.
 */
require_once __DIR__ . '/../config/config.php';
$db = Database::getInstance();

try {
    $db->beginTransaction();

    // 1. Move Combo #43 ("Lagdi Pak & Ladagi Laddu Premium") to 'mixed' since it contains Karadant (Lagdi Pak)
    $db->prepare("UPDATE combos SET category = 'mixed' WHERE id = 43")->execute();
    echo "Moved Combo #43 to 'mixed'\n";

    // 2. Move Combo #45 ("Marvel Oats Ragi Healthy Mix") to 'mixed' since it contains Karadant (Marvel Karadant)
    $db->prepare("UPDATE combos SET category = 'mixed' WHERE id = 45")->execute();
    echo "Moved Combo #45 to 'mixed'\n";

    // 3. Fix Combo #2 ("Festive Namkeen Mix"): category to 'namkeen', items to namkeens (Spicy Mix Namkeen & Golden Sev)
    $db->prepare("UPDATE combos SET category = 'namkeen' WHERE id = 2")->execute();
    $db->prepare("DELETE FROM combo_items WHERE combo_id = 2")->execute();
    $db->prepare("INSERT INTO combo_items (combo_id, product_id, quantity) VALUES (2, 2001, 1), (2, 2002, 1)")->execute();
    echo "Corrected Combo #2: Festive Namkeen Mix (Category: namkeen, Items: Spicy Mix Namkeen + Golden Sev)\n";

    // 4. Fix Combo #23 ("Premium Laddu Mix 13"): category to 'laddu', items to laddus (Besan Laddu, Til Laddu, Peanut Laddu)
    $db->prepare("UPDATE combos SET category = 'laddu' WHERE id = 23")->execute();
    $db->prepare("DELETE FROM combo_items WHERE combo_id = 23")->execute();
    $db->prepare("INSERT INTO combo_items (combo_id, product_id, quantity) VALUES (23, 1011, 1), (23, 1013, 1), (23, 1015, 1)")->execute();
    echo "Corrected Combo #23: Premium Laddu Mix 13 (Category: laddu, Items: Besan, Til, Peanut Laddus)\n";

    // 5. Populate Combo #17 ("Health-Conscious Sweet Mix 07") with healthy laddus (Ragi Laddu, Premium Otts Laddu)
    $db->prepare("DELETE FROM combo_items WHERE combo_id = 17")->execute();
    $db->prepare("INSERT INTO combo_items (combo_id, product_id, quantity) VALUES (17, 1010, 1), (17, 1014, 1)")->execute();
    echo "Populated Combo #17 with Ragi and Oats Laddus\n";

    // 6. Fix Combo #3 ("Premium Laddu Box"): remove soft-deleted product ID 3, add Besan Laddu (1011) to make it a box of assorted laddus
    $db->prepare("DELETE FROM combo_items WHERE combo_id = 3 AND product_id = 3")->execute();
    $db->prepare("INSERT INTO combo_items (combo_id, product_id, quantity) VALUES (3, 1011, 1)")->execute();
    echo "Updated Combo #3 Premium Laddu Box (Added Besan Laddu to replace soft-deleted product)\n";

    // 7. Fix Combo #12 ("Festive Family Pack 02"): add Ragi Laddu (1010) Qty 1 to replace soft-deleted product ID 2
    $db->prepare("DELETE FROM combo_items WHERE combo_id = 12 AND product_id = 2")->execute();
    $db->prepare("INSERT INTO combo_items (combo_id, product_id, quantity) VALUES (12, 1010, 1)")->execute();
    echo "Updated Combo #12 Festive Family Pack 02 (Added Ragi Laddu to replace soft-deleted product)\n";

    // 8. Fix Combo #44 ("Assorted Laddu Box (4 Gems)"): add Ragi Laddu (1010) Qty 1 as the 4th gem (replacing soft-deleted Moong Dal Laddu)
    // First check if Moong Dal Laddu is in it
    $db->prepare("DELETE FROM combo_items WHERE combo_id = 44 AND product_id = 2013")->execute();
    $db->prepare("INSERT INTO combo_items (combo_id, product_id, quantity) VALUES (44, 1010, 1)")->execute();
    echo "Updated Combo #44 Assorted Laddu Box (Added Ragi Laddu to make it 4 Gems)\n";

    // 9. Fix Combo #51 ("Traditional Laddu Trio"): add Dink Laddu (1009) Qty 1 as the 3rd item (replacing soft-deleted Moong Dal Laddu)
    $db->prepare("DELETE FROM combo_items WHERE combo_id = 51 AND product_id = 2013")->execute();
    $db->prepare("INSERT INTO combo_items (combo_id, product_id, quantity) VALUES (51, 1009, 1)")->execute();
    echo "Updated Combo #51 Traditional Laddu Trio (Added Dink Laddu to make it a Trio)\n";

    $db->commit();
    echo "\nAll combo corrections applied successfully!\n";
} catch (Exception $e) {
    $db->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
