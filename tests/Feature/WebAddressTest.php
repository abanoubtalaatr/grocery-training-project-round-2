<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

class WebAddressTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Address::query()->delete();

        $this->user = User::create([
            'username' => 'addruser_' . Str::random(6),
            'email' => 'addr_' . Str::random(6) . '@example.com',
            'password' => bcrypt('Password123!'),
            'firstname' => 'Test',
            'lastname' => 'Address',
        ]);
    }

    private function login(): void
    {
        $this->actingAs($this->user, 'web');
    }

    private function createAddress(array $overrides = []): Address
    {
        return Address::create(array_merge([
            'user_id' => $this->user->id,
            'full_name' => 'Test User',
            'phone' => '1234567890',
            'street_address' => '123 Main St',
            'city' => 'New York',
            'is_default' => false,
        ], $overrides));
    }

    public function test_guest_cannot_view_addresses(): void
    {
        $response = $this->get(route('addresses.index'));

        $response->assertRedirect();
    }

    public function test_user_can_view_addresses(): void
    {
        $this->login();
        $this->createAddress();

        $response = $this->get(route('addresses.index'));

        $response->assertStatus(200);
        $response->assertSee('123 Main St');
    }

    public function test_empty_addresses_shows_empty_state(): void
    {
        $this->login();

        $response = $this->get(route('addresses.index'));

        $response->assertStatus(200);
        $response->assertSee("haven");
    }

    public function test_user_can_view_create_address_form(): void
    {
        $this->login();

        $response = $this->get(route('addresses.create'));

        $response->assertStatus(200);
    }

    public function test_user_can_store_address(): void
    {
        $this->login();

        $response = $this->post(route('addresses.store'), [
            'full_name' => 'John Doe',
            'phone' => '5551234567',
            'street_address' => '456 Oak Ave',
            'city' => 'Los Angeles',
            'country_code' => '+1',
            'building_number' => '10',
            'floor' => '3',
            'apartment' => 'A',
            'state' => 'CA',
            'postal_code' => '90001',
            'country' => 'USA',
        ]);

        $response->assertRedirect(route('addresses.index'));
        $this->assertDatabaseHas('addresses', [
            'user_id' => $this->user->id,
            'full_name' => 'John Doe',
            'street_address' => '456 Oak Ave',
            'city' => 'Los Angeles',
        ]);
    }

    public function test_first_address_becomes_default(): void
    {
        $this->login();

        $this->post(route('addresses.store'), [
            'full_name' => 'John Doe',
            'phone' => '5551234567',
            'street_address' => '456 Oak Ave',
            'city' => 'Los Angeles',
        ]);

        $this->assertDatabaseHas('addresses', [
            'user_id' => $this->user->id,
            'is_default' => true,
        ]);
    }

    public function test_store_requires_full_name(): void
    {
        $this->login();

        $response = $this->post(route('addresses.store'), [
            'phone' => '5551234567',
            'street_address' => '456 Oak Ave',
            'city' => 'Los Angeles',
        ]);

        $response->assertSessionHasErrors('full_name');
    }

    public function test_store_requires_street_address(): void
    {
        $this->login();

        $response = $this->post(route('addresses.store'), [
            'full_name' => 'John Doe',
            'phone' => '5551234567',
            'city' => 'Los Angeles',
        ]);

        $response->assertSessionHasErrors('street_address');
    }

    public function test_store_requires_city(): void
    {
        $this->login();

        $response = $this->post(route('addresses.store'), [
            'full_name' => 'John Doe',
            'phone' => '5551234567',
            'street_address' => '456 Oak Ave',
        ]);

        $response->assertSessionHasErrors('city');
    }

    public function test_store_requires_phone(): void
    {
        $this->login();

        $response = $this->post(route('addresses.store'), [
            'full_name' => 'John Doe',
            'street_address' => '456 Oak Ave',
            'city' => 'Los Angeles',
        ]);

        $response->assertSessionHasErrors('phone');
    }

    public function test_user_can_view_edit_address_form(): void
    {
        $this->login();
        $address = $this->createAddress();

        $response = $this->get(route('addresses.edit', $address->id));

        $response->assertStatus(200);
        $response->assertSee('123 Main St');
    }

    public function test_user_cannot_edit_other_users_address(): void
    {
        $otherUser = User::create([
            'username' => 'other_' . Str::random(6),
            'email' => 'other_' . Str::random(6) . '@example.com',
            'password' => bcrypt('Password123!'),
            'firstname' => 'Other',
            'lastname' => 'User',
        ]);
        $otherAddress = Address::create([
            'user_id' => $otherUser->id,
            'full_name' => 'Other User',
            'phone' => '9999999999',
            'street_address' => '789 Other St',
            'city' => 'Other City',
        ]);

        $this->login();

        $response = $this->get(route('addresses.edit', $otherAddress->id));

        $response->assertStatus(404);
    }

    public function test_user_can_update_address(): void
    {
        $this->login();
        $address = $this->createAddress();

        $response = $this->put(route('addresses.update', $address->id), [
            'full_name' => 'Updated Name',
            'phone' => '1234567890',
            'street_address' => '789 Updated St',
            'city' => 'Updated City',
        ]);

        $response->assertRedirect(route('addresses.index'));
        $this->assertDatabaseHas('addresses', [
            'id' => $address->id,
            'full_name' => 'Updated Name',
            'street_address' => '789 Updated St',
        ]);
    }

    public function test_user_can_delete_address(): void
    {
        $this->login();
        $address = $this->createAddress();

        $response = $this->delete(route('addresses.destroy', $address->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('addresses', ['id' => $address->id]);
    }

    public function test_deleting_default_address_promotes_another(): void
    {
        $this->login();
        $address1 = $this->createAddress(['is_default' => true]);
        $address2 = $this->createAddress(['is_default' => false]);

        $this->delete(route('addresses.destroy', $address1->id));

        $this->assertDatabaseMissing('addresses', ['id' => $address1->id]);
        $this->assertDatabaseHas('addresses', [
            'id' => $address2->id,
            'is_default' => true,
        ]);
    }

    public function test_user_can_set_default_address(): void
    {
        $this->login();
        $address1 = $this->createAddress(['is_default' => true]);
        $address2 = $this->createAddress(['is_default' => false]);

        $response = $this->post(route('addresses.setDefault', $address2->id));

        $response->assertRedirect();
        $this->assertDatabaseHas('addresses', [
            'id' => $address2->id,
            'is_default' => true,
        ]);
        $this->assertDatabaseHas('addresses', [
            'id' => $address1->id,
            'is_default' => false,
        ]);
    }

    public function test_user_cannot_delete_other_users_address(): void
    {
        $otherUser = User::create([
            'username' => 'other_' . Str::random(6),
            'email' => 'other_' . Str::random(6) . '@example.com',
            'password' => bcrypt('Password123!'),
            'firstname' => 'Other',
            'lastname' => 'User',
        ]);
        $otherAddress = Address::create([
            'user_id' => $otherUser->id,
            'full_name' => 'Other User',
            'phone' => '9999999999',
            'street_address' => '789 Other St',
            'city' => 'Other City',
        ]);

        $this->login();

        $response = $this->delete(route('addresses.destroy', $otherAddress->id));

        $response->assertStatus(404);
    }

    public function test_address_shows_label_and_default_badge(): void
    {
        $this->login();
        $this->createAddress(['label' => 'Home', 'is_default' => true]);

        $response = $this->get(route('addresses.index'));

        $response->assertStatus(200);
        $response->assertSee('Home');
        $response->assertSee('Default');
    }
}
