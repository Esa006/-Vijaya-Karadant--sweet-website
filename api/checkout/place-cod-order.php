<?php
/**
 * Sweets Website
 * =============================================================
 * File: api/checkout/place-cod-order.php
 * Description: Securely place a Cash on Delivery (COD) order
 * =============================================================
 */

require_once '../../config/config.php';
require_once '../../src/Autoloader.php';
require_once SERVICES_PATH . '/CartService.php';
require_once REPOS_PATH . '/OrderRepository.php';

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    $input = [];
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized. Please login to place order.']);
    exit;
}

try {
    $cartService = new CartService();
    $orderRepo = new OrderRepository();

    // 1. Save checkout data to session if provided
    if (!empty($input['checkout_data']) && is_array($input['checkout_data'])) {
        $_SESSION['checkout_data'] = $input['checkout_data'];
    }

    $checkoutData = $_SESSION['checkout_data'] ?? [];
    if (empty($checkoutData)) {
        throw new Exception("Shipping information is missing.");
    }

    // 2. Prepare order items
    $cartItems = $cartService->getItems();
    if (empty($cartItems)) {
        throw new Exception("Your cart is empty.");
    }

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

    // 3. Re-calculate totals
    $subtotal = $cartService->getSubtotal();
    $shipping = $cartService->getShippingCharges();
    $discount = $cartService->getCouponDiscount();
    $total = ($subtotal + $shipping) - $discount;

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
                ':uid'     => (int)$_SESSION['user_id'],
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
            error_log("[PlaceCOD] Address save failed: " . $addrEx->getMessage());
        }
    }

    if (!$addressId) {
        require_once SERVICES_PATH . '/AddressService.php';
        $addrService = new AddressService();
        $addresses = $addrService->getAddressesByUser((int)$_SESSION['user_id']);
        foreach ($addresses as $a) {
            if ($a['is_default']) { $addressId = $a['id']; break; }
        }
        if (!$addressId && count($addresses) > 0) $addressId = $addresses[0]['id'];
    }

    // 4. Create DB Order & Lock Stock (COD Status: pending)
    $orderResult = $orderRepo->createWithStockLock((int)$_SESSION['user_id'], $itemsForRepo, [
        'customer_name'       => !empty($customerName) ? $customerName : 'Guest Customer',
        'shipping_address_id' => $addressId,
        'billing_address_id'  => $addressId,
        'status'              => 'pending',
        'payment_status'      => 'pending',
        'payment_method'      => 'cod',
        'total_amount'        => $total,
        'subtotal'            => $subtotal,
        'shipping_charges'    => $shipping,
        'discount_amount'     => $discount
    ]);

    if (!$orderResult['success']) {
        throw new Exception("Stock unavailable: " . $orderResult['error']);
    }

    $orderId = $orderResult['order_id'];

    // 5. Clear cart
    $cartService->clearCart();
    unset($_SESSION['checkout_data']);

    echo json_encode([
        'success' => true,
        'order_id' => $orderId,
        'message' => 'Order placed successfully via COD'
    ]);

} catch (Exception $e) {
    error_log("COD Order Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
