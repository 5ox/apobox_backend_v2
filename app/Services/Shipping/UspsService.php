<?php

namespace App\Services\Shipping;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * USPS Domestic Prices v3 REST API (OAuth 2.0)
 *
 * @see https://developers.usps.com/domesticpricesv3
 * @see https://github.com/USPS/api-examples
 */
class UspsService
{
    protected string $clientId;
    protected string $clientSecret;
    protected string $accountNumber;
    protected string $baseUrl = 'https://apis.usps.com';

    /**
     * Supported mail classes with their v3 API parameters.
     *
     * rateIndicator: SP = Single Piece (standard weight-based pricing)
     * processingCategory: MACHINABLE for standard parcels
     */
    protected array $mailClasses = [
        'PRIORITY_MAIL' => [
            'label' => 'Priority Mail',
            'rateIndicator' => 'SP',
            'processingCategory' => 'MACHINABLE',
        ],
        'PRIORITY_MAIL_EXPRESS' => [
            'label' => 'Priority Mail Express',
            'rateIndicator' => 'SP',
            'processingCategory' => 'MACHINABLE',
        ],
        'USPS_GROUND_ADVANTAGE' => [
            'label' => 'USPS Ground Advantage',
            'rateIndicator' => 'SP',
            'processingCategory' => 'MACHINABLE',
        ],
        'MEDIA_MAIL' => [
            'label' => 'Media Mail',
            'rateIndicator' => 'SP',
            'processingCategory' => 'MACHINABLE',
        ],
        'LIBRARY_MAIL' => [
            'label' => 'Library Mail',
            'rateIndicator' => 'SP',
            'processingCategory' => 'MACHINABLE',
        ],
    ];

    /**
     * Normalize legacy mail_class values from old orders to v3 API names.
     */
    protected array $legacyClassMap = [
        'PARCEL_POST'      => 'USPS_GROUND_ADVANTAGE',
        'PARCEL_SELECT'    => 'USPS_GROUND_ADVANTAGE',
        'STANDARD_POST'    => 'USPS_GROUND_ADVANTAGE',
        'GROUND_ADVANTAGE' => 'USPS_GROUND_ADVANTAGE',
        'APOBOX_DIRECT'    => 'PRIORITY_MAIL',  // legacy default
    ];

    public function __construct()
    {
        $this->clientId = config('shipping.usps.client_id', '');
        $this->clientSecret = config('shipping.usps.client_secret', '');
        $this->accountNumber = config('shipping.usps.account_number', '');
    }

