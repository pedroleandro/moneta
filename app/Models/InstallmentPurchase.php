<?php

namespace App\Models;

use App\Core\AbstractModel;

class InstallmentPurchase extends AbstractModel
{
    protected string $table = "installment_purchases";

    protected string $primaryKey = "id";

    protected array $fillable = [
        "user_id",
        "credit_card_id",
        "category_id",
        "description",
        "total_amount",
        "installments_count",
        "first_installment_date",
    ];

    protected array $required = [
        "credit_card_id" => "O CARTÃO é obrigatório.",
        "category_id" => "A CATEGORIA é obrigatória.",
        "description" => "A DESCRIÇÃO é obrigatória.",
        "total_amount" => "O VALOR TOTAL é obrigatório.",
        "installments_count" => "O NÚMERO DE PARCELAS é obrigatório.",
        "first_installment_date" => "A DATA DA PRIMEIRA PARCELA é obrigatória.",
    ];

    protected bool $timestamps = true;

    protected bool $softDelete = true;

    private ?int $id = null;
    private ?int $userId = null;
    private ?int $creditCardId = null;
    private ?int $categoryId = null;
    private ?string $description = null;
    private ?float $totalAmount = null;
    private ?int $installmentsCount = null;
    private ?string $firstInstallmentDate = null;
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

    public function setCreditCardId(int $id): void
    {
        $this->creditCardId = $id;
        $this->attributes["credit_card_id"] = $id;
    }

    public function getCreditCardId(): ?int
    {
        return $this->creditCardId;
    }

    public function setCategoryId(int $id): void
    {
        $this->categoryId = $id;
        $this->attributes["category_id"] = $id;
    }

    public function getCategoryId(): ?int
    {
        return $this->categoryId;
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

    public function setTotalAmount(float|string $value): void
    {
        $value = (float)str_replace(",", ".", (string)$value);

        if ($value <= 0) {
            throw new \InvalidArgumentException("O valor total deve ser maior que zero.");
        }

        $this->totalAmount = $value;
        $this->attributes["total_amount"] = $value;
    }

    public function getTotalAmount(): ?float
    {
        return $this->totalAmount;
    }

    public function setInstallmentsCount(int|string $value): void
    {
        $value = (int)$value;

        if ($value < 2 || $value > 60) {
            throw new \InvalidArgumentException("O número de parcelas deve estar entre 2 e 60.");
        }

        $this->installmentsCount = $value;
        $this->attributes["installments_count"] = $value;
    }

    public function getInstallmentsCount(): ?int
    {
        return $this->installmentsCount;
    }

    public function setFirstInstallmentDate(string $date): void
    {
        $parsed = \DateTime::createFromFormat("Y-m-d", $date);

        if (!$parsed) {
            throw new \InvalidArgumentException("Data da primeira parcela inválida.");
        }

        $this->firstInstallmentDate = $date;
        $this->attributes["first_installment_date"] = $date;
    }

    public function getFirstInstallmentDate(): ?string
    {
        return $this->firstInstallmentDate;
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    public function belongsToUser(int $userId): bool
    {
        return $this->getUserId() === $userId;
    }

    /**
     * Calcula o valor de cada parcela, absorvendo o resto da divisão
     * (centavos) na última parcela, para o total bater certinho.
     */
    public function calculateInstallmentAmounts(): array
    {
        $count = $this->installmentsCount;
        $total = $this->totalAmount;

        $baseAmount = floor(($total / $count) * 100) / 100;
        $amounts = array_fill(0, $count, $baseAmount);

        $sum = $baseAmount * $count;
        $remainder = round($total - $sum, 2);

        $amounts[$count - 1] = round($amounts[$count - 1] + $remainder, 2);

        return $amounts;
    }

    /**
     * Calcula a data de cada parcela, a partir da primeira, avançando
     * um mês por vez (respeitando o tamanho de cada mês).
     */
    public function calculateInstallmentDates(): array
    {
        $dates = [];
        $first = new \DateTimeImmutable($this->firstInstallmentDate);

        for ($i = 0; $i < $this->installmentsCount; $i++) {
            $target = $first->modify("+{$i} month");
            $dates[] = $target->format("Y-m-d");
        }

        return $dates;
    }

    public static function findAllForUser(int $userId): array
    {
        $model = new static();

        $statement = $model->connection->prepare(
            "SELECT ip.*, cc.name AS credit_card_name
             FROM installment_purchases ip
             INNER JOIN credit_cards cc ON cc.id = ip.credit_card_id
             WHERE ip.user_id = :user_id AND ip.deleted_at IS NULL
             ORDER BY ip.first_installment_date DESC"
        );
        $statement->execute(["user_id" => $userId]);

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

    public function hasAnyInstallmentInPaidInvoice(): bool
    {
        $statement = $this->connection->prepare(
            "SELECT COUNT(*) AS total
         FROM transactions t
         INNER JOIN card_invoices ci ON ci.id = t.card_invoice_id
         WHERE t.installment_purchase_id = :id
           AND t.deleted_at IS NULL
           AND ci.status = 'paga'"
        );
        $statement->execute(["id" => $this->getId()]);

        return (int)$statement->fetch()->total > 0;
    }

    public static function findByIdForUser(int $id, int $userId): ?self
    {
        $model = new static();

        $statement = $model->connection->prepare(
            "SELECT * FROM installment_purchases
         WHERE id = :id AND user_id = :user_id AND deleted_at IS NULL
         LIMIT 1"
        );
        $statement->execute(["id" => $id, "user_id" => $userId]);

        $row = $statement->fetch(\PDO::FETCH_ASSOC);

        return $row ? static::hydrate($row) : null;
    }
}