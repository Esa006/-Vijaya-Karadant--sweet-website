<?php
/**
 * Sweets Website
 * =============================================================
 * File: logout.php
 * Description: Administrative logout handler to resolve 404 error
 * Author: Antigravity - Senior Backend Engineer
 * Version: 1.0.0
 * =============================================================
 */

require_once '../config/config.php';
require_once SERVICES_PATH . '/AuthService.php';

// Initialize session if not already done (config.php handles this usually)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Instantiate AuthService and trigger logout
$auth = new AuthService();
$auth->logout();

// Logic to determine redirect target
// If we came from the admin panel, redirect to admin login
// Otherwise, redirect to the homepage
$referer = $_SERVER['HTTP_REFERER'] ?? '';
$redirectUrl = BASE_URL . 'index.php';

if (strpos($referer, '/admin/') !== false) {
    $redirectUrl = BASE_URL . 'admin/login.php';
}

header("Location: $redirectUrl");
exit();
