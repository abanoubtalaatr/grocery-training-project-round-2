<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Otp;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MfaTest extends TestCase
{
    use DatabaseTransactions;

    public function test_login_returns_token_directly(): void
    {
        $user = User::create([
            'username' => 'mfa_user_' . uniqid(),
            'email' => 'mfa_user_' . uniqid() . '@example.com',
            'password' => Hash::make('SecurePassword123!'),
            'role' => User::ROLE_CUSTOMER,
            'email_verified' => true,
        ]);

        $response = $this->postJson('/api/auth/login', [
            'login' => $user->email,
            'password' => 'SecurePassword123!',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Login successful')
            ->assertJsonPath('data.user.id', $user->id)
            ->assertJsonPath('data.token', fn ($token) => is_string($token) && !empty($token));

        $this->assertDatabaseMissing('otps', [
            'identifier' => $user->email,
            'type' => 'mfa_login',
        ]);
    }

    public function test_verify_mfa_otp_issues_token(): void
    {
        $user = User::create([
            'username' => 'mfa_user_' . uniqid(),
            'email' => 'mfa_user_' . uniqid() . '@example.com',
            'password' => Hash::make('SecurePassword123!'),
            'role' => User::ROLE_CUSTOMER,
        ]);

        // Manually generate OTP (login no longer does this)
        $otp = app(\App\Services\OtpService::class)->generate($user->email, 'mfa_login');

        // Verify OTP and get token
        $response = $this->postJson('/api/auth/verify-mfa', [
            'identifier' => $user->email,
            'otp' => $otp,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Login successful')
            ->assertJsonPath('data.user.id', $user->id)
            ->assertJsonPath('data.token', fn ($token) => is_string($token) && !empty($token));

        // OTP should be marked as used
        $otpRecord = Otp::where('identifier', $user->email)
            ->where('type', 'mfa_login')
            ->where('is_used', true)
            ->first();
        $this->assertNotNull($otpRecord);
    }

    public function test_verify_mfa_with_invalid_otp_fails(): void
    {
        $user = User::create([
            'username' => 'mfa_user_' . uniqid(),
            'email' => 'mfa_user_' . uniqid() . '@example.com',
            'password' => Hash::make('SecurePassword123!'),
            'role' => User::ROLE_CUSTOMER,
        ]);

        // Manually generate OTP
        app(\App\Services\OtpService::class)->generate($user->email, 'mfa_login');

        // Try with invalid OTP
        $response = $this->postJson('/api/auth/verify-mfa', [
            'identifier' => $user->email,
            'otp' => '000000',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'MFA verification failed');
    }

    public function test_verify_mfa_with_expired_otp_fails(): void
    {
        $user = User::create([
            'username' => 'mfa_user_' . uniqid(),
            'email' => 'mfa_user_' . uniqid() . '@example.com',
            'password' => Hash::make('SecurePassword123!'),
            'role' => User::ROLE_CUSTOMER,
        ]);

        // Create an expired OTP
        $otp = Otp::create([
            'identifier' => $user->email,
            'otp' => '123456',
            'type' => 'mfa_login',
            'is_used' => false,
            'expires_at' => now()->subMinutes(10),
        ]);

        $response = $this->postJson('/api/auth/verify-mfa', [
            'identifier' => $user->email,
            'otp' => $otp->otp,
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'MFA verification failed');
    }

    public function test_login_with_invalid_password_fails(): void
    {
        $user = User::create([
            'username' => 'mfa_user_' . uniqid(),
            'email' => 'mfa_user_' . uniqid() . '@example.com',
            'password' => Hash::make('SecurePassword123!'),
            'role' => User::ROLE_CUSTOMER,
        ]);

        $response = $this->postJson('/api/auth/login', [
            'login' => $user->email,
            'password' => 'WrongPassword123!',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Login failed');
    }

    public function test_mfa_otp_type_is_dedicated(): void
    {
        $this->assertEquals('mfa_login', Otp::TYPE_MFA_LOGIN);
    }
}
