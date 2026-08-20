<?php

namespace App\Models;

use App\Core\AbstractModel;

class BankAccount extends AbstractModel
{
    protected string $table = "bank_accounts";

    protected string $primaryKey = "id";

    protected array $fillable = [
        "user_id",
        "name",
        "type",
        "bank_name",
        "initial_balance",
        "color",
        "icon",
        "is_active",
    ];

    protected array $required = [
        "name" => "O campo NOME é obrigatório.",
        "type" => "O campo TIPO é obrigatório.",
    ];

    protected bool $timestamps = true;

    protected bool $softDelete = true;

    public const TYPE_CHECKING = "checking";
    public const TYPE_SAVINGS = "savings";
    public const TYPE_WALLET = "wallet";
    public const TYPE_INVESTMENT = "investment";
    public const TYPE_PAYMENT = "payment";

    private const TYPES = [
        self::TYPE_CHECKING,
        self::TYPE_SAVINGS,
        self::TYPE_WALLET,
        self::TYPE_INVESTMENT,
        self::TYPE_PAYMENT,
    ];

    private ?int $id = null;
    private ?int $userId = null;
    private ?string $name = null;
    private ?string $type = null;
    private ?string $bankName = null;
    private ?float $initialBalance = null;
    private ?float $currentBalance = null;
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
            throw new \InvalidArgumentException("O nome da conta deve ter pelo menos 2 caracteres.");
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
        if (!in_array($type, self::TYPES, true)) {
            throw new \InvalidArgumentException("Tipo de conta inválido.");
        }

        $this->type = $type;
        $this->attributes["type"] = $type;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function getTypeLabel(): string
    {
        return match ($this->type) {
            self::TYPE_CHECKING => "Conta Corrente",
            self::TYPE_SAVINGS => "Poupança",
            self::TYPE_WALLET => "Carteira",
            self::TYPE_INVESTMENT => "Investimento",
            self::TYPE_PAYMENT => "Conta de Pagamento",
            default => "-",
        };
    }

    public function setBankName(?string $bankName): void
    {
        $bankName = $bankName ? trim($bankName) : null;
        $this->bankName = $bankName;
        $this->attributes["bank_name"] = $bankName;
    }

    public function getBankName(): ?string
    {
        return $this->bankName;
    }

    public function setInitialBalance(float|string $value): void
    {
        $value = (float)str_replace(",", ".", (string)$value);

        $this->initialBalance = $value;
        $this->attributes["initial_balance"] = $value;

        if ($this->currentBalance === null) {
            $this->currentBalance = $value;
            $this->attributes["current_balance"] = $value;
        }
    }

    public function getInitialBalance(): ?float
    {
        return $this->initialBalance;
    }

    public function getCurrentBalance(): ?float
    {
        return $this->currentBalance;
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

    public static function typeOptions(): array
    {
        return [
            self::TYPE_CHECKING => "Conta Corrente",
            self::TYPE_SAVINGS => "Poupança",
            self::TYPE_WALLET => "Carteira",
            self::TYPE_INVESTMENT => "Investimento",
            self::TYPE_PAYMENT => "Conta de Pagamento",
        ];
    }

    public static function findAllForUser(int $userId): array
    {
        $model = new static();

        $statement = $model->connection->prepare(
            "SELECT * FROM bank_accounts
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
            "SELECT * FROM bank_accounts
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

        $checks = [
            ["sql" => "SELECT COUNT(*) AS total FROM transactions WHERE bank_account_id = :id", "params" => ["id" => $id]],
            ["sql" => "SELECT COUNT(*) AS total FROM recurrences WHERE bank_account_id = :id", "params" => ["id" => $id]],
            [
                "sql" => "SELECT COUNT(*) AS total FROM account_transfers WHERE from_account_id = :from_id OR to_account_id = :to_id",
                "params" => ["from_id" => $id, "to_id" => $id],
            ],
        ];

        foreach ($checks as $check) {
            $statement = $this->connection->prepare($check["sql"]);
            $statement->execute($check["params"]);

            if ((int)$statement->fetch()->total > 0) {
                return true;
            }
        }

        return false;
    }

    public function adjustBalance(float $delta): void
    {
        $statement = $this->connection->prepare(
            "UPDATE bank_accounts SET current_balance = current_balance + :delta WHERE id = :id"
        );
        $statement->execute(["delta" => $delta, "id" => $this->getId()]);
    }

    public function syncCurrentBalanceWithInitial(): void
    {
        $this->currentBalance = $this->initialBalance;
        $this->attributes["current_balance"] = $this->initialBalance;
    }
}