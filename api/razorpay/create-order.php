<?php
/**
 * Sweets Website
 * =============================================================
 * File: api/razorpay/create-order.php
 * Description: Securely create Razorpay order from server side
 * =============================================================
 */

require_once '../../config/config.php';
require_once '../../src/Autoloader.php';
require_once SERVICES_PATH . '/CartService.php';
require_once SERVICES_PATH . '/PaymentService.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    $input = [];
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $cartService = new CartService();
    $paymentService = new PaymentService();

    if (!empty($input['checkout_data']) && is_array($input['checkout_data'])) {
        $_SESSION['checkout_data'] = $input['checkout_data'];
        error_log("[CreateOrder] checkout_data received and saved to session.");
    } else {
        error_log("[CreateOrder] Warning: No checkout_data received in request body.");
    }

    // Re-calculate totals
    $subtotal = $cartService->getSubtotal();
    $shipping = $cartService->getShippingCharges();
    $discount = $cartService->getCouponDiscount();
    $total = ($subtotal + $shipping) - $discount;

    if ($total <= 0) {
        throw new Exception("Invalid order total");
    }

    $orderData = [
        'total' => $total,
        'user_id' => $_SESSION['user_id']
    ];

    // 1. Create Razorpay Order first (Fast, no DB locks yet)
    $result = $paymentService->createRazorpayOrder($orderData);
    if (!$result['success']) {
        throw new Exception($result['message'] ?? 'Payment gateway error');
    }
    
    $razorpayOrderId = $result['order_id'];

    // 2. Create DB Order & Lock Stock (CRITICAL)
    require_once REPOS_PATH . '/OrderRepository.php';
    $orderRepo = new OrderRepository();
    
    $cartItems = $cartService->getItems();
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
            'items'      => $isCombo ? ($item['items'] ?? []) : []
        ];
    }

    $userId = $_SESSION['user_id'];
    $checkoutData = $_SESSION['checkout_data'] ?? [];
    $customerName = trim(($checkoutData['first_name'] ?? '') . ' ' . ($checkoutData['last_name'] ?? ''));

    // --- ENHANCED ADDRESS RESOLUTION ---
    $addressId = null;
    if (!empty($checkoutData)) {
        try {
            $db = Database::getInstance();
            $sql = "INSERT INTO addresses (user_id, recipient_name, type, address_line1, city, state, zip_code, country, phone)
                    VALUES (:uid, :name, 'shipping', :line1, :city, :state, :zip, :country, :phone)";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':uid'     => $userId,
                ':name'    => !empty($customerName) ? $customerName : 'Customer',
                ':line1'   => $checkoutData['address']  ?? '',
                ':city'    => $checkoutData['city']     ?? '',
                ':state'   => $checkoutData['state']    ?? '',
                ':zip'     => $checkoutData['pin_code'] ?? '',
                ':country' => $checkoutData['country']  ?? 'India',
                ':phone'   => $checkoutData['phone']    ?? '',
            ]);
            $addressId = (int)$db->lastInsertId();
        } catch (\Exception $addrEx) {
            error_log("[CreateOrder] Address save failed: " . $addrEx->getMessage());
        }
    }

    if (!$addressId) {
        require_once SERVICES_PATH . '/AddressService.php';
        $addrService = new AddressService();
        $addresses = $addrService->getAddressesByUser($userId);
        foreach ($addresses as $a) {
            if ($a['is_default']) { $addressId = $a['id']; break; }
        }
        if (!$addressId && count($addresses) > 0) $addressId = $addresses[0]['id'];
    }

    $orderResult = $orderRepo->createWithStockLock($userId, $itemsForRepo, [
        'idempotency_key'     => $razorpayOrderId,
        'customer_name'       => !empty($customerName) ? $customerName : 'Guest Customer',
        'shipping_address_id' => $addressId,
        'billing_address_id'  => $addressId,
        'status'              => 'pending',
        'total_amount'        => $total,
        'subtotal'            => $subtotal,
        'shipping_charges'    => $shipping,
        'discount_amount'     => $discount
    ]);

    if (!$orderResult['success']) {
        throw new Exception("Stock unavailable: " . $orderResult['error']);
    }

    // Include the DB order ID in the result for safety
    $result['db_order_id'] = $orderResult['order_id'];
    
    echo json_encode($result);

} catch (Exception $e) {
    error_log("API Create Order Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
