<?php
/**
 * Sweets Website
 * =============================================================
 * File: place-order.php
 * Description: Controller to process order placement
 *              — with real-time stock revalidation & atomic decrement
 * Version: 2.0.0
 * =============================================================
 */

require_once 'config/config.php';
require_once SERVICES_PATH  . '/CartService.php';
require_once SERVICES_PATH  . '/OrderService.php';
require_once SERVICES_PATH  . '/AddressService.php';
require_once REPOS_PATH     . '/StockRepository.php';

$cartService  = new CartService();
$orderService = new OrderService();
$stockRepo    = new StockRepository();

$cartItems = $cartService->getItems();
$subtotal  = $cartService->getSubtotal();

if (empty($cartItems)) {
    header('Location: shopping-cart.php');
    exit;
}

$userId = $_SESSION['user_id'] ?? 1;

// ── 1. Revalidate every cart item's stock before proceeding ──
$failedIds = $stockRepo->validateCartStock(array_values($cartItems));
if (!empty($failedIds)) {
    $_SESSION['cart_stock_error'] = 'Some items in your cart are no longer available in the requested quantity. Please review your cart.';
    header('Location: shopping-cart.php');
    exit;
}

// ── 2. Calculate totals ───────────────────────────────────────
$shipping = ($subtotal > 0 && $subtotal < 999) ? 50 : 0;
$discount = $subtotal > 1500 ? 100 : 0;
$total    = $subtotal + $shipping - $discount;

// ── 3. Order number ───────────────────────────────────────────
$orderNumber = 'SW-ORD-' . strtoupper(substr(uniqid(), -6)) . '-' . date('Ymd');

// ── 4. Resolve or save shipping address ───────────────────────
$addressId   = null;
$addrService = new AddressService();

if (!empty($_SESSION['checkout_data'])) {
    $cd  = $_SESSION['checkout_data'];
    $db  = Database::getInstance();
    $sql = "INSERT INTO addresses
                (user_id, recipient_name, type, address_line1, city, state, zip_code, country, phone)
            VALUES (:uid, :name, 'shipping', :line1, :city, :state, :zip, :country, :phone)";
    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':uid'     => $userId,
        ':name'    => trim(($cd['first_name'] ?? '') . ' ' . ($cd['last_name'] ?? '')),
        ':line1'   => $cd['address']  ?? '',
        ':city'    => $cd['city']     ?? '',
        ':state'   => $cd['state']    ?? '',
        ':zip'     => $cd['pin_code'] ?? '',
        ':country' => $cd['country']  ?? 'India',
        ':phone'   => $cd['phone']    ?? '',
    ]);
    $addressId = (int)$db->lastInsertId();
    unset($_SESSION['checkout_data']);
} else {
    $addresses = $addrService->getAddressesByUser($userId);
    foreach ($addresses as $a) {
        if ($a['is_default']) { $addressId = $a['id']; break; }
    }
    if (!$addressId && count($addresses) > 0) {
        $addressId = $addresses[0]['id'];
    }
}

// ── 5. Create the order ───────────────────────────────────────
$orderData = [
    'user_id'             => $userId,
    'order_number'        => $orderNumber,
    'total_amount'        => $total,
    'status'              => 'pending',
    'payment_status'      => 'paid',
    'shipping_address_id' => $addressId,
    'billing_address_id'  => $addressId,
    'items'               => $cartItems,
];

$result = $orderService->createOrder($orderData);

if ($result['success']) {
    $orderId = $result['order_id'];

    // ── 6. Atomically decrement stock for every ordered item ──
    foreach ($cartItems as $item) {
        $pid = (int)($item['id'] ?? 0);
        $qty = (int)($item['quantity'] ?? 1);
        if ($pid > 0) {
            $ok = $stockRepo->decrementStock($pid, $qty);
            if (!$ok) {
                // Log — order is placed; admin can investigate via low-stock alerts
                error_log("[place-order] Stock decrement failed for product #{$pid} (qty {$qty})");
            }
        }
    }

    $cartService->clearCart();
    header("Location: order-success.php?order_id=" . $orderId);
    exit;
} else {
    die("Failed to place order: " . ($result['message'] ?? "Unknown error"));
}
