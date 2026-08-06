<?php

namespace App\Action\Api;

use App\Services\AuthService;

class ResetPasswordAction
{
    public function __construct(protected AuthService $authService) {}

    public function execute(string $identifier, string $otp, string $password): void
    {
        $this->authService->resetPassword($identifier, $otp, $password);
    }
}
