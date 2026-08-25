<?php

namespace App\Models;

use App\Core\AbstractModel;

class Transaction extends AbstractModel
{
    protected string $table = "transactions";

    protected string $primaryKey = "id";

    protected array $fillable = [
        "user_id",
        "category_id",
        "bank_account_id",
        "credit_card_id",
        "card_invoice_id",
        "transfer_id",
        "installment_purchase_id",
        "installment_number",
        "type",
        "description",
        "amount",
        "transaction_date",
        "status",
    ];

    protected array $required = [
        "type" => "O TIPO é obrigatório.",
        "description" => "A DESCRIÇÃO é obrigatória.",
        "amount" => "O VALOR é obrigatório.",
        "transaction_date" => "A DATA é obrigatória.",
    ];

    protected bool $timestamps = true;

    protected bool $softDelete = true;

    public const TYPE_INCOME = "receita";
    public const TYPE_EXPENSE = "despesa";
    public const TYPE_TRANSFER = "transferencia";

    public const STATUS_PENDING = "pendente";
    public const STATUS_CONFIRMED = "confirmado";

    private ?int $id = null;
    private ?int $userId = null;
    private ?int $categoryId = null;
    private ?int $bankAccountId = null;
    private ?int $creditCardId = null;
    private ?int $cardInvoiceId = null;
    private ?int $transferId = null;
    private ?int $installmentPurchaseId = null;
    private ?int $installmentNumber = null;
    private ?string $type = null;
    private ?string $description = null;
    private ?float $amount = null;
    private ?string $transactionDate = null;
    private ?string $status = self::STATUS_PENDING;
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

    public function setCategoryId(?int $categoryId): void
    {
        $this->categoryId = $categoryId;
        $this->attributes["category_id"] = $categoryId;
    }

