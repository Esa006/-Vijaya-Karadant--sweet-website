<?php
/**
 * Sweets Website
 * =============================================================
 * File: config.php
 * Description: Global constants and configuration settings
 * Author: Sweets Website Team
 * Version: 1.0.6
 * =============================================================
 */

// 1. Path Constants (Defined first to help with URL calculation)
if (!defined('ROOT_PATH')) {
    // Ensure ROOT_PATH uses forward slashes for consistency
    define('ROOT_PATH', str_replace('\\', '/', dirname(__DIR__)));
}
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('SERVICES_PATH', ROOT_PATH . '/services');
define('REPOS_PATH', ROOT_PATH . '/repositories');

// Load Autoloader & .env
require_once ROOT_PATH . '/src/Autoloader.php';

// Helper to reliably get env vars even if putenv is disabled
function get_env_var($key, $default = null) {
    if (isset($_ENV[$key])) return $_ENV[$key];
    if (isset($_SERVER[$key])) return $_SERVER[$key];
    $val = getenv($key);
    return $val !== false ? $val : $default;
}

// 2. Database Credentials (Loaded via .env)
define('DB_HOST', get_env_var('DB_HOST', '127.0.0.1'));
define('DB_NAME', get_env_var('DB_NAME', 'sweets_db'));
define('DB_USER', get_env_var('DB_USER', 'root'));
define('DB_PASS', get_env_var('DB_PASS', ''));

// Application Mode
define('APP_ENV', get_env_var('APP_ENV', 'production'));
define('APP_DEBUG', (get_env_var('APP_DEBUG') === 'true'));

// 3. Dynamic Settings
$globalSiteSettings = [];
try {
    require_once SERVICES_PATH . '/SettingService.php';
    $__settingSvc = new SettingService();
    $globalSiteSettings = $__settingSvc->getAllSettings();
} catch (Throwable $e) {
    // Failsafe if DB is unavailable during installation
    error_log("Failed to load site settings: " . $e->getMessage());
}

define('SITE_NAME', $globalSiteSettings['store_name'] ?? 'Sweets Website');
define('SITE_LOGO', $globalSiteSettings['store_logo'] ?? 'assets/images/logo-1.png');
define('SITE_FAVICON', $globalSiteSettings['ui_favicon'] ?? 'assets/images/logo-1.png');
define('SITE_TAGLINE', $globalSiteSettings['store_tagline'] ?? 'Authentic Karnataka sweets, crafted with heart.');
define('SITE_EMAIL', $globalSiteSettings['store_email'] ?? 'hello@vijayakaradant.com');
define('SITE_PHONE', $globalSiteSettings['store_phone'] ?? '+91 98860 24567');
define('SITE_ADDRESS', $globalSiteSettings['store_address'] ?? '145, Market Road, Near Gandhi Chowk, Gokak, Belagavi, Karnataka 591307');
define('MAX_QTY_LIMIT', (int)($globalSiteSettings['store_max_qty_limit'] ?? 10));

// SMTP Email Settings
define('SMTP_HOST', get_env_var('SMTP_HOST', ''));
define('SMTP_PORT', (int)(get_env_var('SMTP_PORT', 587)));
define('SMTP_USERNAME', get_env_var('SMTP_USERNAME', ''));
define('SMTP_PASSWORD', get_env_var('SMTP_PASSWORD', ''));
define('SMTP_FROM_EMAIL', get_env_var('SMTP_FROM_EMAIL', SITE_EMAIL));
define('SMTP_FROM_NAME', get_env_var('SMTP_FROM_NAME', SITE_NAME));
define('SMTP_SECURE', strtolower((string)(get_env_var('SMTP_SECURE', 'tls'))));

// Email API Configuration
define('ELASTIC_EMAIL_API_KEY', get_env_var('ELASTIC_EMAIL_API_KEY', ''));
define('RESEND_API_KEY', get_env_var('RESEND_API_KEY', ''));

// Set default timezone
date_default_timezone_set('Asia/Kolkata');

// Versioning for cache busting
define('SITE_VERSION', '1.0.8');
define('SHIPPING_RATES', [
    'standard'       => (float)(get_env_var('SHIPPING_STANDARD', 50.00)),
    'express'        => (float)(get_env_var('SHIPPING_EXPRESS', 150.00)),
    'free_threshold' => (float)(get_env_var('SHIPPING_FREE_THRESHOLD', 1000.00))
]);

