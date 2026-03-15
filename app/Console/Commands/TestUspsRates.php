<?php

namespace App\Console\Commands;

use App\Services\Shipping\UspsService;
use Illuminate\Console\Command;

class TestUspsRates extends Command
{
    protected $signature = 'usps:test-rates
        {--zip=10001 : Destination ZIP code}
        {--pounds=1 : Weight in pounds}
        {--ounces=0 : Weight in ounces}
        {--length=0 : Length in inches}
        {--width=0 : Width in inches}
        {--height=0 : Height in inches}
        {--flush-token : Clear cached OAuth token before testing}';

    protected $description = 'Test USPS Domestic Prices v3 rate lookups';

    public function handle(): int
    {
        $usps = app(UspsService::class);

        if ($this->option('flush-token')) {
            $usps->flushToken();
            $this->info('Cleared cached OAuth token.');
        }

        // Step 1: Test OAuth
        $this->info('');
        $this->info('━━━ Step 1: OAuth Token ━━━');
        try {
            $token = $usps->getAccessToken();
            $this->info('✓ Token obtained: ' . substr($token, 0, 20) . '...');
        } catch (\Exception $e) {
            $this->error('✗ OAuth failed: ' . $e->getMessage());
            $this->newLine();
            $this->warn('Check your USPS_API_CUSTOMER_KEY and USPS_API_CUSTOMER_SECRET in .env');
            return 1;
        }

        // Step 2: Test rate lookups
        $zip = $this->option('zip');
        $pounds = (int) $this->option('pounds');
        $ounces = (int) $this->option('ounces');
        $length = (float) $this->option('length');
        $width = (float) $this->option('width');
        $height = (float) $this->option('height');

        $this->info('');
        $this->info('━━━ Step 2: Rate Lookup ━━━');
        $this->info("Origin ZIP: " . config('shipping.origin_zip', '46563'));
        $this->info("Dest ZIP:   {$zip}");
        $this->info("Weight:     {$pounds} lb {$ounces} oz");
        if ($length > 0) {
            $this->info("Dimensions: {$length}\" × {$width}\" × {$height}\"");
        } else {
            $this->info("Dimensions: (none)");
        }

        $params = [
            'zip' => $zip,
            'pounds' => $pounds,
            'ounces' => $ounces,
            'length' => $length,
            'width' => $width,
            'height' => $height,
        ];

        $rates = $usps->getAllRates($params);

        if (isset($rates['error'])) {
            $this->error('✗ Rate lookup failed: ' . $rates['error']);
            return 1;
        }

        if (empty($rates)) {
            $this->warn('No rates returned. Check shipping logs for details.');
            return 1;
        }

        $this->info('');
        $this->info('━━━ Results ━━━');

        $rows = [];
        foreach ($rates as $rate) {
            $rows[] = [
                $rate['service'],
                $rate['label'],
                '$' . number_format($rate['rate'], 2),
                $rate['retail_rate'] ? '$' . number_format($rate['retail_rate'], 2) : '—',
                $rate['retail_rate'] && $rate['retail_rate'] > $rate['rate']
                    ? '-$' . number_format($rate['retail_rate'] - $rate['rate'], 2)
                    : '—',
                $rate['description'] ?: '—',
            ];
        }

        $this->table(
            ['Mail Class', 'Label', 'Our Rate', 'Retail', 'Savings', 'Description'],
            $rows
        );

        $this->info('');
        $this->info('✓ ' . count($rates) . ' rate(s) returned successfully.');

        // Step 3: Test filtered rates (getRate)
        $this->info('');
        $this->info('━━━ Step 3: Filtered Rates (configured classes) ━━━');
        $configuredClasses = config('shipping.usps.rate_classes', []);
        $this->info('Configured: ' . implode(', ', $configuredClasses));

        $filteredRates = $usps->getRate($params);
        if (isset($filteredRates['error'])) {
            $this->error('✗ Filtered lookup failed: ' . $filteredRates['error']);
        } else {
            $this->info('✓ ' . count($filteredRates) . ' filtered rate(s) returned.');
        }

        // Step 4: Test legacy normalization
        $this->info('');
        $this->info('━━━ Step 4: Legacy Mail Class Normalization ━━━');
        foreach (['PRIORITY_MAIL', 'PARCEL_POST', 'PARCEL_SELECT', 'STANDARD_POST', 'priority_mail'] as $legacy) {
            $normalized = $usps->normalizeMailClass($legacy);
            $changed = $legacy !== $normalized ? " → {$normalized}" : '';
            $this->info("  {$legacy}{$changed}");
        }

        $this->info('');
        return 0;
    }
}
