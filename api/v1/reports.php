<?php
/**
 * Sweets Website - Dashboard Analytics API v1
 * =============================================================
 * Endpoint: /api/v1/reports.php
 * Methods: GET 
 * Author: Antigravity - Senior Backend Engineer
 * =============================================================
 */

require_once '../../config/config.php';
require_once SERVICES_PATH . '/ReportService.php';

// Hardened Check (Admin Only)
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Forbidden']);
    exit;
}

$reportService = new ReportService();

try {
    $stats = $reportService->getOverviewStats();

    echo json_encode([
        'success' => true,
        'data'    => $stats
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal Server Error'
    ]);
}
