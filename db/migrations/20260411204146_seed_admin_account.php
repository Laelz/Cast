<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class SeedAdminAccount extends AbstractMigration
{
    public function up(): void
    {
        $this->table('accounts')->insert([
            [
                'name' => 'Administrador',
                'email' => 'admin@bank.com',
                'password' => password_hash('123456', PASSWORD_DEFAULT),
                'role' => 'admin',
                'balance' => 0,
                'created_at' => date('Y-m-d H:i:s'),
            ]
        ])->saveData();
    }

    public function down(): void
    {
        $this->execute("
            DELETE FROM accounts
            WHERE email = 'admin@bank.com'
        ");
    }
}