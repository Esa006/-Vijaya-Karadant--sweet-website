<?php
/**
 * Sweets Website - CRM Auto-Installer
 * =============================================================
 * Run this once to create necessary tables for Customer CRM
 * =============================================================
 */

require_once '../config/config.php';

try {
    // Get DB instance from config (assuming $db is available via singleton or global)
    // We will create a fresh connection if needed
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    $sql = file_get_contents('../database/customer_crm_system.sql');

    // Split SQL by semicolons to execute individual statements
    // This is safer for some PDO drivers
    $statements = array_filter(array_map('trim', explode(';', $sql)));

    echo "<h3>Starting CRM Installation...</h3>";
    foreach ($statements as $stmt) {
        if (!empty($stmt)) {
            $pdo->exec($stmt);
            echo "<p style='color:green'>Success: " . substr($stmt, 0, 50) . "...</p>";
        }
    }

    echo "<h2 style='color:blue'>CRM Installation Successful!</h2>";
    echo "<p>You can now go back to <a href='customers.php'>Customers Page</a>.</p>";
    echo "<p style='color:red'><strong>Security Note:</strong> Please delete this file (install-crm.php) after installation.</p>";

} catch (Exception $e) {
    echo "<h2 style='color:red'>Installation Failed:</h2>";
    echo "<pre>" . $e->getMessage() . "</pre>";
}
