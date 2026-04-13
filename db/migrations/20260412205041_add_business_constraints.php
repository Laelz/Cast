<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddBusinessConstraints extends AbstractMigration
{
    public function up(): void
    {
        $this->execute("
            ALTER TABLE accounts
            ADD CONSTRAINT chk_accounts_role
            CHECK (role IN ('admin', 'user'));
        ");

        $this->execute("
            ALTER TABLE accounts
            ADD CONSTRAINT chk_accounts_balance
            CHECK (balance >= 0);
        ");

        $this->execute("
            ALTER TABLE transactions
            ADD CONSTRAINT chk_transactions_amount
            CHECK (amount > 0);
        ");
    }

    public function down(): void
    {
        $this->execute("
            ALTER TABLE transactions
            DROP CONSTRAINT IF EXISTS chk_transactions_amount;
        ");

        $this->execute("
            ALTER TABLE accounts
            DROP CONSTRAINT IF EXISTS chk_accounts_balance;
        ");

        $this->execute("
            ALTER TABLE accounts
            DROP CONSTRAINT IF EXISTS chk_accounts_role;
        ");
    }
}