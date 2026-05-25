<?php
/**
 * Sweets Website - Streaming CSV Export API v1
 * =============================================================
 * Endpoint: /api/v1/export.php
 * Methods: GET 
 * Author: Antigravity - Senior Backend Engineer
 * =============================================================
 */

require_once '../../config/config.php';
require_once SERVICES_PATH . '/ReportService.php';
require_once SERVICES_PATH . '/AuditService.php';

// Hardened Check (Admin Only)
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Forbidden']);
    exit;
}

$reportService = new ReportService();
$audit         = new AuditService();

try {
    $filters = [
        'start_date' => $_GET['start_date'] ?? null,
        'end_date'   => $_GET['end_date'] ?? null,
        'status'     => $_GET['status'] ?? null
    ];

    // 1. Audit Log: Start Export
    $audit->log('order_export', 0, 'export_start', $_SESSION['user_id'], $filters);

    // 2. Fetch Data (Optimized for streaming)
    $orders = $reportService->getDetailedSalesReport($filters);

    // 3. Set Headers for Download
    $filename = "orders_export_" . date('Y-m-d_H-i') . ".csv";
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $filename);

    // 4. Stream Output
    $output = fopen('php://output', 'w');

    // Header Row
    fputcsv($output, ['Order Number', 'Customer', 'Email', 'Amount', 'Status', 'Date', 'Transaction ID']);

    foreach ($orders as $order) {
        fputcsv($output, [
            $order['order_number'],
            $order['customer_name'] ?? 'Guest',
            $order['customer_email'] ?? 'N/A',
            $order['total_amount'],
            strtoupper($order['status']),
            $order['created_at'],
            $order['transaction_id'] ?? 'N/A'
        ]);
    }

    fclose($output);
    exit;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Export failed: ' . $e->getMessage()]);
}
