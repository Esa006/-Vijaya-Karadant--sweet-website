<?php
require_once 'config/config.php';
require_once 'config/Database.php';

try {
    $db = Database::getInstance();
    $stmt = $db->query("SELECT id, name FROM products LIMIT 10");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC), JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo $e->getMessage();
}
