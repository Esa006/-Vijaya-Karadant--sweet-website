<?php

namespace App\Core;

use PDO;
use PDOException;

/**
 * Enterprise Database Wrapper
 * Singleton pattern for PDO connection
 */
class Database {
    private static ?PDO $instance = null;
    private static bool $connectionFailed = false;

    /**
     * Get PDO connection
     */
    public static function getConnection(): PDO {
        if (self::$connectionFailed) {
            throw new PDOException("Database connection previously failed in this request.");
        }

        if (self::$instance !== null) {
            try {
                // Ping connection to ensure it is still active
                self::$instance->query('SELECT 1');
            } catch (PDOException $e) {
                // Connection has gone away, clear instance to force reconnect
                self::$instance = null;
            }
        }

        if (self::$instance === null) {
            try {
                // Accessing constants from config/config.php or environment
                $host = DB_HOST;
                $db   = DB_NAME;
                $user = DB_USER;
                $pass = DB_PASS;
                $charset = 'utf8mb4';

                $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ];

                self::$instance = new PDO($dsn, $user, $pass, $options);
            } catch (PDOException $e) {
                self::$connectionFailed = true;
                error_log("Database Connection Error: " . $e->getMessage());
                throw $e;
            }
        }
        return self::$instance;
    }

    /**
     * Run a query with prepared statements
     */
    public static function query(string $sql, array $params = []) {
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Start transaction
     */
    public static function beginTransaction(): bool {
        return self::getConnection()->beginTransaction();
    }

    /**
     * Commit transaction
     */
    public static function commit(): bool {
        return self::getConnection()->commit();
    }

    /**
     * Rollback transaction
     */
    public static function rollBack(): bool {
        return self::getConnection()->rollBack();
    }
}
