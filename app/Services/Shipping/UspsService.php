<?php

namespace App\Services\Shipping;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UspsService
{
    protected string $userId;
    protected string $baseUrl = 'https://secure.shippingapis.com/ShippingAPI.dll';

    public function __construct()
    {
        $this->userId = config('shipping.usps.user_id');
    }

    /**
     * Get shipping rates for a package.
     */
    public function getRate(array $params): array
    {
        $xml = $this->buildRateXml($params);

        try {
            $response = Http::get($this->baseUrl, [
                'API' => 'RateV4',
                'XML' => $xml,
            ]);

            return $this->parseRateResponse($response->body());
        } catch (\Exception $e) {
            Log::error('USPS Rate Error: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    protected function buildRateXml(array $params): string
    {
        $rateClasses = config('shipping.usps.rate_classes');

        $xml = '<RateV4Request USERID="' . $this->userId . '">';
        $xml .= '<Revision>2</Revision>';
        $xml .= '<Package ID="1">';
        $xml .= '<Service>ONLINE</Service>';
        $xml .= '<ZipOrigination>' . config('shipping.origin_zip') . '</ZipOrigination>';
        $xml .= '<ZipDestination>' . ($params['zip'] ?? '') . '</ZipDestination>';
        $xml .= '<Pounds>' . ($params['pounds'] ?? 0) . '</Pounds>';
        $xml .= '<Ounces>' . ($params['ounces'] ?? 0) . '</Ounces>';
        $xml .= '<Container />';
        $xml .= '<Machinable>' . ($params['machinable'] ?? 'true') . '</Machinable>';
        $xml .= '</Package>';
        $xml .= '</RateV4Request>';

        return $xml;
    }

    protected function parseRateResponse(string $body): array
    {
        $rates = [];
        $allowedClasses = config('shipping.usps.rate_classes');

        try {
            $xml = simplexml_load_string($body);
            if (!$xml || isset($xml->Error)) {
                return ['error' => (string) ($xml->Error->Description ?? 'Unknown error')];
            }

            foreach ($xml->Package->Postage ?? [] as $postage) {
                $classId = (string) $postage['CLASSID'];
                if (in_array($classId, $allowedClasses)) {
                    $rates[] = [
                        'class_id' => $classId,
                        'service' => (string) $postage->MailService,
                        'rate' => (float) $postage->Rate,
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::error('USPS XML Parse Error: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }

        return $rates;
    }
}
