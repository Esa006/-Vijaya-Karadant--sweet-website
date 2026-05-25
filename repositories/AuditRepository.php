<?php
/**
 * Sweets Website
 * =============================================================
 * File: AuditRepository.php
 * Description: Data access layer for Audit Logs and Compliance
 * Author: Antigravity - Senior Backend Engineer
 * Version: 2.1.0
 * =============================================================
 */

require_once 'BaseRepository.php';

class AuditRepository extends BaseRepository {

    /**
     * Create log entry (Immutable)
     */
    public function log(array $data): bool {
        $sql = "INSERT INTO audit_logs (entity_type, entity_id, action, performed_by, payload, ip_address) 
                VALUES (:type, :eid, :action, :uid, :payload, :ip)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'type'    => $data['entity_type'],
            'eid'     => $data['entity_id'],
            'action'  => $data['action'],
            'uid'     => $data['performed_by'] ?? null,
            'payload' => $data['payload'] ? json_encode($data['payload']) : null,
            'ip'      => $data['ip_address'] ?? ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0')
        ]);
    }

    /**
     * Get logs for a specific entity
     */
    public function getLogsByEntity(string $type, int $id): array {
        $sql = "SELECT a.*, u.full_name as admin_name 
                FROM audit_logs a 
                LEFT JOIN users u ON a.performed_by = u.id 
                WHERE a.entity_type = :type AND a.entity_id = :id 
                ORDER BY a.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['type' => $type, 'id' => $id]);
        return $stmt->fetchAll();
    }

    /**
     * Get recent logs for general dashboard
     */
    public function getRecentLogs(int $limit = 50): array {
        $sql = "SELECT a.*, u.full_name as admin_name 
                FROM audit_logs a 
                LEFT JOIN users u ON a.performed_by = u.id 
                ORDER BY a.created_at DESC LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
