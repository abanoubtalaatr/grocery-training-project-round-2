<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\ConfirmChangePasswordRequest;
use App\Http\Requests\DeleteAccountRequest;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\VerifyOtpRequest;
use App\Models\Otp;
use App\Services\AuthService;
use App\Services\NotificationService;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService,
        protected OtpService $otpService,
        protected NotificationService $notificationService
    ) {}

    /**
     * Register a new user
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->register($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Registration successful',
                'data' => [
                    'user' => [
                        'id' => $result['user']->id,
                        'username' => $result['user']->username,
                        'email' => $result['user']->email,
                        'phone' => $result['user']->phone,
                        'created_at' => $result['user']->created_at,
                    ],
                    'token' => $result['token'],
                ],
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 400);
        } catch (\Exception $e) {
            Log::error('Registration failed', ['message' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Registration failed. Please try again.',
            ], 500);
        }
    }

    /**
     * Login user - validates credentials and returns token
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->login(
                $request->input('login'),
                $request->input('password')
            );

            $token = $result['user']->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'user' => [
                        'id' => $result['user']->id,
                        'username' => $result['user']->username,
                        'email' => $result['user']->email,
                        'phone' => $result['user']->phone,
                    ],
                    'token' => $token,
                ],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Login failed',
                'errors' => $e->errors(),
            ], 401);
        } catch (\Exception $e) {
            Log::error('Login failed', ['message' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Login failed',
                'error' => 'Internal server error',
            ], 500);
        }
    }

    /**
     * Verify MFA OTP and complete login
     */
    public function verifyMfaAndLogin(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'identifier' => 'required|string',
                'otp' => 'required|string',
            ]);

            $result = $this->authService->verifyMfaAndLogin(
                $request->input('identifier'),
                $request->input('otp')
            );

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'user' => [
                        'id' => $result['user']->id,
                        'username' => $result['user']->username,
                        'email' => $result['user']->email,
                        'phone' => $result['user']->phone,
                    ],
                    'token' => $result['token'],
                ],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'MFA verification failed',
                'errors' => $e->errors(),
            ], 401);
        } catch (\Exception $e) {
            Log::error('MFA verification failed', ['message' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'MFA verification failed',
                'error' => 'Internal server error',
            ], 500);
        }
    }

    /**
     * Logout user
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $this->authService->logout($request->user());

            return response()->json([
                'success' => true,
                'message' => 'Logout successful',
            ]);
        } catch (\Exception $e) {
            Log::error('Logout failed', ['message' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Logout failed',
                'error' => 'Internal server error',
            ], 500);
        }
    }

    /**
     * Forgot password - send OTP
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {

        try {
            $this->authService->forgotPassword($request->input('identifier'));

            return response()->json([
                'success' => true,
                'message' => 'OTP sent successfully. Please check your email or phone.',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send OTP', ['message' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send OTP',
                'error' => 'Internal server error',
            ], 500);
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
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired OTP',
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'OTP verified successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('OTP verification failed', ['message' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'OTP verification failed',
                'error' => 'Internal server error',
            ], 500);
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

            return response()->json([
                'success' => true,
                'message' => 'Password reset successfully',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Password reset failed',
                'errors' => $e->errors(),
            ], 400);
        } catch (\Exception $e) {
            Log::error('Password reset failed', ['message' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Password reset failed',
                'error' => 'Internal server error',
            ], 500);
        }
    }

    /**
     * Get authenticated user
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $request->user()->id,
                    'username' => $request->user()->username,
                    'email' => $request->user()->email,
                    'phone' => $request->user()->phone,
                    'email_verified' => $request->user()->email_verified,
                    'phone_verified' => $request->user()->phone_verified,
                    'created_at' => $request->user()->created_at,
                ],
            ],
        ]);
    }

    public function deleteAccount(DeleteAccountRequest $request): JsonResponse
    {
        try {
            $this->authService->deleteAccount($request->user());
        } catch (\Exception $e) {
            Log::error('Failed to delete account', ['message' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete account',
                'error' => 'Internal server error',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Account deleted successfully',
        ]);
    }

    /**
     * Change password for authenticated user — step 1: validate and send OTP
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        try {
            $user = $request->user();
            $identifier = $user->email;

            $otp = $this->otpService->generate($identifier, Otp::TYPE_PASSWORD_CHANGE);
            $this->notificationService->sendOtpEmail($identifier, $otp, Otp::TYPE_PASSWORD_CHANGE);

            return response()->json([
                'success' => true,
                'message' => 'OTP sent to your email. Please verify to complete password change.',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to initiate password change', ['message' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to initiate password change',
                'error' => 'Internal server error',
            ], 500);
        }
    }

    /**
     * Change password for authenticated user — step 2: verify OTP and apply
     */
    public function confirmChangePassword(ConfirmChangePasswordRequest $request): JsonResponse
    {
        try {
            $user = $request->user();
            $identifier = $user->email;
            $otp = $request->input('otp');

            if (! $this->otpService->verify($identifier, $otp, Otp::TYPE_PASSWORD_CHANGE)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired OTP',
                ], 400);
            }

            $user->update([
                'password' => $request->input('password'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Password changed successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to change password', ['message' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to change password',
                'error' => 'Internal server error',
            ], 500);
        }
    }
}
