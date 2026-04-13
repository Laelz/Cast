<?php

use Dotenv\Dotenv;
use App\Services\AccountService;
use App\Controllers\AccountController;
use App\Controllers\AuthController;
use App\Controllers\AdminController;

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

$container['AuthController'] = function ($container) {
    return new AuthController(
        $container->get('entityManager'),
        $container->get('view')
    );
};

$container['AdminController'] = function ($container) {
    return new AdminController(
        $container->get('entityManager'),
        $container->get('accountService'),
        $container->get('view')
    );
};

$container['AccountController'] = function ($container) {
    return new AccountController(
        $container->get('entityManager'),
        $container->get('accountService'),
        $container->get('view')
    );
};

$container['view'] = function ($container) {
    $view = new \Slim\Views\Twig(__DIR__ . '/../templates');

    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);

    $view->getEnvironment()->addGlobal('flash', $flash);
    $view->getEnvironment()->addGlobal('auth', $_SESSION['auth'] ?? null);
    $view->getEnvironment()->addGlobal('csrf_token', $_SESSION['csrf_token'] ?? '');

    return $view;
};