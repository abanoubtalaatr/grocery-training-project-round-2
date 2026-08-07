<?php

namespace App\Actions\Auth;

use App\Services\AuthService;

class VerifyOtpAction
{
    public function __construct(
        protected AuthService $authService
    ) {}

    public function execute(string $identifier, string $otp): bool
    {
        return $this->authService->verifyOtp($identifier, $otp);
    }
}