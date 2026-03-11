<?php

return [

    'origin_zip' => '46563',
    'rates_backend' => 'Usps',

    'apo_zones' => ['AA', 'AE', 'AP'],

    /*
    |--------------------------------------------------------------------------
    | USPS
    |--------------------------------------------------------------------------
    */
    'usps' => [
        'client_id' => env('USPS_CLIENT_ID', ''),
        'client_secret' => env('USPS_CLIENT_SECRET', ''),
        'account_number' => env('USPS_ACCOUNT_NUMBER', ''),
        'rate_classes' => [
            'Priority Mail Express 2-Day' => '3',
            'Priority Mail Express 2-Day Hold For Pickup' => '2',
            'Priority Mail 2-Day' => '1',
            'Standard Post' => '4',
            'Ground Advantage' => '1058',
        ],
        'tracking_url' => 'https://tools.usps.com/go/TrackConfirmAction_input?qtc_tLabels1=',
    ],

    /*
    |--------------------------------------------------------------------------
    | FedEx
    |--------------------------------------------------------------------------
    */
    'fedex' => [
        'key' => env('FEDEX_KEY', ''),
        'password' => env('FEDEX_PASSWORD', ''),
        'account' => env('FEDEX_ACCOUNT', ''),
        'meter' => env('FEDEX_METER', ''),
        'shipper' => [
            'contact' => [
                'PersonName' => 'APO Box',
                'CompanyName' => 'APO Box',
                'PhoneNumber' => '8004096013',
            ],
            'address' => [
                'StreetLines' => ['1911 Western Avenue'],
                'City' => 'Plymouth',
                'StateOrProvinceCode' => 'IN',
                'PostalCode' => '46563',
                'CountryCode' => 'US',
            ],
        ],
        'label' => [
            'type' => 'ZPLII',
            'purge_weeks' => 4,
        ],
        'valid_countries' => ['United States'],
        'tracking_url' => 'http://www.fedex.com/Tracking?action=track&tracknumbers=',
    ],

    /*
    |--------------------------------------------------------------------------
    | Endicia
    |--------------------------------------------------------------------------
    */
    'endicia' => [
        'account_number' => env('ENDICIA_ACCOUNT_NUMBER', ''),
        'customs_signer' => env('ENDICIA_CUSTOMS_SIGNER', 'Melinda Hauptmann'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Zebra Label Printer
    |--------------------------------------------------------------------------
    */
    'zebra' => [
        'method' => env('ZEBRA_PRINT_METHOD', 'raw'),
        'client' => env('ZEBRA_PRINTER_IP'),
        'auto' => (bool) env('ZEBRA_AUTO_PRINT', false),
    ],

];
