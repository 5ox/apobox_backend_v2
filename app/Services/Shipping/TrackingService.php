<?php

namespace App\Services\Shipping;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Unified package tracking service.
 *
 * Fetches tracking events from USPS, UPS, and FedEx APIs
 * and returns them in a common format.
 */
class TrackingService
{
    /**
     * Detect carrier from tracking number format.
     *
     * Standard formats:
     *  USPS  — 20-22 digits starting with 9 (e.g. 9400, 9200, 9261)
     *          30-34 digits starting with 420 (routing barcode)
     *          13 chars: 2 letters + 9 digits + "US" (international)
     *  UPS   — Starts with "1Z" + 16 alphanumeric
     *  FedEx — 12 digits, 15 digits, 20 digits starting with "96", 22 digits starting with "61"
     *  DHL   — 10 digits, or starts with "JD" + 18 digits, or 5000-5999 range start
     */
    public static function detectCarrier(string $trackingNumber): string
    {
        $tn = strtoupper(trim($trackingNumber));
        $len = strlen($tn);

        // UPS: starts with 1Z + 16 alphanumeric = 18 chars total
        if (str_starts_with($tn, '1Z') && $len === 18) {
            return 'UPS';
        }

        // USPS international: 2 letters + 9 digits + "US" = 13 chars
        if ($len === 13 && preg_match('/^[A-Z]{2}\d{9}US$/', $tn)) {
            return 'USPS';
        }

        // DHL: starts with JD + 18 digits
        if (str_starts_with($tn, 'JD') && $len === 20 && ctype_digit(substr($tn, 2))) {
            return 'DHL';
        }

        // Numeric-only formats
        if (ctype_digit($tn)) {
            // USPS routing barcode: 30-34 digits starting with 420
            if ($len >= 30 && $len <= 34 && str_starts_with($tn, '420')) {
                return 'USPS';
            }

            // USPS: 20-22 digits starting with 9
            if (($len >= 20 && $len <= 22) && str_starts_with($tn, '9')) {
                return 'USPS';
            }

            // USPS: 26 digits starting with 9 (registered mail / other)
            if ($len === 26 && str_starts_with($tn, '9')) {
                return 'USPS';
            }

            // FedEx Ground / SmartPost: 20 digits starting with 96
            if ($len === 20 && str_starts_with($tn, '96')) {
                return 'FEDEX';
            }

            // FedEx Ground: 22 digits starting with 61
            if ($len === 22 && str_starts_with($tn, '61')) {
                return 'FEDEX';
            }

            // FedEx: 15 digits (Ground/Home Delivery)
            if ($len === 15) {
                return 'FEDEX';
            }

            // FedEx Express: 12 digits
            if ($len === 12) {
                return 'FEDEX';
            }

            // DHL: 10-11 digits
            if ($len === 10 || $len === 11) {
                return 'DHL';
            }
        }

        // Fallback: unknown
        return '';
    }

    /**
     * Track a package by number and carrier.
     *
     * @return array ['status' => '', 'summary' => '', 'estimated_delivery' => '', 'events' => [...]]
     */
    public function track(string $trackingNumber, string $carrier): array
    {
        $trackingNumber = trim($trackingNumber);
        $carrier = strtoupper($carrier);

        // Cache for 15 minutes to avoid hammering carrier APIs
        $cacheKey = "tracking:{$carrier}:{$trackingNumber}";

        return Cache::remember($cacheKey, 900, function () use ($trackingNumber, $carrier) {
            return match ($carrier) {
                'USPS' => $this->trackUsps($trackingNumber),
                'UPS' => $this->trackUps($trackingNumber),
                'FEDEX' => $this->trackFedex($trackingNumber),
                'DHL' => $this->trackDhl($trackingNumber),
                default => ['error' => "Unsupported carrier: {$carrier}"],
            };
        });
    }

    /**
     * Track via USPS Tracking v3 REST API.
     */
    protected function trackUsps(string $trackingNumber): array
    {
        try {
            $token = $this->getUspsToken();

            $response = Http::withToken($token)
                ->timeout(15)
                ->get("https://apis.usps.com/tracking/v3/tracking/{$trackingNumber}", [
                    'expand' => 'DETAIL',
                ]);

            if (!$response->successful()) {
                Log::channel('shipping')->warning('USPS tracking failed', [
                    'tracking' => $trackingNumber,
                    'status' => $response->status(),
                ]);
                return ['error' => 'USPS tracking unavailable (HTTP ' . $response->status() . ')'];
            }

            $data = $response->json();
            $summary = $data['statusSummary'] ?? $data['status'] ?? 'Unknown';
            $estimatedDelivery = $data['expectedDeliveryDate'] ?? null;

            $events = [];
            foreach ($data['trackingEvents'] ?? [] as $event) {
                $location = implode(', ', array_filter([
                    $event['eventCity'] ?? '',
                    $event['eventState'] ?? '',
                    $event['eventZIPCode'] ?? '',
                ]));

                $events[] = [
                    'date' => $this->formatDate($event['eventTimestamp'] ?? $event['eventDate'] ?? ''),
                    'description' => $event['eventType'] ?? $event['event'] ?? '',
                    'location' => $location,
                ];
            }

            return [
                'carrier' => 'USPS',
                'tracking_number' => $trackingNumber,
                'status' => $data['status'] ?? 'Unknown',
                'summary' => $summary,
                'estimated_delivery' => $estimatedDelivery ? $this->formatDate($estimatedDelivery) : null,
                'events' => $events,
            ];
        } catch (Exception $e) {
            Log::channel('shipping')->error('USPS tracking exception', [
                'tracking' => $trackingNumber,
                'error' => $e->getMessage(),
            ]);
            return ['error' => 'USPS tracking error: ' . $e->getMessage()];
        }
    }

