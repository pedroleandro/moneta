<?php

namespace App\Models;

use App\Core\AbstractModel;

class CardInvoice extends AbstractModel
{
    protected string $table = "card_invoices";

    protected string $primaryKey = "id";

    protected array $fillable = [
        "credit_card_id",
        "reference_month",
        "closing_date",
        "due_date",
        "total_amount",
        "status",
    ];

    protected array $required = [];

    protected bool $timestamps = true;

    protected bool $softDelete = true;

    public const STATUS_OPEN = "aberta";
    public const STATUS_CLOSED = "fechada";
    public const STATUS_PAID = "paga";

    private ?int $id = null;
    private ?int $creditCardId = null;
    private ?string $referenceMonth = null;
    private ?string $closingDate = null;
    private ?string $dueDate = null;
    private ?float $totalAmount = 0.0;
    private ?string $status = self::STATUS_OPEN;
    private ?string $createdAt = null;
    private ?string $updatedAt = null;
    private ?string $deletedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setCreditCardId(int $id): void
    {
        $this->creditCardId = $id;
        $this->attributes["credit_card_id"] = $id;
    }

    public function getCreditCardId(): ?int
    {
        return $this->creditCardId;
    }

    public function setReferenceMonth(string $date): void
    {
        $this->referenceMonth = $date;
        $this->attributes["reference_month"] = $date;
    }

    public function getReferenceMonth(): ?string
    {
        return $this->referenceMonth;
    }

    public function setClosingDate(string $date): void
    {
        $this->closingDate = $date;
        $this->attributes["closing_date"] = $date;
    }

    public function getClosingDate(): ?string
    {
        return $this->closingDate;
    }

    public function setDueDate(string $date): void
    {
        $this->dueDate = $date;
        $this->attributes["due_date"] = $date;
    }

    public function getDueDate(): ?string
    {
        return $this->dueDate;
    }

