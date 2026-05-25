<?php
/**
 * Sweets Website
 * =============================================================
 * File: permissions.php
 * Description: API for RBAC Management
 * =============================================================
 */

require_once '../../config/config.php';
require_once '../includes/auth.php';
require_once REPOS_PATH . '/PermissionRepository.php';
require_once ROOT_PATH . '/src/Service/AuditLogService.php';

use App\Repositories\PermissionRepository;
use App\Services\AuditLogService;

header('Content-Type: application/json');

// Ensure only super_admin or users with manage_permissions can access
if (!hasPermission('permissions:manage')) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Forbidden']);
    exit;
}

$repo = new PermissionRepository();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $action = $_GET['action'] ?? '';
    
    if ($action === 'get_role_permissions') {
        $roleId = (int)($_GET['role_id'] ?? 0);
        if ($roleId > 0) {
            // Restrict: Cannot view role permissions of roles equal or higher priority than own role
            $targetRoleRow = \App\Core\Database::query("SELECT slug FROM roles WHERE id = :rid", ['rid' => $roleId])->fetch();
            $targetRoleSlug = $targetRoleRow['slug'] ?? '';
            $currentRole = getCurrentAdminRoleSlug();
            if (\App\Policy\AdminPolicy::getPriority($currentRole) < \App\Policy\AdminPolicy::getPriority($targetRoleSlug)) {
                http_response_code(403);
                echo json_encode(['status' => 'error', 'message' => 'Forbidden: Privilege boundary violation.']);
                exit;
            }
            $perms = $repo->getRolePermissions($roleId);
            echo json_encode(['status' => 'success', 'permissions' => $perms]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid role ID']);
        }
        exit;
    }
}

