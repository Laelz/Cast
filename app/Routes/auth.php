<?php

use App\Middleware\CsrfMiddleware;

$app->get('/login', 'AuthController:showLogin');
$app->post('/login', 'AuthController:login')
    ->add(CsrfMiddleware::validate('/login'));
$app->get('/logout', 'AuthController:logout');