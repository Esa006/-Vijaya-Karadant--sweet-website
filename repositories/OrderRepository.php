<?php
/**
 * Sweets Website
 * =============================================================
 * File: repositories/OrderRepository.php
 * Description: High-concurrency Data Access for Orders & Stock
 * Author: Antigravity - Senior Full-Stack Architect
 * Version: 3.0.0
 * =============================================================
 */

require_once __DIR__ . '/BaseRepository.php';

class OrderRepository extends BaseRepository {
    
    public function __construct() {
        parent::__construct();
    }

    /**
     * Fetch order by idempotency key (Razorpay Order ID)
     */
    public function fetchByIdempotencyKey(string $key): ?array {
        return $this->fetchOne(
            "SELECT id, status, shipping_address_id, billing_address_id, customer_name
             FROM orders
             WHERE idempotency_key = :key",
            [':key' => $key]
        );
    }

    /**
     * Create Order with Atomic Stock Deduction (CRITICAL)
     * Uses SELECT ... FOR UPDATE to lock variant rows
     */
    public function createWithStockLock(int $userId, array $items, array $meta = []): array {
        try {
            $this->beginTransaction();

            $totalAmount = 0;
            $orderItems = [];

            // 1. Lock and Verify Stock
            foreach ($items as $item) {
                $isCombo = isset($item['type']) && $item['type'] === 'combo';
                $parentQty = (int)($item['quantity'] ?? 1);
                $itemPrice = (float)($item['price'] ?? 0);

                if ($isCombo) {
                    // Lock all child items within the combo.
                    // Try product_variants first; fall back to products.stock_quantity if no variant exists.
                    foreach ($item['items'] as $child) {
                        $childId       = (int)($child['product_id'] ?? 0);
                        $childQtyNeeded = (int)($child['quantity'] ?? 1) * $parentQty;
                        if ($childId <= 0) continue;

                        // Attempt to lock via product_variants
                        $variant = $this->fetchOne(
                            "SELECT * FROM product_variants WHERE product_id = :pid ORDER BY id ASC LIMIT 1 FOR UPDATE",
                            [':pid' => $childId]
                        );

                        if ($variant) {
                            // Variant row found — use it
                            if ((int)$variant['stock'] < $childQtyNeeded) {
                                throw new Exception("Insufficient stock for combo item: " . ($child['name'] ?? "ID $childId"));
                            }
                            $this->execute(
                                "UPDATE product_variants SET stock = stock - :qty WHERE id = :vid",
                                [':qty' => $childQtyNeeded, ':vid' => $variant['id']]
                            );
                        } else {
                            // No variant row — fall back to products.stock_quantity
                            $product = $this->fetchOne(
                                "SELECT id, name, stock_quantity FROM products WHERE id = :pid FOR UPDATE",
                                [':pid' => $childId]
                            );
                            if (!$product) {
                                // Product not found in DB — skip (combo may have been seeded without a products row)
                                error_log("[OrderRepo] Combo child product ID $childId not found — skipping stock check.");
                                continue;
                            }
                            $availableQty = (int)($product['stock_quantity'] ?? PHP_INT_MAX);
                            if ($availableQty < $childQtyNeeded) {
                                throw new Exception("Insufficient stock for combo item: " . ($product['name'] ?? "Product #$childId"));
                            }
                            if (isset($product['stock_quantity'])) {
                                $this->execute(
                                    "UPDATE products SET stock_quantity = stock_quantity - :qty WHERE id = :pid",
                                    [':qty' => $childQtyNeeded, ':pid' => $childId]
                                );
                            }
                        }
                    }

                    $orderItems[] = [
                        'product_id' => null,
                        'combo_id'   => $item['combo_id'],
                        'variant_id' => 0,
                        'item_type'  => 'combo',
                        'quantity'   => $parentQty,
                        'price'      => $itemPrice
                    ];
                } else {
                    // Handle Regular Product
                    $variantId  = (int)($item['variant_id'] ?? 0);
                    $productId  = (int)($item['product_id'] ?? 0);
                    $variant    = null;

                    // Try to find variant by variant_id first
                    if ($variantId > 0) {
                        $variant = $this->fetchOne(
                            "SELECT * FROM product_variants WHERE id = :id FOR UPDATE",
                            [':id' => $variantId]
                        );
                    }

                    // Fallback: find first variant by product_id (covers variant_id = 0 cases)
                    if (!$variant && $productId > 0) {
                        $variant = $this->fetchOne(
                            "SELECT * FROM product_variants WHERE product_id = :pid ORDER BY id ASC LIMIT 1 FOR UPDATE",
                            [':pid' => $productId]
                        );
                    }

                    if ($variant) {
                        // We have a variant row — use it for stock check & deduction
                        if ($variant['stock'] < $parentQty) {
                            throw new Exception("Insufficient stock for " . ($variant['label'] ?? 'this item'));
                        }
                        $this->execute(
                            "UPDATE product_variants SET stock = stock - :qty WHERE id = :id",
                            [':qty' => $parentQty, ':id' => $variant['id']]
                        );
                        $orderItems[] = [
                            'product_id' => $variant['product_id'],
                            'combo_id'   => null,
                            'variant_id' => $variant['id'],
                            'item_type'  => 'product',
                            'quantity'   => $parentQty,
                            'price'      => $itemPrice > 0 ? $itemPrice : (float)$variant['price']
                        ];
                    } elseif ($productId > 0) {
                        // No variant row at all — fall back to products.stock_quantity
                        $product = $this->fetchOne(
                            "SELECT id, name, stock_quantity FROM products WHERE id = :pid FOR UPDATE",
                            [':pid' => $productId]
                        );
                        if (!$product) {
                            throw new Exception("Product ID $productId not found.");
                        }
                        $availableQty = (int)($product['stock_quantity'] ?? 0);
                        if ($availableQty < $parentQty) {
                            throw new Exception("Insufficient stock for " . ($product['name'] ?? "Product #$productId"));
                        }
                        $this->execute(
                            "UPDATE products SET stock_quantity = stock_quantity - :qty WHERE id = :pid",
                            [':qty' => $parentQty, ':pid' => $productId]
                        );
                        $orderItems[] = [
                            'product_id' => $productId,
                            'combo_id'   => null,
                            'variant_id' => 0,
                            'item_type'  => 'product',
                            'quantity'   => $parentQty,
                            'price'      => $itemPrice
                        ];
                    } else {
                        throw new Exception("Cannot resolve product for cart item (variant_id=$variantId, product_id=$productId).");
                    }
                }
                
                $totalAmount += ($itemPrice * $parentQty);
            }

            $orderNumber = $this->generateOrderNumber();
            $idempotencyKey = $meta['idempotency_key'] ?? null;
            
            // Check Idempotency Key FIRST to prevent double charging/processing
            if ($idempotencyKey) {
                $existingOrder = $this->fetchOne("SELECT id FROM orders WHERE idempotency_key = :key", [':key' => $idempotencyKey]);
                if ($existingOrder) {
                    $this->rollBack();
                    return [
                        'success' => true,
                        'order_id' => $existingOrder['id'],
                        'total' => $totalAmount,
                        'idempotent_hit' => true // Flag to indicate this was a cached response
                    ];
                }
            }

            $orderSql = "INSERT INTO orders
                            (user_id, order_number, idempotency_key, total_amount, subtotal, shipping_charges, discount_amount, status, shipping_address_id, billing_address_id, customer_name, created_at)
                         VALUES
                            (:user_id, :order_number, :idempotency_key, :total, :subtotal, :shipping, :discount, :status, :shipping_address_id, :billing_address_id, :name, NOW())";
            $orderStatus = (string)($meta['status'] ?? 'pending');
            $orderId = $this->executeInsert($orderSql, [
                ':user_id'      => $userId,
                ':order_number' => $orderNumber,
                ':idempotency_key' => $idempotencyKey,
                ':total'        => $meta['total_amount'] ?? $totalAmount,
                ':subtotal'     => $meta['subtotal'] ?? $totalAmount,
                ':shipping'     => $meta['shipping_charges'] ?? 0,
                ':discount'     => $meta['discount_amount'] ?? 0,
                ':status'       => $orderStatus,
                ':shipping_address_id' => $meta['shipping_address_id'] ?? null,
                ':billing_address_id'  => $meta['billing_address_id'] ?? null,
                ':name'         => $meta['customer_name'] ?? null
            ]);

            // 3. Insert Order Items
            foreach ($orderItems as $oi) {
                $itemSql = "INSERT INTO order_items (order_id, product_id, combo_id, variant_id, item_type, quantity, price, price_at_time) 
                           VALUES (:order_id, :product_id, :combo_id, :variant_id, :type, :qty, :price, :price_at)";
                $this->execute($itemSql, [
                    ':order_id'   => $orderId,
                    ':product_id' => $oi['product_id'],
                    ':combo_id'   => $oi['combo_id'],
                    ':variant_id' => $oi['variant_id'],
                    ':type'       => $oi['item_type'],
                    ':qty'        => $oi['quantity'],
                    ':price'      => $oi['price'],
                    ':price_at'   => $oi['price']
                ]);
            }

            $this->commit();

            return [
                'success'  => true,
                'order_id' => $orderId,
                'total'    => $totalAmount
            ];

        } catch (Exception $e) {
            $this->rollBack();
            return [
                'success' => false,
                'error'   => $e->getMessage()
            ];
        }
    }

