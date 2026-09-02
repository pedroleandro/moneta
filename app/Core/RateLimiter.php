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
            Logger::error("RateLimiter indisponível", ["exception" => $exception->getMessage()]);
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
            Logger::error("RateLimiter indisponível", ["exception" => $exception->getMessage()]);
        }
    }

    public static function clear(string $email, string $ip): void
    {
        try {
            self::connection()->del(self::key($email, $ip));
        } catch (\Throwable $exception) {
            Logger::error("RateLimiter indisponível", ["exception" => $exception->getMessage()]);
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

    private static function deviceKey(string $email, string $deviceToken): string
    {
        return "login_attempts_device:" . md5(strtolower($email) . "|" . $deviceToken);
    }

    public static function tooManyAttemptsForDevice(string $email, string $deviceToken): bool
    {
        try {
            $key = self::deviceKey($email, $deviceToken);
            $attempts = (int)self::connection()->get($key);

            return $attempts >= self::MAX_ATTEMPTS;
        } catch (\Throwable $exception) {
            Logger::error("RateLimiter (dispositivo) indisponível", ["exception" => $exception->getMessage()]);
            return false;
        }
    }

    public static function hitForDevice(string $email, string $deviceToken, bool $successful = false): void
    {
        try {
            $key = self::deviceKey($email, $deviceToken);
            $redis = self::connection();

            if ($successful) {
                self::clearForDevice($email, $deviceToken);
                return;
            }

            $attempts = $redis->incr($key);

            if ($attempts === 1) {
                $redis->expire($key, self::DECAY_SECONDS);
            }
        } catch (\Throwable $exception) {
            Logger::error("RateLimiter (dispositivo) indisponível", ["exception" => $exception->getMessage()]);
        }
    }

    public static function clearForDevice(string $email, string $deviceToken): void
    {
        try {
            self::connection()->del(self::deviceKey($email, $deviceToken));
        } catch (\Throwable $exception) {
            Logger::error("RateLimiter (dispositivo) indisponível", ["exception" => $exception->getMessage()]);
        }
    }

    public static function minutesRemainingForDevice(string $email, string $deviceToken): int
    {
        try {
            $ttl = self::connection()->ttl(self::deviceKey($email, $deviceToken));
            return $ttl > 0 ? (int)ceil($ttl / 60) : 0;
        } catch (\Throwable $exception) {
            return 0;
        }
    }
}