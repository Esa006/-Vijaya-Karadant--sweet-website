<?php
require_once 'config/config.php';
require_once 'config/Database.php';

try {
    $db = Database::getInstance();
    $stmt = $db->query("SELECT id, email, role, status FROM users WHERE role = 'admin'");
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($admins, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo $e->getMessage();
}
