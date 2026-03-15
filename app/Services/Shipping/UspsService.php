<?php

namespace App\Services\Shipping;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * USPS Domestic Prices + Shipping Options REST APIs (OAuth 2.0)
 *
 * Standard machinable parcels use Domestic Prices v3 base-rates. USPS
 * Shipping Options v3 is used for non-machinable and oversized parcels so the
 * calculator can return valid Ground Advantage and other supported USPS rates
 * instead of rejecting them up front.
 *
 * @see https://developers.usps.com/domesticpricesv3
 * @see https://developers.usps.com/shippingoptionsv3
 * @see https://github.com/USPS/api-examples
 */
class UspsService
{
    protected const USPS_MAX_WEIGHT_LBS = 70.0;
    protected const MACHINABLE_MAX_WEIGHT_LBS = 25.0;
    protected const MACHINABLE_MAX_LENGTH_IN = 22.0;
    protected const MACHINABLE_MAX_WIDTH_IN = 18.0;
    protected const MACHINABLE_MAX_HEIGHT_IN = 15.0;
    protected const STANDARD_MAX_LENGTH_PLUS_GIRTH_IN = 108.0;
    protected const USPS_MAX_LENGTH_PLUS_GIRTH_IN = 130.0;

    protected string $clientId;
    protected string $clientSecret;
    protected string $accountNumber;
    protected string $baseUrl = 'https://apis.usps.com';

    /**
     * Supported mail classes with their v3 API parameters.
     *
     * Each class lists the rateIndicators to query. The API is called once per
     * mailClass × rateIndicator combination, so we only list indicators that
     * are meaningful for parcel shipping (not flat-rate packaging).
     *
     * Key rateIndicator codes:
     *   SP = Single Piece (standard weight-based pricing)
     *   DR = Dimensional Rectangular (dimension-based pricing)
     *
     * processingCategory: MACHINABLE for standard parcels
     */
    protected array $mailClasses = [
        'PRIORITY_MAIL' => [
            'label' => 'Priority Mail',
            'rateIndicators' => ['DR', 'SP'],
            'processingCategory' => 'MACHINABLE',
        ],
        'PRIORITY_MAIL_EXPRESS' => [
            'label' => 'Priority Mail Express',
            'rateIndicators' => ['DR', 'SP'],
            'processingCategory' => 'MACHINABLE',
        ],
        'USPS_GROUND_ADVANTAGE' => [
            'label' => 'USPS Ground Advantage',
            'rateIndicators' => ['DR', 'SP'],
            'processingCategory' => 'MACHINABLE',
        ],
        'MEDIA_MAIL' => [
            'label' => 'Media Mail',
            'rateIndicators' => ['SP'],
            'processingCategory' => 'MACHINABLE',
        ],
        'LIBRARY_MAIL' => [
            'label' => 'Library Mail',
            'rateIndicators' => ['SP'],
            'processingCategory' => 'MACHINABLE',
        ],
    ];

    /**
     * Flat-rate packaging indicators — skip when auto-selecting postage.
     * These are for specific USPS packaging, not customer parcels.
     */
    protected array $flatRateIndicators = ['FB', 'FE', 'FP', 'PL', 'PM', 'PS'];

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
            $validationError = $this->validateRateLookupRequest($params);
            if ($validationError !== null) {
                return ['error' => $validationError];
            }

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
            $validationError = $this->validateRateLookupRequest($params);
            if ($validationError !== null) {
                return ['error' => $validationError];
            }

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
     * Route the package to the USPS endpoint that supports its characteristics.
     */
    protected function fetchRates(string $token, array $params): array
    {
        $analysis = $this->analyzePackage($params);

        if ($analysis['is_machinable']) {
            return $this->fetchBaseRates($token, $params);
        }

        return $this->fetchShippingOptionsRates($token, $params);
    }

