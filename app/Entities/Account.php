<?php

namespace App\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'accounts')]
class Account
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private string $email;

    #[ORM\Column(type: 'string', length: 255)]
    private string $password;

    #[ORM\Column(type: 'string', length: 20)]
    private string $role;

    #[ORM\Column(type: 'decimal', precision: 15, scale: 2)]
    private string $balance = '0.00';

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct(
        string $name,
        string $email,
        string $password,
        string $role = 'user'
    ) {
        $this->name = $name;
        $this->email = $email;
        $this->password = password_hash($password, PASSWORD_DEFAULT);
        $this->role = $role;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getBalance(): float
    {
        return (float) $this->balance;
    }

    public function setBalance(float $balance): void
    {
        $this->balance = number_format($balance, 2, '.', '');
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function debit(float $amount): void
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('O valor do débito deve ser maior que zero.');
        }

        if ($this->getBalance() < $amount) {
            throw new \RuntimeException('Saldo insuficiente.');
        }

        $this->setBalance($this->getBalance() - $amount);
    }

    public function credit(float $amount): void
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('O valor do crédito deve ser maior que zero.');
        }

        $this->setBalance($this->getBalance() + $amount);
    }

    public function getPassword(): string
    {
        return $this->password;
    }
}