    /**
     * Track via UPS REST API.
     */
    protected function trackUps(string $trackingNumber): array
    {
        $clientId = config('shipping.ups.client_id');
        $clientSecret = config('shipping.ups.client_secret');

        if (empty($clientId) || empty($clientSecret)) {
            return ['error' => 'UPS not configured'];
        }

        try {
            $token = $this->getUpsToken();

            $response = Http::withToken($token)
                ->timeout(15)
                ->get("https://onlinetools.ups.com/api/track/v1/details/{$trackingNumber}");

            if (!$response->successful()) {
                Log::channel('shipping')->warning('UPS tracking failed', [
                    'tracking' => $trackingNumber,
                    'status' => $response->status(),
                ]);
                return ['error' => 'UPS tracking unavailable (HTTP ' . $response->status() . ')'];
            }

            $data = $response->json();
            $shipment = $data['trackResponse']['shipment'][0] ?? [];
            $package = $shipment['package'][0] ?? [];
            $activity = $package['activity'] ?? [];

            $currentStatus = $package['currentStatus']['description'] ?? ($activity[0]['status']['description'] ?? 'Unknown');
            $deliveryDate = $package['deliveryDate'][0]['date'] ?? null;

            $events = [];
            foreach ($activity as $act) {
                $loc = $act['location']['address'] ?? [];
                $location = implode(', ', array_filter([
                    $loc['city'] ?? '',
                    $loc['stateProvince'] ?? '',
                    $loc['countryCode'] ?? '',
                ]));

                $date = ($act['date'] ?? '') . ' ' . ($act['time'] ?? '');

                $events[] = [
                    'date' => $this->formatDate(trim($date)),
                    'description' => $act['status']['description'] ?? '',
                    'location' => $location,
                ];
            }

            return [
                'carrier' => 'UPS',
                'tracking_number' => $trackingNumber,
                'status' => $currentStatus,
                'summary' => $currentStatus,
                'estimated_delivery' => $deliveryDate ? $this->formatDate($deliveryDate) : null,
                'events' => $events,
            ];
        } catch (Exception $e) {
            Log::channel('shipping')->error('UPS tracking exception', [
                'tracking' => $trackingNumber,
                'error' => $e->getMessage(),
            ]);
            return ['error' => 'UPS tracking error: ' . $e->getMessage()];
        }
    }

    /**
     * Track via FedEx REST Track API v1.
     *
     * Uses OAuth 2.0 client credentials (FEDEX_KEY = API Key, FEDEX_PASSWORD = Secret Key).
     * Endpoint: POST https://apis.fedex.com/track/v1/trackingnumbers
     */
    protected function trackFedex(string $trackingNumber): array
    {
        $apiKey = config('shipping.fedex.key');
        $secretKey = config('shipping.fedex.password');

        if (empty($apiKey) || empty($secretKey)) {
            return ['error' => 'FedEx not configured'];
        }

        try {
            $token = $this->getFedexToken();

            $response = Http::withToken($token)
                ->timeout(15)
                ->post('https://apis.fedex.com/track/v1/trackingnumbers', [
                    'trackingInfo' => [
                        [
                            'trackingNumberInfo' => [
                                'trackingNumber' => $trackingNumber,
                            ],
                        ],
                    ],
                    'includeDetailedScans' => true,
                ]);

            if (!$response->successful()) {
                Log::channel('shipping')->warning('FedEx tracking failed', [
                    'tracking' => $trackingNumber,
                    'status' => $response->status(),
                    'body' => $response->json(),
                ]);
                return ['error' => 'FedEx tracking unavailable (HTTP ' . $response->status() . ')'];
            }

            $data = $response->json();
            $trackResult = $data['output']['completeTrackResults'][0]['trackResults'][0] ?? null;

            if (!$trackResult) {
                return ['error' => 'FedEx: no tracking data returned'];
            }

            // Check for tracking-level errors
            if (!empty($trackResult['error'])) {
                $errMsg = $trackResult['error']['message'] ?? 'Unknown FedEx error';
                return ['error' => "FedEx: {$errMsg}"];
            }

            // Current status
            $latestStatus = $trackResult['latestStatusDetail'] ?? [];
            $status = $latestStatus['statusByLocale']
                ?? $latestStatus['description']
                ?? $latestStatus['derivedCode']
                ?? 'Unknown';

            // Estimated delivery
            $estimatedDelivery = null;
            foreach ($trackResult['dateAndTimes'] ?? [] as $dt) {
                if (in_array($dt['type'] ?? '', ['ESTIMATED_DELIVERY', 'ACTUAL_DELIVERY'])) {
                    $estimatedDelivery = $dt['dateTime'] ?? null;
                    break;
                }
            }

            // Scan events
            $events = [];
            foreach ($trackResult['scanEvents'] ?? [] as $scan) {
                $loc = $scan['scanLocation'] ?? [];
                $location = implode(', ', array_filter([
                    $loc['city'] ?? '',
                    $loc['stateOrProvinceCode'] ?? '',
                    $loc['countryCode'] ?? '',
                ]));

                $events[] = [
                    'date' => $this->formatDate($scan['date'] ?? ''),
                    'description' => $scan['eventDescription'] ?? $scan['derivedStatus'] ?? '',
                    'location' => $location,
                ];
            }

            return [
                'carrier' => 'FedEx',
                'tracking_number' => $trackingNumber,
                'status' => $status,
                'summary' => $status,
                'estimated_delivery' => $estimatedDelivery ? $this->formatDate($estimatedDelivery) : null,
                'events' => $events,
            ];
        } catch (Exception $e) {
            Log::channel('shipping')->error('FedEx tracking exception', [
                'tracking' => $trackingNumber,
                'error' => $e->getMessage(),
            ]);
            return ['error' => 'FedEx tracking error: ' . $e->getMessage()];
        }
    }

