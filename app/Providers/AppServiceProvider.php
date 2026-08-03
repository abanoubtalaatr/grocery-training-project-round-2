<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Meal;
use App\Observers\CategoryObserver;
use App\Observers\MealObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Meal::observe(MealObserver::class);
        Category::observe(CategoryObserver::class);
    }
}
