<?php

namespace App\Models;

use App\Core\AbstractModel;

class Loan extends AbstractModel
{
    protected string $table = "loans";

    protected string $primaryKey = "id";

    protected array $fillable = [
        "owner_user_id",
        "card_user_id",
        "bank_account_id",
        "transaction_id",
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

    public const STATUS_PENDING = "pendente";
    public const STATUS_CONFIRMED = "confirmado";
    public const STATUS_SETTLED = "quitado";

    private ?int $id = null;
    private ?int $ownerUserId = null;
    private ?int $cardUserId = null;
    private ?int $bankAccountId = null;
    private ?int $transactionId = null;
    private ?string $description = null;
    private ?float $amount = null;
    private ?string $dueDate = null;
    private ?string $notes = null;
    private ?string $status = self::STATUS_PENDING;
    private ?string $createdAt = null;
    private ?string $updatedAt = null;
    private ?string $deletedAt = null;

    private float $returnedAmount = 0.0;

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

    public function setBankAccountId(?int $id): void
    {
        $this->bankAccountId = $id;
        $this->attributes["bank_account_id"] = $id;
    }

    public function getBankAccountId(): ?int
    {
        return $this->bankAccountId;
    }

    public function setTransactionId(?int $id): void
    {
        $this->transactionId = $id;
        $this->attributes["transaction_id"] = $id;
    }

    public function getTransactionId(): ?int
    {
        return $this->transactionId;
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
                throw new \InvalidArgumentException("Data de retorno inválida.");
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
        if (!in_array($status, [self::STATUS_PENDING, self::STATUS_CONFIRMED, self::STATUS_SETTLED], true)) {
            throw new \InvalidArgumentException("Status de empréstimo inválido.");
        }

        $this->status = $status;
        $this->attributes["status"] = $status;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isConfirmed(): bool
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    public function isSettled(): bool
    {
        return $this->status === self::STATUS_SETTLED;
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    public function belongsToUser(int $userId): bool
    {
        return $this->getOwnerUserId() === $userId;
    }

    public function getReturnedAmount(): float
    {
        return $this->returnedAmount;
    }

    public function getRemainingAmount(): float
    {
        return max(0, ($this->amount ?? 0) - $this->returnedAmount);
    }

    public function isFullyReturned(): bool
    {
        return $this->getRemainingAmount() <= 0.001;
    }

    public static function findAllForUser(int $userId): array
    {
        $model = new static();

        $statement = $model->connection->prepare(
            "SELECT l.*, cu.name AS card_user_name,
                    COALESCE(lp.total, 0) AS returned_amount
             FROM loans l
             INNER JOIN card_users cu ON cu.id = l.card_user_id
             LEFT JOIN (
                 SELECT loan_id, SUM(amount) AS total
                 FROM loan_payments
                 GROUP BY loan_id
             ) lp ON lp.loan_id = l.id
             WHERE l.owner_user_id = :user_id AND l.deleted_at IS NULL
             ORDER BY l.status ASC, l.due_date ASC, l.created_at DESC"
        );
        $statement->execute(["user_id" => $userId]);

        return static::hydrateWithExtras($statement->fetchAll(\PDO::FETCH_ASSOC));
    }

    public static function findByIdForUser(int $id, int $userId): ?self
    {
        $model = new static();

        $statement = $model->connection->prepare(
            "SELECT l.*, cu.name AS card_user_name,
                    COALESCE(lp.total, 0) AS returned_amount
             FROM loans l
             INNER JOIN card_users cu ON cu.id = l.card_user_id
             LEFT JOIN (
                 SELECT loan_id, SUM(amount) AS total
                 FROM loan_payments
                 GROUP BY loan_id
             ) lp ON lp.loan_id = l.id
             WHERE l.id = :id AND l.owner_user_id = :user_id AND l.deleted_at IS NULL
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
            "SELECT COALESCE(SUM(l.amount), 0) - COALESCE(SUM(lp.total), 0) AS total
             FROM loans l
             LEFT JOIN (
                 SELECT loan_id, SUM(amount) AS total
                 FROM loan_payments
                 GROUP BY loan_id
             ) lp ON lp.loan_id = l.id
             WHERE l.owner_user_id = :user_id AND l.status = :status AND l.deleted_at IS NULL"
        );
        $statement->execute(["user_id" => $userId, "status" => self::STATUS_CONFIRMED]);

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
            $returnedAmount = (float)$row["returned_amount"];
            unset($row["card_user_name"], $row["returned_amount"]);

            $instance = static::hydrate($row);
            $instance->cardUserName = $cardUserName;
            $instance->returnedAmount = $returnedAmount;
            $results[] = $instance;
        }

        return $results;
    }
}