<?php

namespace App\Actions\Api\Auth;

use App\Http\Resources\Api\AuthUserResource;
use App\Models\User;
use App\Services\AuthService;

class AuthAction
{
    public function __construct(protected AuthService $authService) {}

    /**
     * Register a new user.
     */
    public function register(array $data): array
    {
        return $this->authService->register($data);
    }

    /**
     * Login a user.
     */
    public function login(string $identifier, string $password): array
    {
        return $this->authService->login($identifier, $password);
    }

    /**
     * Logout the authenticated user.
     */
    public function logout(User $user): bool
    {
        return $this->authService->logout($user);
    }

    /**
     * Request a password reset OTP.
     */
    public function forgotPassword(string $identifier): bool
    {
        return $this->authService->forgotPassword($identifier);
    }

    /**
     * Verify an OTP.
     */
    public function verifyOtp(string $identifier, string $otp): bool
    {
        return $this->authService->verifyOtp($identifier, $otp);
    }

    /**
     * Reset a password.
     */
    public function resetPassword(string $identifier, string $otp, string $password): bool
    {
        return $this->authService->resetPassword($identifier, $otp, $password);
    }

    /**
     * Get the authenticated user's profile payload.
     */
    public function getAuthenticatedUser(User $user): array
    {
        return [
            'user' => new AuthUserResource($user),
        ];
    }

    /**
     * Delete the authenticated user's account.
     */
    public function deleteAccount(User $user): bool
    {
        return $this->authService->deleteAccount($user);
    }

    /**
     * Change the authenticated user's password.
     */
    public function changePassword(User $user, string $password): bool
    {
        return $this->authService->changePassword($user, $password);
    }
}
