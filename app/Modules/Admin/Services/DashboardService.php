<?php

namespace App\Modules\Admin\Services;

use App\Core\Database;

/**
 * Dashboard Service
 * Business logic for admin analytics
 */
class DashboardService {
    
    /**
     * Get aggregate counts for the dashboard
     */
    public function getDashboardMetrics(): array {
        return [
            'total_orders'    => $this->getTotalOrders(),
            'total_revenue'   => $this->getTotalRevenue(),
            'total_customers' => $this->getTotalCustomers(),
            'total_products'  => $this->getTotalProducts()
        ];
    }

    private function getTotalOrders(): int {
        return (int) Database::query("SELECT COUNT(*) FROM orders")->fetchColumn();
    }

    private function getTotalRevenue(): float {
        return (float) Database::query("SELECT SUM(total_amount) FROM orders WHERE status != 'cancelled'")->fetchColumn();
    }

    private function getTotalCustomers(): int {
        return (int) Database::query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();
    }

    private function getTotalProducts(): int {
        return (int) Database::query("SELECT COUNT(*) FROM products")->fetchColumn();
    }
}
