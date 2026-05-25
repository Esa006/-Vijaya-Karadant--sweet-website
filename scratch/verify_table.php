<?php
require_once dirname(__DIR__) . '/config/config.php';
use App\Core\Database;

try {
    $stmt = Database::query("DESCRIBE permissions");
    echo "<h1>Table 'permissions' structure:</h1><ul>";
    while ($row = $stmt->fetch()) {
        echo "<li><b>{$row['Field']}</b> ({$row['Type']})</li>";
    }
    echo "</ul>";
} catch (Exception $e) {
    echo "<h1>Error: " . $e->getMessage() . "</h1>";
}
