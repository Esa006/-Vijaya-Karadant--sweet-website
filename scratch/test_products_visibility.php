<?php
// Mock session for auth
session_start();
$_SESSION['user_role'] = 'admin';
$_SESSION['user_id'] = 1;
$_SESSION['user_ip'] = '127.0.0.1';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

// Mock constants if not defined
define('ROOT_PATH', str_replace('\\', '/', dirname(__DIR__)));
require_once __DIR__ . '/../config/config.php';

// Capture output
ob_start();
require_once __DIR__ . '/../admin/products.php';
$html = ob_get_clean();

// Check for Namkeen products in HTML
echo "Checking for Namkeen products in HTML output...\n";
$namkeen_count = substr_count($html, 'Namkeen');
echo "Found 'Namkeen' $namkeen_count times in HTML.\n";

if (strpos($html, 'Butter Muruku') !== false) {
    echo "Product 'Butter Muruku' is present in HTML.\n";
} else {
    echo "Product 'Butter Muruku' is MISSING from HTML.\n";
}

if (strpos($html, 'Spicy Mix Namkeen') !== false) {
    echo "Product 'Spicy Mix Namkeen' is present in HTML.\n";
} else {
    echo "Product 'Spicy Mix Namkeen' is MISSING from HTML.\n";
}

// Check for the table rows
$row_count = substr_count($html, 'class="product-row"');
echo "Total product rows found: $row_count\n";

// Save result to file for inspection
file_put_contents(__DIR__ . '/products_output.html', $html);
