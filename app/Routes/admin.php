<?php

use App\Middleware\AuthMiddleware;

$app->get('/admin', 'AdminController:dashboard')
    ->add(AuthMiddleware::require('admin'));

$app->post('/admin/accounts/create', 'AdminController:createAccount')
    ->add(AuthMiddleware::require('admin'));