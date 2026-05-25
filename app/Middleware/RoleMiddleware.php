<?php

namespace App\Middleware;

use App\Modules\Auth\Services\AuthService;

/**
 * Role Middleware
 * Restricts access to specific roles (e.g., Admin)
 */
class RoleMiddleware {
    
    private string $requiredRole = 'admin';

    public function handle(): bool {
        if (!AuthService::isLoggedIn() || !AuthService::hasRole($this->requiredRole)) {
            header('Content-Type: application/json');
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Forbidden: Insufficient permissions']);
            return false;
        }
        return true;
    }
}
