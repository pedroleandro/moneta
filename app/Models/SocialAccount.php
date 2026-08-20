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

    private ?int $id = null;
    private ?int $userId = null;
    private ?string $provider = null;
    private ?string $providerId = null;
    private ?string $accessToken = null;
    private ?string $refreshToken = null;
    private ?string $createdAt = null;
    private ?string $updatedAt = null;
    private ?string $deletedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
        $this->attributes["user_id"] = $userId;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setProvider(string $provider): void
    {
        $this->provider = $provider;
        $this->attributes["provider"] = $provider;
    }

    public function getProvider(): ?string
    {
        return $this->provider;
    }

    public function setProviderId(string $providerId): void
    {
        $this->providerId = $providerId;
        $this->attributes["provider_id"] = $providerId;
    }

    public function getProviderId(): ?string
    {
        return $this->providerId;
    }

    public function setAccessToken(?string $token): void
    {
        $this->accessToken = $token;
        $this->attributes["access_token"] = $token;
    }

    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    public function setRefreshToken(?string $token): void
    {
        $this->refreshToken = $token;
        $this->attributes["refresh_token"] = $token;
    }

    public function getRefreshToken(): ?string
    {
        return $this->refreshToken;
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?string
    {
        return $this->updatedAt;
    }

    public function getDeletedAt(): ?string
    {
        return $this->deletedAt;
    }

    public static function findByProvider(string $provider, string $providerId): ?self
    {
        return (new static())
            ->where("provider", "=", $provider)
            ->where("provider_id", "=", $providerId)
            ->first();
    }
}