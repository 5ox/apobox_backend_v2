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
     * Track via FedEx SOAP API.
     */
    protected function trackFedex(string $trackingNumber): array
    {
        $key = config('shipping.fedex.key');
        $password = config('shipping.fedex.password');
        $account = config('shipping.fedex.account');
        $meter = config('shipping.fedex.meter');

        if (empty($key) || empty($password)) {
            return ['error' => 'FedEx not configured'];
        }

        try {
            $wsdlPath = storage_path('wsdl/TrackService_v19.wsdl');
            if (!file_exists($wsdlPath)) {
                // Fall back — no WSDL available
                return $this->trackFedexRest($trackingNumber);
            }

            $client = new \SoapClient($wsdlPath, ['trace' => true]);

            $request = [
                'WebAuthenticationDetail' => ['UserCredential' => [
                    'Key' => $key,
                    'Password' => $password,
                ]],
                'ClientDetail' => [
                    'AccountNumber' => $account,
                    'MeterNumber' => $meter,
                ],
                'Version' => ['ServiceId' => 'trck', 'Major' => '19', 'Intermediate' => '0', 'Minor' => '0'],
                'SelectionDetails' => [
                    'PackageIdentifier' => [
                        'Type' => 'TRACKING_NUMBER_OR_DOORTAG',
                        'Value' => $trackingNumber,
                    ],
                ],
                'ProcessingOptions' => 'INCLUDE_DETAILED_SCANS',
            ];

            $response = $client->track($request);

            $detail = $response->CompletedTrackDetails->TrackDetails ?? null;
            if (!$detail) {
                return ['error' => 'FedEx: no tracking data'];
            }

            $status = $detail->StatusDetail->Description ?? 'Unknown';
            $deliveryDate = $detail->EstimatedDeliveryTimestamp ?? null;

            $events = [];
            $scans = $detail->Events ?? [];
            if (!is_array($scans)) {
                $scans = [$scans];
            }

            foreach ($scans as $scan) {
                $addr = $scan->Address ?? null;
                $location = '';
                if ($addr) {
                    $location = implode(', ', array_filter([
                        $addr->City ?? '',
                        $addr->StateOrProvinceCode ?? '',
                        $addr->CountryCode ?? '',
                    ]));
                }

                $events[] = [
                    'date' => $this->formatDate($scan->Timestamp ?? ''),
                    'description' => $scan->EventDescription ?? '',
                    'location' => $location,
                ];
            }

            return [
                'carrier' => 'FedEx',
                'tracking_number' => $trackingNumber,
                'status' => $status,
                'summary' => $status,
                'estimated_delivery' => $deliveryDate ? $this->formatDate($deliveryDate) : null,
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
     * FedEx REST fallback if no SOAP WSDL.
     */
    protected function trackFedexRest(string $trackingNumber): array
    {
        return [
            'carrier' => 'FedEx',
            'tracking_number' => $trackingNumber,
            'status' => 'Unavailable',
            'summary' => 'FedEx tracking WSDL not found. Upload TrackService_v19.wsdl to storage/wsdl/.',
            'estimated_delivery' => null,
            'events' => [],
        ];
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
