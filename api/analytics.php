<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../repositories/AnalyticsRepository.php';

$repo = new AnalyticsRepository();

// Input Validation & Processing
$range = isset($_GET['range']) ? (int)$_GET['range'] : 30;
$endDate = date('Y-m-d H:i:s');
$startDate = date('Y-m-d H:i:s', strtotime("-$range days"));

try {
    $type = $_GET['type'] ?? 'overview';

    switch ($type) {


        case 'revenue_chart':
            $chartData = $repo->getRevenueChartData($startDate, $endDate);
            echo json_encode(['status' => 'success', 'data' => $chartData]);
            break;

        case 'units_chart':
            $chartData = $repo->getUnitsChartData($startDate, $endDate);
            echo json_encode(['status' => 'success', 'data' => $chartData]);
            break;

        case 'aov_chart':
            $chartData = $repo->getAovChartData($startDate, $endDate);
            echo json_encode(['status' => 'success', 'data' => $chartData]);
            break;

        case 'category_chart':
            $chartData = $repo->getCategoryData($startDate, $endDate);
            echo json_encode(['status' => 'success', 'data' => $chartData]);
            break;

        case 'top_products':
            $products = $repo->getTopProducts($startDate, $endDate, 8);
            echo json_encode([
                'status' => 'success',
                'data'   => $products
            ]);
            break;

        case 'overview':
            $stats  = $repo->getOverviewStats($startDate, $endDate);
            $profit = $repo->getProfit($startDate, $endDate);

            // Real growth: compare current period vs same-length previous period
            $prevEnd   = date('Y-m-d H:i:s', strtotime("-$range days"));
            $prevStart = date('Y-m-d H:i:s', strtotime("-" . ($range * 2) . " days"));
            $prevStats = $repo->getOverviewStats($prevStart, $prevEnd);

            $currRevenue = (float)($stats['net_revenue'] ?? 0);
            $prevRevenue = (float)($prevStats['net_revenue'] ?? 0);
            $growth = $prevRevenue > 0
                ? round((($currRevenue - $prevRevenue) / $prevRevenue) * 100, 1)
                : 0;

            echo json_encode([
                'status' => 'success',
                'data'   => [
                    'revenue' => round($currRevenue, 2),
                    'orders'  => (int)($stats['total_orders'] ?? 0),
                    'units'   => (int)($stats['total_units'] ?? 0),
                    'aov'     => round((float)($stats['aov'] ?? 0), 2),
                    'profit'  => round($profit, 2),
                    'growth'  => $growth,
                ]
            ]);
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid endpoint type']);

    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
