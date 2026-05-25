<?php
require_once 'config/config.php';
require_once 'config/Database.php';

try {
    $db = Database::getInstance();
    $password = 'admin123';
    $hash = password_hash($password, PASSWORD_DEFAULT);
    
    // Update existing admin or create admin@sweets.com
    $stmt = $db->prepare("UPDATE users SET email = 'admin@sweets.com', password = ? WHERE email = 'admin@vijayakaradant.com'");
    $stmt->execute([$hash]);
    
    if ($stmt->rowCount() > 0) {
        echo "Admin email updated to admin@sweets.com and password reset to admin123";
    } else {
        // If not found, just insert
        $stmt = $db->prepare("INSERT INTO users (full_name, email, password, role, status, created_at) VALUES ('System Admin', 'admin@sweets.com', ?, 'admin', 'Active', NOW())");
        $stmt->execute([$hash]);
        echo "Created new admin user admin@sweets.com with password admin123";
    }
} catch (Exception $e) {
    echo $e->getMessage();
}
