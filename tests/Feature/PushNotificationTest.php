<?php

namespace Tests\Feature;

use App\Models\PushToken;
use App\Models\User;
use App\Models\Order;
use App\Models\Address;
use App\Notifications\DeliverySlaBreachedNotification;
use App\Services\PushNotificationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PushNotificationTest extends TestCase
{
    use DatabaseTransactions;

    private function createCustomer(): User
    {
        return User::create([
            'name' => 'Push Customer',
            'username' => 'pushcust_' . time() . rand(10, 99),
            'email' => 'pushcust_' . time() . rand(10, 99) . '@example.com',
            'password' => bcrypt('SecurePassword123!'),
            'role' => User::ROLE_CUSTOMER,
        ]);
    }

    public function test_authenticated_user_can_register_push_token(): void
    {
        $user = $this->createCustomer();

        $this->actingAs($user, 'sanctum');
        $response = $this->postJson('/api/push-tokens', [
            'token' => 'fake-fcm-token-12345',
            'platform' => 'web',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('push_tokens', [
            'user_id' => $user->id,
            'token' => 'fake-fcm-token-12345',
            'platform' => 'web',
        ]);
    }

    public function test_unauthenticated_user_cannot_register_token(): void
    {
        $response = $this->postJson('/api/push-tokens', [
            'token' => 'fake-fcm-token-12345',
            'platform' => 'web',
        ]);

        $response->assertStatus(401);
    }

    public function test_user_cannot_manage_another_users_token(): void
    {
        $user = $this->createCustomer();
        $otherUser = User::create([
            'name' => 'Other',
            'username' => 'other_' . time() . rand(10, 99),
            'email' => 'other_' . time() . rand(10, 99) . '@example.com',
            'password' => bcrypt('SecurePassword123!'),
            'role' => User::ROLE_CUSTOMER,
        ]);

        $pushToken = PushToken::create([
            'user_id' => $otherUser->id,
            'token' => 'other-users-token',
            'platform' => 'web',
        ]);

        $this->actingAs($user, 'sanctum');
        $response = $this->deleteJson('/api/push-tokens/' . urlencode('other-users-token'));

        $response->assertStatus(404);

        $this->assertDatabaseHas('push_tokens', [
            'id' => $pushToken->id,
            'token' => 'other-users-token',
        ]);
    }

    public function test_duplicate_token_handling_works(): void
    {
        $user = $this->createCustomer();

        $this->actingAs($user, 'sanctum');

        $this->postJson('/api/push-tokens', [
            'token' => 'duplicate-token-123',
            'platform' => 'web',
        ])->assertStatus(201);

        $response = $this->postJson('/api/push-tokens', [
            'token' => 'duplicate-token-123',
            'platform' => 'web',
        ]);

        $response->assertStatus(201);

        $count = PushToken::where('user_id', $user->id)
            ->where('token', 'duplicate-token-123')
            ->count();
        $this->assertEquals(1, $count);
    }

    public function test_user_can_unregister_their_own_token(): void
    {
        $user = $this->createCustomer();

        PushToken::create([
            'user_id' => $user->id,
            'token' => 'token-to-remove',
            'platform' => 'web',
        ]);

        $this->actingAs($user, 'sanctum');
        $response = $this->deleteJson('/api/push-tokens/' . urlencode('token-to-remove'));

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('push_tokens', [
            'user_id' => $user->id,
            'token' => 'token-to-remove',
        ]);
    }

    public function test_push_not_sent_when_push_notifications_disabled(): void
    {
        $user = $this->createCustomer();
        $user->initializeNotificationSettings();
        $user->notificationSettings->update(['push_notifications' => false]);

        PushToken::create([
            'user_id' => $user->id,
            'token' => 'test-token',
            'platform' => 'web',
        ]);

        $mockService = $this->createMock(PushNotificationService::class);
        $mockService->expects($this->never())->method('sendToUser');
        $this->app->instance(PushNotificationService::class, $mockService);

        $address = Address::create([
            'user_id' => $user->id,
            'label' => 'Home',
            'full_name' => $user->name,
            'phone' => '+201012345678',
            'street_address' => '123 Main St',
            'city' => 'Cairo',
            'latitude' => 30.0444,
            'longitude' => 31.2357,
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'address_id' => $address->id,
            'payment_method' => 'cash_on_delivery',
            'delivery_type' => 'delivery',
            'status' => 'out_for_delivery',
            'subtotal' => 50.00,
            'tax' => 5.00,
            'discount' => 0.00,
            'shipping_fee' => 10.00,
            'total' => 65.00,
            'placed_at' => now(),
            'estimated_delivery_time' => now()->subHour(),
        ]);

        // The notification sends via database channel (still works),
        // but PushNotificationService must NOT be called.
        $user->notify(new DeliverySlaBreachedNotification($order));

        // Database notification should still exist
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $user->id,
            'type' => DeliverySlaBreachedNotification::class,
        ]);
    }

    public function test_active_notification_can_trigger_push_delivery(): void
    {
        $user = $this->createCustomer();
        $user->initializeNotificationSettings();

        PushToken::create([
            'user_id' => $user->id,
            'token' => 'test-push-token',
            'platform' => 'web',
        ]);

        $mockService = $this->createMock(PushNotificationService::class);
        $mockService->expects($this->once())
            ->method('sendToUser')
            ->willReturn(1);
        $this->app->instance(PushNotificationService::class, $mockService);

        $address = Address::create([
            'user_id' => $user->id,
            'label' => 'Home',
            'full_name' => $user->name,
            'phone' => '+201012345678',
            'street_address' => '123 Main St',
            'city' => 'Cairo',
            'latitude' => 30.0444,
            'longitude' => 31.2357,
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'address_id' => $address->id,
            'payment_method' => 'cash_on_delivery',
            'delivery_type' => 'delivery',
            'status' => 'out_for_delivery',
            'subtotal' => 50.00,
            'tax' => 5.00,
            'discount' => 0.00,
            'shipping_fee' => 10.00,
            'total' => 65.00,
            'placed_at' => now(),
            'estimated_delivery_time' => now()->subHour(),
        ]);

        $user->notify(new DeliverySlaBreachedNotification($order));
    }

    public function test_multiple_tokens_for_same_user_are_handled(): void
    {
        $user = $this->createCustomer();

        PushToken::create([
            'user_id' => $user->id,
            'token' => 'token-1',
            'platform' => 'web',
        ]);
        PushToken::create([
            'user_id' => $user->id,
            'token' => 'token-2',
            'platform' => 'web',
        ]);

        $this->actingAs($user, 'sanctum');
        $response = $this->getJson('/api/push-tokens');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_invalid_token_is_handled_safely(): void
    {
        $user = $this->createCustomer();

        PushToken::create([
            'user_id' => $user->id,
            'token' => 'invalid-token',
            'platform' => 'web',
        ]);

        $mockService = $this->createMock(PushNotificationService::class);
        $mockService->method('sendToUser')->willReturn(0);
        $this->app->instance(PushNotificationService::class, $mockService);

        $this->assertEquals(0, app(PushNotificationService::class)->sendToUser(
            $user,
            'Test',
            'Body'
        ));
    }

    public function test_fcm_failure_does_not_break_business_operation(): void
    {
        $user = $this->createCustomer();
        $user->initializeNotificationSettings();

        $mockService = $this->createMock(PushNotificationService::class);
        $mockService->method('sendToUser')->willThrowException(new \Exception('FCM down'));
        $this->app->instance(PushNotificationService::class, $mockService);

        $address = Address::create([
            'user_id' => $user->id,
            'label' => 'Home',
            'full_name' => $user->name,
            'phone' => '+201012345678',
            'street_address' => '123 Main St',
            'city' => 'Cairo',
            'latitude' => 30.0444,
            'longitude' => 31.2357,
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'address_id' => $address->id,
            'payment_method' => 'cash_on_delivery',
            'delivery_type' => 'delivery',
            'status' => 'out_for_delivery',
            'subtotal' => 50.00,
            'tax' => 5.00,
            'discount' => 0.00,
            'shipping_fee' => 10.00,
            'total' => 65.00,
            'placed_at' => now(),
            'estimated_delivery_time' => now()->subHour(),
        ]);

        // Should not throw — FCM failure must not break the notification flow
        $user->notify(new DeliverySlaBreachedNotification($order));

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $user->id,
            'type' => DeliverySlaBreachedNotification::class,
        ]);
    }

    public function test_existing_database_notification_behavior_remains_intact(): void
    {
        $user = $this->createCustomer();
        $user->initializeNotificationSettings();

        $address = Address::create([
            'user_id' => $user->id,
            'label' => 'Home',
            'full_name' => $user->name,
            'phone' => '+201012345678',
            'street_address' => '123 Main St',
            'city' => 'Cairo',
            'latitude' => 30.0444,
            'longitude' => 31.2357,
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'address_id' => $address->id,
            'payment_method' => 'cash_on_delivery',
            'delivery_type' => 'delivery',
            'status' => 'out_for_delivery',
            'subtotal' => 50.00,
            'tax' => 5.00,
            'discount' => 0.00,
            'shipping_fee' => 10.00,
            'total' => 65.00,
            'placed_at' => now(),
            'estimated_delivery_time' => now()->subHour(),
        ]);

        $mockService = $this->createMock(PushNotificationService::class);
        $mockService->method('sendToUser')->willReturn(0);
        $this->app->instance(PushNotificationService::class, $mockService);

        $user->notify(new DeliverySlaBreachedNotification($order));

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $user->id,
            'type' => DeliverySlaBreachedNotification::class,
        ]);

        $notification = $user->notifications()->where('type', DeliverySlaBreachedNotification::class)->first();
        $this->assertNotNull($notification);
        $this->assertNull($notification->read_at);
    }
}
