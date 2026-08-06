<?php

namespace App\Actions\Api\Auth;

use App\Services\AuthService;

class VerifyResetOtpAction
{
    public function __construct(
        protected AuthService $authService
    ) {}

    public function run(string $identifier, string $otp): bool
    {
        return $this->authService->verifyOtp($identifier, $otp);
    }
}
