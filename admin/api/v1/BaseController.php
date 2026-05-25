<?php
/**
 * Sweets Website
 * =============================================================
 * File: BaseController.php
 * Description: Abstract base controller for Admin API v1
 * Author: Antigravity - Senior Backend Engineer
 * Version: 2.1.0
 * =============================================================
 */

require_once dirname(dirname(dirname(__DIR__))) . '/config/config.php';

abstract class BaseController {

    public function __construct() {
        $this->enforceAuth();
        $this->enforceJsonHeader();
    }

    /**
     * Enforce Admin Authentication
     */
    protected function enforceAuth(): void {
        $role = $_SESSION['user_role'] ?? $_SESSION['role'] ?? '';
        if (!isset($_SESSION['user_id']) || $role !== 'admin') {
            $this->error('unauthorized', 'Admin authentication required.', 401);
        }
    }

    /**
     * Set JSON Content-Type
     */
    protected function enforceJsonHeader(): void {
        header('Content-Type: application/json');
    }

    /**
     * Verify CSRF Token for Write Operations
     */
    protected function verifyCsrf(): void {
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['_csrf_token'] ?? null;
        if (!$token || $token !== ($_SESSION['csrf_token'] ?? '')) {
            $this->error('forbidden', 'Invalid or missing CSRF token.', 403);
        }
    }

    /**
     * Send success response
     */
    protected function success(array $data = [], string $message = 'Operation successful.'): void {
        echo json_encode([
            'status'  => 'success',
            'message' => $message,
            'data'    => $data
        ]);
        exit;
    }

    /**
     * Send error response
     */
    protected function error(string $code, string $message, int $httpStatus = 400): void {
        http_response_code($httpStatus);
        echo json_encode([
            'status'  => 'error',
            'code'    => $code,
            'message' => $message
        ]);
        exit;
    }

    /**
     * Get JSON input from request body
     */
    protected function getJsonInput(): array {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        return $data ?: [];
    }
}
