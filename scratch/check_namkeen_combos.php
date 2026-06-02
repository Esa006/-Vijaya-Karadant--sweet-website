<?php
require_once __DIR__ . '/../config/config.php';
$db = Database::getInstance();

echo "=== NAMKEEN COMBOS ===\n";
print_r($db->query("SELECT id, name, category FROM combos WHERE category = 'namkeen'")->fetchAll(PDO::FETCH_ASSOC));
