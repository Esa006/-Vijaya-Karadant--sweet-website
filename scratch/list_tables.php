<?php
require_once 'config/config.php';
use App\Core\Database;

try {
    $stmt = Database::query("SHOW TABLES");
    echo "Tables in database:\n";
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        echo " - {$row[0]}\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
