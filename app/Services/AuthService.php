<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(
        protected OtpService $otpService,
        protected NotificationService $notificationService
    ) {}

    /**
     * Register a new user
     */
    public function register(array $data): array
    {

        // Create user
        try {
            $user = User::create([
                'username' => $data['username'],
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'password' => Hash::make($data['password']),
            ]);
        } catch (QueryException $e) {
            Log::error('Registration database error', [
                'message' => $e->getMessage(),
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings(),
            ]);

            throw ValidationException::withMessages([
                'username' => ['Registration failed. Please try again.'],
            ]);
        }

        // Generate token
        $token = $user->createToken('auth_token')->plainTextToken;

        // Send welcome notification and email verification OTP
        if ($user->email) {
            $this->notificationService->sendWelcomeEmail($user->email, $user->username);

            $otp = $this->otpService->generate($user->email, 'email_verification');
            $this->notificationService->sendOtpEmail($user->email, $otp, 'email_verification');
        }

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * Login user - validate credentials
     */
    public function login(string $identifier, string $password): array
    {
        $user = User::findByIdentifier($identifier);

        if (! $user) {
            throw ValidationException::withMessages([
                'login' => ['Unable to sign in. Please try again.'],
            ]);
        }

        if (! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => ['The password you entered is incorrect.'],
            ]);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'login' => ['Your account has been deactivated.'],
            ]);
        }

        if ($user->email && ! $user->email_verified) {
            throw ValidationException::withMessages([
                'login' => ['Please verify your email address before logging in.'],
            ]);
        }

        return [
            'user' => $user,
        ];
    }

    /**
     * Verify MFA OTP and issue token
     */
    public function verifyMfaAndLogin(string $identifier, string $otp): array
    {
        if (! $this->otpService->verify($identifier, $otp, 'mfa_login')) {
            throw ValidationException::withMessages([
                'otp' => ['Invalid or expired OTP.'],
            ]);
        }

        $user = User::findByIdentifier($identifier);

        if (! $user) {
            throw ValidationException::withMessages([
                'identifier' => ['User not found.'],
            ]);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'login' => ['Your account has been deactivated.'],
            ]);
        }

        // Generate token
        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * Logout user
     */
    public function logout(User $user): bool
    {
        // Revoke all tokens for the user
        $user->tokens()->delete();

        return true;
    }

    /**
     * Initiate forgot password process
     */
    public function forgotPassword(string $identifier): bool
    {
        // Find user
        $user = User::findByIdentifier($identifier);

        if (! $user) {
            // Always return true to prevent user enumeration
            return true;
        }

        // Generate OTP
        $otp = $this->otpService->generate($identifier, 'password_reset');

        // Send OTP via email or SMS
        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            $this->notificationService->sendOtpEmail($identifier, $otp, 'password_reset');
        } else {
            $this->notificationService->sendOtpSms($identifier, $otp, 'password_reset');
        }

        return true;
    }

    /**
     * Verify OTP
     */
    public function verifyOtp(string $identifier, string $otp): bool
    {
        return $this->otpService->verify($identifier, $otp, 'password_reset');
    }

    /**
     * Reset password
     */
    public function resetPassword(string $identifier, string $otp, string $newPassword): bool
    {
        // Verify OTP BEFORE changing the password
        if (! $this->otpService->isValid($identifier, $otp, 'password_reset')) {
            throw ValidationException::withMessages([
                'otp' => ['The OTP is invalid or has expired.'],
            ]);
        }

        // Find user
        $user = User::findByIdentifier($identifier);

        if (! $user) {
            throw ValidationException::withMessages([
                'identifier' => ['User not found.'],
            ]);
        }

        // Mark OTP as used BEFORE changing password (prevents replay)
        $this->otpService->verify($identifier, $otp, 'password_reset');

        // Update password
        $user->password = Hash::make($newPassword);
        $user->save();

        // Revoke all existing tokens
        $user->tokens()->delete();

        return true;
    }

    /**
     * Delete user account
     */
    public function deleteAccount(User $user): bool
    {
        $user->delete();

        // Revoke all tokens for the user
        $user->tokens()->delete();

        return true;
    }
}
