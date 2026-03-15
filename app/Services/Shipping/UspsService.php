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
     * Query Domestic Prices v3 for each mail class × rate indicator.
     */
    protected function fetchRates(string $token, array $params): array
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

                    // --- Retail rate (always try — no account needed) ---
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
                        // Not all rateIndicator × mailClass combos are valid — info-level only
                        $apiError = $retailBody['error']['message']
                            ?? $retailBody['message']
                            ?? $retailBody['error']
                            ?? 'HTTP ' . $retailResponse->status();
                        Log::channel('shipping')->info("USPS rate N/A for {$mailClass}/{$rateIndicator}: {$apiError}");
                        continue; // this indicator doesn't apply to this class
                    }

                    // --- Commercial (our discounted) rate — only if account is configured ---
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
                                $commercialError = $commercialBody['error']['message']
                                    ?? $commercialBody['message']
                                    ?? $commercialBody['error']
                                    ?? 'HTTP ' . $commercialResponse->status();
                                Log::channel('shipping')->info("USPS commercial rate unavailable for {$mailClass}/{$rateIndicator}: {$commercialError}");
                            }
                        } catch (\Exception $e) {
                            Log::channel('shipping')->info("USPS commercial rate unavailable for {$mailClass}/{$rateIndicator}: " . $e->getMessage());
                            $commercialError = $e->getMessage();
                        }
                    }

                    // Use commercial rate if available, otherwise retail
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
                        'rate' => $commercialRate ?? $retailRate,
                        'rate_source' => $commercialRate !== null ? 'COMMERCIAL' : 'RETAIL',
                        'commercial_rate' => $commercialRate,
                        'retail_rate' => $retailRate,
                        'postage_id' => $retailPostageId ?: $commercialPostageId,
                        'retail_postage_id' => $retailPostageId,
                        'commercial_postage_id' => $commercialPostageId,
                        'commercial_error' => $commercialError,
                        'fees' => $fees,
                        'description' => $description,
                    ];
                } catch (\Exception $e) {
                    Log::channel('shipping')->warning("USPS rate query failed for {$mailClass}/{$rateIndicator}: " . $e->getMessage());
                }
            }
        }

        return $rates;
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
     * Current lookup path only supports machinable USPS parcel pricing.
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

        if ($analysis['is_machinable']) {
            return null;
        }

        if ($analysis['is_oversized']) {
            return sprintf(
                'Package is oversized at %.1f" length + girth. Priority Mail, Priority Mail Express, Media Mail, and Library Mail are unavailable at this size. USPS Ground Advantage oversized pricing requires a separate lookup path; this calculator currently supports machinable USPS parcels only.',
                $analysis['length_plus_girth']
            );
        }

        return sprintf(
            'Package is non-machinable for the current USPS lookup path (%.2f lb, %.1f" x %.1f" x %.1f) because %s. This calculator currently supports machinable USPS parcels only; Ground Advantage nonstandard pricing requires a separate lookup path.',
            $analysis['weight_lbs'],
            $analysis['longest_side'],
            $analysis['middle_side'],
            $analysis['shortest_side'],
            $analysis['non_machinable_reason_text']
        );
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
