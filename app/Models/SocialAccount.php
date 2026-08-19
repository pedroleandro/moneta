<?php

namespace App\Models;

use App\Core\AbstractModel;

class SocialAccount extends AbstractModel
{
    protected string $table = "social_accounts";

    protected string $primaryKey = "id";

    protected array $fillable = [
        "user_id",
        "provider",
        "provider_id",
        "access_token",
        "refresh_token",
    ];

    protected array $required = [
        "user_id" => "O usuário é obrigatório.",
        "provider" => "O provedor é obrigatório.",
        "provider_id" => "O ID do provedor é obrigatório.",
    ];

    protected bool $timestamps = true;

    protected bool $softDelete = true;

    public function getId(): ?int
    {
        return isset($this->attributes["id"]) ? (int)$this->attributes["id"] : null;
    }

    public function setUserId(int $userId): void
    {
        $this->attributes["user_id"] = $userId;
    }

    public function getUserId(): ?int
    {
        return isset($this->attributes["user_id"]) ? (int)$this->attributes["user_id"] : null;
    }

    public function setProvider(string $provider): void
    {
        $this->attributes["provider"] = $provider;
    }

    public function setProviderId(string $providerId): void
    {
        $this->attributes["provider_id"] = $providerId;
    }

    public function setAccessToken(?string $token): void
    {
        $this->attributes["access_token"] = $token;
    }

    public function setRefreshToken(?string $token): void
    {
        $this->attributes["refresh_token"] = $token;
    }

    public static function findByProvider(string $provider, string $providerId): ?self
    {
        return (new static())
            ->where("provider", "=", $provider)
            ->where("provider_id", "=", $providerId)
            ->first();
    }
}