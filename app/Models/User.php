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
        "email_verification_token",
        "email_verification_sent_at",
    ];

    protected array $required = [
        "name" => "O campo NOME é obrigatório.",
        "email" => "O campo EMAIL é obrigatório.",
        "password" => "O campo SENHA é obrigatório.",
    ];

    protected bool $timestamps = true;

    protected bool $softDelete = true;

    private ?int $id = null;
    private ?string $name = null;
    private ?string $email = null;
    private ?string $password = null;
    private ?string $avatar = null;
    private ?string $emailVerifiedAt = null;
    private ?string $resetToken = null;
    private ?string $resetExpiresAt = null;
    private ?string $emailVerificationToken = null;
    private ?string $emailVerificationSentAt = null;
    private ?string $createdAt = null;
    private ?string $updatedAt = null;
    private ?string $deletedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setName(string $name): void
    {
        $name = trim(strip_tags($name));

        if (strlen($name) < 3) {
            throw new \InvalidArgumentException("O nome deve ter pelo menos 3 caracteres.");
        }

        $this->name = $name;
        $this->attributes["name"] = $name;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setEmail(string $email): void
    {
        $email = filter_var(trim($email), FILTER_VALIDATE_EMAIL);

        if (!$email) {
            throw new \InvalidArgumentException("O e-mail é inválido.");
        }

        $this->email = $email;
        $this->attributes["email"] = $email;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setPassword(?string $password): void
    {
        if ($password === null || $password === "") {
            $this->password = null;
            $this->attributes["password"] = null;
            return;
        }

        if (strlen($password) < 8 || strlen($password) > 72) {
            throw new \InvalidArgumentException("A senha deve ter entre 8 e 72 caracteres.");
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $this->password = $hash;
        $this->attributes["password"] = $hash;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function passwordVerify(string $password): bool
    {
        return password_verify($password, $this->password ?? "");
    }

    public function hasPassword(): bool
    {
        return !empty($this->password);
    }

    public function setAvatar(?string $avatar): void
    {
        $this->avatar = $avatar;
        $this->attributes["avatar"] = $avatar;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function markEmailAsVerified(): void
    {
        $now = $this->now();
        $this->emailVerifiedAt = $now;
        $this->attributes["email_verified_at"] = $now;
    }

    public function isEmailVerified(): bool
    {
        return !empty($this->emailVerifiedAt);
    }

    /**
     * @throws RandomException
     */
    public function setEmailVerificationToken(): string
    {
        $token = bin2hex(random_bytes(32));
        $hash = hash("sha256", $token);
        $now = $this->now();

        $this->emailVerificationToken = $hash;
        $this->emailVerificationSentAt = $now;

        $this->attributes["email_verification_token"] = $hash;
        $this->attributes["email_verification_sent_at"] = $now;

        return $token;
    }

    public static function findByEmailVerificationToken(string $token): ?self
    {
        $hash = hash("sha256", $token);

        return (new static())->where("email_verification_token", "=", $hash)->first();
    }

    public function clearEmailVerificationToken(): void
    {
        $this->emailVerificationToken = null;
        $this->attributes["email_verification_token"] = null;
    }

    /**
     * @throws RandomException
     */
    public function setResetToken(): string
    {
        $token = bin2hex(random_bytes(32));
        $hash = hash("sha256", $token);

        $this->resetToken = $hash;
        $this->attributes["reset_token"] = $hash;

        $this->setResetExpiresAt();

        return $token;
    }

    public function getResetToken(): ?string
    {
        return $this->resetToken;
    }

    /**
     * @throws \Exception
     */
    public function setResetExpiresAt(): void
    {
        $timezone = new \DateTimeZone(APP_TIMEZONE);
        $expiresAt = new \DateTimeImmutable("now", $timezone);
        $formatted = $expiresAt->modify("+2 hours")->format("Y-m-d H:i:s");

        $this->resetExpiresAt = $formatted;
        $this->attributes["reset_expires_at"] = $formatted;
    }

    public function getResetExpiresAt(): ?string
    {
        return $this->resetExpiresAt;
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
        $this->resetToken = null;
        $this->resetExpiresAt = null;

        $this->attributes["reset_token"] = null;
        $this->attributes["reset_expires_at"] = null;
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?string
    {
        return $this->updatedAt;
    }

    public function getDeletedAt(): ?string
    {
        return $this->deletedAt;
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