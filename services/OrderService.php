<?php
/**
 * Sweets Website
 * =============================================================
 * File: OrderService.php
 * Description: Industrial Order Lifecycle & State Machine
 * Author: Antigravity - Senior Backend Engineer
 * Version: 2.1.0
 * =============================================================
 */

require_once REPOS_PATH . '/OrderRepository.php';
require_once SERVICES_PATH . '/InventoryService.php';
require_once SERVICES_PATH . '/AuditService.php';

class OrderService {
    private OrderRepository $orderRepo;
    private InventoryService $inventory;
    private AuditService $audit;

    public function __construct() {
        $this->orderRepo = new OrderRepository();
        $this->inventory = new InventoryService();
        $this->audit     = new AuditService();
    }

    /**
     * Create new order and reserve stock
     */
    public function createOrder(array $data): array {
        $db = Database::getInstance();
        $db->beginTransaction();

        try {
            // 1. Create order
            $orderId = $this->orderRepo->create($data);
            $this->orderRepo->addItems($orderId, $data['items']);

            // 2. Reserve stock (Pre-payment)
            foreach ($data['items'] as $item) {
                if (isset($item['type']) && $item['type'] === 'combo') {
                    // It's a combo, reserve stock for child items
                    if (!empty($item['items'])) {
                        foreach ($item['items'] as $child) {
                            $childQty = (int)($child['quantity'] ?? 1);
                            $totalChildQty = $childQty * (int)$item['quantity'];
                            $childPid = $child['product_id'];
                            if (!$this->inventory->reserveStock($childPid, $totalChildQty)) {
                                throw new Exception("Insufficient stock for combo child product ID: $childPid");
                            }
                        }
                    }
                } else {
                    $pid = $item['product_id'] ?? $item['id'];
                    if (!$this->inventory->reserveStock($pid, (int)$item['quantity'])) {
                        throw new Exception("Insufficient stock for product ID: $pid");
                    }
                }
            }

            // 3. Auto-create a pending shipment row so the delivery dashboard
            //    always has 100% coverage without requiring a manual backfill.
            try {
                $destination = '';
                if (!empty($data['shipping_city']) || !empty($data['shipping_state'])) {
                    $destination = trim(
                        ($data['shipping_city'] ?? '') . ', ' . ($data['shipping_state'] ?? ''),
                        ', '
                    );
                }
                $db->prepare(
                    "INSERT IGNORE INTO shipments (order_id, status, destination)
                     VALUES (:oid, 'pending', :dest)"
                )->execute([':oid' => $orderId, ':dest' => $destination]);
            } catch (\Exception $shipEx) {
                // Non-fatal: log but don't abort the order
                error_log('[OrderService] Could not auto-create shipment: ' . $shipEx->getMessage());
            }

            $db->commit();
            $this->audit->log('order', $orderId, 'create', null, $data);

            return ['success' => true, 'order_id' => $orderId];

        } catch (Exception $e) {
            $db->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Strict State Machine Transition
     * PENDING -> PAID (Commit Stock)
     * PAID -> SHIPPED
     * SHIPPED -> DELIVERED
     * PENDING -> CANCELLED (Release Stock)
     * PAID -> CANCELLED (Refund flow)
     */
    public function transitionStatus(int $orderId, string $newStatus): array {
        $order = $this->orderRepo->getById($orderId);
        if (!$order) return ['success' => false, 'message' => 'Order not found'];

        $currentStatus = $order['status'];

        // 1. Validate State Transition
        if (!$this->isValidTransition($currentStatus, $newStatus)) {
            return ['success' => false, 'message' => "Invalid transition: $currentStatus to $newStatus"];
        }

        $db = Database::getInstance();
        $db->beginTransaction();

        try {
            // 2. Execute side-effects
            if ($newStatus === 'paid') {
                $this->finalizePayment($orderId);
            } elseif ($newStatus === 'cancelled') {
                $this->cancelOrder($orderId, $currentStatus);
            }

            // 3. Update DB
            $this->orderRepo->update($orderId, ['status' => $newStatus]);

            $db->commit();
            $this->audit->log('order', $orderId, 'status_change', null, ['from' => $currentStatus, 'to' => $newStatus]);

            return ['success' => true];

        } catch (Exception $e) {
            $db->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Compile dashboard statistics for orders — delegates to a single SQL query.
     */
    public function getOrderStats(): array {
        return $this->orderRepo->getStats();
    }

    /**
     * Get full order details with items for the detail view / API.
     */
    public function getOrderDetails(int $id): ?array {
        $order = $this->orderRepo->getById($id);
        if (!$order) {
            return null;
        }

        // --- ENHANCED ADDRESS FALLBACK LOGIC ---
        // If shipping_line1 is missing, try to fetch the user's default shipping address
        if (empty($order['shipping_line1'])) {
            try {
                $db = Database::getInstance();
                $stmt = $db->prepare("SELECT recipient_name, address_line1, address_line2, city, state, zip_code, country, phone 
                                     FROM addresses WHERE user_id = ? AND type = 'shipping' 
                                     ORDER BY is_default DESC, id DESC LIMIT 1");
                $stmt->execute([$order['user_id']]);
                $fallbackAddr = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($fallbackAddr) {
                    $order['shipping_recipient'] = $fallbackAddr['recipient_name'];
                    $order['shipping_line1']     = $fallbackAddr['address_line1'];
                    $order['shipping_line2']     = $fallbackAddr['address_line2'];
                    $order['shipping_city']      = $fallbackAddr['city'];
                    $order['shipping_state']     = $fallbackAddr['state'];
                    $order['shipping_zip']       = $fallbackAddr['zip_code'];
                    $order['shipping_country']   = $fallbackAddr['country'];
                    $order['shipping_phone']     = $fallbackAddr['phone'];
                }
            } catch (\Exception $e) {
                error_log("[OrderService] Fallback address fetch failed: " . $e->getMessage());
            }
        }

        // Harmonize Name: Prioritize shipping recipient, then customer name from DB, then fallback.
        $harmonizedName = $order['shipping_recipient'] 
            ?? $order['customer_name'] 
            ?? ($order['full_name'] ?? 'Customer');
        
        $order['full_name'] = $harmonizedName;
        $order['customer_name'] = $harmonizedName; // For compatibility with Admin Panel

        // Construct Full Address from fragments for the 'address' key
        $addrParts = array_filter([
            $order['shipping_line1'] ?? '',
            $order['shipping_line2'] ?? '',
            $order['shipping_city'] ?? '',
            $order['shipping_state'] ?? '',
            $order['shipping_zip'] ?? '',
            $order['shipping_country'] ?? ''
        ], function($v) { 
            return trim((string)$v) !== '' && strtolower(trim((string)$v)) !== 'undefined'; 
        });

        $order['address'] = !empty($addrParts) ? implode(', ', $addrParts) : null;

        // Harmonize Phone: Prioritize shipping phone, then customer phone, then generic fallback.
        $harmonizedPhone = $order['shipping_phone'] 
            ?? $order['customer_phone'] 
            ?? ($order['phone'] ?? '+91 00000 00000');
            
        $order['phone'] = $harmonizedPhone;
        $order['customer_phone'] = $harmonizedPhone; // For compatibility with Admin Panel

        $order['items'] = $this->orderRepo->getItemsByOrderId($id);
        return $order;
    }

    /**
     * Get customer-facing order listing with filters
     */
    public function getCustomerOrders(int $userId, array $filters = []): array {
        try {
            $orders = $this->orderRepo->getFilteredOrdersByUserId($userId, $filters);
            foreach ($orders as &$order) {
                $order['status'] = $this->normalizeStatus((string)($order['status'] ?? 'pending'));
            }
            unset($order);

            return $orders;
        } catch (\Throwable $e) {
            error_log('OrderService::getCustomerOrders failed: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get order counters for customer tabs
     */
    public function getCustomerOrderCounts(int $userId, string $timeRange = 'last_6_months'): array {
        try {
            return $this->orderRepo->getOrderCountsByUserId($userId, $timeRange);
        } catch (\Throwable $e) {
            error_log('OrderService::getCustomerOrderCounts failed: ' . $e->getMessage());
            return [
                'all' => 0,
                'pending' => 0,
                'processing' => 0,
                'shipped' => 0,
                'delivered' => 0,
                'cancelled' => 0,
                'paid' => 0
            ];
        }
    }

    /**
     * Cancel customer order if still cancellable
     */
    public function cancelCustomerOrder(int $userId, int $orderId): array {
        if ($orderId <= 0) {
            return ['success' => false, 'message' => 'Invalid order selected.'];
        }

        try {
            $order = $this->orderRepo->getById($orderId);
            if (!$order || (int)$order['user_id'] !== $userId) {
                return ['success' => false, 'message' => 'Order not found.'];
            }

            $status = $this->normalizeStatus((string)($order['status'] ?? ''));
            if (!in_array($status, ['pending', 'paid'], true)) {
                return ['success' => false, 'message' => 'Only pending orders can be cancelled.'];
            }

            $isCancelled = $this->orderRepo->cancelOrderByUser($orderId, $userId);
            if (!$isCancelled) {
                return ['success' => false, 'message' => 'Unable to cancel this order right now.'];
            }

            $this->audit->log('order', $orderId, 'cancelled_by_customer', ['status' => $status], ['status' => 'cancelled']);

            return ['success' => true, 'message' => 'Order cancelled successfully.'];
        } catch (\Throwable $e) {
            error_log('OrderService::cancelCustomerOrder failed: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Something went wrong while cancelling your order.'];
        }
    }

    public function getOrderTracking(int $orderId): array {
        return $this->orderRepo->getOrderTracking($orderId);
    }

    private function normalizeStatus(string $status): string {
        $normalized = strtolower(trim($status));
        if ($normalized === 'paid') {
            return 'processing';
        }
        return $normalized;
    }

    private function isValidTransition(string $from, string $to): bool {
        $allowed = [
            'pending' => ['paid', 'cancelled'],
            'paid'    => ['shipped', 'cancelled'],
            'shipped' => ['delivered'],
            'delivered' => [],
            'cancelled' => []
        ];

        return in_array($to, $allowed[$from] ?? []);
    }

    private function finalizePayment(int $orderId): void {
        $items = $this->orderRepo->getItemsByOrderId($orderId);
        foreach ($items as $item) {
            if (isset($item['item_type']) && $item['item_type'] === 'combo' && !empty($item['combo_id'])) {
                $db = Database::getInstance();
                $stmt = $db->prepare("SELECT product_id, quantity FROM combo_items WHERE combo_id = ?");
                $stmt->execute([$item['combo_id']]);
                $children = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($children as $child) {
                    $this->inventory->finalizeStock((int)$child['product_id'], (int)$child['quantity'] * (int)$item['quantity']);
                }
            } else if (!empty($item['product_id'])) {
                $this->inventory->finalizeStock((int)$item['product_id'], (int)$item['quantity']);
            }
        }
    }

    private function cancelOrder(int $orderId, string $currentStatus): void {
        if ($currentStatus === 'pending') {
            // Release reserved stock back to physical
            $items = $this->orderRepo->getItemsByOrderId($orderId);
            foreach ($items as $item) {
                if (isset($item['item_type']) && $item['item_type'] === 'combo' && !empty($item['combo_id'])) {
                    $db = Database::getInstance();
                    $stmt = $db->prepare("SELECT product_id, quantity FROM combo_items WHERE combo_id = ?");
                    $stmt->execute([$item['combo_id']]);
                    $children = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($children as $child) {
                        $this->inventory->releaseStock((int)$child['product_id'], (int)$child['quantity'] * (int)$item['quantity']);
                    }
                } else if (!empty($item['product_id'])) {
                    $this->inventory->releaseStock((int)$item['product_id'], (int)$item['quantity']);
                }
            }
        }
        // If PAID, handle refund placeholder
    }

    public function deleteOrder(int $orderId): bool {
        return $this->orderRepo->delete($orderId);
    }

    public function updateOrder(int $id, array $data): bool {
        return $this->orderRepo->update($id, $data);
    }
}
