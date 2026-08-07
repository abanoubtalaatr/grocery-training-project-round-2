<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\DeleteAccountRequest;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\VerifyOtpRequest;
use App\Http\Resources\Api\AuthUserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {}

    /**
     * Register a new user
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->register($request->validated());

            return $this->successResponse([
                'user' => new AuthUserResource($result['user']),
                'token' => $result['token'],
            ], 'Registration successful', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Registration failed', 500, null, ['error' => $e->getMessage()]);
        }
    }

    /**
     * Login user
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->login(
                $request->input('login'),
                $request->input('password')
            );

            return $this->successResponse([
                'user' => new AuthUserResource($result['user']),
                'token' => $result['token'],
            ], 'Login successful');
        } catch (ValidationException $e) {
            return $this->errorResponse('Login failed', 401, $e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Login failed', 500, null, ['error' => $e->getMessage()]);
        }
    }

    /**
     * Logout user
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $this->authService->logout($request->user());

            return $this->successResponse(null, 'Logout successful');
        } catch (\Exception $e) {
            return $this->errorResponse('Logout failed', 500, null, ['error' => $e->getMessage()]);
        }
    }

    /**
     * Forgot password - send OTP
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {

        try {
            $this->authService->forgotPassword($request->input('identifier'));

            return $this->successResponse(null, 'OTP sent successfully. Please check your email or phone.');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to send OTP', 500, null, ['error' => $e->getMessage()]);
        }
    }

    /**
     * Verify OTP
     */
    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        try {
            $isValid = $this->authService->verifyOtp(
                $request->input('identifier'),
                $request->input('otp')
            );

            if (! $isValid) {
                return $this->errorResponse('Invalid or expired OTP');
            }

            return $this->successResponse(null, 'OTP verified successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('OTP verification failed', 500, null, ['error' => $e->getMessage()]);
        }
    }

    /**
     * Reset password
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        try {
            $this->authService->resetPassword(
                $request->input('identifier'),
                $request->input('otp'),
                $request->input('password')
            );

            return $this->successResponse(null, 'Password reset successfully');
        } catch (ValidationException $e) {
            return $this->errorResponse('Password reset failed', 400, $e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Password reset failed', 500, null, ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get authenticated user
     */
    public function me(Request $request): JsonResponse
    {
        return $this->successResponse([
            'user' => new AuthUserResource($request->user()),
        ]);
    }

    public function deleteAccount(DeleteAccountRequest $request): JsonResponse
    {
        try {
            $this->authService->deleteAccount($request->user());
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete account', 500, null, ['error' => $e->getMessage()]);
        }

        return $this->successResponse(null, 'Account deleted successfully');
    }

    /**
     * Change password for authenticated user
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        try {
            $user = $request->user();

            // Let the User model's "hashed" cast hash the plain password once (avoid double hashing).
            $user->update([
                'password' => $request->input('password'),
            ]);

            // Revoke all tokens except the current one (optional - for security)
            // $user->tokens()->where('id', '!=', $request->user()->currentAccessToken()->id)->delete();

            return $this->successResponse(null, 'Password changed successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to change password', 500, null, ['error' => $e->getMessage()]);
        }
    }
}
