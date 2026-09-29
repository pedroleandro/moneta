<?php

namespace App\Models;

use App\Core\AbstractModel;

class Debt extends AbstractModel
{
    protected string $table = "debts";

    protected string $primaryKey = "id";

    protected array $fillable = [
        "owner_user_id",
        "card_user_id",
        "description",
        "amount",
        "due_date",
        "notes",
        "status",
    ];

    protected array $required = [
        "card_user_id" => "A PESSOA é obrigatória.",
        "description" => "A DESCRIÇÃO é obrigatória.",
        "amount" => "O VALOR é obrigatório.",
    ];

    protected bool $timestamps = true;

    protected bool $softDelete = true;

    public const STATUS_OPEN = "aberto";
    public const STATUS_PAID = "pago";

    private ?int $id = null;
    private ?int $ownerUserId = null;
    private ?int $cardUserId = null;
    private ?string $description = null;
    private ?float $amount = null;
    private ?string $dueDate = null;
    private ?string $notes = null;
    private ?string $status = self::STATUS_OPEN;
    private ?string $createdAt = null;
    private ?string $updatedAt = null;
    private ?string $deletedAt = null;

    private float $paidAmount = 0.0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setOwnerUserId(int $id): void
    {
        $this->ownerUserId = $id;
        $this->attributes["owner_user_id"] = $id;
    }

    public function getOwnerUserId(): ?int
    {
        return $this->ownerUserId;
    }

    public function setCardUserId(int $id): void
    {
        $this->cardUserId = $id;
        $this->attributes["card_user_id"] = $id;
    }

    public function getCardUserId(): ?int
    {
        return $this->cardUserId;
    }

    public function setDescription(string $description): void
    {
        $description = trim(strip_tags($description));

        if (strlen($description) < 2) {
            throw new \InvalidArgumentException("A descrição deve ter pelo menos 2 caracteres.");
        }

        $this->description = $description;
        $this->attributes["description"] = $description;
    }

    public function getDescription(): ?string
    {
        return $this->description;
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

    public function setDueDate(?string $date): void
    {
        if ($date) {
            $parsed = \DateTime::createFromFormat("Y-m-d", $date);

            if (!$parsed) {
                throw new \InvalidArgumentException("Data de vencimento inválida.");
            }
        }

        $this->dueDate = $date;
        $this->attributes["due_date"] = $date;
    }

    public function getDueDate(): ?string
    {
        return $this->dueDate;
    }

    public function setNotes(?string $notes): void
    {
        $notes = $notes ? trim($notes) : null;
        $this->notes = $notes;
        $this->attributes["notes"] = $notes;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setStatus(string $status): void
    {
        if (!in_array($status, [self::STATUS_OPEN, self::STATUS_PAID], true)) {
            throw new \InvalidArgumentException("Status de dívida inválido.");
        }

        $this->status = $status;
        $this->attributes["status"] = $status;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    public function belongsToUser(int $userId): bool
    {
        return $this->getOwnerUserId() === $userId;
    }

    public function getPaidAmount(): float
    {
        return $this->paidAmount;
    }

    public function getRemainingAmount(): float
    {
        return max(0, ($this->amount ?? 0) - $this->paidAmount);
    }

    public function isFullyPaid(): bool
    {
        return $this->getRemainingAmount() <= 0.001;
    }

    public static function findAllForUser(int $userId): array
    {
        $model = new static();

        $statement = $model->connection->prepare(
            "SELECT d.*, cu.name AS card_user_name,
                    COALESCE(dp.total, 0) AS paid_amount
             FROM debts d
             INNER JOIN card_users cu ON cu.id = d.card_user_id
             LEFT JOIN (
                 SELECT debt_id, SUM(amount) AS total
                 FROM debt_payments
                 GROUP BY debt_id
             ) dp ON dp.debt_id = d.id
             WHERE d.owner_user_id = :user_id AND d.deleted_at IS NULL
             ORDER BY d.status ASC, d.due_date ASC, d.created_at DESC"
        );
        $statement->execute(["user_id" => $userId]);

        return static::hydrateWithExtras($statement->fetchAll(\PDO::FETCH_ASSOC));
    }

    public static function findByIdForUser(int $id, int $userId): ?self
    {
        $model = new static();

        $statement = $model->connection->prepare(
            "SELECT d.*, cu.name AS card_user_name,
                    COALESCE(dp.total, 0) AS paid_amount
             FROM debts d
             INNER JOIN card_users cu ON cu.id = d.card_user_id
             LEFT JOIN (
                 SELECT debt_id, SUM(amount) AS total
                 FROM debt_payments
                 GROUP BY debt_id
             ) dp ON dp.debt_id = d.id
             WHERE d.id = :id AND d.owner_user_id = :user_id AND d.deleted_at IS NULL
             LIMIT 1"
        );
        $statement->execute(["id" => $id, "user_id" => $userId]);

        $row = $statement->fetch(\PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return static::hydrateWithExtras([$row])[0];
    }

    public static function getTotalOutstandingForUser(int $userId): float
    {
        $model = new static();

        $statement = $model->connection->prepare(
            "SELECT COALESCE(SUM(d.amount), 0) - COALESCE(SUM(dp.total), 0) AS total
             FROM debts d
             LEFT JOIN (
                 SELECT debt_id, SUM(amount) AS total
                 FROM debt_payments
                 GROUP BY debt_id
             ) dp ON dp.debt_id = d.id
             WHERE d.owner_user_id = :user_id AND d.status = :status AND d.deleted_at IS NULL"
        );
        $statement->execute(["user_id" => $userId, "status" => self::STATUS_OPEN]);

        return max(0, (float)$statement->fetch()->total);
    }

    private ?string $cardUserName = null;

    public function getCardUserName(): ?string
    {
        return $this->cardUserName;
    }

    private static function hydrateWithExtras(array $rows): array
    {
        $results = [];

        foreach ($rows as $row) {
            $cardUserName = $row["card_user_name"];
            $paidAmount = (float)$row["paid_amount"];
            unset($row["card_user_name"], $row["paid_amount"]);

            $instance = static::hydrate($row);
            $instance->cardUserName = $cardUserName;
            $instance->paidAmount = $paidAmount;
            $results[] = $instance;
        }

        return $results;
    }
}