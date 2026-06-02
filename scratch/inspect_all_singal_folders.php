<?php
require_once __DIR__ . '/../config/config.php';
$db = Database::getInstance();

$singalDir = __DIR__ . '/../assets/images/Singal';
$targetFolders = [
    'Dry-fruit-honey',
    'Gandhagiri-laddu-small-pink-pack',
    'Gandhgiri-laddu-box',
    'Gandhgiri-laddu-bucket',
    'Lagadi-pak-premium-laddu',
    'Lagdipak-cubes',
    'Lagdipak-laddu',
    'Marvel-dates',
    'Moong-dal-laddu',
    'Oats-laddu',
    'Peanut-laddu',
    'Premium-250g',
    'Premium-500g',
    'Raagi-laddu',
    'regal-anjeer',
    'Regal-anjeer-samll-tub',
    'Regal-anjeer-tub',
    'Supreme-0',
    'Supreme-250g',
    'Supreme-500g',
    'Supreme-tub',
    'Till-laddu'
];

foreach ($targetFolders as $folder) {
    $folderPath = $singalDir . '/' . $folder;
    echo "\nFolder: $folder\n";
    if (is_dir($folderPath)) {
        $files = scandir($folderPath);
        $fileList = [];
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            $fileList[] = $file;
        }
        echo "  Total: " . count($fileList) . " files\n";
        echo "  First few files:\n";
        foreach (array_slice($fileList, 0, 3) as $f) {
            echo "    - $f (" . filesize($folderPath . '/' . $f) . " bytes)\n";
        }
    } else {
        echo "  Directory does not exist!\n";
    }
}
