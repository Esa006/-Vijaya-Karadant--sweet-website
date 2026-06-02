<?php
/**
 * Sweets Website
 * =============================================================
 * File: auth.php
 * Description: Admin authentication middleware to restrict access
 * =============================================================
 */

/**
 * Administrative Authentication & RBAC Middleware
 * Principal Security Architect Standard v2.1
 */

/**
 * Principal Security Auth Check (Zero Trust)
 * NO LOCALHOST BYPASS ALLOWED
 */
function requireAdmin() {
    $remoteAddr = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    
    // Broaden local detection to include common private network ranges (192.168.x.x, 10.x.x.x, etc.)
    $isLocal = in_array($remoteAddr, ['127.0.0.1', '::1']) || 
               strpos($remoteAddr, '192.168.') === 0 || 
               strpos($remoteAddr, '10.') === 0 ||
               strpos($remoteAddr, '172.') === 0; // Simplified private IP check
    
    $isAuthorized = (
        isset($_SESSION['user_role']) && 
        $_SESSION['user_role'] === 'admin' && 
        isset($_SESSION['user_id'])
    );

    if (!$isAuthorized) {
        // Detect if this is an AJAX or API request
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ||
                  (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) ||
                  (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/api/') !== false);

        if ($isAjax) {
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Session expired or unauthorized. Please log in again.']);
            exit;
        }

        // Log Unauthorized Access Attempt
        error_log("[AuthSecurity] Forbidden access attempt from " . $remoteAddr);
        
        // Secure Redirect: Force login without destroying the whole session (to preserve messages/state)
        header('Location: ' . BASE_URL . 'admin/login.php?auth_error=1');
        exit;
    }
}

/**
 * RBAC: Check for specific administrative permissions
 */
function hasPermission(string $permission): bool {
    // 1. Check granular permissions via RBAC module
    if (class_exists('App\Modules\Permissions\Services\PermissionService')) {
        return \App\Modules\Permissions\Services\PermissionService::can($permission);
    }

    // 2. Fallback: If no granular permission found, allow access if the user has the 'admin' base role
    // This ensures backward compatibility for existing administrative accounts.
    return (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin');
}

/**
 * CSRF Guard: Verify token for write operations
 */
function verifyCSRF(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') return;
    
    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['csrf_token'] ?? $_POST['_csrf_token'] ?? null;
    if (!$token || $token !== ($_SESSION['csrf_token'] ?? '')) {
        header('Content-Type: application/json');
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Security check failed: Invalid CSRF token.']);
        exit;
    }
}

/**
 * Role Hierarchy Helper: Get the current admin's role slug.
 */
function getCurrentAdminRoleSlug(): string {
    if (empty($_SESSION['user_id'])) {
        return '';
    }
    if (empty($_SESSION['user_role_slug'])) {
        try {
            $db = \App\Core\Database::getConnection();
            $stmt = $db->prepare("
                SELECT r.slug FROM roles r
                JOIN user_roles ur ON r.id = ur.role_id
                WHERE ur.user_id = :uid LIMIT 1
            ");
            $stmt->execute([':uid' => $_SESSION['user_id']]);
            $_SESSION['user_role_slug'] = $stmt->fetchColumn() ?: '';
        } catch (\Throwable $e) {
            error_log("Error fetching user role slug: " . $e->getMessage());
            return '';
        }
    }
    return $_SESSION['user_role_slug'];
}

/**
 * Role Hierarchy Helper: Get target user's role slug.
 */
function getAdminRoleSlug(int $userId): string {
    if ($userId <= 0) {
        return '';
    }
    try {
        $db = \App\Core\Database::getConnection();
        $stmt = $db->prepare("
            SELECT r.slug FROM roles r
            JOIN user_roles ur ON r.id = ur.role_id
            WHERE ur.user_id = :uid LIMIT 1
        ");
        $stmt->execute([':uid' => $userId]);
        return $stmt->fetchColumn() ?: '';
    } catch (\Throwable $e) {
        error_log("Error fetching target user role slug: " . $e->getMessage());
        return '';
    }
}

/**
 * Check if the current user can view a target user.
 */
function canViewUser(int $targetUserId): bool {
    $currentUserId = (int)($_SESSION['user_id'] ?? 0);
    if ($currentUserId === $targetUserId) {
        return true;
    }
    $currentRole = getCurrentAdminRoleSlug();
    $targetRole = getAdminRoleSlug($targetUserId);
    return \App\Policy\AdminPolicy::canViewUser($currentRole, $currentUserId, $targetRole, $targetUserId);
}

/**
 * Check if the current user can manage a target user.
 */
function canManageUser(int $targetUserId): bool {
    $currentUserId = (int)($_SESSION['user_id'] ?? 0);
    if ($currentUserId === $targetUserId) {
        return true;
    }
    $currentRole = getCurrentAdminRoleSlug();
    $targetRole = getAdminRoleSlug($targetUserId);
    return \App\Policy\AdminPolicy::canManageUser($currentRole, $currentUserId, $targetRole, $targetUserId);
}

/**
 * Enforce that the current user can manage a target user.
 * Terminates request with a 403 Forbidden JSON if not authorized.
 */
function requireManageUser(int $targetUserId): void {
    if (!canManageUser($targetUserId)) {
        header('Content-Type: application/json');
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Forbidden: Privilege boundary violation.']);
        exit;
    }
}

// Global Enforcement
requireAdmin();

