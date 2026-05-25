<?php
/**
 * Sweets Website - Analytics Export
 * =============================================================
 * File: api/export-reports.php
 * Generates CSV exports for analytics data
 * =============================================================
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../repositories/AnalyticsRepository.php';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=sales_report_' . date('Y-m-d') . '.csv');

$range = isset($_GET['range']) ? (int)$_GET['range'] : 30;
$endDate = date('Y-m-d H:i:s');
$startDate = date('Y-m-d H:i:s', strtotime("-$range days"));

$repo = new AnalyticsRepository();
$data = $repo->getTopProducts($startDate, $endDate, 100);

$output = fopen('php://output', 'w');

// Add Header
fputcsv($output, ['Rank', 'Product Name', 'SKU', 'Units Sold', 'Total Revenue (INR)']);

// Add Data
foreach ($data as $index => $row) {
    fputcsv($output, [
        $index + 1,
        $row['name'],
        $row['sku'],
        $row['total_sold'],
        $row['product_revenue']
    ]);
}

fclose($output);
exit;
