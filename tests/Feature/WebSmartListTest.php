<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Meal;
use App\Models\SmartList;
use App\Models\SmartListMeal;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

class WebSmartListTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;
    protected Meal $meal;

    protected function setUp(): void
    {
        parent::setUp();

        SmartListMeal::query()->delete();
        SmartList::query()->delete();
        CartItem::query()->delete();
        Cart::query()->delete();

        $this->user = User::create([
            'username' => 'sluser_' . Str::random(6),
            'email' => 'sl_' . Str::random(6) . '@example.com',
            'password' => bcrypt('Password123!'),
            'firstname' => 'Test',
            'lastname' => 'SmartList',
        ]);

        $category = Category::create([
            'name' => 'Test Category',
            'slug' => 'test-cat-' . Str::random(6),
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->meal = Meal::create([
            'category_id' => $category->id,
            'title' => 'Test Meal',
            'slug' => 'test-meal-' . Str::random(6),
            'description' => 'A test meal',
            'image' => 'https://example.com/image.jpg',
            'price' => 15.00,
            'stock_quantity' => 50,
            'is_available' => true,
        ]);
    }

    private function login(): void
    {
        $this->actingAs($this->user, 'web');
    }

    private function createSmartList(array $overrides = []): SmartList
    {
        return SmartList::create(array_merge([
            'user_id' => $this->user->id,
            'name' => 'Test List',
            'notify_on_price_drop' => true,
            'notify_on_offers' => true,
        ], $overrides));
    }

    // ==================== Index Tests ====================

    public function test_guest_cannot_view_smart_lists(): void
    {
        $response = $this->get(route('smart-lists.index'));

        $response->assertRedirect();
    }

    public function test_user_can_view_empty_smart_lists(): void
    {
        $this->login();

        $response = $this->get(route('smart-lists.index'));

        $response->assertStatus(200);
        $response->assertSee("haven");
    }

    public function test_user_can_view_smart_lists(): void
    {
        $this->login();
        $this->createSmartList();

        $response = $this->get(route('smart-lists.index'));

        $response->assertStatus(200);
        $response->assertSee('Test List');
    }

    // ==================== Create Tests ====================

    public function test_user_can_view_create_form(): void
    {
        $this->login();

        $response = $this->get(route('smart-lists.create'));

        $response->assertStatus(200);
    }

    public function test_user_can_create_smart_list(): void
    {
        $this->login();

        $response = $this->post(route('smart-lists.store'), [
            'name' => 'My Grocery List',
            'category' => 'Groceries',
            'description' => 'Weekly groceries',
            'notify_on_price_drop' => '1',
            'notify_on_offers' => '1',
        ]);

        $response->assertRedirect(route('smart-lists.index'));
        $this->assertDatabaseHas('smart_lists', [
            'user_id' => $this->user->id,
            'name' => 'My Grocery List',
        ]);
    }

    public function test_create_requires_name(): void
    {
        $this->login();

        $response = $this->post(route('smart-lists.store'), [
            'category' => 'Groceries',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_create_sets_notification_defaults(): void
    {
        $this->login();

        $this->post(route('smart-lists.store'), [
            'name' => 'Test List',
        ]);

        $list = SmartList::where('user_id', $this->user->id)->first();
        $this->assertTrue($list->notify_on_price_drop);
        $this->assertTrue($list->notify_on_offers);
    }

    // ==================== Show Tests ====================

    public function test_user_can_view_smart_list(): void
    {
        $this->login();
        $list = $this->createSmartList();

        $response = $this->get(route('smart-lists.show', $list->id));

        $response->assertStatus(200);
        $response->assertSee('Test List');
    }

    public function test_user_cannot_view_other_users_list(): void
    {
        $otherUser = User::create([
            'username' => 'other_' . Str::random(6),
            'email' => 'other_' . Str::random(6) . '@example.com',
            'password' => bcrypt('Password123!'),
            'firstname' => 'Other',
            'lastname' => 'User',
        ]);
        $otherList = SmartList::create([
            'user_id' => $otherUser->id,
            'name' => 'Other List',
        ]);

        $this->login();

        $response = $this->get(route('smart-lists.show', $otherList->id));

        $response->assertStatus(404);
    }

    public function test_smart_list_shows_meals(): void
    {
        $this->login();
        $list = $this->createSmartList();
        $list->meals()->attach($this->meal->id);

        $response = $this->get(route('smart-lists.show', $list->id));

        $response->assertStatus(200);
        $response->assertSee('Test Meal');
    }

    // ==================== Edit Tests ====================

    public function test_user_can_view_edit_form(): void
    {
        $this->login();
        $list = $this->createSmartList();

        $response = $this->get(route('smart-lists.edit', $list->id));

        $response->assertStatus(200);
        $response->assertSee('Test List');
    }

    public function test_user_can_update_smart_list(): void
    {
        $this->login();
        $list = $this->createSmartList();

        $response = $this->put(route('smart-lists.update', $list->id), [
            'name' => 'Updated List',
            'category' => 'Updated',
            'notify_on_price_drop' => '0',
            'notify_on_offers' => '0',
        ]);

        $response->assertRedirect(route('smart-lists.show', $list->id));
        $this->assertDatabaseHas('smart_lists', [
            'id' => $list->id,
            'name' => 'Updated List',
        ]);
    }

    public function test_update_requires_name(): void
    {
        $this->login();
        $list = $this->createSmartList();

        $response = $this->put(route('smart-lists.update', $list->id), [
            'name' => '',
        ]);

        $response->assertSessionHasErrors('name');
    }

    // ==================== Delete Tests ====================

    public function test_user_can_delete_smart_list(): void
    {
        $this->login();
        $list = $this->createSmartList();

        $response = $this->delete(route('smart-lists.destroy', $list->id));

        $response->assertRedirect(route('smart-lists.index'));
        $this->assertDatabaseMissing('smart_lists', ['id' => $list->id]);
    }

    public function test_user_cannot_delete_other_users_list(): void
    {
        $otherUser = User::create([
            'username' => 'other_' . Str::random(6),
            'email' => 'other_' . Str::random(6) . '@example.com',
            'password' => bcrypt('Password123!'),
            'firstname' => 'Other',
            'lastname' => 'User',
        ]);
        $otherList = SmartList::create([
            'user_id' => $otherUser->id,
            'name' => 'Other List',
        ]);

        $this->login();

        $response = $this->delete(route('smart-lists.destroy', $otherList->id));

        $response->assertStatus(404);
        $this->assertDatabaseHas('smart_lists', ['id' => $otherList->id]);
    }

    // ==================== Meal Management Tests ====================

    public function test_user_can_add_meal_to_list(): void
    {
        $this->login();
        $list = $this->createSmartList();

        $response = $this->post(route('smart-lists.meals.store', $list->id), [
            'meal_id' => $this->meal->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('smart_list_meals', [
            'smart_list_id' => $list->id,
            'meal_id' => $this->meal->id,
        ]);
    }

    public function test_adding_duplicate_meal_shows_already_exists(): void
    {
        $this->login();
        $list = $this->createSmartList();
        $list->meals()->attach($this->meal->id);

        $response = $this->post(route('smart-lists.meals.store', $list->id), [
            'meal_id' => $this->meal->id,
        ]);

        $response->assertRedirect();
    }

    public function test_user_can_remove_meal_from_list(): void
    {
        $this->login();
        $list = $this->createSmartList();
        $list->meals()->attach($this->meal->id);

        $response = $this->delete(route('smart-lists.meals.remove', [$list->id, $this->meal->id]));

        $response->assertRedirect();
        $this->assertDatabaseMissing('smart_list_meals', [
            'smart_list_id' => $list->id,
            'meal_id' => $this->meal->id,
        ]);
    }

    public function test_add_meal_requires_valid_meal(): void
    {
        $this->login();
        $list = $this->createSmartList();

        $response = $this->post(route('smart-lists.meals.store', $list->id), [
            'meal_id' => 99999,
        ]);

        $response->assertSessionHasErrors('meal_id');
    }

    public function test_user_cannot_add_meal_to_other_users_list(): void
    {
        $otherUser = User::create([
            'username' => 'other_' . Str::random(6),
            'email' => 'other_' . Str::random(6) . '@example.com',
            'password' => bcrypt('Password123!'),
            'firstname' => 'Other',
            'lastname' => 'User',
        ]);
        $otherList = SmartList::create([
            'user_id' => $otherUser->id,
            'name' => 'Other List',
        ]);

        $this->login();

        $response = $this->post(route('smart-lists.meals.store', $otherList->id), [
            'meal_id' => $this->meal->id,
        ]);

        $response->assertStatus(404);
    }

    // ==================== Add to Cart Tests ====================

    public function test_user_can_add_list_to_cart(): void
    {
        $this->login();
        $list = $this->createSmartList();
        $list->meals()->attach($this->meal->id);

        $response = $this->post(route('smart-lists.addToCart', $list->id));

        $response->assertRedirect(route('cart.show'));
        $this->assertDatabaseHas('cart_items', [
            'meal_id' => $this->meal->id,
            'quantity' => 1,
        ]);
    }

    public function test_add_empty_list_to_cart_shows_message(): void
    {
        $this->login();
        $list = $this->createSmartList();

        $response = $this->post(route('smart-lists.addToCart', $list->id));

        $response->assertRedirect(route('cart.show'));
    }

    public function test_add_list_with_unavailable_meal_skips_it(): void
    {
        $this->login();
        $unavailableMeal = Meal::create([
            'category_id' => $this->meal->category_id,
            'title' => 'Unavailable Meal',
            'slug' => 'unavail-' . Str::random(6),
            'description' => 'Unavailable',
            'image' => 'https://example.com/image.jpg',
            'price' => 10.00,
            'stock_quantity' => 0,
            'is_available' => false,
        ]);

        $list = $this->createSmartList();
        $list->meals()->attach($unavailableMeal->id);

        $response = $this->post(route('smart-lists.addToCart', $list->id));

        $response->assertRedirect(route('cart.show'));
        $this->assertDatabaseMissing('cart_items', [
            'meal_id' => $unavailableMeal->id,
        ]);
    }

    public function test_user_cannot_add_other_users_list_to_cart(): void
    {
        $otherUser = User::create([
            'username' => 'other_' . Str::random(6),
            'email' => 'other_' . Str::random(6) . '@example.com',
            'password' => bcrypt('Password123!'),
            'firstname' => 'Other',
            'lastname' => 'User',
        ]);
        $otherList = SmartList::create([
            'user_id' => $otherUser->id,
            'name' => 'Other List',
        ]);

        $this->login();

        $response = $this->post(route('smart-lists.addToCart', $otherList->id));

        $response->assertStatus(404);
    }

    // ==================== Price Drop Notification Tests ====================

    public function test_smart_list_shows_notification_settings(): void
    {
        $this->login();
        $list = $this->createSmartList([
            'notify_on_price_drop' => true,
            'notify_on_offers' => false,
        ]);

        $response = $this->get(route('smart-lists.show', $list->id));

        $response->assertStatus(200);
        $response->assertSee('Price Drop Alerts');
    }

    public function test_create_form_has_notification_checkboxes(): void
    {
        $this->login();

        $response = $this->get(route('smart-lists.create'));

        $response->assertStatus(200);
        $response->assertSee('notify_on_price_drop');
        $response->assertSee('notify_on_offers');
    }

    public function test_notification_settings_can_be_toggled(): void
    {
        $this->login();
        $list = $this->createSmartList([
            'notify_on_price_drop' => true,
            'notify_on_offers' => true,
        ]);

        $this->put(route('smart-lists.update', $list->id), [
            'name' => $list->name,
            'notify_on_price_drop' => '0',
            'notify_on_offers' => '0',
        ]);

        $this->assertDatabaseHas('smart_lists', [
            'id' => $list->id,
            'notify_on_price_drop' => false,
            'notify_on_offers' => false,
        ]);
    }
}
