<?php

use App\Middleware\AuthMiddleware;

$app->get('/account', 'AccountController:dashboard')
    ->add(AuthMiddleware::require());

$app->post('/account/credit', 'AccountController:credit')
    ->add(AuthMiddleware::require());

$app->post('/account/debit', 'AccountController:debit')
    ->add(AuthMiddleware::require());

$app->post('/account/transfer', 'AccountController:transfer')
    ->add(AuthMiddleware::require());