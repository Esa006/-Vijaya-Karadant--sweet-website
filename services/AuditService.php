<?php
/**
 * Sweets Website
 * =============================================================
 * File: AuditService.php
 * Description: Business logic for Security Audit Trails
 * Author: Antigravity - Senior Backend Engineer
 * Version: 2.1.0
 * =============================================================
 */

require_once REPOS_PATH . '/AuditRepository.php';

class AuditService {
    private AuditRepository $repo;

    public function __construct() {
        $this->repo = new AuditRepository();
    }

    /**
     * Log a security or administrative event
     */
    public function log(string $entityType, int $entityId, string $action, ?int $userId = null, array $payload = []): bool {
        return $this->repo->log([
            'entity_type'  => $entityType,
            'entity_id'    => $entityId,
            'action'       => $action,
            'performed_by' => $userId,
            'payload'      => $payload
        ]);
    }

    /**
     * Get history for a specific entity (Order, Product, etc.)
     */
    public function getHistory(string $type, int $id): array {
        return $this->repo->getLogsByEntity($type, $id);
    }
}
