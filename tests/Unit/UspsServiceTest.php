<?php

namespace Tests\Unit;

use App\Services\Shipping\UspsService;
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
        $service = app(UspsService::class);

        $message = $service->validateRateLookupRequest([
            'pounds' => 1,
            'ounces' => 0,
            'length' => 8,
            'width' => 6,
            'height' => 4,
        ]);

        $this->assertNull($message);
    }

    public function test_validate_rate_lookup_request_rejects_oversized_parcels_with_specific_message(): void
    {
        $service = app(UspsService::class);

        $message = $service->validateRateLookupRequest([
            'pounds' => 26,
            'ounces' => 2,
            'length' => 22,
            'width' => 22,
            'height' => 22,
        ]);

        $this->assertIsString($message);
        $this->assertStringContainsString('oversized', strtolower($message));
        $this->assertStringContainsString('Ground Advantage', $message);
    }

    public function test_validate_rate_lookup_request_rejects_non_machinable_parcels_with_specific_message(): void
    {
        $service = app(UspsService::class);

        $message = $service->validateRateLookupRequest([
            'pounds' => 10,
            'ounces' => 0,
            'length' => 20,
            'width' => 20,
            'height' => 10,
        ]);

        $this->assertIsString($message);
        $this->assertStringContainsString('non-machinable', strtolower($message));
    }
}
