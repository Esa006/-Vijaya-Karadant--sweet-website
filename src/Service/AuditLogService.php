<?php
/**
 * Sweets Website
 * =============================================================
 * File: AuditLogService.php
 * Description: Centralized Audit Logging Service
 * =============================================================
 */

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

class AuditLogService
{
    /**
     * Log an admin action to audit_logs table.
     */
    public static function log(
        string $action,
        string $entityType,
        int    $entityId    = 0,
        ?array $payload     = null,
        int    $performedBy = 0
    ): void {
        try {
            if ($performedBy === 0) {
                $performedBy = (int)($_SESSION['user_id'] ?? 0);
            }

            $ip        = self::getIp();
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

            Database::query(
                "INSERT INTO audit_logs
                    (entity_type, entity_id, action, performed_by, payload, ip_address, user_agent, created_at)
                 VALUES
                    (:entity_type, :entity_id, :action, :performed_by, :payload, :ip, :ua, NOW())",
                [
                    'entity_type'  => $entityType,
                    'entity_id'    => $entityId,
                    'action'       => $action,
                    'performed_by' => $performedBy ?: null,
                    'payload'      => $payload ? json_encode($payload) : null,
                    'ip'           => $ip,
                    'ua'           => substr($userAgent, 0, 255),
                ]
            );
        } catch (\Throwable $e) {
            // Never let audit logging break the main flow
            error_log('[AuditLogService] Failed to write log: ' . $e->getMessage());
        }
    }

    private static function getIp(): string
    {
        foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $key) {
            if (!empty($_SERVER[$key])) {
                return trim(explode(',', $_SERVER[$key])[0]);
            }
        }
        return 'unknown';
    }

    /**
     * Fetch paginated logs with actor name joined.
     */
    public static function getLogsPage(int $page, int $perPage, array $filters = []): array
    {
        $where  = ['1=1'];
        $params = [];

        // Enforce privilege boundary checks: lower roles cannot see Super Admin logs
        $currentUserId = (int)($_SESSION['user_id'] ?? 0);
        $currentRole = '';
        if ($currentUserId > 0) {
            $currentRole = $_SESSION['user_role_slug'] ?? '';
            if (empty($currentRole)) {
                try {
                    $db = Database::getConnection();
                    $stmt = $db->prepare("
                        SELECT r.slug FROM roles r
                        JOIN user_roles ur ON r.id = ur.role_id
                        WHERE ur.user_id = :uid LIMIT 1
                    ");
                    $stmt->execute([':uid' => $currentUserId]);
                    $currentRole = $stmt->fetchColumn() ?: '';
                    $_SESSION['user_role_slug'] = $currentRole;
                } catch (\Throwable $e) {
                    error_log("Error retrieving current role slug in getLogsPage: " . $e->getMessage());
                }
            }
        }

        if ($currentRole !== 'super_admin') {
            // Exclude logs performed by super_admins
            $where[] = "(al.performed_by IS NULL OR al.performed_by NOT IN (
                SELECT ur.user_id FROM user_roles ur 
                JOIN roles r ON ur.role_id = r.id 
                WHERE r.slug = 'super_admin'
            ))";
            // Exclude logs targeting super_admins
            $where[] = "NOT (al.entity_type = 'user' AND al.entity_id IN (
                SELECT ur.user_id FROM user_roles ur 
                JOIN roles r ON ur.role_id = r.id 
                WHERE r.slug = 'super_admin'
            ))";
        }

        if (!empty($filters['action'])) {
            $where[]          = 'LOWER(al.action) = LOWER(:action)';
            $params['action'] = $filters['action'];
        }
        if (!empty($filters['entity_type'])) {
            $where[]                 = 'al.entity_type = :entity_type';
            $params['entity_type']   = $filters['entity_type'];
        }
        if (!empty($filters['actor_id'])) {
            $where[]              = 'al.performed_by = :actor_id';
            $params['actor_id']   = (int)$filters['actor_id'];
        }
        if (!empty($filters['date_from'])) {
            $where[]               = 'DATE(al.created_at) >= :date_from';
            $params['date_from']   = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where[]             = 'DATE(al.created_at) <= :date_to';
            $params['date_to']   = $filters['date_to'];
        }
        if (!empty($filters['search'])) {
            $where[]          = '(al.action LIKE :s1 OR al.entity_type LIKE :s2 OR u.full_name LIKE :s3)';
            $searchVal        = '%' . $filters['search'] . '%';
            $params['s1']     = $searchVal;
            $params['s2']     = $searchVal;
            $params['s3']     = $searchVal;
        }

        $whereStr = implode(' AND ', $where);
        $offset   = ($page - 1) * $perPage;

        $countRow = Database::query(
            "SELECT COUNT(*) as total FROM audit_logs al
             LEFT JOIN users u ON u.id = al.performed_by
             WHERE $whereStr",
            $params
        )->fetch();

        $total = (int)($countRow['total'] ?? 0);

        // Use explicit integer binding for LIMIT / OFFSET
        $pdo  = Database::getConnection();
        $sql  = "SELECT al.*, u.full_name as actor_name, u.email as actor_email
                 FROM audit_logs al
                 LEFT JOIN users u ON u.id = al.performed_by
                 WHERE $whereStr
                 ORDER BY al.created_at DESC
                 LIMIT :limit OFFSET :offset";
        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue(':' . $key, $val);
        }
        $stmt->bindValue(':limit',  $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset,  \PDO::PARAM_INT);
        $stmt->execute();

        return [
            'logs'    => $stmt->fetchAll(),
            'total'   => $total,
            'pages'   => (int)ceil($total / $perPage),
            'current' => $page,
        ];
    }

    /**
     * Get all distinct action types for filter dropdown.
     */
    public static function getDistinctActions(): array
    {
        return Database::query("SELECT DISTINCT action FROM audit_logs ORDER BY action ASC")
                       ->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * Get all distinct entity types for filter dropdown.
     */
    public static function getDistinctEntityTypes(): array
    {
        return Database::query("SELECT DISTINCT entity_type FROM audit_logs ORDER BY entity_type ASC")
                       ->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * Dashboard summary stats.
     */
    public static function getSummaryStats(): array
    {
        $today = Database::query("SELECT COUNT(*) as c FROM audit_logs WHERE DATE(created_at) = CURDATE()")->fetch()['c'] ?? 0;
        $week  = Database::query("SELECT COUNT(*) as c FROM audit_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetch()['c'] ?? 0;
        $total = Database::query("SELECT COUNT(*) as c FROM audit_logs")->fetch()['c'] ?? 0;
        $actors = Database::query("SELECT COUNT(DISTINCT performed_by) as c FROM audit_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetch()['c'] ?? 0;

        return compact('today', 'week', 'total', 'actors');
    }
}
