<?php

namespace App\Models;

use App\Core\AbstractModel;

class CardInvoicePayment extends AbstractModel
{
    protected string $table = "card_invoice_payments";

    protected string $primaryKey = "id";

    protected array $fillable = [
        "card_invoice_id",
        "bank_account_id",
        "amount",
        "payment_date",
        "notes",
    ];

    protected array $required = [
        "card_invoice_id" => "A FATURA é obrigatória.",
        "amount" => "O VALOR é obrigatório.",
        "payment_date" => "A DATA é obrigatória.",
    ];

    protected bool $timestamps = false;

    protected bool $softDelete = false;

    private ?int $id = null;
    private ?int $cardInvoiceId = null;
    private ?int $bankAccountId = null;
    private ?float $amount = null;
    private ?string $paymentDate = null;
    private ?string $notes = null;
    private ?string $createdAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setCardInvoiceId(int $id): void
    {
        $this->cardInvoiceId = $id;
        $this->attributes["card_invoice_id"] = $id;
    }

    public function getCardInvoiceId(): ?int
    {
        return $this->cardInvoiceId;
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

    public static function findAllForInvoice(int $invoiceId): array
    {
        $model = new static();

        $statement = $model->connection->prepare(
            "SELECT cip.*, ba.name AS bank_account_name
             FROM card_invoice_payments cip
             LEFT JOIN bank_accounts ba ON ba.id = cip.bank_account_id
             WHERE cip.card_invoice_id = :invoice_id
             ORDER BY cip.payment_date DESC"
        );
        $statement->execute(["invoice_id" => $invoiceId]);

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
}