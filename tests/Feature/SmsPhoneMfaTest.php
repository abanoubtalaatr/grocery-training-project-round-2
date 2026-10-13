<?php

namespace Tests\Feature;

use App\Models\Otp;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\TestCase;

class SmsPhoneMfaTest extends TestCase
{
    use DatabaseTransactions;

    private string $phone = '+15551234567';

    protected function setUp(): void
    {
        parent::setUp();

        config(['otp.fixed_code' => '123456']);
        config(['services.twilio.sid' => 'test_sid']);
        config(['services.twilio.token' => 'test_token']);
        config(['services.twilio.phone' => '+15559990000']);
    }

    private function createUserWithPhone(): User
    {
        return User::create([
            'username' => 'sms_user_' . uniqid(),
            'phone' => $this->phone,
            'password' => Hash::make('SecurePassword123!'),
            'role' => User::ROLE_CUSTOMER,
        ]);
    }

    public function test_phone_password_reset_generates_otp_and_attempts_sms_delivery(): void
    {
        $user = $this->createUserWithPhone();

        $notificationService = Mockery::mock(NotificationService::class);
        $notificationService->shouldReceive('sendOtpSms')
            ->once()
            ->with($this->phone, '123456', 'password_reset')
            ->andReturn(true);
        $this->app->instance(NotificationService::class, $notificationService);

        $response = $this->postJson('/api/auth/forgot-password', [
            'identifier' => $this->phone,
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('otps', [
            'identifier' => $this->phone,
            'type' => 'password_reset',
            'is_used' => false,
        ]);
    }

    public function test_twilio_sms_called_with_correct_destination_and_sender(): void
    {
        $mockMessages = Mockery::mock();
        $mockMessages->shouldReceive('create')
            ->once()
            ->with($this->phone, Mockery::on(function ($params) {
                return $params['from'] === '+15559990000'
                    && str_contains($params['body'], '123456');
            }));

        $mockClient = Mockery::mock();
        $mockClient->messages = $mockMessages;

        $service = new class($mockClient) extends NotificationService {
            private $mockClient;

            public function __construct($mockClient)
            {
                $this->mockClient = $mockClient;
            }

            protected function createTwilioClient(string $sid, string $token): mixed
            {
                return $this->mockClient;
            }
        };

        $result = $service->sendOtpSms($this->phone, '123456', 'mfa_login');

        $this->assertTrue($result);
    }

    public function test_otp_value_included_in_sms_message(): void
    {
        $capturedBody = null;
        $mockMessages = Mockery::mock();
        $mockMessages->shouldReceive('create')
            ->once()
            ->withArgs(function (string $to, array $options) use (&$capturedBody) {
                $capturedBody = $options['body'];
                return true;
            });

        $mockClient = Mockery::mock();
        $mockClient->messages = $mockMessages;

        $service = new class($mockClient) extends NotificationService {
            private $mockClient;

            public function __construct($mockClient)
            {
                $this->mockClient = $mockClient;
            }

            protected function createTwilioClient(string $sid, string $token): mixed
            {
                return $this->mockClient;
            }
        };

        $service->sendOtpSms($this->phone, '123456', 'mfa_login');

        $this->assertNotNull($capturedBody);
        $this->assertStringContainsString('123456', $capturedBody);
    }

    public function test_invalid_otp_does_not_authenticate_user(): void
    {
        $user = $this->createUserWithPhone();

        $notificationService = Mockery::mock(NotificationService::class);
        $notificationService->shouldReceive('sendOtpSms')->andReturn(true);
        $this->app->instance(NotificationService::class, $notificationService);

        app(\App\Services\OtpService::class)->generate($this->phone, 'mfa_login');

        $response = $this->postJson('/api/auth/verify-mfa', [
            'identifier' => $this->phone,
            'otp' => '000000',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('success', false);
    }

    public function test_valid_otp_authenticates_user(): void
    {
        $user = $this->createUserWithPhone();

        $otp = app(\App\Services\OtpService::class)->generate($this->phone, 'mfa_login');

        $response = $this->postJson('/api/auth/verify-mfa', [
            'identifier' => $this->phone,
            'otp' => $otp,
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Login successful')
            ->assertJsonPath('data.user.id', $user->id)
            ->assertJsonPath('data.token', fn ($token) => is_string($token) && !empty($token));
    }

    public function test_expired_otp_is_rejected(): void
    {
        $user = $this->createUserWithPhone();

        Otp::create([
            'identifier' => $this->phone,
            'otp' => Hash::make('123456'),
            'type' => 'mfa_login',
            'is_used' => false,
            'expires_at' => now()->subMinutes(10),
        ]);

        $response = $this->postJson('/api/auth/verify-mfa', [
            'identifier' => $this->phone,
            'otp' => '123456',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('success', false);
    }

    public function test_otp_cannot_be_reused(): void
    {
        $user = $this->createUserWithPhone();

        $otp = app(\App\Services\OtpService::class)->generate($this->phone, 'mfa_login');

        // First use: succeeds
        $this->postJson('/api/auth/verify-mfa', [
            'identifier' => $this->phone,
            'otp' => $otp,
        ])->assertOk();

        // Second use: fails
        $this->postJson('/api/auth/verify-mfa', [
            'identifier' => $this->phone,
            'otp' => $otp,
        ])->assertStatus(401);
    }

    public function test_sms_provider_failure_is_handled_safely(): void
    {
        $user = $this->createUserWithPhone();

        $notificationService = Mockery::mock(NotificationService::class);
        $notificationService->shouldReceive('sendOtpSms')->andReturn(false);
        $this->app->instance(NotificationService::class, $notificationService);

        $response = $this->postJson('/api/auth/forgot-password', [
            'identifier' => $this->phone,
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('otps', [
            'identifier' => $this->phone,
            'type' => 'password_reset',
        ]);
    }

    public function test_otp_not_written_to_application_logs(): void
    {
        $mockMessages = Mockery::mock();
        $mockMessages->shouldReceive('create')->once()->andReturn((object) []);

        $mockClient = Mockery::mock();
        $mockClient->messages = $mockMessages;

        $service = new class($mockClient) extends NotificationService {
            private $mockClient;

            public function __construct($mockClient)
            {
                $this->mockClient = $mockClient;
            }

            protected function createTwilioClient(string $sid, string $token): mixed
            {
                return $this->mockClient;
            }
        };

        Log::shouldReceive('info')->once()->with(Mockery::on(fn ($msg) => str_contains($msg, 'phone ending in')));
        Log::shouldReceive('error')->never();

        $result = $service->sendOtpSms($this->phone, '123456', 'mfa_login');

        $this->assertTrue($result);
    }

    public function test_email_otp_behavior_remains_unchanged(): void
    {
        $email = 'sms_test_' . uniqid() . '@example.com';
        $user = User::create([
            'username' => 'email_user_' . uniqid(),
            'email' => $email,
            'password' => Hash::make('SecurePassword123!'),
            'role' => User::ROLE_CUSTOMER,
        ]);

        $response = $this->postJson('/api/auth/forgot-password', [
            'identifier' => $email,
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('otps', [
            'identifier' => $email,
            'type' => 'password_reset',
            'is_used' => false,
        ]);
    }

    public function test_phone_forgot_password_uses_sms(): void
    {
        $user = $this->createUserWithPhone();

        $notificationService = Mockery::mock(NotificationService::class);
        $notificationService->shouldReceive('sendOtpSms')
            ->once()
            ->with($this->phone, '123456', 'password_reset')
            ->andReturn(true);
        $this->app->instance(NotificationService::class, $notificationService);

        $response = $this->postJson('/api/auth/forgot-password', [
            'identifier' => $this->phone,
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('otps', [
            'identifier' => $this->phone,
            'type' => 'password_reset',
        ]);
    }

    public function test_send_otp_sms_returns_false_when_twilio_config_incomplete(): void
    {
        config(['services.twilio.sid' => null]);
        config(['services.twilio.token' => null]);
        config(['services.twilio.phone' => null]);

        $service = new NotificationService();
        $result = $service->sendOtpSms($this->phone, '123456', 'mfa_login');

        $this->assertFalse($result);
    }

    public function test_sms_message_contains_app_name_and_validity(): void
    {
        $mockMessages = Mockery::mock();
        $mockMessages->shouldReceive('create')
            ->once()
            ->withArgs(function (string $to, array $options) {
                $this->assertStringContainsString(config('app.name'), $options['body']);
                $this->assertStringContainsString(config('otp.expiry_minutes', 3), $options['body']);
                return true;
            });

        $mockClient = Mockery::mock();
        $mockClient->messages = $mockMessages;

        $service = new class($mockClient) extends NotificationService {
            private $mockClient;

            public function __construct($mockClient)
            {
                $this->mockClient = $mockClient;
            }

            protected function createTwilioClient(string $sid, string $token): mixed
            {
                return $this->mockClient;
            }
        };

        $service->sendOtpSms($this->phone, '5678', 'mfa_login');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
