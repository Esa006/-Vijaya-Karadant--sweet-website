<?php
declare(strict_types=1);

/**
 * Sweets Website
 * =============================================================
 * File: db.php
 * Description: PDO database connection helper for admin shipment module
 * =============================================================
 */

require_once dirname(__DIR__) . '/config/config.php';

function getPDOConnection(): PDO {
    static $pdo = null;
    static $connectionFailed = false;

    if ($connectionFailed) {
        throw new PDOException("Database connection previously failed in this request.");
    }

    if ($pdo instanceof PDO) {
        try {
            // Ping connection to ensure it is still active
            $pdo->query('SELECT 1');
            return $pdo;
        } catch (PDOException $e) {
            // Connection has gone away, clear variable to force reconnect
            $pdo = null;
        }
    }

    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
        $connectionFailed = true;
        throw $e;
    }

    return $pdo;
}

function tableExists(PDO $pdo, string $table): bool {
    $stmt = $pdo->prepare(
        'SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = :schema AND TABLE_NAME = :table LIMIT 1'
    );
    $stmt->execute([
        ':schema' => DB_NAME,
        ':table' => $table
    ]);

    return (bool)$stmt->fetchColumn();
}

function columnExists(PDO $pdo, string $table, string $column): bool {
    $stmt = $pdo->prepare(
        'SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = :schema AND TABLE_NAME = :table AND COLUMN_NAME = :column LIMIT 1'
    );
    $stmt->execute([
        ':schema' => DB_NAME,
        ':table' => $table,
        ':column' => $column
    ]);

    return (bool)$stmt->fetchColumn();
}

function ensureShipmentTrackingSchema(PDO $pdo): void {
    if (!tableExists($pdo, 'orders')) {
        throw new RuntimeException('Orders table not found.');
    }

    if (!tableExists($pdo, 'shipments')) {
        throw new RuntimeException('Shipments table not found.');
    }

    $alterParts = [];

    if (!columnExists($pdo, 'shipments', 'destination')) {
        $alterParts[] = 'ADD COLUMN destination VARCHAR(255) NULL AFTER order_id';
    }

    if (!columnExists($pdo, 'shipments', 'status')) {
        $alterParts[] = "ADD COLUMN status ENUM('pending','in_transit','delivered') NOT NULL DEFAULT 'pending'";
    }

    if (!columnExists($pdo, 'shipments', 'updated_at')) {
        $alterParts[] = 'ADD COLUMN updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP';
    }

    if (!empty($alterParts)) {
        $pdo->exec('ALTER TABLE shipments ' . implode(', ', $alterParts));
    }
}
