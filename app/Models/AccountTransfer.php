<?php

namespace App\Models;

use App\Core\AbstractModel;

class AccountTransfer extends AbstractModel
{
    protected string $table = "account_transfers";

    protected string $primaryKey = "id";

    protected array $fillable = [
        "user_id",
        "from_account_id",
        "to_account_id",
        "amount",
        "transfer_date",
        "description",
    ];

    protected array $required = [
        "from_account_id" => "A conta de ORIGEM é obrigatória.",
        "to_account_id" => "A conta de DESTINO é obrigatória.",
        "amount" => "O VALOR é obrigatório.",
        "transfer_date" => "A DATA é obrigatória.",
    ];

    protected bool $timestamps = true;

    protected bool $softDelete = true;

    private ?int $id = null;
    private ?int $userId = null;
    private ?int $fromAccountId = null;
    private ?int $toAccountId = null;
    private ?float $amount = null;
    private ?string $transferDate = null;
    private ?string $description = null;
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

    public function setFromAccountId(int $id): void
    {
        $this->fromAccountId = $id;
        $this->attributes["from_account_id"] = $id;
    }

    public function getFromAccountId(): ?int
    {
        return $this->fromAccountId;
    }

    public function setToAccountId(int $id): void
    {
        $this->toAccountId = $id;
        $this->attributes["to_account_id"] = $id;
    }

    public function getToAccountId(): ?int
    {
        return $this->toAccountId;
    }

    public function setAmount(float|string $value): void
    {
        $value = (float)str_replace(",", ".", (string)$value);

        if ($value <= 0) {
            throw new \InvalidArgumentException("O valor deve ser maior que zero.");
        }

        $this->amount = $value;
        $this->attributes["amount"] = $value;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setTransferDate(string $date): void
    {
        $parsed = \DateTime::createFromFormat("Y-m-d", $date);

        if (!$parsed) {
            throw new \InvalidArgumentException("Data da transferência inválida.");
        }

        $this->transferDate = $date;
        $this->attributes["transfer_date"] = $date;
    }

    public function getTransferDate(): ?string
    {
        return $this->transferDate;
    }

    public function setDescription(?string $description): void
    {
        $description = $description ? trim($description) : null;
        $this->description = $description;
        $this->attributes["description"] = $description;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    public function belongsToUser(int $userId): bool
    {
        return $this->getUserId() === $userId;
    }

    public static function findAllForUser(int $userId): array
    {
        $model = new static();

        $statement = $model->connection->prepare(
            "SELECT at.*, fa.name AS from_account_name, ta.name AS to_account_name
             FROM account_transfers at
             INNER JOIN bank_accounts fa ON fa.id = at.from_account_id
             INNER JOIN bank_accounts ta ON ta.id = at.to_account_id
             WHERE at.user_id = :user_id AND at.deleted_at IS NULL
             ORDER BY at.transfer_date DESC, at.id DESC"
        );
        $statement->execute(["user_id" => $userId]);

        $results = [];

        foreach ($statement->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $fromName = $row["from_account_name"];
            $toName = $row["to_account_name"];
            unset($row["from_account_name"], $row["to_account_name"]);

            $instance = static::hydrate($row);
            $instance->fromAccountName = $fromName;
            $instance->toAccountName = $toName;

            $results[] = $instance;
        }

        return $results;
    }

    private ?string $fromAccountName = null;
    private ?string $toAccountName = null;

    public function getFromAccountName(): ?string
    {
        return $this->fromAccountName;
    }

    public function getToAccountName(): ?string
    {
        return $this->toAccountName;
    }

    public static function findByIdForUser(int $id, int $userId): ?self
    {
        $model = new static();

        $statement = $model->connection->prepare(
            "SELECT * FROM account_transfers
             WHERE id = :id AND user_id = :user_id AND deleted_at IS NULL
             LIMIT 1"
        );
        $statement->execute(["id" => $id, "user_id" => $userId]);

        $row = $statement->fetch(\PDO::FETCH_ASSOC);

        return $row ? static::hydrate($row) : null;
    }
}