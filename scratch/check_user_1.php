<?php
require_once 'config/config.php';
require_once 'config/Database.php';

try {
    $db = Database::getInstance();
    $stmt = $db->query("SELECT id FROM users WHERE id = 1");
    $user = $stmt->fetch();
    echo $user ? 'YES' : 'NO';
} catch (Exception $e) {
    echo $e->getMessage();
}
