<?php

namespace App\Actions\Api\Auth;

use App\Models\User;
use App\Services\AuthService;

class DeleteAccountAction
{
    public function __construct(
        protected AuthService $authService
    ) {}

    public function run(User $user): void
    {
        $this->authService->deleteAccount($user);
    }
}
