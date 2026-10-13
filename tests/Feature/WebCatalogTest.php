<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Meal;
use App\Models\Subcategory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class WebCatalogTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Meal::withoutEvents(function () {
            Meal::withTrashed()->forceDelete();
        });
        Subcategory::query()->delete();
        Category::withTrashed()->forceDelete();
    }

    private function createCategory(array $overrides = []): Category
    {
        return Category::create(array_merge([
            'name' => 'Test Category ' . uniqid(),
            'slug' => 'test-cat-' . Str::random(8),
            'is_active' => true,
            'sort_order' => 1,
        ], $overrides));
    }

    private function createMeal(Category $category, array $overrides = []): Meal
    {
        return Meal::create(array_merge([
            'category_id' => $category->id,
            'title' => 'Test Meal ' . Str::random(4),
            'slug' => 'test-meal-' . Str::random(8),
            'description' => 'A test meal description',
            'image' => 'https://example.com/image.jpg',
            'price' => 10.00,
            'is_available' => true,
        ], $overrides));
    }

    public function test_guest_can_view_categories_page(): void
    {
        $category = $this->createCategory([
            'name' => 'Fresh Vegetables',
            'slug' => 'fresh-vegetables',
        ]);

        $response = $this->get(route('catalog.categories'));

        $response->assertStatus(200);
        $response->assertSee('Fresh Vegetables');
    }

    public function test_guest_can_view_category_detail_page(): void
    {
        $category = $this->createCategory([
            'name' => 'Fruits',
            'slug' => 'fruits',
            'description' => 'Fresh fruits',
        ]);

        $this->createMeal($category, ['title' => 'Apple', 'slug' => 'apple']);

        $response = $this->get(route('catalog.category', 'fruits'));

        $response->assertStatus(200);
        $response->assertSee('Fruits');
        $response->assertSee('Apple');
    }

    public function test_inactive_category_returns_404(): void
    {
        $category = $this->createCategory([
            'name' => 'Hidden Category',
            'slug' => 'hidden-category',
            'is_active' => false,
        ]);

        $response = $this->get(route('catalog.category', 'hidden-category'));

        $response->assertStatus(404);
    }

    public function test_guest_can_view_subcategory_detail_page(): void
    {
        $category = $this->createCategory(['name' => 'Dairy', 'slug' => 'dairy']);

        $subcategory = Subcategory::create([
            'category_id' => $category->id,
            'name' => 'Cheese',
            'slug' => 'cheese',
            'is_active' => true,
        ]);

        $this->createMeal($category, [
            'subcategory_id' => $subcategory->id,
            'title' => 'Cheddar',
            'slug' => 'cheddar',
        ]);

        $response = $this->get(route('catalog.subcategory', 'cheese'));

        $response->assertStatus(200);
        $response->assertSee('Cheese');
        $response->assertSee('Cheddar');
    }

    public function test_inactive_subcategory_returns_404(): void
    {
        $category = $this->createCategory(['name' => 'Dairy2', 'slug' => 'dairy2']);

        Subcategory::create([
            'category_id' => $category->id,
            'name' => 'Hidden',
            'slug' => 'hidden-sub',
            'is_active' => false,
        ]);

        $response = $this->get(route('catalog.subcategory', 'hidden-sub'));

        $response->assertStatus(404);
    }

    public function test_guest_can_view_meals_listing_page(): void
    {
        $category = $this->createCategory(['name' => 'Bakery', 'slug' => 'bakery']);

        $this->createMeal($category, ['title' => 'Sourdough Bread', 'slug' => 'sourdough-bread']);

        $response = $this->get(route('catalog.meals'));

        $response->assertStatus(200);
        $response->assertSee('Sourdough Bread');
    }

    public function test_meals_page_filters_by_category(): void
    {
        $catA = $this->createCategory(['name' => 'Cat A', 'slug' => 'cat-a']);
        $catB = $this->createCategory(['name' => 'Cat B', 'slug' => 'cat-b']);

        $this->createMeal($catA, ['title' => 'Meal A', 'slug' => 'meal-a']);
        $this->createMeal($catB, ['title' => 'Meal B', 'slug' => 'meal-b']);

        $response = $this->get(route('catalog.meals', ['category_id' => $catA->id]));

        $response->assertStatus(200);
        $response->assertSee('Meal A');
        $response->assertDontSee('Meal B');
    }

    public function test_meals_page_sorts_by_price_asc(): void
    {
        $category = $this->createCategory(['name' => 'SortTest', 'slug' => 'sort-test']);

        $this->createMeal($category, ['title' => 'Expensive', 'slug' => 'expensive', 'price' => 50]);
        $this->createMeal($category, ['title' => 'Cheap', 'slug' => 'cheap', 'price' => 5]);

        $response = $this->get(route('catalog.meals', ['sort_by' => 'price', 'sort_order' => 'asc']));

        $response->assertStatus(200);

        $content = $response->getContent();
        $cheapPos = strpos($content, 'Cheap');
        $expensivePos = strpos($content, 'Expensive');

        $this->assertNotFalse($cheapPos);
        $this->assertNotFalse($expensivePos);
        $this->assertLessThan($expensivePos, $cheapPos);
    }

    public function test_meals_page_filters_by_search(): void
    {
        $category = $this->createCategory(['name' => 'SearchCat', 'slug' => 'search-cat']);

        $this->createMeal($category, ['title' => 'Organic Apple', 'slug' => 'organic-apple']);
        $this->createMeal($category, ['title' => 'Banana', 'slug' => 'banana']);

        $response = $this->get(route('catalog.meals', ['search' => 'Apple']));

        $response->assertStatus(200);
        $response->assertSee('Organic Apple');
        $response->assertDontSee('Banana');
    }

    public function test_guest_can_view_meal_detail_page(): void
    {
        $category = $this->createCategory(['name' => 'DetailCat', 'slug' => 'detail-cat']);

        $this->createMeal($category, [
            'title' => 'Test Meal',
            'slug' => 'test-meal',
            'description' => 'A test meal',
            'price' => 9.99,
        ]);

        $response = $this->get(route('catalog.meal', 'test-meal'));

        $response->assertStatus(200);
        $response->assertSee('Test Meal');
        $response->assertSee('9.99');
    }

    public function test_unavailable_meal_returns_404(): void
    {
        $category = $this->createCategory(['name' => 'UnavailCat', 'slug' => 'unavail-cat']);

        $this->createMeal($category, [
            'title' => 'Hidden Meal',
            'slug' => 'hidden-meal',
            'is_available' => false,
        ]);

        $response = $this->get(route('catalog.meal', 'hidden-meal'));

        $response->assertStatus(404);
    }

    public function test_guest_can_view_search_page(): void
    {
        $response = $this->get(route('catalog.search'));

        $response->assertStatus(200);
    }

    public function test_search_returns_matching_products(): void
    {
        $category = $this->createCategory(['name' => 'SearchRes', 'slug' => 'search-res']);

        $this->createMeal($category, ['title' => 'Organic Banana', 'slug' => 'organic-banana']);
        $this->createMeal($category, ['title' => 'Red Apple', 'slug' => 'red-apple']);

        $response = $this->get(route('catalog.search', ['search' => 'Banana']));

        $response->assertStatus(200);
        $response->assertSee('Organic Banana');
        $response->assertDontSee('Red Apple');
    }

    public function test_guest_can_view_recommendations_page(): void
    {
        $response = $this->get(route('catalog.recommendations'));

        $response->assertStatus(200);
    }

    public function test_guest_can_view_today_deals_page(): void
    {
        $response = $this->get(route('catalog.today-deals'));

        $response->assertStatus(200);
    }

    public function test_today_deals_shows_discounted_meals(): void
    {
        $category = $this->createCategory(['name' => 'DealsCat', 'slug' => 'deals-cat']);

        $this->createMeal($category, [
            'title' => 'Discounted Item',
            'slug' => 'discounted-item',
            'price' => 10,
            'discount_price' => 7.50,
        ]);

        $this->createMeal($category, [
            'title' => 'Regular Item',
            'slug' => 'regular-item',
            'price' => 10,
        ]);

        $response = $this->get(route('catalog.today-deals'));

        $response->assertStatus(200);
        $response->assertSee('Discounted Item');
    }

    public function test_only_available_meals_are_shown(): void
    {
        $category = $this->createCategory(['name' => 'AvailCat', 'slug' => 'avail-cat']);

        $this->createMeal($category, ['title' => 'Available', 'slug' => 'available-item']);
        $this->createMeal($category, ['title' => 'Unavailable', 'slug' => 'unavailable-item', 'is_available' => false]);

        $response = $this->get(route('catalog.meals'));

        $response->assertStatus(200);
        $response->assertSee('Available');
        $response->assertDontSee('Unavailable');
    }

    public function test_inactive_categories_not_shown_in_listing(): void
    {
        $this->createCategory(['name' => 'Active', 'slug' => 'active-cat']);
        $this->createCategory(['name' => 'Inactive', 'slug' => 'inactive-cat', 'is_active' => false]);

        $response = $this->get(route('catalog.categories'));

        $response->assertStatus(200);
        $response->assertSee('Active');
        $response->assertDontSee('Inactive');
    }

    public function test_breadcrumbs_shown_on_category_page(): void
    {
        $category = $this->createCategory(['name' => 'Bread', 'slug' => 'bread']);

        $this->createMeal($category, ['title' => 'Loaf', 'slug' => 'loaf']);

        $response = $this->get(route('catalog.category', 'bread'));

        $response->assertStatus(200);
        $response->assertSeeText(trans('common.categories'));
    }

    public function test_meal_detail_shows_category_link(): void
    {
        $category = $this->createCategory(['name' => 'Drinks', 'slug' => 'drinks']);

        $this->createMeal($category, ['title' => 'Cola', 'slug' => 'cola']);

        $response = $this->get(route('catalog.meal', 'cola'));

        $response->assertStatus(200);
        $response->assertSee('Drinks');
    }

    public function test_meals_page_respects_pagination(): void
    {
        $category = $this->createCategory(['name' => 'Paged', 'slug' => 'paged']);

        for ($i = 1; $i <= 20; $i++) {
            $this->createMeal($category, [
                'title' => "Meal {$i}",
                'slug' => "meal-{$i}-" . Str::random(4),
                'price' => $i,
            ]);
        }

        $response = $this->get(route('catalog.meals'));

        $response->assertStatus(200);
        $response->assertSee('Meal 1');
        $response->assertDontSee('Meal 16');
    }

    public function test_meal_detail_shows_related_products(): void
    {
        $category = $this->createCategory(['name' => 'RelCat', 'slug' => 'rel-cat']);

        $this->createMeal($category, ['title' => 'Main Product', 'slug' => 'main-product']);

        $this->createMeal($category, ['title' => 'Related One', 'slug' => 'related-one']);

        $response = $this->get(route('catalog.meal', 'main-product'));

        $response->assertStatus(200);
        $response->assertSee('Related One');
    }

    public function test_search_page_shows_query_in_heading(): void
    {
        $response = $this->get(route('catalog.search', ['search' => 'Apple']));

        $response->assertStatus(200);
        $response->assertSee('Apple');
    }

    public function test_meals_page_filters_by_min_price(): void
    {
        $category = $this->createCategory(['name' => 'PriceCat', 'slug' => 'price-cat']);

        $this->createMeal($category, ['title' => 'Cheap', 'slug' => 'cheap-item', 'price' => 3]);
        $this->createMeal($category, ['title' => 'Expensive', 'slug' => 'expensive-item', 'price' => 30]);

        $response = $this->get(route('catalog.meals', ['min_price' => 10]));

        $response->assertStatus(200);
        $response->assertSee('Expensive');
        $response->assertDontSee('Cheap');
    }

    public function test_meals_page_filters_by_max_price(): void
    {
        $category = $this->createCategory(['name' => 'PriceCat2', 'slug' => 'price-cat2']);

        $this->createMeal($category, ['title' => 'Affordable', 'slug' => 'affordable-item', 'price' => 5]);
        $this->createMeal($category, ['title' => 'Pricy', 'slug' => 'pricy-item', 'price' => 50]);

        $response = $this->get(route('catalog.meals', ['max_price' => 10]));

        $response->assertStatus(200);
        $response->assertSee('Affordable');
        $response->assertDontSee('Pricy');
    }

    public function test_meals_page_filters_by_discount_price(): void
    {
        $category = $this->createCategory(['name' => 'DiscCat', 'slug' => 'disc-cat']);

        $this->createMeal($category, [
            'title' => 'Discounted',
            'slug' => 'disc-item',
            'price' => 50,
            'discount_price' => 5,
        ]);

        $this->createMeal($category, [
            'title' => 'Full Price',
            'slug' => 'full-price-item',
            'price' => 30,
        ]);

        $response = $this->get(route('catalog.meals', ['max_price' => 10]));

        $response->assertStatus(200);
        $response->assertSee('Discounted');
        $response->assertDontSee('Full Price');
    }

    public function test_meal_detail_page_shows_price(): void
    {
        $category = $this->createCategory(['name' => 'PriceDetail', 'slug' => 'price-detail']);

        $this->createMeal($category, [
            'title' => 'Priced Item',
            'slug' => 'priced-item',
            'price' => 12.50,
        ]);

        $response = $this->get(route('catalog.meal', 'priced-item'));

        $response->assertStatus(200);
        $response->assertSee('12.50');
    }

    public function test_meal_detail_page_shows_discount_price(): void
    {
        $category = $this->createCategory(['name' => 'DiscDetail', 'slug' => 'disc-detail']);

        $this->createMeal($category, [
            'title' => 'Discounted Item Detail',
            'slug' => 'discounted-item-detail',
            'price' => 20,
            'discount_price' => 14.99,
        ]);

        $response = $this->get(route('catalog.meal', 'discounted-item-detail'));

        $response->assertStatus(200);
        $response->assertSee('14.99');
        $response->assertSee('20');
    }

    public function test_home_page_displays_categories(): void
    {
        $this->createCategory(['name' => 'Home Cat', 'slug' => 'home-cat']);

        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('Home Cat');
    }

    public function test_locale_toggle_works(): void
    {
        $response = $this->get(route('locale.toggle', 'ar'));

        $response->assertStatus(302);
        $this->assertEquals('ar', app()->getLocale());
    }
}
