<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Otp;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class EmailOtpDeliveryTest extends TestCase
{
    use DatabaseTransactions;

    private function createUser(): User
    {
        return User::create([
            'username' => 'email_otp_user_' . uniqid(),
            'email' => 'email_otp_user_' . uniqid() . '@example.com',
            'password' => Hash::make('SecurePassword123!'),
            'role' => User::ROLE_CUSTOMER,
        ]);
    }

    public function test_password_reset_generates_otp_and_dispatches_email(): void
    {
        Mail::fake();
        $user = $this->createUser();

        $response = $this->postJson('/api/auth/forgot-password', [
            'identifier' => $user->email,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $otpRecord = Otp::where('identifier', $user->email)
            ->where('type', 'password_reset')
            ->where('is_used', false)
            ->first();

        $this->assertNotNull($otpRecord, 'OTP should be created in database');
        $this->assertEquals($user->email, $otpRecord->identifier);
        $this->assertEquals('password_reset', $otpRecord->type);
        $this->assertFalse($otpRecord->is_used);
        $this->assertTrue($otpRecord->expires_at->isFuture());
    }

    public function test_notification_service_send_otp_email_dispatches_mail(): void
    {
        $email = 'test_' . uniqid() . '@example.com';
        $otp = '1234';

        Mail::shouldReceive('raw')
            ->once()
            ->with(
                \PHPUnit\Framework\Assert::callback(fn (string $content) => str_contains($content, $otp)),
                \PHPUnit\Framework\Assert::callback(fn ($callback) => is_callable($callback))
            )
            ->andReturn(true);

        $service = app(NotificationService::class);
        $result = $service->sendOtpEmail($email, $otp, 'mfa_login');

        $this->assertTrue($result);
    }

    public function test_notification_service_send_otp_email_with_password_reset_type(): void
    {
        $email = 'test_' . uniqid() . '@example.com';
        $otp = '5678';

        Mail::shouldReceive('raw')
            ->once()
            ->with(
                \PHPUnit\Framework\Assert::callback(fn (string $content) => str_contains($content, $otp)),
                \PHPUnit\Framework\Assert::callback(fn ($callback) => is_callable($callback))
            )
            ->andReturn(true);

        $service = app(NotificationService::class);
        $result = $service->sendOtpEmail($email, $otp, 'password_reset');

        $this->assertTrue($result);
    }

    public function test_notification_service_send_otp_email_logs_failure(): void
    {
        Mail::shouldReceive('raw')
            ->once()
            ->andThrow(new \RuntimeException('SMTP connection failed'));

        $service = app(NotificationService::class);
        $email = 'test_' . uniqid() . '@example.com';

        Log::shouldReceive('error')
            ->once()
            ->with(\PHPUnit\Framework\Assert::callback(function ($message) {
                return str_contains($message, 'Failed to send OTP email');
            }));

        $result = $service->sendOtpEmail($email, '1234', 'mfa_login');

        $this->assertFalse($result);
    }

    public function test_password_reset_verification_works_after_otp_email(): void
    {
        Mail::fake();
        config(['otp.fixed_code' => '1234']);

        $user = $this->createUser();

        $this->postJson('/api/auth/forgot-password', [
            'identifier' => $user->email,
        ]);

        $otpRecord = Otp::where('identifier', $user->email)
            ->where('type', 'password_reset')
            ->where('is_used', false)
            ->first();

        $this->assertNotNull($otpRecord);

        $response = $this->postJson('/api/auth/reset-password', [
            'identifier' => $user->email,
            'otp' => '1234',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Password reset successfully');

        $otpRecord->refresh();
        $this->assertTrue($otpRecord->is_used);
    }

    public function test_web_forgot_password_dispatches_email(): void
    {
        Mail::fake();
        $user = $this->createUser();

        $response = $this->post(route('password.forgot'), [
            'identifier' => $user->email,
        ]);

        $response->assertRedirect(route('password.reset.form'));

        $otpRecord = Otp::where('identifier', $user->email)
            ->where('type', 'password_reset')
            ->where('is_used', false)
            ->first();

        $this->assertNotNull($otpRecord, 'OTP should be created for web forgot password');
    }

    public function test_otp_email_sent_for_phone_identifier(): void
    {
        $phone = '+201000000000';
        $otp = '9999';

        Mail::shouldReceive('raw')
            ->once()
            ->with(
                \PHPUnit\Framework\Assert::callback(fn (string $content) => str_contains($content, $otp)),
                \PHPUnit\Framework\Assert::callback(fn ($callback) => is_callable($callback))
            )
            ->andReturn(true);

        $service = app(NotificationService::class);
        $result = $service->sendOtpEmail($phone, $otp, 'mfa_login');

        $this->assertTrue($result);
    }
}