    /**
     * DHL — not implemented yet.
     */
    protected function trackDhl(string $trackingNumber): array
    {
        return [
            'carrier' => 'DHL',
            'tracking_number' => $trackingNumber,
            'status' => 'Unavailable',
            'summary' => 'DHL tracking not yet configured.',
            'estimated_delivery' => null,
            'events' => [],
        ];
    }

    /**
     * Get USPS OAuth token (reuses existing cache from UspsService).
     */
    protected function getUspsToken(): string
    {
        return Cache::remember('usps_oauth_token', 7 * 3600, function () {
            $response = Http::post('https://apis.usps.com/oauth2/v3/token', [
                'client_id' => config('shipping.usps.client_id'),
                'client_secret' => config('shipping.usps.client_secret'),
                'grant_type' => 'client_credentials',
            ]);

            if (!$response->successful() || !$response->json('access_token')) {
                throw new Exception('USPS OAuth failed: ' . ($response->json('error_description') ?? 'unknown'));
            }

            return $response->json('access_token');
        });
    }

    /**
     * Get UPS OAuth token (reuses existing cache from UpsService).
     */
    protected function getUpsToken(): string
    {
        return Cache::remember('ups_oauth_token', 3.5 * 3600, function () {
            $response = Http::withBasicAuth(
                config('shipping.ups.client_id'),
                config('shipping.ups.client_secret')
            )->asForm()->post('https://onlinetools.ups.com/security/v1/oauth/token', [
                'grant_type' => 'client_credentials',
            ]);

            if (!$response->successful() || !$response->json('access_token')) {
                throw new Exception('UPS OAuth failed');
            }

            return $response->json('access_token');
        });
    }

    /**
     * Get FedEx OAuth token.
     *
     * FedEx REST API uses client_credentials grant.
     * Token expires after 60 minutes; we cache for 50 minutes.
     * FEDEX_KEY = API Key (client_id), FEDEX_PASSWORD = Secret Key (client_secret).
     */
    protected function getFedexToken(): string
    {
        return Cache::remember('fedex_oauth_token', 50 * 60, function () {
            $response = Http::asForm()->post('https://apis.fedex.com/oauth/token', [
                'grant_type' => 'client_credentials',
                'client_id' => config('shipping.fedex.key'),
                'client_secret' => config('shipping.fedex.password'),
            ]);

            if (!$response->successful() || !$response->json('access_token')) {
                throw new Exception('FedEx OAuth failed: ' . ($response->json('errors.0.message') ?? $response->body()));
            }

            return $response->json('access_token');
        });
    }

    /**
     * Format various date formats into a readable string.
     */
    protected function formatDate(string $raw): string
    {
        if (empty($raw)) {
            return '';
        }

        // UPS format: YYYYMMDD HHMMSS
        if (preg_match('/^(\d{4})(\d{2})(\d{2})\s*(\d{2})?(\d{2})?(\d{2})?$/', $raw, $m)) {
            $dt = "{$m[1]}-{$m[2]}-{$m[3]}";
            if (!empty($m[4])) {
                $dt .= " {$m[4]}:{$m[5]}:{$m[6]}";
            }
            $raw = $dt;
        }

        try {
            $dt = new \DateTime($raw);
            return $dt->format('m/d/Y g:ia');
        } catch (Exception $e) {
            return $raw;
        }
    }
}
