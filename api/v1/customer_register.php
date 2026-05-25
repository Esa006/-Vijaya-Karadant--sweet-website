<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/Database.php';
header('Content-Type: application/json');
ini_set('display_errors', '0'); // Prevent warnings from breaking JSON

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$password = trim($_POST['password'] ?? '');
$redirect = $_POST['redirect'] ?? '../../index.php';

if (empty($name) || empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Name, email, and password are required']);
    exit;
}

if (strlen($password) < 6) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    exit;
}

try {
    $db = Database::getInstance();
    
    // 1. Check if user already exists
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'An account with this email already exists']);
        exit;
    }
    
    $db->beginTransaction();
    
    // 2. Insert into users table
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $db->prepare("INSERT INTO users (full_name, email, phone, password, role, status, created_at, updated_at) VALUES (?, ?, ?, ?, 'customer', 'Active', NOW(), NOW())");
    $stmt->execute([$name, $email, $phone, $hash]);
    $userId = $db->lastInsertId();
    
    // 3. Insert into customers table
    $stmt = $db->prepare("INSERT INTO customers (user_id, name, phone, status, created_at) VALUES (?, ?, ?, 'active', NOW())");
    $stmt->execute([$userId, $name, $phone]);
    
    $db->commit();
    
    // 4. Create Session and Log User In
    if (session_status() === PHP_SESSION_NONE) session_start();
    session_regenerate_id(true);
    
    $_SESSION['user_id'] = $userId;
    $_SESSION['user_role'] = 'customer';
    $_SESSION['user_name'] = $name;
    
    // 5. Merge Cart Logic (Guest Cart -> DB Cart)
    $sessionCart = $_SESSION['cart'] ?? [];
    
    if (!empty($sessionCart)) {
        // Create user cart in DB
        $stmt = $db->prepare("INSERT INTO carts (user_id, created_at) VALUES (?, NOW())");
        $stmt->execute([$userId]);
        $cartId = $db->lastInsertId();
        
        // Insert session items into DB
        foreach ($sessionCart as $key => $item) {
            $type = $item['type'] ?? 'product';
            $productId = ($type === 'product') ? ($item['id'] ?? null) : null;
            $comboId = ($type === 'combo') ? ($item['combo_id'] ?? $item['id'] ?? null) : null;
            $qty = (int)($item['quantity'] ?? 1);
            $weight = $item['weight'] ?? ($type === 'combo' ? 'Bundle' : '500g');
            $price = $item['price'] ?? 0;
            
            if (!$productId && !$comboId) continue;
            
            $insertStmt = $db->prepare("INSERT INTO cart_items (cart_id, item_type, product_id, combo_id, quantity, weight, price) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $insertStmt->execute([$cartId, $type, $productId, $comboId, $qty, $weight, $price]);
        }
        
        // Repopulate session cart from DB just in case to maintain frontend state sync
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
    }
    
    // 6. Return Success
    echo json_encode(['success' => true, 'redirect' => $redirect]);
    
} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    error_log("[CustomerRegister] Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'System error during registration. Please try again later.']);
}
