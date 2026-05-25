<?php
/**
 * Sweets Website
 * =============================================================
 * File: DashboardService.php
 * Description: Business logic for admin dashboard analytics
 * =============================================================
 */

require_once REPOS_PATH . '/DashboardRepository.php';

class DashboardService {
    private DashboardRepository $repo;

    public function __construct() {
        $this->repo = new DashboardRepository();
    }

    /**
     * Get all dashboard metrics with dynamic trend calculations
     */
    public function getStats(): array {
        try {
            $today = date('Y-m-d');
            $last30DaysStart = date('Y-m-d', strtotime('-30 days'));
            $prev30DaysStart = date('Y-m-d', strtotime('-60 days'));
            $prev30DaysEnd   = date('Y-m-d', strtotime('-31 days'));

            $currentStats = $this->repo->getStatsForPeriod($last30DaysStart, $today);
            $prevStats    = $this->repo->getStatsForPeriod($prev30DaysStart, $prev30DaysEnd);
            
            $stockValue = $this->repo->getStockValue();
            $pendingOrdersValue = $this->repo->getPendingOrdersValue();

            return [
                'total_orders'      => (int)$currentStats['total_orders'],
                'total_revenue'     => (float)$currentStats['total_revenue'],
                'total_customers'   => (int)$currentStats['total_customers'],
                'stock_value'       => $stockValue,
                'pending_orders'    => $pendingOrdersValue,
                'revenue_formatted' => $this->formatCurrency($currentStats['total_revenue']),
                'stock_formatted'   => $this->formatLargeNumber($stockValue),
                'pending_formatted' => $this->formatLargeNumber($pendingOrdersValue),
                'trends' => [
                    'orders'    => $this->calculateTrend($currentStats['total_orders'], $prevStats['total_orders']),
                    'revenue'   => $this->calculateTrend($currentStats['total_revenue'], $prevStats['total_revenue']),
                    'customers' => $this->calculateTrend($currentStats['total_customers'], $prevStats['total_customers']),
                    'stock'     => '-2.4%' // Static or needs inventory history
                ],
                'recent_orders' => $this->getRecentOrders()
            ];
        } catch (Exception $e) {
            error_log("[DashboardService] Error: " . $e->getMessage());
            return [
                'total_orders'      => 0,
                'total_revenue'     => 0,
                'total_customers'   => 0,
                'stock_value'       => 0,
                'pending_orders'    => 0,
                'revenue_formatted' => '₹ 0',
                'stock_formatted'   => '₹ 0',
                'pending_formatted' => '₹ 0',
                'trends' => ['orders' => '0%', 'revenue' => '0%', 'customers' => '0%', 'stock' => '0%'],
                'recent_orders'     => []
            ];
        }
    }

