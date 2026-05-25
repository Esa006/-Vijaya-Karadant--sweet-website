<?php
require_once 'config/config.php';
require_once 'config/Database.php';

try {
    $db = Database::getInstance();
    $sql = file_get_contents('database/create_failed_orders.sql');
    $db->exec($sql);
    echo "Failed orders table ensured.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
