<?php

use PHPUnit\Framework\TestCase;
use Dotenv\Dotenv;
use Doctrine\ORM\EntityManager;
use App\Services\AccountService;
use App\Entities\Account;
use App\Entities\Transaction;

class AccountServiceTest extends TestCase
{
    private EntityManager $entityManager;
    private AccountService $accountService;

    protected function setUp(): void
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../..');
        $dotenv->load();

        $this->entityManager = require __DIR__ . '/../../config/doctrine.php';
        $this->accountService = new AccountService($this->entityManager);

        $conn = $this->entityManager->getConnection();

        $conn->executeStatement('DELETE FROM transactions');
        $conn->executeStatement('DELETE FROM accounts');
        $conn->executeStatement('ALTER SEQUENCE accounts_id_seq RESTART WITH 1');
        $conn->executeStatement('ALTER SEQUENCE transactions_id_seq RESTART WITH 1');
    }

    public function testCreateAccount(): void
    {
        $account = $this->accountService->createAccount(
            'Teste',
            'teste@email.com',
            '123456',
            'user'
        );

        $this->assertInstanceOf(Account::class, $account);
        $this->assertSame('Teste', $account->getName());
        $this->assertSame('teste@email.com', $account->getEmail());
        $this->assertSame('user', $account->getRole());
        $this->assertSame(0.0, $account->getBalance());
    }

    public function testCredit(): void
    {
        $account = $this->accountService->createAccount(
            'Teste',
            'credit@email.com',
            '123456',
            'user'
        );

        $this->accountService->credit($account->getId(), 100, 'Crédito de teste');

        $this->entityManager->clear();

        $updated = $this->entityManager->find(Account::class, $account->getId());

        $this->assertSame(100.0, $updated->getBalance());
    }

    public function testDebit(): void
    {
        $account = $this->accountService->createAccount(
            'Teste',
            'debit@email.com',
            '123456',
            'user'
        );

        $this->accountService->credit($account->getId(), 100, 'Crédito inicial');
        $this->accountService->debit($account->getId(), 40, 'Débito de teste');

        $this->entityManager->clear();

        $updated = $this->entityManager->find(Account::class, $account->getId());

        $this->assertSame(60.0, $updated->getBalance());
    }

    public function testDebitWithInsufficientBalance(): void
    {
        $account = $this->accountService->createAccount(
            'Teste',
            'saldo@email.com',
            '123456',
            'user'
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Saldo insuficiente.');

        $this->accountService->debit($account->getId(), 50, 'Tentativa inválida');
    }

    public function testTransfer(): void
    {
        $from = $this->accountService->createAccount(
            'Origem',
            'origem@email.com',
            '123456',
            'user'
        );

        $to = $this->accountService->createAccount(
            'Destino',
            'destino@email.com',
            '123456',
            'user'
        );

        $this->accountService->credit($from->getId(), 200, 'Saldo inicial');
        $this->accountService->transfer($from->getId(), $to->getId(), 50, 'Transferência teste');

        $this->entityManager->clear();

        $updatedFrom = $this->entityManager->find(Account::class, $from->getId());
        $updatedTo = $this->entityManager->find(Account::class, $to->getId());

        $this->assertSame(150.0, $updatedFrom->getBalance());
        $this->assertSame(50.0, $updatedTo->getBalance());

        $transactions = $this->entityManager
            ->getRepository(Transaction::class)
            ->findBy([], ['id' => 'ASC']);

        $this->assertCount(3, $transactions);
    }

    public function testTransferToSameAccount(): void
    {
        $account = $this->accountService->createAccount(
            'Mesmo',
            'mesmo@email.com',
            '123456',
            'user'
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Não é possível transferir para a mesma conta.');

        $this->accountService->transfer($account->getId(), $account->getId(), 10, 'Inválida');
    }
}