<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateTransactionsTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('transactions');

        $table
            ->addColumn('account_id', 'integer')
            ->addColumn('type', 'string', ['limit' => 30])
            ->addColumn('amount', 'decimal', [
                'precision' => 15,
                'scale' => 2,
            ])
            ->addColumn('related_account_id', 'integer', ['null' => true])
            ->addColumn('description', 'string', [
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('created_at', 'timestamp')
            ->addForeignKey('account_id', 'accounts', 'id', [
                'delete' => 'RESTRICT',
                'update' => 'NO_ACTION',
            ])
            ->addForeignKey('related_account_id', 'accounts', 'id', [
                'delete' => 'RESTRICT',
                'update' => 'NO_ACTION',
            ])
            ->create();
    }
}