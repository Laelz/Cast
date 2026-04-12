<?php

namespace App\Controllers;

use App\Entities\Account;
use Doctrine\ORM\EntityManager;

class AuthController
{
    private EntityManager $entityManager;
    private \Slim\Views\Twig $view;

    public function __construct(
      EntityManager $entityManager,
      \Slim\Views\Twig $view
    ) {
        $this->entityManager = $entityManager;
        $this->view = $view;
    }

    public function showLogin($request, $response)
    {
        if (isset($_SESSION['auth'])) {
            if ($_SESSION['auth']['role'] === 'admin') {
                return $response->withRedirect('/admin');
            }

            return $response->withRedirect('/account');
        }

        return $this->view->render($response, 'auth/login.twig');
    }

    public function login($request, $response)
    {
        $data = $request->getParsedBody();
        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';

        $account = $this->entityManager
            ->getRepository(Account::class)
            ->findOneBy(['email' => $email]);

        if (!$account || !password_verify($password, $account->getPassword())) {
            $_SESSION['flash'] = [
                'type' => 'danger',
                'message' => 'Credenciais inválidas.'
            ];

            return $response->withRedirect('/login');
        }
        
        session_regenerate_id(true);

        $_SESSION['auth'] = [
            'id' => $account->getId(),
            'name' => $account->getName(),
            'email' => $account->getEmail(),
            'role' => $account->getRole(),
        ];

        $_SESSION['flash'] = [
            'type' => 'success',
            'message' => 'Login realizado com sucesso.'
        ];

        return $account->getRole() === 'admin'
            ? $response->withRedirect('/admin')
            : $response->withRedirect('/account');
    }

    public function logout($request, $response)
    {
        $_SESSION = [];
        session_unset();
        session_destroy();

        session_start();

        $_SESSION['flash'] = [
            'type' => 'success',
            'message' => 'Logout realizado com sucesso.'
        ];

        return $response->withRedirect('/login');
    }

    private function render($response, string $template, array $data = [])
    {
        global $app;
        return $app->getContainer()->get('view')->render($response, $template, $data);
    }
}