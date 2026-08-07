<?php

namespace App\Http\Controllers\Api;

use App\Actions\Auth\ChangePasswordAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\DeleteAccountRequest;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\VerifyOtpRequest;
use App\Http\Resources\Api\UserResource;
use App\Services\AuthService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected AuthService $authService
    ) {}

    /**
     * Register a new user
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register($request->validated());

        return $this->created([
            'user'  => new UserResource($result['user']),
            'token' => $result['token'],
        ], 'Registration successful');
    }

    /**
     * Login user
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login(
            $request->input('login'),
            $request->input('password')
        );

        return $this->success([
            'user'  => new UserResource($result['user']),
            'token' => $result['token'],
        ], 'Login successful');
    }

    /**
     * Logout user
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return $this->success(null, 'Logout successful');
    }

    /**
     * Forgot password - send OTP
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $this->authService->forgotPassword($request->input('identifier'));

        return $this->success(null, 'OTP sent successfully. Please check your email or phone.');
    }

    /**
     * Verify OTP
     */
    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        $isValid = $this->authService->verifyOtp(
            $request->input('identifier'),
            $request->input('otp')
        );

        if (! $isValid) {
            return $this->error('Invalid or expired OTP', 400);
        }

        return $this->success(null, 'OTP verified successfully');
    }

    /**
     * Reset password
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $this->authService->resetPassword(
            $request->input('identifier'),
            $request->input('otp'),
            $request->input('password')
        );

        return $this->success(null, 'Password reset successfully');
    }

    /**
     * Get authenticated user
     */
    public function me(Request $request): JsonResponse
    {
        return $this->success([
            'user' => new UserResource($request->user()),
        ]);
    }

    /**
     * Delete account
     */
    public function deleteAccount(DeleteAccountRequest $request): JsonResponse
    {
        $this->authService->deleteAccount($request->user());

        return $this->success(null, 'Account deleted successfully');
    }

    /**
     * Change password for authenticated user
     */
    public function changePassword(ChangePasswordRequest $request, ChangePasswordAction $action): JsonResponse
    {
        $action->execute($request->user(), $request->input('password'));

        return $this->success(null, 'Password changed successfully');
    }
}
