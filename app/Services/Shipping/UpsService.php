<?php

namespace App\Services\Shipping;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * UPS REST API service (OAuth 2.0).
 *
 * Handles rating and label creation via the UPS Developer API.
 * @see https://developer.ups.com/api/reference
 */
class UpsService
{
    protected string $clientId;
    protected string $clientSecret;
    protected string $accountNumber;
    protected array $shipper;
    protected string $baseUrl = 'https://onlinetools.ups.com';

    public function __construct()
    {
        $this->clientId = config('shipping.ups.client_id', '');
        $this->clientSecret = config('shipping.ups.client_secret', '');
        $this->accountNumber = config('shipping.ups.account_number', '');
        $this->shipper = config('shipping.ups.shipper', []);
    }

    /**
     * Check if UPS credentials are configured.
     */
    public function isConfigured(): bool
    {
        return !empty($this->clientId) && !empty($this->clientSecret) && !empty($this->accountNumber);
    }

    /**
     * Get an OAuth 2.0 access token (cached for 3.5 hours — tokens last 4h).
     */
    protected function getAccessToken(): string
    {
        return Cache::remember('ups_oauth_token', 3.5 * 3600, function () {
            $response = Http::withBasicAuth($this->clientId, $this->clientSecret)
                ->asForm()
                ->post($this->baseUrl . '/security/v1/oauth/token', [
                    'grant_type' => 'client_credentials',
                ]);

            if (!$response->successful() || !$response->json('access_token')) {
                $error = $response->json('response.errors.0.message', 'OAuth token request failed');
                throw new Exception('UPS OAuth Error: ' . $error);
            }

            return $response->json('access_token');
        });
    }

    /**
     * Build an authenticated HTTP client.
     */
    protected function client(): \Illuminate\Http\Client\PendingRequest
    {
        $token = $this->getAccessToken();

        return Http::withToken($token)
            ->acceptJson()
            ->asJson()
            ->timeout(30);
    }

