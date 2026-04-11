<?php

namespace App\Controllers;

use App\Services\AccountService;

class AccountController
{
    private AccountService $accountService;

    public function __construct(AccountService $accountService)
    {
        $this->accountService = $accountService;
    }

    public function create($request, $response)
    {
        $data = $request->getParsedBody();

        $this->accountService->createAccount(
            $data['name'],
            $data['email'],
            $data['password'],
            $data['role'] ?? 'user'
        );

        return $response->withJson([
            'message' => 'Conta criada com sucesso'
        ]);
    }

    public function credit($request, $response)
    {
        $data = $request->getParsedBody();

        $this->accountService->credit(
            (int)$data['account_id'],
            (float)$data['amount'],
            $data['description'] ?? null
        );

        return $response->withJson([
            'message' => 'Crédito realizado com sucesso'
        ]);
    }

    public function debit($request, $response)
    {
        $data = $request->getParsedBody();

        $this->accountService->debit(
            (int)$data['account_id'],
            (float)$data['amount'],
            $data['description'] ?? null
        );

        return $response->withJson([
            'message' => 'Débito realizado com sucesso'
        ]);
    }

    public function transfer($request, $response)
    {
        $data = $request->getParsedBody();

        $this->accountService->transfer(
            (int)$data['from_account_id'],
            (int)$data['to_account_id'],
            (float)$data['amount'],
            $data['description'] ?? null
        );

        return $response->withJson([
            'message' => 'Transferência realizada com sucesso'
        ]);
    }
}