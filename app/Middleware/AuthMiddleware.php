<?php

namespace App\Middleware;

class AuthMiddleware
{
    public static function require(?string $role = null): callable
    {
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
    }
}