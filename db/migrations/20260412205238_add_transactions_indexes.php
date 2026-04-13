<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddTransactionsIndexes extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('transactions');

        $table
            ->addIndex(['account_id'], ['name' => 'idx_transactions_account_id'])
            ->addIndex(['related_account_id'], ['name' => 'idx_transactions_related_account_id'])
            ->addIndex(['created_at'], ['name' => 'idx_transactions_created_at'])
            ->update();
    }

    public function down(): void
    {
        $table = $this->table('transactions');

        $table
            ->removeIndexByName('idx_transactions_account_id')
            ->removeIndexByName('idx_transactions_related_account_id')
            ->removeIndexByName('idx_transactions_created_at')
            ->update();
    }
}