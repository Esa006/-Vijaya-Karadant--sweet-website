<?php
declare(strict_types=1);
/**
 * Sweets Website
 * =============================================================
 * File: services/LogService.php
 * Description: Enterprise centralized structured logging service.
 *              PSR-3 inspired. JSON output. Named channels.
 *              Correlation ID aware. Production-safe.
 * =============================================================
 */

class LogService
{
    // ── Log Levels (ascending severity) ─────────────────────────
    public const DEBUG    = 'DEBUG';
    public const INFO     = 'INFO';
    public const NOTICE   = 'NOTICE';
    public const WARNING  = 'WARNING';
    public const ERROR    = 'ERROR';
    public const CRITICAL = 'CRITICAL';
    public const ALERT    = 'ALERT';

    // ── Named Channels ───────────────────────────────────────────
    public const CH_PAYMENT       = 'payment';
    public const CH_ORDERS        = 'orders';
    public const CH_INVENTORY     = 'inventory';
    public const CH_AUTH          = 'auth';
    public const CH_SECURITY      = 'security';
    public const CH_PHP           = 'php-errors';
    public const CH_JS            = 'js-errors';
    public const CH_API           = 'api';
    public const CH_ADMIN         = 'admin-actions';
    public const CH_APP           = 'app';

    // ── Fields that must NEVER appear in logs ────────────────────
    private const SCRUB_KEYS = [
        'password', 'password_confirmation', 'card_number', 'cvv', 'cvc',
        'secret', 'api_secret', 'razorpay_secret', 'token', 'pin',
        'db_pass', 'DB_PASS',
    ];

    // ── Max bytes per log file before rotation warning ───────────
    private const MAX_FILE_BYTES = 52_428_800; // 50 MB

    private static ?string $logDir    = null;
    private static ?string $corrId    = null;
    private static int     $minLevel  = 0;

    // ── Severity numeric map ─────────────────────────────────────
    private static array $levelMap = [
        self::DEBUG    => 0,
        self::INFO     => 1,
        self::NOTICE   => 2,
        self::WARNING  => 3,
        self::ERROR    => 4,
        self::CRITICAL => 5,
        self::ALERT    => 6,
    ];

    // ────────────────────────────────────────────────────────────
    // Bootstrap
    // ────────────────────────────────────────────────────────────

    public static function init(string $logDir, string $minLevel = self::DEBUG): void
    {
        self::$logDir   = rtrim($logDir, '/\\') . DIRECTORY_SEPARATOR;
        self::$minLevel = self::$levelMap[$minLevel] ?? 0;

        if (!is_dir(self::$logDir)) {
            @mkdir(self::$logDir, 0750, true);
        }
    }

    /** Returns (or lazily creates) the correlation ID for this request. */
    public static function corrId(): string
    {
        if (self::$corrId === null) {
            // Use existing header from load-balancer / reverse proxy if present
            self::$corrId = $_SERVER['HTTP_X_CORRELATION_ID']
                ?? $_SERVER['HTTP_X_REQUEST_ID']
                ?? self::generateId();
        }
        return self::$corrId;
    }

    // ────────────────────────────────────────────────────────────
    // Public Level Shortcuts
    // ────────────────────────────────────────────────────────────

    /** Public alias — for use by ErrorHandler (passes dynamic level string). */
    public static function write_public(string $level, string $channel, string $message, array $context = []): void
    {
        self::write($level, $channel, $message, $context);
    }

    public static function debug(string $channel, string $message, array $context = []): void
    {
        self::write(self::DEBUG, $channel, $message, $context);
    }

    public static function info(string $channel, string $message, array $context = []): void
    {
        self::write(self::INFO, $channel, $message, $context);
    }

    public static function notice(string $channel, string $message, array $context = []): void
    {
        self::write(self::NOTICE, $channel, $message, $context);
    }

    public static function warning(string $channel, string $message, array $context = []): void
    {
        self::write(self::WARNING, $channel, $message, $context);
    }

    public static function error(string $channel, string $message, array $context = []): void
    {
        self::write(self::ERROR, $channel, $message, $context);
        // Also push to PHP's native error_log so Apache/XAMPP captures it
        error_log("[{$channel}][ERROR] {$message}");
    }

    public static function critical(string $channel, string $message, array $context = []): void
    {
        self::write(self::CRITICAL, $channel, $message, $context);
        error_log("[{$channel}][CRITICAL] {$message}");
    }