    /**
     * Get shipping rates for a package.
     *
     * @param array $recipient ['AddressLine' => '...', 'City' => '...', 'StateProvinceCode' => '...', 'PostalCode' => '...', 'CountryCode' => '...']
     * @param float $weightLbs Weight in pounds
     * @return array Array of rate entries or ['error' => 'message']
     */
    public function getRate(array $recipient, float $weightLbs): array
    {
        if (!$this->isConfigured()) {
            return ['error' => 'UPS not configured'];
        }

        try {
            $payload = [
                'RateRequest' => [
                    'Request' => [
                        'TransactionReference' => ['CustomerContext' => 'APO Box Rate'],
                    ],
                    'Shipment' => [
                        'Shipper' => $this->shipper,
                        'ShipTo' => [
                            'Address' => $recipient,
                        ],
                        'ShipFrom' => [
                            'Address' => $this->shipper['Address'],
                        ],
                        'Package' => [
                            'PackagingType' => ['Code' => '02'], // Customer Supplied Package
                            'PackageWeight' => [
                                'UnitOfMeasurement' => ['Code' => 'LBS'],
                                'Weight' => (string) round($weightLbs, 1),
                            ],
                        ],
                    ],
                ],
            ];

            $response = $this->client()->post($this->baseUrl . '/api/rating/v2403/Shop', $payload);

            if (!$response->successful()) {
                $errorMsg = $response->json('response.errors.0.message', "HTTP {$response->status()}");
                Log::channel('shipping')->error('UPS Rate Error', [
                    'status' => $response->status(),
                    'error' => $errorMsg,
                ]);
                return ['error' => $errorMsg];
            }

            return $this->parseRateResponse($response->json());
        } catch (Exception $e) {
            Log::channel('shipping')->error('UPS Rate Exception: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Create a shipping label.
     *
     * @param array $recipient Address array
     * @param float $weightLbs Weight in pounds
     * @param array $options Optional: contact info, service code, etc.
     * @return array ['success' => bool, 'tracking_number' => '', 'label_data' => ''] or ['error' => '']
     */
    public function printLabel(array $recipient, float $weightLbs, array $options = []): array
    {
        if (!$this->isConfigured()) {
            return ['error' => 'UPS not configured'];
        }

        $labelType = config('shipping.ups.label.type', 'ZPL');
        $serviceCode = $options['service_code'] ?? '03'; // 03 = UPS Ground

        try {
            $payload = [
                'ShipmentRequest' => [
                    'Request' => [
                        'TransactionReference' => ['CustomerContext' => 'APO Box Ship'],
                    ],
                    'Shipment' => [
                        'Description' => $options['description'] ?? 'APO Box Shipment',
                        'Shipper' => $this->shipper,
                        'ShipTo' => [
                            'Name' => $options['contact']['PersonName'] ?? '',
                            'Phone' => ['Number' => $options['contact']['PhoneNumber'] ?? ''],
                            'Address' => array_merge($recipient, [
                                'CountryCode' => $recipient['CountryCode'] ?? 'US',
                            ]),
                        ],
                        'ShipFrom' => [
                            'Name' => $this->shipper['Name'],
                            'Address' => $this->shipper['Address'],
                        ],
                        'PaymentInformation' => [
                            'ShipmentCharge' => [
                                'Type' => '01', // Transportation
                                'BillShipper' => [
                                    'AccountNumber' => $this->accountNumber,
                                ],
                            ],
                        ],
                        'Service' => [
                            'Code' => $serviceCode,
                        ],
                        'Package' => [
                            'Packaging' => ['Code' => '02'], // Customer Supplied
                            'PackageWeight' => [
                                'UnitOfMeasurement' => ['Code' => 'LBS'],
                                'Weight' => (string) round($weightLbs, 1),
                            ],
                        ],
                    ],
                    'LabelSpecification' => [
                        'LabelImageFormat' => ['Code' => $labelType],
                        'LabelStockSize' => [
                            'Height' => '6',
                            'Width' => '4',
                        ],
                    ],
                ],
            ];

            $response = $this->client()->post(
                $this->baseUrl . '/api/shipments/v2403/ship',
                $payload
            );

            if (!$response->successful()) {
                $errorMsg = $response->json('response.errors.0.message', "HTTP {$response->status()}");
                Log::channel('shipping')->error('UPS Ship Error', [
                    'status' => $response->status(),
                    'error' => $errorMsg,
                    'body' => $response->json(),
                ]);
                return ['success' => false, 'error' => $errorMsg];
            }

            return $this->parseShipResponse($response->json());
        } catch (Exception $e) {
            Log::channel('shipping')->error('UPS Ship Exception: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Parse rate response into a simplified array.
     */
    protected function parseRateResponse(array $data): array
    {
        $rates = [];
        $ratedShipments = $data['RateResponse']['RatedShipment'] ?? [];

        // Single result may not be wrapped in array
        if (isset($ratedShipments['Service'])) {
            $ratedShipments = [$ratedShipments];
        }

        $serviceNames = [
            '01' => 'UPS Next Day Air',
            '02' => 'UPS 2nd Day Air',
            '03' => 'UPS Ground',
            '12' => 'UPS 3 Day Select',
            '13' => 'UPS Next Day Air Saver',
            '14' => 'UPS Next Day Air Early',
            '59' => 'UPS 2nd Day Air A.M.',
        ];

        foreach ($ratedShipments as $shipment) {
            $code = $shipment['Service']['Code'] ?? '';
            $rates[] = [
                'service_code' => $code,
                'service' => $serviceNames[$code] ?? "UPS Service {$code}",
                'rate' => (float) ($shipment['TotalCharges']['MonetaryValue'] ?? 0),
                'currency' => $shipment['TotalCharges']['CurrencyCode'] ?? 'USD',
            ];
        }

        return $rates;
    }

    /**
     * Parse ship/label response.
     */
    protected function parseShipResponse(array $data): array
    {
        $result = ['success' => false];

        $shipmentResults = $data['ShipmentResponse']['ShipmentResults'] ?? [];
        if (empty($shipmentResults)) {
            return $result;
        }

        $result['success'] = true;

        // Tracking number
        $packageResults = $shipmentResults['PackageResults'] ?? [];
        if (isset($packageResults['TrackingNumber'])) {
            $result['tracking_number'] = $packageResults['TrackingNumber'];
        } elseif (isset($packageResults[0]['TrackingNumber'])) {
            $result['tracking_number'] = $packageResults[0]['TrackingNumber'];
        }

        // Label image
        $label = $packageResults['ShippingLabel'] ?? ($packageResults[0]['ShippingLabel'] ?? []);
        $result['label_data'] = $label['GraphicImage'] ?? '';

        // Total charges
        $result['total_charges'] = (float) ($shipmentResults['ShipmentCharges']['TotalCharges']['MonetaryValue'] ?? 0);

        return $result;
    }

    /**
     * Flush the cached OAuth token.
     */
    public function flushToken(): void
    {
        Cache::forget('ups_oauth_token');
    }
}
