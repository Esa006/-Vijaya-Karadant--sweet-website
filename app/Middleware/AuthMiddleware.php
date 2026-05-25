<?php

namespace App\Middleware;

use App\Modules\Auth\Services\AuthService;

/**
 * Auth Middleware
 * Protects routes from unauthenticated access
 */
class AuthMiddleware {
    
    /**
     * Handle the request
     * @return bool True if authorized, False otherwise
     */
    public function handle(): bool {
        if (!AuthService::isLoggedIn()) {
            // Check if it's an API request
            if (isset($_SERVER['CONTENT_TYPE']) && $_SERVER['CONTENT_TYPE'] === 'application/json' || 
                strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
                header('Content-Type: application/json');
                http_response_code(401);
                echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
                return false;
            }

            // Redirect to login for web requests
            header('Location: /admin/login.php'); // Fallback to existing login for now
            return false;
        }

        return true;
    }
}
