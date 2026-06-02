<?php
require_once __DIR__ . '/../config/config.php';
$db = Database::getInstance();

$row = $db->query("SELECT COUNT(*) AS cnt FROM combo_images")->fetch(PDO::FETCH_ASSOC);
echo "Total combo_images rows: " . $row['cnt'] . "\n";

$row = $db->query("SELECT COUNT(*) AS cnt FROM combo_images WHERE is_primary = 1")->fetch(PDO::FETCH_ASSOC);
echo "Primary images: " . $row['cnt'] . "\n";

// Combos with >1 gallery image (will show Swiper on frontend)
$stmt = $db->query("SELECT combo_id, COUNT(*) as cnt FROM combo_images GROUP BY combo_id HAVING cnt > 1 ORDER BY cnt DESC LIMIT 10");
echo "\nTop combos by gallery count:\n";
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $name = $db->query("SELECT name FROM combos WHERE id = " . (int)$r['combo_id'])->fetchColumn();
    echo "  #{$r['combo_id']} {$name}: {$r['cnt']} images\n";
}
