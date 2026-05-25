<?php
require_once 'config/config.php';
require_once 'config/Database.php';

try {
    $db = Database::getInstance();
    $stmt = $db->prepare("SELECT id, email, role, status FROM users WHERE email = ?");
    $stmt->execute(['admin@sweets.com']);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($user, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo $e->getMessage();
}
