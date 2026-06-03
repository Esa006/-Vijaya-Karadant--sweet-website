<?php
require_once 'config/config.php';
require_once 'config/Database.php';

$email = 'esakiraj006@gmail.com';
$newPassword = 'Password123!';

try {
    $db = Database::getInstance();
    
    // Hash the new password
    $hash = password_hash($newPassword, PASSWORD_DEFAULT);
    
    // Update the database directly
    $stmt = $db->prepare("UPDATE users SET password = ? WHERE email = ?");
    $stmt->execute([$hash, $email]);
    
    if ($stmt->rowCount() > 0) {
        echo "<h1>Success!</h1>";
        echo "<p>The password for <strong>$email</strong> has been successfully reset.</p>";
        echo "<p>Your new password is: <strong>$newPassword</strong></p>";
        echo "<p>Please <a href='login.php'>click here to login</a>.</p>";
        echo "<p style='color:red;'>⚠️ For security, please delete this file (reset_admin.php) from your server after you login.</p>";
    } else {
        echo "<h1>Error</h1>";
        echo "<p>Could not find a user with the email $email. Are you sure that is the correct admin email?</p>";
    }
} catch (Exception $e) {
    echo "Database Error: " . $e->getMessage();
}
