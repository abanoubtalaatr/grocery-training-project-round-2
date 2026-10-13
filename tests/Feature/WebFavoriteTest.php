<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Favorite;
use App\Models\Meal;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

class WebFavoriteTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;
    protected Meal $meal;

    protected function setUp(): void
    {
        parent::setUp();

        Favorite::query()->delete();

        $this->user = User::create([
            'username' => 'favuser_' . Str::random(6),
            'email' => 'fav_' . Str::random(6) . '@example.com',
            'password' => bcrypt('Password123!'),
            'firstname' => 'Test',
            'lastname' => 'Fav',
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

    public function test_guest_cannot_view_favorites(): void
    {
        $response = $this->get(route('favorites.index'));

        $response->assertRedirect();
    }

    public function test_user_can_view_empty_favorites(): void
    {
        $this->login();

        $response = $this->get(route('favorites.index'));

        $response->assertStatus(200);
        $response->assertSee("haven");
    }

    public function test_user_can_view_favorites_with_items(): void
    {
        $this->login();
        $this->user->favorites()->create(['meal_id' => $this->meal->id]);

        $response = $this->get(route('favorites.index'));

        $response->assertStatus(200);
        $response->assertSee('Test Meal');
    }

    public function test_user_can_toggle_favorite_add(): void
    {
        $this->login();

        $response = $this->post(route('favorites.toggle', $this->meal->id));

        $response->assertRedirect();
        $this->assertDatabaseHas('favorites', [
            'user_id' => $this->user->id,
            'meal_id' => $this->meal->id,
        ]);
    }

    public function test_user_can_toggle_favorite_remove(): void
    {
        $this->login();
        $this->user->favorites()->create(['meal_id' => $this->meal->id]);

        $response = $this->post(route('favorites.toggle', $this->meal->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('favorites', [
            'user_id' => $this->user->id,
            'meal_id' => $this->meal->id,
        ]);
    }

    public function test_user_can_remove_favorite(): void
    {
        $this->login();
        $this->user->favorites()->create(['meal_id' => $this->meal->id]);

        $response = $this->delete(route('favorites.remove', $this->meal->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('favorites', [
            'user_id' => $this->user->id,
            'meal_id' => $this->meal->id,
        ]);
    }

    public function test_remove_nonexistent_favorite_shows_error(): void
    {
        $this->login();

        $response = $this->delete(route('favorites.remove', $this->meal->id));

        $response->assertRedirect();
    }

    public function test_favorites_are_isolated_between_users(): void
    {
        $this->login();
        $this->user->favorites()->create(['meal_id' => $this->meal->id]);

        $otherUser = User::create([
            'username' => 'other_' . Str::random(6),
            'email' => 'other_' . Str::random(6) . '@example.com',
            'password' => bcrypt('Password123!'),
            'firstname' => 'Other',
            'lastname' => 'User',
        ]);
        $this->actingAs($otherUser, 'web');

        $response = $this->get(route('favorites.index'));

        $response->assertStatus(200);
        $response->assertDontSee('Test Meal');
    }

    public function test_toggle_does_not_affect_other_users(): void
    {
        $this->login();
        $this->user->favorites()->create(['meal_id' => $this->meal->id]);

        $otherUser = User::create([
            'username' => 'other_' . Str::random(6),
            'email' => 'other_' . Str::random(6) . '@example.com',
            'password' => bcrypt('Password123!'),
            'firstname' => 'Other',
            'lastname' => 'User',
        ]);
        $this->actingAs($otherUser, 'web');

        $this->post(route('favorites.toggle', $this->meal->id));

        $this->assertDatabaseHas('favorites', [
            'user_id' => $this->user->id,
            'meal_id' => $this->meal->id,
        ]);
    }

    public function test_duplicate_favorite_handled_correctly(): void
    {
        $this->login();
        $this->user->favorites()->create(['meal_id' => $this->meal->id]);

        $response = $this->post(route('favorites.toggle', $this->meal->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('favorites', [
            'user_id' => $this->user->id,
            'meal_id' => $this->meal->id,
        ]);
    }
}
