<?php
$dir = 'c:/xampp/htdocs/sweet-website/assets/images/combo-offer/';
$files = glob($dir . '*.jpeg');
sort($files);
$i = 1;
foreach ($files as $file) {
    $newName = 'combo-offer-' . str_pad($i, 2, '0', STR_PAD_LEFT) . '.jpeg';
    rename($file, $dir . $newName);
    echo "Renamed: " . basename($file) . " -> " . $newName . "\n";
    $i++;
}
