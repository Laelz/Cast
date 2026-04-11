<?php

$app->get('/', function ($request, $response) {
    $em = $this->entityManager;
    $conn = $em->getConnection();
    $result = $conn->executeQuery('SELECT NOW()')->fetchOne();

    return $this->view->render($response, 'home.twig', [
        'title' => 'Banco OK',
        'message' => 'Doctrine conectado com sucesso',
        'hora' => $result
    ]);
});