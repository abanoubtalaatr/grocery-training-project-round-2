<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Faq;
use App\Models\Meal;
use App\Models\Order;
use App\Models\Subcategory;
use App\Models\User;
use Tests\TestCase;

class AdminDashboardRoutesTest extends TestCase
{
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::where('is_admin', true)->first() ?? User::factory()->create([
            'is_admin' => true,
            'is_active' => true,
        ]);
    }

    public function test_admin_can_access_all_dashboard_get_routes()
    {
        $this->actingAs($this->admin, 'web');

        $category = Category::first() ?? Category::factory()->create();
        $subcategory = Subcategory::first() ?? Subcategory::factory()->create(['category_id' => $category->id]);
        $meal = Meal::first() ?? Meal::factory()->create(['category_id' => $category->id, 'subcategory_id' => $subcategory->id]);
        $customer = User::where('is_admin', false)->first() ?? User::factory()->create();
        $order = Order::first() ?? Order::factory()->create(['user_id' => $customer->id]);
        $faq = Faq::first() ?? Faq::factory()->create();
        $supportMessage = \App\Models\ContactMessage::first() ?? \App\Models\ContactMessage::create([
            'name' => 'John', 'email' => 'john@example.com', 'subject' => 'Help', 'message' => 'Need help'
        ]);

        $routes = [
            'admin.dashboard' => route('admin.dashboard'),
            'admin.categories.index' => route('admin.categories.index'),
            'admin.categories.create' => route('admin.categories.create'),
            'admin.categories.show' => route('admin.categories.show', $category->id),
            'admin.categories.edit' => route('admin.categories.edit', $category->id),
            'admin.subcategories.index' => route('admin.subcategories.index'),
            'admin.subcategories.create' => route('admin.subcategories.create'),
            'admin.subcategories.show' => route('admin.subcategories.show', $subcategory->id),
            'admin.subcategories.edit' => route('admin.subcategories.edit', $subcategory->id),
            'admin.meals.index' => route('admin.meals.index'),
            'admin.meals.create' => route('admin.meals.create'),
            'admin.meals.show' => route('admin.meals.show', $meal->id),
            'admin.meals.edit' => route('admin.meals.edit', $meal->id),
            'admin.orders.index' => route('admin.orders.index'),
            'admin.orders.show' => route('admin.orders.show', $order->id),
            'admin.users.index' => route('admin.users.index'),
            'admin.users.show' => route('admin.users.show', $customer->id),
            'admin.faqs.index' => route('admin.faqs.index'),
            'admin.faqs.create' => route('admin.faqs.create'),
            'admin.faqs.show' => route('admin.faqs.show', $faq->id),
            'admin.faqs.edit' => route('admin.faqs.edit', $faq->id),
            'admin.support.index' => route('admin.support.index'),
            'admin.support.show' => route('admin.support.show', $supportMessage->id),
            'admin.notifications.index' => route('admin.notifications.index'),
            'admin.notifications.create' => route('admin.notifications.create'),
            'admin.analytics' => route('admin.analytics'),
            'admin.monitoring' => route('admin.monitoring'),
            'admin.settings.index' => route('admin.settings.index'),
        ];

        foreach ($routes as $name => $url) {
            $response = $this->get($url);
            $this->assertEquals(200, $response->getStatusCode(), "Failed route name: {$name} at URL: {$url}");
        }
    }
}