    /**
     * Get shipping rates filtered to configured rate classes.
     *
     * Returns: [['service' => 'PRIORITY_MAIL', 'label' => 'Priority Mail', 'rate' => 8.50, 'retail_rate' => 10.50, 'description' => '...'], ...]
     * On error: ['error' => 'message']
     */
    public function getRate(array $params): array
    {
        $allowedClasses = config('shipping.usps.rate_classes', []);

        try {
            $token = $this->getAccessToken();
            $rates = $this->fetchRates($token, $params);

            if (empty($allowedClasses)) {
                return $rates;
            }

            return collect($rates)->filter(function ($rate) use ($allowedClasses) {
                return in_array($rate['service'], $allowedClasses);
            })->values()->all();
        } catch (\Exception $e) {
            Log::channel('shipping')->error('USPS Rate Error: ' . $e->getMessage(), [
                'params' => $params,
            ]);
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Get all available rates without filtering by configured classes.
     */
    public function getAllRates(array $params): array
    {
        try {
            $token = $this->getAccessToken();
            return $this->fetchRates($token, $params);
        } catch (\Exception $e) {
            Log::channel('shipping')->error('USPS Rate Error: ' . $e->getMessage(), [
                'params' => $params,
            ]);
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Obtain an OAuth 2.0 access token (cached for ~7 hours).
     */
    public function getAccessToken(): string
    {
        return Cache::remember('usps_oauth_token', 7 * 3600, function () {
            $response = Http::connectTimeout(10)
                ->timeout(20)
                ->retry(2, 500, throw: false)
                ->post($this->baseUrl . '/oauth2/v3/token', [
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'grant_type' => 'client_credentials',
                ]);

            if (!$response->successful() || !$response->json('access_token')) {
                $error = $response->json('error_description')
                    ?? $response->json('error')
                    ?? 'OAuth token request failed (HTTP ' . $response->status() . ')';
                throw new \RuntimeException('USPS OAuth Error: ' . $error);
            }

            return $response->json('access_token');
        });
    }

    /**
     * Query Domestic Prices v3 for each mail class.
     */
    protected function fetchRates(string $token, array $params): array
    {
        $url = $this->baseUrl . '/prices/v3/base-rates/search';
        $originZip = $this->prepareZip(config('shipping.origin_zip', '46563'));
        $destZip = $this->prepareZip($params['zip'] ?? '');

        if (empty($destZip)) {
            throw new \RuntimeException('Destination ZIP code is required');
        }

        // Convert pounds + ounces to decimal pounds
        $pounds = (int) ($params['pounds'] ?? 0);
        $ounces = (int) ($params['ounces'] ?? 0);
        $weightLbs = round(($pounds * 16 + $ounces) / 16, 4);

        // Weight must be > 0 — minimum 1 oz
        if ($weightLbs <= 0) {
            $weightLbs = 0.0625;
        }

        // Dimensions — required by USPS API, default to 1" cube when not provided
        $length = max((float) ($params['length'] ?? 0), 1);
        $width = max((float) ($params['width'] ?? 0), 1);
        $height = max((float) ($params['height'] ?? 0), 1);

        $rates = [];

        foreach ($this->mailClasses as $mailClass => $config) {
            $payload = [
                'originZIPCode' => $originZip,
                'destinationZIPCode' => $destZip,
                'weight' => $weightLbs,
                'length' => $length,
                'width' => $width,
                'height' => $height,
                'mailClass' => $mailClass,
                'processingCategory' => $config['processingCategory'],
                'destinationEntryFacilityType' => 'NONE',
                'rateIndicator' => $config['rateIndicator'],
                'mailingDate' => now()->format('Y-m-d'),
            ];

            try {
                // --- Commercial (our discounted) rate ---
                $commercialPayload = $payload + [
                    'priceType' => 'COMMERCIAL',
                    'accountType' => 'EPS',
                    'accountNumber' => $this->accountNumber,
                ];

                $response = Http::withToken($token)
                    ->connectTimeout(10)
                    ->timeout(20)
                    ->retry(2, 500, throw: false)
                    ->post($url, $commercialPayload);

                $body = $response->json() ?? [];

                if (!$response->successful()) {
                    $apiError = $body['error']['message']
                        ?? $body['message']
                        ?? $body['error']
                        ?? 'HTTP ' . $response->status();
                    Log::channel('shipping')->warning("USPS rate API error for {$mailClass}: {$apiError}", [
                        'status' => $response->status(),
                        'payload' => $commercialPayload,
                        'response' => $body,
                    ]);
                    continue;
                }

                $commercialRate = $this->extractPrice($body);
                $description = $this->extractDescription($body);

                if ($commercialRate === null) {
                    Log::channel('shipping')->info("USPS no rate extracted for {$mailClass}", [
                        'response' => $body,
                    ]);
                    continue;
                }

                // --- Retail rate (for comparison) ---
                $retailPayload = $payload + [
                    'priceType' => 'RETAIL',
                ];

                $retailRate = null;
                try {
                    $retailResponse = Http::withToken($token)
                        ->connectTimeout(10)
                        ->timeout(20)
                        ->retry(2, 500, throw: false)
                        ->post($url, $retailPayload);

                    if ($retailResponse->successful()) {
                        $retailRate = $this->extractPrice($retailResponse->json() ?? []);
                    }
                } catch (\Exception $e) {
                    Log::channel('shipping')->info("USPS retail rate unavailable for {$mailClass}: " . $e->getMessage());
                }

                $rates[] = [
                    'service' => $mailClass,
                    'label' => $config['label'],
                    'rate' => $commercialRate,
                    'retail_rate' => $retailRate,
                    'description' => $description,
                ];
            } catch (\Exception $e) {
                Log::channel('shipping')->warning("USPS rate query failed for {$mailClass}: " . $e->getMessage());
            }
        }

        return $rates;
    }

    /**
     * Extract the price from a USPS v3 rate response.
     *
     * Response structure: { "totalBasePrice": 8.02, "rates": [{ "price": 8.02, ... }] }
     */
    protected function extractPrice(array $body): ?float
    {
        // Primary: use price from rates array
        if (!empty($body['rates'][0]['price'])) {
            return (float) $body['rates'][0]['price'];
        }

        // Fallback: totalBasePrice at root level
        if (!empty($body['totalBasePrice'])) {
            return (float) $body['totalBasePrice'];
        }

        return null;
    }

    /**
     * Extract the description from a USPS v3 rate response.
     */
    protected function extractDescription(array $body): string
    {
        return $body['rates'][0]['description'] ?? $body['description'] ?? '';
    }

    /**
     * Normalize a legacy mail_class value to a v3 API mailClass name.
     */
    public function normalizeMailClass(string $mailClass): string
    {
        $mailClass = strtoupper(trim($mailClass));

        return $this->legacyClassMap[$mailClass] ?? $mailClass;
    }

    /**
     * Removes the +4 part of a ZIP code if present.
     */
    public function prepareZip(string $zip): string
    {
        if (preg_match('/^(\d{5})/', $zip, $matches)) {
            return $matches[1];
        }
        return $zip;
    }

    /**
     * Calculates package size category.
     */
    public function calculateSize(float $height, float $length, float $width): string
    {
        if (ceil($height) > 12 || ceil($length) > 12 || ceil($width) > 12) {
            return 'Large';
        }
        return 'Regular';
    }

    /**
     * Converts total ounces to pounds + ounces.
     */
    public function calculateWeight(float $totalOunces): array
    {
        if ($totalOunces < 16) {
            return ['pounds' => 0, 'ounces' => (int) ceil($totalOunces)];
        }

        $pounds = $totalOunces / 16;
        $ounces = fmod($pounds, 1) * 16;

        return ['pounds' => (int) floor($pounds), 'ounces' => (int) ceil($ounces)];
    }

    /**
     * Flush the cached OAuth token (e.g. after credential rotation).
     */
    public function flushToken(): void
    {
        Cache::forget('usps_oauth_token');
    }

    /**
     * Get the supported mail classes configuration.
     */
    public function getMailClasses(): array
    {
        return $this->mailClasses;
    }
}
