<?php
/**
 * Sweets Website
 * =============================================================
 * File: scratch/migrate_combo_images.php
 * Description: Import all images from assets/images/combos/Combo/
 *              subfolders into combo_images table.
 *              Maps each subfolder name to its combo in the DB
 *              by fuzzy slug/name matching.
 *              The largest file per folder becomes the primary image.
 * =============================================================
 */
require_once __DIR__ . '/../config/config.php';

$db       = Database::getInstance();
$baseDir  = __DIR__ . '/../assets/images/combos/Combo/';
$webBase  = 'assets/images/combos/';  // relative path stored in DB

// ── Load all combos ─────────────────────────────────────────────────────────
$stmt   = $db->query("SELECT id, name, slug, image FROM combos ORDER BY id");
$combos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Build lookup by normalised name/slug
function normalise(string $s): string {
    $s = strtolower($s);
    $s = preg_replace('/[^a-z0-9]+/', '-', $s);
    return trim($s, '-');
}

$comboMap = [];
foreach ($combos as $c) {
    $comboMap[normalise($c['slug'])] = $c;
    $comboMap[normalise($c['name'])] = $c;
}

// ── Scan subfolders ─────────────────────────────────────────────────────────
$folders = glob($baseDir . '*', GLOB_ONLYDIR);
sort($folders);

$totalImported  = 0;
$totalSkipped   = 0;
$notMatched     = [];

foreach ($folders as $folder) {
    $folderName = basename($folder);
    $normFolder = normalise($folderName);

    // Try exact match first
    $matched = $comboMap[$normFolder] ?? null;

    // If not exact, try partial — find combo whose slug contains the folder name words
    if (!$matched) {
        $folderWords = preg_split('/-+/', $normFolder);
        $bestScore   = 0;
        foreach ($comboMap as $key => $c) {
            $keyWords  = preg_split('/-+/', $key);
            $common    = count(array_intersect($folderWords, $keyWords));
            $score     = $common / max(1, count($folderWords));
            if ($score > $bestScore && $score >= 0.5) {
                $bestScore = $score;
                $matched   = $c;
            }
        }
    }

    if (!$matched) {
        $notMatched[] = $folderName;
        echo "⚠  No match for [{$folderName}] — skipping\n";
        continue;
    }

    $comboId   = (int)$matched['id'];
    $comboName = $matched['name'];

    // Get images in folder — sort by size descending (largest = primary candidate)
    $images = glob($folder . '/*.{jpg,jpeg,png,webp,gif}', GLOB_BRACE);
    if (empty($images)) {
        echo "  ○ [{$folderName}] → #{$comboId} {$comboName} — no images, skip\n";
        continue;
    }
    usort($images, fn($a, $b) => filesize($b) - filesize($a));

    echo "  ► [{$folderName}] → #{$comboId} {$comboName} (" . count($images) . " images)\n";

    // Check existing gallery rows for this combo
    $existingStmt = $db->prepare("SELECT image_path FROM combo_images WHERE combo_id = :cid");
    $existingStmt->execute([':cid' => $comboId]);
    $existingPaths = array_column($existingStmt->fetchAll(PDO::FETCH_ASSOC), 'image_path');

    $sortOrder  = count($existingPaths);   // append after any already-seeded images
    $insertStmt = $db->prepare(
        "INSERT INTO combo_images (combo_id, image_path, is_primary, sort_order)
         VALUES (:combo_id, :image_path, :is_primary, :sort_order)"
    );
    $clearPrimaryStmt = $db->prepare(
        "UPDATE combo_images SET is_primary = 0 WHERE combo_id = :cid"
    );
    $setPrimaryStmt   = $db->prepare(
        "UPDATE combos SET image = :img WHERE id = :id"
    );

    $madeFirstPrimary = false;

    foreach ($images as $imgFile) {
        $ext      = strtolower(pathinfo($imgFile, PATHINFO_EXTENSION));
        $newName  = 'combo-' . $comboId . '-' . substr(md5($imgFile), 0, 8) . '.' . $ext;
        $destPath = dirname(__DIR__) . '/assets/images/combos/' . $newName;
        $webPath  = $webBase . $newName;

        // Skip if already in DB (by path prefix match — file might have been copied before)
        $alreadyIn = false;
        foreach ($existingPaths as $ep) {
            if (strpos($ep, 'combo-' . $comboId . '-') !== false) {
                // already imported some from this combo; still add new ones
            }
        }

        // Copy file if destination doesn't exist
        if (!file_exists($destPath)) {
            if (!copy($imgFile, $destPath)) {
                echo "    ✗ Failed to copy " . basename($imgFile) . "\n";
                continue;
            }
        }

        // Check if this exact path already in combo_images
        if (in_array($webPath, $existingPaths)) {
            $totalSkipped++;
            continue;
        }

        // First image (largest) → set as primary for this combo
        $isPrimary = (!$madeFirstPrimary) ? 1 : 0;

        if ($isPrimary) {
            // Clear existing primary flags
            $clearPrimaryStmt->execute([':cid' => $comboId]);
        }

        $insertStmt->execute([
            ':combo_id'   => $comboId,
            ':image_path' => $webPath,
            ':is_primary' => $isPrimary,
            ':sort_order' => $sortOrder,
        ]);

        if ($isPrimary) {
            // Sync combos.image to the new primary
            $setPrimaryStmt->execute([':img' => $webPath, ':id' => $comboId]);
            $madeFirstPrimary = true;
            echo "    ★ PRIMARY: {$newName}\n";
        } else {
            echo "    + gallery: {$newName}\n";
        }

        $existingPaths[] = $webPath;
        $sortOrder++;
        $totalImported++;
    }
}

echo "\n=============================\n";
echo "Done. Imported {$totalImported} images.\n";
echo "Skipped (already present): {$totalSkipped}\n";
if (!empty($notMatched)) {
    echo "Unmatched folders:\n";
    foreach ($notMatched as $f) echo "  - {$f}\n";
}
