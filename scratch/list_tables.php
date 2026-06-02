<?php
require_once __DIR__ . '/../config/config.php';
$db = Database::getInstance();

echo "=== DATABASE TABLES ===\n";
$tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
foreach ($tables as $t) {
    echo "- $t\n";
}
