<?php
require_once 'config/config.php';
require_once 'config/Database.php';

try {
    $db = Database::getInstance();
    $stmt = $db->query("SELECT * FROM orders ORDER BY id DESC LIMIT 5");
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($orders, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo $e->getMessage();
}
