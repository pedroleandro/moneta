<?php

namespace App\Models;

use App\Core\AbstractModel;
use Random\RandomException;

class User extends AbstractModel
{
    protected string $table = "users";

    protected string $primaryKey = "id";

    protected array $fillable = [
        "name",
        "email",
        "password",
        "avatar",
        "email_verified_at",
        "reset_token",
        "reset_expires_at",
    ];

    protected array $required = [
        "name" => "O campo NOME é obrigatório.",
        "email" => "O campo EMAIL é obrigatório.",
        "password" => "O campo SENHA é obrigatório.",
    ];

    protected bool $timestamps = true;

    protected bool $softDelete = true;

    public function getId(): ?int
    {
        return isset($this->attributes["id"]) ? (int)$this->attributes["id"] : null;
    }

    public function setName(string $name): void
    {
        $name = trim(strip_tags($name));

        if (strlen($name) < 3) {
            throw new \InvalidArgumentException("O nome deve ter pelo menos 3 caracteres.");
        }

        $this->attributes["name"] = $name;
    }

    public function getName(): ?string
    {
        return $this->attributes["name"] ?? null;
    }

    public function setEmail(string $email): void
    {
        $email = filter_var(trim($email), FILTER_VALIDATE_EMAIL);

        if (!$email) {
            throw new \InvalidArgumentException("O e-mail é inválido.");
        }

        $this->attributes["email"] = $email;
    }

    public function getEmail(): ?string
    {
        return $this->attributes["email"] ?? null;
    }

    public function setPassword(?string $password): void
    {
        if ($password === null || $password === "") {
            throw new \InvalidArgumentException("A senha não pode ser vazia.");
        }

        if (strlen($password) < 8 || strlen($password) > 16) {
            throw new \InvalidArgumentException("A senha deve ter entre 8 e 16 caracteres.");
        }

        $this->attributes["password"] = password_hash($password, PASSWORD_DEFAULT);
    }

    public function getPassword(): ?string
    {
        return $this->attributes["password"] ?? null;
    }

    public function passwordVerify(string $password): bool
    {
        return password_verify($password, $this->attributes["password"] ?? "");
    }

    public function setAvatar(?string $avatar): void
    {
        $this->attributes["avatar"] = $avatar;
    }

    public function getAvatar(): ?string
    {
        return $this->attributes["avatar"] ?? null;
    }

    public function markEmailAsVerified(): void
    {
        $this->attributes["email_verified_at"] = $this->now();
    }

    public function isEmailVerified(): bool
    {
        return !empty($this->attributes["email_verified_at"]);
    }

    /**
     * @throws RandomException
     */
    public function setResetToken(): string
    {
        $token = bin2hex(random_bytes(32));

        $this->attributes["reset_token"] = hash("sha256", $token);
        $this->setResetExpiresAt();

        return $token;
    }

    public function getResetToken(): ?string
    {
        return $this->attributes["reset_token"] ?? null;
    }

    /**
     * @throws \Exception
     */
    public function setResetExpiresAt(): void
    {
        $timezone = new \DateTimeZone(APP_TIMEZONE);
        $expiresAt = new \DateTimeImmutable("now", $timezone);

        $this->attributes["reset_expires_at"] = $expiresAt->modify("+2 hours")->format("Y-m-d H:i:s");
    }

    public function getResetExpiresAt(): ?string
    {
        return $this->attributes["reset_expires_at"] ?? null;
    }

    /**
     * @throws \Exception
     */
    public function resetTokenIsExpired(): bool
    {
        $expiresAt = $this->getResetExpiresAt();

        if (!$expiresAt) {
            return true;
        }

        $timezone = new \DateTimeZone(APP_TIMEZONE);
        $now = new \DateTimeImmutable("now", $timezone);
        $expires = new \DateTimeImmutable($expiresAt, $timezone);

        return $now > $expires;
    }

    public function clearResetToken(): void
    {
        $this->attributes["reset_token"] = null;
        $this->attributes["reset_expires_at"] = null;
    }

    public static function findByEmail(?string $email): ?self
    {
        if (!$email) {
            return null;
        }

        return (new static())->where("email", "=", $email)->first();
    }

    public static function findByResetToken(string $token): ?self
    {
        $hash = hash("sha256", $token);

        return (new static())->where("reset_token", "=", $hash)->first();
    }

    public function existsByEmail(string $email, ?int $ignoreId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE email = :email AND deleted_at IS NULL";
        $params = ["email" => $email];

        if ($ignoreId) {
            $sql .= " AND id != :ignore_id";
            $params["ignore_id"] = $ignoreId;
        }

        $statement = $this->connection->prepare($sql);
        $statement->execute($params);

        return (int)$statement->fetchColumn() > 0;
    }

    public function toSessionData(): array
    {
        return [
            "id" => $this->getId(),
            "name" => $this->getName(),
            "email" => $this->getEmail(),
            "avatar" => $this->getAvatar(),
        ];
    }
}