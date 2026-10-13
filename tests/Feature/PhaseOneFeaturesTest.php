<?php

namespace Tests\Feature;

use App\Actions\Api\AddSmartListToCartAction;
use App\Actions\Api\CancelOrderAction;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Meal;
use App\Models\Offer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\SmartList;
use App\Models\Subcategory;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PhaseOneFeaturesTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Item 1: Test OTP configuration (4 digits, 3 minutes expiry).
     */
    public function test_otp_configuration_matches_srs(): void
    {
        $this->assertSame(4, config('otp.length'));
        $this->assertSame(3, config('otp.expiry_minutes'));

        $otpService = app(OtpService::class);
        $user = User::first() ?? User::factory()->create();

        $otpCode = $otpService->generate($user->email, 'verification');

        $this->assertSame(4, strlen($otpCode));
        $this->assertTrue(ctype_digit($otpCode));

        $otpRecord = \App\Models\Otp::where('identifier', $user->email)->where('type', 'verification')->latest()->first();
        $this->assertNotNull($otpRecord);
        $expectedExpiry = now()->addMinutes(3);
        $this->assertEqualsWithDelta($expectedExpiry->timestamp, $otpRecord->expires_at->timestamp, 5);
    }

    /**
     * Item 2: Test password minimum 12 characters.
     */
    public function test_registration_requires_minimum_12_character_password(): void
    {
        // 8 characters should fail validation
        $responseShort = $this->postJson('/api/auth/register', [
            'username' => 'shortpassuser',
            'email' => 'shortpass_' . time() . '@example.com',
            'password' => 'Pass1234',
            'password_confirmation' => 'Pass1234',
        ]);

        $responseShort->assertStatus(400)
            ->assertJsonValidationErrors(['password']);

        // 12+ characters should pass password validation rule
        $responseValid = $this->postJson('/api/auth/register', [
            'username' => 'validpassuser' . rand(100, 999),
            'email' => 'validpass_' . time() . rand(100, 999) . '@example.com',
            'password' => 'SecurePass1234!',
            'password_confirmation' => 'SecurePass1234!',
        ]);

        $this->assertContains($responseValid->status(), [200, 201]);
    }

    /**
     * Item 3: Test Arabic localization via Accept-Language header.
     */
    public function test_arabic_localization_via_accept_language_header(): void
    {
        $category = Category::create([
            'name' => 'English Category ' . time(),
            'name_ar' => 'تصنيف تجريبي بالعربية',
            'slug' => 'test-cat-' . time(),
        ]);

        // Request with English header
        $responseEn = $this->withHeaders(['Accept-Language' => 'en'])
            ->getJson("/api/categories/{$category->id}");

        $responseEn->assertStatus(200);
        $this->assertSame($category->name, $responseEn->json('data.name'));

        // Request with Arabic header
        $responseAr = $this->withHeaders(['Accept-Language' => 'ar'])
            ->getJson("/api/categories/{$category->id}");

        $responseAr->assertStatus(200);
        $this->assertSame('تصنيف تجريبي بالعربية', $responseAr->json('data.name'));
    }

    /**
     * Item 4: Test Cart coupon apply, persist, totals recalculation, and removal.
     */
    public function test_cart_coupon_apply_recalculate_and_remove(): void
    {
        $user = User::first() ?? User::factory()->create();
        $meal = Meal::where('is_available', true)->where('stock_quantity', '>', 5)->first();

        // Create active coupon
        $couponCode = 'PHASE1TEST' . rand(100, 999);
        $offer = Offer::create([
            'code' => $couponCode,
            'title' => 'Phase 1 Test Offer',
            'type' => 'percentage',
            'discount_value' => 10, // 10%
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
            'is_active' => true,
        ]);

        // Set up cart with 1 item
        $cart = $user->getOrCreateCart();
        $cart->items()->delete();
        $cart->coupon_code = null;
        $cart->coupon_discount = 0;
        $cart->save();

        $cart->items()->create([
            'meal_id' => $meal->id,
            'quantity' => 2,
            'unit_price' => 10.00,
            'discount_amount' => 0,
            'subtotal' => 20.00,
        ]);
        $cart->calculateTotals();

        $this->actingAs($user, 'sanctum');

        // Apply coupon
        $applyResponse = $this->postJson('/api/cart/coupon', [
            'code' => $couponCode,
        ]);

        $applyResponse->assertStatus(200)
            ->assertJsonPath('data.coupon_code', $couponCode);

        $cart->refresh();
        $this->assertSame($couponCode, $cart->coupon_code);
        $this->assertGreaterThan(0, (float) $cart->coupon_discount);

        // Remove coupon
        $removeResponse = $this->deleteJson('/api/cart/coupon');
        $removeResponse->assertStatus(200)
            ->assertJsonPath('data.coupon_code', null)
            ->assertJsonPath('data.coupon_discount', 0);

        $cart->refresh();
        $this->assertNull($cart->coupon_code);
        $this->assertEquals(0, (float) $cart->coupon_discount);
    }

    /**
     * Item 5: Test order cancellation and stock restoration.
     */
    public function test_order_cancellation_restores_stock_and_marks_cancelled(): void
    {
        $user = User::first() ?? User::factory()->create();
        $otherUser = User::where('id', '!=', $user->id)->first();
        if (!$otherUser) {
            $otherUser = User::create([
                'name' => 'Other User',
                'email' => 'other_' . time() . '@example.com',
                'password' => bcrypt('Password12345!'),
            ]);
        }

        $meal = Meal::where('is_available', true)->where('stock_quantity', '>', 5)->first();
        $initialStock = $meal->stock_quantity;

        // Create order
        $order = Order::create([
            'user_id' => $user->id,
            'payment_method' => 'cash_on_delivery',
            'delivery_type' => 'pickup',
            'status' => 'placed',
            'subtotal' => 20.00,
            'tax' => 2.00,
            'discount' => 0.00,
            'shipping_fee' => 0.00,
            'total' => 22.00,
            'placed_at' => now(),
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'meal_id' => $meal->id,
            'quantity' => 2,
            'unit_price' => 10.00,
            'discount_amount' => 0,
            'subtotal' => 20.00,
        ]);

        $meal->decrement('stock_quantity', 2);
        $meal->refresh();
        $this->assertSame($initialStock - 2, $meal->stock_quantity);

        // Test other user cannot cancel
        $this->actingAs($otherUser, 'sanctum');
        $forbiddenResponse = $this->postJson("/api/orders/{$order->id}/cancel", [
            'reason' => 'Unauthorized cancellation',
        ]);
        $forbiddenResponse->assertStatus(403);

        // Owner cancels
        $this->actingAs($user, 'sanctum');
        $cancelResponse = $this->postJson("/api/orders/{$order->id}/cancel", [
            'reason' => 'Customer requested cancellation',
        ]);

        $cancelResponse->assertStatus(200)
            ->assertJsonPath('data.status', 'cancelled');

        $order->refresh();
        $meal->refresh();

        $this->assertSame('cancelled', $order->status);
        $this->assertNotNull($order->cancelled_at);
        $this->assertSame($initialStock, $meal->stock_quantity);

        // Cannot cancel already cancelled order
        $secondCancel = $this->postJson("/api/orders/{$order->id}/cancel");
        $secondCancel->assertStatus(400);
    }

    /**
     * Item 6: Test Smart List Add All to Cart.
     */
    public function test_smart_list_add_all_to_cart(): void
    {
        $user = User::first() ?? User::factory()->create();
        $otherUser = User::where('id', '!=', $user->id)->first();
        if (!$otherUser) {
            $otherUser = User::create([
                'name' => 'Other User 2',
                'email' => 'other2_' . time() . '@example.com',
                'password' => bcrypt('Password12345!'),
            ]);
        }

        $availableMeal = Meal::where('is_available', true)->where('stock_quantity', '>', 5)->first();
        $unavailableMeal = Meal::where('id', '!=', $availableMeal->id)->first();
        $originalAvailability = $unavailableMeal->is_available;
        $unavailableMeal->update(['is_available' => false]);

        $smartList = SmartList::create([
            'name' => 'Test List ' . time(),
            'category' => 'weekly',
            'user_id' => $user->id,
        ]);

        $smartList->meals()->attach([$availableMeal->id, $unavailableMeal->id]);

        $cart = $user->getOrCreateCart();
        $cart->items()->delete();
        $cart->calculateTotals();

        // Other user cannot add someone else's smart list
        $this->actingAs($otherUser, 'sanctum');
        $forbiddenResponse = $this->postJson("/api/smart-lists/{$smartList->id}/add-to-cart");
        $forbiddenResponse->assertStatus(403);

        // Owner adds to cart
        $this->actingAs($user, 'sanctum');
        $addResponse = $this->postJson("/api/smart-lists/{$smartList->id}/add-to-cart");

        $addResponse->assertStatus(200)
            ->assertJsonPath('data.added_count', 1)
            ->assertJsonPath('data.skipped_count', 1);

        $cart->refresh();
        $cartItem = $cart->items()->where('meal_id', $availableMeal->id)->first();
        $this->assertNotNull($cartItem);
        $this->assertSame(1, $cartItem->quantity);

        // Add second time -> increments to 2
        $secondResponse = $this->postJson("/api/smart-lists/{$smartList->id}/add-to-cart");
        $secondResponse->assertStatus(200);

        $cartItem->refresh();
        $this->assertSame(2, $cartItem->quantity);

        // Restore original availability
        $unavailableMeal->update(['is_available' => $originalAvailability]);
    }
}
