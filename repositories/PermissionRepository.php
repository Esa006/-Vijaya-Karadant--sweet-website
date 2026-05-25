<?php
/**
 * Sweets Website
 * =============================================================
 * File: PermissionRepository.php
 * Description: Data access for RBAC (Roles, Permissions, User Assignments)
 * =============================================================
 */

namespace App\Repositories;

use App\Core\Database;
use PDO;

class PermissionRepository {
    
    /**
     * Get all roles
     */
    public function getAllRoles(): array {
        $sql = "SELECT * FROM roles WHERE deleted_at IS NULL ORDER BY `name` ASC";
        return Database::query($sql)->fetchAll();
    }

    /**
     * Get all permissions
     */
    public function getAllPermissions(): array {
        $sql = "SELECT * FROM permissions WHERE deleted_at IS NULL ORDER BY `name` ASC";
        return Database::query($sql)->fetchAll();
    }

    /**
     * Get permissions for a specific role
     */
    public function getRolePermissions(int $roleId): array {
        $sql = "SELECT p.key_name FROM permissions p
                JOIN role_permissions rp ON p.id = rp.permission_id
                WHERE rp.role_id = :role_id AND p.deleted_at IS NULL";
        return Database::query($sql, ['role_id' => $roleId])->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Sync role permissions
     */
    public function syncRolePermissions(int $roleId, array $permissionKeys): void {
        Database::beginTransaction();
        try {
            // 1. Remove all existing
            Database::query("DELETE FROM role_permissions WHERE role_id = :role_id", ['role_id' => $roleId]);

            // 2. Add new
            if (!empty($permissionKeys)) {
                $placeholders = implode(',', array_fill(0, count($permissionKeys), '?'));
                $sql = "INSERT INTO role_permissions (role_id, permission_id)
                        SELECT ?, id FROM permissions WHERE key_name IN ($placeholders)";
                
                $params = array_merge([$roleId], $permissionKeys);
                Database::query($sql, $params);
            }
            Database::commit();
        } catch (\Exception $e) {
            Database::rollBack();
            throw $e;
        }
    }

    /**
     * Get the role slug of a user
     */
    public function getUserRoleSlug(int $userId): ?string {
        $sql = "SELECT r.slug FROM roles r
                JOIN user_roles ur ON r.id = ur.role_id
                WHERE ur.user_id = :uid LIMIT 1";
        $row = Database::query($sql, ['uid' => $userId])->fetch();
        return $row['slug'] ?? null;
    }

    /**
     * Get users with their roles (hierarchically filtered)
     */
    public function getAdminUsersWithRoles(int $currentUserId, string $currentRoleSlug): array {
        $where = "u.role = 'admin' AND u.deleted_at IS NULL";
        $params = [];

        if ($currentRoleSlug === 'manager') {
            $where .= " AND (r.slug IS NULL OR r.slug IN ('manager', 'editor', 'staff'))";
        } elseif ($currentRoleSlug === 'editor') {
            $where .= " AND (r.slug IS NULL OR r.slug IN ('editor', 'staff'))";
        } elseif ($currentRoleSlug === 'staff') {
            $where .= " AND u.id = :current_user_id";
            $params['current_user_id'] = $currentUserId;
        } elseif ($currentRoleSlug !== 'super_admin') {
            // Failsafe: restrict to self for unknown/lower roles
            $where .= " AND u.id = :current_user_id";
            $params['current_user_id'] = $currentUserId;
        }

        $sql = "SELECT u.id, u.full_name, u.email, u.status, r.name as role_name, r.id as role_id, r.slug as role_slug,
                       (SELECT MAX(created_at) FROM admin_login_activity WHERE admin_id = u.id AND status = 'success') as last_login_at
                FROM users u
                LEFT JOIN user_roles ur ON u.id = ur.user_id
                LEFT JOIN roles r ON ur.role_id = r.id
                WHERE $where";
        return Database::query($sql, $params)->fetchAll();
    }

    /**
     * Assign role to user
     */
    public function assignRole(int $userId, int $roleId): void {
        // Remove existing role (one role per user for now)
        Database::query("DELETE FROM user_roles WHERE user_id = :user_id", ['user_id' => $userId]);
        
        // Assign new
        Database::query("INSERT INTO user_roles (user_id, role_id) VALUES (:user_id, :role_id)", [
            'user_id' => $userId,
            'role_id' => $roleId
        ]);
    }

    /**
     * Create or upgrade a user to an Admin
     */
    public function createOrUpgradeAdmin(array $data): int {
        Database::beginTransaction();
        try {
            $email = trim(strtolower($data['email']));
            $name = trim($data['name']);
            $roleId = (int)$data['role_id'];
            
            // Check if user exists
            $stmt = Database::query("SELECT id FROM users WHERE email = :email", ['email' => $email]);
            $user = $stmt->fetch();
            
            $userId = 0;
            if ($user) {
                // User exists, upgrade to admin
                $userId = $user['id'];
                
                $updateParams = ['id' => $userId];
                $updateSql = "UPDATE users SET role = 'admin', full_name = :name";
                
                // If password is provided, update it
                if (!empty($data['password'])) {
                    $updateSql .= ", password = :pass";
                    $updateParams['pass'] = password_hash($data['password'], PASSWORD_DEFAULT);
                }
                
                $updateSql .= " WHERE id = :id";
                $updateParams['name'] = $name;
                
                Database::query($updateSql, $updateParams);
            } else {
                // New user
                // Check if password was provided, if not, generate a random one (though it should be required for new users from the UI)
                $password = !empty($data['password']) ? $data['password'] : bin2hex(random_bytes(8));
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                $status = !empty($data['send_invite']) ? 'pending_invite' : 'Active';
                Database::query("INSERT INTO users (full_name, email, password, role, status) VALUES (:name, :email, :pass, 'admin', :status)", [
                    'name' => $name,
                    'email' => $email,
                    'pass' => $hashedPassword,
                    'status' => $status
                ]);
                
                $userId = Database::getConnection()->lastInsertId();
            }
            
            // Assign the RBAC role
            $this->assignRole((int)$userId, $roleId);
            
            Database::commit();
            return (int)$userId;
        } catch (\Exception $e) {
            Database::rollBack();
            throw $e;
        }
    }
}
