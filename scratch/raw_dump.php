<?php
require_once __DIR__ . '/../config/config.php';
$db = Database::getInstance();

echo "=== RAW COMBO #2 ===\n";
print_r($db->query("SELECT * FROM combos WHERE id = 2")->fetchAll(PDO::FETCH_ASSOC));

echo "=== RAW COMBO ITEMS FOR COMBO #2 ===\n";
print_r($db->query("SELECT * FROM combo_items WHERE combo_id = 2")->fetchAll(PDO::FETCH_ASSOC));
