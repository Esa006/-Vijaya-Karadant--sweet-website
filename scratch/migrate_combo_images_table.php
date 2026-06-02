<?php
/**
 * Sweets Website
 * Migration: Create combo_images table
 */
require_once __DIR__ . '/../config/config.php';
$db = Database::getInstance();

$sql = "
CREATE TABLE IF NOT EXISTS combo_images (
    id          INT(11)      NOT NULL AUTO_INCREMENT,
    combo_id    INT(11)      NOT NULL,
    image_path  VARCHAR(255) NOT NULL,
    is_primary  TINYINT(1)   NOT NULL DEFAULT 0,
    sort_order  INT(11)      NOT NULL DEFAULT 0,
    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_combo_images_combo_id (combo_id),
    KEY idx_combo_images_primary  (combo_id, is_primary),
    CONSTRAINT fk_combo_images_combo
        FOREIGN KEY (combo_id) REFERENCES combos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

try {
    $db->exec($sql);
    echo "OK: combo_images table created (or already exists).\n";
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

// Seed primary images from existing combos.image column
$stmt = $db->query("SELECT id, image FROM combos WHERE image IS NOT NULL AND image != ''");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$insert = $db->prepare(
    "INSERT IGNORE INTO combo_images (combo_id, image_path, is_primary, sort_order)
     SELECT :combo_id, :image_path, 1, 0
     WHERE NOT EXISTS (
         SELECT 1 FROM combo_images WHERE combo_id = :combo_id2 AND is_primary = 1
     )"
);

$seeded = 0;
foreach ($rows as $r) {
    $insert->execute([
        ':combo_id'   => $r['id'],
        ':image_path' => $r['image'],
        ':combo_id2'  => $r['id'],
    ]);
    if ($insert->rowCount() > 0) $seeded++;
}

echo "OK: Seeded {$seeded} primary combo images from existing combos.image column.\n";
