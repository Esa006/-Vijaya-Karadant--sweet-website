<?php

namespace App\Core;

/**
 * Base Controller
 * Shared functionality for all modules
 */
abstract class BaseController {
    
    /**
     * Return JSON response
     */
    protected function json(array $data, int $statusCode = 200): void {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }

    /**
     * Success JSON response
     */
    protected function success(string $message, $data = [], int $statusCode = 200): void {
        $this->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }

    /**
     * Error JSON response
     */
    protected function error(string $message, int $statusCode = 400): void {
        $this->json([
            'status' => 'error',
            'message' => $message
        ], $statusCode);
    }

    /**
     * Sanitize input
     */
    protected function input(string $key, $default = null) {
        $value = $_REQUEST[$key] ?? $default;
        return is_string($value) ? htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8') : $value;
    }

    /**
     * Redirect to URL
     */
    protected function redirect(string $url): void {
        header("Location: $url");
        exit;
    }
}
