<?php

namespace App\Middleware;

class CsrfMiddleware
{
    public static function validate(string $redirect = '/'): callable
    {
        return function ($request, $response, $next) use ($redirect) {
            $data = $request->getParsedBody();

            if (
                empty($_SESSION['csrf_token']) ||
                empty($data['csrf_token']) ||
                !hash_equals($_SESSION['csrf_token'], $data['csrf_token'])
            ) {
                $_SESSION['flash'] = [
                    'type' => 'danger',
                    'message' => 'Token de segurança inválido. Tente novamente.'
                ];

                return $response->withRedirect($redirect);
            }

            return $next($request, $response);
        };
    }
}