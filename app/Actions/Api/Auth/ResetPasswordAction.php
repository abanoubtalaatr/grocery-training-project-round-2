<?php

namespace App\Actions\Api\Auth;

use App\Services\AuthService;

class ResetPasswordAction
{
    public function __construct(
        protected AuthService $authService
    ) {}

    public function run(string $identifier, string $otp, string $password): void
    {
        $this->authService->resetPassword($identifier, $otp, $password);
    }
}