    public function getCategoryId(): ?int
    {
        return $this->categoryId;
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

    public function setCardInvoiceId(?int $id): void
    {
        $this->cardInvoiceId = $id;
        $this->attributes["card_invoice_id"] = $id;
    }

    public function getCardInvoiceId(): ?int
    {
        return $this->cardInvoiceId;
    }

    public function setTransferId(?int $id): void
    {
        $this->transferId = $id;
        $this->attributes["transfer_id"] = $id;
    }

    public function getTransferId(): ?int
    {
        return $this->transferId;
    }

    public function setInstallmentPurchaseId(?int $id): void
    {
        $this->installmentPurchaseId = $id;
        $this->attributes["installment_purchase_id"] = $id;
    }

    public function getInstallmentPurchaseId(): ?int
    {
        return $this->installmentPurchaseId;
    }

    public function setInstallmentNumber(?int $number): void
    {
        $this->installmentNumber = $number;
        $this->attributes["installment_number"] = $number;
    }

    public function getInstallmentNumber(): ?int
    {
        return $this->installmentNumber;
    }

    public function setType(string $type): void
    {
        if (!in_array($type, [self::TYPE_INCOME, self::TYPE_EXPENSE, self::TYPE_TRANSFER], true)) {
            throw new \InvalidArgumentException("Tipo de lançamento inválido.");
        }

        $this->type = $type;
        $this->attributes["type"] = $type;
    }

    public function getType(): ?string
    {
        return $this->type;
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

    public function getSignedAmount(): float
    {
        return $this->type === self::TYPE_EXPENSE ? -$this->amount : $this->amount;
    }

    public function setTransactionDate(string $date): void
    {
        $parsed = \DateTime::createFromFormat("Y-m-d", $date);

        if (!$parsed) {
            throw new \InvalidArgumentException("Data do lançamento inválida.");
        }

        $this->transactionDate = $date;
        $this->attributes["transaction_date"] = $date;
    }

    public function getTransactionDate(): ?string
    {
        return $this->transactionDate;
    }

    public function setStatus(string $status): void
    {
        if (!in_array($status, [self::STATUS_PENDING, self::STATUS_CONFIRMED], true)) {
            throw new \InvalidArgumentException("Status de lançamento inválido.");
        }

        $this->status = $status;
        $this->attributes["status"] = $status;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function isConfirmed(): bool
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    public function isCardTransaction(): bool
    {
        return $this->creditCardId !== null;
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
     * Só afeta saldo de conta bancária. Cartão nunca mexe em saldo —
     * mexe no total da fatura (ver CardInvoice::recalculateTotal()).
     */
    public function applyBalanceEffect(): void
    {
        if (!$this->isConfirmed() || !$this->bankAccountId) {
            return;
        }

        $account = BankAccount::find($this->bankAccountId);

        if ($account) {
            $account->adjustBalance($this->getSignedAmount());
        }
    }

    public function reverseBalanceEffect(): void
    {
        if (!$this->isConfirmed() || !$this->bankAccountId) {
            return;
        }

        $account = BankAccount::find($this->bankAccountId);

        if ($account) {
            $account->adjustBalance(-$this->getSignedAmount());
        }
    }

    /**
     * Recalcula o total da fatura vinculada, se houver.
     */
    public function refreshInvoiceTotal(): void
    {
        if (!$this->cardInvoiceId) {
            return;
        }

        $invoice = CardInvoice::find($this->cardInvoiceId);

        if ($invoice) {
            $invoice->recalculateTotal();
        }
    }

    public static function findAllForUser(int $userId, ?string $type = null): array
    {
        $model = new static();

        $sql = "SELECT t.*, c.name AS category_name, c.color AS category_color,
                       ba.name AS bank_account_name, cc.name AS credit_card_name
                FROM transactions t
                LEFT JOIN categories c ON c.id = t.category_id
                LEFT JOIN bank_accounts ba ON ba.id = t.bank_account_id
                LEFT JOIN credit_cards cc ON cc.id = t.credit_card_id
                WHERE t.user_id = :user_id AND t.deleted_at IS NULL";

        $params = ["user_id" => $userId];

        if ($type) {
            $sql .= " AND t.type = :type";
            $params["type"] = $type;
        }

        $sql .= " ORDER BY t.transaction_date DESC, t.id DESC";

        $statement = $model->connection->prepare($sql);
        $statement->execute($params);

        $results = [];

        foreach ($statement->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $extra = [
                "category_name" => $row["category_name"],
                "category_color" => $row["category_color"],
                "bank_account_name" => $row["bank_account_name"],
                "credit_card_name" => $row["credit_card_name"],
            ];
            unset(
                $row["category_name"], $row["category_color"],
                $row["bank_account_name"], $row["credit_card_name"]
            );

            $instance = static::hydrate($row);
            $instance->categoryName = $extra["category_name"];
            $instance->categoryColor = $extra["category_color"];
            $instance->bankAccountName = $extra["bank_account_name"];
            $instance->creditCardName = $extra["credit_card_name"];

            $results[] = $instance;
        }

        return $results;
    }

    private ?string $categoryName = null;
    private ?string $categoryColor = null;
    private ?string $bankAccountName = null;
    private ?string $creditCardName = null;

    public function getCategoryName(): ?string
    {
        return $this->categoryName;
    }

    public function getCategoryColor(): ?string
    {
        return $this->categoryColor;
    }

    public function getBankAccountName(): ?string
    {
        return $this->bankAccountName;
    }

    public function getCreditCardName(): ?string
    {
        return $this->creditCardName;
    }

    public static function findByIdForUser(int $id, int $userId): ?self
    {
        $model = new static();

        $statement = $model->connection->prepare(
            "SELECT * FROM transactions
             WHERE id = :id AND user_id = :user_id AND deleted_at IS NULL
             LIMIT 1"
        );
        $statement->execute(["id" => $id, "user_id" => $userId]);

        $row = $statement->fetch(\PDO::FETCH_ASSOC);

        return $row ? static::hydrate($row) : null;
    }

    public static function findAllForInvoice(int $invoiceId): array
    {
        $model = new static();

        $statement = $model->connection->prepare(
            "SELECT t.*, c.name AS category_name
             FROM transactions t
             LEFT JOIN categories c ON c.id = t.category_id
             WHERE t.card_invoice_id = :invoice_id AND t.deleted_at IS NULL
             ORDER BY t.transaction_date ASC"
        );
        $statement->execute(["invoice_id" => $invoiceId]);

        $results = [];

        foreach ($statement->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $categoryName = $row["category_name"];
            unset($row["category_name"]);

            $instance = static::hydrate($row);
            $instance->categoryName = $categoryName;
            $results[] = $instance;
        }

        return $results;
    }

    public static function getMonthlyTotal(int $userId, string $type, string $yearMonth): float
    {
        $model = new static();

        $statement = $model->connection->prepare(
            "SELECT COALESCE(SUM(amount), 0) AS total
             FROM transactions
             WHERE user_id = :user_id AND type = :type AND status = 'confirmado'
               AND DATE_FORMAT(transaction_date, '%Y-%m') = :year_month
               AND deleted_at IS NULL"
        );
        $statement->execute(["user_id" => $userId, "type" => $type, "year_month" => $yearMonth]);

        return (float)$statement->fetch()->total;
    }

    public static function getMonthlyChartData(int $userId, int $monthsBack = 6): array
    {
        $labels = [];
        $income = [];
        $expense = [];

        for ($i = $monthsBack - 1; $i >= 0; $i--) {
            $reference = (new \DateTimeImmutable('first day of this month'))->modify("-{$i} month");
            $yearMonth = $reference->format('Y-m');

            $labels[] = ucfirst($reference->format('M')) . '/' . $reference->format('y');
            $income[] = static::getMonthlyTotal($userId, self::TYPE_INCOME, $yearMonth);
            $expense[] = static::getMonthlyTotal($userId, self::TYPE_EXPENSE, $yearMonth);
        }

        return ["labels" => $labels, "income" => $income, "expense" => $expense];
    }

    public static function getTopCategories(int $userId, string $yearMonth, int $limit = 5): array
    {
        $model = new static();

        $statement = $model->connection->prepare(
            "SELECT c.name AS category_name, c.color AS category_color, c.icon AS category_icon,
                    SUM(t.amount) AS total
             FROM transactions t
             INNER JOIN categories c ON c.id = t.category_id
             WHERE t.user_id = :user_id AND t.type = 'despesa' AND t.status = 'confirmado'
               AND DATE_FORMAT(t.transaction_date, '%Y-%m') = :year_month
               AND t.deleted_at IS NULL
             GROUP BY c.id, c.name, c.color, c.icon
             ORDER BY total DESC
             LIMIT :limit"
        );
        $statement->bindValue(":user_id", $userId, \PDO::PARAM_INT);
        $statement->bindValue(":year_month", $yearMonth, \PDO::PARAM_STR);
        $statement->bindValue(":limit", $limit, \PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function findRecentForUser(int $userId, int $limit = 6): array
    {
        $model = new static();

        $statement = $model->connection->prepare(
            "SELECT t.*, c.name AS category_name, c.icon AS category_icon, c.color AS category_color,
                    ba.name AS bank_account_name, cc.name AS credit_card_name
             FROM transactions t
             LEFT JOIN categories c ON c.id = t.category_id
             LEFT JOIN bank_accounts ba ON ba.id = t.bank_account_id
             LEFT JOIN credit_cards cc ON cc.id = t.credit_card_id
             WHERE t.user_id = :user_id AND t.deleted_at IS NULL
             ORDER BY t.transaction_date DESC, t.id DESC
             LIMIT :limit"
        );
        $statement->bindValue(":user_id", $userId, \PDO::PARAM_INT);
        $statement->bindValue(":limit", $limit, \PDO::PARAM_INT);
        $statement->execute();

        $results = [];

        foreach ($statement->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $extra = [
                "category_name" => $row["category_name"],
                "category_icon" => $row["category_icon"],
                "category_color" => $row["category_color"],
                "bank_account_name" => $row["bank_account_name"],
                "credit_card_name" => $row["credit_card_name"],
            ];
            unset(
                $row["category_name"], $row["category_icon"], $row["category_color"],
                $row["bank_account_name"], $row["credit_card_name"]
            );

            $instance = static::hydrate($row);
            $instance->categoryName = $extra["category_name"];
            $instance->categoryIcon = $extra["category_icon"];
            $instance->categoryColor = $extra["category_color"];
            $instance->bankAccountName = $extra["bank_account_name"];
            $instance->creditCardName = $extra["credit_card_name"];

            $results[] = $instance;
        }

        return $results;
    }

    private ?string $categoryIcon = null;

    public function getCategoryIcon(): ?string
    {
        return $this->categoryIcon;
    }

    /**
     * @throws \Exception
     */
    public static function getFirstMonthForUser(int $userId): ?string
    {
        $model = new static();

        $statement = $model->connection->prepare(
            "SELECT MIN(transaction_date) AS first_date
             FROM transactions
             WHERE user_id = :user_id AND deleted_at IS NULL"
        );
        $statement->execute(["user_id" => $userId]);

        $firstDate = $statement->fetch()->first_date;

        return $firstDate ? (new \DateTimeImmutable($firstDate))->format('Y-m') : null;
    }

    /**
     * @throws \Exception
     */
    public static function getLastMonthForUser(int $userId): ?string
    {
        $model = new static();

        $statement = $model->connection->prepare(
            "SELECT MAX(transaction_date) AS last_date
             FROM transactions
             WHERE user_id = :user_id AND deleted_at IS NULL"
        );
        $statement->execute(["user_id" => $userId]);

        $lastDate = $statement->fetch()->last_date;

        return $lastDate ? (new \DateTimeImmutable($lastDate))->format('Y-m') : null;
    }
}