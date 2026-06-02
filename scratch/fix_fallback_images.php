<?php
require_once __DIR__ . '/../config/config.php';

$src = __DIR__ . '/../assets/images/homepage/The Karadant Range (1) - Copy.png';
$dest = __DIR__ . '/../assets/images/homepage/The Karadant Range (1).png';

echo "=== FIXING FALLBACK IMAGE ===\n";
if (file_exists($src)) {
    if (copy($src, $dest)) {
        echo "Successfully copied:\n  From: $src\n  To: $dest\n";
    } else {
        echo "Error: Failed to copy file.\n";
    }
} else {
    echo "Error: Source file does not exist at $src\n";
}
