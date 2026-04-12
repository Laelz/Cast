<?php

$app->get('/', function ($request, $response) {
    return $this->view->render($response, 'home.twig');
});

require __DIR__ . '/Routes/auth.php';
require __DIR__ . '/Routes/admin.php';
require __DIR__ . '/Routes/account.php';