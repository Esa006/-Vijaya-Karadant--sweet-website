<?php
/**
 * Sweets Website
 * =============================================================
 * File: upi-payment.php
 * Description: UPI QR Payment Simulator API
 * =============================================================
 */

require_once '../../config/config.php';

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthenticated']); exit;
}

$input  = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $input['action'] ?? ($_GET['action'] ?? '');

// ── Helpers ──────────────────────────────────────────────────
function generateTxnId(): string {
    return 'UPI' . strtoupper(substr(md5(uniqid((string)mt_rand(), true)), 0, 10));
}

function dbConn(): PDO {
    static $pdo;
    if (!$pdo) {
        $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
    return $pdo;
}

// ── Action: Initiate UPI Payment ─────────────────────────────
if ($action === 'initiate') {
    $orderId  = (int)($input['order_id'] ?? 0);
    $amount   = (float)($input['amount'] ?? 0);
    $name     = trim($input['merchant_name'] ?? SITE_NAME);
    $upiId    = UPI_ID ?? 'demo@upi';

    if ($amount <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid amount']); exit;
    }

    $txnId    = generateTxnId();
    $upiUri   = "upi://pay?pa={$upiId}&pn=" . urlencode($name) . "&am={$amount}&cu=INR&tn=" . urlencode("Order #{$orderId}");
    $expiresAt = date('Y-m-d H:i:s', time() + 300); // 5 minutes

    // Store in session for polling
    $_SESSION['upi_txn'] = [
        'txn_id'      => $txnId,
        'order_id'    => $orderId, // This is db_order_id
        'amount'      => $amount,
        'upi_uri'     => $upiUri,
        'upi_id'      => $upiId,
        'shop_qr'     => UPI_QR_IMAGE,
        'initiated'   => time(),
        'expires_at'  => time() + 300,
        'status'      => 'pending',
    ];

    // Build shop QR absolute URL if path is set
    $shopQrUrl = '';
    if (!empty(UPI_QR_IMAGE)) {
        $shopQrUrl = rtrim(BASE_URL, '/') . '/' . ltrim(UPI_QR_IMAGE, '/');
    }

    echo json_encode([
        'success'      => true,
        'txn_id'       => $txnId,
        'upi_uri'      => $upiUri,
        'upi_id'       => $upiId,
        'amount'       => $amount,
        'expires_in'   => 300,
        'expires_at'   => $expiresAt,
        'shop_qr_url'  => $shopQrUrl,
    ]);
    exit;
}

// ── Action: Poll Payment Status ───────────────────────────────
if ($action === 'poll') {
    $txn = $_SESSION['upi_txn'] ?? null;

    if (!$txn) {
        echo json_encode(['success' => false, 'status' => 'not_found']); exit;
    }

    // Check database if it has already been processed or marked as success/failed
    if (in_array($txn['status'], ['success', 'failed', 'expired'])) {
        echo json_encode(['success' => true, 'status' => $txn['status'], 'txn_id' => $txn['txn_id']]); exit;
    }

    // Check timeout
    if (time() > $txn['expires_at']) {
        $_SESSION['upi_txn']['status'] = 'expired';
        
        // Update order status to failed in DB
        try {
            require_once '../../src/Autoloader.php';
            require_once REPOS_PATH . '/OrderRepository.php';
            $orderRepo = new OrderRepository();
            $orderRepo->update((int)$txn['order_id'], ['status' => 'failed']);
        } catch (\Exception $e) {}

        echo json_encode(['success' => true, 'status' => 'expired', 'txn_id' => $txn['txn_id']]); exit;
    }

    $elapsed = time() - $txn['initiated'];

    // Simulate realistic payment response: 
    // Between 8-20 seconds, 75% success, 20% failed, 5% stays pending
    $resolved = false;
    $outcome = 'pending';
    $failureReason = null;

    if ($elapsed >= 8) {
        $rand = mt_rand(1, 100);
        if ($rand <= 75) {
            $outcome = 'success';
            $resolved = true;
        } elseif ($rand <= 95) {
            $outcome = 'failed';
            $reasons = [
                'Insufficient balance in your UPI account',
                'Transaction declined by your bank',
                'UPI app cancelled the request',
                'Network timeout — payment not processed',
            ];
            $failureReason = $reasons[array_rand($reasons)];
            $resolved = true;
        }
    }

    if ($resolved) {
        $_SESSION['upi_txn']['status'] = $outcome;
        if ($outcome === 'success') {
            try {
                require_once '../../src/Autoloader.php';
                require_once SERVICES_PATH . '/CartService.php';
                require_once REPOS_PATH . '/OrderRepository.php';

                $orderRepo = new OrderRepository();
                $cartService = new CartService();

                // 1. Mark as paid in DB
                $orderRepo->markAsPaid((int)$txn['order_id'], $txn['txn_id'], 'upi');

                // 2. Log payment in DB
                $orderRepo->logPayment([
                    'order_id' => $txn['order_id'],
                    'gateway'  => 'upi',
                    'txn_id'   => $txn['txn_id'],
                    'amount'   => $txn['amount'],
                    'status'   => 'success',
                    'raw'      => json_encode(['method' => 'upi', 'simulation' => true])
                ]);

                // 3. Clear cart
                $cartService->clearCart();
                unset($_SESSION['checkout_data']);

            } catch (\Exception $e) {
                error_log("UPI Sim DB Resolve Error: " . $e->getMessage());
            }

            echo json_encode([
                'success'       => true,
                'status'        => 'success',
                'txn_id'        => $txn['txn_id'],
                'remaining'     => max(0, $txn['expires_at'] - time()),
            ]);
        } else {
            $_SESSION['upi_txn']['failure_reason'] = $failureReason;
            
            // Mark order as failed in DB
            try {
                require_once '../../src/Autoloader.php';
                require_once REPOS_PATH . '/OrderRepository.php';
                $orderRepo = new OrderRepository();
                $orderRepo->update((int)$txn['order_id'], ['status' => 'failed']);
                
                // Log failed payment
                $orderRepo->logPayment([
                    'order_id' => $txn['order_id'],
                    'gateway'  => 'upi',
                    'txn_id'   => $txn['txn_id'],
                    'amount'   => $txn['amount'],
                    'status'   => 'failed',
                    'raw'      => json_encode(['method' => 'upi', 'reason' => $failureReason])
                ]);
            } catch (\Exception $e) {}

            echo json_encode([
                'success'        => true,
                'status'         => 'failed',
                'txn_id'         => $txn['txn_id'],
                'failure_reason' => $failureReason,
            ]);
        }
    } else {
        // Still pending
        echo json_encode(['success' => true, 'status' => 'pending', 'remaining' => max(0, $txn['expires_at'] - time())]);
    }
    exit;
}

// ── Action: Force outcome (Demo Controls) ─────────────────────
if ($action === 'force') {
    $outcome = $input['outcome'] ?? 'success';
    if (!isset($_SESSION['upi_txn'])) {
        echo json_encode(['success' => false, 'message' => 'No active UPI session']); exit;
    }
    
    $txn = $_SESSION['upi_txn'];
    $_SESSION['upi_txn']['status'] = $outcome;

    if ($outcome === 'success') {
        try {
            require_once '../../src/Autoloader.php';
            require_once SERVICES_PATH . '/CartService.php';
            require_once REPOS_PATH . '/OrderRepository.php';

            $orderRepo = new OrderRepository();
            $cartService = new CartService();

            $orderRepo->markAsPaid((int)$txn['order_id'], $txn['txn_id'], 'upi');
            $orderRepo->logPayment([
                'order_id' => $txn['order_id'],
                'gateway'  => 'upi',
                'txn_id'   => $txn['txn_id'],
                'amount'   => $txn['amount'],
                'status'   => 'success',
                'raw'      => json_encode(['method' => 'upi', 'forced' => true])
            ]);

            $cartService->clearCart();
            unset($_SESSION['checkout_data']);
        } catch (\Exception $e) {}
    } else {
        $reasons = [
            'Bank server timeout',
            'Insufficient balance',
            'UPI app cancelled',
            'Network issue',
        ];
        $reason = $reasons[array_rand($reasons)];
        $_SESSION['upi_txn']['failure_reason'] = $reason;

        try {
            require_once '../../src/Autoloader.php';
            require_once REPOS_PATH . '/OrderRepository.php';
            $orderRepo = new OrderRepository();
            $orderRepo->update((int)$txn['order_id'], ['status' => 'failed']);
            
            $orderRepo->logPayment([
                'order_id' => $txn['order_id'],
                'gateway'  => 'upi',
                'txn_id'   => $txn['txn_id'],
                'amount'   => $txn['amount'],
                'status'   => 'failed',
                'raw'      => json_encode(['method' => 'upi', 'forced' => true, 'reason' => $reason])
            ]);
        } catch (\Exception $e) {}
    }

    echo json_encode(['success' => true, 'status' => $outcome]);
    exit;
}

// ── Action: Regenerate QR ─────────────────────────────────────
if ($action === 'regenerate') {
    if (!isset($_SESSION['upi_txn'])) {
        echo json_encode(['success' => false, 'message' => 'No active UPI session']); exit;
    }
    $txn = $_SESSION['upi_txn'];
    $txn['status']     = 'pending';
    $txn['initiated']  = time();
    $txn['expires_at'] = time() + 300;
    $txn['txn_id']     = generateTxnId();
    $_SESSION['upi_txn'] = $txn;
    echo json_encode([
        'success'    => true,
        'txn_id'     => $txn['txn_id'],
        'upi_uri'    => $txn['upi_uri'],
        'expires_in' => 300,
    ]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Unknown action']);
