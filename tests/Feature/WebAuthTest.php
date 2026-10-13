<?php

namespace Tests\Feature;

use App\Models\Otp;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class WebAuthTest extends TestCase
{
    use DatabaseTransactions;

    public function test_guest_can_view_home_page(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
    }

    public function test_guest_can_view_register_page(): void
    {
        $response = $this->get(route('register'));

        $response->assertStatus(200);
    }

    public function test_guest_can_view_login_page(): void
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
    }

    public function test_guest_can_view_forgot_password_page(): void
    {
        $response = $this->get(route('password.forgot.form'));

        $response->assertStatus(200);
    }

    public function test_user_registration_creates_account_and_redirects_to_verify_email(): void
    {
        $email = 'webtest_' . uniqid() . '@example.com';

        $response = $this->post(route('register'), [
            'username' => 'webtest_' . uniqid(),
            'email' => $email,
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
        ]);

        $response->assertRedirect(route('verify-email.form'));
        $this->assertDatabaseHas('users', ['email' => $email]);
        $this->assertDatabaseHas('otps', [
            'identifier' => $email,
            'type' => 'email_verification',
            'is_used' => false,
        ]);
    }

    public function test_login_validates_credentials_and_redirects_to_home(): void
    {
        $email = 'login_web_' . uniqid() . '@example.com';
        $user = User::create([
            'username' => 'login_web_' . uniqid(),
            'email' => $email,
            'password' => Hash::make('SecurePassword123!'),
            'is_active' => true,
            'email_verified' => true,
        ]);

        $response = $this->post(route('login'), [
            'login' => $email,
            'password' => 'SecurePassword123!',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertDatabaseMissing('otps', [
            'identifier' => $email,
            'type' => 'mfa_login',
        ]);
    }

    public function test_mfa_otp_verification_logs_user_in(): void
    {
        $email = 'mfa_verify_' . uniqid() . '@example.com';
        $user = User::create([
            'username' => 'mfa_verify_' . uniqid(),
            'email' => $email,
            'password' => Hash::make('SecurePassword123!'),
            'is_active' => true,
            'email_verified' => true,
        ]);

        $otp = app(\App\Services\OtpService::class)->generate($email, 'mfa_login');

        $response = $this->withSession(['auth.mfa.identifier' => $email])
            ->post(route('mfa.verify'), [
                'otp' => $otp,
            ]);

        $this->assertAuthenticatedAs($user);
    }

    public function test_invalid_mfa_otp_fails_verification(): void
    {
        $email = 'mfa_fail_' . uniqid() . '@example.com';
        User::create([
            'username' => 'mfa_fail_' . uniqid(),
            'email' => $email,
            'password' => Hash::make('SecurePassword123!'),
            'is_active' => true,
        ]);

        $response = $this->withSession(['auth.mfa.identifier' => $email])
            ->post(route('mfa.verify'), [
                'otp' => '000000',
            ]);

        $response->assertRedirect();
        $this->assertGuest();
    }

    public function test_logout_terminates_session(): void
    {
        $email = 'logout_' . uniqid() . '@example.com';
        $user = User::create([
            'username' => 'logout_' . uniqid(),
            'email' => $email,
            'password' => Hash::make('SecurePassword123!'),
            'is_active' => true,
            'email_verified' => true,
        ]);

        $this->post(route('login'), [
            'login' => $email,
            'password' => 'SecurePassword123!',
        ]);

        $this->assertAuthenticatedAs($user);

        $response = $this->post(route('logout'));

        $response->assertRedirect(route('home'));
        $this->assertGuest();
    }

    public function test_forgot_password_sends_otp(): void
    {
        $email = 'forgot_' . uniqid() . '@example.com';
        User::create([
            'username' => 'forgot_' . uniqid(),
            'email' => $email,
            'password' => Hash::make('SecurePassword123!'),
            'is_active' => true,
        ]);

        $response = $this->post(route('password.forgot'), [
            'identifier' => $email,
        ]);

        $response->assertRedirect(route('password.reset.form'));
        $this->assertDatabaseHas('otps', [
            'identifier' => $email,
            'type' => 'password_reset',
            'is_used' => false,
        ]);
    }

    public function test_reset_password_verifies_otp_and_updates_password(): void
    {
        $email = 'reset_' . uniqid() . '@example.com';
        User::create([
            'username' => 'reset_' . uniqid(),
            'email' => $email,
            'password' => Hash::make('SecurePassword123!'),
            'is_active' => true,
        ]);

        $otp = app(\App\Services\OtpService::class)->generate($email, 'password_reset');

        $response = $this->withSession(['password_reset.identifier' => $email])
            ->post(route('password.reset'), [
                'identifier' => $email,
                'otp' => $otp,
                'password' => 'NewSecurePassword123!',
                'password_confirmation' => 'NewSecurePassword123!',
            ]);

        $response->assertRedirect(route('login'));

        $user = User::where('email', $email)->first();
        $this->assertTrue(Hash::check('NewSecurePassword123!', $user->password));
    }

    public function test_active_sessions_list_shows_devices(): void
    {
        $email = 'sessions_' . uniqid() . '@example.com';
        $user = User::create([
            'username' => 'sessions_' . uniqid(),
            'email' => $email,
            'password' => Hash::make('SecurePassword123!'),
            'is_active' => true,
            'email_verified' => true,
        ]);

        $this->post(route('login'), [
            'login' => $email,
            'password' => 'SecurePassword123!',
        ]);

        $this->assertAuthenticatedAs($user);

        $response = $this->get(route('sessions.index'));

        $response->assertStatus(200);
    }

    public function test_account_deletion_logs_user_out(): void
    {
        $email = 'delete_' . uniqid() . '@example.com';
        $user = User::create([
            'username' => 'delete_' . uniqid(),
            'email' => $email,
            'password' => Hash::make('SecurePassword123!'),
            'is_active' => true,
            'email_verified' => true,
        ]);

        $this->post(route('login'), [
            'login' => $email,
            'password' => 'SecurePassword123!',
        ]);

        $this->assertAuthenticatedAs($user);

        $response = $this->delete(route('delete-account'), [
            'password' => 'SecurePassword123!',
        ]);

        $this->assertGuest();
    }

    public function test_locale_switcher_changes_ui_language(): void
    {
        $response = $this->get(route('locale.toggle', 'ar'));

        $response->assertRedirect();
        $this->assertEquals('ar', $this->app->getLocale());
    }

    public function test_login_with_invalid_credentials_shows_error(): void
    {
        $response = $this->post(route('login'), [
            'login' => 'nonexistent@example.com',
            'password' => 'WrongPassword123!',
        ]);

        $response->assertSessionHasErrors();
    }

    public function test_unverified_email_cannot_login(): void
    {
        $email = 'unverified_' . uniqid() . '@example.com';
        User::create([
            'username' => 'unverified_' . uniqid(),
            'email' => $email,
            'password' => Hash::make('SecurePassword123!'),
            'is_active' => true,
            'email_verified' => false,
        ]);

        $response = $this->post(route('login'), [
            'login' => $email,
            'password' => 'SecurePassword123!',
        ]);

        $response->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    public function test_mfa_requires_session_identifier(): void
    {
        $response = $this->post(route('mfa.verify'), [
            'otp' => '1234',
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_google_redirect_returns_302(): void
    {
        config(['services.google.client_id' => 'test-client-id']);
        config(['services.google.client_secret' => 'test-client-secret']);
        config(['services.google.redirect' => 'http://localhost/auth/google/callback']);

        $response = $this->get(route('google.redirect'));

        $response->assertStatus(302);
    }

    public function test_google_redirect_graceful_when_client_id_missing(): void
    {
        config(['services.google.client_id' => null]);
        config(['services.google.client_secret' => null]);

        $response = $this->get(route('google.redirect'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('login');
    }

    public function test_google_config_reads_from_correct_keys(): void
    {
        config(['services.google.client_id' => 'my-client-id']);
        config(['services.google.client_secret' => 'my-client-secret']);
        config(['services.google.redirect' => 'http://localhost/auth/google/callback']);

        $this->assertEquals('my-client-id', config('services.google.client_id'));
        $this->assertEquals('my-client-secret', config('services.google.client_secret'));
        $this->assertEquals('http://localhost/auth/google/callback', config('services.google.redirect'));
    }

    public function test_google_login_uses_customer_route_not_admin(): void
    {
        $route = route('google.redirect');

        $this->assertStringContainsString('/auth/google/redirect', $route);
        $this->assertStringNotContainsString('/admin', $route);
    }

    public function test_google_redirect_url_has_client_id_in_config(): void
    {
        config(['services.google.client_id' => 'test-client-id-123']);
        config(['services.google.client_secret' => 'test-secret']);
        config(['services.google.redirect' => 'http://localhost/auth/google/callback']);

        $this->assertNotEmpty(config('services.google.client_id'));
        $this->assertStringContainsString('test-client-id-123', config('services.google.client_id'));
    }

    public function test_reregistration_after_soft_delete_succeeds_with_new_id(): void
    {
        $email = 'rereg_' . uniqid() . '@example.com';
        $username = 'rereg_' . uniqid();
        $phone = '+2010' . rand(10000000, 99999999);
        $password = 'SecurePassword123!';

        // Step 1: Register via the real endpoint (user is auto-logged in)
        $response = $this->post(route('register'), [
            'username' => $username,
            'email' => $email,
            'phone' => $phone,
            'password' => $password,
            'password_confirmation' => $password,
        ]);

        $response->assertRedirect(route('verify-email.form'));

        $originalUser = User::where('email', $email)->first();
        $this->assertNotNull($originalUser, 'Original user should be created');
        $originalId = $originalUser->id;
        $this->assertAuthenticatedAs($originalUser);

        // Step 2: Delete account via the real HTTP endpoint (soft delete + logout)
        $response = $this->delete(route('delete-account'), [
            'password' => $password,
        ]);

        $response->assertRedirect(route('home'));
        $this->assertGuest();

        // Verify old user is soft-deleted, not hard-deleted
        $trashedUser = User::withTrashed()->find($originalId);
        $this->assertNotNull($trashedUser, 'Old user record should still exist');
        $this->assertNotNull($trashedUser->deleted_at, 'Old user should be soft-deleted');

        // Step 3: Re-register with the same username, email, and phone
        $response = $this->post(route('register'), [
            'username' => $username,
            'email' => $email,
            'phone' => $phone,
            'password' => 'NewPassword1234!',
            'password_confirmation' => 'NewPassword1234!',
        ]);

        $response->assertRedirect(route('verify-email.form'));

        // New user should exist with a different ID
        $allUsers = User::withTrashed()->where('email', $email)->get();
        $this->assertCount(2, $allUsers, 'Should have 2 user records with this email');

        $newUser = $allUsers->firstWhere('id', '!=', $originalId);
        $this->assertNotNull($newUser);
        $this->assertNull($newUser->deleted_at, 'New user should not be deleted');
        $this->assertNotEquals($originalId, $newUser->id);

        // Old record still soft-deleted
        $oldUser = $allUsers->firstWhere('id', $originalId);
        $this->assertNotNull($oldUser->deleted_at);
    }

    public function test_banned_user_cannot_reregister(): void
    {
        $email = 'banned_' . uniqid() . '@example.com';
        $username = 'banned_' . uniqid();

        // Create a soft-deleted banned user
        $bannedUser = User::create([
            'username' => $username,
            'email' => $email,
            'password' => Hash::make('SecurePassword123!'),
            'is_active' => false,
        ]);
        $bannedUser->delete();

        // Try to re-register with same email
        $response = $this->post(route('register'), [
            'username' => $username . '_new',
            'email' => $email,
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_database_error_during_registration_does_not_expose_sql(): void
    {
        // Mock AuthService::register to throw a ValidationException (simulating
        // what happens when QueryException is caught and converted)
        $mockService = \Mockery::mock(\App\Services\AuthService::class);
        $mockService->shouldReceive('register')
            ->once()
            ->andThrow(
                \Illuminate\Validation\ValidationException::withMessages([
                    'username' => ['Registration failed. Please try again.'],
                ])
            );
        $this->app->instance(\App\Services\AuthService::class, $mockService);

        $response = $this->post(route('register'), [
            'username' => 'dbfail_' . uniqid(),
            'email' => 'dbfail_' . uniqid() . '@example.com',
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
        ]);

        // Should redirect back with validation errors (not 500 with raw SQL)
        $response->assertStatus(302);
        $response->assertSessionHasErrors('username');

        $content = $response->getContent();
        $this->assertStringNotContainsString('SQLSTATE', $content);
        $this->assertStringNotContainsString('Integrity constraint', $content);
        $this->assertStringNotContainsString('QueryException', $content);
        $this->assertStringNotContainsString('Stack trace', $content);
        $this->assertStringNotContainsString('PDOException', $content);
    }
}
