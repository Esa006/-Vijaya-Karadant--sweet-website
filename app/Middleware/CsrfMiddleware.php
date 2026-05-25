<?php

namespace App\Middleware;

/**
 * CSRF Protection Middleware
 * Ensures all non-GET requests have a valid CSRF token
 */
class CsrfMiddleware {
    
    public function handle(): bool {
        // Skip check for GET, HEAD, OPTIONS
        if (in_array($_SERVER['REQUEST_METHOD'], ['GET', 'HEAD', 'OPTIONS'])) {
            return true;
        }

        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['csrf_token'] ?? null;

        if (!$token || $token !== ($_SESSION['csrf_token'] ?? '')) {
            header('Content-Type: application/json');
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'CSRF token mismatch']);
            return false;
        }

        return true;
    }
}
