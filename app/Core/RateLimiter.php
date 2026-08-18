<?php

namespace App\Core;

class RateLimiter
{
    private const MAX_ATTEMPTS = 3;
    private const DECAY_SECONDS = 900;

    private static ?\Redis $redis = null;

    private static function connection(): \Redis
    {
        if (self::$redis === null) {
            self::$redis = new \Redis();
            self::$redis->connect(REDIS_HOST, REDIS_PORT);
        }

        return self::$redis;
    }

    private static function key(string $email, string $ip): string
    {
        return "login_attempts:" . md5(strtolower($email) . "|" . $ip);
    }

    public static function tooManyAttempts(string $email, string $ip): bool
    {
        try {
            $key = self::key($email, $ip);
            $attempts = (int)self::connection()->get($key);

            return $attempts >= self::MAX_ATTEMPTS;
        } catch (\Throwable $exception) {
            // Se o Redis estiver fora do ar, não bloqueia o login por causa disso.
            error_log("RateLimiter indisponível: " . $exception->getMessage());
            return false;
        }
    }

    public static function hit(string $email, string $ip, bool $successful = false): void
    {
        try {
            $key = self::key($email, $ip);
            $redis = self::connection();

            if ($successful) {
                self::clear($email, $ip);
                return;
            }

            $attempts = $redis->incr($key);

            if ($attempts === 1) {
                $redis->expire($key, self::DECAY_SECONDS);
            }
        } catch (\Throwable $exception) {
            error_log("RateLimiter indisponível: " . $exception->getMessage());
        }
    }

    public static function clear(string $email, string $ip): void
    {
        try {
            self::connection()->del(self::key($email, $ip));
        } catch (\Throwable $exception) {
            error_log("RateLimiter indisponível: " . $exception->getMessage());
        }
    }

    public static function minutesRemaining(string $email, string $ip): int
    {
        try {
            $ttl = self::connection()->ttl(self::key($email, $ip));
            return $ttl > 0 ? (int)ceil($ttl / 60) : 0;
        } catch (\Throwable $exception) {
            return 0;
        }
    }
}