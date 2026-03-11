<?php

namespace App\Services\Shipping;

use Illuminate\Support\Facades\Log;
use SoapClient;
use SoapFault;

class FedexService
{
    protected array $auth;
    protected array $shipper;

    public function __construct()
    {
        $this->auth = [
            'Key' => config('shipping.fedex.key'),
            'Password' => config('shipping.fedex.password'),
            'AccountNumber' => config('shipping.fedex.account'),
            'MeterNumber' => config('shipping.fedex.meter'),
        ];
        $this->shipper = config('shipping.fedex.shipper');
    }

    /**
     * Get shipping rate from FedEx.
     */
    public function getRate(array $recipient, float $weightLbs): array
    {
        try {
            $client = new SoapClient(
                storage_path('wsdl/RateService_v28.wsdl'),
                ['trace' => true]
            );

            $request = $this->buildRateRequest($recipient, $weightLbs);
            $response = $client->getRates($request);

            return $this->parseRateResponse($response);
        } catch (SoapFault $e) {
            Log::error('FedEx Rate Error: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Create a shipping label.
     */
    public function printLabel(array $recipient, float $weightLbs, array $options = []): array
    {
        try {
            $client = new SoapClient(
                storage_path('wsdl/ShipService_v25.wsdl'),
                ['trace' => true]
            );

            $request = $this->buildShipRequest($recipient, $weightLbs, $options);
            $response = $client->processShipment($request);

            return $this->parseShipResponse($response);
        } catch (SoapFault $e) {
            Log::error('FedEx Ship Error: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    protected function buildRateRequest(array $recipient, float $weightLbs): array
    {
        return [
            'WebAuthenticationDetail' => ['UserCredential' => [
                'Key' => $this->auth['Key'],
                'Password' => $this->auth['Password'],
            ]],
            'ClientDetail' => [
                'AccountNumber' => $this->auth['AccountNumber'],
                'MeterNumber' => $this->auth['MeterNumber'],
            ],
            'Version' => ['ServiceId' => 'crs', 'Major' => '28', 'Intermediate' => '0', 'Minor' => '0'],
            'RequestedShipment' => [
                'DropoffType' => 'REGULAR_PICKUP',
                'ServiceType' => 'FEDEX_GROUND',
                'PackagingType' => 'YOUR_PACKAGING',
                'Shipper' => $this->shipper,
                'Recipient' => ['Address' => $recipient],
                'PackageCount' => '1',
                'RequestedPackageLineItems' => [[
                    'SequenceNumber' => 1,
                    'GroupPackageCount' => 1,
                    'Weight' => ['Units' => 'LB', 'Value' => $weightLbs],
                ]],
            ],
        ];
    }

    protected function buildShipRequest(array $recipient, float $weightLbs, array $options): array
    {
        $labelType = config('shipping.fedex.label.type', 'ZPLII');

        return [
            'WebAuthenticationDetail' => ['UserCredential' => [
                'Key' => $this->auth['Key'],
                'Password' => $this->auth['Password'],
            ]],
            'ClientDetail' => [
                'AccountNumber' => $this->auth['AccountNumber'],
                'MeterNumber' => $this->auth['MeterNumber'],
            ],
            'Version' => ['ServiceId' => 'ship', 'Major' => '25', 'Intermediate' => '0', 'Minor' => '0'],
            'RequestedShipment' => [
                'ShipTimestamp' => now()->format('c'),
                'DropoffType' => 'REGULAR_PICKUP',
                'ServiceType' => $options['service_type'] ?? 'FEDEX_GROUND',
                'PackagingType' => 'YOUR_PACKAGING',
                'Shipper' => $this->shipper,
                'Recipient' => [
                    'Contact' => $options['contact'] ?? [],
                    'Address' => $recipient,
                ],
                'LabelSpecification' => [
                    'LabelFormatType' => 'COMMON2D',
                    'ImageType' => $labelType,
                ],
                'PackageCount' => '1',
                'RequestedPackageLineItems' => [[
                    'SequenceNumber' => 1,
                    'Weight' => ['Units' => 'LB', 'Value' => $weightLbs],
                ]],
            ],
        ];
    }

    protected function parseRateResponse($response): array
    {
        $rates = [];
        if (isset($response->RateReplyDetails)) {
            foreach ((array) $response->RateReplyDetails as $detail) {
                $rates[] = [
                    'service' => $detail->ServiceType ?? '',
                    'rate' => $detail->RatedShipmentDetails[0]->ShipmentRateDetail->TotalNetCharge->Amount ?? 0,
                ];
            }
        }
        return $rates;
    }

    protected function parseShipResponse($response): array
    {
        $result = ['success' => false];
        if (isset($response->CompletedShipmentDetail)) {
            $detail = $response->CompletedShipmentDetail;
            $result['success'] = true;
            $result['tracking_number'] = $detail->CompletedPackageDetails->TrackingIds->TrackingNumber ?? '';
            $result['label_data'] = $detail->CompletedPackageDetails->Label->Parts->Image ?? '';
        }
        return $result;
    }
}