    /**
     * Query Domestic Prices v3 for machinable mail class × rate indicator combinations.
     */
    protected function fetchBaseRates(string $token, array $params): array
    {
        $url = $this->baseUrl . '/prices/v3/base-rates/search';
        $originZip = $this->prepareZip(config('shipping.origin_zip', '46563'));
        $destZip = $this->prepareZip($params['zip'] ?? '');

        if (empty($destZip)) {
            throw new \RuntimeException('Destination ZIP code is required');
        }

        $weightLbs = $this->toDecimalPounds($params);
        ['length' => $length, 'width' => $width, 'height' => $height] = $this->extractDimensions($params);

        $rates = [];

        foreach ($this->mailClasses as $mailClass => $config) {
            foreach ($config['rateIndicators'] as $rateIndicator) {
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
                    'rateIndicator' => $rateIndicator,
                    'mailingDate' => now()->format('Y-m-d'),
                ];

                try {
                    $commercialRate = null;
                    $commercialBody = [];
                    $commercialError = null;
                    $retailRate = null;
                    $retailBody = [];
                    $description = '';
                    $fees = [];

                    $retailPayload = $payload + ['priceType' => 'RETAIL'];

                    $retailResponse = Http::withToken($token)
                        ->connectTimeout(10)
                        ->timeout(20)
                        ->retry(2, 500, throw: false)
                        ->post($url, $retailPayload);

                    $retailBody = $retailResponse->json() ?? [];

                    if ($retailResponse->successful()) {
                        $retailRate = $this->extractPrice($retailBody);
                        $description = $this->extractDescription($retailBody);
                        $fees = $this->extractFees($retailBody);
                    } else {
                        Log::channel('shipping')->info(
                            "USPS rate N/A for {$mailClass}/{$rateIndicator}: " . $this->formatApiError($retailBody, $retailResponse->status())
                        );
                        continue;
                    }

                    if (!empty($this->accountNumber)) {
                        try {
                            $commercialPayload = $payload + [
                                'priceType' => 'COMMERCIAL',
                                'accountType' => 'EPS',
                                'accountNumber' => $this->accountNumber,
                            ];

                            $commercialResponse = Http::withToken($token)
                                ->connectTimeout(10)
                                ->timeout(20)
                                ->retry(2, 500, throw: false)
                                ->post($url, $commercialPayload);

                            $commercialBody = $commercialResponse->json() ?? [];

                            if ($commercialResponse->successful()) {
                                $commercialRate = $this->extractPrice($commercialBody);
                                if (empty($fees)) {
                                    $fees = $this->extractFees($commercialBody);
                                }
                            } else {
                                $commercialError = $this->formatApiError($commercialBody, $commercialResponse->status());
                                Log::channel('shipping')->info("USPS commercial rate unavailable for {$mailClass}/{$rateIndicator}: {$commercialError}");
                            }
                        } catch (\Exception $e) {
                            Log::channel('shipping')->info("USPS commercial rate unavailable for {$mailClass}/{$rateIndicator}: " . $e->getMessage());
                            $commercialError = $e->getMessage();
                        }
                    }

                    $rate = $commercialRate ?? $retailRate;

                    if ($rate === null) {
                        continue;
                    }

                    $retailPostageId = $this->extractPostageId($retailBody);
                    $commercialPostageId = $this->extractPostageId($commercialBody);

                    $rates[] = [
                        'service' => $mailClass,
                        'label' => $config['label'],
                        'rateIndicator' => $rateIndicator,
                        'processingCategory' => $config['processingCategory'],
                        'rate' => $rate,
                        'rate_source' => $commercialRate !== null ? 'COMMERCIAL' : 'RETAIL',
                        'commercial_rate' => $commercialRate,
                        'retail_rate' => $retailRate,
                        'postage_id' => $retailPostageId ?: $commercialPostageId,
                        'retail_postage_id' => $retailPostageId,
                        'commercial_postage_id' => $commercialPostageId,
                        'commercial_error' => $commercialError,
                        'fees' => $fees,
                        'description' => $description,
                        'lookup_path' => 'BASE_RATES',
                    ];
                } catch (\Exception $e) {
                    Log::channel('shipping')->warning("USPS rate query failed for {$mailClass}/{$rateIndicator}: " . $e->getMessage());
                }
            }
        }

