<?php

use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;

$app->get('/admin', 'AdminController:dashboard')
    ->add(AuthMiddleware::require('admin'));

$app->post('/admin/accounts/create', 'AdminController:createAccount')
    ->add(CsrfMiddleware::validate('/admin'))
    ->add(AuthMiddleware::require('admin'));