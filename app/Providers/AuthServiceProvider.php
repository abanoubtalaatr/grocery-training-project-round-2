<?php

namespace App\Providers;

use App\Models\Address;
use App\Models\Cart;
use App\Models\ContactMessage;
use App\Models\Favorite;
use App\Models\Meal;
use App\Models\Order;
use App\Models\SmartList;
use App\Policies\AddressPolicy;
use App\Policies\CartPolicy;
use App\Policies\ContactMessagePolicy;
use App\Policies\FavoritePolicy;
use App\Policies\MealPolicy;
use App\Policies\OrderPolicy;
use App\Policies\SmartListListPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        SmartList::class => SmartListListPolicy::class,
        Address::class => AddressPolicy::class,
        Cart::class => CartPolicy::class,
        Favorite::class => FavoritePolicy::class,
        Meal::class => MealPolicy::class,
        ContactMessage::class => ContactMessagePolicy::class,
        Order::class => OrderPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
