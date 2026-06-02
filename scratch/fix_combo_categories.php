<?php
/**
 * Fix combo categories based on actual products in each combo.
 * Rules:
 *  karadant  → contains karadant products (Vijaya, Regal Anjeer, Dink Karadant, Marvel Karadant, Classic, Supreme, Premium)
 *  laddu     → contains laddu products (Dink Laddu, Ragi Laddu, Besan, Til, Peanut, Gandahagiri Laddu, Ladagi, Otts)
 *  namkeen   → contains namkeen products (Muruku, Kodubale, Sev, Mix, Ribbon, Nippattu, Masala, Namkeen)
 *  gifting   → large bucket/tub combos, premium gift boxes, or explicit gifting packs (Tilkut Box, Gift Box, Anjeer Gift Box)
 *  mixed     → genuinely spans 2+ categories equally
 */
require_once __DIR__ . '/../config/config.php';
$db = Database::getInstance();

// Explicit corrections (id => correct_category)
$fixes = [
    // ── Combos 1-32 ────────────────────────────────────────────────────────
    1  => 'mixed',      // Mega Sweet Combo: Dink Laddu + Premium Karadant → mixed
    2  => 'laddu',      // Festive Namkeen Mix: actually only Ragi Laddu
    3  => 'laddu',      // Premium Laddu Box: Dink Laddu → laddu ✓
    4  => 'karadant',   // Family Festival Pack: Premium Karadant → karadant ✓
    5  => 'karadant',   // Classic Karadant Pair: Classic + Premium Vijaya → karadant ✓
    6  => 'mixed',      // Healthy Bites: Dink Karadant + Ragi Laddu → mixed
    7  => 'gifting',    // Ultimate Gift Box: Regal Anjeer + Supreme → gifting ✓
    8  => 'laddu',      // Mini Snack Pack: Dink Laddu → laddu (was namkeen, wrong)
    9  => 'karadant',   // demo: Supreme Vijaya → karadant ✓
    10 => 'karadant',   // laddu (test): Premium + Supreme Vijaya → karadant
    11 => 'mixed',      // Premium Traditional Combo 01: Bengaluru Mix (namkeen) + Peanut Laddu → mixed
    12 => 'laddu',      // Festive Family Pack 02: Dink Laddu → laddu (was gifting)
    13 => 'karadant',   // Gourmet Sweet Assortment 03: Classic + Premium Vijaya → karadant ✓
    14 => 'gifting',    // Luxury Celebration Box 04: Regal Anjeer + Supreme → gifting ✓
    15 => 'karadant',   // Artisanal Karadant Selection 05 → karadant ✓
    16 => 'karadant',   // Classic Sweet Duo 06 → karadant ✓
    17 => 'laddu',      // Health-Conscious Sweet Mix 07 → laddu ✓
    18 => 'mixed',      // Royal Sweet Platter 08: Dink Karadant + Spicy Mix Namkeen → mixed (was gifting)
    19 => 'gifting',    // Grand Festival Combo 09: Tilkut Gift Box + Bengaluru Mix → gifting ✓
    20 => 'mixed',      // Signature Sweet Box 10: Butter Muruku (namkeen) + Supreme Karadant → mixed (was karadant)
    21 => 'mixed',      // Traditional Delights 11: Premium Mixture (namkeen) + Supreme Karadant → mixed (was karadant)
    22 => 'karadant',   // Sweet Heritage Pack 12: Dink Karadant + Premium Vijaya + Rice Kodubale → karadant-dominant
    23 => 'namkeen',    // Premium Laddu Mix 13: Garlic Ribbon + Nippattu → namkeen (was laddu, wrong!)
    24 => 'namkeen',    // Crunchy Namkeen Combo 14 → namkeen ✓
    25 => 'gifting',    // Sweet & Spicy Pair 15 → gifting ✓
    26 => 'gifting',    // Corporate Gifting Pack 16 → gifting ✓
    27 => 'gifting',    // Homecoming Special 17: Premium Gift Box + Ribbon Pakoda → gifting
    28 => 'namkeen',    // Evening Snack Mix 18 → namkeen ✓
    29 => 'karadant',   // Bestseller Combo 19 → karadant ✓
    30 => 'mixed',      // Chef Special Selection 20: Butter Muruku + Premium Otts Laddu → mixed (was karadant)
    31 => 'laddu',      // karkant: Premium Otts Laddu → laddu (was laddu ✓)
    32 => 'gifting',    // demo updated: Festive Special Box → gifting (was karadant)

    // ── Real combos 33-51 ─────────────────────────────────────────────────
    33 => 'karadant',   // Dink & Regal Anjeer Combo: Dink Karadant + Regal Anjeer → karadant ✓
    34 => 'gifting',    // Dink & Gandhagiri Heritage Buckets: 1kg buckets → gifting ✓
    35 => 'mixed',      // Dink Laddu Bucket & Premium Karadant: laddu + karadant → mixed (was karadant)
    36 => 'mixed',      // Dink Laddu Premium Duo: Dink Laddu + Premium Vijaya → mixed (was karadant)
    37 => 'mixed',      // Dink Laddu Supreme Combo: Dink Laddu + Supreme Vijaya → mixed (was karadant)
    38 => 'karadant',   // Dink Supreme Gandhagiri Trio: Dink Karadant + Supreme + Gandahagiri Laddu → karadant-dominant
    39 => 'karadant',   // Gandh Supreme Marvel Dink Quartet: Dink+Gandh+Marvel Karadant+Supreme → karadant (was gifting)
    40 => 'gifting',    // Gandhagiri Bucket & Premium Karadant: 1kg Bucket → gifting ✓
    41 => 'gifting',    // Grand Buckets & Tubs Feast: 1kg buckets → gifting ✓
    42 => 'karadant',   // Gandhagiri Supreme Duo: Gandahagiri Laddu + Supreme Karadant → karadant ✓
    43 => 'laddu',      // Lagdi Pak & Ladagi Laddu Premium: Ladagi Laddu + Lagdi Pak → laddu ✓
    44 => 'laddu',      // Assorted Laddu Box: Besan + Peanut + Til Laddu → laddu ✓
    45 => 'laddu',      // Marvel Oats Ragi Healthy Mix: Marvel Karadant + Otts + Ragi Laddu → laddu-dominant
    46 => 'karadant',   // Premium Lagdi Pak & Marvel Trio: Marvel Karadant + Premium Vijaya + Lagdi Pak → karadant ✓
    47 => 'gifting',    // Ultimate Heritage Grand Collection: 6-product mega → gifting ✓
    48 => 'karadant',   // Regal Anjeer Supreme Pair: Regal Anjeer + Supreme → karadant ✓
    49 => 'gifting',    // Regal Anjeer Tub & Dink Laddu Bucket: tub+bucket combo → gifting ✓ (was gifting ✓)
    50 => 'gifting',    // Anjeer & Gandhagiri Festive Trio: Bucket+Tub+Karadant → gifting ✓
    51 => 'laddu',      // Traditional Laddu Trio: Besan + Til Laddu → laddu ✓
];

