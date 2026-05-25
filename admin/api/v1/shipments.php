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

if (!$orderId || $action !== 'assign') {
    echo json_encode(['success' => false, 'error' => 'Invalid parameters']);
    exit;
}

$data = [
    'courier_name' => $_POST['courier_name'] ?? '',
    'tracking_id' => $_POST['tracking_id'] ?? '',
    'estimated_delivery' => $_POST['estimated_delivery'] ?? null
];

if (empty($data['courier_name']) || empty($data['tracking_id'])) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit;
}

$repo = new OrderRepository();
$result = $repo->assignShipment($orderId, $data);

echo json_encode(['success' => $result]);
