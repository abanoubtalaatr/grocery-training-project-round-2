<?php

namespace App\Actions\Api\Auth;

use App\Services\AuthService;

class RegisterUserAction
{
    public function __construct(
        protected AuthService $authService
    ) {}

    public function run(array $data): array
    {
        return $this->authService->register($data);
    }
}
