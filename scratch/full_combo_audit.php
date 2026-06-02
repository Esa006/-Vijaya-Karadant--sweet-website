<?php
/**
 * Full combo image audit — shows every combo, its DB gallery count,
 * which Combo/ subfolder matched (if any), and whether the primary
 * image file actually exists on disk.
 */
require_once __DIR__ . '/../config/config.php';

$db      = Database::getInstance();
$baseDir = __DIR__ . '/../assets/images/combos/Combo/';
$assetDir = __DIR__ . '/../assets/images/combos/';

// Load all combos
$stmt   = $db->query("SELECT id, name, slug, image FROM combos ORDER BY id");
$combos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Load gallery counts per combo
$gcStmt = $db->query("SELECT combo_id, COUNT(*) AS cnt, SUM(is_primary) AS primaries FROM combo_images GROUP BY combo_id");
$gcMap  = [];
while ($r = $gcStmt->fetch(PDO::FETCH_ASSOC)) {
    $gcMap[(int)$r['combo_id']] = ['cnt' => (int)$r['cnt'], 'primaries' => (int)$r['primaries']];
}

// Load all Combo/ subfolders
$folders = [];
foreach (glob($baseDir . '*', GLOB_ONLYDIR) as $f) {
    $folders[strtolower(basename($f))] = $f;
}

// Normalise helper
function norm(string $s): string {
    return strtolower(trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($s)), '-'));
}

echo "╔══════════════════════════════════════════════════════════════════════╗\n";
echo "║              COMBO IMAGE AUDIT                                       ║\n";
echo "╚══════════════════════════════════════════════════════════════════════╝\n\n";

$withGallery    = 0;
$withoutGallery = 0;
$missingPrimary = 0;
$primaryBroken  = 0;

foreach ($combos as $c) {
    $id   = (int)$c['id'];
    $gc   = $gcMap[$id] ?? ['cnt' => 0, 'primaries' => 0];
    $img  = $c['image'] ?? '';

    $imgExists = $img && file_exists(__DIR__ . '/../' . $img);
    $status    = '';

    if ($gc['cnt'] === 0) {
        $status = '⚠  NO GALLERY';
        $withoutGallery++;
    } elseif ($gc['primaries'] === 0) {
        $status = '⚠  NO PRIMARY';
        $missingPrimary++;
    } elseif (!$imgExists) {
        $status = '✗  PRIMARY FILE MISSING';
        $primaryBroken++;
    } else {
        $status = '✓  OK (' . $gc['cnt'] . ' imgs)';
        $withGallery++;
    }

    // Find matching subfolder
    $matchedFolder = '—';
    $normSlug = norm($c['slug']);
    $normName = norm($c['name']);
    foreach ($folders as $fname => $fpath) {
        if ($fname === $normSlug || $fname === $normName ||
            strpos($normSlug, $fname) !== false || strpos($fname, $normSlug) !== false ||
            similar_text($fname, $normSlug, $pct) && $pct > 60) {
            $matchedFolder = basename($fpath);
            break;
        }
    }

    printf("  #%-3d %-40s  %s  [folder: %s]\n",
        $id,
        substr($c['name'], 0, 40),
        $status,
        $matchedFolder
    );
}

echo "\n";
echo "══════════════════════════════════════\n";
printf("  Total combos       : %d\n", count($combos));
printf("  With gallery (OK)  : %d\n", $withGallery);
printf("  No gallery images  : %d\n", $withoutGallery);
printf("  Missing primary    : %d\n", $missingPrimary);
printf("  Primary file 404   : %d\n", $primaryBroken);
echo "══════════════════════════════════════\n\n";

// Show what's in each subfolder vs what made it into DB
echo "╔══════════════════════════════════════════════════════════════════════╗\n";
echo "║              SUBFOLDER → DB MATCH DETAIL                            ║\n";
echo "╚══════════════════════════════════════════════════════════════════════╝\n\n";

foreach (glob($baseDir . '*', GLOB_ONLYDIR) as $folder) {
    $fname  = basename($folder);
    $images = glob($folder . '/*.{jpg,jpeg,png,webp}', GLOB_BRACE);
    echo "  [{$fname}] — " . count($images) . " source images\n";
    // Check how many of these made it into DB (by size of copied files in assets/images/combos/)
    $copiedCount = count(glob($assetDir . 'combo-*.*'));
    // just show src count for now
}
echo "\n  Total source images in all Combo/ subfolders: ";
$total = 0;
foreach (glob($baseDir . '*/') as $d) {
    $total += count(glob(rtrim($d, '/') . '/*.{jpg,jpeg,png,webp}', GLOB_BRACE));
}
echo $total . "\n";

echo "\n  combo_images rows in DB: ";
echo $db->query("SELECT COUNT(*) FROM combo_images")->fetchColumn() . "\n";
echo "  (Primary rows)         : ";
echo $db->query("SELECT COUNT(*) FROM combo_images WHERE is_primary=1")->fetchColumn() . "\n\n";
