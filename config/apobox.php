<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Identity
    |--------------------------------------------------------------------------
    */
    'short_name' => 'APO Box',
    'long_name' => 'APO Box',
    'domain' => 'apobox.com',

    /*
    |--------------------------------------------------------------------------
    | Warehouse Address
    |--------------------------------------------------------------------------
    */
    'address' => [
        'line1' => env('APOBOX_ADDRESS_LINE1', '1911 Western Ave'),
        'city' => env('APOBOX_ADDRESS_CITY', 'Plymouth'),
        'state' => env('APOBOX_ADDRESS_STATE', 'IN'),
        'zip' => env('APOBOX_ADDRESS_ZIP', '46563'),
    ],

    'warehouse' => [
        'code' => 'IN',
    ],

    /*
    |--------------------------------------------------------------------------
    | Tracking
    |--------------------------------------------------------------------------
    */
    'tracking' => [
        'prefix' => 'S:',
    ],

    /*
    |--------------------------------------------------------------------------
    | Fee by Weight (ounces)
    |--------------------------------------------------------------------------
    | Keys are the minimum ounces, values are the fee amount.
    */
    'fee_by_weight' => [
        0 => 10.95,
        17 => 12.95,
    ],

    /*
    |--------------------------------------------------------------------------
    | Order Settings
    |--------------------------------------------------------------------------
    */
    'orders' => [
        'fee_rates' => [
            'inspection' => '5.00',
            'return' => '10.00',
            'misaddressed' => '5.00',
            'ship_to_us' => '10.00',
        ],
        'payment_reminders' => 3,
        'default_customs_description' => 'Household & Personal Goods',
        'minimum_label_value' => '1.00',
        'storage' => [
            'daily_rate' => 2.00,
            'grace_days' => 14,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Customer Settings
    |--------------------------------------------------------------------------
    */
    'customers' => [
        'signup_reminders' => 2,
        'purge_partials_weeks' => 4,
        'expired_card_reminders' => [
            'number_to_send' => 1,
            'max_months' => 6,
            'send_delay_seconds' => 2,
            'send_max_per_run' => 200,
        ],
        'sources' => [
            0 => 'How did you hear about us?',
            1 => 'Google',
            2 => 'Yahoo',
            3 => 'Apple',
            4 => 'Stars and Stripes',
            5 => 'Amazon',
            6 => 'Army Times',
            7 => 'Target',
            8 => 'Friend',
            9 => 'APO FPO Post Office',
            9999 => 'Other',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Credit Card
    |--------------------------------------------------------------------------
    */
    'credit_card' => [
        'key' => env('CREDIT_CARD_KEY', 'testkey'),
        'invalid_before' => '3D',
    ],

    /*
    |--------------------------------------------------------------------------
    | Security
    |--------------------------------------------------------------------------
    */
    'admin_allowed_ips' => array_filter(explode(',', env('ADMIN_ALLOWED_IPS', ''))),

    /*
    |--------------------------------------------------------------------------
    | PayPal
    |--------------------------------------------------------------------------
    */
    'paypal' => [
        'client_id' => env('PAYPAL_CLIENT_ID', ''),
        'client_secret' => env('PAYPAL_CLIENT_SECRET', ''),
        'mode' => env('PAYPAL_MODE', 'sandbox'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Google OAuth2
    |--------------------------------------------------------------------------
    */
    'google_oauth' => [
        'client_id' => env('GOOGLE_CLIENT_ID', ''),
        'client_secret' => env('GOOGLE_CLIENT_SECRET', ''),
        'redirect_uri' => env('GOOGLE_REDIRECT_URI', ''),
        'legacy_login' => env('ADMIN_LEGACY_LOGIN', true),
        'log_failed_attempts' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Zendesk
    |--------------------------------------------------------------------------
    */
    'zendesk' => [
        'subdomain' => env('ZENDESK_SUBDOMAIN', 'apobox'),
        'api_token' => env('ZENDESK_API', ''),
        'agent_email' => env('ZENDESK_AGENT_EMAIL', 'support@apobox.com'),
        'widget_key' => env('ZENDESK_WIDGET_KEY', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Search Date Options
    |--------------------------------------------------------------------------
    */
    'search' => [
        'date' => [
            'default' => '-60 days',
        ],
    ],

    'search_date' => [
        'default' => '-60 days',
        'options' => [
            '-24 hours' => 'past 24 hours',
            '-7 days' => 'Past week',
            '-30 days' => 'Past 30 days',
            '-60 days' => 'Past 60 days',
            '-90 days' => 'Past 90 days',
            '-120 days' => 'Past 120 days',
            '-1 year' => 'Past year',
            '0' => 'All',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Postal Classes
    |--------------------------------------------------------------------------
    */
    'postal_classes' => [
        'priority_mail' => 'Priority Mail',
        'parcel_post' => 'Parcel Select',
    ],

    /*
    |--------------------------------------------------------------------------
    | Tooltips
    |--------------------------------------------------------------------------
    */
    'tooltips' => [
        'default_address' => 'This is the address that is on file for your credit card. You can add your APO Box address to be an authorized shipping address on your credit card by calling the number on the back of your credit card.',
        'shipping_address' => 'This is the address that we will forward your packages to. This must be an APO/FPO/DPO address',
        'emergency_address' => 'This is your backup shipping address. It is only used when a box is over the size or weight limit for your APO/FPO/DPO. It will also be used if your package is returned to us. This address must be in the US and cannot be another APO/FPO/DPO address.',
    ],

    /*
    |--------------------------------------------------------------------------
    | US States for Billing
    |--------------------------------------------------------------------------
    */
    'billing_states' => [
        '' => 'State',
        'AE' => 'Armed Forces Africa / Canada / Europe / Middle East',
        'AA' => 'Armed Forces America (Except Canada)',
        'AP' => 'Armed Forces Pacific',
        'AL' => 'Alabama', 'AK' => 'Alaska', 'AS' => 'American Samoa',
        'AZ' => 'Arizona', 'AR' => 'Arkansas', 'CA' => 'California',
        'CO' => 'Colorado', 'CT' => 'Connecticut', 'DE' => 'Delaware',
        'DC' => 'District of Columbia', 'FL' => 'Florida', 'GA' => 'Georgia',
        'GU' => 'Guam GU', 'HI' => 'Hawaii', 'ID' => 'Idaho',
        'IL' => 'Illinois', 'IN' => 'Indiana', 'IA' => 'Iowa',
        'KS' => 'Kansas', 'KY' => 'Kentucky', 'LA' => 'Louisiana',
        'ME' => 'Maine', 'MH' => 'Marshall Islands', 'MD' => 'Maryland',
        'MA' => 'Massachusetts', 'MI' => 'Michigan', 'MN' => 'Minnesota',
        'MS' => 'Mississippi', 'MO' => 'Missouri', 'MT' => 'Montana',
        'NE' => 'Nebraska', 'NV' => 'Nevada', 'NH' => 'New Hampshire',
        'NJ' => 'New Jersey', 'NM' => 'New Mexico', 'NY' => 'New York',
        'NC' => 'North Carolina', 'ND' => 'North Dakota',
        'MP' => 'Northern Mariana Islands', 'OH' => 'Ohio', 'OK' => 'Oklahoma',
        'OR' => 'Oregon', 'PW' => 'Palau', 'PA' => 'Pennsylvania',
        'PR' => 'Puerto Rico', 'RI' => 'Rhode Island', 'SC' => 'South Carolina',
        'SD' => 'South Dakota', 'TN' => 'Tennessee', 'TX' => 'Texas',
        'UT' => 'Utah', 'VT' => 'Vermont', 'VI' => 'Virgin Islands',
        'VA' => 'Virginia', 'WA' => 'Washington', 'WV' => 'West Virginia',
        'WI' => 'Wisconsin', 'WY' => 'Wyoming',
    ],

];
