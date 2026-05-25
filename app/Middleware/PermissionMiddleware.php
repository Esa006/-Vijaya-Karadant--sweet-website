<?php

namespace App\Middleware;

use App\Modules\Permissions\Services\PermissionService;

/**
 * Permission Middleware
 * Validates granular permissions for protected routes
 */
class PermissionMiddleware {
    
    private string $requiredPermission;

    public function __construct(string $requiredPermission) {
        $this->requiredPermission = $requiredPermission;
    }

    /**
     * Handle the request
     */
    public function handle(): bool {
        if (!PermissionService::can($this->requiredPermission)) {
            header('Content-Type: application/json');
            http_response_code(403);
            echo json_encode([
                'status' => 'error',
                'message' => "Forbidden: You do not have the required permission ({$this->requiredPermission})"
            ]);
            return false;
        }

        return true;
    }
}
