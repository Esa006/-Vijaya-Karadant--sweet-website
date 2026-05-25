<?php

namespace App\Modules\Admin\Controllers;

use App\Core\BaseController;
use App\Modules\Admin\Services\DashboardService;

/**
 * Admin Dashboard Controller
 * Orchestrates dashboard logic for the modular architecture
 */
class DashboardController extends BaseController {
    
    private DashboardService $dashboardService;

    public function __construct() {
        $this->dashboardService = new DashboardService();
    }

    /**
     * Get dashboard summary via AJAX
     */
    public function index(): void {
        try {
            $metrics = $this->dashboardService->getDashboardMetrics();
            $this->success("Dashboard metrics loaded successfully", $metrics);
        } catch (\Throwable $e) {
            $this->error("Failed to load dashboard metrics: " . $e->getMessage(), 500);
        }
    }

    /**
     * Display the dashboard view
     */
    public function show(): void {
        // In a full MVC, this would render a PHP template.
        // For now, we will return the metrics as we transition to an AJAX-driven UI.
        $this->index();
    }
}