// UPI Payment Settings — DB (admin settings) takes priority over .env
define('UPI_ID',       $globalSiteSettings['upiId']          ?? get_env_var('UPI_ID', 'vijayakaradant@upi'));
define('UPI_MERCHANT', $globalSiteSettings['upiDisplayName']  ?? get_env_var('UPI_MERCHANT', 'Vijaya Karadant Sweets'));
define('UPI_QR_IMAGE', $globalSiteSettings['shop_qr']         ?? '');

// Razorpay Payment Settings
define('RAZORPAY_KEY',     get_env_var('RAZORPAY_KEY', ''));
define('RAZORPAY_SECRET',  get_env_var('RAZORPAY_SECRET', ''));


// 3. Site URL Detection (Robust Base URL calculation)
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
           (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
$scheme = $isHttps ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

// Calculate BASE_URL by comparing physical path with document root
$physicalRoot = str_replace('\\', '/', ROOT_PATH);
$docRoot = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? '');

// Find the URL path by removing DOCUMENT_ROOT from the physical path
// Handle case-insensitivity on Windows (e.g., C: vs c:)
if ($docRoot !== '' && strncasecmp($physicalRoot, $docRoot, strlen($docRoot)) === 0) {
    $basePath = substr($physicalRoot, strlen($docRoot));
} else {
    // Fallback: manually detect project folder from ROOT_PATH
    $basePath = '/' . basename($physicalRoot);
}

$basePath = '/' . ltrim(str_replace('\\', '/', $basePath), '/'); 
$basePath = rtrim($basePath, '/') . '/'; // Ensure ends with a single slash
$basePath = str_replace(' ', '%20', $basePath); // Encode spaces

if (strpos($host, 'test-vijayakaradant.innovasoftmax.com') !== false || strpos($host, 'testfinal-vijayakaradant.innovasoftmax.com') !== false) {
    define('BASE_URL', 'https://' . $host . '/');
} else {
    define('BASE_URL', $scheme . '://' . $host . $basePath);
}

// 5. Dynamic Navigation Items
define('NAV_ITEMS', [
    ['label' => 'Home', 'url' => 'index.php'],
    ['label' => 'About Us', 'url' => 'about.php'],
    ['label' => 'Karadant', 'url' => 'karadant.php'],
    ['label' => 'Namkeen', 'url' => 'namkeen.php'],
    ['label' => 'Gifting', 'url' => 'gifting.php'],
    ['label' => 'Cart', 'url' => 'cart.php'],
    ['label' => 'Franchise', 'url' => 'franchise.php'],
    ['label' => 'Branches', 'url' => 'branches.php'],
    ['label' => 'Contact Us', 'url' => 'contact.php'],
]);

/**
 * Helper to check if a menu item is active
 */
function is_active($url, array $related_pages = []) {
    $current_page = basename($_SERVER['PHP_SELF']);
    if ($current_page === $url) return 'active';
    foreach ($related_pages as $page) {
        if ($current_page === $page) return 'active';
    }
    return '';
}

// 6. Enterprise Logging & Error Handling (CRITICAL)
require_once SERVICES_PATH . '/LogService.php';
require_once ROOT_PATH . '/src/ErrorHandler.php';

// Initialize Logging
LogService::init(ROOT_PATH . '/logs', APP_DEBUG ? LogService::DEBUG : LogService::INFO);

// Register Global Error Handler
if (!APP_DEBUG) {
    ErrorHandler::register();
}

// Session Hardening & Extension
$isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
            (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

$redisUrl = get_env_var('REDIS_URL');
if (extension_loaded('redis') && $redisUrl) {
    ini_set('session.save_handler', 'redis');
    ini_set('session.save_path', $redisUrl);
} else {
    // Use a private session directory to avoid GC conflicts with other XAMPP projects
    $sessionPath = ROOT_PATH . '/sessions';
    if (!is_dir($sessionPath)) {
        @mkdir($sessionPath, 0700, true);
    }
    if (is_writable($sessionPath)) {
        session_save_path($sessionPath);
    }
}

ini_set('session.gc_maxlifetime', 86400);    // 24 Hours
ini_set('session.cookie_lifetime', 86400);  // 24 Hours
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', 'Lax');
if ($isSecure) {
    ini_set('session.cookie_secure', 1);
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Generate CSRF token if missing
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
