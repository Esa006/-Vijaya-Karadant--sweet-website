<?php
/**
 * Sweets Website - Admin API
 * =============================================================
 * File: admin/api/v1/festival-timer.php
 * Description: GET/POST endpoint for the festival countdown timer
 * =============================================================
 */
declare(strict_types=1);

require_once __DIR__ . '/../../../config/config.php';
require_once ROOT_PATH . '/repositories/PromotionRepository.php';

header('Content-Type: application/json');

// Admin auth guard
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin' || !isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$db   = Database::getInstance();
$repo = new PromotionRepository($db);

// ── GET: fetch current timer value ─────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $promo = $repo->getPromotionBySectionId('festival-offers');
    echo json_encode([
        'success'   => true,
        'timer_end' => $promo['timer_end'] ?? null,
    ]);
    exit;
}

// ── POST: update timer ─────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input  = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $action = $input['action'] ?? 'set'; // 'set' | 'stop'

    if ($action === 'stop') {
        // Set timer_end to a past date so the frontend shows "Offer Expired!"
        $timerEnd = date('Y-m-d H:i:s', strtotime('-1 second'));
    } else {
        $rawDate = trim((string)($input['timer_end'] ?? ''));
        if ($rawDate === '') {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'timer_end is required']);
            exit;
        }
        // Accept both datetime-local (YYYY-MM-DDTHH:MM) and full datetime string
        $ts = strtotime($rawDate);
        if ($ts === false || $ts <= 0) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'Invalid date format']);
            exit;
        }
        $timerEnd = date('Y-m-d H:i:s', $ts);
    }

    $ok = $repo->updateTimerEnd('festival-offers', $timerEnd);
    echo json_encode([
        'success'   => $ok,
        'timer_end' => $timerEnd,
        'message'   => $ok
            ? ($action === 'stop' ? 'Countdown stopped.' : 'Countdown updated to ' . $timerEnd)
            : 'DB update failed — check that the festival-offers promotion row exists.',
    ]);
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Method not allowed']);
