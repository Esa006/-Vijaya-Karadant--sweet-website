<?php
// Final category verification — show the correct final state
require_once __DIR__ . '/../config/config.php';
$db = Database::getInstance();

echo "╔══════════════ FINAL COMBO CATEGORIES ══════════════════════════╗\n";

$stmt = $db->query("
    SELECT c.id, c.name, c.category,
           COUNT(ci.id) AS item_count
    FROM combos c
    LEFT JOIN combo_items ci ON ci.combo_id = c.id
    GROUP BY c.id
    ORDER BY c.category, c.id
");

$prev = '';
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    if ($r['category'] !== $prev) {
        echo "\n  ── " . strtoupper($r['category']) . " ──\n";
        $prev = $r['category'];
    }
    printf("    #%-3d %-46s (%d items)\n", $r['id'], $r['name'], $r['item_count']);
}

echo "\n╠══════════════ DISTRIBUTION ════════════════════════════════════╣\n";
$stmt = $db->query("SELECT category, COUNT(*) AS cnt FROM combos GROUP BY category ORDER BY cnt DESC");
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $bar = str_repeat('█', $r['cnt']);
    printf("  %-10s : %2d  %s\n", $r['category'], $r['cnt'], $bar);
}
echo "╚════════════════════════════════════════════════════════════════╝\n";
