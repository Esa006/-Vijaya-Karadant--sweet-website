<?php
/**
 * Sweets Website - Enterprise Entry Point
 */

// Define application start time for performance tracking
define('APP_START', microtime(true));

require_once __DIR__ . '/../config/config.php';

use App\Core\Router;

try {
    // Initialize Router
    $router = new Router();

    // Define Routes
    // (We will move these to a separate routes file later)
    $router->get('/', function() {
        // Change working directory to project root so legacy includes work
        chdir(dirname(__DIR__));
        require_once 'legacy/index_legacy.php';
    });

    $router->get('/api/health', function() {
        echo json_encode(['status' => 'success', 'timestamp' => time(), 'csrf_token' => $_SESSION['csrf_token']]);
    });

    // Auth Routes
    $router->post('/api/auth/login', 'Auth\Controllers\AuthController@login', [
        \App\Middleware\CsrfMiddleware::class
    ]);
    $router->get('/api/auth/logout', 'Auth\Controllers\AuthController@logout');

    // Admin Routes (Protected)
    $router->get('/api/admin/dashboard', 'Admin\Controllers\DashboardController@index', [
        \App\Middleware\AuthMiddleware::class,
        new \App\Middleware\PermissionMiddleware('dashboard.view')
    ]);

    // Dispatch Request
    // Calculate the base path relative to the server root
    // For XAMPP: /sweet-website /public/index.php -> basePath is /sweet-website 
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);
    $basePath = str_replace('/public/index.php', '', $scriptName);
    
    $requestUri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
    
    // Robustly remove base path
    if (!empty($basePath) && strpos($requestUri, $basePath) === 0) {
        $requestUri = substr($requestUri, strlen($basePath));
    }
    
    // Handle the case where /public might still be there
    $requestUri = str_replace('/public', '', $requestUri);

    // Final clean up: ensure it starts with / and has no trailing spaces that break regex
    $requestUri = '/' . trim(ltrim($requestUri, '/'));
    
    // Handle index.php being explicitly called
    if ($requestUri === '/index.php') {
        $requestUri = '/';
    }


    $router->dispatch($_SERVER['REQUEST_METHOD'], $requestUri);

} catch (\Throwable $e) {
    // Let the enterprise ErrorHandler deal with it (logs, corr_id, JSON/HTML response)
    ErrorHandler::handleException($e);
}
