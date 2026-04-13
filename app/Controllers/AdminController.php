<?php

namespace App\Controllers;

use App\Entities\Account;
use App\Services\AccountService;
use Doctrine\ORM\EntityManager;

class AdminController
{
    private EntityManager $entityManager;
    private AccountService $accountService;
    private \Slim\Views\Twig $view;

    public function __construct(
      EntityManager $entityManager,
      AccountService $accountService,
      \Slim\Views\Twig $view
    ) {
        $this->entityManager = $entityManager;
        $this->accountService = $accountService;
        $this->view = $view;
    }

    public function dashboard($request, $response)
    {
        $accounts = $this->entityManager->getRepository(Account::class)->findAll();

        $data = [];
        $totalAccounts = 0;
        $totalUsers = 0;
        $totalAdmins = 0;
        $totalBalance = 0.0;

        foreach ($accounts as $account) {
            $totalAccounts++;
            $totalBalance += $account->getBalance();

            if ($account->getRole() === 'admin') {
                $totalAdmins++;
            } else {
                $totalUsers++;
            }

            $data[] = [
                'id' => $account->getId(),
                'name' => $account->getName(),
                'email' => $account->getEmail(),
                'role' => $account->getRole(),
                'balance' => $account->getBalance(),
            ];
        }

        return $this->view->render($response, 'admin/dashboard.twig', [
            'accounts' => $data,
            'total_accounts' => $totalAccounts,
            'total_users' => $totalUsers,
            'total_admins' => $totalAdmins,
            'total_balance' => $totalBalance,
        ]);
    }

    public function createAccount($request, $response)
    {
        $data = $request->getParsedBody();

        try {
            $this->accountService->createAccount(
                $data['name'],
                $data['email'],
                $data['password'],
                $data['role'] ?? 'user'
            );

            $_SESSION['flash'] = [
                'type' => 'success',
                'message' => 'Conta criada com sucesso.'
            ];
        } catch (\Throwable $e) {
            $_SESSION['flash'] = [
                'type' => 'danger',
                'message' => $e->getMessage()
            ];
        }

        return $response->withRedirect('/admin');
    }
}