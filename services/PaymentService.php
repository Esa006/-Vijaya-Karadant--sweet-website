<?php
/**
 * Sweets Website
 * =============================================================
 * File: PaymentService.php
 * Description: Business logic for Payment Integrations
 * Author: Antigravity - Senior Backend Engineer
 * Version: 2.1.0
 * =============================================================
 */

require_once REPOS_PATH . '/OrderRepository.php';
require_once SERVICES_PATH . '/AuditService.php';

class PaymentService {
    private OrderRepository $orderRepo;
    private AuditService $audit;

    public function __construct() {
        $this->orderRepo = new OrderRepository();
        $this->audit     = new AuditService();
    }

    /**
     * Create Razorpay Order (Secure Backend Call)
     */
    public function createRazorpayOrder(array $cartData): array {
        $amountInPaise = (int)($cartData['total'] * 100);
        $keyId = defined('RAZORPAY_KEY') ? RAZORPAY_KEY : (getenv('RAZORPAY_KEY') ?: '');
        $keySecret = defined('RAZORPAY_SECRET') ? RAZORPAY_SECRET : (getenv('RAZORPAY_SECRET') ?: '');

        if ($keyId === '' || $keySecret === '') {
            error_log('[PaymentService] Razorpay credentials are not configured.');
            return [
                'success' => false,
                'message' => 'Razorpay credentials are not configured. Add RAZORPAY_KEY and RAZORPAY_SECRET in .env.'
            ];
        }

        if (!function_exists('curl_init')) {
            error_log('[PaymentService] PHP cURL extension is not enabled.');
            return [
                'success' => false,
                'message' => 'Payment gateway is unavailable because PHP cURL is not enabled.'
            ];
        }

        $payload = [
            'amount' => $amountInPaise,
            'currency' => 'INR',
            'receipt' => 'rcpt_' . uniqid(),
            'payment_capture' => 1
        ];

        $ch = curl_init('https://api.razorpay.com/v1/orders');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, "$keyId:$keySecret");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false || $curlError !== '') {
            error_log('[PaymentService] Razorpay cURL error: ' . $curlError);
            return [
                'success' => false,
                'message' => 'Could not reach Razorpay. Check internet connection and cURL/SSL settings.'
            ];
        }

        if ($httpCode !== 200) {
            $error = json_decode((string)$response, true);
            $gatewayMessage = $error['error']['description'] ?? $error['error']['reason'] ?? 'Payment gateway rejected the order.';
            error_log('[PaymentService] Razorpay order error HTTP ' . $httpCode . ': ' . $response);

            if ($httpCode === 401) {
                return [
                    'success' => false,
                    'message' => 'Razorpay authentication failed. Check RAZORPAY_KEY and RAZORPAY_SECRET in .env.'
                ];
            }

            return ['success' => false, 'message' => $gatewayMessage];
        }

        $order = json_decode($response, true);
        if (!is_array($order) || empty($order['id'])) {
            error_log('[PaymentService] Invalid Razorpay order response: ' . $response);
            return ['success' => false, 'message' => 'Invalid response from payment gateway.'];
        }

        return [
            'success' => true,
            'order_id' => $order['id'],
            'amount' => $amountInPaise,
            'key' => $keyId
        ];
    }

    /**
     * Verify Razorpay Signature (Security Boundary)
     */
    public function verifyRazorpaySignature(string $orderId, string $paymentId, string $signature): bool {
        $keySecret = defined('RAZORPAY_SECRET') ? RAZORPAY_SECRET : (getenv('RAZORPAY_SECRET') ?: '');
        if ($keySecret === '') {
            error_log('[PaymentService] Cannot verify Razorpay signature without RAZORPAY_SECRET.');
            return false;
        }
        $expectedSignature = hash_hmac('sha256', $orderId . '|' . $paymentId, $keySecret);
        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Process validated webhook event
     */
    public function processWebhookEvent(array $event): bool {
        $orderId = $event['order_id'] ?? 0;
        $status  = $event['status'] ?? 'failed';

        if ($status === 'paid') {
            // Orchestrate the OrderService for state transition
            require_once SERVICES_PATH . '/OrderService.php';
            $orderService = new OrderService();
            $result = $orderService->transitionStatus($orderId, 'paid');
            return $result['success'];
        }

        return false;
    }
}