    public function setStatus(string $status): void
    {
        if (!in_array($status, [self::STATUS_OPEN, self::STATUS_CLOSED, self::STATUS_PAID], true)) {
            throw new \InvalidArgumentException("Status de fatura inválido.");
        }

        $this->status = $status;
        $this->attributes["status"] = $status;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function getTotalAmount(): ?float
    {
        return $this->totalAmount;
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    public function getPaidAmount(): float
    {
        $statement = $this->connection->prepare(
            "SELECT COALESCE(SUM(amount), 0) AS total FROM card_invoice_payments WHERE card_invoice_id = :id"
        );
        $statement->execute(["id" => $this->getId()]);

        return (float)$statement->fetch()->total;
    }

    public function getRemainingAmount(): float
    {
        return max(0, ($this->getTotalAmount() ?? 0) - $this->getPaidAmount());
    }

    public function recalculateTotal(): float
    {
        $statement = $this->connection->prepare(
            "SELECT COALESCE(SUM(amount), 0) AS total
             FROM transactions
             WHERE card_invoice_id = :id AND deleted_at IS NULL"
        );
        $statement->execute(["id" => $this->getId()]);

        $total = (float)$statement->fetch()->total;

        $this->totalAmount = $total;
        $this->attributes["total_amount"] = $total;
        $this->save();

        return $total;
    }

    public static function findByCardAndMonth(int $creditCardId, string $referenceMonth): ?self
    {
        $model = new static();

        $statement = $model->connection->prepare(
            "SELECT * FROM card_invoices
             WHERE credit_card_id = :credit_card_id AND reference_month = :reference_month AND deleted_at IS NULL
             LIMIT 1"
        );
        $statement->execute([
            "credit_card_id" => $creditCardId,
            "reference_month" => $referenceMonth,
        ]);

        $row = $statement->fetch(\PDO::FETCH_ASSOC);

        return $row ? static::hydrate($row) : null;
    }

    public static function findByIdForUser(int $id, int $userId): ?self
    {
        $model = new static();

        $statement = $model->connection->prepare(
            "SELECT ci.* FROM card_invoices ci
             INNER JOIN credit_cards cc ON cc.id = ci.credit_card_id
             WHERE ci.id = :id AND cc.user_id = :user_id AND ci.deleted_at IS NULL
             LIMIT 1"
        );
        $statement->execute(["id" => $id, "user_id" => $userId]);

        $row = $statement->fetch(\PDO::FETCH_ASSOC);

        return $row ? static::hydrate($row) : null;
    }

    public static function findAllForCard(int $creditCardId): array
    {
        $model = new static();

        $statement = $model->connection->prepare(
            "SELECT * FROM card_invoices
             WHERE credit_card_id = :credit_card_id AND deleted_at IS NULL
             ORDER BY reference_month DESC"
        );
        $statement->execute(["credit_card_id" => $creditCardId]);

        $results = [];

        foreach ($statement->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $results[] = static::hydrate($row);
        }

        return $results;
    }

    public static function findUpcomingForUser(int $userId, int $limit = 5): array
    {
        $model = new static();

        $statement = $model->connection->prepare(
            "SELECT ci.*, cc.name AS credit_card_name
             FROM card_invoices ci
             INNER JOIN credit_cards cc ON cc.id = ci.credit_card_id
             WHERE cc.user_id = :user_id AND ci.status != 'paga' AND ci.deleted_at IS NULL
             ORDER BY ci.due_date ASC
             LIMIT :limit"
        );
        $statement->bindValue(":user_id", $userId, \PDO::PARAM_INT);
        $statement->bindValue(":limit", $limit, \PDO::PARAM_INT);
        $statement->execute();

        $results = [];

        foreach ($statement->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $cardName = $row["credit_card_name"];
            unset($row["credit_card_name"]);

            $instance = static::hydrate($row);
            $instance->creditCardName = $cardName;
            $results[] = $instance;
        }

        return $results;
    }

    private ?string $creditCardName = null;

    public function getCreditCardName(): ?string
    {
        return $this->creditCardName;
    }

    public static function findWindowForCard(int $creditCardId, int $pastCount = 1, int $futureCount = 2): array
    {
        $model = new static();

        $currentStatement = $model->connection->prepare(
            "SELECT * FROM card_invoices
             WHERE credit_card_id = :id AND status = 'aberta' AND deleted_at IS NULL
             ORDER BY due_date ASC LIMIT 1"
        );
        $currentStatement->execute(["id" => $creditCardId]);
        $currentRow = $currentStatement->fetch(\PDO::FETCH_ASSOC);

        if (!$currentRow) {
            $fallbackStatement = $model->connection->prepare(
                "SELECT * FROM card_invoices
                 WHERE credit_card_id = :id AND deleted_at IS NULL
                 ORDER BY ABS(DATEDIFF(due_date, CURDATE())) ASC LIMIT 1"
            );
            $fallbackStatement->execute(["id" => $creditCardId]);
            $currentRow = $fallbackStatement->fetch(\PDO::FETCH_ASSOC);
        }

        if (!$currentRow) {
            return ["past" => [], "current" => null, "future" => []];
        }

        $current = static::hydrate($currentRow);

        $pastStatement = $model->connection->prepare(
            "SELECT * FROM card_invoices
             WHERE credit_card_id = :id AND due_date < :due_date AND deleted_at IS NULL
             ORDER BY due_date DESC LIMIT :limit"
        );
        $pastStatement->bindValue(":id", $creditCardId, \PDO::PARAM_INT);
        $pastStatement->bindValue(":due_date", $current->getDueDate());
        $pastStatement->bindValue(":limit", $pastCount, \PDO::PARAM_INT);
        $pastStatement->execute();
        $past = array_reverse(array_map(
            fn($row) => static::hydrate($row),
            $pastStatement->fetchAll(\PDO::FETCH_ASSOC)
        ));

        $futureStatement = $model->connection->prepare(
            "SELECT * FROM card_invoices
             WHERE credit_card_id = :id AND due_date > :due_date AND deleted_at IS NULL
             ORDER BY due_date ASC LIMIT :limit"
        );
        $futureStatement->bindValue(":id", $creditCardId, \PDO::PARAM_INT);
        $futureStatement->bindValue(":due_date", $current->getDueDate());
        $futureStatement->bindValue(":limit", $futureCount, \PDO::PARAM_INT);
        $futureStatement->execute();
        $future = array_map(
            fn($row) => static::hydrate($row),
            $futureStatement->fetchAll(\PDO::FETCH_ASSOC)
        );

        return ["past" => $past, "current" => $current, "future" => $future];
    }
}