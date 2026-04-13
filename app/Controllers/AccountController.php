<?php

namespace App\Controllers;

use App\Entities\Account;
use App\Services\AccountService;
use Doctrine\ORM\EntityManager;

class AccountController
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
        $accountId = (int) ($_SESSION['auth']['id'] ?? 0);
        $account = $this->entityManager->find(Account::class, $accountId);

        if (!$account) {
            $_SESSION['flash'] = [
                'type' => 'danger',
                'message' => 'Conta não encontrada.'
            ];

            return $response->withRedirect('/login');
        }

        $accounts = $this->entityManager->getRepository(Account::class)->findAll();
        $accountsData = [];

        foreach ($accounts as $item) {
            $accountsData[] = [
                'id' => $item->getId(),
                'name' => $item->getName(),
                'email' => $item->getEmail(),
                'role' => $item->getRole(),
                'balance' => $item->getBalance(),
            ];
        }

        $selectedAccount = [
            'id' => $account->getId(),
            'name' => $account->getName(),
            'email' => $account->getEmail(),
            'role' => $account->getRole(),
            'balance' => $account->getBalance(),
        ];

        $params = $request->getQueryParams();

        $filters = [
            'type' => $params['type'] ?? '',
            'date_from' => $params['date_from'] ?? '',
            'date_to' => $params['date_to'] ?? '',
        ];

        $page = isset($params['page']) ? max(1, (int) $params['page']) : 1;
        $perPage = 10;

        $statementResult = $this->accountService->getStatement($accountId, $filters, $page, $perPage);
        $transactions = $statementResult['items'];

        $statement = [];
        $creditsTotal = 0.0;
        $debitsTotal = 0.0;

        foreach ($transactions as $transaction) {
            $type = $transaction->getType();
            $amount = $transaction->getAmount();

            if (in_array($type, ['credit', 'transfer_in'], true)) {
                $creditsTotal += $amount;
            }

            if (in_array($type, ['debit', 'transfer_out'], true)) {
                $debitsTotal += $amount;
            }

            $statement[] = [
                'id' => $transaction->getId(),
                'type' => $type,
                'amount' => $amount,
                'description' => $transaction->getDescription(),
                'created_at' => $transaction->getCreatedAt()->format('d/m/Y H:i:s'),
                'related_account_id' => $transaction->getRelatedAccount()
                    ? $transaction->getRelatedAccount()->getId()
                    : null,
            ];
        }

        return $this->view->render($response, 'account/dashboard.twig', [
            'accounts' => $accountsData,
            'selectedAccount' => $selectedAccount,
            'statement' => $statement,
            'credits_total' => $creditsTotal,
            'debits_total' => $debitsTotal,
            'filters' => $filters,
            'pagination' => [
                'page' => $statementResult['page'],
                'per_page' => $statementResult['per_page'],
                'total' => $statementResult['total'],
                'total_pages' => $statementResult['total_pages'],
            ],
        ]);
    }

    public function credit($request, $response)
    {
        $data = $request->getParsedBody();
        $accountId = (int) ($_SESSION['auth']['id'] ?? 0);

        try {
            $this->accountService->credit(
                $accountId,
                (float) $data['amount'],
                $data['description'] ?? null
            );

            $_SESSION['flash'] = [
                'type' => 'success',
                'message' => 'Crédito realizado com sucesso.'
            ];
        } catch (\Throwable $e) {
            $_SESSION['flash'] = [
                'type' => 'danger',
                'message' => $e->getMessage()
            ];
        }

        return $response->withRedirect('/account');
    }

    public function debit($request, $response)
    {
        $data = $request->getParsedBody();
        $accountId = (int) ($_SESSION['auth']['id'] ?? 0);

        try {
            $this->accountService->debit(
                $accountId,
                (float) $data['amount'],
                $data['description'] ?? null
            );

            $_SESSION['flash'] = [
                'type' => 'success',
                'message' => 'Débito realizado com sucesso.'
            ];
        } catch (\Throwable $e) {
            $_SESSION['flash'] = [
                'type' => 'danger',
                'message' => $e->getMessage()
            ];
        }

        return $response->withRedirect('/account');
    }

    public function transfer($request, $response)
    {
        $data = $request->getParsedBody();
        $fromAccountId = (int) ($_SESSION['auth']['id'] ?? 0);

        try {
            $this->accountService->transfer(
                $fromAccountId,
                (int) $data['to_account_id'],
                (float) $data['amount'],
                $data['description'] ?? null
            );

            $_SESSION['flash'] = [
                'type' => 'success',
                'message' => 'Transferência realizada com sucesso.'
            ];
        } catch (\Throwable $e) {
            $_SESSION['flash'] = [
                'type' => 'danger',
                'message' => $e->getMessage()
            ];
        }

        return $response->withRedirect('/account');
    }
}