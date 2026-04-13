<?php

use PHPUnit\Framework\TestCase;
use Dotenv\Dotenv;
use Doctrine\ORM\EntityManager;
use App\Services\AccountService;
use App\Entities\Account;
use App\Entities\Transaction;
use App\Exceptions\InsufficientBalanceException;
use App\Exceptions\InvalidTransferException;
use App\Exceptions\InvalidTransactionAmountException;

class AccountServiceTest extends TestCase
{
    private EntityManager $entityManager;
    private AccountService $accountService;

    protected function setUp(): void
    {
        $this->entityManager = require __DIR__ . '/../../config/doctrine.php';
        $this->accountService = new AccountService($this->entityManager);

        $conn = $this->entityManager->getConnection();

        $conn->executeStatement('DELETE FROM transactions');
        $conn->executeStatement('DELETE FROM accounts');
        $conn->executeStatement('ALTER SEQUENCE accounts_id_seq RESTART WITH 1');
        $conn->executeStatement('ALTER SEQUENCE transactions_id_seq RESTART WITH 1');

        $this->entityManager->clear();
    }

    public function testCreateAccountSuccessfully(): void
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
        $this->assertTrue(password_verify('123456', $account->getPassword()));
    }

    public function testCreateAccountWithDuplicateEmail(): void
    {
        $this->accountService->createAccount(
            'Primeiro',
            'duplicado@email.com',
            '123456',
            'user'
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Já existe uma conta cadastrada com esse e-mail.');

        $this->accountService->createAccount(
            'Segundo',
            'duplicado@email.com',
            '123456',
            'user'
        );
    }

    public function testCreateAccountWithInvalidEmail(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('E-mail inválido.');

        $this->accountService->createAccount(
            'Teste',
            'email-invalido',
            '123456',
            'user'
        );
    }

    public function testCreateAccountWithShortPassword(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A senha deve ter no mínimo 6 caracteres.');

        $this->accountService->createAccount(
            'Teste',
            'senha@email.com',
            '123',
            'user'
        );
    }

    public function testCreditSuccessfully(): void
    {
        $account = $this->createUser('credit@email.com');

        $this->accountService->credit($account->getId(), 100, 'Crédito de teste');

        $this->entityManager->clear();

        $updated = $this->entityManager->find(Account::class, $account->getId());

        $this->assertSame(100.0, $updated->getBalance());

        $transactions = $this->entityManager
            ->getRepository(Transaction::class)
            ->findBy(['account' => $updated]);

        $this->assertCount(1, $transactions);
        $this->assertSame('credit', $transactions[0]->getType());
        $this->assertSame(100.0, $transactions[0]->getAmount());
    }

    public function testCreditWithInvalidAmount(): void
    {
        $account = $this->createUser('credit-invalid@email.com');

        $this->expectException(InvalidTransactionAmountException::class);

        $this->accountService->credit($account->getId(), 0, 'Valor inválido');
    }

    public function testDebitSuccessfully(): void
    {
        $account = $this->createUser('debit@email.com');

        $this->accountService->credit($account->getId(), 100, 'Crédito inicial');
        $this->accountService->debit($account->getId(), 40, 'Débito de teste');

        $this->entityManager->clear();

        $updated = $this->entityManager->find(Account::class, $account->getId());

        $this->assertSame(60.0, $updated->getBalance());
    }

    public function testDebitWithInsufficientBalance(): void
    {
        $account = $this->createUser('saldo@email.com');

        $this->expectException(InsufficientBalanceException::class);
        $this->expectExceptionMessage('Saldo insuficiente.');

        $this->accountService->debit($account->getId(), 50, 'Tentativa inválida');
    }

    public function testTransferSuccessfully(): void
    {
        $from = $this->createUser('origem@email.com');
        $to = $this->createUser('destino@email.com');

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
        $this->assertSame('credit', $transactions[0]->getType());
        $this->assertSame('transfer_out', $transactions[1]->getType());
        $this->assertSame('transfer_in', $transactions[2]->getType());
    }

    public function testTransferToSameAccount(): void
    {
        $account = $this->createUser('mesmo@email.com');

        $this->expectException(InvalidTransferException::class);
        $this->expectExceptionMessage('Não é possível transferir para a mesma conta.');

        $this->accountService->transfer($account->getId(), $account->getId(), 10, 'Inválida');
    }

    public function testTransferWithInvalidAmount(): void
    {
        $from = $this->createUser('origem2@email.com');
        $to = $this->createUser('destino2@email.com');

        $this->expectException(InvalidTransactionAmountException::class);

        $this->accountService->transfer($from->getId(), $to->getId(), 0, 'Inválida');
    }

    public function testGetStatementReturnsTransactions(): void
    {
        $account = $this->createUser('statement@email.com');

        $this->accountService->credit($account->getId(), 100, 'Crédito 1');
        $this->accountService->debit($account->getId(), 30, 'Débito 1');

        $this->entityManager->clear();

        $statement = $this->accountService->getStatement($account->getId());

        $this->assertCount(2, $statement);
        $this->assertSame('debit', $statement[0]->getType());
        $this->assertSame('credit', $statement[1]->getType());
    }
    private function createUser(string $email): Account
    {
        return $this->accountService->createAccount(
            'Usuário Teste',
            $email,
            '123456',
            'user'
        );
    }
}