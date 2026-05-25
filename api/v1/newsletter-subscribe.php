<?php
/**
 * Sweets Website
 * =============================================================
 * File: api/v1/newsletter-subscribe.php
 * Description: Newsletter subscription endpoint (footer + checkout)
 * =============================================================
 */
declare(strict_types=1);

require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$input  = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$email  = trim((string)($input['email'] ?? ''));
$source = trim((string)($input['source'] ?? 'footer'));

// Validate
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
    exit;
}

$source = in_array($source, ['footer', 'checkout'], true) ? $source : 'footer';

try {
    $db = Database::getInstance();

    // INSERT OR UPDATE to re-activate if previously unsubscribed
    $stmt = $db->prepare(
        "INSERT INTO newsletter_subscribers (email, source, is_active)
         VALUES (:email, :source, 1)
         ON DUPLICATE KEY UPDATE is_active = 1, source = VALUES(source)"
    );
    $stmt->execute([':email' => $email, ':source' => $source]);

    echo json_encode(['success' => true, 'message' => 'Thank you! You are now subscribed to our newsletter.']);

} catch (Throwable $e) {
    error_log('[Newsletter] Subscribe failed: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Something went wrong. Please try again.']);
}
