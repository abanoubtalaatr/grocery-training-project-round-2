<?php

namespace App\Providers;

use App\Models\Meal;
use App\Models\Order;
use App\Observers\MealObserver;
use App\Observers\OrderObserver;
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
        Order::observe(OrderObserver::class);
    }
}
