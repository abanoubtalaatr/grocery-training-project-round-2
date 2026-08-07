<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\Meal;
use App\Models\Offer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Review;
use App\Models\Subcategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        // ==================== Users ====================
        $users = [];
        for ($i = 1; $i <= 50; $i++) {
            $users[] = User::create([
                'username' => "user{$i}",
                'firstname' => fake()->firstName(),
                'lastname' => fake()->lastName(),
                'email' => fake()->unique()->safeEmail(),
                'phone' => '+20' . fake()->numberBetween(1000000000, 9999999999),
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'is_admin' => false,
            ]);
        }

        // ==================== Categories ====================
        $categoriesData = [
            ['name' => 'Fruits & Vegetables', 'description' => 'Fresh fruits and vegetables'],
            ['name' => 'Dairy & Eggs', 'description' => 'Milk, cheese, eggs and more'],
            ['name' => 'Bakery', 'description' => 'Fresh bread and pastries'],
            ['name' => 'Meat & Poultry', 'description' => 'Fresh meat and poultry'],
            ['name' => 'Beverages', 'description' => 'Drinks and beverages'],
            ['name' => 'Snacks', 'description' => 'Chips, cookies and snacks'],
        ];

        $categories = [];
        foreach ($categoriesData as $data) {
            $categories[] = Category::create([
                'name' => $data['name'],
                'slug' => str()->slug($data['name']),
                'description' => $data['description'],
                'is_active' => true,
                'sort_order' => fake()->numberBetween(1, 10),
            ]);
        }

        // ==================== Subcategories ====================
        $subcategoriesData = [
            ['name' => 'Fresh Fruits', 'category_id' => 1],
            ['name' => 'Fresh Vegetables', 'category_id' => 1],
            ['name' => 'Milk', 'category_id' => 2],
            ['name' => 'Cheese', 'category_id' => 2],
            ['name' => 'Bread', 'category_id' => 3],
            ['name' => 'Pastries', 'category_id' => 3],
            ['name' => 'Chicken', 'category_id' => 4],
            ['name' => 'Beef', 'category_id' => 4],
            ['name' => 'Soft Drinks', 'category_id' => 5],
            ['name' => 'Juices', 'category_id' => 5],
            ['name' => 'Chips', 'category_id' => 6],
            ['name' => 'Cookies', 'category_id' => 6],
        ];

        foreach ($subcategoriesData as $data) {
            Subcategory::create([
                'name' => $data['name'],
                'slug' => str()->slug($data['name']),
                'category_id' => $data['category_id'],
                'is_active' => true,
                'order' => fake()->numberBetween(1, 10),
            ]);
        }

        // ==================== Products (Meals) ====================
        $productNames = [
            'Organic Bananas', 'Red Apples', 'Fresh Tomatoes', 'Green Lettuce',
            'Whole Milk 1L', 'Cheddar Cheese', 'Greek Yogurt', 'Free Range Eggs 12pk',
            'Sourdough Bread', 'Croissants 4pk', 'Chocolate Cake', 'Blueberry Muffins',
            'Chicken Breast 500g', 'Ground Beef 500g', 'Salmon Fillet', 'Turkey Slices',
            'Coca Cola 2L', 'Orange Juice 1L', 'Mineral Water 1.5L', 'Green Tea',
            'Potato Chips', 'Chocolate Cookies', 'Mixed Nuts', 'Protein Bars',
        ];

        $products = [];
        foreach ($productNames as $index => $name) {
            $price = fake()->randomFloat(2, 5, 100);
            $hasDiscount = fake()->boolean(30);
            
            $products[] = Meal::create([
                'title' => $name,
                'slug' => str()->slug($name),
                'description' => fake()->sentence(10),
                'price' => $price,
                'discount_price' => $hasDiscount ? $price * 0.8 : null,
                'category_id' => $categories[array_rand($categories)]->id,
                'subcategory_id' => fake()->boolean(70) ? fake()->numberBetween(1, 12) : null,
                'stock_quantity' => fake()->numberBetween(0, 100),
                'size' => fake()->randomElement(['Small', 'Medium', 'Large', null]),
                'brand' => fake()->randomElement(['FreshCo', 'OrganicBest', 'DailyFresh', 'GreenValley', null]),
                'is_available' => fake()->boolean(80),
                'is_featured' => fake()->boolean(20),
                'rating' => fake()->randomFloat(1, 1, 5),
                'rating_count' => fake()->numberBetween(0, 200),
                'sold_count' => fake()->numberBetween(0, 500),
                'features' => fake()->randomElements(['Organic', 'Gluten-Free', 'Vegan', 'Sugar-Free', 'High Protein'], fake()->numberBetween(0, 3)),
            ]);
        }

        // ==================== Offers ====================
        $offersData = [
            ['title' => 'Summer Sale 20%', 'code' => 'SUMMER20', 'type' => 'percentage', 'discount_value' => 20],
            ['title' => 'First Order $10 Off', 'code' => 'FIRST10', 'type' => 'fixed', 'discount_value' => 10],
            ['title' => 'Free Delivery', 'code' => 'FREESHIP', 'type' => 'percentage', 'discount_value' => 100],
            ['title' => 'Weekend Special 15%', 'code' => 'WEEKEND15', 'type' => 'percentage', 'discount_value' => 15],
            ['title' => 'New Year $25 Off', 'code' => 'NEWYEAR25', 'type' => 'fixed', 'discount_value' => 25],
        ];

        foreach ($offersData as $data) {
            Offer::create([
                'title' => $data['title'],
                'code' => $data['code'],
                'description' => fake()->sentence(),
                'type' => $data['type'],
                'discount_value' => $data['discount_value'],
                'minimum_purchase' => fake()->randomElement([0, 0, 0, 50, 100]),
                'start_date' => now()->subDays(fake()->numberBetween(1, 30)),
                'end_date' => now()->addDays(fake()->numberBetween(5, 60)),
                'is_active' => true,
                'is_featured' => fake()->boolean(40),
            ]);
        }

        // ==================== Orders ====================
        $statuses = ['placed', 'processing', 'shipping', 'out_for_delivery', 'delivered', 'cancelled'];
        
        for ($i = 1; $i <= 100; $i++) {
            $user = $users[array_rand($users)];
            $status = fake()->randomElement($statuses);
            $subtotal = fake()->randomFloat(2, 20, 500);
            $tax = round($subtotal * 0.1, 2);
            $discount = fake()->boolean(20) ? fake()->randomFloat(2, 5, 50) : 0;
            $shipping = $subtotal > 100 ? 0 : fake()->randomFloat(2, 5, 20);
            $total = $subtotal + $tax - $discount + $shipping;

            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => 'ORD-' . str_pad($i, 8, '0', STR_PAD_LEFT),
                'status' => $status,
                'payment_method' => fake()->randomElement(['card', 'cash_on_delivery']),
                'delivery_type' => fake()->randomElement(['delivery', 'pickup']),
                'subtotal' => $subtotal,
                'tax' => $tax,
                'discount' => $discount,
                'shipping_fee' => $shipping,
                'total' => $total,
                'notes' => fake()->boolean(10) ? fake()->sentence() : null,
                'placed_at' => fake()->dateTimeBetween('-6 months', 'now'),
                'created_at' => fake()->dateTimeBetween('-6 months', 'now'),
            ]);

            // Order Items
            $itemCount = fake()->numberBetween(1, 5);
            $selectedProducts = fake()->randomElements($products, $itemCount);
            
            foreach ($selectedProducts as $product) {
                $quantity = fake()->numberBetween(1, 5);
                $unitPrice = $product->discount_price ?? $product->price;
                $itemSubtotal = $unitPrice * $quantity;
                
                OrderItem::create([
                    'order_id' => $order->id,
                    'meal_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'discount_amount' => 0,
                    'subtotal' => $itemSubtotal,
                ]);
            }
        }

        // ==================== Reviews ====================
        foreach ($products as $product) {
            $reviewCount = fake()->numberBetween(0, 10);
            for ($i = 1; $i <= $reviewCount; $i++) {
                Review::create([
                    'user_id' => $users[array_rand($users)]->id,
                    'meal_id' => $product->id,
                    'rating' => fake()->numberBetween(1, 5),
                    'comment' => fake()->boolean(70) ? fake()->sentence(fake()->numberBetween(3, 15)) : null,
                    'is_approved' => fake()->boolean(80),
                ]);
            }
        }

        // ==================== Contact Messages ====================
        for ($i = 1; $i <= 30; $i++) {
            ContactMessage::create([
                'name' => fake()->name(),
                'email' => fake()->email(),
                'phone' => fake()->boolean(50) ? fake()->phoneNumber() : null,
                'subject' => fake()->sentence(fake()->numberBetween(3, 8)),
                'message' => fake()->paragraph(fake()->numberBetween(1, 3)),
                'status' => fake()->randomElement(['new', 'read', 'replied', 'spam']),
            ]);
        }
    }
}
