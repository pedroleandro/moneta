<?php

namespace App\Models;

use App\Core\AbstractModel;

class CreditCard extends AbstractModel
{
    protected string $table = "credit_cards";

    protected string $primaryKey = "id";

    protected array $fillable = [
        "user_id",
        "name",
        "card_limit",
        "closing_day",
        "due_day",
        "color",
        "icon",
        "is_active",
    ];

    protected array $required = [
        "name" => "O campo NOME é obrigatório.",
        "closing_day" => "O DIA DE FECHAMENTO é obrigatório.",
        "due_day" => "O DIA DE VENCIMENTO é obrigatório.",
    ];

    protected bool $timestamps = true;

    protected bool $softDelete = true;

    private ?int $id = null;
    private ?int $userId = null;
    private ?string $name = null;
    private ?float $cardLimit = null;
    private ?int $closingDay = null;
    private ?int $dueDay = null;
    private ?string $color = null;
    private ?string $icon = null;
    private ?int $isActive = 1;
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

    public function setName(string $name): void
    {
        $name = trim(strip_tags($name));

        if (strlen($name) < 2) {
            throw new \InvalidArgumentException("O nome do cartão deve ter pelo menos 2 caracteres.");
        }

        $this->name = $name;
        $this->attributes["name"] = $name;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setCardLimit(float|string $value): void
    {
        $value = (float)str_replace(",", ".", (string)$value);

        if ($value < 0) {
            throw new \InvalidArgumentException("O limite do cartão não pode ser negativo.");
        }

        $this->cardLimit = $value;
        $this->attributes["card_limit"] = $value;
    }

    public function getCardLimit(): ?float
    {
        return $this->cardLimit;
    }

    public function setClosingDay(int|string $day): void
    {
        $day = (int)$day;

        if ($day < 1 || $day > 31) {
            throw new \InvalidArgumentException("O dia de fechamento deve estar entre 1 e 31.");
        }

        $this->closingDay = $day;
        $this->attributes["closing_day"] = $day;
    }

    public function getClosingDay(): ?int
    {
        return $this->closingDay;
    }

    public function setDueDay(int|string $day): void
    {
        $day = (int)$day;

        if ($day < 1 || $day > 31) {
            throw new \InvalidArgumentException("O dia de vencimento deve estar entre 1 e 31.");
        }

        $this->dueDay = $day;
        $this->attributes["due_day"] = $day;
    }

    public function getDueDay(): ?int
    {
        return $this->dueDay;
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

    public function setIsActive(bool|int $isActive): void
    {
        $value = $isActive ? 1 : 0;
        $this->isActive = $value;
        $this->attributes["is_active"] = $value;
    }

    public function isActive(): bool
    {
        return (bool)$this->isActive;
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

    public function belongsToUser(int $userId): bool
    {
        return $this->getUserId() === $userId;
    }

    public static function findAllForUser(int $userId): array
    {
        $model = new static();

        $statement = $model->connection->prepare(
            "SELECT * FROM credit_cards
             WHERE user_id = :user_id AND deleted_at IS NULL
             ORDER BY name ASC"
        );
        $statement->execute(["user_id" => $userId]);

        $results = [];

        foreach ($statement->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $results[] = static::hydrate($row);
        }

        return $results;
    }

    public static function findByIdForUser(int $id, int $userId): ?self
    {
        $model = new static();

        $statement = $model->connection->prepare(
            "SELECT * FROM credit_cards
             WHERE id = :id AND user_id = :user_id AND deleted_at IS NULL
             LIMIT 1"
        );
        $statement->execute(["id" => $id, "user_id" => $userId]);

        $row = $statement->fetch(\PDO::FETCH_ASSOC);

        return $row ? static::hydrate($row) : null;
    }

    public function isInUse(): bool
    {
        $id = $this->getId();

        $queries = [
            "SELECT COUNT(*) AS total FROM transactions WHERE credit_card_id = :id",
            "SELECT COUNT(*) AS total FROM card_invoices WHERE credit_card_id = :id",
            "SELECT COUNT(*) AS total FROM card_users WHERE credit_card_id = :id",
            "SELECT COUNT(*) AS total FROM installment_purchases WHERE credit_card_id = :id",
            "SELECT COUNT(*) AS total FROM recurrences WHERE credit_card_id = :id",
        ];

        foreach ($queries as $sql) {
            $statement = $this->connection->prepare($sql);
            $statement->execute(["id" => $id]);

            if ((int)$statement->fetch()->total > 0) {
                return true;
            }
        }

        return false;
    }
}