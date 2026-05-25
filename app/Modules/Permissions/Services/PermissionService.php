<?php

namespace App\Modules\Permissions\Services;

use App\Core\Database;
use PDO;

/**
 * Enterprise Permission Service
 * Handles complex RBAC resolution (Roles + Overrides + Cache)
 */
class PermissionService {
    
    private static string $cacheKey = 'user_permissions_map';
    private static string $superAdminSlug = 'super_admin';

    /**
     * Check if current user has permission
     */
    public static function can(string $permissionKey): bool {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }

        $userId = $_SESSION['user_id'];
        $permissions = self::getUserPermissions($userId);

        // 1. Super Admin Bypass
        if (isset($permissions['is_super_admin']) && $permissions['is_super_admin'] === true) {
            return true;
        }

        // 2. Check flattened permission map
        return isset($permissions['keys'][$permissionKey]) && $permissions['keys'][$permissionKey] === true;
    }

    /**
     * Get and cache user permissions
     */
    private static function getUserPermissions(int $userId): array {
        // Return from session cache if available
        if (isset($_SESSION[self::$cacheKey])) {
            return $_SESSION[self::$cacheKey];
        }

        $db = Database::getConnection();

        // 1. Check for Super Admin role
        $roleSql = "SELECT r.slug FROM roles r 
                    JOIN user_roles ur ON r.id = ur.role_id 
                    WHERE ur.user_id = :user_id AND r.slug = :slug AND r.deleted_at IS NULL";
        $isSuperAdmin = Database::query($roleSql, [
            'user_id' => $userId, 
            'slug' => self::$superAdminSlug
        ])->fetch();

        if ($isSuperAdmin) {
            $data = ['is_super_admin' => true, 'keys' => []];
            $_SESSION[self::$cacheKey] = $data;
            return $data;
        }

        // 2. Aggregate Permissions from all roles
        $permSql = "SELECT DISTINCT p.key_name FROM permissions p
                    JOIN role_permissions rp ON p.id = rp.permission_id
                    JOIN user_roles ur ON rp.role_id = ur.role_id
                    WHERE ur.user_id = :user_id AND p.deleted_at IS NULL";
        $rolePerms = Database::query($permSql, ['user_id' => $userId])->fetchAll(PDO::FETCH_COLUMN);

        $permissionMap = [];
        foreach ($rolePerms as $key) {
            $permissionMap[$key] = true;
        }

        // 3. Apply Direct User Overrides (Allow / Deny)
        $overrideSql = "SELECT p.key_name, up.type FROM user_permissions up
                        JOIN permissions p ON up.permission_id = p.id
                        WHERE up.user_id = :user_id AND p.deleted_at IS NULL";
        $overrides = Database::query($overrideSql, ['user_id' => $userId])->fetchAll();

        foreach ($overrides as $override) {
            if ($override['type'] === 'deny') {
                $permissionMap[$override['key_name']] = false;
            } else {
                $permissionMap[$override['key_name']] = true;
            }
        }

        // Filter out false values (Denied)
        $finalKeys = array_filter($permissionMap, fn($val) => $val === true);

        $data = ['is_super_admin' => false, 'keys' => $finalKeys];
        $_SESSION[self::$cacheKey] = $data;

        return $data;
    }

    /**
     * Clear permission cache (call on role/permission change)
     */
    public static function clearCache(): void {
        unset($_SESSION[self::$cacheKey]);
    }
}
