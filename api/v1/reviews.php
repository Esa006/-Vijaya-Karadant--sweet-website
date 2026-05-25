<?php
/**
 * Sweets Website
 * =============================================================
 * File: api/v1/reviews.php
 * Description: REST-style API endpoint for product reviews
 *   POST               – submit a review (verified purchasers only)
 *   POST ?action=helpful – increment helpful count
 * =============================================================
 */
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config/config.php';
require_once SERVICES_PATH . '/ReviewService.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$action = trim($_GET['action'] ?? '');

$reviewService = new ReviewService();
$userId = (int)($_SESSION['user_id'] ?? 0);

// ── POST ?action=helpful: Mark helpful ────────────────────────
if ($method === 'POST' && $action === 'helpful') {
    $reviewId = (int)($_POST['review_id'] ?? 0);
    if ($reviewId <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid review ID.']);
        exit;
    }
    // Throttle: max 20 helpful votes per session
    $_SESSION['helpful_count'] = (int)($_SESSION['helpful_count'] ?? 0) + 1;
    if ($_SESSION['helpful_count'] > 20) {
        echo json_encode(['success' => false, 'message' => 'Too many requests.']);
        exit;
    }
    $ok = $reviewService->markHelpful($reviewId);
    echo json_encode(['success' => $ok]);
    exit;
}

// ── POST: Submit review ───────────────────────────────────────
if ($method === 'POST') {

    // CSRF check
    $token = $_POST['csrf_token'] ?? '';
    if (!$token || $token !== ($_SESSION['csrf_token'] ?? '')) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Security check failed. Please refresh the page.']);
        exit;
    }

    if ($userId <= 0) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'You must be logged in to submit a review.']);
        exit;
    }

    // Rate limit: max 3 review submissions per hour per session
    $now     = time();
    $rateKey = 'review_submit_times';
    $_SESSION[$rateKey] = array_values(array_filter(
        (array)($_SESSION[$rateKey] ?? []),
        fn($t) => ($now - (int)$t) < 3600
    ));
    if (count($_SESSION[$rateKey]) >= 3) {
        http_response_code(429);
        echo json_encode(['success' => false, 'message' => 'Too many review submissions. Please try again in an hour.']);
        exit;
    }

    $productId = !empty($_POST['product_id']) ? (int)$_POST['product_id'] : null;
    $comboId   = !empty($_POST['combo_id'])   ? (int)$_POST['combo_id']   : null;
    $orderId   = (int)($_POST['order_id'] ?? 0);
    $rating    = (int)($_POST['rating']   ?? 0);
    $title     = trim((string)($_POST['title'] ?? ''));
    $body      = trim((string)($_POST['body']  ?? ''));

    // Validate that order_id was supplied (prevents spoofing with 0)
    if ($orderId <= 0) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Invalid order reference. Please try again.']);
        exit;
    }

    $result = $reviewService->submitReview(
        $userId, $productId, $comboId, $orderId, $rating, $title, $body
    );

    // Record timestamp on success for rate limiting
    if ($result['success']) {
        $_SESSION[$rateKey][] = $now;
    }

    // Ensure there is always a user-facing message
    if (empty($result['message'])) {
        $result['message'] = $result['success']
            ? 'Your review has been published!'
            : 'Could not submit your review. Please try again.';
    }

    http_response_code($result['success'] ? 200 : 422);
    echo json_encode($result);
    exit;
}

// ── Fallback ──────────────────────────────────────────────────
http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
