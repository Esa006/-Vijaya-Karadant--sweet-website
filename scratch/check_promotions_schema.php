<?php
require_once 'config/config.php';
require_once 'config/Database.php';

try {
    $db = Database::getInstance();
    $stmt = $db->query("DESCRIBE promotions");
    echo "Promotions Table Schema:\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "Field: {$row['Field']} | Type: {$row['Type']}\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
