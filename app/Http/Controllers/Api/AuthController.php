<?php

namespace App\Http\Controllers\Api;

use App\Actions\Api\Auth\AuthAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ChangePasswordRequest;
use App\Http\Requests\Api\DeleteAccountRequest;
use App\Http\Requests\Api\ForgotPasswordRequest;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\RegisterRequest;
use App\Http\Requests\Api\ResetPasswordRequest;
use App\Http\Requests\Api\VerifyOtpRequest;
use App\Http\Resources\Api\AuthUserResource;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use ApiResponse;

    public function __construct(protected AuthAction $authAction) {}

    /**
     * Register a new user.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $result = $this->authAction->register($request->validated());

            return $this->success(
                [
                    'user' => new AuthUserResource($result['user']),
                    'token' => $result['token'],
                ],
                'Registration successful',
                201
            );
        } catch (\Exception $e) {
            return $this->error('Registration failed', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * Login user.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $result = $this->authAction->login(
                $request->input('login'),
                $request->input('password')
            );

            return $this->success(
                [
                    'user' => new AuthUserResource($result['user']),
                    'token' => $result['token'],
                ],
                'Login successful'
            );
        } catch (ValidationException $e) {
            return $this->error('Login failed', 401, ['errors' => $e->errors()]);
        } catch (\Exception $e) {
            return $this->error('Login failed', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * Logout user.
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $this->authAction->logout($request->user());

            return $this->success(null, 'Logout successful');
        } catch (\Exception $e) {
            return $this->error('Logout failed', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * Forgot password - send OTP.
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        try {
            $this->authAction->forgotPassword($request->input('identifier'));

            return $this->success(null, 'OTP sent successfully. Please check your email or phone.');
        } catch (ValidationException $e) {
            return $this->error('Failed to send OTP', 422, ['errors' => $e->errors()]);
        } catch (\Exception $e) {
            return $this->error('Failed to send OTP', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * Verify OTP.
     */
    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        try {
            $isValid = $this->authAction->verifyOtp(
                $request->input('identifier'),
                $request->input('otp')
            );

            if (! $isValid) {
                return $this->error('Invalid or expired OTP', 400);
            }

            return $this->success(null, 'OTP verified successfully');
        } catch (\Exception $e) {
            return $this->error('OTP verification failed', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * Reset password.
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        try {
            $this->authAction->resetPassword(
                $request->input('identifier'),
                $request->input('otp'),
                $request->input('password')
            );

            return $this->success(null, 'Password reset successfully');
        } catch (ValidationException $e) {
            return $this->error('Password reset failed', 400, ['errors' => $e->errors()]);
        } catch (\Exception $e) {
            return $this->error('Password reset failed', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get authenticated user.
     */
    public function me(Request $request): JsonResponse
    {
        $data = $this->authAction->getAuthenticatedUser($request->user());

        return $this->success($data, 'Authenticated user retrieved successfully');
    }

    /**
     * Delete the authenticated user's account.
     */
    public function deleteAccount(DeleteAccountRequest $request): JsonResponse
    {
        try {
            $this->authAction->deleteAccount($request->user());
        } catch (\Exception $e) {
            return $this->error('Failed to delete account', 500, ['error' => $e->getMessage()]);
        }

        return $this->success(null, 'Account deleted successfully');
    }

    /**
     * Change password for authenticated user.
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        try {
            $this->authAction->changePassword($request->user(), $request->input('password'));

            return $this->success(null, 'Password changed successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to change password', 500, ['error' => $e->getMessage()]);
        }
    }
}
