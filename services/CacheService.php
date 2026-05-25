<?php
declare(strict_types=1);

/**
 * Sweets Website
 * =============================================================
 * File: CacheService.php
 * Description: Redis-backed Application Cache and Rate Limiter
 * =============================================================
 */

class CacheService {
    private static ?Redis $redis = null;
    private static bool $available = true;

    public static function get(string $key): mixed {
        $r = self::connect();
        if (!$r) return null;
        try {
            $val = $r->get($key);
            return $val !== false ? unserialize($val) : null;
        } catch (Throwable $e) {
            self::$available = false;
            return null;
        }
    }

    public static function set(string $key, mixed $val, int $ttl = 300): bool {
        $r = self::connect();
        if (!$r) return false;
        try {
            return (bool)$r->setEx($key, $ttl, serialize($val));
        } catch (Throwable $e) {
            self::$available = false;
            return false;
        }
    }

    public static function delete(string $key): void {
        $r = self::connect();
        if ($r) {
            try { $r->del($key); } catch (Throwable $e) {}
        }
    }

    public static function connect(): ?Redis {
        if (!self::$available || !extension_loaded('redis')) return null;
        if (self::$redis !== null) return self::$redis;
        try {
            $r = new Redis();
            $url = getenv('REDIS_URL');
            if ($url) {
                $parsed = parse_url($url);
                $host = $parsed['host'] ?? '127.0.0.1';
                $port = $parsed['port'] ?? 6379;
                $r->connect($host, $port, 1.0); // 1s timeout
            } else {
                $r->connect('127.0.0.1', 6379, 1.0);
            }
            self::$redis = $r;
        } catch (Throwable $e) {
            self::$available = false;
            return null;
        }
        return self::$redis;
    }
}

class RateLimiter {
    public static function check(string $key, int $limit, int $windowSeconds): bool {
        $r = CacheService::connect();
        if (!$r) return true; // Fail open if Redis unavailable

        try {
            $current = $r->incr($key);
            if ($current === 1) {
                $r->expire($key, $windowSeconds);
            }
            return $current <= $limit;
        } catch (Throwable $e) {
            return true; // Fail open
        }
    }
}
