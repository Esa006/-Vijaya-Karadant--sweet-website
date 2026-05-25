<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
verifyCSRF();
require_once __DIR__ . '/../../../config/Database.php';
require_once __DIR__ . '/../../../repositories/OrderRepository.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid method']);
    exit;
}

$orderId = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
$action = $_POST['action'] ?? '';

if (!$orderId || $action !== 'add') {
    echo json_encode(['success' => false, 'error' => 'Invalid parameters']);
    exit;
}

$status = $_POST['status'] ?? '';
$description = $_POST['description'] ?? '';
$location = $_POST['location'] ?? '';

if (empty($status)) {
    echo json_encode(['success' => false, 'error' => 'Status is required']);
    exit;
}

$repo = new OrderRepository();
$result = $repo->addDeliveryTracking($orderId, $status, $description, $location);

if ($result && $status === 'DELIVERED') {
    // If delivered, we could optionally update customer_metrics here
    // but a trigger or cron might be better. Or we just leave it for the 
    // aggregation queries as requested by the user.
}

echo json_encode(['success' => $result]);
