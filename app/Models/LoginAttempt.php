<?php

namespace App\Models;

use App\Core\AbstractModel;

class LoginAttempt extends AbstractModel
{
    protected string $table = "login_attempts";

    protected string $primaryKey = "id";

    protected array $fillable = [
        "email",
        "ip_address",
        "successful",
    ];

    protected bool $timestamps = true;

    protected bool $softDelete = true;

    public static function register(string $email, ?string $ipAddress, bool $successful): void
    {
        (new static())->fill([
            "email" => mb_strtolower(trim($email)),
            "ip_address" => $ipAddress,
            "successful" => $successful ? 1 : 0,
        ])->save();
    }

    public static function recentFailedCount(string $email, int $minutes): int
    {
        $since = (new \DateTimeImmutable("now", new \DateTimeZone(APP_TIMEZONE)))
            ->modify("-{$minutes} minutes")
            ->format("Y-m-d H:i:s");

        return (new static())
            ->where("email", "=", mb_strtolower(trim($email)))
            ->where("successful", "=", 0)
            ->where("created_at", ">", $since)
            ->count();
    }

    public static function isLocked(string $email): bool
    {
        return self::recentFailedCount($email, AUTH_LOCKOUT_MINUTES) >= AUTH_MAX_ATTEMPTS;
    }
}