<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Meal;
use App\Models\Order;
use App\Observers\CategoryObserver;
use App\Observers\MealObserver;
use App\Observers\OrderObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
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
        Order::observe(OrderObserver::class);

        $this->configureRateLimiting();
    }

    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(5)->by($request->input('login') ?: $request->input('identifier') ?: $request->ip());
        });

        RateLimiter::for('otp', function (Request $request) {
            return Limit::perMinute(5)->by($request->input('identifier') ?: $request->ip());
        });

        RateLimiter::for('chatbot', function (Request $request) {
            return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('contact', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });
    }
}
