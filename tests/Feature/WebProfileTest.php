<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class WebProfileTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'username' => 'profileuser_' . Str::random(6),
            'email' => 'profile_' . Str::random(6) . '@example.com',
            'password' => bcrypt('Password123!'),
            'firstname' => 'Test',
            'lastname' => 'Profile',
            'phone' => '1234567890',
            'country_code' => '+1',
        ]);
    }

    private function login(): void
    {
        $this->actingAs($this->user, 'web');
    }

    public function test_guest_cannot_view_profile(): void
    {
        $response = $this->get(route('profile.show'));

        $response->assertRedirect();
    }

    public function test_user_can_view_profile(): void
    {
        $this->login();

        $response = $this->get(route('profile.show'));

        $response->assertStatus(200);
        $response->assertSee('Test');
        $response->assertSee('Profile');
    }

    public function test_user_can_view_edit_profile(): void
    {
        $this->login();

        $response = $this->get(route('profile.edit'));

        $response->assertStatus(200);
        $response->assertSee('profileuser_');
    }

    public function test_user_can_update_profile_info(): void
    {
        $this->login();

        $response = $this->put(route('profile.update'), [
            'firstname' => 'Updated',
            'lastname' => 'Name',
            'username' => $this->user->username,
            'email' => $this->user->email,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'firstname' => 'Updated',
            'lastname' => 'Name',
        ]);
    }

    public function test_update_profile_requires_firstname(): void
    {
        $this->login();

        $response = $this->put(route('profile.update'), [
            'firstname' => '',
            'lastname' => 'Name',
            'username' => $this->user->username,
        ]);

        $response->assertSessionHasErrors('firstname');
    }

    public function test_update_profile_requires_lastname(): void
    {
        $this->login();

        $response = $this->put(route('profile.update'), [
            'firstname' => 'Test',
            'lastname' => '',
            'username' => $this->user->username,
        ]);

        $response->assertSessionHasErrors('lastname');
    }

    public function test_update_profile_requires_unique_username(): void
    {
        User::create([
            'username' => 'taken_name',
            'email' => 'taken@example.com',
            'password' => bcrypt('Password123!'),
            'firstname' => 'Taken',
            'lastname' => 'User',
        ]);

        $this->login();

        $response = $this->put(route('profile.update'), [
            'firstname' => 'Test',
            'lastname' => 'Name',
            'username' => 'taken_name',
        ]);

        $response->assertSessionHasErrors('username');
    }

    public function test_user_can_view_image_page(): void
    {
        $this->login();

        $response = $this->get(route('profile.image'));

        $response->assertStatus(200);
    }

    public function test_user_can_upload_profile_image(): void
    {
        Storage::fake('public');
        $this->login();

        $file = UploadedFile::fake()->image('profile.jpg', 200, 200)->size(100);

        $response = $this->post(route('profile.image.upload'), [
            'profile_image' => $file,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
        ]);
        $this->assertNotNull($this->user->fresh()->profile_image);
    }

    public function test_upload_requires_image_file(): void
    {
        $this->login();

        $response = $this->post(route('profile.image.upload'), []);

        $response->assertSessionHasErrors('profile_image');
    }

    public function test_user_can_delete_profile_image(): void
    {
        Storage::fake('public');
        $this->user->update(['profile_image' => 'profile-images/test.jpg']);
        $this->login();

        $response = $this->delete(route('profile.image.delete'));

        $response->assertRedirect();
        $this->assertNull($this->user->fresh()->profile_image);
    }

    public function test_profile_shows_order_stats(): void
    {
        $this->login();

        $response = $this->get(route('profile.show'));

        $response->assertStatus(200);
        $response->assertSee('Total Orders');
        $response->assertSee('Active Orders');
    }

    public function test_profile_shows_personal_info(): void
    {
        $this->login();

        $response = $this->get(route('profile.show'));

        $response->assertStatus(200);
        $response->assertSee('First Name');
        $response->assertSee('Last Name');
        $response->assertSee('Email');
    }

    public function test_profile_shows_quick_actions(): void
    {
        $this->login();

        $response = $this->get(route('profile.show'));

        $response->assertStatus(200);
        $response->assertSee(route('orders.index'));
        $response->assertSee(route('addresses.index'));
        $response->assertSee(route('profile.image'));
    }
}
