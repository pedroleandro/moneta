<?php

namespace App\Models;

use App\Core\AbstractModel;

class DebtPayment extends AbstractModel
{
    protected string $table = "debt_payments";

    protected string $primaryKey = "id";

    protected array $fillable = [
        "debt_id",
        "transaction_id",
        "bank_account_id",
        "credit_card_id",
        "paying_card_user_id",
        "amount",
        "payment_date",
        "notes",
    ];

    protected array $required = [
        "debt_id" => "A DÍVIDA é obrigatória.",
        "amount" => "O VALOR é obrigatório.",
        "payment_date" => "A DATA é obrigatória.",
    ];

    protected bool $timestamps = false;

    protected bool $softDelete = false;

    private ?int $id = null;
    private ?int $debtId = null;
    private ?int $transactionId = null;
    private ?int $bankAccountId = null;
    private ?int $creditCardId = null;
    private ?int $payingCardUserId = null;
    private ?float $amount = null;
    private ?string $paymentDate = null;
    private ?string $notes = null;
    private ?string $createdAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setDebtId(int $id): void
    {
        $this->debtId = $id;
        $this->attributes["debt_id"] = $id;
    }

    public function getDebtId(): ?int
    {
        return $this->debtId;
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

    public function setBankAccountId(?int $id): void
    {
        $this->bankAccountId = $id;
        $this->attributes["bank_account_id"] = $id;
    }

    public function getBankAccountId(): ?int
    {
        return $this->bankAccountId;
    }

    public function setCreditCardId(?int $id): void
    {
        $this->creditCardId = $id;
        $this->attributes["credit_card_id"] = $id;
    }

    public function getCreditCardId(): ?int
    {
        return $this->creditCardId;
    }

    public function setPayingCardUserId(?int $id): void
    {
        $this->payingCardUserId = $id;
        $this->attributes["paying_card_user_id"] = $id;
    }

    public function getPayingCardUserId(): ?int
    {
        return $this->payingCardUserId;
    }

    public function setAmount(float|string $value): void
    {
        $value = (float)str_replace(",", ".", (string)$value);

        if ($value <= 0) {
            throw new \InvalidArgumentException("O valor do pagamento deve ser maior que zero.");
        }

        $this->amount = $value;
        $this->attributes["amount"] = $value;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setPaymentDate(string $date): void
    {
        $this->paymentDate = $date;
        $this->attributes["payment_date"] = $date;
    }

    public function getPaymentDate(): ?string
    {
        return $this->paymentDate;
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

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    public static function findAllForDebt(int $debtId): array
    {
        $model = new static();

        $statement = $model->connection->prepare(
            "SELECT dp.*, ba.name AS bank_account_name, cc.name AS credit_card_name, cu.name AS paying_person_name
             FROM debt_payments dp
             LEFT JOIN bank_accounts ba ON ba.id = dp.bank_account_id
             LEFT JOIN credit_cards cc ON cc.id = dp.credit_card_id
             LEFT JOIN card_users cu ON cu.id = dp.paying_card_user_id
             WHERE dp.debt_id = :debt_id
             ORDER BY dp.payment_date DESC"
        );
        $statement->execute(["debt_id" => $debtId]);

        $results = [];

        foreach ($statement->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $bankAccountName = $row["bank_account_name"];
            $creditCardName = $row["credit_card_name"];
            $payingPersonName = $row["paying_person_name"];
            unset($row["bank_account_name"], $row["credit_card_name"], $row["paying_person_name"]);

            $instance = static::hydrate($row);
            $instance->bankAccountName = $bankAccountName;
            $instance->creditCardName = $creditCardName;
            $instance->payingPersonName = $payingPersonName;
            $results[] = $instance;
        }

        return $results;
    }

    private ?string $bankAccountName = null;
    private ?string $creditCardName = null;
    private ?string $payingPersonName = null;

    public function getBankAccountName(): ?string
    {
        return $this->bankAccountName;
    }

    public function getCreditCardName(): ?string
    {
        return $this->creditCardName;
    }

    public function getPayingPersonName(): ?string
    {
        return $this->payingPersonName;
    }

    public static function findByIdForUser(int $id, int $userId): ?self
    {
        $model = new static();

        $statement = $model->connection->prepare(
            "SELECT dp.* FROM debt_payments dp
             INNER JOIN debts d ON d.id = dp.debt_id
             WHERE dp.id = :id AND d.owner_user_id = :user_id
             LIMIT 1"
        );
        $statement->execute(["id" => $id, "user_id" => $userId]);

        $row = $statement->fetch(\PDO::FETCH_ASSOC);

        return $row ? static::hydrate($row) : null;
    }

    public static function getTotalPaidForDebt(int $debtId): float
    {
        $model = new static();

        $statement = $model->connection->prepare(
            "SELECT COALESCE(SUM(amount), 0) AS total FROM debt_payments WHERE debt_id = :debt_id"
        );
        $statement->execute(["debt_id" => $debtId]);

        return (float)$statement->fetch()->total;
    }
}