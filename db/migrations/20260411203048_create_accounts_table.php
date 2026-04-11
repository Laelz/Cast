<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateAccountsTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('accounts');

        $table
            ->addColumn('name', 'string', ['limit' => 255])
            ->addColumn('email', 'string', ['limit' => 255])
            ->addColumn('password', 'string', ['limit' => 255])
            ->addColumn('role', 'string', ['limit' => 20])
            ->addColumn('balance', 'decimal', [
                'precision' => 15,
                'scale' => 2,
                'default' => 0,
            ])
            ->addColumn('created_at', 'timestamp')
            ->addIndex(['email'], ['unique' => true])
            ->create();
    }
}