    public static function alert(string $channel, string $message, array $context = []): void
    {
        self::write(self::ALERT, $channel, $message, $context);
        error_log("[{$channel}][ALERT] {$message}");
    }

    /** Log a Throwable with full stack trace. */
    public static function exception(string $channel, \Throwable $e, array $context = []): void
    {
        self::error($channel, $e->getMessage(), array_merge($context, [
            'exception_class' => get_class($e),
            'file'            => $e->getFile(),
            'line'            => $e->getLine(),
            'trace'           => self::formatTrace($e),
        ]));
    }

    // ────────────────────────────────────────────────────────────
    // Core Write
    // ────────────────────────────────────────────────────────────

    private static function write(
        string $level,
        string $channel,
        string $message,
        array  $context
    ): void {
        // Respect minimum log level
        if ((self::$levelMap[$level] ?? 0) < self::$minLevel) {
            return;
        }

        self::ensureInit();

        $entry = json_encode([
            'time'        => date('Y-m-d\TH:i:sP'),
            'level'       => $level,
            'channel'     => $channel,
            'corr_id'     => self::corrId(),
            'message'     => $message,
            'user_id'     => $_SESSION['user_id'] ?? null,
            'request_uri' => $_SERVER['REQUEST_URI'] ?? null,
            'ip'          => self::clientIp(),
            'context'     => self::scrub($context),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;

        $file = self::$logDir . $channel . '.log';

        // Warn if file is approaching size limit (don't block writes)
        if (file_exists($file) && filesize($file) > self::MAX_FILE_BYTES) {
            self::rotateSingle($file);
        }

        // Atomic append with exclusive lock
        file_put_contents($file, $entry, FILE_APPEND | LOCK_EX);
    }

    // ────────────────────────────────────────────────────────────
    // Rotation
    // ────────────────────────────────────────────────────────────

    private static function rotateSingle(string $file): void
    {
        $rotated = $file . '.' . date('Ymd-His') . '.gz';
        if (function_exists('gzopen')) {
            $gz  = gzopen($rotated, 'wb9');
            $fh  = fopen($file, 'rb');
            if ($gz && $fh) {
                while (!feof($fh)) {
                    gzwrite($gz, fread($fh, 65536));
                }
                fclose($fh);
                gzclose($gz);
                file_put_contents($file, ''); // truncate
            }
        } else {
            rename($file, $file . '.' . date('Ymd-His'));
        }
    }

    // ────────────────────────────────────────────────────────────
    // Helpers
    // ────────────────────────────────────────────────────────────

    private static function ensureInit(): void
    {
        if (self::$logDir === null) {
            // Auto-init using ROOT_PATH if defined
            $dir = defined('ROOT_PATH') ? ROOT_PATH . '/logs' : sys_get_temp_dir() . '/sweets_logs';
            self::init($dir, self::INFO);
        }
    }

    /** Recursively scrub sensitive fields from context arrays. */
    private static function scrub(array $data, int $depth = 0): array
    {
        if ($depth > 5) return ['[truncated]'];
        $clean = [];
        foreach ($data as $k => $v) {
            if (in_array(strtolower((string)$k), array_map('strtolower', self::SCRUB_KEYS), true)) {
                $clean[$k] = '[REDACTED]';
            } elseif (is_array($v)) {
                $clean[$k] = self::scrub($v, $depth + 1);
            } else {
                $clean[$k] = $v;
            }
        }
        return $clean;
    }

    /** Format a compact stack trace (file:line pairs only — no source code). */
    private static function formatTrace(\Throwable $e): array
    {
        return array_map(fn($f) => sprintf(
            '%s:%d %s%s%s()',
            $f['file']     ?? '[internal]',
            $f['line']     ?? 0,
            $f['class']    ?? '',
            $f['type']     ?? '',
            $f['function'] ?? ''
        ), array_slice($e->getTrace(), 0, 10));
    }

    private static function clientIp(): string
    {
        return $_SERVER['HTTP_X_FORWARDED_FOR']
            ?? $_SERVER['HTTP_X_REAL_IP']
            ?? $_SERVER['REMOTE_ADDR']
            ?? 'unknown';
    }

    private static function generateId(): string
    {
        return sprintf('%s-%04x', date('Ymd-His'), mt_rand(0, 0xffff));
    }
}
