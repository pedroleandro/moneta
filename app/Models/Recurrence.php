<?php

namespace App\Models;

use App\Core\AbstractModel;
use Exception;

class Recurrence extends AbstractModel
{
    protected string $table = "recurrences";

    protected string $primaryKey = "id";

    protected array $fillable = [
        "user_id",
        "category_id",
        "bank_account_id",
        "credit_card_id",
        "type",
        "description",
        "amount",
        "frequency",
        "day_of_month",
        "start_date",
        "end_date",
        "next_occurrence_date",
        "is_active",
    ];

    protected array $required = [
        "category_id" => "A CATEGORIA é obrigatória.",
        "type" => "O TIPO é obrigatório.",
        "description" => "A DESCRIÇÃO é obrigatória.",
        "amount" => "O VALOR é obrigatório.",
        "day_of_month" => "O DIA DO MÊS é obrigatório.",
        "start_date" => "A DATA DE INÍCIO é obrigatória.",
    ];

    protected bool $timestamps = true;

    protected bool $softDelete = true;

    public const TYPE_INCOME = "receita";
    public const TYPE_EXPENSE = "despesa";

    public const FREQUENCY_MONTHLY = "mensal";

    private ?int $id = null;
    private ?int $userId = null;
    private ?int $categoryId = null;
    private ?int $bankAccountId = null;
    private ?int $creditCardId = null;
    private ?string $type = null;
    private ?string $description = null;
    private ?float $amount = null;
    private ?string $frequency = self::FREQUENCY_MONTHLY;
    private ?int $dayOfMonth = null;
    private ?string $startDate = null;
    private ?string $endDate = null;
    private ?string $nextOccurrenceDate = null;
    private ?int $isActive = 1;
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

