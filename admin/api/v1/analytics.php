<?php
/**
 * Sweets Website
 * =============================================================
 * File: analytics.php
 * Description: Production-grade Single-Endpoint Analytics API
 * Author: Antigravity - Senior Full-Stack Architect
 * Version: 3.0.0
 * =============================================================
 */

require_once '../../../config/config.php';
require_once '../../includes/auth.php';
require_once SERVICES_PATH . '/ReportService.php';

// 1. Headers & Security
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

try {
    // 2. Initialize Service
    $service = new ReportService();
    $range = $_GET['range'] ?? 'weekly';

    // 3. Fetch Aggregate Data
    $data = $service->getFullAnalytics($range);

    // 4. Response
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'data' => $data,
        'execution_time' => (microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]) * 1000 . 'ms'
    ]);

} catch (Exception $e) {
    error_log('[AnalyticsAPI] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Internal Server Error',
        'debug' => (defined('DEBUG') && DEBUG) ? $e->getMessage() : null
    ]);
}
