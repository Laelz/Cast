<?php

$app->get('/login', 'AuthController:showLogin');
$app->post('/login', 'AuthController:login');
$app->get('/logout', 'AuthController:logout');