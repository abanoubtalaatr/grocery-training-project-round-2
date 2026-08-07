<?php

namespace App\Actions\Auth;

use App\Models\User;
use App\Services\AuthService;

class DeleteAccountAction
{
    public function __construct(
        protected AuthService $authService
    ) {}

    public function execute(User $user): void
    {
        $this->authService->deleteAccount($user);
    }
}