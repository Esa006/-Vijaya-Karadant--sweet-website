<?php
/**
 * Sweets Website
 * =============================================================
 * File: reports.php
 * Description: API endpoint for dashboard analytics and reports
 * =============================================================
 */

require_once dirname(__DIR__, 3) . '/config/config.php';
require_once __DIR__ . '/BaseController.php';
require_once SERVICES_PATH . '/DashboardService.php';

class ReportsAPI extends BaseController {
    private DashboardService $service;

    public function __construct() {
        parent::__construct();
        $this->service = new DashboardService();
    }

    public function handleRequest() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->error('method_not_allowed', 'Method not allowed', 405);
        }

        try {
            $stats = $this->service->getStats();
            $this->success($stats);
        } catch (Exception $e) {
            $this->error('system', $e->getMessage());
        }
    }
}

$api = new ReportsAPI();
$api->handleRequest();
