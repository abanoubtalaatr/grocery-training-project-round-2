<?php

namespace App\Action\Api;

use App\Services\AuthService;

class RegisterUserAction
{
    public function __construct(protected AuthService $authService) {}

    public function execute(array $data): array
    {
        return $this->authService->register($data);
    }
}
