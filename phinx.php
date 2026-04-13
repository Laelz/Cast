<?php

require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

return [
    'paths' => [
        'migrations' => 'db/migrations',
        'seeds' => 'db/seeds',
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_environment' => 'development',

        'development' => [
            'adapter' => 'pgsql',
            'host' => $_ENV['DB_HOST'] ?? 'db',
            'name' => $_ENV['DB_NAME'] ?? 'app_db',
            'user' => $_ENV['DB_USER'] ?? 'app_user',
            'pass' => $_ENV['DB_PASS'] ?? 'app_pass',
            'port' => (int) ($_ENV['DB_PORT'] ?? 5432),
            'charset' => 'utf8',
        ],

        'testing' => [
            'adapter' => 'pgsql',
            'host' => 'db',
            'name' => 'app_db_test',
            'user' => 'app_user',
            'pass' => 'app_pass',
            'port' => 5432,
            'charset' => 'utf8',
        ],
    ],
    'version_order' => 'creation',
];