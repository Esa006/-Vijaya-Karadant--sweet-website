<?php
/**
 * Sweets Website
 * =============================================================
 * File: api/cron-release-abandoned-orders.php
 * Description: Background job to handle Failure Design. 
 * Reverts stock for users who locked stock but never completed payment.
 * =============================================================
 */

// Allow script to run indefinitely
set_time_limit(0);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Autoloader.php';
require_once SERVICES_PATH . '/OrderService.php';

// Security: Allow CLI or require a secret token via web (Replace 'YOUR_SECRET_TOKEN' in production)
$isCLI = (php_sapi_name() === 'cli');
$secretToken = $_GET['token'] ?? '';
$configuredToken = defined('CRON_SECRET') ? CRON_SECRET : 'dev_secret_token';

if (!$isCLI && $secretToken !== $configuredToken) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized Access']);
    exit;
}

try {
    $db = Database::getInstance();
    $orderService = new OrderService();

    // Find all 'pending' orders older than 15 minutes (Payment Timeout)
    $sql = "SELECT id, order_number FROM orders 
            WHERE LOWER(status) = 'pending' 
            AND created_at < DATE_SUB(NOW(), INTERVAL 15 MINUTE)";
    
    $stmt = $db->query($sql);
    $abandonedOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $releasedCount = 0;
    $errors = [];

    foreach ($abandonedOrders as $order) {
        $orderId = (int)$order['id'];
        
        // Use the existing state machine which handles the stock release securely via `cancelOrder`
        $result = $orderService->transitionStatus($orderId, 'cancelled');
        
        if ($result['success']) {
            // Document the failure reason
            $updateSql = "UPDATE orders SET admin_notes = 'Auto-cancelled due to payment timeout. Stock released.' WHERE id = :id";
            $db->prepare($updateSql)->execute([':id' => $orderId]);
            $releasedCount++;
            
            error_log("[CRON] Released stock for abandoned Order #{$order['order_number']}");
        } else {
            $errors[] = "Failed Order #{$order['order_number']}: " . ($result['message'] ?? 'Unknown error');
        }
    }

    $response = [
        'success' => true,
        'message' => "Successfully released stock for $releasedCount abandoned orders.",
        'errors' => $errors
    ];

    if (!$isCLI) {
        header('Content-Type: application/json');
        echo json_encode($response);
    } else {
        echo $response['message'] . "\n";
        if (!empty($errors)) {
            print_r($errors);
        }
    }

} catch (Exception $e) {
    $errorMsg = "CRON Error: " . $e->getMessage();
    error_log($errorMsg);
    
    if (!$isCLI) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $errorMsg]);
    } else {
        echo $errorMsg . "\n";
    }
}
