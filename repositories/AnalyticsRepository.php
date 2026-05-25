<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/Database.php';

class AnalyticsRepository {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getOverviewStats(string $startDate, string $endDate): array {
        $sql = "SELECT 
                    (SUM(o.total_amount) - COALESCE((SELECT SUM(amount) FROM refunds WHERE created_at BETWEEN :sub_start AND :sub_end), 0)) as net_revenue,
                    COUNT(DISTINCT o.id) as total_orders,
                    AVG(o.total_amount) as aov,
                    (SELECT SUM(quantity) FROM order_items WHERE order_id IN (SELECT id FROM orders WHERE status != 'cancelled' AND created_at BETWEEN :oi_start AND :oi_end)) as total_units
                FROM orders o
                WHERE o.status != 'cancelled' 
                AND o.created_at BETWEEN :start AND :end";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'sub_start' => $startDate, 
            'sub_end' => $endDate,
            'oi_start' => $startDate,
            'oi_end' => $endDate,
            'start' => $startDate, 
            'end' => $endDate
        ]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ?: [
            'net_revenue' => 0,
            'total_orders' => 0,
            'aov' => 0,
            'total_units' => 0
        ];
    }

    public function getProfit(string $startDate, string $endDate): float {
        $sql = "SELECT SUM(oi.quantity * (oi.price_at_time - p.cost_price)) as gross_profit
                FROM order_items oi
                JOIN products p ON oi.product_id = p.id
                JOIN orders o ON oi.order_id = o.id
                WHERE o.status != 'cancelled' 
                AND o.created_at BETWEEN :start AND :end";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['start' => $startDate, 'end' => $endDate]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $refundSql = "SELECT SUM(amount) FROM refunds WHERE created_at BETWEEN :start AND :end";
        $refundStmt = $this->db->prepare($refundSql);
        $refundStmt->execute(['start' => $startDate, 'end' => $endDate]);
        $refunds = (float)$refundStmt->fetchColumn();

        return (float)($row['gross_profit'] ?? 0) - $refunds;
    }

    public function getTopProducts(string $startDate, string $endDate, int $limit = 8): array {
        $sql = "
            SELECT 
                p.name,
                p.sku,
                SUM(oi.quantity)                           AS total_sold,
                SUM(oi.quantity * oi.price_at_time)        AS product_revenue
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            JOIN orders o   ON oi.order_id   = o.id
            WHERE o.status != 'cancelled'
              AND o.created_at BETWEEN :start AND :end
            GROUP BY p.id, p.name, p.sku
            ORDER BY product_revenue DESC
            LIMIT :lim
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':start', $startDate);
        $stmt->bindValue(':end',   $endDate);
        $stmt->bindValue(':lim',   $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRevenueChartData(string $startDate, string $endDate): array {
        $sql = "SELECT DATE(created_at) as date, SUM(total_amount) as revenue
                FROM orders 
                WHERE status != 'cancelled' 
                AND created_at BETWEEN :start AND :end
                GROUP BY DATE(created_at)
                ORDER BY date ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['start' => $startDate, 'end' => $endDate]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getUnitsChartData(string $startDate, string $endDate): array {
        $sql = "SELECT DATE(o.created_at) as date, SUM(oi.quantity) as units
                FROM order_items oi
                JOIN orders o ON oi.order_id = o.id
                WHERE o.status != 'cancelled' 
                AND o.created_at BETWEEN :start AND :end
                GROUP BY DATE(o.created_at)
                ORDER BY date ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['start' => $startDate, 'end' => $endDate]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAovChartData(string $startDate, string $endDate): array {
        $sql = "SELECT DATE(created_at) as date, AVG(total_amount) as aov
                FROM orders 
                WHERE status != 'cancelled' 
                AND created_at BETWEEN :start AND :end
                GROUP BY DATE(created_at)
                ORDER BY date ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['start' => $startDate, 'end' => $endDate]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCategoryData(string $startDate, string $endDate): array {
        $sql = "SELECT c.name as category, SUM(oi.quantity * oi.price_at_time) as revenue
                FROM order_items oi
                JOIN products p ON oi.product_id = p.id
                JOIN categories c ON p.category_id = c.id
                JOIN orders o ON oi.order_id = o.id
                WHERE o.status != 'cancelled' 
                AND o.created_at BETWEEN :start AND :end
                GROUP BY c.id, c.name
                ORDER BY revenue DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['start' => $startDate, 'end' => $endDate]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