    public function setCategoryId(int $id): void
    {
        $this->categoryId = $id;
        $this->attributes["category_id"] = $id;
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

    public function setType(string $type): void
    {
        if (!in_array($type, [self::TYPE_INCOME, self::TYPE_EXPENSE], true)) {
            throw new \InvalidArgumentException("Tipo de recorrência inválido.");
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

    /**
     * Só MENSAL é suportado por enquanto. Semanal/anual ficam para depois
     * (day_of_month não cobre esses casos ainda).
     */
    public function setFrequency(string $frequency): void
    {
        if ($frequency !== self::FREQUENCY_MONTHLY) {
            throw new \InvalidArgumentException("Por enquanto só é suportada a frequência MENSAL.");
        }

        $this->frequency = $frequency;
        $this->attributes["frequency"] = $frequency;
    }

    public function getFrequency(): ?string
    {
        return $this->frequency;
    }

    public function setDayOfMonth(int|string $day): void
    {
        $day = (int)$day;

        if ($day < 1 || $day > 31) {
            throw new \InvalidArgumentException("O dia do mês deve estar entre 1 e 31.");
        }

        $this->dayOfMonth = $day;
        $this->attributes["day_of_month"] = $day;
    }

    public function getDayOfMonth(): ?int
    {
        return $this->dayOfMonth;
    }

    public function setStartDate(string $date): void
    {
        $parsed = \DateTime::createFromFormat("Y-m-d", $date);

        if (!$parsed) {
            throw new \InvalidArgumentException("Data de início inválida.");
        }

        $this->startDate = $date;
        $this->attributes["start_date"] = $date;
    }

    public function getStartDate(): ?string
    {
        return $this->startDate;
    }

    public function setEndDate(?string $date): void
    {
        if ($date) {
            $parsed = \DateTime::createFromFormat("Y-m-d", $date);

            if (!$parsed) {
                throw new \InvalidArgumentException("Data de término inválida.");
            }
        }

        $this->endDate = $date;
        $this->attributes["end_date"] = $date;
    }

    public function getEndDate(): ?string
    {
        return $this->endDate;
    }

    public function setNextOccurrenceDate(string $date): void
    {
        $this->nextOccurrenceDate = $date;
        $this->attributes["next_occurrence_date"] = $date;
    }

    public function getNextOccurrenceDate(): ?string
    {
        return $this->nextOccurrenceDate;
    }

    public function setIsActive(bool|int $isActive): void
    {
        $value = $isActive ? 1 : 0;
        $this->isActive = $value;
        $this->attributes["is_active"] = $value;
    }

    public function isActive(): bool
    {
        return (bool)$this->isActive;
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    public function getDeletedAt(): ?string
    {
        return $this->deletedAt;
    }

    public function belongsToUser(int $userId): bool
    {
        return $this->getUserId() === $userId;
    }

    public function isCardRecurrence(): bool
    {
        return $this->creditCardId !== null;
    }

    /**
     * Garante que exatamente um dos dois (conta OU cartão) está preenchido.
     * Chamar antes do save(), já que depende dos dois campos juntos.
     *
     * @throws \InvalidArgumentException
     */
    public function validateAccountOrCard(): void
    {
        $hasAccount = $this->bankAccountId !== null;
        $hasCard = $this->creditCardId !== null;

        if ($hasAccount === $hasCard) {
            throw new \InvalidArgumentException(
                "A recorrência deve pertencer a exatamente uma CONTA BANCÁRIA ou um CARTÃO DE CRÉDITO, nunca os dois nem nenhum."
            );
        }
    }

    /**
     * Calcula a próxima data de ocorrência a partir de uma data de referência,
     * avançando um mês e aplicando "clamping" quando o mês não tem o dia
     * (ex: dia 31 em fevereiro cai no dia 28/29). Mesmo padrão usado em
     * CreditCard::resolveInvoiceForReferenceMonth().
     *
     * @throws Exception
     */
    public function calculateNextOccurrenceDate(string $fromDate): string
    {
        $date = (new \DateTimeImmutable($fromDate))->modify('first day of next month');

        $day = min($this->dayOfMonth, (int)$date->format('t'));

        return $date->setDate(
            (int)$date->format('Y'),
            (int)$date->format('m'),
            $day
        )->format('Y-m-d');
    }

    /**
     * Calcula a primeira data de ocorrência a partir de start_date + day_of_month,
     * usada no cadastro (não pelo cron).
     *
     * @throws Exception
     */
    public function calculateFirstOccurrenceDate(): string
    {
        $start = new \DateTimeImmutable($this->startDate);
        $day = min($this->dayOfMonth, (int)$start->format('t'));

        $candidate = $start->setDate(
            (int)$start->format('Y'),
            (int)$start->format('m'),
            $day
        );

        if ($candidate < $start) {
            $candidate = $candidate->modify('first day of next month');
            $day = min($this->dayOfMonth, (int)$candidate->format('t'));
            $candidate = $candidate->setDate(
                (int)$candidate->format('Y'),
                (int)$candidate->format('m'),
                $day
            );
        }

        return $candidate->format('Y-m-d');
    }

    public static function findAllForUser(int $userId): array
    {
        $model = new static();

        $statement = $model->connection->prepare(
            "SELECT * FROM recurrences
             WHERE user_id = :user_id AND deleted_at IS NULL
             ORDER BY description ASC"
        );
        $statement->execute(["user_id" => $userId]);

        $results = [];

        foreach ($statement->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $results[] = static::hydrate($row);
        }

        return $results;
    }

    public static function findByIdForUser(int $id, int $userId): ?self
    {
        $model = new static();

        $statement = $model->connection->prepare(
            "SELECT * FROM recurrences
             WHERE id = :id AND user_id = :user_id AND deleted_at IS NULL
             LIMIT 1"
        );
        $statement->execute(["id" => $id, "user_id" => $userId]);

        $row = $statement->fetch(\PDO::FETCH_ASSOC);

        return $row ? static::hydrate($row) : null;
    }

    /**
     * Usado pelo cron: todas as recorrências ativas que já venceram.
     */
    public static function findDueForProcessing(): array
    {
        $model = new static();

        $statement = $model->connection->prepare(
            "SELECT * FROM recurrences
             WHERE is_active = 1 AND next_occurrence_date <= CURDATE() AND deleted_at IS NULL"
        );
        $statement->execute();

        $results = [];

        foreach ($statement->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $results[] = static::hydrate($row);
        }

        return $results;
    }
}