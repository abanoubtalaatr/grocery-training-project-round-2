<?php

namespace App\Providers;

use App\Models\Meal;
use App\Observers\MealObserver;
use Illuminate\Support\ServiceProvider;
use App\Interfaces\CategoryServiceInterface;
use App\Services\CategoryService;

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
           $this->app->bind(
        CategoryServiceInterface::class,
        CategoryService::class
    );
        
    }
}
