<?php
declare(strict_types=1);
/**
 * Sweets Website
 * =============================================================
 * File: src/ErrorHandler.php
 * Description: Global production-safe error handler.
 *              - set_error_handler   → PHP warnings/notices
 *              - set_exception_handler → uncaught exceptions
 *              - register_shutdown_function → fatal/parse errors
 *              User sees a reference code. Details go to logs only.
 * =============================================================
 */

class ErrorHandler
{
    private static bool $registered = false;

    public static function register(): void
    {
        if (self::$registered) return;
        self::$registered = true;

        // 1. PHP warnings, notices, deprecations
        set_error_handler([self::class, 'handleError']);

        // 2. Uncaught exceptions
        set_exception_handler([self::class, 'handleException']);

        // 3. Fatal errors (E_ERROR, E_PARSE) — cannot be caught above
        register_shutdown_function([self::class, 'handleShutdown']);
    }

    // ── set_error_handler callback ───────────────────────────────
    public static function handleError(
        int    $errno,
        string $errstr,
        string $errfile = '',
        int    $errline = 0
    ): bool {
        // Respect the @ error-suppression operator
        if (!(error_reporting() & $errno)) {
            return false;
        }

        $level   = self::phpErrnoToLevel($errno);
        $channel = LogService::CH_PHP;
        $ref     = LogService::corrId();

        LogService::write_public($level, $channel, $errstr, [
            'errno'   => $errno,
            'file'    => $errfile,
            'line'    => $errline,
            'ref'     => $ref,
        ]);

        // For E_ERROR severity via error_handler (rare), surface to user
        if ($errno === E_USER_ERROR) {
            self::sendUserResponse($ref);
            exit(1);
        }

        // Return true → don't execute PHP's built-in error handler
        return true;
    }

    // ── set_exception_handler callback ──────────────────────────
    public static function handleException(\Throwable $e): void
    {
        $ref = LogService::corrId();

        LogService::exception(LogService::CH_PHP, $e, ['ref' => $ref]);

        self::sendUserResponse($ref);
        exit(1);
    }

    // ── register_shutdown_function callback ─────────────────────
    public static function handleShutdown(): void
    {
        $error = error_get_last();
        if ($error === null) return;

        $fatals = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR];
        if (!in_array($error['type'], $fatals, true)) return;

        $ref = LogService::corrId();

        LogService::critical(LogService::CH_PHP, 'Fatal shutdown: ' . $error['message'], [
            'file' => $error['file'],
            'line' => $error['line'],
            'ref'  => $ref,
        ]);

        // Only send response if headers not yet sent
        if (!headers_sent()) {
            self::sendUserResponse($ref);
        }
    }

    // ── User-facing response ─────────────────────────────────────
    private static function sendUserResponse(string $ref): void
    {
        if (!headers_sent()) {
            http_response_code(500);
        }

        // AJAX / JSON consumers get structured error
        $wantsJson = (
            ($_SERVER['HTTP_ACCEPT'] ?? '') === 'application/json' ||
            ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest' ||
            strpos($_SERVER['REQUEST_URI'] ?? '', '/api/') !== false
        );

        if ($wantsJson) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'An unexpected error occurred. Please try again.',
                'ref'     => $ref,
            ]);
            return;
        }

        // Browser users get a friendly HTML page
        echo self::friendlyHtml($ref);
    }

    private static function friendlyHtml(string $ref): string
    {
        $logoUrl = defined('BASE_URL') && defined('SITE_LOGO') ? BASE_URL . SITE_LOGO : 'https://cdn-icons-png.flaticon.com/512/564/564619.png';
        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Something went wrong — Vijaya Karadant</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --brand: #7a1f1f;
            --brand-dark: #5a1414;
            --cream: #fdf6ee;
            --gold: #c5a059;
        }
        body { 
            font-family: 'Outfit', sans-serif; 
            background: var(--cream); 
            display: flex;
            align-items: center; 
            justify-content: center; 
            min-height: 100vh; 
            margin: 0; 
            color: #333;
        }
        .error-container {
            background: #fff;
            border-radius: 24px;
            padding: 60px 40px;
            max-width: 500px;
            width: 90%;
            text-align: center;
            box-shadow: 0 20px 60px rgba(122, 31, 31, 0.1);
            border: 1px solid rgba(122, 31, 31, 0.05);
            position: relative;
            overflow: hidden;
        }
        .error-container::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; height: 6px;
            background: linear-gradient(90deg, var(--brand), var(--gold), var(--brand));
        }
        .logo {
            width: 80px;
            margin-bottom: 30px;
        }
        h1 { 
            color: var(--brand); 
            font-size: 2rem; 
            font-weight: 800;
            margin-bottom: 1rem;
            letter-spacing: -0.02em;
        }
        p { 
            color: #666; 
            line-height: 1.7;
            font-size: 1.1rem;
            margin-bottom: 2rem;
        }
        .ref-box {
            background: #f8f1e9;
            border: 1px dashed var(--gold);
            padding: 12px 20px;
            border-radius: 12px;
            display: inline-block;
            margin-bottom: 2.5rem;
        }
        .ref-label {
            display: block;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--gold);
            font-weight: 700;
            margin-bottom: 4px;
        }
        .ref-code {
            font-family: 'Monaco', 'Consolas', monospace;
            font-size: 0.95rem;
            color: var(--brand);
            font-weight: 600;
        }
        .btn-home {
            display: inline-block;
            background: var(--brand);
            color: #fff;
            text-decoration: none;
            padding: 14px 32px;
            border-radius: 12px;
            font-weight: 700;
            transition: all 0.3s ease;
            box-shadow: 0 8px 20px rgba(122, 31, 31, 0.2);
        }
        .btn-home:hover {
            background: var(--brand-dark);
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(122, 31, 31, 0.3);
        }
        .decor {
            position: absolute;
            opacity: 0.03;
            z-index: 0;
            pointer-events: none;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <img src="{$logoUrl}" alt="Vijaya Karadant" class="logo">
        <h1>Something went wrong</h1>
        <p>We're sorry — an unexpected error occurred while processing your request. Our technical team has been notified and is looking into it.</p>
        
        <div class="ref-box">
            <span class="ref-label">Error Reference</span>
            <span class="ref-code">{$ref}</span>
        </div>
        
        <div>
            <a href="/" class="btn-home">Return to Homepage</a>
        </div>
    </div>
</body>
</html>
HTML;
    }

    // ── Map PHP errno to LogService level ────────────────────────
    private static function phpErrnoToLevel(int $errno): string
    {
        switch ($errno) {
            case E_ERROR:
            case E_PARSE:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_USER_ERROR:
                return LogService::CRITICAL;
            case E_WARNING:
            case E_USER_WARNING:
            case E_CORE_WARNING:
            case E_COMPILE_WARNING:
                return LogService::WARNING;
            case E_DEPRECATED:
            case E_USER_DEPRECATED:
                return LogService::NOTICE;
            default:
                return LogService::INFO;
        }
    }
}
