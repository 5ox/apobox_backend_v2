<?php

namespace App\Services\Shipping;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * USPS Domestic Prices v3 REST API (OAuth 2.0)
 *
 * Replaces the retired RateV4 XML API (retired Jan 25, 2026).
 * @see https://developers.usps.com/domesticpricesv3
 */
class UspsService
{
    protected string $clientId;
    protected string $clientSecret;
    protected string $accountNumber;
    protected string $baseUrl = 'https://apis.usps.com';

    /**
     * Old CLASSID => new v3 mailClass mapping
     */
    protected array $classIdToMailClass = [
        '1'    => 'PRIORITY_MAIL',
        '2'    => 'PRIORITY_MAIL_EXPRESS',
        '3'    => 'PRIORITY_MAIL_EXPRESS',
        '4'    => 'USPS_GROUND_ADVANTAGE',
        '6'    => 'MEDIA_MAIL',
        '7'    => 'LIBRARY_MAIL',
        '1058' => 'USPS_GROUND_ADVANTAGE',
    ];

    public function __construct()
    {
        $this->clientId = config('shipping.usps.client_id', '');
        $this->clientSecret = config('shipping.usps.client_secret', '');
        $this->accountNumber = config('shipping.usps.account_number', '');
    }

    /**
     * Get shipping rates for a package.
     *
     * Returns an array of rates compatible with the old format:
     * [['class_id' => '1', 'service' => 'PRIORITY_MAIL', 'rate' => 8.50], ...]
     */
    public function getRate(array $params): array
    {
        $allowedClasses = config('shipping.usps.rate_classes', []);

        try {
            $token = $this->getAccessToken();
            $rates = $this->fetchRates($token, $params);

            // Filter to only configured rate classes
            return collect($rates)->filter(function ($rate) use ($allowedClasses) {
                return in_array($rate['class_id'], $allowedClasses);
            })->values()->all();
        } catch (\Exception $e) {
            Log::channel('shipping')->error('USPS Rate Error: ' . $e->getMessage(), [
                'params' => $params,
            ]);
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Get all available rates without filtering by class.
     */
    public function getAllRates(array $params): array
    {
        try {
            $token = $this->getAccessToken();
            return $this->fetchRates($token, $params);
        } catch (\Exception $e) {
            Log::channel('shipping')->error('USPS Rate Error: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Obtain an OAuth 2.0 access token (cached for ~8 hours).
     */
    protected function getAccessToken(): string
    {
        return Cache::remember('usps_oauth_token', 7 * 3600, function () {
            $response = Http::post($this->baseUrl . '/oauth2/v3/token', [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'grant_type' => 'client_credentials',
            ]);

            if (!$response->successful() || !$response->json('access_token')) {
                $error = $response->json('error_description', 'OAuth token request failed');
                throw new \RuntimeException('USPS OAuth Error: ' . $error);
            }

            return $response->json('access_token');
        });
    }

    /**
     * Query Domestic Prices v3 for each configured mail class.
     */
    protected function fetchRates(string $token, array $params): array
    {
        $url = $this->baseUrl . '/prices/v3/base-rates/search';
        $originZip = config('shipping.origin_zip');

        // Convert pounds + ounces to decimal pounds
        $pounds = (int) ($params['pounds'] ?? 0);
        $ounces = (int) ($params['ounces'] ?? 0);
        $weightLbs = round(($pounds * 16 + $ounces) / 16, 4);

        // Dimensions (optional, default to 0)
        $length = (float) ($params['length'] ?? 0);
        $width = (float) ($params['width'] ?? 0);
        $height = (float) ($params['height'] ?? 0);

        $rates = [];
        $queried = []; // avoid duplicate mailClass queries

        foreach ($this->classIdToMailClass as $classId => $mailClass) {
            if (isset($queried[$mailClass])) {
                // Map additional classIds that share the same mailClass
                if (!empty($queried[$mailClass])) {
                    foreach ($queried[$mailClass] as $rate) {
                        $rates[] = array_merge($rate, ['class_id' => $classId]);
                    }
                }
                continue;
            }

            $basePayload = [
                'originZIPCode' => $originZip,
                'destinationZIPCode' => $this->prepareZip($params['zip'] ?? ''),
                'weight' => $weightLbs,
                'length' => $length,
                'width' => $width,
                'height' => $height,
                'mailClass' => $mailClass,
                'processingCategory' => 'MACHINABLE',
                'destinationEntryFacilityType' => 'NONE',
                'rateIndicator' => 'DR',
            ];

            try {
                // --- Commercial (our discounted) rate ---
                $commercialPayload = $basePayload + [
                    'priceType' => 'COMMERCIAL',
                    'accountType' => 'EPS',
                    'accountNumber' => $this->accountNumber,
                ];

                $response = Http::withToken($token)->post($url, $commercialPayload);
                $body = $response->json();

                $commercialRate = $this->extractPrice($body);

                // --- Retail rate ---
                $retailPayload = $basePayload + [
                    'priceType' => 'RETAIL',
                ];

                $retailRate = null;
                try {
                    $retailResponse = Http::withToken($token)->post($url, $retailPayload);
                    $retailBody = $retailResponse->json();
                    $retailRate = $this->extractPrice($retailBody);
                } catch (\Exception $e) {
                    // Retail lookup is non-critical; log and continue
                    Log::channel('shipping')->info("USPS retail rate unavailable for {$mailClass}: " . $e->getMessage());
                }

                $classRates = [];

                if ($commercialRate !== null) {
                    $classRates[] = [
                        'class_id' => $classId,
                        'service' => $mailClass,
                        'rate' => $commercialRate,
                        'retail_rate' => $retailRate,
                        'description' => $body['description'] ?? '',
                    ];
                }

                $queried[$mailClass] = $classRates;
                $rates = array_merge($rates, $classRates);
            } catch (\Exception $e) {
                Log::channel('shipping')->warning("USPS rate query failed for {$mailClass}: " . $e->getMessage());
                $queried[$mailClass] = [];
            }
        }

        return $rates;
    }

    /**
     * Extract the price from a USPS v3 rate response body.
     */
    protected function extractPrice(array $body): ?float
    {
        if (!empty($body['rates'])) {
            // Use the first rate entry
            $rate = $body['rates'][0];
            return (float) ($rate['price'] ?? $rate['totalPrice'] ?? 0);
        }

        if (!empty($body['totalPrice'])) {
            return (float) $body['totalPrice'];
        }

        return null;
    }

    /**
     * Removes the +4 part of a ZIP code if present.
     */
    public function prepareZip(string $zip): string
    {
        if (preg_match('/^\d{5}.+$/', $zip)) {
            return substr($zip, 0, 5);
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
}