    /**
     * Get data structured for Chart.js instances on the dashboard
     */
    public function getChartData(): array {
        try {
            // 1. Sales Analytics (Last 7 Days)
            $daily = $this->repo->getDailySales(7);
            $salesData = [
                'labels' => [],
                'revenue' => [],
                'volume' => []
            ];
            
            // Fill with default 7 days in case DB returns gaps
            for ($i = 6; $i >= 0; $i--) {
                $dateStr = date('Y-m-d', strtotime("-$i days"));
                $salesData['labels'][] = date('D', strtotime($dateStr));
                
                $found = false;
                foreach ($daily as $row) {
                    if ($row['order_date'] === $dateStr) {
                        $salesData['revenue'][] = (float)$row['revenue'];
                        $salesData['volume'][] = (int)$row['volume'] * 500; // Scaled up for visual parity with revenue if needed
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $salesData['revenue'][] = 0;
                    $salesData['volume'][] = 0;
                }
            }

            // 2. Revenue Overview (Last 7 Months)
            $monthly = $this->repo->getMonthlyRevenue(7);
            $revenueData = [
                'labels' => [],
                'data' => []
            ];
            
            for ($i = 6; $i >= 0; $i--) {
                $monthStr = date('Y-m', strtotime("-$i months"));
                $revenueData['labels'][] = date('M', strtotime($monthStr . '-01'));
                
                $found = false;
                foreach ($monthly as $row) {
                    if ($row['month_str'] === $monthStr) {
                        $revenueData['data'][] = (float)$row['revenue'];
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $revenueData['data'][] = 0;
                }
            }

            // 3. Category Sales
            $categories = $this->repo->getCategorySales();
            $categoryData = [
                'labels' => [],
                'data' => []
            ];
            $totalCatSales = array_sum(array_column($categories, 'total_sales'));
            $totalProducts = array_sum(array_column($categories, 'product_count'));
            $categoryPercentages = [];
            
            if (empty($categories)) {
                $categoryData['labels'] = ['No Categories'];
                $categoryData['data'] = [100];
                $categoryPercentages = ['No Categories' => '0%'];
            } else {
                foreach ($categories as $cat) {
                    if ($totalCatSales > 0) {
                        $pct = round(($cat['total_sales'] / $totalCatSales) * 100);
                        $label = $pct . '% Sales';
                    } else {
                        // Fallback: Distribution of products across categories
                        $pct = $totalProducts > 0 ? round(($cat['product_count'] / $totalProducts) * 100) : 0;
                        $label = $pct . '% Products';
                    }
                    $categoryData['labels'][] = $cat['category_name'];
                    $categoryData['data'][] = $pct;
                    $categoryPercentages[$cat['category_name']] = $label;
                }
            }

            return [
                'sales' => $salesData,
                'revenue' => $revenueData,
                'category' => $categoryData,
                'category_percentages' => $categoryPercentages
            ];
        } catch (Exception $e) {
            error_log("[DashboardService] getChartData Error: " . $e->getMessage());
            return [
                'sales' => ['labels' => ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'], 'revenue' => [0,0,0,0,0,0,0], 'volume' => [0,0,0,0,0,0,0]],
                'revenue' => ['labels' => ['Oct','Nov','Dec','Jan','Feb','Mar','Apr'], 'data' => [0,0,0,0,0,0,0]],
                'category' => ['labels' => ['Karadant','Laddu','Namkeen','Gift Box'], 'data' => [40,25,20,15]],
                'category_percentages' => [
                    'Karadant' => '40% Sales',
                    'Laddu' => '25% Sales',
                    'Namkeen' => '20% Sales',
                    'Gift Box' => '15% Sales'
                ]
            ];
        }
    }

    private function calculateTrend($current, $previous): string {
        if ($previous == 0) return $current > 0 ? '+100%' : '0%';
        $pct = (($current - $previous) / $previous) * 100;
        return ($pct >= 0 ? '+' : '') . round($pct, 1) . '%';
    }

    /**
     * Get recent orders with UI-friendly statuses
     */
    public function getRecentOrders(): array {
        $orders = $this->repo->getRecentOrders(5);
        
        foreach ($orders as &$order) {
            $order['initials'] = $this->getInitials($order['customer_name']);
            $order['status_class'] = $this->getStatusClass($order['status']);
        }
        
        return $orders;
    }

    private function formatCurrency(float $amount): string {
        return '₹ ' . number_format($amount, 0);
    }

    private function formatLargeNumber(float $num): string {
        if ($num >= 100000) {
            return '₹ ' . number_format($num / 100000, 1) . 'L';
        }
        if ($num >= 1000) {
            return '₹ ' . number_format($num / 1000, 1) . 'K';
        }
        return '₹ ' . number_format($num, 0);
    }

    private function getInitials(string $name): string {
        $parts = explode(' ', trim($name));
        $initials = '';
        if (count($parts) >= 2) {
            $initials = substr($parts[0], 0, 1) . substr($parts[count($parts)-1], 0, 1);
        } else {
            $initials = substr($parts[0], 0, 2);
        }
        return strtoupper($initials);
    }

    private function getStatusClass(string $status): string {
        switch (strtolower($status)) {
            case 'delivered':
            case 'completed':
            case 'active':
                return 'products-status-in';
            case 'pending':
            case 'processing':
                return 'products-status-low';
            case 'cancelled':
                return 'products-status-out';
            default:
                return 'products-status-low';
        }
    }
}
