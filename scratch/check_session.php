<?php
require_once __DIR__ . '/config/config.php';
header('Content-Type: application/json');
echo json_encode([
    'session_id' => session_id(),
    'csrf_token' => $_SESSION['csrf_token'] ?? 'MISSING',
    'user_role'  => $_SESSION['user_role'] ?? 'GUEST'
]);
