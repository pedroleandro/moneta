<?php

namespace App\Models;

use App\Core\AbstractModel;

class RecurrenceSplit extends AbstractModel
{
    protected string $table = "recurrence_splits";

    protected string $primaryKey = "id";

    protected array $fillable = [
        "recurrence_id",
        "card_user_id",
        "amount",
    ];

    protected array $required = [
        "recurrence_id" => "A RECORRÊNCIA é obrigatória.",
        "card_user_id" => "A PESSOA é obrigatória.",
        "amount" => "O VALOR é obrigatório.",
    ];

    protected bool $timestamps = false;

    protected bool $softDelete = false;

    private ?int $id = null;
    private ?int $recurrenceId = null;
    private ?int $cardUserId = null;
    private ?float $amount = null;
    private ?string $createdAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setRecurrenceId(int $id): void
    {
        $this->recurrenceId = $id;
        $this->attributes["recurrence_id"] = $id;
    }

    public function getRecurrenceId(): ?int
    {
        return $this->recurrenceId;
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

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    public static function deleteAllForRecurrence(int $recurrenceId): void
    {
        $model = new static();
        $statement = $model->connection->prepare(
            "DELETE FROM recurrence_splits WHERE recurrence_id = :recurrence_id"
        );
        $statement->execute(["recurrence_id" => $recurrenceId]);
    }

    public static function findAllForRecurrence(int $recurrenceId): array
    {
        $model = new static();

        $statement = $model->connection->prepare(
            "SELECT rs.*, cu.name AS card_user_name
             FROM recurrence_splits rs
             INNER JOIN card_users cu ON cu.id = rs.card_user_id
             WHERE rs.recurrence_id = :recurrence_id
             ORDER BY cu.name ASC"
        );
        $statement->execute(["recurrence_id" => $recurrenceId]);

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
}