if ($method === 'POST') {
    verifyCSRF();
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';

    if ($action === 'sync_role_permissions') {
        $roleId = (int)($input['role_id'] ?? 0);
        $permissions = $input['permissions'] ?? [];
        
        // Restrict: Cannot modify permissions of roles equal or higher priority than own role
        $targetRoleRow = \App\Core\Database::query("SELECT slug FROM roles WHERE id = :rid", ['rid' => $roleId])->fetch();
        $targetRoleSlug = $targetRoleRow['slug'] ?? '';
        $currentRole = getCurrentAdminRoleSlug();
        if (\App\Policy\AdminPolicy::getPriority($currentRole) <= \App\Policy\AdminPolicy::getPriority($targetRoleSlug)) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Forbidden: Privilege boundary violation. You cannot sync permissions for this role.']);
            exit;
        }
        
        try {
            $repo->syncRolePermissions($roleId, $permissions);
            if (class_exists('App\Modules\Permissions\Services\PermissionService')) {
                \App\Modules\Permissions\Services\PermissionService::clearCache();
            }
            echo json_encode(['status' => 'success', 'message' => 'Permissions synced']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    if ($action === 'assign_role') {
        $userId = (int)($input['user_id'] ?? 0);
        $roleId = (int)($input['role_id'] ?? 0);
        
        // Restrict: Check manage rights on target user
        requireManageUser($userId);
        
        // Restrict: Cannot assign a role equal or higher to one's own role
        $targetRoleRow = \App\Core\Database::query("SELECT slug FROM roles WHERE id = :rid", ['rid' => $roleId])->fetch();
        $targetRoleSlug = $targetRoleRow['slug'] ?? '';
        $currentRole = getCurrentAdminRoleSlug();
        if (\App\Policy\AdminPolicy::getPriority($currentRole) <= \App\Policy\AdminPolicy::getPriority($targetRoleSlug)) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Forbidden: Cannot assign a role equal to or higher than your own.']);
            exit;
        }
        
        try {
            $repo->assignRole($userId, $roleId);
            if (class_exists('App\Modules\Permissions\Services\PermissionService')) {
                \App\Modules\Permissions\Services\PermissionService::clearCache();
            }
            AuditLogService::log('role_assigned', 'user', $userId, ['role_id' => $roleId]);
            
            // Send email notification on role assignment (position change)
            $userRow = \App\Core\Database::query("SELECT full_name, email FROM users WHERE id = :id", ['id' => $userId])->fetch();
            $roleRow = \App\Core\Database::query("SELECT name FROM roles WHERE id = :rid", ['rid' => $roleId])->fetch();
            if ($userRow && $roleRow) {
                $email = $userRow['email'];
                $name = $userRow['full_name'];
                $roleName = $roleRow['name'];
                $subject = "Admin Profile Update: Role Changed";
                $body = "<h2>Hello " . htmlspecialchars($name) . ",</h2>
                         <p>Your administrative role on the Sweets Website portal has been updated.</p>
                         <p>Your new assigned role is: <strong>" . htmlspecialchars($roleName) . "</strong></p>
                         <p>If you did not authorize this change or have questions, please contact the lead system administrator.</p>
                         <p>Best regards,<br>Security Operations Team</p>";
                if (class_exists('App\Services\EmailService')) {
                    \App\Services\EmailService::send($email, $subject, $body);
                }
            }
            
            echo json_encode(['status' => 'success', 'message' => 'Role assigned']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    if ($action === 'create_admin') {
        $name = trim($input['name'] ?? '');
        $email = trim($input['email'] ?? '');
        $sendInvite = !empty($input['send_invite']);
        $roleId = (int)($input['role_id'] ?? 0);

        if (empty($name) || empty($email) || $roleId <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Please provide Name, Email, and select a Role.']);
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid email format.']);
            exit;
        }

        // Restrict: Cannot assign a role equal or higher to one's own role
        $targetRoleRow = \App\Core\Database::query("SELECT slug FROM roles WHERE id = :rid", ['rid' => $roleId])->fetch();
        $targetRoleSlug = $targetRoleRow['slug'] ?? '';
        $currentRole = getCurrentAdminRoleSlug();
        if (\App\Policy\AdminPolicy::getPriority($currentRole) <= \App\Policy\AdminPolicy::getPriority($targetRoleSlug)) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Forbidden: Cannot create an admin with a role equal to or higher than your own.']);
            exit;
        }

        try {
            $data = [
                'name' => $name,
                'email' => $email,
                'send_invite' => $sendInvite,
                'role_id' => $roleId
            ];
            $userId = $repo->createOrUpgradeAdmin($data);
            
            AuditLogService::log('admin_created', 'user', $userId, ['email' => $email, 'role_id' => $roleId, 'invite_sent' => $sendInvite]);
            if ($sendInvite) {
                AuditLogService::log('invite_sent', 'admin_invite', $userId, ['email' => $email]);
                // Generate secure token
                $token = bin2hex(random_bytes(32));
                $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));
                $createdBy = $_SESSION['user_id'] ?? 0;
                
                \App\Core\Database::query("INSERT INTO admin_invites (email, role_id, token, expires_at, created_by) VALUES (:email, :role, :token, :exp, :creator)", [
                    'email' => $email,
                    'role' => $roleId,
                    'token' => $token,
                    'exp' => $expiresAt,
                    'creator' => $createdBy
                ]);
                
                // Send email
                $resetLink = BASE_URL . "admin/setup-password.php?token=" . $token;
                if (class_exists('App\Services\EmailService')) {
                    $subject = "Admin Invitation - Setup Your Password";
                    $body = "<h2>Hello " . htmlspecialchars($name) . ",</h2>
                             <p>You have been invited to manage the Sweets Website portal.</p>
                             <p>Please click the link below to set up your password and access the dashboard:</p>
                             <p><a href='$resetLink' style='display:inline-block;padding:10px 20px;background:#7a1f1f;color:#fff;text-decoration:none;border-radius:5px;'>Set Up Password</a></p>
                             <p>Or copy and paste this link in your browser:</p>
                             <p><small>$resetLink</small></p>
                             <p><em>This link expires in 1 hour.</em></p>";
                    \App\Services\EmailService::send($email, $subject, $body);
                }
            }
            
            echo json_encode(['status' => 'success', 'message' => 'Admin successfully created. ' . ($sendInvite ? 'Invite email sent.' : '')]);
        } catch (\PDOException $e) {
            // Check for duplicate email error specifically if necessary
            // But since we are doing createOrUpgrade, it shouldn't hit duplicate email unless there's a race condition
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    // ===== SUSPEND =====
    if ($action === 'suspend') {
        $userId = (int)($input['user_id'] ?? 0);
        requireManageUser($userId);
        if ($userId <= 0 || $userId === (int)($_SESSION['user_id'] ?? 0)) {
            echo json_encode(['status' => 'error', 'message' => 'Cannot suspend this account.']);
            exit;
        }
        try {
            \App\Core\Database::query("UPDATE users SET status = 'suspended' WHERE id = :id AND role = 'admin'", ['id' => $userId]);
            AuditLogService::log('admin_suspended', 'user', $userId, ['suspended_by' => $_SESSION['user_id'] ?? null]);
            echo json_encode(['status' => 'success', 'message' => 'Admin suspended successfully.']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    // ===== REACTIVATE =====
    if ($action === 'reactivate') {
        $userId = (int)($input['user_id'] ?? 0);
        requireManageUser($userId);
        if ($userId <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid user.']);
            exit;
        }
        try {
            \App\Core\Database::query("UPDATE users SET status = 'Active' WHERE id = :id AND role = 'admin'", ['id' => $userId]);
            AuditLogService::log('admin_reactivated', 'user', $userId);
            echo json_encode(['status' => 'success', 'message' => 'Admin reactivated successfully.']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    // ===== REMOVE ACCESS =====
    if ($action === 'remove_access') {
        $userId = (int)($input['user_id'] ?? 0);
        requireManageUser($userId);
        if ($userId <= 0 || $userId === (int)($_SESSION['user_id'] ?? 0)) {
            echo json_encode(['status' => 'error', 'message' => 'Cannot remove your own access.']);
            exit;
        }
        try {
            \App\Core\Database::beginTransaction();
            // Revoke admin role, block account
            \App\Core\Database::query("UPDATE users SET role = 'customer', status = 'Blocked' WHERE id = :id", ['id' => $userId]);
            // Remove all RBAC role assignments
            \App\Core\Database::query("DELETE FROM user_roles WHERE user_id = :id", ['id' => $userId]);
            \App\Core\Database::commit();
            AuditLogService::log('admin_removed', 'user', $userId, ['removed_by' => $_SESSION['user_id'] ?? null]);
            echo json_encode(['status' => 'success', 'message' => 'Admin access removed and account blocked.']);
        } catch (Exception $e) {
            \App\Core\Database::rollBack();
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    // ===== RESEND INVITE =====
    if ($action === 'resend_invite') {
        $userId = (int)($input['user_id'] ?? 0);
        requireManageUser($userId);
        if ($userId <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid user.']);
            exit;
        }
        try {
            $userRow = \App\Core\Database::query("SELECT full_name, email FROM users WHERE id = :id", ['id' => $userId])->fetch();
            if (!$userRow) {
                echo json_encode(['status' => 'error', 'message' => 'User not found.']);
                exit;
            }
            // Invalidate old tokens
            \App\Core\Database::query("UPDATE admin_invites SET used_at = NOW() WHERE email = :email AND used_at IS NULL", ['email' => $userRow['email']]);
            // New token
            $token = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));
            $createdBy = $_SESSION['user_id'] ?? 0;
            $roleStmt = \App\Core\Database::query("SELECT role_id FROM user_roles WHERE user_id = :id LIMIT 1", ['id' => $userId]);
            $roleRow = $roleStmt->fetch();
            \App\Core\Database::query("INSERT INTO admin_invites (email, role_id, token, expires_at, created_by) VALUES (:email, :role, :token, :exp, :creator)", [
                'email' => $userRow['email'],
                'role'  => $roleRow['role_id'] ?? 1,
                'token' => $token,
                'exp'   => $expiresAt,
                'creator' => $createdBy
            ]);
            // Send email
            $resetLink = BASE_URL . "admin/setup-password.php?token=" . $token;
            if (class_exists('App\Services\EmailService')) {
                $body = "<h2>Hello " . htmlspecialchars($userRow['full_name']) . ",</h2>
                         <p>Your admin invitation has been resent. Click below to set up your password:</p>
                         <p><a href='$resetLink' style='display:inline-block;padding:10px 20px;background:#7a1f1f;color:#fff;text-decoration:none;border-radius:5px;'>Set Up Password</a></p>
                         <p><small>$resetLink</small></p>
                         <p><em>This link expires in 1 hour.</em></p>";
                \App\Services\EmailService::send($userRow['email'], "Admin Invitation Resent", $body);
            }
            echo json_encode(['status' => 'success', 'message' => 'Invite email resent successfully.']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }
}

echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
