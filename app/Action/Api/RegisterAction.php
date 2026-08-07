<?php

namespace App\Action\Api;

use App\Services\AuthService;

class RegisterAction
{
    public function __construct(protected AuthService $authService) {}

    public function execute(array $data): array
    {
        return $this->authService->register($data);
    }
}