<?php

namespace App\Providers;

use App\Models\Meal;
use App\Observers\MealObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
         $this->app->bind(

            \App\Services\Email\Contracts\EmailServiceInterface::class,

            \App\Services\Email\EmailService::class

        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Meal::observe(MealObserver::class);
    }
}
