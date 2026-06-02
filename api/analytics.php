<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../repositories/AnalyticsRepository.php';

$repo = new AnalyticsRepository();

// Input Validation & Processing
$range = isset($_GET['range']) ? (int)$_GET['range'] : 30;
$endDate = date('Y-m-d H:i:s');
$startDate = date('Y-m-d H:i:s', strtotime("-$range days"));

function fillMissingDates(array $dbData, int $range, array $defaults): array {
    $indexed = [];
    foreach ($dbData as $row) {
        if (isset($row['date'])) {
            $indexed[$row['date']] = $row;
        }
    }
    
    $filled = [];
    for ($i = $range; $i >= 0; $i--) {
        $dateStr = date('Y-m-d', strtotime("-$i days"));
        if (isset($indexed[$dateStr])) {
            $filled[] = $indexed[$dateStr];
        } else {
            $filled[] = array_merge(['date' => $dateStr], $defaults);
        }
    }
    return $filled;
}

try {
    $type = $_GET['type'] ?? 'overview';

    switch ($type) {


        case 'revenue_chart':
            $chartData = $repo->getRevenueChartData($startDate, $endDate);
            $chartData = fillMissingDates($chartData, $range, ['revenue' => 0.00, 'orders' => 0]);
            echo json_encode(['status' => 'success', 'data' => $chartData]);
            break;

        case 'units_chart':
            $chartData = $repo->getUnitsChartData($startDate, $endDate);
            $chartData = fillMissingDates($chartData, $range, ['units' => 0]);
            echo json_encode(['status' => 'success', 'data' => $chartData]);
            break;

        case 'aov_chart':
            $chartData = $repo->getAovChartData($startDate, $endDate);
            $chartData = fillMissingDates($chartData, $range, ['aov' => 0.00]);
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
            $revenueGrowth = $prevRevenue > 0
                ? round((($currRevenue - $prevRevenue) / $prevRevenue) * 100, 1)
                : 0;

            $currOrders = (int)($stats['total_orders'] ?? 0);
            $prevOrders = (int)($prevStats['total_orders'] ?? 0);
            $ordersGrowth = $prevOrders > 0
                ? round((($currOrders - $prevOrders) / $prevOrders) * 100, 1)
                : 0;

            $currUnits = (int)($stats['total_units'] ?? 0);
            $prevUnits = (int)($prevStats['total_units'] ?? 0);
            $unitsGrowth = $prevUnits > 0
                ? round((($currUnits - $prevUnits) / $prevUnits) * 100, 1)
                : 0;

            $currAov = (float)($stats['aov'] ?? 0);
            $prevAov = (float)($prevStats['aov'] ?? 0);
            $aovGrowth = $prevAov > 0
                ? round((($currAov - $prevAov) / $prevAov) * 100, 1)
                : 0;

            echo json_encode([
                'status' => 'success',
                'data'   => [
                    'revenue'        => round($currRevenue, 2),
                    'orders'         => $currOrders,
                    'units'          => $currUnits,
                    'aov'            => round($currAov, 2),
                    'profit'         => round($profit, 2),
                    'revenue_growth' => $revenueGrowth,
                    'orders_growth'  => $ordersGrowth,
                    'units_growth'   => $unitsGrowth,
                    'aov_growth'     => $aovGrowth,
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
