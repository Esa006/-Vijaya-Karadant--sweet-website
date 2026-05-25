<?php
/**
 * Sweets Website
 * =============================================================
 * File: DashboardRepository.php
 * Description: Analytics and statistical data access 
 * =============================================================
 */

require_once 'BaseRepository.php';

class DashboardRepository extends BaseRepository {

    /**
     * Get total order count
     */
    public function getTotalOrders(): int {
        $stmt = $this->db->query("SELECT COUNT(*) FROM orders");
        return (int)$stmt->fetchColumn();
    }

    /**
     * Get total revenue (excluding cancelled)
     */
    public function getTotalRevenue(): float {
        $stmt = $this->db->query("SELECT SUM(total_amount) FROM orders WHERE status != 'cancelled'");
        return (float)($stmt->fetchColumn() ?: 0);
    }

    /**
     * Get pending orders value (Pending + Paid)
     */
    public function getPendingOrdersValue(): float {
        $stmt = $this->db->query("SELECT SUM(total_amount) FROM orders WHERE status IN ('pending', 'paid')");
        return (float)($stmt->fetchColumn() ?: 0);
    }

    /**
     * Get total customer count
     */
    public function getTotalCustomers(): int {
        $stmt = $this->db->query("SELECT COUNT(*) FROM users WHERE role = 'customer'");
        return (int)$stmt->fetchColumn();
    }

    /**
     * Get total stock value (Price * Quantity)
     */
    public function getStockValue(): float {
        $stmt = $this->db->query("
            SELECT SUM(
                COALESCE(i.stock, p.stock_quantity, 0) * 
                COALESCE(p.sale_price, p.base_price, 0)
            ) 
            FROM products p
            LEFT JOIN inventory i ON p.id = i.product_id
            WHERE p.deleted_at IS NULL
        ");
        return (float)($stmt->fetchColumn() ?: 0);
    }

    /**
     * Get recent orders with customer names
     */
    public function getRecentOrders(int $limit = 5): array {
        $stmt = $this->db->prepare("
            SELECT o.id, o.order_number, u.id as user_id, 
                   COALESCE(u.full_name, 'Guest Customer') as customer_name, 
                   o.total_amount, o.status, o.created_at
            FROM orders o
            LEFT JOIN users u ON o.user_id = u.id
            ORDER BY o.created_at DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get summary stats for a specific date range
     */
    public function getStatsForPeriod(string $start, string $end): array {
        $sql = "SELECT 
                    COUNT(*) as total_orders,
                    COALESCE(SUM(total_amount), 0) as total_revenue,
                    COUNT(DISTINCT user_id) as total_customers
                FROM orders 
                WHERE created_at BETWEEN :start AND :end
                AND status != 'cancelled'";
        
        return $this->fetchOne($sql, [
            'start' => $start . ' 00:00:00',
            'end'   => $end . ' 23:59:59'
        ]);
    }

    /**
     * Get daily sales/volume for the last 7 days
     */
    public function getDailySales(int $days = 7): array {
        $sql = "SELECT 
                    DATE(created_at) as order_date, 
                    COALESCE(SUM(total_amount), 0) as revenue,
                    COUNT(*) as volume
                FROM orders
                WHERE status != 'cancelled'
                AND created_at >= DATE_SUB(CURDATE(), INTERVAL :days DAY)
                GROUP BY DATE(created_at)
                ORDER BY DATE(created_at) ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':days', $days - 1, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get monthly revenue for the last X months
     */
    public function getMonthlyRevenue(int $months = 7): array {
        $sql = "SELECT 
                    DATE_FORMAT(created_at, '%Y-%m') as month_str,
                    COALESCE(SUM(total_amount), 0) as revenue
                FROM orders
                WHERE status != 'cancelled'
                AND created_at >= DATE_SUB(CURDATE(), INTERVAL :months MONTH)
                GROUP BY month_str
                ORDER BY month_str ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':months', $months - 1, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get sales distribution by category
     */
    public function getCategorySales(): array {
        $sql = "SELECT 
                    c.name as category_name,
                    COALESCE(SUM(oi.price_at_time * oi.quantity), 0) as total_sales,
                    COUNT(DISTINCT p.id) as product_count
                FROM categories c
                LEFT JOIN products p ON p.category_id = c.id AND p.deleted_at IS NULL
                LEFT JOIN order_items oi ON oi.product_id = p.id
                LEFT JOIN orders o ON oi.order_id = o.id AND o.status != 'cancelled'
                WHERE c.status = 'active' AND (c.parent_id IS NULL OR c.parent_id = 0)
                GROUP BY c.id, c.name
                ORDER BY total_sales DESC, product_count DESC";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
