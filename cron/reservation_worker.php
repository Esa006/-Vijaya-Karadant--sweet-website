<?php
/**
 * Sweets Website
 * =============================================================
 * File: cron/reservation_worker.php
 * Description: Background daemon to release inventory reservations
 *              for abandoned checkouts after 10 minutes.
 * =============================================================
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../repositories/OrderRepository.php';

// Ensure CLI execution only
if (php_sapi_name() !== 'cli') {
    die("This script can only be run from the command line.");
}

$repo = new OrderRepository();
$timeoutMinutes = 10;
$sleepSeconds = 60;

echo "[+] Starting Inventory Reservation Worker (Timeout: {$timeoutMinutes}m)...\n";
echo "[+] Press Ctrl+C to stop.\n\n";

while (true) {
    try {
        $expiredCount = $repo->expirePendingReservations($timeoutMinutes);
        
        if ($expiredCount > 0) {
            $timestamp = date('Y-m-d H:i:s');
            echo "[$timestamp] ✅ Released inventory for $expiredCount expired reservation(s).\n";
        }

    } catch (Exception $e) {
        $timestamp = date('Y-m-d H:i:s');
        echo "[$timestamp] ❌ Worker Error: " . $e->getMessage() . "\n";
    }

    // Sleep before next check
    sleep($sleepSeconds);
}
