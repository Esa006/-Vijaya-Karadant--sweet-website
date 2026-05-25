<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/Database.php';
header('Content-Type: application/json');
ini_set('display_errors', '0'); // Prevent warnings from breaking JSON

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');
$redirect = $_POST['redirect'] ?? '../../index.php';

if (empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Email and password are required']);
    exit;
}

require_once __DIR__ . '/../../services/CacheService.php';
$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
if (!RateLimiter::check("rate:cust_login:{$ip}", 5, 60)) {
    echo json_encode(['success' => false, 'message' => 'Too many login attempts. Please try again in a minute.']);
    exit;
}

try {
    $db = Database::getInstance();
    
    // 1. Fetch user
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND role = 'customer' LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
        exit;
    }
    
    // 3. Check status
    if (strtolower($user['status']) === 'blocked' || strtolower($user['status']) === 'inactive') {
        echo json_encode(['success' => false, 'message' => 'Account is blocked or inactive. Please contact support.']);
        exit;
    }
    
    // 2. Verify password (fallback for plain text during dev)
    $isValid = password_verify($password, $user['password']);
    if (!$isValid && $password === $user['password']) {
        $isValid = true;
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $updateStmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
        $updateStmt->execute([$hash, $user['id']]);
    }
    
    if (!$isValid) {
        echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
        exit;
    }
    
    // 4. Create Session
    if (session_status() === PHP_SESSION_NONE) session_start();
    session_regenerate_id(true);
    
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_role'] = 'customer';
    $_SESSION['user_name'] = $user['full_name'];
    
    // 5. Merge Cart Logic
    $userId = $user['id'];
    $sessionCart = $_SESSION['cart'] ?? [];
    
    // Fetch or create user cart in DB
    $stmt = $db->prepare("SELECT id FROM carts WHERE user_id = ? LIMIT 1");
    $stmt->execute([$userId]);
    $cartId = $stmt->fetchColumn();
    
    if (!$cartId) {
        $stmt = $db->prepare("INSERT INTO carts (user_id, created_at) VALUES (?, NOW())");
        $stmt->execute([$userId]);
        $cartId = $db->lastInsertId();
    }
    
    // Merge session cart into DB cart
    foreach ($sessionCart as $key => $item) {
        $type = $item['type'] ?? 'product';
        $productId = ($type === 'product') ? ($item['id'] ?? null) : null;
        $comboId = ($type === 'combo') ? ($item['combo_id'] ?? $item['id'] ?? null) : null;
        $qty = (int)($item['quantity'] ?? 1);
        $weight = $item['weight'] ?? ($type === 'combo' ? 'Bundle' : '500g');
        $price = $item['price'] ?? 0;
        
        if (!$productId && !$comboId) continue;
        
        // Check if item exists
        if ($type === 'combo') {
            $checkStmt = $db->prepare("SELECT id, quantity FROM cart_items WHERE cart_id = ? AND combo_id = ?");
            $checkStmt->execute([$cartId, $comboId]);
        } else {
            $checkStmt = $db->prepare("SELECT id, quantity FROM cart_items WHERE cart_id = ? AND product_id = ? AND weight = ?");
            $checkStmt->execute([$cartId, $productId, $weight]);
        }
        $existingItem = $checkStmt->fetch();
        
        if ($existingItem) {
            $newQty = $existingItem['quantity'] + $qty;
            $updateStmt = $db->prepare("UPDATE cart_items SET quantity = ?, price = ? WHERE id = ?");
            $updateStmt->execute([$newQty, $price, $existingItem['id']]);
        } else {
            $insertStmt = $db->prepare("INSERT INTO cart_items (cart_id, item_type, product_id, combo_id, quantity, weight, price) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $insertStmt->execute([$cartId, $type, $productId, $comboId, $qty, $weight, $price]);
        }
    }
    
    // Repopulate session cart from DB
    $stmt = $db->prepare(
        "SELECT ci.*, 
                COALESCE(p.name, c.name) AS name, 
                COALESCE(p.slug, c.slug) AS slug,
                COALESCE(pi.image_path, c.image, 'assets/images/placeholder.png') AS image_path
         FROM cart_items ci
         LEFT JOIN products p ON ci.product_id = p.id
         LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_main = 1
         LEFT JOIN combos c ON ci.combo_id = c.id
         WHERE ci.cart_id = ?"
    );
    $stmt->execute([$cartId]);
    $dbItems = $stmt->fetchAll();
    
    $mergedCart = [];
    foreach ($dbItems as $row) {
        $cartKey = $row['slug'] . '-' . preg_replace('/[^a-zA-Z0-9]/', '', (string)$row['weight']);
        $mergedItem = [
            'type'     => $row['item_type'],
            'name'     => $row['name'],
            'slug'     => $row['slug'],
            'image'    => $row['image_path'],
            'price'    => $row['price'],
            'weight'   => $row['weight'],
            'quantity' => $row['quantity']
        ];

        if ($row['item_type'] === 'combo') {
            $mergedItem['combo_id'] = $row['combo_id'];
        } else {
            $mergedItem['id'] = $row['product_id'];
        }

        $mergedCart[$cartKey] = $mergedItem;
    }
    
    $_SESSION['cart'] = $mergedCart;
    
    // 6. Redirect
    echo json_encode(['success' => true, 'redirect' => $redirect]);
    
} catch (Exception $e) {
    error_log("[CustomerAuth] Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'System error. Please try again later.']);
}
