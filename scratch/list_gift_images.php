<?php
$baseDir = "C:/xampp/htdocs/sweet-website/assets/images/Gift";
$dirs = array_filter(glob($baseDir . '/*'), 'is_dir');

foreach ($dirs as $dir) {
    echo "Folder: " . basename($dir) . "\n";
    $files = glob($dir . '/*');
    foreach ($files as $file) {
        if (is_file($file)) {
            echo "  - " . basename($file) . " (" . filesize($file) . " bytes)\n";
        }
    }
}
