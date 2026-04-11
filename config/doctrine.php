<?php

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;

require_once __DIR__ . '/../vendor/autoload.php';

$paths = [__DIR__ . '/../app/Entities'];
$isDevMode = true;

$config = Setup::createAttributeMetadataConfiguration($paths, $isDevMode);

$connectionParams = [
    'driver'   => $_ENV['DB_DRIVER'],
    'host'     => $_ENV['DB_HOST'],
    'port'     => $_ENV['DB_PORT'],
    'dbname'   => $_ENV['DB_NAME'],
    'user'     => $_ENV['DB_USER'],
    'password' => $_ENV['DB_PASS'],
];

return EntityManager::create($connectionParams, $config);