<?php

use Dotenv\Dotenv;

$container = $app->getContainer();

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$container['view'] = function () {
    return new \Slim\Views\Twig(__DIR__ . '/../templates');
};

$container['entityManager'] = function () {
    return require __DIR__ . '/../config/doctrine.php';
};