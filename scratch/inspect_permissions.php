<?php
require_once 'config/config.php';
use App\Core\Database;

$output = "";
try {
    $stmt = Database::query("DESCRIBE permissions");
    $output .= "Table 'permissions' structure:\n";
    while ($row = $stmt->fetch()) {
        $output .= " - {$row['Field']} ({$row['Type']})\n";
    }
} catch (Exception $e) {
    $output .= "Error: " . $e->getMessage() . "\n";
}

file_put_contents('scratch/table_info.txt', $output);
echo "Done.";
