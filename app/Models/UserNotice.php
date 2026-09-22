<?php

namespace App\Models;

use App\Core\AbstractModel;

class UserNotice extends AbstractModel
{
    protected string $table = "user_notices";

    protected string $primaryKey = "id";

    protected array $fillable = [
        "user_id",
        "type",
        "payload",
    ];

    protected array $required = [
        "user_id" => "O usuário é obrigatório.",
        "type" => "O tipo é obrigatório.",
        "payload" => "O conteúdo é obrigatório.",
    ];

    protected bool $timestamps = false;

    protected bool $softDelete = false;

    public const TYPE_RECURRENCE_AUTO_CONFIRMED = "recurrence_auto_confirmed";

    private ?int $id = null;
    private ?int $userId = null;
    private ?string $type = null;
    private ?string $payload = null;
    private ?string $createdAt = null;
    private ?string $dismissedAt = null;

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

    public function setType(string $type): void
    {
        $this->type = $type;
        $this->attributes["type"] = $type;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setPayload(array|string $payload): void
    {
        $encoded = is_array($payload) ? json_encode($payload, JSON_UNESCAPED_UNICODE) : $payload;
        $this->payload = $encoded;
        $this->attributes["payload"] = $encoded;
    }

    public function getPayload(): ?array
    {
        return $this->payload ? json_decode($this->payload, true) : null;
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    public function getDismissedAt(): ?string
    {
        return $this->dismissedAt;
    }

    public function dismiss(): void
    {
        $statement = $this->connection->prepare(
            "UPDATE user_notices SET dismissed_at = :now WHERE id = :id"
        );
        $statement->execute(["now" => date("Y-m-d H:i:s"), "id" => $this->getId()]);
    }

    public static function findPendingForUser(int $userId): array
    {
        $model = new static();

        $statement = $model->connection->prepare(
            "SELECT * FROM user_notices
             WHERE user_id = :user_id AND dismissed_at IS NULL
             ORDER BY created_at ASC"
        );
        $statement->execute(["user_id" => $userId]);

        $results = [];

        foreach ($statement->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $results[] = static::hydrate($row);
        }

        return $results;
    }
}