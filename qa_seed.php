<?php
// Bootstrap Laravel
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Category;
use App\Models\Meal;

// Create category if not exists
$cat = Category::first();
if (!$cat) {
    $cat = Category::create([
        'name'      => 'Groceries',
        'slug'      => 'groceries',
        'is_active' => true,
    ]);
    echo "Created category: {$cat->name} (ID: {$cat->id})\n";
} else {
    echo "Using existing category: {$cat->name} (ID: {$cat->id})\n";
}

// Create meal if not exists
$meal = Meal::where('slug', 'fresh-organic-apples')->first();
if (!$meal) {
    $meal = Meal::create([
        'category_id'    => $cat->id,
        'title'          => 'Fresh Organic Apples',
        'slug'           => 'fresh-organic-apples',
        'description'    => 'Crispy and sweet organic apples, freshly picked from the farm.',
        'image'          => 'meals/default.jpg',
        'price'          => 15.99,
        'stock_quantity' => 100,
        'is_available'   => true,
        'is_featured'    => true,
    ]);
    echo "Created meal: {$meal->title} (ID: {$meal->id})\n";
} else {
    echo "Using existing meal: {$meal->title} (ID: {$meal->id})\n";
}

echo json_encode([
    'meal_id'        => $meal->id,
    'title'          => $meal->title,
    'price'          => $meal->price,
    'stock_quantity' => $meal->stock_quantity,
    'is_available'   => $meal->is_available,
]);
echo "\n";
