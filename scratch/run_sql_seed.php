<?php
require_once 'config/config.php';
require_once 'config/Database.php';

try {
    $db = Database::getInstance();
    $sql = file_get_contents('database/seed_new_combos.sql');
    $db->exec($sql);
    echo "SQL Seed executed successfully!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
