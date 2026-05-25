<?php
/**
 * Sweets Website - JS Error Bridge
 * Receives frontend errors and pipes them to LogService
 */
require_once __DIR__ . '/../config/config.php';

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || empty($input['message'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid payload']);
    exit();
}

// Log to the JS channel
LogService::error(LogService::CH_JS, $input['message'], [
    'url'        => $input['url'] ?? 'unknown',
    'line'       => $input['line'] ?? 0,
    'col'        => $input['col'] ?? 0,
    'stack'      => $input['stack'] ?? null,
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
]);

echo json_encode(['success' => true, 'ref' => LogService::corrId()]);
