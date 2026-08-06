<?php

namespace App\Action\Api;

use App\Services\AuthService;

class LogoutUserAction
{
    public function __construct(protected AuthService $authService) {}

    public function execute($user): void
    {
        $this->authService->logout($user);
    }
}
