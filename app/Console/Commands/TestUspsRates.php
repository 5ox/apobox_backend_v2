<?php

namespace App\Console\Commands;

use App\Services\Shipping\UspsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestUspsRates extends Command
{
    protected $signature = 'usps:test-rates
        {--zip=10001 : Destination ZIP code}
        {--pounds=1 : Weight in pounds}
        {--ounces=0 : Weight in ounces}
        {--length=0 : Length in inches}
        {--width=0 : Width in inches}
        {--height=0 : Height in inches}
        {--flush-token : Clear cached OAuth token before testing}
        {--debug : Show raw API requests and responses}';

    protected $description = 'Test USPS Domestic Prices v3 rate lookups';

    public function handle(): int
    {
        $usps = app(UspsService::class);
        $debug = $this->option('debug');

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

        $weightLbs = round(($pounds * 16 + $ounces) / 16, 4);
        if ($weightLbs <= 0) {
            $weightLbs = 0.0625;
        }

        // Dimensions are required by USPS API — default to 1" cube
        $length = max($length, 1);
        $width = max($width, 1);
        $height = max($height, 1);

        $originZip = config('shipping.origin_zip', '46563');

        $this->info('');
        $this->info('━━━ Step 2: Rate Lookup ━━━');
        $this->info("Origin ZIP:  {$originZip}");
        $this->info("Dest ZIP:    {$zip}");
        $this->info("Weight:      {$pounds} lb {$ounces} oz ({$weightLbs} lbs)");
        $this->info("Account #:   " . (config('shipping.usps.account_number') ? '***' . substr(config('shipping.usps.account_number'), -4) : '(empty)'));
        $this->info("Dimensions:  {$length}\" × {$width}\" × {$height}\"");

        // Query each mail class individually with raw output
        $mailClasses = $usps->getMailClasses();
        $successCount = 0;
        $rates = [];

        foreach ($mailClasses as $mailClass => $config) {
            $this->info('');
            $this->info("── {$mailClass} ({$config['label']}) ──");

            $payload = [
                'originZIPCode' => $originZip,
                'destinationZIPCode' => $zip,
                'weight' => $weightLbs,
                'length' => $length,
                'width' => $width,
                'height' => $height,
                'mailClass' => $mailClass,
                'processingCategory' => $config['processingCategory'],
                'destinationEntryFacilityType' => 'NONE',
                'rateIndicator' => $config['rateIndicator'],
                'mailingDate' => now()->format('Y-m-d'),
                'priceType' => 'RETAIL',
            ];

            if ($debug) {
                $this->line('  Request: ' . json_encode($payload, JSON_PRETTY_PRINT));
            }

            try {
                $response = Http::withToken($token)
                    ->connectTimeout(10)
                    ->timeout(20)
                    ->post('https://apis.usps.com/prices/v3/base-rates/search', $payload);

                $body = $response->json() ?? [];
                $status = $response->status();

                if ($debug) {
                    $this->line("  Status: {$status}");
                    $this->line('  Response: ' . json_encode($body, JSON_PRETTY_PRINT));
                }

                if (!$response->successful()) {
                    $errorMsg = $body['error']['message']
                        ?? $body['message']
                        ?? $body['error']
                        ?? json_encode($body);
                    $this->error("  ✗ HTTP {$status}: {$errorMsg}");
                    continue;
                }

                $price = $body['rates'][0]['price']
                    ?? $body['totalBasePrice']
                    ?? null;
                $description = $body['rates'][0]['description'] ?? '';

                if ($price !== null) {
                    $this->info("  ✓ \${$price} — {$description}");
                    $rates[] = [
                        'service' => $mailClass,
                        'label' => $config['label'],
                        'rate' => (float) $price,
                        'description' => $description,
                    ];
                    $successCount++;
                } else {
                    $this->warn("  ✗ No price in response");
                    if (!$debug) {
                        $this->line('  Response: ' . json_encode($body));
                    }
                }
            } catch (\Exception $e) {
                $this->error("  ✗ Exception: " . $e->getMessage());
            }
        }

        // Summary
        $this->info('');
        $this->info('━━━ Summary ━━━');
        if ($successCount > 0) {
            $this->table(
                ['Mail Class', 'Label', 'Rate', 'Description'],
                collect($rates)->map(fn($r) => [
                    $r['service'],
                    $r['label'],
                    '$' . number_format($r['rate'], 2),
                    $r['description'] ?: '—',
                ])->all()
            );
            $this->info("✓ {$successCount}/" . count($mailClasses) . ' mail classes returned rates.');
        } else {
            $this->error('✗ No rates returned from any mail class.');
            if (!$debug) {
                $this->warn('Run with --debug to see raw API requests/responses.');
            }
        }

        // Step 3: Test via UspsService.getAllRates()
        $this->info('');
        $this->info('━━━ Step 3: UspsService->getAllRates() ━━━');
        $params = compact('zip', 'pounds', 'ounces', 'length', 'width', 'height');
        $serviceRates = $usps->getAllRates($params);

        if (isset($serviceRates['error'])) {
            $this->error('✗ Error: ' . $serviceRates['error']);
        } elseif (empty($serviceRates)) {
            $this->warn('✗ Empty result — service returned no rates.');
        } else {
            $this->info('✓ ' . count($serviceRates) . ' rate(s) via service method.');
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
        return $successCount > 0 ? 0 : 1;
    }
}
