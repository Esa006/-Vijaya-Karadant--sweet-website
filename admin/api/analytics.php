<?php
/**
 * Sweets Website
 * =============================================================
 * File: admin/api/analytics.php
 * Description: Dynamic fetch endpoint for Reports & Analytics
 * Author: Antigravity - Senior Backend Engineer
 * Version: 1.0.0
 * =============================================================
 */

require_once '../../config/config.php';
require_once '../includes/auth.php';
require_once SERVICES_PATH . '/ReportService.php';

header('Content-Type: application/json');

// Get parameters
$period = $_GET['period'] ?? 'weekly';
$start  = $_GET['start'] ?? null;
$end    = $_GET['end'] ?? null;

try {
    $service = new ReportService();
    $data = $service->getDashboardData($period, $start, $end);
    
    echo json_encode([
        'status' => 'success',
        'data' => $data
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