    /**
     * Expire abandoned checkouts and release inventory locks
     */
    public function expirePendingReservations(int $minutes = 10): int {
        try {
            $this->beginTransaction();

            // 1. Find all abandoned pending orders
            $sql = "SELECT id FROM orders 
                    WHERE status = 'pending' 
                    AND created_at <= DATE_SUB(NOW(), INTERVAL :mins MINUTE) 
                    FOR UPDATE";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':mins', $minutes, PDO::PARAM_INT);
            $stmt->execute();
            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($orders)) {
                $this->rollBack();
                return 0;
            }

            $orderIds = array_column($orders, 'id');
            $expiredCount = 0;

            // 2. Restore stock for each order's items
            foreach ($orderIds as $orderId) {
                $items = $this->fetchAll(
                    "SELECT product_id, variant_id, quantity FROM order_items WHERE order_id = :oid",
                    [':oid' => $orderId]
                );

                foreach ($items as $item) {
                    $qty = (int)$item['quantity'];
                    $vId = (int)($item['variant_id'] ?? 0);
                    $pId = (int)($item['product_id'] ?? 0);

                    if ($vId > 0) {
                        $this->execute(
                            "UPDATE product_variants SET stock = stock + :qty WHERE id = :vid",
                            [':qty' => $qty, ':vid' => $vId]
                        );
                    } elseif ($pId > 0) {
                        $this->execute(
                            "UPDATE products SET stock_quantity = stock_quantity + :qty WHERE id = :pid",
                            [':qty' => $qty, ':pid' => $pId]
                        );
                    }
                }
                $expiredCount++;
            }

            // 3. Mark orders as expired
            $placeholders = str_repeat('?,', count($orderIds) - 1) . '?';
            $updateSql = "UPDATE orders SET status = 'expired', updated_at = NOW() WHERE id IN ($placeholders)";
            
            $stmt = $this->db->prepare($updateSql);
            $stmt->execute($orderIds);

            $this->commit();
            return $expiredCount;

        } catch (Exception $e) {
            $this->rollBack();
            error_log("[OrderRepo] Expire Reservations Error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Update Order Status after Payment Verification
     */
    public function markAsPaid(int $orderId, string $paymentId, string $method): bool {
        $sql = "UPDATE orders SET status = 'paid', payment_status = 'paid', payment_id = :pay_id, payment_method = :method 
                WHERE id = :id";
        return $this->execute($sql, [
            ':id'     => $orderId,
            ':pay_id' => $paymentId,
            ':method' => $method
        ]);
    }

    /**
     * Log Payment Audit
     */
    public function logPayment(array $data): int {
        $sql = "INSERT INTO payments (order_id, gateway, transaction_id, amount, status, raw_response) 
                VALUES (:order_id, :gateway, :txn_id, :amount, :status, :raw)";
        return $this->executeInsert($sql, [
            ':order_id' => $data['order_id'],
            ':gateway'  => $data['gateway'],
            ':txn_id'   => $data['txn_id'],
            ':amount'   => $data['amount'],
            ':status'   => $data['status'],
            ':raw'      => $data['raw'] ?? null
        ]);
    }

    /**
     * Get Order Statistics for Admin Dashboard
     */
    public function getStats(): array {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status IN ('paid', 'processing', 'shipped', 'delivered') THEN total_amount ELSE 0 END) as revenue,
                    COUNT(CASE WHEN status IN ('pending', 'paid') THEN 1 END) as pending,
                    COUNT(CASE WHEN status = 'processing' THEN 1 END) as processing,
                    COUNT(CASE WHEN status = 'delivered' THEN 1 END) as delivered
                FROM orders";
        return $this->fetchOne($sql) ?? [
            'total' => 0,
            'revenue' => 0,
            'pending' => 0,
            'processing' => 0,
            'delivered' => 0
        ];
    }

    /**
     * Get all orders with customer details for management
     */
    public function getAllOrders(int $limit = 100): array {
        $sql = "SELECT o.*, 
                       COALESCE(o.customer_name, u.full_name, 'Guest Customer') as customer_name,
                       u.email as customer_email,
                       (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count
                FROM orders o
                LEFT JOIN users u ON o.user_id = u.id
                ORDER BY o.created_at DESC
                LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Bulk update order statuses (Single Query)
     */
    public function bulkUpdate(array $ids, string $status): int {
        if (empty($ids)) return 0;
        
        $placeholders = str_repeat('?,', count($ids) - 1) . '?';
        $params = [$status];
        
        foreach ($ids as $id) $params[] = (int)$id;

        $sql = "UPDATE orders SET status = ? WHERE id IN ($placeholders)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    /**
     * Get a list of shipments for the delivery dashboard
     */
    public function getShipmentOverview(int $limit = 100): array {
        $sql = "SELECT 
                    o.id, 
                    o.order_number, 
                    o.total_amount, 
                    o.status, 
                    o.created_at, 
                    o.updated_at,
                    u.full_name as customer_name,
                    a.address_line1, 
                    a.address_line2, 
                    a.city, 
                    a.state
                FROM orders o
                LEFT JOIN users u ON o.user_id = u.id
                LEFT JOIN addresses a ON o.shipping_address_id = a.id
                ORDER BY o.created_at DESC
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get orders for a specific user with item summary
     */
    public function getOrdersByUserId(int $userId, int $limit = 20): array {
        $sql = "SELECT o.*, 
                (SELECT GROUP_CONCAT(COALESCE(p.name, c.name) SEPARATOR ', ') 
                 FROM order_items oi 
                 LEFT JOIN products p ON oi.product_id = p.id 
                 LEFT JOIN combos c ON oi.combo_id = c.id 
                 WHERE oi.order_id = o.id) as product_names,
                (SELECT COALESCE(pi.image_path, c.image) 
                 FROM order_items oi 
                 LEFT JOIN products p ON oi.product_id = p.id 
                 LEFT JOIN combos c ON oi.combo_id = c.id
                 LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_main = 1 
                 WHERE oi.order_id = o.id LIMIT 1) as main_image
                FROM orders o 
                WHERE o.user_id = :uid 
                ORDER BY o.created_at DESC 
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get customer orders with optional status/search/time filters
     */
    public function getFilteredOrdersByUserId(int $userId, array $filters = []): array {
        $status = strtolower(trim((string)($filters['status'] ?? 'all')));
        $search = trim((string)($filters['search'] ?? ''));
        $timeRange = strtolower(trim((string)($filters['time_range'] ?? 'last_6_months')));
        $limit = (int)($filters['limit'] ?? 50);
        $offset = (int)($filters['offset'] ?? 0);

        $validStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled', 'paid'];
        if (!in_array($status, $validStatuses, true)) {
            $status = 'all';
        }

        $validRanges = ['last_1_month', 'last_3_months', 'last_6_months', 'all_time'];
        if (!in_array($timeRange, $validRanges, true)) {
            $timeRange = 'last_6_months';
        }

        $limit = max(1, min(100, $limit));
        $offset = max(0, $offset);

        $where = ['o.user_id = :uid'];
        $params = [':uid' => $userId];
        $types = [':uid' => PDO::PARAM_INT];

        if ($status !== 'all') {
            if ($status === 'processing') {
                $where[] = "LOWER(o.status) IN ('processing', 'paid')";
            } else {
                $where[] = 'LOWER(o.status) = :status';
                $params[':status'] = $status;
                $types[':status'] = PDO::PARAM_STR;
            }
        }

        if ($search !== '') {
            $where[] = '(o.order_number LIKE :search OR p.name LIKE :search OR CAST(o.id AS CHAR) LIKE :search)';
            $params[':search'] = '%' . $search . '%';
            $types[':search'] = PDO::PARAM_STR;
        }

        if ($timeRange !== 'all_time') {
            $fromDate = date('Y-m-d H:i:s', strtotime('-6 months'));
            if ($timeRange === 'last_3_months') {
                $fromDate = date('Y-m-d H:i:s', strtotime('-3 months'));
            } elseif ($timeRange === 'last_1_month') {
                $fromDate = date('Y-m-d H:i:s', strtotime('-1 month'));
            }

            $where[] = 'o.created_at >= :fromDate';
            $params[':fromDate'] = $fromDate;
            $types[':fromDate'] = PDO::PARAM_STR;
        }

        $sql = "SELECT 
                    o.*,
                    COALESCE(NULLIF(GROUP_CONCAT(DISTINCT COALESCE(p.name, c.name) ORDER BY COALESCE(p.name, c.name) SEPARATOR ', '), ''), 'Custom Sweets Box') AS product_names,
                    MAX(CASE WHEN pi.is_main = 1 THEN pi.image_path ELSE c.image END) AS main_image,
                    SUM(COALESCE(oi.quantity, 0)) AS total_items
                FROM orders o
                LEFT JOIN order_items oi ON oi.order_id = o.id
                LEFT JOIN products p ON p.id = oi.product_id
                LEFT JOIN combos c ON c.id = oi.combo_id
                LEFT JOIN product_images pi ON pi.product_id = oi.product_id
                WHERE " . implode(' AND ', $where) . "
                GROUP BY o.id
                ORDER BY o.created_at DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, $types[$key] ?? PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get customer order counts by status (time-range aware)
     */
    public function getOrderCountsByUserId(int $userId, string $timeRange = 'last_6_months'): array {
        $timeRange = strtolower(trim($timeRange));
        $validRanges = ['last_1_month', 'last_3_months', 'last_6_months', 'all_time'];
        if (!in_array($timeRange, $validRanges, true)) {
            $timeRange = 'last_6_months';
        }

        $where = ['user_id = :uid'];
        $params = [':uid' => $userId];

        if ($timeRange !== 'all_time') {
            $fromDate = date('Y-m-d H:i:s', strtotime('-6 months'));
            if ($timeRange === 'last_3_months') {
                $fromDate = date('Y-m-d H:i:s', strtotime('-3 months'));
            } elseif ($timeRange === 'last_1_month') {
                $fromDate = date('Y-m-d H:i:s', strtotime('-1 month'));
            }
            $where[] = 'created_at >= :fromDate';
            $params[':fromDate'] = $fromDate;
        }

        $sql = "SELECT LOWER(status) AS status, COUNT(*) AS total
                FROM orders
                WHERE " . implode(' AND ', $where) . "
                GROUP BY LOWER(status)";

        $rows = $this->fetchAll($sql, $params);

        $counts = [
            'all' => 0,
            'pending' => 0,
            'processing' => 0,
            'shipped' => 0,
            'delivered' => 0,
            'cancelled' => 0,
            'paid' => 0
        ];

        foreach ($rows as $row) {
            $key = strtolower((string)($row['status'] ?? ''));
            $count = (int)($row['total'] ?? 0);
            if (array_key_exists($key, $counts)) {
                $counts[$key] = $count;
                $counts['all'] += $count;
            }
        }

        return $counts;
    }

    /**
     * Cancel an order only when owned by user and in cancellable status
     */
    public function cancelOrderByUser(int $orderId, int $userId): bool {
        $sql = "UPDATE orders
                SET status = 'cancelled', updated_at = NOW()
                WHERE id = :oid AND user_id = :uid AND LOWER(status) IN ('pending', 'paid')";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':oid', $orderId, PDO::PARAM_INT);
        $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    /**
     * Get order by ID
     */
    public function getById(int $id): ?array {
        $sql = "SELECT o.*, 
                       COALESCE(o.customer_name, u.full_name) as customer_name, 
                       u.email as customer_email, 
                       u.phone as customer_phone,
                       u.created_at as customer_since,
                       sa.recipient_name as shipping_recipient,
                       sa.address_line1 as shipping_line1, 
                       sa.address_line2 as shipping_line2, 
                       sa.city as shipping_city, 
                       sa.state as shipping_state, 
                       sa.zip_code as shipping_zip, 
                       sa.country as shipping_country,
                       sa.phone as shipping_phone,
                       ba.recipient_name as billing_recipient,
                       ba.address_line1 as billing_line1, 
                       ba.address_line2 as billing_line2, 
                       ba.city as billing_city, 
                       ba.state as billing_state, 
                       ba.zip_code as billing_zip, 
                       ba.country as billing_country,
                       ba.phone as billing_phone
                FROM orders o
                LEFT JOIN users u ON o.user_id = u.id
                LEFT JOIN addresses sa ON o.shipping_address_id = sa.id
                LEFT JOIN addresses ba ON o.billing_address_id = ba.id
                WHERE o.id = :id";
        return $this->fetchOne($sql, [':id' => $id]);
    }

    /**
     * Get order items — normalises price_at_time regardless of schema variant
     */
    public function getItemsByOrderId(int $orderId): array {
        try {
            $sql = "SELECT oi.*,
                           COALESCE(oi.price_at_time, oi.price, 0) AS price_at_time,
                           COALESCE(p.name, c.name) as name, 
                           COALESCE(p.slug, c.slug) as slug, 
                           COALESCE(p.sku, 'N/A') as sku,
                           COALESCE(pi.image_path, c.image) as image, 
                           pv.label as variant_label
                    FROM order_items oi
                    LEFT JOIN products p ON oi.product_id = p.id
                    LEFT JOIN combos c ON oi.combo_id = c.id
                    LEFT JOIN product_variants pv ON oi.variant_id = pv.id
                    LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_main = 1
                    WHERE oi.order_id = :oid";
            return $this->fetchAll($sql, [':oid' => $orderId]);
        } catch (\PDOException $e) {
            // Fallback for schema without product_variants
            $sql = "SELECT oi.*,
                           COALESCE(oi.price_at_time, oi.price, 0) AS price_at_time,
                           COALESCE(p.name, c.name) as name, 
                           COALESCE(p.slug, c.slug) as slug, 
                           COALESCE(p.sku, 'N/A') as sku,
                           COALESCE(pi.image_path, c.image) as image
                    FROM order_items oi
                    LEFT JOIN products p ON oi.product_id = p.id
                    LEFT JOIN combos c ON oi.combo_id = c.id
                    LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_main = 1
                    WHERE oi.order_id = :oid";
            return $this->fetchAll($sql, [':oid' => $orderId]);
        }
    }


    /**
     * Update order record
     */
    public function update(int $id, array $data): bool {
        if (empty($data)) return true;
        $fields = [];
        $params = [':id' => $id];
        foreach ($data as $key => $value) {
            $fields[] = "$key = :$key";
            $params[":$key"] = $value;
        }
        $sql = "UPDATE orders SET " . implode(', ', $fields) . " WHERE id = :id";
        return $this->execute($sql, $params);
    }

    /**
     * Delete order
     */
    public function delete(int $id): bool {
        return $this->execute("DELETE FROM orders WHERE id = :id", [':id' => $id]);
    }

    /**
     * Create order master (Simplified version for service)
     */
    public function create(array $data): int {
        $orderNumber = !empty($data['order_number']) ? (string)$data['order_number'] : $this->generateOrderNumber();

        $sql = "INSERT INTO orders (user_id, order_number, total_amount, subtotal, shipping_charges, discount_amount, status, shipping_address_id, billing_address_id)
                VALUES (:user_id, :order_number, :total, :subtotal, :shipping, :discount, :status, :shipping_addr, :billing)";

        return $this->executeInsert($sql, [
            ':user_id'      => $data['user_id'],
            ':order_number' => $orderNumber,
            ':total'        => $data['total_amount'] ?? 0,
            ':subtotal'     => $data['subtotal'] ?? 0,
            ':shipping'     => $data['shipping_charges'] ?? 0,
            ':discount'     => $data['discount_amount'] ?? 0,
            ':status'       => $data['status'] ?? 'pending',
            ':shipping_addr' => $data['shipping_address_id'] ?? null,
            ':billing'      => $data['billing_address_id'] ?? null
        ]);
    }

    /**
     * Generate Unique Order Number
     */
    private function generateOrderNumber(): string {
        return 'SW-' . strtoupper(substr(md5(uniqid((string)mt_rand(), true)), 0, 8)) . '-' . date('Ymd');
    }

    /**
     * Bulk add items to order.
     * Writes price into both `price` and `price_at_time` columns so the
     * order-details view always gets a non-zero value regardless of schema.
     */
    public function addItems(int $orderId, array $items): void {
        foreach ($items as $item) {
            $isCombo = isset($item['type']) && $item['type'] === 'combo';
            $pid   = $isCombo ? null : ($item['product_id'] ?? $item['id']);
            $cid   = $isCombo ? ($item['combo_id'] ?? $item['id']) : null;
            $type  = $isCombo ? 'combo' : 'product';
            $qty   = (int)($item['quantity'] ?? 1);
            $price = (float)($item['price'] ?? 0);
            $vid   = $item['variant_id'] ?? 0;

            try {
                // Schema v3: with item_type and combo_id
                $sql = "INSERT INTO order_items
                            (order_id, product_id, variant_id, quantity, price, price_at_time, item_type, combo_id)
                        VALUES
                            (:oid, :pid, :vid, :qty, :price, :price_at, :type, :cid)
                        ON DUPLICATE KEY UPDATE
                            price = VALUES(price),
                            price_at_time = VALUES(price_at_time)";
                $this->execute($sql, [
                    ':oid'      => $orderId,
                    ':pid'      => $pid,
                    ':vid'      => $vid,
                    ':qty'      => $qty,
                    ':price'    => $price,
                    ':price_at' => $price,
                    ':type'     => $type,
                    ':cid'      => $cid
                ]);
            } catch (\PDOException $e) {
                // Fallback for older schemas (ignoring combos for older schemas that don't support it)
                if ($isCombo) continue; 
                
                try {
                    $sql = "INSERT INTO order_items (order_id, product_id, quantity, price_at_time)
                            VALUES (:oid, :pid, :qty, :price)";
                    $this->execute($sql, [
                        ':oid'   => $orderId,
                        ':pid'   => $pid,
                        ':qty'   => $qty,
                        ':price' => $price,
                    ]);
                } catch (\PDOException $e2) {
                    $sql = "INSERT INTO order_items (order_id, product_id, quantity, price)
                            VALUES (:oid, :pid, :qty, :price)";
                    $this->execute($sql, [
                        ':oid'   => $orderId,
                        ':pid'   => $pid,
                        ':qty'   => $qty,
                        ':price' => $price,
                    ]);
                }
            }
        }
    }


    /**
     * Get shipment details for a specific order
     */
    public function getShipmentDetails(int $orderId): ?array {
        try {
            $sql = "SELECT * FROM shipments WHERE order_id = :oid LIMIT 1";
            return $this->fetchOne($sql, [':oid' => $orderId]);
        } catch (\PDOException $e) {
            return null;
        }
    }

    /**
     * Get delivery tracking timeline for a specific order
     */
    public function getDeliveryTimeline(int $orderId): array {
        try {
            $sql = "SELECT * FROM delivery_tracking WHERE order_id = :oid ORDER BY created_at DESC, id DESC";
            return $this->fetchAll($sql, [':oid' => $orderId]);
        } catch (\PDOException $e) {
            return [];
        }
    }

    /**
     * Add a delivery tracking event
     */
    public function addDeliveryTracking(int $orderId, string $status, string $description = '', string $location = ''): bool {
        try {
            // Map input status (usually uppercase, e.g. PACKED, OUT_FOR_DELIVERY) to orders status enum (lowercase)
            $dbStatus = strtolower($status);
            if ($dbStatus === 'packed') {
                $dbStatus = 'processing';
            } elseif ($dbStatus === 'out_for_delivery') {
                $dbStatus = 'shipped';
            }
            
            // Validate that the status is a valid enum value for orders table
            $validEnumValues = ['pending', 'paid', 'processing', 'shipped', 'delivered', 'cancelled', 'failed'];
            if (in_array($dbStatus, $validEnumValues, true)) {
                // Update the order status
                $this->execute("UPDATE orders SET status = :status WHERE id = :oid", [
                    ':status' => $dbStatus,
                    ':oid' => $orderId
                ]);
            }
            
            // Insert the tracking event
            $sql = "INSERT INTO delivery_tracking (order_id, status, description, location) VALUES (:oid, :status, :desc, :location)";
            return $this->execute($sql, [
                ':oid' => $orderId,
                ':status' => strtoupper($status),
                ':desc' => $description,
                ':location' => $location
            ]);
        } catch (\PDOException $e) {
            error_log("[OrderRepository] addDeliveryTracking failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Assign shipment to order
     */
    public function assignShipment(int $orderId, array $data): bool {
        try {
            // Check if exists
            $existing = $this->getShipmentDetails($orderId);
            
            if ($existing) {
                $sql = "UPDATE shipments SET courier_name = :courier, tracking_id = :tracking, estimated_delivery = :est_del, dispatch_date = NOW() WHERE order_id = :oid";
            } else {
                $sql = "INSERT INTO shipments (order_id, courier_name, tracking_id, estimated_delivery, dispatch_date) VALUES (:oid, :courier, :tracking, :est_del, NOW())";
            }
            
            $result = $this->execute($sql, [
                ':oid' => $orderId,
                ':courier' => $data['courier_name'],
                ':tracking' => $data['tracking_id'],
                ':est_del' => $data['estimated_delivery']
            ]);
            
            if ($result) {
                // Keep orders table columns in sync with shipments table
                $this->execute("UPDATE orders SET tracking_id = :tracking, delivery_partner = :courier, estimated_delivery_date = :est_del WHERE id = :oid", [
                    ':tracking' => $data['tracking_id'],
                    ':courier' => $data['courier_name'],
                    ':est_del' => $data['estimated_delivery'],
                    ':oid' => $orderId
                ]);

                // Also add a timeline event automatically
                $this->addDeliveryTracking($orderId, 'SHIPPED', "Handed over to " . $data['courier_name'] . ". Tracking #" . $data['tracking_id'], "Warehouse");
            }
            
            return $result;
        } catch (\PDOException $e) {
            error_log("[OrderRepository] assignShipment failed: " . $e->getMessage());
            return false;
        }
    }

    public function getOrderTracking(int $orderId): array {
        $sql = "SELECT * FROM delivery_tracking WHERE order_id = :order_id ORDER BY created_at DESC";
        return $this->fetchAll($sql, [':order_id' => $orderId]);
    }
}
