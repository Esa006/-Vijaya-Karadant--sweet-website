<?php
declare(strict_types=1);
/**
 * Sweets Website
 * =============================================================
 * File: CartService.php
 * Description: Business logic for managing the shopping cart
 * Author: Sweets Website Team
 * Version: 1.0.0
 * =============================================================
 */

class CartService {
    private ?PDO $db = null;
    private ?int $customerCartId = null;
    
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        if (defined('ROOT_PATH')) {
            require_once ROOT_PATH . '/config/Database.php';
            $this->db = Database::getInstance();
        }
    }
    public function getAvailableStockForItem(array $cartItem): ?int {
        if (!$this->db) {
            return null;
        }

        // Handle Combo Stock
        if (isset($cartItem['type']) && $cartItem['type'] === 'combo') {
            if (empty($cartItem['items'])) return 0;
            
            $maxComboStock = PHP_INT_MAX;
            foreach ($cartItem['items'] as $child) {
                $childQty = (int)($child['quantity'] ?? 1);
                $childItemMock = ['id' => $child['product_id'], 'variant_id' => 0];
                $childStock = $this->getAvailableStockForItem($childItemMock);
                
                if ($childStock === null) continue; // Unmanaged stock
                
                if ($childStock < $childQty) {
                    return 0; // Out of stock
                }
                
                $possibleCombos = floor($childStock / $childQty);
                if ($possibleCombos < $maxComboStock) {
                    $maxComboStock = (int)$possibleCombos;
                }
            }
            return $maxComboStock === PHP_INT_MAX ? null : $maxComboStock;
        }

        $productId = (int)($cartItem['id'] ?? 0);
        $variantId = (int)($cartItem['variant_id'] ?? 0);

        try {
            if ($variantId > 0) {
                $stmt = $this->db->prepare('SELECT stock FROM product_variants WHERE id = :id LIMIT 1');
                $stmt->execute([':id' => $variantId]);
                $stock = $stmt->fetchColumn();
                if ($stock !== false) {
                    return max(0, (int)$stock);
                }
            }

            if ($productId > 0) {
                $stmt = $this->db->prepare('SELECT stock FROM inventory WHERE product_id = :pid LIMIT 1');
                $stmt->execute([':pid' => $productId]);
                $stock = $stmt->fetchColumn();
                if ($stock !== false) {
                    return max(0, (int)$stock);
                }
            }
        } catch (Throwable $e) {
            error_log('[CartService] stock check failed: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Helper to generate consistent cart keys
     */
    private function getCartKey($slug, $weight) {
        return $slug . '-' . preg_replace('/[^a-zA-Z0-9]/', '', $weight);
    }

    /**
     * Resolve a safe image path for cart items.
     */
    private function resolveCartImage(array $product): string {
        $candidates = [
            $product['image_path'] ?? '',
            $product['image'] ?? '',
            $product['main_image'] ?? '',
            $product['thumbnail'] ?? ''
        ];

        foreach ($candidates as $candidate) {
            $image = trim((string)$candidate);
            if ($image === '') {
                continue;
            }

            $normalizedImage = str_replace('\\', '/', $image);

            if (preg_match('/^https?:\/\//i', $normalizedImage)) {
                return $normalizedImage;
            }

            if (defined('ROOT_PATH')) {
                $absolutePath = ROOT_PATH . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, ltrim($normalizedImage, '/'));
                if (is_file($absolutePath)) {
                    return $normalizedImage;
                }
            } else {
                return $normalizedImage;
            }
        }

        return 'assets/images/placeholder.png';
    }

    private function canSyncCustomerCart(): bool {
        return $this->db !== null
            && isset($_SESSION['user_id'], $_SESSION['user_role'])
            && $_SESSION['user_role'] === 'customer';
    }

    private function getCustomerCartId(): ?int {
        if (!$this->canSyncCustomerCart()) {
            return null;
        }

        if ($this->customerCartId !== null) {
            return $this->customerCartId;
        }

        $userId = (int)$_SESSION['user_id'];

        try {
            $stmt = $this->db->prepare('SELECT id FROM carts WHERE user_id = :uid LIMIT 1');
            $stmt->execute([':uid' => $userId]);
            $cartId = (int)($stmt->fetchColumn() ?: 0);

            if ($cartId <= 0) {
                $stmt = $this->db->prepare('INSERT INTO carts (user_id, created_at) VALUES (:uid, NOW())');
                $stmt->execute([':uid' => $userId]);
                $cartId = (int)$this->db->lastInsertId();
            }

            $this->customerCartId = $cartId > 0 ? $cartId : null;
        } catch (Throwable $e) {
            error_log('[CartService] getCustomerCartId failed: ' . $e->getMessage());
            $this->customerCartId = null;
        }

        return $this->customerCartId;
    }

    private function upsertCartItemToDb(array $item): void {
        $cartId = $this->getCustomerCartId();
        if (!$cartId) {
            return;
        }

        $type = (string)($item['type'] ?? 'product');
        $qty = max(1, (int)($item['quantity'] ?? 1));
        $weight = (string)($item['weight'] ?? ($type === 'combo' ? 'Bundle' : '500g'));
        $price = (float)($item['price'] ?? 0);

        try {
            if ($type === 'combo') {
                $comboId = (int)($item['combo_id'] ?? $item['id'] ?? 0);
                if ($comboId <= 0) {
                    return;
                }

                $check = $this->db->prepare('SELECT id FROM cart_items WHERE cart_id = :cart_id AND combo_id = :combo_id LIMIT 1');
                $check->execute([':cart_id' => $cartId, ':combo_id' => $comboId]);
                $existingId = (int)($check->fetchColumn() ?: 0);

                if ($existingId > 0) {
                    $update = $this->db->prepare('UPDATE cart_items SET quantity = :qty, weight = :weight, price = :price WHERE id = :id');
                    $update->execute([':qty' => $qty, ':weight' => $weight, ':price' => $price, ':id' => $existingId]);
                } else {
                    $insert = $this->db->prepare('INSERT INTO cart_items (cart_id, item_type, product_id, combo_id, quantity, weight, price) VALUES (:cart_id, :type, NULL, :combo_id, :qty, :weight, :price)');
                    $insert->execute([':cart_id' => $cartId, ':type' => $type, ':combo_id' => $comboId, ':qty' => $qty, ':weight' => $weight, ':price' => $price]);
                }

                return;
            }

            $productId = (int)($item['id'] ?? 0);
            if ($productId <= 0) {
                return;
            }

            $check = $this->db->prepare('SELECT id FROM cart_items WHERE cart_id = :cart_id AND product_id = :product_id AND weight = :weight LIMIT 1');
            $check->execute([':cart_id' => $cartId, ':product_id' => $productId, ':weight' => $weight]);
            $existingId = (int)($check->fetchColumn() ?: 0);

            if ($existingId > 0) {
                $update = $this->db->prepare('UPDATE cart_items SET quantity = :qty, price = :price WHERE id = :id');
                $update->execute([':qty' => $qty, ':price' => $price, ':id' => $existingId]);
            } else {
                $insert = $this->db->prepare('INSERT INTO cart_items (cart_id, item_type, product_id, combo_id, quantity, weight, price) VALUES (:cart_id, :type, :product_id, NULL, :qty, :weight, :price)');
                $insert->execute([':cart_id' => $cartId, ':type' => $type, ':product_id' => $productId, ':qty' => $qty, ':weight' => $weight, ':price' => $price]);
            }
        } catch (Throwable $e) {
            error_log('[CartService] upsertCartItemToDb failed: ' . $e->getMessage());
        }
    }

    private function deleteCartItemFromDb(array $item): void {
        $cartId = $this->getCustomerCartId();
        if (!$cartId) {
            return;
        }

        $type = (string)($item['type'] ?? 'product');
        $weight = (string)($item['weight'] ?? ($type === 'combo' ? 'Bundle' : '500g'));

        try {
            if ($type === 'combo') {
                $comboId = (int)($item['combo_id'] ?? $item['id'] ?? 0);
                if ($comboId > 0) {
                    $stmt = $this->db->prepare('DELETE FROM cart_items WHERE cart_id = :cart_id AND combo_id = :combo_id');
                    $stmt->execute([':cart_id' => $cartId, ':combo_id' => $comboId]);
                }
                return;
            }

            $productId = (int)($item['id'] ?? 0);
            if ($productId > 0) {
                $stmt = $this->db->prepare('DELETE FROM cart_items WHERE cart_id = :cart_id AND product_id = :product_id AND weight = :weight');
                $stmt->execute([':cart_id' => $cartId, ':product_id' => $productId, ':weight' => $weight]);
            }
        } catch (Throwable $e) {
            error_log('[CartService] deleteCartItemFromDb failed: ' . $e->getMessage());
        }
    }

    /**
     * Add a product item to the cart
     */
    public function addItem($product, $quantity = 1, $weight = '500g', $variantId = 0) {
        $cartId = $this->getCartKey($product['slug'], $weight);
        $quantity = max(1, (int)$quantity);

        $existingQty = isset($_SESSION['cart'][$cartId]) ? (int)$_SESSION['cart'][$cartId]['quantity'] : 0;
        $nextQty = $existingQty + $quantity;

        $stockItem = [
            'type' => 'product',
            'id' => (int)($product['id'] ?? 0),
            'variant_id' => (int)$variantId
        ];
        $availableStock = $this->getAvailableStockForItem($stockItem);
        if ($availableStock !== null) {
            if ($availableStock <= 0) {
                return false;
            }
            $nextQty = min($nextQty, $availableStock);
        }
        
        if (isset($_SESSION['cart'][$cartId])) {
            $_SESSION['cart'][$cartId]['quantity'] = $nextQty;
        } else {
            $_SESSION['cart'][$cartId] = [
                'type' => 'product',
                'id' => $product['id'],
                'variant_id' => $variantId,
                'name' => $product['name'],
                'slug' => $product['slug'],
                'image' => $this->resolveCartImage($product),
                'price' => $product['sale_price'] ?? $product['price'] ?? $product['base_price'] ?? 0,
                'original_price' => $product['base_price'] ?? $product['original_price'] ?? 0,
                'weight' => $weight,
                'quantity' => $nextQty
            ];
        }
        $this->upsertCartItemToDb($_SESSION['cart'][$cartId]);
        return true;
    }

    /**
     * Add a combo item to the cart
     */
    public function addCombo(array $combo, int $quantity = 1) {
        $cartId = 'combo-' . $combo['slug'];
        $quantity = max(1, $quantity);

        $existingQty = isset($_SESSION['cart'][$cartId]) ? (int)$_SESSION['cart'][$cartId]['quantity'] : 0;
        $nextQty = $existingQty + $quantity;

        $stockItem = [
            'type' => 'combo',
            'combo_id' => (int)$combo['id'],
            'items' => $combo['items']
        ];
        
        $availableStock = $this->getAvailableStockForItem($stockItem);
        if ($availableStock !== null) {
            if ($availableStock <= 0) {
                return false;
            }
            $nextQty = min($nextQty, $availableStock);
        }

        if (isset($_SESSION['cart'][$cartId])) {
            $_SESSION['cart'][$cartId]['quantity'] = $nextQty;
        } else {
            $_SESSION['cart'][$cartId] = [
                'type' => 'combo',
                'combo_id' => $combo['id'],
                'name' => $combo['name'],
                'slug' => $combo['slug'],
                'image' => $this->resolveCartImage($combo),
                'price' => $combo['final_price'],
                'original_price' => $combo['original_price'],
                'items' => $combo['items'],
                'weight' => 'Bundle',
                'quantity' => $nextQty
            ];
        }
        $this->upsertCartItemToDb($_SESSION['cart'][$cartId]);
        return true;
    }

    /**
     * Refresh cart prices from Database (Source of Truth)
     * Prevents users from holding stale, cheaper prices in their session.
     */
    private function refreshCartPrices(): void {
        if (empty($_SESSION['cart']) || !$this->db) {
            return;
        }

        // Only refresh once per request to avoid unnecessary DB calls
        static $refreshed = false;
        if ($refreshed) return;
        
        foreach ($_SESSION['cart'] as $cartId => &$item) {
            try {
                if ($item['type'] === 'combo' && isset($item['combo_id'])) {
                    $stmt = $this->db->prepare("SELECT price FROM combos WHERE id = :id AND is_active = 1");
                    $stmt->execute([':id' => $item['combo_id']]);
                    $combo = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($combo) {
                        $item['price'] = (float)$combo['price'];
                        $item['original_price'] = (float)$combo['price'];
                        
                        // Check Combo Stock dynamically
                        $availableStock = $this->getAvailableStockForItem($item);
                        if ($availableStock !== null && $item['quantity'] > $availableStock) {
                            $item['quantity'] = max(1, $availableStock);
                            if ($availableStock <= 0) $item['error'] = 'Out of stock';
                        }
                    } else {
                        $item['error'] = 'Item no longer available';
                    }
                } else {
                    // Regular product
                    $stmt = $this->db->prepare("SELECT base_price, sale_price FROM products WHERE id = :id");
                    $stmt->execute([':id' => $item['id']]);
                    $prod = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($prod) {
                        $activePrice = (!empty($prod['sale_price']) && $prod['sale_price'] > 0) ? $prod['sale_price'] : $prod['base_price'];
                        $item['price'] = (float)$activePrice;
                        $item['original_price'] = (float)$prod['base_price'];
                        
                        // Check Regular Product Stock dynamically
                        $availableStock = $this->getAvailableStockForItem($item);
                        if ($availableStock !== null && $item['quantity'] > $availableStock) {
                            $item['quantity'] = max(1, $availableStock);
                            if ($availableStock <= 0) $item['error'] = 'Out of stock';
                        }
                    } else {
                        $item['error'] = 'Item no longer available';
                    }
                }
            } catch (Throwable $e) {
                error_log('[CartService] Error refreshing price for ' . $cartId . ': ' . $e->getMessage());
            }
        }
        unset($item);
        $refreshed = true;
    }

    /**
     * Get all items in the cart
     */
    public function getItems() {
        $this->refreshCartPrices();
        return $_SESSION['cart'] ?? [];
    }

    /**
     * Update item quantity
     */
    public function updateQuantity($cartId, $quantity) {
        $cartId = trim($cartId);
        if (isset($_SESSION['cart'][$cartId])) {
            if ($quantity <= 0) {
                $this->deleteCartItemFromDb($_SESSION['cart'][$cartId]);
                unset($_SESSION['cart'][$cartId]);
            } else {
                $quantity = (int)$quantity;
                $availableStock = $this->getAvailableStockForItem($_SESSION['cart'][$cartId]);
                if ($availableStock !== null) {
                    $quantity = min($quantity, $availableStock);
                }
                $_SESSION['cart'][$cartId]['quantity'] = max(1, $quantity);
                $this->upsertCartItemToDb($_SESSION['cart'][$cartId]);
            }
            return true;
        }
        return false;
    }

    /**
     * Remove an item from the cart
     */
    public function removeItem($cartId) {
        $cartId = trim($cartId);
        error_log("[CartService] removeItem called for ID: " . $cartId);
        
        if (isset($_SESSION['cart'][$cartId])) {
            $this->deleteCartItemFromDb($_SESSION['cart'][$cartId]);
            unset($_SESSION['cart'][$cartId]);
            error_log("[CartService] Removed item by key: " . $cartId);
            return true;
        }
        
        // Fallback: try to find by ID if the key doesn't match directly
        foreach ($_SESSION['cart'] as $key => $item) {
            if (trim((string)$key) === $cartId) {
                $this->deleteCartItemFromDb($item);
                unset($_SESSION['cart'][$key]);
                error_log("[CartService] Removed item by fallback search: " . $key);
                return true;
            }
        }
        
        error_log("[CartService] Item not found for removal: " . $cartId);
        error_log("[CartService] Current cart keys: " . implode(', ', array_keys($_SESSION['cart'])));
        return false;
    }

    /**
     * Clear the cart
     */
    public function clearCart() {
        $cartId = $this->getCustomerCartId();
        if ($cartId) {
            try {
                $stmt = $this->db->prepare('DELETE FROM cart_items WHERE cart_id = :cart_id');
                $stmt->execute([':cart_id' => $cartId]);
            } catch (Throwable $e) {
                error_log('[CartService] clearCart DB delete failed: ' . $e->getMessage());
            }
        }
        $_SESSION['cart'] = [];
    }

    /**
     * Calculate subtotal
     */
    public function getSubtotal() {
        $subtotal = 0;
        foreach ($this->getItems() as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }
        return $subtotal;
    }

    /**
     * Get total item count
     */
    public function getItemCount() {
        $count = 0;
        foreach ($this->getItems() as $item) {
            $count += $item['quantity'];
        }
        return $count;
    }

    /**
     * Get shipping charges based on subtotal
     */
    public function getShippingCharges(): float {
        $subtotal = $this->getSubtotal();
        if ($subtotal >= SHIPPING_RATES['free_threshold']) {
            return 0.00;
        }
        return (float)SHIPPING_RATES['standard'];
    }

    /**
     * Get total amount (subtotal + shipping - discount)
     */
    public function getTotal(): float {
        $discount = (float)($_SESSION['coupon_discount'] ?? 0);
        $total = ($this->getSubtotal() + $this->getShippingCharges()) - $discount;
        return max(0, $total);
    }

    /**
     * Set a coupon discount in session
     */
    public function setCouponDiscount(float $amount, string $title = ''): void {
        $_SESSION['coupon_discount'] = $amount;
        $_SESSION['coupon_title'] = $title;
    }

    /**
     * Get current coupon discount
     */
    public function getCouponDiscount(): float {
        return (float)($_SESSION['coupon_discount'] ?? 0);
    }

    /**
     * Get current coupon title
     */
    public function getCouponTitle(): string {
        return (string)($_SESSION['coupon_title'] ?? '');
    }

    /**
     * Clear applied coupon
     */
    public function clearCoupon(): void {
        unset($_SESSION['coupon_discount']);
        unset($_SESSION['coupon_title']);
    }
}
