<?php

namespace App\Action\Api;

use App\Models\User;
use App\Services\AuthService;

class LogoutAction
{
    public function __construct(protected AuthService $authService) {}

    public function execute(User $user): void
    {
        $this->authService->logout($user);
    }
}