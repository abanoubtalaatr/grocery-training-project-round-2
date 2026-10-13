<?php

namespace Tests\Feature;

use App\Models\Otp;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordChangeOtpTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;
    private OtpService $otpService;

    protected function setUp(): void
    {
        parent::setUp();

        config(['otp.fixed_code' => '123456']);
        $this->otpService = app(OtpService::class);

        $this->user = User::create([
            'username' => 'pwchange_' . uniqid(),
            'email' => 'pwchange_' . uniqid() . '@example.com',
            'password' => Hash::make('OldPassword123!'),
            'role' => User::ROLE_CUSTOMER,
        ]);
    }

    public function test_password_change_requires_otp(): void
    {
        $this->actingAs($this->user, 'sanctum');

        $response = $this->postJson('/api/auth/change-password', [
            'current_password' => 'OldPassword123!',
            'password' => 'NewPassword456!',
            'password_confirmation' => 'NewPassword456!',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonFragment(['message' => 'OTP sent to your email. Please verify to complete password change.']);

        // Password should NOT have changed yet
        $this->user->refresh();
        $this->assertTrue(Hash::check('OldPassword123!', $this->user->password));
        $this->assertFalse(Hash::check('NewPassword456!', $this->user->password));
    }

    public function test_valid_otp_allows_password_change(): void
    {
        $this->actingAs($this->user, 'sanctum');

        // Step 1: request OTP
        $this->postJson('/api/auth/change-password', [
            'current_password' => 'OldPassword123!',
            'password' => 'NewPassword456!',
            'password_confirmation' => 'NewPassword456!',
        ]);

        $this->assertDatabaseHas('otps', [
            'identifier' => $this->user->email,
            'type' => Otp::TYPE_PASSWORD_CHANGE,
            'is_used' => false,
        ]);

        // Step 2: verify OTP and change password
        $response = $this->postJson('/api/auth/change-password/confirm', [
            'otp' => '123456',
            'password' => 'NewPassword456!',
            'password_confirmation' => 'NewPassword456!',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Password changed successfully');

        $this->user->refresh();
        $this->assertTrue(Hash::check('NewPassword456!', $this->user->password));

        // OTP should be marked as used
        $this->assertDatabaseHas('otps', [
            'identifier' => $this->user->email,
            'type' => Otp::TYPE_PASSWORD_CHANGE,
            'is_used' => true,
        ]);
    }

    public function test_invalid_otp_prevents_password_change(): void
    {
        $this->actingAs($this->user, 'sanctum');

        $this->postJson('/api/auth/change-password', [
            'current_password' => 'OldPassword123!',
            'password' => 'NewPassword456!',
            'password_confirmation' => 'NewPassword456!',
        ]);

        $response = $this->postJson('/api/auth/change-password/confirm', [
            'otp' => '000000',
            'password' => 'NewPassword456!',
            'password_confirmation' => 'NewPassword456!',
        ]);

        $response->assertStatus(400)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Invalid or expired OTP');

        $this->user->refresh();
        $this->assertTrue(Hash::check('OldPassword123!', $this->user->password));
    }

    public function test_expired_otp_prevents_password_change(): void
    {
        $this->actingAs($this->user, 'sanctum');

        Otp::create([
            'identifier' => $this->user->email,
            'otp' => Hash::make('123456'),
            'type' => Otp::TYPE_PASSWORD_CHANGE,
            'is_used' => false,
            'expires_at' => now()->subMinutes(10),
        ]);

        $response = $this->postJson('/api/auth/change-password/confirm', [
            'otp' => '123456',
            'password' => 'NewPassword456!',
            'password_confirmation' => 'NewPassword456!',
        ]);

        $response->assertStatus(400)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Invalid or expired OTP');

        $this->user->refresh();
        $this->assertTrue(Hash::check('OldPassword123!', $this->user->password));
    }

    public function test_otp_cannot_be_reused(): void
    {
        $this->actingAs($this->user, 'sanctum');

        $this->postJson('/api/auth/change-password', [
            'current_password' => 'OldPassword123!',
            'password' => 'NewPassword456!',
            'password_confirmation' => 'NewPassword456!',
        ]);

        // First use: succeeds
        $this->postJson('/api/auth/change-password/confirm', [
            'otp' => '123456',
            'password' => 'NewPassword456!',
            'password_confirmation' => 'NewPassword456!',
        ])->assertOk();

        // Second use: fails
        $this->postJson('/api/auth/change-password/confirm', [
            'otp' => '123456',
            'password' => 'AnotherPassword789!',
            'password_confirmation' => 'AnotherPassword789!',
        ])->assertStatus(400);
    }

    public function test_wrong_current_password_fails(): void
    {
        $this->actingAs($this->user, 'sanctum');

        $response = $this->postJson('/api/auth/change-password', [
            'current_password' => 'WrongPassword999!',
            'password' => 'NewPassword456!',
            'password_confirmation' => 'NewPassword456!',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['current_password']);

        // No OTP should be generated
        $this->assertDatabaseMissing('otps', [
            'identifier' => $this->user->email,
            'type' => Otp::TYPE_PASSWORD_CHANGE,
        ]);
    }

    public function test_password_policy_enforced_on_confirm(): void
    {
        $this->actingAs($this->user, 'sanctum');

        $this->postJson('/api/auth/change-password', [
            'current_password' => 'OldPassword123!',
            'password' => 'NewPassword456!',
            'password_confirmation' => 'NewPassword456!',
        ]);

        // Try with weak password (no numbers)
        $response = $this->postJson('/api/auth/change-password/confirm', [
            'otp' => '123456',
            'password' => 'NewPasswordNoNumber!',
            'password_confirmation' => 'NewPasswordNoNumber!',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);

        $this->user->refresh();
        $this->assertTrue(Hash::check('OldPassword123!', $this->user->password));
    }

    public function test_password_change_type_constant_exists(): void
    {
        $this->assertEquals('password_change', Otp::TYPE_PASSWORD_CHANGE);
    }
}
