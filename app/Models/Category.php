<?php

namespace App\Models;

use App\Core\AbstractModel;

class Category extends AbstractModel
{
    protected string $table = "categories";

    protected string $primaryKey = "id";

    protected array $fillable = [
        "user_id",
        "parent_id",
        "name",
        "type",
        "color",
        "icon",
    ];

    protected array $required = [
        "name" => "O campo NOME é obrigatório.",
        "type" => "O campo TIPO é obrigatório.",
    ];

    protected bool $timestamps = true;

    protected bool $softDelete = true;

    private ?int $id = null;
    private ?int $userId = null;
    private ?int $parentId = null;
    private ?string $name = null;
    private ?string $type = null;
    private ?string $color = null;
    private ?string $icon = null;
    private ?string $createdAt = null;
    private ?string $updatedAt = null;
    private ?string $deletedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setUserId(?int $userId): void
    {
        $this->userId = $userId;
        $this->attributes["user_id"] = $userId;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setParentId(?int $parentId): void
    {
        $this->parentId = $parentId;
        $this->attributes["parent_id"] = $parentId;
    }

    public function getParentId(): ?int
    {
        return $this->parentId;
    }

    public function setName(string $name): void
    {
        $name = trim(strip_tags($name));

        if (strlen($name) < 2) {
            throw new \InvalidArgumentException("O nome da categoria deve ter pelo menos 2 caracteres.");
        }

        $this->name = $name;
        $this->attributes["name"] = $name;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setType(string $type): void
    {
        if (!in_array($type, ["receita", "despesa"], true)) {
            throw new \InvalidArgumentException("O tipo da categoria deve ser 'receita' ou 'despesa'.");
        }

        $this->type = $type;
        $this->attributes["type"] = $type;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setColor(?string $color): void
    {
        if ($color && !preg_match('/^#[0-9A-Fa-f]{6}$/', $color)) {
            throw new \InvalidArgumentException("A cor deve estar no formato hexadecimal, ex: #4CAF50.");
        }

        $this->color = $color;
        $this->attributes["color"] = $color;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setIcon(?string $icon): void
    {
        $icon = $icon ? trim($icon) : null;
        $this->icon = $icon;
        $this->attributes["icon"] = $icon;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
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

    public function isSystemDefault(): bool
    {
        return $this->getUserId() === null;
    }

    public function belongsToUser(int $userId): bool
    {
        return $this->getUserId() === $userId;
    }

    public static function findAllForUser(int $userId): array
    {
        $model = new static();

        $statement = $model->connection->prepare(
            "SELECT * FROM categories
             WHERE (user_id = :user_id OR user_id IS NULL)
               AND deleted_at IS NULL
             ORDER BY name ASC"
        );
        $statement->execute(["user_id" => $userId]);

        $results = [];

        foreach ($statement->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $results[] = static::hydrate($row);
        }

        return $results;
    }

    public function isInUse(): bool
    {
        $id = $this->getId();
        $tables = ["transactions", "recurrences", "installment_purchases"];

        foreach ($tables as $table) {
            $statement = $this->connection->prepare(
                "SELECT COUNT(*) AS total FROM {$table} WHERE category_id = :id"
            );
            $statement->execute(["id" => $id]);

            if ((int)$statement->fetch()->total > 0) {
                return true;
            }
        }

        return false;
    }

    public static function findByIdForUser(int $id, int $userId): ?self
    {
        $model = new static();

        $statement = $model->connection->prepare(
            "SELECT * FROM categories
             WHERE id = :id
               AND (user_id = :user_id OR user_id IS NULL)
               AND deleted_at IS NULL
             LIMIT 1"
        );
        $statement->execute(["id" => $id, "user_id" => $userId]);

        $row = $statement->fetch(\PDO::FETCH_ASSOC);

        return $row ? static::hydrate($row) : null;
    }

    public function duplicateForUser(int $userId): self
    {
        $copy = new self();

        $copy->fill([
            "user_id" => $userId,
            "name"    => $this->getName(),
            "type"    => $this->getType(),
            "color"   => $this->getColor(),
            "icon"    => $this->getIcon(),
        ]);

        $copy->save();

        return $copy;
    }
}