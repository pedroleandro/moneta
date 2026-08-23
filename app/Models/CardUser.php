<?php

namespace App\Models;

use App\Core\AbstractModel;

class CardUser extends AbstractModel
{
    protected string $table = "card_users";

    protected string $primaryKey = "id";

    protected array $fillable = [
        "owner_user_id",
        "name",
        "phone",
        "notes",
    ];

    protected array $required = [
        "name" => "O NOME é obrigatório.",
    ];

    protected bool $timestamps = true;

    protected bool $softDelete = true;

    private ?int $id = null;
    private ?int $ownerUserId = null;
    private ?string $name = null;
    private ?string $phone = null;
    private ?string $notes = null;
    private ?string $createdAt = null;
    private ?string $updatedAt = null;
    private ?string $deletedAt = null;

    private array $linkedCards = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setOwnerUserId(int $ownerUserId): void
    {
        $this->ownerUserId = $ownerUserId;
        $this->attributes["owner_user_id"] = $ownerUserId;
    }

    public function getOwnerUserId(): ?int
    {
        return $this->ownerUserId;
    }

    public function setName(string $name): void
    {
        $name = trim(strip_tags($name));

        if (strlen($name) < 2) {
            throw new \InvalidArgumentException("O nome deve ter pelo menos 2 caracteres.");
        }

        $this->name = $name;
        $this->attributes["name"] = $name;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setPhone(?string $phone): void
    {
        if ($phone) {
            $phone = preg_replace('/\D/', '', $phone);
            $phone = $phone === '' ? null : $phone;
        } else {
            $phone = null;
        }

        $this->phone = $phone;
        $this->attributes["phone"] = $phone;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function getFormattedPhone(): ?string
    {
        if (!$this->phone) {
            return null;
        }

        $digits = $this->phone;

        if (strlen($digits) === 11) {
            return sprintf(
                "(%s) %s-%s",
                substr($digits, 0, 2),
                substr($digits, 2, 5),
                substr($digits, 7)
            );
        }

        if (strlen($digits) === 10) {
            return sprintf(
                "(%s) %s-%s",
                substr($digits, 0, 2),
                substr($digits, 2, 4),
                substr($digits, 6)
            );
        }

        return $digits;
    }

    public function getWhatsappLink(): ?string
    {
        if (!$this->phone) {
            return null;
        }

        return "https://wa.me/55" . $this->phone;
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

    public function getUpdatedAt(): ?string
    {
        return $this->updatedAt;
    }

    public function belongsToUser(int $userId): bool
    {
        return $this->getOwnerUserId() === $userId;
    }

    public function getLinkedCards(): array
    {
        return $this->linkedCards;
    }

    public function getLinkedCardNames(): string
    {
        return implode(", ", array_values($this->linkedCards));
    }

    public function getLinkedCardIds(): array
    {
        return array_keys($this->linkedCards);
    }

    public static function findAllForUser(int $userId): array
    {
        $model = new static();

        $statement = $model->connection->prepare(
            "SELECT * FROM card_users
             WHERE owner_user_id = :user_id AND deleted_at IS NULL
             ORDER BY name ASC"
        );
        $statement->execute(["user_id" => $userId]);

        $rows = $statement->fetchAll(\PDO::FETCH_ASSOC);

        if (!$rows) {
            return [];
        }

        $ids = array_column($rows, "id");
        $cardsByPerson = static::loadLinkedCards($ids);

        $results = [];

        foreach ($rows as $row) {
            $instance = static::hydrate($row);
            $instance->linkedCards = $cardsByPerson[$row["id"]] ?? [];
            $results[] = $instance;
        }

        return $results;
    }

    public static function findByIdForUser(int $id, int $userId): ?self
    {
        $model = new static();

        $statement = $model->connection->prepare(
            "SELECT * FROM card_users
             WHERE id = :id AND owner_user_id = :user_id AND deleted_at IS NULL
             LIMIT 1"
        );
        $statement->execute(["id" => $id, "user_id" => $userId]);

        $row = $statement->fetch(\PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        $instance = static::hydrate($row);
        $cardsByPerson = static::loadLinkedCards([$id]);
        $instance->linkedCards = $cardsByPerson[$id] ?? [];

        return $instance;
    }

    private static function loadLinkedCards(array $cardUserIds): array
    {
        if (empty($cardUserIds)) {
            return [];
        }

        $model = new static();
        $placeholders = implode(",", array_fill(0, count($cardUserIds), "?"));

        $statement = $model->connection->prepare(
            "SELECT cucc.card_user_id, cc.id AS credit_card_id, cc.name AS credit_card_name
             FROM card_user_credit_cards cucc
             INNER JOIN credit_cards cc ON cc.id = cucc.credit_card_id
             WHERE cucc.card_user_id IN ({$placeholders})
             ORDER BY cc.name ASC"
        );
        $statement->execute(array_values($cardUserIds));

        $result = [];

        foreach ($statement->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $result[(int)$row["card_user_id"]][(int)$row["credit_card_id"]] = $row["credit_card_name"];
        }

        return $result;
    }

    /**
     * Sincroniza os vínculos dessa pessoa com a lista de cartões
     * informada — remove os que saíram, adiciona os novos.
     */
    public function syncCards(array $creditCardIds): void
    {
        $creditCardIds = array_unique(array_map('intval', $creditCardIds));

        $deleteStatement = $this->connection->prepare(
            "DELETE FROM card_user_credit_cards WHERE card_user_id = :card_user_id"
        );
        $deleteStatement->execute(["card_user_id" => $this->getId()]);

        if (empty($creditCardIds)) {
            return;
        }

        $insertStatement = $this->connection->prepare(
            "INSERT INTO card_user_credit_cards (card_user_id, credit_card_id) VALUES (:card_user_id, :credit_card_id)"
        );

        foreach ($creditCardIds as $creditCardId) {
            $insertStatement->execute([
                "card_user_id" => $this->getId(),
                "credit_card_id" => $creditCardId,
            ]);
        }
    }

    public function totalSpent(): float
    {
        $statement = $this->connection->prepare(
            "SELECT COALESCE(SUM(ts.amount), 0) AS total
         FROM transaction_splits ts
         INNER JOIN transactions t ON t.id = ts.transaction_id
         WHERE ts.card_user_id = :id AND t.deleted_at IS NULL"
        );
        $statement->execute(["id" => $this->getId()]);

        return (float)$statement->fetch()->total;
    }

    public function isInUse(): bool
    {
        $id = $this->getId();

        $queries = [
            "SELECT COUNT(*) AS total FROM transaction_splits WHERE card_user_id = :id",
            "SELECT COUNT(*) AS total FROM installment_purchases WHERE card_user_id = :id",
        ];

        foreach ($queries as $sql) {
            $statement = $this->connection->prepare($sql);
            $statement->execute(["id" => $id]);

            if ((int)$statement->fetch()->total > 0) {
                return true;
            }
        }

        return false;
    }
}