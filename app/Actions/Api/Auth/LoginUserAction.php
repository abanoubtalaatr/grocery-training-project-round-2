<?php

namespace App\Actions\Api\Auth;

use App\Services\AuthService;

class LoginUserAction
{
    public function __construct(
        protected AuthService $authService
    ) {}

    public function run(string $login, string $password): array
    {
        return $this->authService->login($login, $password);
    }
}
