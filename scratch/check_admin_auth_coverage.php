<?php
require_once __DIR__ . '/../config/config.php';

$adminDir = ROOT_PATH . '/admin';
$filesWithNoAuth = [];

function scanDirRecursive($dir, &$filesWithNoAuth) {
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir . '/' . $item;
        if (is_dir($path)) {
            scanDirRecursive($path, $filesWithNoAuth);
        } else if (pathinfo($path, PATHINFO_EXTENSION) === 'php') {
            // Ignore login.php, setup-password.php, and anything under includes/ (they are parts, not entrypoints, except sidebar/topbar/etc. but it's good to check)
            $filename = basename($path);
            if ($filename === 'login.php' || $filename === 'setup-password.php') {
                continue;
            }
            if (strpos($path, '/includes/') !== false) {
                continue;
            }
            
            $content = file_get_contents($path);
            if (strpos($content, 'auth.php') === false) {
                $filesWithNoAuth[] = str_replace(ROOT_PATH . '/', '', $path);
            }
        }
    }
}

scanDirRecursive($adminDir, $filesWithNoAuth);

echo "=== FILES MISSING AUTH.PHP INCLUSION ===\n";
if (empty($filesWithNoAuth)) {
    echo "None! All files include auth.php.\n";
} else {
    print_r($filesWithNoAuth);
}
