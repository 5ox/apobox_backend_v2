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
}
