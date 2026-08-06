<?php

namespace App\Action\Api;

use App\Services\AuthService;

class DeleteAccountAction
{
    public function __construct(protected AuthService $authService) {}

    public function execute($user): void
    {
        $this->authService->deleteAccount($user);
    }
}
