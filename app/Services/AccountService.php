<?php

namespace App\Services;

use App\Entities\Account;
use App\Entities\Transaction;
use Doctrine\ORM\EntityManager;

class AccountService
{
    private EntityManager $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function createAccount(
        string $name,
        string $email,
        string $password,
        string $role = 'user'
    ): Account {
        if (trim($name) === '') {
            throw new \InvalidArgumentException('O nome é obrigatório.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('E-mail inválido.');
        }

        if (strlen($password) < 6) {
            throw new \InvalidArgumentException('A senha deve ter no mínimo 6 caracteres.');
        }

        $existing = $this->entityManager
            ->getRepository(Account::class)
            ->findOneBy(['email' => $email]);

        if ($existing) {
            throw new \InvalidArgumentException('Já existe uma conta cadastrada com esse e-mail.');
        }

        $account = new Account($name, $email, $password, $role);

        $this->entityManager->persist($account);
        $this->entityManager->flush();

        return $account;
    }
    public function credit(int $accountId, float $amount, ?string $description = null): void
    {
        $this->entityManager->wrapInTransaction(function () use ($accountId, $amount, $description) {
            $account = $this->entityManager->find(Account::class, $accountId);

            if (!$account) {
                throw new \RuntimeException('Conta não encontrada.');
            }

            $this->entityManager->lock($account, \Doctrine\DBAL\LockMode::PESSIMISTIC_WRITE);

            $account->credit($amount);

            $transaction = new Transaction(
                $account,
                'credit',
                $amount,
                null,
                $description
            );

            $this->entityManager->persist($transaction);
        });

        $this->entityManager->flush();
    }

    public function debit(int $accountId, float $amount, ?string $description = null): void
    {
        $this->entityManager->wrapInTransaction(function () use ($accountId, $amount, $description) {
            $account = $this->entityManager->find(Account::class, $accountId);

            if (!$account) {
                throw new \RuntimeException('Conta não encontrada.');
            }

            $this->entityManager->lock($account, \Doctrine\DBAL\LockMode::PESSIMISTIC_WRITE);

            $account->debit($amount);

            $transaction = new Transaction(
                $account,
                'debit',
                $amount,
                null,
                $description
            );

            $this->entityManager->persist($transaction);
        });

        $this->entityManager->flush();
    }

    public function transfer(
        int $fromAccountId,
        int $toAccountId,
        float $amount,
        ?string $description = null
    ): void {
        if ($fromAccountId === $toAccountId) {
            throw new \InvalidArgumentException('Não é possível transferir para a mesma conta.');
        }

        $this->entityManager->wrapInTransaction(function () use (
            $fromAccountId,
            $toAccountId,
            $amount,
            $description
        ) {
            $firstId = min($fromAccountId, $toAccountId);
            $secondId = max($fromAccountId, $toAccountId);

            $firstAccount = $this->entityManager->find(Account::class, $firstId);
            $secondAccount = $this->entityManager->find(Account::class, $secondId);

            if (!$firstAccount || !$secondAccount) {
                throw new \RuntimeException('Conta de origem ou destino não encontrada.');
            }

            $this->entityManager->lock($firstAccount, \Doctrine\DBAL\LockMode::PESSIMISTIC_WRITE);
            $this->entityManager->lock($secondAccount, \Doctrine\DBAL\LockMode::PESSIMISTIC_WRITE);

            $fromAccount = $fromAccountId === $firstId ? $firstAccount : $secondAccount;
            $toAccount = $toAccountId === $firstId ? $firstAccount : $secondAccount;

            $fromAccount->debit($amount);
            $toAccount->credit($amount);

            $transferOut = new Transaction(
                $fromAccount,
                'transfer_out',
                $amount,
                $toAccount,
                $description
            );

            $transferIn = new Transaction(
                $toAccount,
                'transfer_in',
                $amount,
                $fromAccount,
                $description
            );

            $this->entityManager->persist($transferOut);
            $this->entityManager->persist($transferIn);
        });

        $this->entityManager->flush();
    }

    public function getStatement(int $accountId): array
    {
        $account = $this->entityManager->find(Account::class, $accountId);

        if (!$account) {
            throw new \RuntimeException('Conta não encontrada.');
        }

        return $this->entityManager
            ->getRepository(Transaction::class)
            ->findBy(
                ['account' => $account],
                ['createdAt' => 'DESC']
            );
    }
}