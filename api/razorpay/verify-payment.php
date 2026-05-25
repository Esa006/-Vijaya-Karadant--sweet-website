<?php
/**
 * Sweets Website
 * =============================================================
 * File: api/razorpay/verify-payment.php
 * Description: Verify payment signature and persist order (Transaction-safe)
 * =============================================================
 */

require_once '../../config/config.php';
require_once '../../src/Autoloader.php';
require_once SERVICES_PATH . '/CartService.php';
require_once SERVICES_PATH . '/PaymentService.php';
require_once SERVICES_PATH . '/AddressService.php';
require_once REPOS_PATH . '/OrderRepository.php';

ini_set('display_errors', '0'); // CRITICAL: Prevent warnings from breaking JSON
error_reporting(E_ALL);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['razorpay_order_id'], $input['razorpay_payment_id'], $input['razorpay_signature'])) {
    echo json_encode(['success' => false, 'message' => 'Missing payment details']);
    exit;
}

try {
    $paymentService = new PaymentService();
    $orderRepo = new OrderRepository();
    $cartService = new CartService();
    $addressService = new AddressService();

    // 1. Verify Signature
    $isValid = $paymentService->verifyRazorpaySignature(
        $input['razorpay_order_id'],
        $input['razorpay_payment_id'],
        $input['razorpay_signature']
    );

    if (!$isValid) {
        throw new Exception("Invalid payment signature");
    }

    // 2. Persist Order (Transaction-safe)
    $cartItems = $cartService->getItems();
    $userId = $_SESSION['user_id'] ?? 1; // Default to System Guest (ID 1)
    
    // Re-calculate totals
    $subtotal = $cartService->getSubtotal();
    $shipping = $cartService->getShippingCharges();
    $discount = $cartService->getCouponDiscount();
    $total = ($subtotal + $shipping) - $discount;

    // Prepare order items for repository
    $itemsForRepo = [];
    foreach ($cartItems as $item) {
        $isCombo = isset($item['type']) && $item['type'] === 'combo';
        
        $itemsForRepo[] = [
            'type'       => $item['type'] ?? 'product',
            'variant_id' => $item['variant_id'] ?? 0,
            'product_id' => $isCombo ? null : ($item['id'] ?? null),
            'combo_id'   => $isCombo ? ($item['combo_id'] ?? null) : null,
            'quantity'   => $item['quantity'],
            'price'      => $item['price'],
            'items'      => $isCombo ? ($item['items'] ?? []) : [] // For child stock locking
        ];
    }

    // Resolve shipping/billing address so admin and customer pages get dynamic address details.
    $addressId = null;
    $checkoutData = [];
    if (!empty($_SESSION['checkout_data']) && is_array($_SESSION['checkout_data'])) {
        $checkoutData = $_SESSION['checkout_data'];
    } elseif (!empty($input['checkout_data']) && is_array($input['checkout_data'])) {
        $checkoutData = $input['checkout_data'];
    }

    if (!empty($checkoutData)) {
        error_log("[VerifyPayment] Checkout data found. Proceeding to save address.");
        $cd = $checkoutData;
        $db = Database::getInstance();
        $sql = "INSERT INTO addresses
                    (user_id, recipient_name, type, address_line1, city, state, zip_code, country, phone)
                VALUES
                    (:uid, :name, 'shipping', :line1, :city, :state, :zip, :country, :phone)";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':uid'     => $userId,
            ':name'    => trim((string)(($cd['first_name'] ?? '') . ' ' . ($cd['last_name'] ?? ''))),
            ':line1'   => (string)($cd['address'] ?? ''),
            ':city'    => (string)($cd['city'] ?? ''),
            ':state'   => (string)($cd['state'] ?? ''),
            ':zip'     => (string)($cd['pin_code'] ?? ''),
            ':country' => (string)($cd['country'] ?? 'India'),
            ':phone'   => (string)($cd['phone'] ?? ''),
        ]);
        $addressId = (int)$db->lastInsertId();
        error_log("[VerifyPayment] Address saved with ID: $addressId");
    } elseif ($userId > 0) {
        $addresses = $addressService->getAddressesByUser($userId);
        foreach ($addresses as $a) {
            if (!empty($a['is_default'])) {
                $addressId = (int)$a['id'];
                break;
            }
        }
        if (!$addressId && !empty($addresses[0]['id'])) {
            $addressId = (int)$addresses[0]['id'];
        }
    }

    // Determine customer name safely
    $customerName = trim(($checkoutData['first_name'] ?? '') . ' ' . ($checkoutData['last_name'] ?? ''));
    if (empty($customerName) && $userId > 1) {
        // Fallback to user profile if available (assuming AuthService or similar)
        // For now, use a generic placeholder or fetch if needed.
    }

    // Check if the order was already created by create-order.php
    $existingOrder = $orderRepo->fetchByIdempotencyKey($input['razorpay_order_id']);
    
    $orderId = null;

    if ($existingOrder) {
        $orderId = $existingOrder['id'];
        
        // --- ADDRESS BACKFILL ON IDEMPOTENCY HIT ---
        if (empty($existingOrder['shipping_address_id']) && $addressId) {
            $orderRepo->update($orderId, [
                'shipping_address_id' => $addressId,
                'billing_address_id'  => $addressId,
                'customer_name'       => !empty($customerName) ? $customerName : ($existingOrder['customer_name'] ?? 'Guest Customer')
            ]);
        }

        if (strtolower($existingOrder['status']) === 'paid') {
            error_log("[VerifyPayment] True Idempotency Hit. Order {$orderId} is already PAID.");
            echo json_encode(['success' => true, 'message' => 'Order already verified', 'order_id' => $orderId]);
            exit;
        }
    } else {
        // Fallback: Create order if it somehow wasn't created in create-order.php
        $orderResult = $orderRepo->createWithStockLock($userId, $itemsForRepo, [
            'idempotency_key'     => $input['razorpay_order_id'],
            'shipping_address_id' => $addressId ?: null,
            'billing_address_id'  => $addressId ?: null,
            'customer_name'       => !empty($customerName) ? $customerName : 'Guest Customer',
            'status'              => 'pending',
            'total_amount'        => $total,
            'subtotal'            => $subtotal,
            'shipping_charges'    => $shipping,
            'discount_amount'     => $discount
        ]);

        if (!$orderResult['success']) {
            throw new Exception("Order persistence failed: " . $orderResult['error']);
        }
        $orderId = $orderResult['order_id'];
    }

    // 3. Update Order as Paid
    $orderRepo->markAsPaid($orderId, $input['razorpay_payment_id'], 'razorpay');

    // 4. Log Payment
    $orderRepo->logPayment([
        'order_id' => $orderId,
        'gateway' => 'razorpay',
        'txn_id' => $input['razorpay_payment_id'],
        'amount' => $total,
        'status' => 'success',
        'raw' => json_encode($input)
    ]);

    // 5. Clear Cart
    $cartService->clearCart();
    unset($_SESSION['checkout_data']);

    echo json_encode([
        'success' => true,
        'message' => 'Order placed successfully',
        'order_id' => $orderId
    ]);

} catch (Exception $e) {
    error_log("Payment Verification Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
