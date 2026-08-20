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
    protected bool $softDelete = false;

    private ?int $id = null;
    private ?string $email = null;
    private ?string $ipAddress = null;
    private ?int $successful = null;
    private ?string $createdAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function isSuccessful(): bool
    {
        return (bool)$this->successful;
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    public static function register(string $email, ?string $ipAddress, bool $successful): void
    {
        (new static())->fill([
            "email" => mb_strtolower(trim($email)),
            "ip_address" => $ipAddress,
            "successful" => $successful ? 1 : 0,
        ])->save();
    }

    /**
     * @throws \Exception
     */
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

    /**
     * @throws \Exception
     */
    public static function isLocked(string $email): bool
    {
        return self::recentFailedCount($email, AUTH_LOCKOUT_MINUTES) >= AUTH_MAX_ATTEMPTS;
    }
}