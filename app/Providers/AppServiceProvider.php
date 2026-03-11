<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Hash;
use App\Auth\ApoboxPasswordHasher;
use App\Models\Customer;
use App\Models\OrderLineItems\OrderLineItem;
use App\Models\AuthorizedName;
use App\Observers\CustomerObserver;
use App\Observers\OrderLineItemObserver;
use App\Observers\AuthorizedNameObserver;
use App\Services\PaymentService;
use App\Services\CreditCardService;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PaymentService::class, function ($app) {
            return new PaymentService(
                config('apobox.paypal.client_id'),
                config('apobox.paypal.client_secret'),
                config('apobox.paypal.mode'),
            );
        });

        $this->app->singleton(CreditCardService::class, function ($app) {
            return new CreditCardService(config('apobox.credit_card.key'));
        });
    }

    public function boot(): void
    {
        Hash::extend('apobox', function () {
            return new ApoboxPasswordHasher();
        });

        Customer::observe(CustomerObserver::class);
        OrderLineItem::observe(OrderLineItemObserver::class);
        AuthorizedName::observe(AuthorizedNameObserver::class);
    }
}
