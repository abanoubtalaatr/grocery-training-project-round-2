<?php

namespace Tests\Feature;

use App\Models\Otp;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthSecurityTest extends TestCase
{
    use DatabaseTransactions;

    // ─── C1: Password Reset OTP Verification ───────────────────────────

    public function test_reset_password_requires_valid_otp(): void
    {
        $email = 'c1_reset_' . uniqid() . '@example.com';
        User::create([
            'username' => 'c1_user',
            'email' => $email,
            'password' => Hash::make('OldPassword123!'),
            'is_active' => true,
        ]);

        $response = $this->withSession(['password_reset.identifier' => $email])
            ->post(route('password.reset'), [
                'identifier' => $email,
                'otp' => '000000',
                'password' => 'NewPassword123!',
                'password_confirmation' => 'NewPassword123!',
            ]);

        $response->assertSessionHasErrors('otp');

        $user = User::where('email', $email)->first();
        $this->assertTrue(Hash::check('OldPassword123!', $user->password));
    }

    public function test_reset_password_requires_correct_otp(): void
    {
        $email = 'c1_correct_' . uniqid() . '@example.com';
        User::create([
            'username' => 'c1_user2',
            'email' => $email,
            'password' => Hash::make('OldPassword123!'),
            'is_active' => true,
        ]);

        // Generate OTP for a different identifier
        $otherEmail = 'other_' . uniqid() . '@example.com';
        $otp = app(\App\Services\OtpService::class)->generate($otherEmail, 'password_reset');

        // Try to use it for our email
        $response = $this->withSession(['password_reset.identifier' => $email])
            ->post(route('password.reset'), [
                'identifier' => $email,
                'otp' => $otp,
                'password' => 'NewPassword123!',
                'password_confirmation' => 'NewPassword123!',
            ]);

        $response->assertSessionHasErrors('otp');

        $user = User::where('email', $email)->first();
        $this->assertTrue(Hash::check('OldPassword123!', $user->password));
    }

    public function test_used_otp_cannot_reset_password(): void
    {
        $email = 'c1_used_' . uniqid() . '@example.com';
        User::create([
            'username' => 'c1_user3',
            'email' => $email,
            'password' => Hash::make('OldPassword123!'),
            'is_active' => true,
        ]);

        $otpService = app(\App\Services\OtpService::class);
        $otp = $otpService->generate($email, 'password_reset');

        // Use the OTP once
        $otpService->verify($email, $otp, 'password_reset');

        // Try to use it again
        $response = $this->withSession(['password_reset.identifier' => $email])
            ->post(route('password.reset'), [
                'identifier' => $email,
                'otp' => $otp,
                'password' => 'NewPassword123!',
                'password_confirmation' => 'NewPassword123!',
            ]);

        $response->assertSessionHasErrors('otp');

        $user = User::where('email', $email)->first();
        $this->assertTrue(Hash::check('OldPassword123!', $user->password));
    }

    // ─── H7: Login User Enumeration ────────────────────────────────────

    public function test_api_login_does_not_enumerate_email(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'login' => 'nonexistent_' . uniqid() . '@example.com',
            'password' => 'SomePassword123!',
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'success' => false,
        ]);
        // Should NOT contain "not registered" or "not found"
        $response->assertDontSee('not registered');
        $response->assertDontSee('not found');
    }

    public function test_api_login_does_not_enumerate_deleted_account(): void
    {
        $email = 'deleted_' . uniqid() . '@example.com';
        $user = User::create([
            'username' => 'del_user',
            'email' => $email,
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $user->delete();

        $response = $this->postJson('/api/auth/login', [
            'login' => $email,
            'password' => 'Password123!',
        ]);

        $response->assertStatus(401);
        $response->assertDontSee('deleted');
    }

    // ─── H8: Forgot Password User Enumeration ──────────────────────────

    public function test_forgot_password_does_not_enumerate_user(): void
    {
        $response = $this->postJson('/api/auth/forgot-password', [
            'identifier' => 'nonexistent_' . uniqid() . '@example.com',
        ]);

        // Should return success (not 404 or error) to prevent enumeration
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
    }

    public function test_web_forgot_password_does_not_enumerate_user(): void
    {
        $response = $this->post(route('password.forgot'), [
            'identifier' => 'nonexistent_' . uniqid() . '@example.com',
        ]);

        // Should redirect to reset form (not back with error)
        $response->assertRedirect(route('password.reset.form'));
    }

    // ─── H2: OTP Hashed Storage ────────────────────────────────────────

    public function test_otp_is_stored_hashed(): void
    {
        $email = 'hash_' . uniqid() . '@example.com';
        $otpService = app(\App\Services\OtpService::class);

        $otpCode = $otpService->generate($email, 'password_reset');

        $otpRecord = Otp::where('identifier', $email)
            ->where('type', 'password_reset')
            ->first();

        $this->assertNotNull($otpRecord);
        // The stored OTP should NOT match the plaintext code
        $this->assertNotEquals($otpCode, $otpRecord->otp);
        // But it should be a valid bcrypt hash
        $this->assertTrue(Hash::check($otpCode, $otpRecord->otp));
    }

    // ─── M4: Fixed OTP Only in Non-Production ──────────────────────────

    public function test_fixed_otp_not_used_in_non_local_environment(): void
    {
        config(['otp.fixed_code' => '123456']);
        $this->app['env'] = 'production';

        $otpService = app(\App\Services\OtpService::class);
        $otpCode = $otpService->generate('test@example.com', 'password_reset');

        // Should NOT be the fixed code in production
        $this->assertNotEquals('123456', $otpCode);
    }
}
