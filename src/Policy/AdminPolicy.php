<?php
/**
 * Sweets Website
 * =============================================================
 * File: AdminPolicy.php
 * Description: Security Policy for Admin Permissions & Role Hierarchy
 * =============================================================
 */

declare(strict_types=1);

namespace App\Policy;

class AdminPolicy {
    public const ROLE_PRIORITY = [
        'super_admin' => 100,
        'manager' => 70,
        'editor' => 40,
        'staff' => 10
    ];

    /**
     * Get priority score for a role slug
     */
    public static function getPriority(string $roleSlug): int {
        return self::ROLE_PRIORITY[$roleSlug] ?? 0;
    }

    /**
     * Check if actor (role, id) can view the target user (role, id)
     */
    public static function canViewUser(string $actorRole, int $actorId, string $targetRole, int $targetId): bool {
        if ($actorId === $targetId) {
            return true;
        }

        if ($actorRole === 'staff') {
            return false;
        }

        return self::getPriority($actorRole) >= self::getPriority($targetRole);
    }

    /**
     * Check if actor (role, id) can manage the target user (role, id)
     * e.g., suspend, delete, edit, change role.
     */
    public static function canManageUser(string $actorRole, int $actorId, string $targetRole, int $targetId): bool {
        if ($actorId === $targetId) {
            return true;
        }

        if ($actorRole === 'staff') {
            return false;
        }

        // Actors can only manage users with strictly lower priority roles!
        return self::getPriority($actorRole) > self::getPriority($targetRole);
    }
}
