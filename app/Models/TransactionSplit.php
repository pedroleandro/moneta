<?php

namespace App\Models;

use App\Core\AbstractModel;

class TransactionSplit extends AbstractModel
{
    protected string $table = "transaction_splits";

    protected string $primaryKey = "id";

    protected array $fillable = [
        "transaction_id",
        "card_user_id",
        "amount",
    ];

    protected array $required = [
        "transaction_id" => "O LANÇAMENTO é obrigatório.",
        "card_user_id" => "A PESSOA é obrigatória.",
        "amount" => "O VALOR é obrigatório.",
    ];

    protected bool $timestamps = true;

    protected bool $softDelete = false;

    private ?int $id = null;
    private ?int $transactionId = null;
    private ?int $cardUserId = null;
    private ?float $amount = null;
    private ?string $createdAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setTransactionId(int $id): void
    {
        $this->transactionId = $id;
        $this->attributes["transaction_id"] = $id;
    }

    public function getTransactionId(): ?int
    {
        return $this->transactionId;
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

    public function setAmount(float|string $value): void
    {
        $value = (float)str_replace(",", ".", (string)$value);

        if ($value <= 0) {
            throw new \InvalidArgumentException("O valor da divisão deve ser maior que zero.");
        }

        $this->amount = $value;
        $this->attributes["amount"] = $value;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public static function deleteAllForTransaction(int $transactionId): void
    {
        $model = new static();
        $statement = $model->connection->prepare(
            "DELETE FROM transaction_splits WHERE transaction_id = :transaction_id"
        );
        $statement->execute(["transaction_id" => $transactionId]);
    }

    public static function findAllForTransaction(int $transactionId): array
    {
        $model = new static();

        $statement = $model->connection->prepare(
            "SELECT ts.*, cu.name AS card_user_name
             FROM transaction_splits ts
             INNER JOIN card_users cu ON cu.id = ts.card_user_id
             WHERE ts.transaction_id = :transaction_id
             ORDER BY cu.name ASC"
        );
        $statement->execute(["transaction_id" => $transactionId]);

        $results = [];

        foreach ($statement->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $cardUserName = $row["card_user_name"];
            unset($row["card_user_name"]);

            $instance = static::hydrate($row);
            $instance->cardUserName = $cardUserName;
            $results[] = $instance;
        }

        return $results;
    }

    private ?string $cardUserName = null;

    public function getCardUserName(): ?string
    {
        return $this->cardUserName;
    }

    public static function findTotalsForInvoice(int $invoiceId): array
    {
        $model = new static();

        $statement = $model->connection->prepare(
            "SELECT cu.id AS card_user_id, cu.name AS card_user_name,
                    COALESCE(splits.total, 0) AS split_total,
                    COALESCE(payments.total, 0) AS paid_total
             FROM card_users cu
             LEFT JOIN (
                 SELECT ts.card_user_id, SUM(ts.amount) AS total
                 FROM transaction_splits ts
                 INNER JOIN transactions t ON t.id = ts.transaction_id
                 WHERE t.card_invoice_id = :invoice_id_1 AND t.deleted_at IS NULL
                 GROUP BY ts.card_user_id
             ) splits ON splits.card_user_id = cu.id
             LEFT JOIN (
                 SELECT paying_card_user_id, SUM(amount) AS total
                 FROM card_invoice_payments
                 WHERE card_invoice_id = :invoice_id_2 AND paying_card_user_id IS NOT NULL
                 GROUP BY paying_card_user_id
             ) payments ON payments.paying_card_user_id = cu.id
             WHERE splits.total IS NOT NULL OR payments.total IS NOT NULL
             ORDER BY cu.name ASC"
        );
        $statement->execute(["invoice_id_1" => $invoiceId, "invoice_id_2" => $invoiceId]);

        $results = [];

        foreach ($statement->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $results[] = [
                "card_user_id" => $row["card_user_id"],
                "card_user_name" => $row["card_user_name"],
                "gross" => (float)$row["split_total"],
                "total" => max(0, (float)$row["split_total"] - (float)$row["paid_total"]),
            ];
        }

        return $results;
    }

    public static function getTotalOwedToUser(int $userId): float
    {
        $model = new static();

        $statement = $model->connection->prepare(
            "SELECT COALESCE(SUM(ts.amount), 0) AS total
             FROM transaction_splits ts
             INNER JOIN transactions t ON t.id = ts.transaction_id
             INNER JOIN card_users cu ON cu.id = ts.card_user_id
             WHERE cu.owner_user_id = :user_id AND t.deleted_at IS NULL"
        );
        $statement->execute(["user_id" => $userId]);

        return (float)$statement->fetch()->total;
    }

    public static function getOwedToUserForMonth(int $userId, string $yearMonth, ?int $cardUserId = null): float
    {
        $model = new static();
        $splitSql = "SELECT COALESCE(SUM(ts.amount), 0) AS total
                  FROM transaction_splits ts
                  INNER JOIN transactions t ON t.id = ts.transaction_id
                  LEFT JOIN card_invoices ci ON ci.id = t.card_invoice_id
                  INNER JOIN card_users cu ON cu.id = ts.card_user_id
                  WHERE cu.owner_user_id = :user_id
                    AND t.deleted_at IS NULL
                    AND DATE_FORMAT(COALESCE(ci.due_date, t.transaction_date), '%Y-%m') = :year_month";
        $paidSql = "SELECT COALESCE(SUM(cip.amount), 0) AS total
                 FROM card_invoice_payments cip
                 INNER JOIN card_users cu ON cu.id = cip.paying_card_user_id
                 WHERE cu.owner_user_id = :user_id
                   AND DATE_FORMAT(cip.payment_date, '%Y-%m') = :year_month";
        $params = ["user_id" => $userId, "year_month" => $yearMonth];
        if ($cardUserId) {
            $splitSql .= " AND ts.card_user_id = :card_user_id";
            $paidSql .= " AND cip.paying_card_user_id = :card_user_id";
            $params["card_user_id"] = $cardUserId;
        }
        $splitStatement = $model->connection->prepare($splitSql);
        $splitStatement->execute($params);
        $owed = (float)$splitStatement->fetch()->total;
        $paidStatement = $model->connection->prepare($paidSql);
        $paidStatement->execute($params);
        $paid = (float)$paidStatement->fetch()->total;
        return max(0, $owed - $paid);
    }
}