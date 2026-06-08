<?php
/**
 * Sweets Website
 * =============================================================
 * File: tests/bootstrap.php
 * Description: Bootstrapping for PHPUnit testing
 * =============================================================
 */

define('ROOT_PATH', str_replace('\\', '/', dirname(__DIR__)));
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('SERVICES_PATH', ROOT_PATH . '/services');
define('REPOS_PATH', ROOT_PATH . '/repositories');

// Load Autoloader & env variables
require_once ROOT_PATH . '/src/Autoloader.php';

// Register fallback autoloader for global namespace classes (Legacy Services, Repositories, Config)
spl_autoload_register(function ($class) {
    $paths = [
        ROOT_PATH . '/repositories/' . $class . '.php',
        ROOT_PATH . '/services/' . $class . '.php',
        ROOT_PATH . '/config/' . $class . '.php',
    ];

    foreach ($paths as $file) {
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});
