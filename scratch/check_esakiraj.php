<?php
require_once __DIR__ . '/../config/config.php';

$db = Database::getInstance();
$r = $db->query("SELECT * FROM users WHERE full_name LIKE '%esakiraj%' OR email LIKE '%esakiraj%'")->fetchAll(PDO::FETCH_ASSOC);
echo "=== SEARCH FOR ESAKIRAJ ===\n";
print_r($r);
