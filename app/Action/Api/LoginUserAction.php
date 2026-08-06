<?php

namespace App\Action\Api;

use App\Services\AuthService;

class LoginUserAction
{
    public function __construct(protected AuthService $authService) {}

    public function execute(string $login, string $password): array
    {
        return $this->authService->login($login, $password);
    }
}
