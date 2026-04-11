<?php

use App\Entities\Account;

$requireAuth = function ($role = null) {
    return function ($request, $response, $next) use ($role) {
        if (!isset($_SESSION['auth'])) {
            $_SESSION['flash'] = [
                'type' => 'danger',
                'message' => 'Você precisa fazer login.'
            ];

            return $response->withRedirect('/login');
        }

        if ($role && ($_SESSION['auth']['role'] ?? null) !== $role) {
            $_SESSION['flash'] = [
                'type' => 'danger',
                'message' => 'Acesso não autorizado.'
            ];

            return $response->withRedirect('/');
        }

        return $next($request, $response);
    };
};

$app->get('/', function ($request, $response) {
    return $this->view->render($response, 'home.twig');
});

$app->get('/login', function ($request, $response) {
    if (isset($_SESSION['auth'])) {
        if ($_SESSION['auth']['role'] === 'admin') {
            return $response->withRedirect('/admin');
        }

        return $response->withRedirect('/account');
    }

    return $this->view->render($response, 'auth/login.twig');
});

$app->post('/login', function ($request, $response) {
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

    if ($account->getRole() === 'admin') {
        return $response->withRedirect('/admin');
    }

    return $response->withRedirect('/account');
});

$app->get('/logout', function ($request, $response) {
    unset($_SESSION['auth']);

    $_SESSION['flash'] = [
        'type' => 'success',
        'message' => 'Logout realizado com sucesso.'
    ];

    return $response->withRedirect('/login');
});

$app->get('/admin', function ($request, $response) {
    $accounts = $this->entityManager->getRepository(Account::class)->findAll();

    $data = [];

    foreach ($accounts as $account) {
        $data[] = [
            'id' => $account->getId(),
            'name' => $account->getName(),
            'email' => $account->getEmail(),
            'role' => $account->getRole(),
            'balance' => $account->getBalance(),
        ];
    }

    return $this->view->render($response, 'admin/dashboard.twig', [
        'accounts' => $data
    ]);
})->add($requireAuth('admin'));

$app->post('/admin/accounts/create', function ($request, $response) {
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
})->add($requireAuth('admin'));

$app->get('/account', function ($request, $response) {
    $accountId = (int) ($_SESSION['auth']['id'] ?? 0);

    $accounts = $this->entityManager->getRepository(Account::class)->findAll();

    $accountsData = [];
    foreach ($accounts as $account) {
        $accountsData[] = [
            'id' => $account->getId(),
            'name' => $account->getName(),
            'email' => $account->getEmail(),
            'role' => $account->getRole(),
            'balance' => $account->getBalance(),
        ];
    }

    $account = $this->entityManager->find(Account::class, $accountId);

    if (!$account) {
        $_SESSION['flash'] = [
            'type' => 'danger',
            'message' => 'Conta não encontrada.'
        ];

        return $response->withRedirect('/login');
    }

    $selectedAccount = [
        'id' => $account->getId(),
        'name' => $account->getName(),
        'email' => $account->getEmail(),
        'role' => $account->getRole(),
        'balance' => $account->getBalance(),
    ];

    $statement = [];
    $transactions = $this->accountService->getStatement($accountId);

    foreach ($transactions as $transaction) {
        $statement[] = [
            'id' => $transaction->getId(),
            'type' => $transaction->getType(),
            'amount' => $transaction->getAmount(),
            'description' => $transaction->getDescription(),
            'created_at' => $transaction->getCreatedAt()->format('d/m/Y H:i:s'),
            'related_account_id' => $transaction->getRelatedAccount() ? $transaction->getRelatedAccount()->getId() : null,
        ];
    }

    return $this->view->render($response, 'account/dashboard.twig', [
        'accounts' => $accountsData,
        'selectedAccount' => $selectedAccount,
        'statement' => $statement,
    ]);
})->add($requireAuth());

$app->post('/account/credit', function ($request, $response) {
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
})->add($requireAuth());

$app->post('/account/debit', function ($request, $response) {
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
})->add($requireAuth());

$app->post('/account/transfer', function ($request, $response) {
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
})->add($requireAuth());