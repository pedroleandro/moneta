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
            "SELECT cu.id AS card_user_id, cu.name AS card_user_name, SUM(ts.amount) AS total
         FROM transaction_splits ts
         INNER JOIN transactions t ON t.id = ts.transaction_id
         INNER JOIN card_users cu ON cu.id = ts.card_user_id
         WHERE t.card_invoice_id = :invoice_id AND t.deleted_at IS NULL
         GROUP BY cu.id, cu.name
         ORDER BY cu.name ASC"
        );
        $statement->execute(["invoice_id" => $invoiceId]);

        return $statement->fetchAll(\PDO::FETCH_ASSOC);
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

        $sql = "SELECT COALESCE(SUM(ts.amount), 0) AS total
                FROM transaction_splits ts
                INNER JOIN transactions t ON t.id = ts.transaction_id
                INNER JOIN card_users cu ON cu.id = ts.card_user_id
                WHERE cu.owner_user_id = :user_id
                  AND t.deleted_at IS NULL
                  AND DATE_FORMAT(t.transaction_date, '%Y-%m') = :year_month";

        $params = ["user_id" => $userId, "year_month" => $yearMonth];

        if ($cardUserId) {
            $sql .= " AND ts.card_user_id = :card_user_id";
            $params["card_user_id"] = $cardUserId;
        }

        $statement = $model->connection->prepare($sql);
        $statement->execute($params);

        return (float)$statement->fetch()->total;
    }
}