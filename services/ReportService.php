<?php
/**
 * Sweets Website
 * =============================================================
 * File: ReportService.php
 * Description: Production-grade Analytics Service with Caching
 * Author: Antigravity - Senior Full-Stack Architect
 * Version: 3.0.0
 * =============================================================
 */

require_once REPOS_PATH . '/ReportRepository.php';

class ReportService {
    private ReportRepository $repo;
    private string $cacheDir;

    public function __construct() {
        $this->repo = new ReportRepository();
        $this->cacheDir = ROOT_PATH . '/cache/analytics';
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0777, true);
        }
    }

    /**
     * Fetch complete analytics dashboard dataset
     * MANDATORY: Single API call support
     */
    public function getFullAnalytics(string $range = 'weekly'): array {
        $cacheFile = $this->cacheDir . "/dashboard_{$range}.json";
        
        // 1. Cache Check (5 minutes TTL as requested)
        if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < 300)) {
            return json_decode(file_get_contents($cacheFile), true);
        }

        // 2. Resolve Date Range
        list($start, $end) = $this->resolveRange($range);

        // 3. Aggregate Data (Target < 200ms via indexed queries)
        $data = [
            'summary'      => $this->repo->getSummary($start, $end),
            'time_series'  => $this->repo->getTimeSeries($start, $end),
            'categories'   => $this->repo->getCategoryStats($start, $end),
            'top_products' => $this->repo->getTopProducts($start, $end),
            'last_updated' => date('Y-m-d H:i:s'),
            'range'        => $range
        ];

        // 4. Persistence / Caching
        file_put_to_contents($cacheFile, json_encode($data));

        return $data;
    }

    /**
     * Calculate start/end dates for period aliases
     */
    private function resolveRange(string $range): array {
        $end = date('Y-m-d 23:59:59');
        switch ($range) {
            case 'daily':
                $start = date('Y-m-d 00:00:00');
                break;
            case 'weekly':
                $start = date('Y-m-d 00:00:00', strtotime('-7 days'));
                break;
            case 'monthly':
                $start = date('Y-m-d 00:00:00', strtotime('-30 days'));
                break;
            default:
                $start = date('Y-m-d 00:00:00', strtotime('-7 days'));
        }
        return [$start, $end];
    }

    /**
     * Helper to clear cache manually if needed (e.g. after large batch updates)
     */
    public function clearCache(): void {
        $files = glob($this->cacheDir . '/*');
        foreach($files as $file) {
            if(is_file($file)) unlink($file);
        }
    }
}
