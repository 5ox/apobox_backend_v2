<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Auth\ApoboxPasswordHasher;
use App\Mail\Auth\GmailOAuthAuthenticator;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use App\Models\Customer;
use App\Models\OrderLineItems\OrderShipping;
use App\Models\OrderLineItems\OrderInsurance;
use App\Models\OrderLineItems\OrderBattery;
use App\Models\OrderLineItems\OrderRepack;
use App\Models\OrderLineItems\OrderStorage;
use App\Models\OrderLineItems\OrderReturn;
use App\Models\OrderLineItems\OrderMisaddressed;
use App\Models\OrderLineItems\OrderShipToUS;
use App\Models\OrderLineItems\OrderFee;
use App\Models\OrderLineItems\OrderSubtotal;
use App\Models\OrderLineItems\OrderTotal;
use App\Models\AuthorizedName;
use App\Observers\CustomerObserver;
use App\Observers\OrderLineItemObserver;
use App\Observers\AuthorizedNameObserver;
use App\Services\PaymentService;
use App\Services\CreditCardService;
use App\Services\ZendeskService;

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

        $this->app->singleton(ZendeskService::class, function ($app) {
            return new ZendeskService(
                config('apobox.zendesk.subdomain'),
                config('apobox.zendesk.api_token'),
                config('apobox.zendesk.agent_email'),
            );
        });
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        Hash::extend('apobox', function () {
            return new ApoboxPasswordHasher();
        });

        Mail::extend('gmail-oauth', function (array $config = []) {
            $authenticator = new GmailOAuthAuthenticator(
                clientId: $config['client_id'],
                clientSecret: $config['client_secret'],
                refreshToken: $config['refresh_token'],
                username: $config['username'],
            );

            $transport = new EsmtpTransport(
                host: $config['host'] ?? 'smtp.gmail.com',
                port: (int) ($config['port'] ?? 465),
                tls: true,
                authenticators: [$authenticator],
            );

            $transport->setUsername($config['username']);
            // Must be non-empty or EsmtpTransport skips authentication
            $transport->setPassword('oauth2');

            return $transport;
        });

        Customer::observe(CustomerObserver::class);
        AuthorizedName::observe(AuthorizedNameObserver::class);

        // Register on concrete subclasses (OrderLineItem is abstract)
        $lineItemModels = [
            OrderShipping::class, OrderInsurance::class, OrderBattery::class,
            OrderRepack::class, OrderStorage::class, OrderReturn::class,
            OrderMisaddressed::class, OrderShipToUS::class, OrderFee::class,
            OrderSubtotal::class, OrderTotal::class,
        ];
        foreach ($lineItemModels as $model) {
            $model::observe(OrderLineItemObserver::class);
        }
    }
}
