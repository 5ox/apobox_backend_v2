<?php

/**
 * PayPal configuration for srmklive/paypal ~3.0.
 *
 * Actual credentials are set programmatically via PaymentService::ensureInitialized().
 * This config file is required by the package's service provider.
 */
return [
    'mode' => env('PAYPAL_MODE', 'sandbox'),

    'sandbox' => [
        'client_id' => env('PAYPAL_CLIENT_ID', ''),
        'client_secret' => env('PAYPAL_CLIENT_SECRET', ''),
        'app_id' => '',
    ],

    'live' => [
        'client_id' => env('PAYPAL_CLIENT_ID', ''),
        'client_secret' => env('PAYPAL_CLIENT_SECRET', ''),
        'app_id' => '',
    ],

    'payment_action' => 'Sale',
    'currency' => 'USD',
    'notify_url' => '',
    'locale' => 'en_US',
    'validate_ssl' => true,
];
