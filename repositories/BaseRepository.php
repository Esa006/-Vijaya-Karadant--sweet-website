<?php
/**
 * Sweets Website
 * =============================================================
 * File: BaseRepository.php
 * Description: Abstract base class for all repositories
 * Author: Antigravity - Senior Backend Engineer
 * Version: 2.1.0
 * =============================================================
 */

require_once dirname(__DIR__) . '/config/Database.php';

abstract class BaseRepository {
    protected PDO $db;

    public function __construct(?PDO $db = null) {
        $this->db = $db ?? Database::getInstance();
    }

    /**
     * Start a new transaction
     */
    public function beginTransaction(): bool {
        return $this->db->beginTransaction();
    }

    /**
     * Commit current transaction
     */
    public function commit(): bool {
        return $this->db->commit();
    }

    /**
     * Rollback current transaction
     */
    public function rollBack(): bool {
        return $this->db->rollBack();
    }

    /**
     * Check if in transaction
     */
    public function inTransaction(): bool {
        return $this->db->inTransaction();
    }

    /**
     * Fetch all results for a query
     */
    protected function fetchAll(string $sql, array $params = [], array $types = []): array {
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $type = $types[$key] ?? PDO::PARAM_STR;
            $stmt->bindValue($key, $value, $type);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Fetch a single row for a query
     */
    protected function fetchOne(string $sql, array $params = []): ?array {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Get the last inserted ID
     */
    protected function lastInsertId(): string {
        return $this->db->lastInsertId();
    }

    /**
     * Execute a query and return success/failure
     */
    protected function execute(string $sql, array $params = []): bool {
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Execute an insert and return the last insert ID
     */
    protected function executeInsert(string $sql, array $params = []): int {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$this->db->lastInsertId();
    }
}
