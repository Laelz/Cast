<?php

use Dotenv\Dotenv;
use App\Services\AccountService;
use App\Controllers\AccountController;

$container = $app->getContainer();

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$container['view'] = function () {
    return new \Slim\Views\Twig(__DIR__ . '/../templates');
};

$container['entityManager'] = function () {
    return require __DIR__ . '/../config/doctrine.php';
};

$container['accountService'] = function ($container) {
    return new AccountService($container->get('entityManager'));
};

$container['AccountController'] = function ($container) {
    return new AccountController(
        $container->get('accountService')
    );
};

$container['view'] = function () {
    $view = new \Slim\Views\Twig(__DIR__ . '/../templates');

    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);

    $view->getEnvironment()->addGlobal('flash', $flash);

    return $view;
};

$container['view'] = function () {
    $view = new \Slim\Views\Twig(__DIR__ . '/../templates');

    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);

    $view->getEnvironment()->addGlobal('flash', $flash);
    $view->getEnvironment()->addGlobal('auth', $_SESSION['auth'] ?? null);

    return $view;
};