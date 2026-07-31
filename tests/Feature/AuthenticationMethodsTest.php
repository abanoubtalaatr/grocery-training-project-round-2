<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\ClientRepository;
use Tests\TestCase;

class AuthenticationMethodsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('passport:keys', ['--force' => true]);
    }

    public function test_it_shows_the_auth_presentation_home(): void
    {
        $this->get(route('presentation.index'))
            ->assertOk()
            ->assertSee('Four authentication methods');
    }

    public function test_session_login_and_dashboard_access(): void
    {
        $user = User::factory()->student()->create([
            'email' => 'session-student@example.com',
            'email_verified_at' => now(),
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Session Auth');
    }

    public function test_sanctum_token_login_and_profile(): void
    {
        $user = User::factory()->student()->create([
            'email' => 'sanctum@example.com',
        ]);

        $login = $this->postJson('/api/sanctum/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertOk()
            ->assertJsonPath('auth', 'sanctum');

        $token = $login->json('token');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/sanctum/profile')
            ->assertOk()
            ->assertJsonPath('user.email', $user->email);
    }

    public function test_jwt_login_and_profile(): void
    {
        $user = User::factory()->student()->create([
            'email' => 'jwt@example.com',
        ]);

        $login = $this->postJson('/api/jwt/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertOk()
            ->assertJsonPath('auth', 'jwt');

        $token = $login->json('access_token');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/jwt/profile')
            ->assertOk()
            ->assertJsonPath('user.email', $user->email);
    }

    public function test_passport_client_credentials_with_courses_read_scope(): void
    {
        $instructor = User::factory()->instructor()->create();

        Course::query()->create([
            'title' => 'OAuth Course',
            'description' => 'Demo',
            'instructor_id' => $instructor->id,
        ]);

        $client = app(ClientRepository::class)->createClientCredentialsGrantClient('ABC University Test');

        $tokenResponse = $this->post('/oauth/token', [
            'grant_type' => 'client_credentials',
            'client_id' => $client->id,
            'client_secret' => $client->plainSecret,
            'scope' => 'courses.read',
        ])->assertOk();

        $accessToken = $tokenResponse->json('access_token');

        $this->withHeader('Authorization', 'Bearer '.$accessToken)
            ->getJson('/api/oauth-demo/courses')
            ->assertOk()
            ->assertJsonPath('auth', 'passport');
    }

    public function test_passport_rejects_missing_required_scope(): void
    {
        $client = app(ClientRepository::class)->createClientCredentialsGrantClient('ExamPro Test');

        $tokenResponse = $this->post('/oauth/token', [
            'grant_type' => 'client_credentials',
            'client_id' => $client->id,
            'client_secret' => $client->plainSecret,
            'scope' => 'grades.read',
        ])->assertOk();

        $accessToken = $tokenResponse->json('access_token');

        $this->withHeader('Authorization', 'Bearer '.$accessToken)
            ->getJson('/api/oauth-demo/students')
            ->assertForbidden();
    }

    public function test_instructor_can_create_course_via_session(): void
    {
        $instructor = User::factory()->instructor()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($instructor)
            ->post(route('courses.store'), [
                'title' => 'New Auth Course',
                'description' => 'Session CRUD demo',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('courses', [
            'title' => 'New Auth Course',
            'instructor_id' => $instructor->id,
        ]);
    }
}
