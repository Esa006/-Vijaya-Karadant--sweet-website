<?php
/**
 * Sweets Website
 * =============================================================
 * File: ReportRepository.php
 * Description: Production-grade Analytics Data Access Layer
 * Author: Antigravity - Senior Full-Stack Architect
 * Version: 3.0.0
 * =============================================================
 */

require_once 'BaseRepository.php';

class ReportRepository extends BaseRepository {

    /**
     * Get aggregate KPI summary for a given date range
     * STRICT: Excludes cancelled orders, uses completed only where applicable
     */
    public function getSummary(string $start, string $end): array {
        $sql = "SELECT 
                    COALESCE(SUM(total_amount), 0) as revenue,
                    COUNT(id) as orders,
                    (SELECT COALESCE(SUM(quantity), 0) FROM order_items oi 
                     JOIN orders o2 ON oi.order_id = o2.id 
                     WHERE o2.created_at BETWEEN :start AND :end AND o2.status != 'cancelled') as units,
                    COALESCE(SUM(total_amount) / NULLIF(COUNT(id), 0), 0) as aov
                FROM orders 
                WHERE created_at BETWEEN :start AND :end 
                AND status != 'cancelled'";
        
        return $this->fetchOne($sql, ['start' => $start, 'end' => $end]);
    }

    /**
     * Get time-series data for revenue and orders
     * Groups by DATE for granular chart rendering
     */
    public function getTimeSeries(string $start, string $end): array {
        $sql = "SELECT 
                    DATE(created_at) as date,
                    SUM(total_amount) as revenue,
                    COUNT(id) as orders
                FROM orders 
                WHERE status != 'cancelled' 
                AND created_at BETWEEN :start AND :end
                GROUP BY DATE(created_at)
                ORDER BY date ASC";
        
        return $this->fetchAll($sql, ['start' => $start, 'end' => $end]);
    }

    /**
     * Get revenue distribution by product category
     */
    public function getCategoryStats(string $start, string $end): array {
        $sql = "SELECT 
                    c.name,
                    SUM(oi.quantity * oi.price_at_time) as revenue
                FROM order_items oi
                JOIN orders o ON oi.order_id = o.id
                JOIN products p ON oi.product_id = p.id
                JOIN categories c ON p.category_id = c.id
                WHERE o.status != 'cancelled'
                AND o.created_at BETWEEN :start AND :end
                GROUP BY c.id
                ORDER BY revenue DESC";
        
        return $this->fetchAll($sql, ['start' => $start, 'end' => $end]);
    }

    /**
     * Get top selling products based on volume and revenue
     */
    public function getTopProducts(string $start, string $end, int $limit = 5): array {
        $sql = "SELECT 
                    p.name,
                    SUM(oi.quantity) as sold,
                    SUM(oi.quantity * oi.price_at_time) as revenue
                FROM order_items oi
                JOIN orders o ON oi.order_id = o.id
                JOIN products p ON oi.product_id = p.id
                WHERE o.status != 'cancelled'
                AND o.created_at BETWEEN :start AND :end
                GROUP BY p.id
                ORDER BY sold DESC
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':start', $start);
        $stmt->bindValue(':end', $end);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
