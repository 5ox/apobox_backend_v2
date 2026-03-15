<?php

namespace Tests\Unit;

use App\Services\Shipping\UspsService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class UspsServiceTest extends TestCase
{
    public function test_has_complete_dimensions_requires_all_three_values(): void
    {
        $service = app(UspsService::class);

        $this->assertTrue($service->hasCompleteDimensions([
            'length' => 12,
            'width' => 8,
            'height' => 6,
        ]));

        $this->assertFalse($service->hasCompleteDimensions([
            'length' => 12,
            'width' => 8,
            'height' => 0,
        ]));
    }

    public function test_select_auto_rate_prefers_dr_for_matching_mail_class(): void
    {
        $service = app(UspsService::class);

        $selected = $service->selectAutoRate([
            [
                'service' => 'PRIORITY_MAIL',
                'rateIndicator' => 'SP',
                'rate' => 12.50,
                'retail_rate' => 14.25,
            ],
            [
                'service' => 'PRIORITY_MAIL',
                'rateIndicator' => 'DR',
                'rate' => 11.75,
                'retail_rate' => 13.50,
            ],
        ], 'PRIORITY_MAIL');

        $this->assertNotNull($selected);
        $this->assertSame('DR', $selected['rateIndicator']);
    }

    public function test_select_auto_rate_does_not_fall_back_to_a_different_mail_class(): void
    {
        $service = app(UspsService::class);

        $selected = $service->selectAutoRate([
            [
                'service' => 'PRIORITY_MAIL',
                'rateIndicator' => 'DR',
                'rate' => 11.75,
                'retail_rate' => 13.50,
            ],
        ], 'MEDIA_MAIL');

        $this->assertNull($selected);
    }

    public function test_validate_rate_lookup_request_accepts_machinable_parcels(): void
    {
        $service = new UspsService();

        $message = $service->validateRateLookupRequest([
            'pounds' => 1,
            'ounces' => 0,
            'length' => 8,
            'width' => 6,
            'height' => 4,
        ]);

        $this->assertNull($message);
        $this->assertFalse($service->usesShippingOptionsLookup([
            'pounds' => 1,
            'ounces' => 0,
            'length' => 8,
            'width' => 6,
            'height' => 4,
        ]));
    }

    public function test_validate_rate_lookup_request_allows_oversized_parcels_within_usps_limits(): void
    {
        $service = new UspsService();

        $message = $service->validateRateLookupRequest([
            'pounds' => 26,
            'ounces' => 2,
            'length' => 22,
            'width' => 22,
            'height' => 22,
        ]);

        $this->assertNull($message);
        $this->assertTrue($service->usesShippingOptionsLookup([
            'pounds' => 26,
            'ounces' => 2,
            'length' => 22,
            'width' => 22,
            'height' => 22,
        ]));
    }

    public function test_validate_rate_lookup_request_allows_non_machinable_weight_within_usps_limits(): void
    {
        $service = new UspsService();

        $message = $service->validateRateLookupRequest([
            'pounds' => 26,
            'ounces' => 2,
            'length' => 10,
            'width' => 10,
            'height' => 10,
        ]);

        $this->assertNull($message);
        $this->assertTrue($service->usesShippingOptionsLookup([
            'pounds' => 26,
            'ounces' => 2,
            'length' => 10,
            'width' => 10,
            'height' => 10,
        ]));
    }

    public function test_analyze_package_reports_weight_based_non_machinable_reason(): void
    {
        $service = new UspsService();

        $analysis = $service->analyzePackage([
            'pounds' => 26,
            'ounces' => 2,
            'length' => 10,
            'width' => 10,
            'height' => 10,
        ]);

        $this->assertFalse($analysis['is_machinable']);
        $this->assertFalse($analysis['is_oversized']);
        $this->assertStringContainsString(
            'weight 26.12 lb exceeds the 25 lb machinable limit',
            $analysis['non_machinable_reason_text']
        );
    }

    public function test_analyze_package_reports_dimension_based_non_machinable_reason(): void
    {
        $service = new UspsService();

        $analysis = $service->analyzePackage([
            'pounds' => 10,
            'ounces' => 0,
            'length' => 20,
            'width' => 20,
            'height' => 10,
        ]);

        $this->assertFalse($analysis['is_machinable']);
        $this->assertFalse($analysis['is_oversized']);
        $this->assertStringContainsString(
            'second side 20.0" exceeds the 18" machinable limit',
            $analysis['non_machinable_reason_text']
        );
    }

    public function test_validate_rate_lookup_request_rejects_parcels_that_exceed_usps_hard_limits(): void
    {
        $service = new UspsService();

        $message = $service->validateRateLookupRequest([
            'pounds' => 71,
            'ounces' => 0,
            'length' => 40,
            'width' => 20,
            'height' => 20,
        ]);

        $this->assertIsString($message);
        $this->assertStringContainsString('exceeds USPS maximum parcel limits', $message);
    }

    public function test_get_all_rates_uses_shipping_options_for_non_machinable_parcels(): void
    {
        config([
            'shipping.usps.client_id' => 'client-id',
            'shipping.usps.client_secret' => 'client-secret',
            'shipping.usps.account_number' => '1234567890',
            'shipping.origin_zip' => '46563',
        ]);

        Cache::forget('usps_oauth_token');

        Http::fake([
            'https://apis.usps.com/oauth2/v3/token' => Http::response([
                'access_token' => 'test-token',
            ], 200),
            'https://apis.usps.com/shipments/v3/options/search' => function (Request $request) {
                $mailClass = data_get($request->data(), 'packageDescription.mailClass');
                $priceType = data_get($request->data(), 'pricingOptions.0.priceType');

                if ($mailClass !== 'USPS_GROUND_ADVANTAGE') {
                    return Http::response([
                        'pricingOptions' => [[
                            'priceType' => $priceType,
                            'shippingOptions' => [],
                        ]],
                    ], 200);
                }

                return Http::response(
                    $this->makeShippingOptionsResponse(
                        'USPS_GROUND_ADVANTAGE',
                        $priceType,
                        $priceType === 'COMMERCIAL' ? 18.05 : 20.35,
                        3.00
                    ),
                    200
                );
            },
            'https://apis.usps.com/prices/v3/base-rates/search' => Http::response([
                'error' => ['message' => 'base-rates should not be called for non-machinable parcels'],
            ], 500),
        ]);

        $service = new UspsService();

        $rates = $service->getAllRates([
            'zip' => '22066',
            'pounds' => 26,
            'ounces' => 2,
            'length' => 10,
            'width' => 10,
            'height' => 10,
        ]);

        $this->assertCount(1, $rates);
        $this->assertSame('USPS_GROUND_ADVANTAGE', $rates[0]['service']);
        $this->assertSame('SP', $rates[0]['rateIndicator']);
        $this->assertSame('NONSTANDARD', $rates[0]['processingCategory']);
        $this->assertSame('SHIPPING_OPTIONS', $rates[0]['lookup_path']);
        $this->assertSame(18.05, $rates[0]['rate']);
        $this->assertSame(18.05, $rates[0]['commercial_rate']);
        $this->assertSame(20.35, $rates[0]['retail_rate']);
        $this->assertSame('COMMERCIAL', $rates[0]['rate_source']);
        $this->assertSame('RETSKU001', $rates[0]['postage_id']);
        $this->assertSame('RETSKU001', $rates[0]['retail_postage_id']);
        $this->assertSame('COMSKU001', $rates[0]['commercial_postage_id']);
        $this->assertSame('Nonstandard Fee', $rates[0]['fees'][0]['name']);
        $this->assertSame(3.0, $rates[0]['fees'][0]['price']);

        Http::assertSent(fn (Request $request) => str_contains($request->url(), '/shipments/v3/options/search'));
        Http::assertNotSent(fn (Request $request) => str_contains($request->url(), '/prices/v3/base-rates/search'));
    }

    protected function makeShippingOptionsResponse(
        string $mailClass,
        string $priceType,
        float $totalBasePrice,
        float $feePrice
    ): array {
        return [
            'pricingOptions' => [[
                'priceType' => $priceType,
                'shippingOptions' => [[
                    'mailClass' => $mailClass,
                    'rateOptions' => [[
                        'totalBasePrice' => $totalBasePrice,
                        'rates' => [[
                            'description' => 'USPS Ground Advantage Nonstandard Single-piece',
                            'price' => $totalBasePrice - $feePrice,
                            'priceType' => $priceType,
                            'mailClass' => $mailClass,
                            'productName' => '',
                            'productDefinition' => '',
                            'processingCategory' => 'NONSTANDARD',
                            'rateIndicator' => 'SP',
                            'destinationEntryFacilityType' => 'NONE',
                            'SKU' => $priceType === 'COMMERCIAL' ? 'COMSKU001' : 'RETSKU001',
                            'fees' => [[
                                'name' => 'Nonstandard Fee',
                                'price' => $feePrice,
                                'SKU' => 'FEE001',
                            ]],
                        ]],
                        'extraServices' => [],
                    ]],
                ]],
            ]],
        ];
    }
}
