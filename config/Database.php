<?php
/**
 * Sweets Website
 * =============================================================
 * File: Database.php
 * Description: Singleton PDO database connection class
 * Author: Sweets Website Team
 * Version: 1.0.0
 * =============================================================
 */

class Database {
    private static ?PDO $instance = null;
    private static bool $connectionFailed = false;

    /**
     * Get PDO singleton instance
     */
    public static function getInstance(): PDO {
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
                $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ];
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                self::$connectionFailed = true;
                throw $e;
            }
        }
        return self::$instance;
    }

    // Prevent direct instantiation
    private function __construct() {}
    private function __clone() {}
}
