<?php
/**
 * Precision re-migration: explicitly maps each Combo/ subfolder
 * to the correct combo ID. Adds missing images without duplicating.
 */
require_once __DIR__ . '/../config/config.php';

$db      = Database::getInstance();
$baseDir = __DIR__ . '/../assets/images/combos/Combo/';
$webBase = 'assets/images/combos/';

// ── EXPLICIT folder → combo_id mapping ──────────────────────────────────────
// Based on actual combo data from DB + folder names
$map = [
    'Dink+regal anjeer'                          => 33,  // Dink & Regal Anjeer Combo
    'Dink-bucket-Gandhagiri-bucket'              => 34,  // Dink & Gandhagiri Heritage Buckets
    'Dink-laddu-bucket-premium'                  => 35,  // Dink Laddu Bucket & Premium Karadant
    'Dink-laddu-premium-combo'                   => 36,  // Dink Laddu Premium Duo
    'Dink-laddu-supreme'                         => 37,  // Dink Laddu Supreme Combo
    'Dink-supreme-gandhagiri'                    => 38,  // Dink Supreme Gandhagiri Trio
    'Gandh-supreme-marvel-dink'                  => 39,  // Gandh Supreme Marvel Dink Quartet
    'gandhagiri-bucket-premium'                  => 40,  // Gandhagiri Bucket & Premium Karadant
    'Gandhagiri-dink-buckets-supreme-anjeer-tubs'=> 41,  // Grand Buckets & Tubs Feast
    'Gandhagiri-supreme'                         => 42,  // Gandhagiri Supreme Duo
    'Lagdipak-lagdi-laddu-premium'               => 43,  // Lagdi Pak & Ladagi Laddu Premium
    'Moong-peanut-til-besan-laddu'               => 44,  // Assorted Laddu Box (4 Gems)
    'Mrvel-oats-raagi'                           => 45,  // Marvel Oats Ragi Healthy Mix
    'Premium-lagdipak-marvel'                    => 46,  // Premium Lagdi Pak & Marvel Trio
    'ragi-dink-marvel-suprem-oats-gandhagiri'    => 47,  // Ultimate Heritage Grand Collection
    'regal-anjeer-supreme'                       => 48,  // Regal Anjeer Supreme Pair
    'Regal-anjeer-tub-dink-laddu-bucket'         => 49,  // Regal Anjeer Tub & Dink Laddu Bucket
    'Regal-anjeer-tub-Gandhgir-bucket-supreme-tub'=> 50, // Anjeer & Gandhagiri Festive Trio
    'Til-besan-moong-laddu'                      => 51,  // Traditional Laddu Trio
    'ladgi-laddu-premium'                        => 43,  // also Lagdi Pak & Ladagi Laddu Premium
    'Supreme-gandh-dink'                         => 38,  // alt shots for Dink Supreme Gandhagiri
    'Supreme-gandhagiri'                         => 42,  // alt shots for Gandhagiri Supreme Duo
];

// ── Helper: get existing image paths for a combo ────────────────────────────
function getExistingPaths(PDO $db, int $comboId): array {
    $s = $db->prepare("SELECT image_path FROM combo_images WHERE combo_id = :cid");
    $s->execute([':cid' => $comboId]);
    return array_column($s->fetchAll(PDO::FETCH_ASSOC), 'image_path');
}

$totalAdded = 0;
$totalSkip  = 0;

foreach ($map as $folderName => $comboId) {
    $folder = $baseDir . $folderName;
    if (!is_dir($folder)) {
        echo "⚠  Folder not found: {$folderName}\n";
        continue;
    }

    $images = glob($folder . '/*.{jpg,jpeg,png,webp}', GLOB_BRACE);
    if (empty($images)) {
        echo "  ○ [{$folderName}] — no images\n";
        continue;
    }

    // Sort by size desc (largest = best quality = preferred primary)
    usort($images, fn($a, $b) => filesize($b) - filesize($a));

    // Load current DB state for this combo
    $existing       = getExistingPaths($db, $comboId);
    $hasPrimary     = (bool)$db->query("SELECT is_primary FROM combo_images WHERE combo_id={$comboId} AND is_primary=1 LIMIT 1")->fetchColumn();
    $sortOrder      = (int)$db->query("SELECT COUNT(*) FROM combo_images WHERE combo_id={$comboId}")->fetchColumn();
    $madeFirstPrimary = $hasPrimary; // if already has a primary, don't override

    $insertStmt = $db->prepare(
        "INSERT INTO combo_images (combo_id, image_path, is_primary, sort_order)
         VALUES (:cid, :path, :primary, :sort)"
    );
    $clearPrimStmt = $db->prepare("UPDATE combo_images SET is_primary = 0 WHERE combo_id = :cid");
    $syncMainStmt  = $db->prepare("UPDATE combos SET image = :img WHERE id = :id");

    echo "  ► [{$folderName}] → #{$comboId} (" . count($images) . " src images, {$sortOrder} in DB)\n";

    foreach ($images as $imgFile) {
        $ext     = strtolower(pathinfo($imgFile, PATHINFO_EXTENSION));
        $newName = 'combo-' . $comboId . '-' . substr(md5_file($imgFile), 0, 10) . '.' . $ext;
        $dest    = __DIR__ . '/../assets/images/combos/' . $newName;
        $webPath = $webBase . $newName;

        // Skip if this exact web path already in DB
        if (in_array($webPath, $existing)) {
            $totalSkip++;
            continue;
        }

        // Copy file
        if (!file_exists($dest)) {
            if (!copy($imgFile, $dest)) {
                echo "    ✗ Copy failed: " . basename($imgFile) . "\n";
                continue;
            }
        }

        // Determine if this should be primary
        $isPrimary = (!$madeFirstPrimary) ? 1 : 0;

        if ($isPrimary) {
            $clearPrimStmt->execute([':cid' => $comboId]);
        }

        $insertStmt->execute([
            ':cid'     => $comboId,
            ':path'    => $webPath,
            ':primary' => $isPrimary,
            ':sort'    => $sortOrder,
        ]);

        if ($isPrimary) {
            $syncMainStmt->execute([':img' => $webPath, ':id' => $comboId]);
            $madeFirstPrimary = true;
            echo "    ★ PRIMARY: {$newName}\n";
        } else {
            echo "    + gallery: {$newName}\n";
        }

        $existing[]  = $webPath;
        $sortOrder++;
        $totalAdded++;
    }
}

echo "\n═══════════════════════════════\n";
echo "Added   : {$totalAdded} new images\n";
echo "Skipped : {$totalSkip} (already in DB)\n";
echo "\nFinal combo_images count: ";
echo $db->query("SELECT COUNT(*) FROM combo_images")->fetchColumn() . "\n";

// Final summary table
echo "\n╔═══ FINAL PER-COMBO GALLERY COUNT ════════════════════╗\n";
$stmt = $db->query("
    SELECT c.id, c.name, COUNT(ci.id) AS total, SUM(ci.is_primary) AS primaries
    FROM combos c
    LEFT JOIN combo_images ci ON ci.combo_id = c.id
    WHERE c.id >= 33
    GROUP BY c.id ORDER BY c.id
");
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $flag = ((int)$r['primaries'] > 0 && (int)$r['total'] > 0) ? '✓' : '✗';
    printf("  %s #%-3d %-42s %d imgs\n", $flag, $r['id'], substr($r['name'],0,42), $r['total']);
}
echo "╚═══════════════════════════════════════════════════════╝\n";