$updateStmt = $db->prepare("UPDATE combos SET category = :cat WHERE id = :id");

// Load current categories for diff display
$current = [];
$stmt = $db->query("SELECT id, name, category FROM combos ORDER BY id");
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $current[(int)$r['id']] = ['name' => $r['name'], 'cat' => $r['category']];
}

$changed = 0;
$same    = 0;

echo "╔═════════ CATEGORY CORRECTIONS ════════════════════════════════════╗\n";
foreach ($fixes as $id => $newCat) {
    $old = $current[$id]['cat'] ?? '?';
    $name = $current[$id]['name'] ?? "#{$id}";
    if ($old !== $newCat) {
        $updateStmt->execute([':cat' => $newCat, ':id' => $id]);
        printf("  ✏  #%-3d %-40s  %s → %s\n", $id, substr($name,0,40), str_pad($old,10), $newCat);
        $changed++;
    } else {
        printf("  ✓  #%-3d %-40s  %s (unchanged)\n", $id, substr($name,0,40), $old);
        $same++;
    }
}
echo "╚════════════════════════════════════════════════════════════════════╝\n";
echo "\nUpdated: {$changed} combos\nUnchanged: {$same} combos\n\n";

// Final category distribution
echo "╔═════════ CATEGORY DISTRIBUTION ═══════════╗\n";
$stmt = $db->query("SELECT category, COUNT(*) AS cnt FROM combos GROUP BY category ORDER BY cnt DESC");
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    printf("  %-12s : %d combos\n", $r['category'], $r['cnt']);
}
echo "╚════════════════════════════════════════════╝\n";
