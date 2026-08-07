<?php

namespace App\Actions\Auth;

use App\Services\AuthService;

class ForgotPasswordAction
{
    public function __construct(
        protected AuthService $authService
    ) {}

    public function execute(string $identifier): void
    {
        $this->authService->forgotPassword($identifier);
    }
}