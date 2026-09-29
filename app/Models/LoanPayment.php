<?php

namespace App\Models;

use App\Core\AbstractModel;

class LoanPayment extends AbstractModel
{
    protected string $table = "loan_payments";

    protected string $primaryKey = "id";

    protected array $fillable = [
        "loan_id",
        "transaction_id",
        "bank_account_id",
        "amount",
        "payment_date",
        "notes",
    ];

    protected array $required = [
        "loan_id" => "O EMPRÉSTIMO é obrigatório.",
        "bank_account_id" => "A CONTA é obrigatória.",
        "amount" => "O VALOR é obrigatório.",
        "payment_date" => "A DATA é obrigatória.",
    ];

    protected bool $timestamps = false;

    protected bool $softDelete = false;

    private ?int $id = null;
    private ?int $loanId = null;
    private ?int $transactionId = null;
    private ?int $bankAccountId = null;
    private ?float $amount = null;
    private ?string $paymentDate = null;
    private ?string $notes = null;
    private ?string $createdAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setLoanId(int $id): void
    {
        $this->loanId = $id;
        $this->attributes["loan_id"] = $id;
    }

    public function getLoanId(): ?int
    {
        return $this->loanId;
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

    public function setBankAccountId(int $id): void
    {
        $this->bankAccountId = $id;
        $this->attributes["bank_account_id"] = $id;
    }

    public function getBankAccountId(): ?int
    {
        return $this->bankAccountId;
    }

    public function setAmount(float|string $value): void
    {
        $value = (float)str_replace(",", ".", (string)$value);

        if ($value <= 0) {
            throw new \InvalidArgumentException("O valor da devolução deve ser maior que zero.");
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

    public static function findAllForLoan(int $loanId): array
    {
        $model = new static();

        $statement = $model->connection->prepare(
            "SELECT lp.*, ba.name AS bank_account_name
             FROM loan_payments lp
             INNER JOIN bank_accounts ba ON ba.id = lp.bank_account_id
             WHERE lp.loan_id = :loan_id
             ORDER BY lp.payment_date DESC"
        );
        $statement->execute(["loan_id" => $loanId]);

        $results = [];

        foreach ($statement->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $bankAccountName = $row["bank_account_name"];
            unset($row["bank_account_name"]);

            $instance = static::hydrate($row);
            $instance->bankAccountName = $bankAccountName;
            $results[] = $instance;
        }

        return $results;
    }

    private ?string $bankAccountName = null;

    public function getBankAccountName(): ?string
    {
        return $this->bankAccountName;
    }

    public static function findByIdForUser(int $id, int $userId): ?self
    {
        $model = new static();

        $statement = $model->connection->prepare(
            "SELECT lp.* FROM loan_payments lp
             INNER JOIN loans l ON l.id = lp.loan_id
             WHERE lp.id = :id AND l.owner_user_id = :user_id
             LIMIT 1"
        );
        $statement->execute(["id" => $id, "user_id" => $userId]);

        $row = $statement->fetch(\PDO::FETCH_ASSOC);

        return $row ? static::hydrate($row) : null;
    }

    public static function getTotalReturnedForLoan(int $loanId): float
    {
        $model = new static();

        $statement = $model->connection->prepare(
            "SELECT COALESCE(SUM(amount), 0) AS total FROM loan_payments WHERE loan_id = :loan_id"
        );
        $statement->execute(["loan_id" => $loanId]);

        return (float)$statement->fetch()->total;
    }
}