<?php

namespace App\Providers;

use App\Models\Address;
use App\Models\CartItem;
use App\Models\ContactMessage;
use App\Models\Review;
use App\Models\SmartList;
use App\Policies\AddressPolicy;
use App\Policies\CartItemPolicy;
use App\Policies\ContactMessagePolicy;
use App\Policies\ReviewPolicy;
use App\Policies\SmartListPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Address::class => AddressPolicy::class,
        CartItem::class => CartItemPolicy::class,
        ContactMessage::class => ContactMessagePolicy::class,
        Review::class => ReviewPolicy::class,
        SmartList::class => SmartListPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
