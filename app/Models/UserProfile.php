<?php

namespace App\Models;

use App\Core\AbstractModel;

class UserProfile extends AbstractModel
{
    protected string $table = "user_profiles";

    protected string $primaryKey = "id";

    protected array $fillable = [
        "user_id",
        "cpf",
        "phone",
        "birth_date",
        "gender",
        "zip_code",
        "address",
        "address_number",
        "neighborhood",
        "city",
        "state",
        "currency",
        "timezone",
        "theme",
        "notify_invoice_due",
        "notify_budget_exceeded",
        "bio",
    ];

    protected array $required = [];

    protected bool $timestamps = true;

    protected bool $softDelete = true;

    private ?int $id = null;
    private ?int $userId = null;
    private ?string $cpf = null;
    private ?string $phone = null;
    private ?string $birthDate = null;
    private ?string $gender = null;
    private ?string $zipCode = null;
    private ?string $address = null;
    private ?string $addressNumber = null;
    private ?string $neighborhood = null;
    private ?string $city = null;
    private ?string $state = null;
    private ?string $currency = "BRL";
    private ?string $timezone = "America/Sao_Paulo";
    private ?string $theme = "claro";
    private ?int $notifyInvoiceDue = 1;
    private ?int $notifyBudgetExceeded = 1;
    private ?string $bio = null;
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

    public function setCpf(?string $cpf): void
    {
        if ($cpf === null || $cpf === "") {
            $this->cpf = null;
            $this->attributes["cpf"] = null;
            return;
        }

        $digits = preg_replace('/\D/', '', $cpf);

        if (strlen($digits) !== 11) {
            throw new \InvalidArgumentException("O CPF deve conter 11 dígitos.");
        }

        $formatted = substr($digits, 0, 3) . "." . substr($digits, 3, 3) . "." .
            substr($digits, 6, 3) . "-" . substr($digits, 9, 2);

        $this->cpf = $formatted;
        $this->attributes["cpf"] = $formatted;
    }

    public function getCpf(): ?string
    {
        return $this->cpf;
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

    public function setBirthDate(?string $date): void
    {
        if (!$date) {
            $this->birthDate = null;
            $this->attributes["birth_date"] = null;
            return;
        }

        $parsed = \DateTime::createFromFormat("Y-m-d", $date);

        if (!$parsed) {
            throw new \InvalidArgumentException("Data de nascimento inválida.");
        }

        $this->birthDate = $date;
        $this->attributes["birth_date"] = $date;
    }

    public function getBirthDate(): ?string
    {
        return $this->birthDate;
    }

    public function setGender(?string $gender): void
    {
        $valid = ["masculino", "feminino", "outro", "prefiro_nao_informar"];

        if ($gender && !in_array($gender, $valid, true)) {
            throw new \InvalidArgumentException("Valor de gênero inválido.");
        }

        $this->gender = $gender ?: null;
        $this->attributes["gender"] = $this->gender;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setZipCode(?string $zipCode): void
    {
        $zipCode = $zipCode ? trim($zipCode) : null;
        $this->zipCode = $zipCode;
        $this->attributes["zip_code"] = $zipCode;
    }

    public function getZipCode(): ?string
    {
        return $this->zipCode;
    }

    public function setAddress(?string $address): void
    {
        $address = $address ? trim($address) : null;
        $this->address = $address;
        $this->attributes["address"] = $address;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddressNumber(?string $number): void
    {
        $number = $number ? trim($number) : null;
        $this->addressNumber = $number;
        $this->attributes["address_number"] = $number;
    }

    public function getAddressNumber(): ?string
    {
        return $this->addressNumber;
    }

    public function setNeighborhood(?string $neighborhood): void
    {
        $neighborhood = $neighborhood ? trim($neighborhood) : null;
        $this->neighborhood = $neighborhood;
        $this->attributes["neighborhood"] = $neighborhood;
    }

    public function getNeighborhood(): ?string
    {
        return $this->neighborhood;
    }

    public function setCity(?string $city): void
    {
        $city = $city ? trim($city) : null;
        $this->city = $city;
        $this->attributes["city"] = $city;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setState(?string $state): void
    {
        $state = $state ? strtoupper(trim($state)) : null;

        if ($state && strlen($state) !== 2) {
            throw new \InvalidArgumentException("A UF deve ter 2 letras.");
        }

        $this->state = $state;
        $this->attributes["state"] = $state;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setCurrency(string $currency): void
    {
        $this->currency = strtoupper(trim($currency)) ?: "BRL";
        $this->attributes["currency"] = $this->currency;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setTimezone(string $timezone): void
    {
        $this->timezone = trim($timezone) ?: "America/Sao_Paulo";
        $this->attributes["timezone"] = $this->timezone;
    }

    public function getTimezone(): ?string
    {
        return $this->timezone;
    }

    public function setTheme(string $theme): void
    {
        if (!in_array($theme, ["claro", "escuro"], true)) {
            throw new \InvalidArgumentException("Tema inválido.");
        }

        $this->theme = $theme;
        $this->attributes["theme"] = $theme;
    }

    public function getTheme(): ?string
    {
        return $this->theme;
    }

    public function setNotifyInvoiceDue(bool|int $value): void
    {
        $value = $value ? 1 : 0;
        $this->notifyInvoiceDue = $value;
        $this->attributes["notify_invoice_due"] = $value;
    }

    public function notifyInvoiceDue(): bool
    {
        return (bool)$this->notifyInvoiceDue;
    }

    public function setNotifyBudgetExceeded(bool|int $value): void
    {
        $value = $value ? 1 : 0;
        $this->notifyBudgetExceeded = $value;
        $this->attributes["notify_budget_exceeded"] = $value;
    }

    public function notifyBudgetExceeded(): bool
    {
        return (bool)$this->notifyBudgetExceeded;
    }

    public function setBio(?string $bio): void
    {
        $bio = $bio ? trim($bio) : null;
        $this->bio = $bio;
        $this->attributes["bio"] = $bio;
    }

    public function getBio(): ?string
    {
        return $this->bio;
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?string
    {
        return $this->updatedAt;
    }

    public static function findByUserId(int $userId): ?self
    {
        return (new static())->where("user_id", "=", $userId)->first();
    }

    public static function findOrCreateForUser(int $userId): self
    {
        $profile = self::findByUserId($userId);

        if ($profile) {
            return $profile;
        }

        $profile = new self();
        $profile->setUserId($userId);
        $profile->save();

        return $profile;
    }
}