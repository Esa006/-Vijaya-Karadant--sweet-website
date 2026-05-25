<?php
/**
 * REST API Endpoint: /api/contact.php
 */
declare(strict_types=1);

// Security Headers
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

// Require Configuration, Autoloader & Database
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/Database.php';

use App\Validator\ContactValidator;
use App\DTO\ContactRequestDTO;
use App\Repository\ContactRepository;
use App\Service\ContactService;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
    exit;
}

// 1. Invisible Honeypot Check (Instant Reject Bot)
if (!empty($_POST['website_url'])) { 
    http_response_code(200); 
    echo json_encode(['status' => 'success', 'message' => 'Your message has been received.']); 
    exit;
}

// 2. CSRF Validation
if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Invalid security token. Please refresh the page.']);
    exit;
}

try {
    // 3. Validation Layer Maps Raw $_POST to DTO securely
    $validator = new ContactValidator();
    $dto = $validator->validate($_POST);

    // 4. Service & Repository Layer Execution
    $pdo = Database::getInstance();
    $repo = new ContactRepository($pdo);
    $service = new ContactService($repo);

    // This throws RuntimeException if Rate Limited
    $service->submitMessage($dto);

    // 5. Success (HTTP 202 Accepted) - Email sending pushes to background async
    http_response_code(202);
    echo json_encode(['status' => 'success', 'message' => 'Your message has been received securely.']);

} catch (InvalidArgumentException $e) {
    // Validation Errors
    http_response_code(400); 
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    
} catch (RuntimeException $e) {
    // Security / Rate Limit Errors (e.g., Code 429)
    http_response_code($e->getCode() ?: 500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    
} catch (Exception $e) {
    // Generic / DB Fallback Errors
    error_log("[Contact API] " . $e->getMessage()); // Logs to PHP's global stream cleanly
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Internal server error while processing your request.']);
}
