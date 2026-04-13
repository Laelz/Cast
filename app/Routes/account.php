<?php

use App\Middleware\CsrfMiddleware;
use App\Middleware\AuthMiddleware;

$app->get('/account', 'AccountController:dashboard')
    ->add(AuthMiddleware::require());

$app->post('/account/credit', 'AccountController:credit')
    ->add(CsrfMiddleware::validate('/account'))
    ->add(AuthMiddleware::require());

$app->post('/account/debit', 'AccountController:debit')
    ->add(CsrfMiddleware::validate('/account'))
    ->add(AuthMiddleware::require());

$app->post('/account/transfer', 'AccountController:transfer')
    ->add(CsrfMiddleware::validate('/account'))
    ->add(AuthMiddleware::require());