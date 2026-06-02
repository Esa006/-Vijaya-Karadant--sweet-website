<?php
/**
 * Deduplicate combo_images by file content (md5 of actual file).
 * Keeps the first (lowest id) row per unique file, removes the rest.
 * Also ensures exactly one is_primary per combo.
 */
require_once __DIR__ . '/../config/config.php';
$db = Database::getInstance();

$assetRoot = __DIR__ . '/../';

// Get all gallery images grouped by combo
$stmt = $db->query("SELECT id, combo_id, image_path, is_primary, sort_order FROM combo_images ORDER BY combo_id, id ASC");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$toDelete    = [];
$seenHashes  = []; // combo_id => [md5 => image_id]

foreach ($rows as $r) {
    $cid  = (int)$r['combo_id'];
    $path = $assetRoot . $r['image_path'];

    if (!file_exists($path)) {
        // file missing — mark for deletion from DB
        $toDelete[] = (int)$r['id'];
        continue;
    }

    $hash = md5_file($path);
    if (!isset($seenHashes[$cid])) $seenHashes[$cid] = [];

    if (isset($seenHashes[$cid][$hash])) {
        // Duplicate — keep the first (lowest id), delete this one
        $toDelete[] = (int)$r['id'];
    } else {
        $seenHashes[$cid][$hash] = (int)$r['id'];
    }
}

$deletedCount = 0;
if (!empty($toDelete)) {
    // Delete in batches
    $chunks = array_chunk($toDelete, 100);
    foreach ($chunks as $chunk) {
        $placeholders = implode(',', array_fill(0, count($chunk), '?'));
        $del = $db->prepare("DELETE FROM combo_images WHERE id IN ({$placeholders})");
        $del->execute($chunk);
        $deletedCount += $del->rowCount();
    }
}

echo "Deleted {$deletedCount} duplicate rows.\n";
echo "Remaining combo_images rows: " . $db->query("SELECT COUNT(*) FROM combo_images")->fetchColumn() . "\n\n";

// Ensure exactly one primary per combo (use lowest sort_order/id if none)
$combosWithImages = $db->query("SELECT DISTINCT combo_id FROM combo_images")->fetchAll(PDO::FETCH_COLUMN);
$fixedPrimary = 0;
foreach ($combosWithImages as $cid) {
    $cid = (int)$cid;
    $primaries = $db->query("SELECT id FROM combo_images WHERE combo_id={$cid} AND is_primary=1")->fetchAll(PDO::FETCH_COLUMN);

    if (count($primaries) === 0) {
        // No primary — set first image
        $firstId = $db->query("SELECT id FROM combo_images WHERE combo_id={$cid} ORDER BY sort_order,id ASC LIMIT 1")->fetchColumn();
        $db->exec("UPDATE combo_images SET is_primary=1 WHERE id={$firstId}");
        $img = $db->query("SELECT image_path FROM combo_images WHERE id={$firstId}")->fetchColumn();
        $db->exec("UPDATE combos SET image='{$img}' WHERE id={$cid}");
        $fixedPrimary++;
    } elseif (count($primaries) > 1) {
        // Multiple primaries — keep only first
        $keep = $primaries[0];
        $db->exec("UPDATE combo_images SET is_primary=0 WHERE combo_id={$cid} AND id != {$keep}");
        $fixedPrimary++;
    }
}
echo "Fixed primary flags for {$fixedPrimary} combos.\n";

// Fix sort_order to be sequential per combo
$combos = $db->query("SELECT DISTINCT combo_id FROM combo_images ORDER BY combo_id")->fetchAll(PDO::FETCH_COLUMN);
$updateSort = $db->prepare("UPDATE combo_images SET sort_order=:s WHERE id=:id");
foreach ($combos as $cid) {
    $imgs = $db->query("SELECT id FROM combo_images WHERE combo_id={$cid} ORDER BY is_primary DESC, sort_order ASC, id ASC")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($imgs as $i => $imgId) {
        $updateSort->execute([':s' => $i, ':id' => $imgId]);
    }
}
echo "Sort orders normalised.\n\n";

// Final summary
echo "╔═══ FINAL GALLERY COUNTS (combos #33+) ═════════════╗\n";
$stmt = $db->query("
    SELECT c.id, c.name, COUNT(ci.id) AS total
    FROM combos c
    LEFT JOIN combo_images ci ON ci.combo_id = c.id
    WHERE c.id >= 33
    GROUP BY c.id ORDER BY c.id
");
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    printf("  #%-3d %-43s %d imgs\n", $r['id'], substr($r['name'],0,43), $r['total']);
}
echo "╚═════════════════════════════════════════════════════╝\n";
echo "\nTotal combo_images: " . $db->query("SELECT COUNT(*) FROM combo_images")->fetchColumn() . "\n";
echo "Primaries:          " . $db->query("SELECT COUNT(*) FROM combo_images WHERE is_primary=1")->fetchColumn() . "\n";