        return $rates;
    }

    /**
     * Query Shipping Options v3 for non-machinable and oversized parcels.
     */
    protected function fetchShippingOptionsRates(string $token, array $params): array
    {
        $url = $this->baseUrl . '/shipments/v3/options/search';
        $originZip = $this->prepareZip(config('shipping.origin_zip', '46563'));
        $destZip = $this->prepareZip($params['zip'] ?? '');

        if (empty($destZip)) {
            throw new \RuntimeException('Destination ZIP code is required');
        }

        $weightLbs = $this->toDecimalPounds($params);
        ['length' => $length, 'width' => $width, 'height' => $height] = $this->extractDimensions($params);

        $rates = [];

        foreach ($this->mailClasses as $mailClass => $config) {
            $retailRates = [];
            $commercialRates = [];
            $commercialError = null;

            try {
                $retailPayload = $this->buildShippingOptionsPayload(
                    $originZip,
                    $destZip,
                    $weightLbs,
                    $length,
                    $width,
                    $height,
                    $mailClass,
                    'RETAIL'
                );

                $retailResponse = Http::withToken($token)
                    ->connectTimeout(10)
                    ->timeout(20)
                    ->retry(2, 500, throw: false)
                    ->post($url, $retailPayload);

                $retailBody = $retailResponse->json() ?? [];

                if (!$retailResponse->successful()) {
                    Log::channel('shipping')->info(
                        "USPS shipping option N/A for {$mailClass}/RETAIL: " . $this->formatApiError($retailBody, $retailResponse->status())
                    );
                    continue;
                }

                $retailRates = $this->extractShippingOptionsRateRows($retailBody, $mailClass);
            } catch (\Exception $e) {
                Log::channel('shipping')->warning("USPS shipping option query failed for {$mailClass}/RETAIL: " . $e->getMessage());
                continue;
            }

            if (!empty($this->accountNumber)) {
                try {
                    $commercialPayload = $this->buildShippingOptionsPayload(
                        $originZip,
                        $destZip,
                        $weightLbs,
                        $length,
                        $width,
                        $height,
                        $mailClass,
                        'COMMERCIAL'
                    );

                    $commercialResponse = Http::withToken($token)
                        ->connectTimeout(10)
                        ->timeout(20)
                        ->retry(2, 500, throw: false)
                        ->post($url, $commercialPayload);

                    $commercialBody = $commercialResponse->json() ?? [];

                    if ($commercialResponse->successful()) {
                        $commercialRates = $this->extractShippingOptionsRateRows($commercialBody, $mailClass);
                    } else {
                        $commercialError = $this->formatApiError($commercialBody, $commercialResponse->status());
                        Log::channel('shipping')->info("USPS commercial shipping option unavailable for {$mailClass}: {$commercialError}");
                    }
                } catch (\Exception $e) {
                    $commercialError = $e->getMessage();
                    Log::channel('shipping')->info("USPS commercial shipping option unavailable for {$mailClass}: {$commercialError}");
                }
            }

            $rates = array_merge(
                $rates,
                $this->mergeShippingOptionRates($retailRates, $commercialRates, $commercialError)
            );
        }

        return $rates;
    }

    protected function buildShippingOptionsPayload(
        string $originZip,
        string $destZip,
        float $weightLbs,
        float $length,
        float $width,
        float $height,
        string $mailClass,
        string $priceType
    ): array {
        $pricingOption = ['priceType' => $priceType];

        if ($priceType === 'COMMERCIAL' && $this->accountNumber !== '') {
            $pricingOption['paymentAccount'] = [
                'accountType' => 'EPS',
                'accountNumber' => $this->accountNumber,
            ];
        }

        return [
            'originZIPCode' => $originZip,
            'destinationZIPCode' => $destZip,
            'destinationEntryFacilityType' => 'NONE',
            'packageDescription' => [
                'weight' => $weightLbs,
                'length' => $length,
                'width' => $width,
                'height' => $height,
                'mailClass' => $mailClass,
                'mailingDate' => now()->format('Y-m-d'),
            ],
            'pricingOptions' => [$pricingOption],
        ];
    }

    /**
     * Flatten Shipping Options responses into the shared rate row shape.
     */
    protected function extractShippingOptionsRateRows(array $body, string $requestedMailClass): array
    {
        $rows = [];

        foreach ($body['pricingOptions'] ?? [] as $pricingOption) {
            foreach ($pricingOption['shippingOptions'] ?? [] as $shippingOption) {
                $service = $this->normalizeMailClass($shippingOption['mailClass'] ?? $requestedMailClass);
                $label = $this->getServiceLabel($service, $requestedMailClass);

                foreach ($shippingOption['rateOptions'] ?? [] as $rateOption) {
                    $rateEntries = $rateOption['rates'] ?? [];
                    $useOptionTotal = count($rateEntries) === 1 && isset($rateOption['totalBasePrice']);

                    foreach ($rateEntries as $rateEntry) {
                        $priceBody = ['rates' => [$rateEntry]];
                        if ($useOptionTotal) {
                            $priceBody['totalBasePrice'] = $rateOption['totalBasePrice'];
                        }

                        $rows[] = [
                            'service' => $service,
                            'label' => $label,
                            'rateIndicator' => $rateEntry['rateIndicator'] ?? '',
                            'processingCategory' => $rateEntry['processingCategory'] ?? null,
                            'destinationEntryFacilityType' => $rateEntry['destinationEntryFacilityType'] ?? null,
                            'rate' => $this->extractPrice($priceBody),
                            'postage_id' => $this->extractPostageId($priceBody),
                            'fees' => $this->extractFees($priceBody),
                            'description' => $this->extractDescription($priceBody),
                            'lookup_path' => 'SHIPPING_OPTIONS',
                        ];
                    }
                }
            }
        }

        return array_values(array_filter($rows, function ($row) {
            return $row['rate'] !== null;
        }));
    }

    /**
     * Merge separate retail and commercial Shipping Options responses into the
     * calculator's standard retail/commercial comparison shape.
     */
    protected function mergeShippingOptionRates(
        array $retailRates,
        array $commercialRates,
        ?string $commercialError
    ): array {
        $retailByKey = [];
        foreach ($retailRates as $rate) {
            $retailByKey[$this->buildShippingOptionRateKey($rate)] = $rate;
        }

        $commercialByKey = [];
        foreach ($commercialRates as $rate) {
            $commercialByKey[$this->buildShippingOptionRateKey($rate)] = $rate;
        }

        $merged = [];
        $keys = array_values(array_unique(array_merge(array_keys($retailByKey), array_keys($commercialByKey))));

        foreach ($keys as $key) {
            $retail = $retailByKey[$key] ?? null;
            $commercial = $commercialByKey[$key] ?? null;
            $base = $commercial ?? $retail;

            if ($base === null) {
                continue;
            }

            $retailPostageId = $retail['postage_id'] ?? '';
            $commercialPostageId = $commercial['postage_id'] ?? '';

            $merged[] = [
                'service' => $base['service'],
                'label' => $base['label'],
                'rateIndicator' => $base['rateIndicator'],
                'processingCategory' => $commercial['processingCategory'] ?? $retail['processingCategory'] ?? null,
                'destinationEntryFacilityType' => $commercial['destinationEntryFacilityType'] ?? $retail['destinationEntryFacilityType'] ?? null,
                'rate' => $commercial['rate'] ?? $retail['rate'],
                'rate_source' => $commercial !== null ? 'COMMERCIAL' : 'RETAIL',
                'commercial_rate' => $commercial['rate'] ?? null,
                'retail_rate' => $retail['rate'] ?? null,
                'postage_id' => $retailPostageId !== '' ? $retailPostageId : $commercialPostageId,
                'retail_postage_id' => $retailPostageId,
                'commercial_postage_id' => $commercialPostageId,
                'commercial_error' => $commercial === null ? $commercialError : null,
                'fees' => !empty($commercial['fees']) ? $commercial['fees'] : ($retail['fees'] ?? []),
                'description' => $commercial['description'] ?? $retail['description'] ?? '',
                'lookup_path' => 'SHIPPING_OPTIONS',
            ];
        }

        return $merged;
    }

    protected function buildShippingOptionRateKey(array $rate): string
    {
        return implode('|', [
            $rate['service'] ?? '',
            $rate['processingCategory'] ?? '',
            $rate['rateIndicator'] ?? '',
            $rate['destinationEntryFacilityType'] ?? '',
            $rate['description'] ?? '',
        ]);
    }

    protected function formatApiError(array $body, int $status): string
    {
        return $body['error']['message']
            ?? $body['message']
            ?? $body['error']
            ?? 'HTTP ' . $status;
    }

    protected function getServiceLabel(string $mailClass, ?string $fallbackMailClass = null): string
    {
        $normalizedMailClass = $this->normalizeMailClass($mailClass);
        if (isset($this->mailClasses[$normalizedMailClass]['label'])) {
            return $this->mailClasses[$normalizedMailClass]['label'];
        }

        $fallbackMailClass = $fallbackMailClass !== null ? $this->normalizeMailClass($fallbackMailClass) : null;
        if ($fallbackMailClass !== null && isset($this->mailClasses[$fallbackMailClass]['label'])) {
            return $this->mailClasses[$fallbackMailClass]['label'];
        }

        return ucwords(strtolower(str_replace('_', ' ', $normalizedMailClass)));
    }

    /**
     * Extract the total price from a USPS v3 rate response.
     *
     * Uses totalBasePrice (includes base rate + surcharges/fees) as the
     * primary source. Falls back to rates[0].price + fees if needed.
     *
     * Response structure:
     *   { "totalBasePrice": 12.50, "rates": [{ "price": 8.50, "fees": [{ "name": "...", "price": 4.00 }] }] }
     */
    protected function extractPrice(array $body): ?float
    {
        // Primary: totalBasePrice includes base rate + all applicable fees/surcharges
        if (!empty($body['totalBasePrice'])) {
            return (float) $body['totalBasePrice'];
        }

        // Fallback: sum rates[0].price + rates[0].fees[].price
        if (!empty($body['rates'][0]['price'])) {
            $price = (float) $body['rates'][0]['price'];
            foreach ($body['rates'][0]['fees'] ?? [] as $fee) {
                $price += (float) ($fee['price'] ?? 0);
            }
            return $price;
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
     * Extract additional fees/surcharges from a USPS v3 rate response.
     *
     * Returns: [['name' => 'Nonstandard Fee', 'price' => 4.00], ...]
     */
    protected function extractFees(array $body): array
    {
        $fees = [];
        foreach ($body['rates'][0]['fees'] ?? [] as $fee) {
            if (!empty($fee['name']) && ($fee['price'] ?? 0) > 0) {
                $fees[] = [
                    'name' => $fee['name'],
                    'price' => (float) $fee['price'],
                ];
            }
        }
        return $fees;
    }

    /**
     * Select the best rate for automatic postage calculation.
     *
     * Priority: match order's mail class → prefer DR indicator → skip flat rate indicators.
     * Returns null if no suitable rate found.
     */
    public function selectAutoRate(array $rates, string $orderMailClass): ?array
    {
        $orderMailClass = $this->normalizeMailClass($orderMailClass);

        // Filter to rates matching the order's mail class
        $matching = collect($rates)->filter(function ($rate) use ($orderMailClass) {
            return $rate['service'] === $orderMailClass;
        });

        // Exclude flat rate packaging indicators
        $matching = $matching->filter(function ($rate) {
            return !in_array($rate['rateIndicator'] ?? '', $this->flatRateIndicators);
        });

        if ($matching->isEmpty()) {
            return null;
        }

        // Prefer DR (Dimensional Rectangular) over other indicators
        $dr = $matching->firstWhere('rateIndicator', 'DR');
        if ($dr) {
            return $dr;
        }

        return $matching->first();
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
     * USPS pricing requires all three dimensions.
     */
    public function hasCompleteDimensions(array $params): bool
    {
        return (float) ($params['length'] ?? 0) > 0
            && (float) ($params['width'] ?? 0) > 0
            && (float) ($params['height'] ?? 0) > 0;
    }

    /**
     * Hard-limit validation before calling USPS. Non-machinable and oversized
     * parcels are routed through Shipping Options instead of being rejected.
     */
    public function validateRateLookupRequest(array $params): ?string
    {
        $analysis = $this->analyzePackage($params);

        if ($analysis['exceeds_usps_limits']) {
            return sprintf(
                'Package exceeds USPS maximum parcel limits (%.2f lb, %.1f" length + girth; USPS max is 70 lb and 130").',
                $analysis['weight_lbs'],
                $analysis['length_plus_girth']
            );
        }

        return null;
    }

    /**
     * USPS Shipping Options is required whenever the package is valid but not machinable.
     */
    public function usesShippingOptionsLookup(array $params): bool
    {
        $analysis = $this->analyzePackage($params);

        return !$analysis['exceeds_usps_limits'] && !$analysis['is_machinable'];
    }

    /**
     * USPS parcel standards used to decide whether the standard lookup path applies.
     */
    public function analyzePackage(array $params): array
    {
        ['length' => $length, 'width' => $width, 'height' => $height] = $this->extractDimensions($params);

        $dimensions = [$length, $width, $height];
        rsort($dimensions, SORT_NUMERIC);

        $longestSide = (float) ($dimensions[0] ?? 0);
        $middleSide = (float) ($dimensions[1] ?? 0);
        $shortestSide = (float) ($dimensions[2] ?? 0);

        $weightLbs = $this->toDecimalPounds($params);
        $lengthPlusGirth = $longestSide + (2 * ($middleSide + $shortestSide));

        $isMachinable = $weightLbs <= self::MACHINABLE_MAX_WEIGHT_LBS
            && $longestSide <= self::MACHINABLE_MAX_LENGTH_IN
            && $middleSide <= self::MACHINABLE_MAX_WIDTH_IN
            && $shortestSide <= self::MACHINABLE_MAX_HEIGHT_IN
            && $lengthPlusGirth <= self::STANDARD_MAX_LENGTH_PLUS_GIRTH_IN;

        $nonMachinableReasons = $this->buildNonMachinableReasons(
            $weightLbs,
            $longestSide,
            $middleSide,
            $shortestSide,
            $lengthPlusGirth
        );

        return [
            'weight_lbs' => $weightLbs,
            'longest_side' => $longestSide,
            'middle_side' => $middleSide,
            'shortest_side' => $shortestSide,
            'length_plus_girth' => $lengthPlusGirth,
            'is_machinable' => $isMachinable,
            'non_machinable_reasons' => $nonMachinableReasons,
            'non_machinable_reason_text' => $this->formatReasonList($nonMachinableReasons),
            'is_oversized' => $lengthPlusGirth > self::STANDARD_MAX_LENGTH_PLUS_GIRTH_IN
                && $lengthPlusGirth <= self::USPS_MAX_LENGTH_PLUS_GIRTH_IN
                && $weightLbs <= self::USPS_MAX_WEIGHT_LBS,
            'exceeds_usps_limits' => $weightLbs > self::USPS_MAX_WEIGHT_LBS
                || $lengthPlusGirth > self::USPS_MAX_LENGTH_PLUS_GIRTH_IN,
        ];
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

    /**
     * Normalize and validate dimensions for USPS rate lookups.
     */
    protected function extractDimensions(array $params): array
    {
        $dimensions = [
            'length' => (float) ($params['length'] ?? 0),
            'width' => (float) ($params['width'] ?? 0),
            'height' => (float) ($params['height'] ?? 0),
        ];

        if (!$this->hasCompleteDimensions($dimensions)) {
            throw new \RuntimeException('Package dimensions are required for USPS rate lookups.');
        }

        return $dimensions;
    }

    protected function toDecimalPounds(array $params): float
    {
        $pounds = (int) ($params['pounds'] ?? 0);
        $ounces = (int) ($params['ounces'] ?? 0);
        $weightLbs = round(($pounds * 16 + $ounces) / 16, 4);

        return $weightLbs > 0 ? $weightLbs : 0.0625;
    }

    /**
     * Explain which machinable rule(s) the parcel exceeds.
     */
    protected function buildNonMachinableReasons(
        float $weightLbs,
        float $longestSide,
        float $middleSide,
        float $shortestSide,
        float $lengthPlusGirth
    ): array {
        $reasons = [];

        if ($weightLbs > self::MACHINABLE_MAX_WEIGHT_LBS) {
            $reasons[] = sprintf('weight %.2f lb exceeds the 25 lb machinable limit', $weightLbs);
        }

        if ($longestSide > self::MACHINABLE_MAX_LENGTH_IN) {
            $reasons[] = sprintf('longest side %.1f" exceeds the 22" machinable limit', $longestSide);
        }

        if ($middleSide > self::MACHINABLE_MAX_WIDTH_IN) {
            $reasons[] = sprintf('second side %.1f" exceeds the 18" machinable limit', $middleSide);
        }

        if ($shortestSide > self::MACHINABLE_MAX_HEIGHT_IN) {
            $reasons[] = sprintf('third side %.1f" exceeds the 15" machinable limit', $shortestSide);
        }

        if ($lengthPlusGirth > self::STANDARD_MAX_LENGTH_PLUS_GIRTH_IN) {
            $reasons[] = sprintf('length + girth %.1f" exceeds the 108" standard parcel limit', $lengthPlusGirth);
        }

        return $reasons;
    }

    protected function formatReasonList(array $reasons): string
    {
        if (empty($reasons)) {
            return 'it does not fit the machinable parcel rules';
        }

        if (count($reasons) === 1) {
            return $reasons[0];
        }

        $lastReason = array_pop($reasons);

        return implode(', ', $reasons) . ', and ' . $lastReason;
    }

    /**
     * USPS may return a product/quote identifier such as SKU.
     */
    protected function extractPostageId(array $body): string
    {
        $rate = $body['rates'][0] ?? [];
        $candidates = [
            $body['SKU'] ?? null,
            $body['sku'] ?? null,
            $body['productId'] ?? null,
            $body['productID'] ?? null,
            $rate['SKU'] ?? null,
            $rate['sku'] ?? null,
            $rate['productId'] ?? null,
            $rate['productID'] ?? null,
        ];

        foreach ($candidates as $candidate) {
            if (is_scalar($candidate)) {
                $value = trim((string) $candidate);
                if ($value !== '') {
                    return $value;
                }
            }
        }

        return '';
    }
}
