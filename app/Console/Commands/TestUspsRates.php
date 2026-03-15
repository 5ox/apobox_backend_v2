<?php

namespace App\Console\Commands;

use App\Services\Shipping\UspsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class TestUspsRates extends Command
{
    protected $signature = 'usps:test-rates
        {--zip=10001 : Destination ZIP code}
        {--pounds=1 : Weight in pounds}
        {--ounces=0 : Weight in ounces}
        {--length=0 : Length in inches (required)}
        {--width=0 : Width in inches (required)}
        {--height=0 : Height in inches (required)}
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

        // Step 2: Direct API test — each mail class × rate indicator
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

        if (!$usps->hasCompleteDimensions(compact('length', 'width', 'height'))) {
            $this->error('Dimensions are required. Pass --length, --width, and --height with values greater than 0.');
            return 1;
        }

        $lookupError = $usps->validateRateLookupRequest(compact('zip', 'pounds', 'ounces', 'length', 'width', 'height'));
        if ($lookupError !== null) {
            $this->error($lookupError);
            return 1;
        }

        $usesShippingOptionsLookup = $usps->usesShippingOptionsLookup(compact('zip', 'pounds', 'ounces', 'length', 'width', 'height'));

        $originZip = config('shipping.origin_zip', '46563');

        $this->info('');
        $this->info('━━━ Step 2: Rate Lookup ━━━');
        $this->info("Origin ZIP:  {$originZip}");
        $this->info("Dest ZIP:    {$zip}");
        $this->info("Weight:      {$pounds} lb {$ounces} oz ({$weightLbs} lbs)");
        $this->info("Account #:   " . (config('shipping.usps.account_number') ? '***' . substr(config('shipping.usps.account_number'), -4) : '(empty)'));
        $this->info("Dimensions:  {$length}\" × {$width}\" × {$height}\"");
        $this->info('Lookup:      ' . ($usesShippingOptionsLookup
            ? 'Shipping Options v3 (non-machinable / oversized)'
            : 'Domestic Prices v3 base-rates (machinable)'));

        $successCount = 0;
        $rates = [];

        if ($usesShippingOptionsLookup) {
            $this->warn('Skipping direct base-rates probe because USPS requires the Shipping Options lookup path for this parcel.');
        } else {
            $mailClasses = $usps->getMailClasses();

            foreach ($mailClasses as $mailClass => $config) {
                $rateIndicators = $config['rateIndicators'] ?? ['SP'];

                foreach ($rateIndicators as $rateIndicator) {
                    $this->info('');
                    $this->info("── {$mailClass} / {$rateIndicator} ({$config['label']}) ──");

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
                        'rateIndicator' => $rateIndicator,
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
                            $this->warn("  ✗ HTTP {$status}: {$errorMsg}");
                            continue;
                        }

                        $totalPrice = $body['totalBasePrice'] ?? null;
                        $basePrice = $body['rates'][0]['price'] ?? null;
                        $description = $body['rates'][0]['description'] ?? '';

                        $fees = [];
                        foreach ($body['rates'][0]['fees'] ?? [] as $fee) {
                            if (!empty($fee['name']) && ($fee['price'] ?? 0) > 0) {
                                $fees[] = $fee['name'] . ': $' . number_format($fee['price'], 2);
                            }
                        }

                        $price = $totalPrice ?? $basePrice;

                        if ($price !== null) {
                            $feeStr = !empty($fees) ? ' [' . implode(', ', $fees) . ']' : '';
                            $this->info("  ✓ \${$price}{$feeStr} — {$description}");
                            if ($totalPrice && $basePrice && $totalPrice != $basePrice) {
                                $this->info("    Base: \${$basePrice} + fees = \${$totalPrice}");
                            }
                            $rates[] = [
                                'service' => $mailClass,
                                'label' => $config['label'],
                                'indicator' => $rateIndicator,
                                'rate' => (float) $price,
                                'fees' => implode(', ', $fees) ?: '—',
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
            }
        }

        // Summary table
        $this->info('');
        $this->info('━━━ Summary ━━━');
        if ($successCount > 0) {
            $this->table(
                ['Mail Class', 'Ind.', 'Rate', 'Fees', 'Description'],
                collect($rates)->map(fn($r) => [
                    $r['service'],
                    $r['indicator'],
                    '$' . number_format($r['rate'], 2),
                    $r['fees'],
                    Str::limit($r['description'],50),
                ])->all()
            );
            $this->info("✓ {$successCount} rate(s) returned.");
        } elseif ($usesShippingOptionsLookup) {
            $this->warn('Direct base-rates summary skipped because this parcel uses the Shipping Options lookup path.');
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
            $this->table(
                ['Service', 'Ind.', 'Our Rate', 'Retail', 'Fees', 'Description'],
                collect($serviceRates)->map(fn($r) => [
                    $r['service'],
                    $r['rateIndicator'] ?? '?',
                    '$' . number_format($r['rate'], 2),
                    isset($r['retail_rate']) ? '$' . number_format($r['retail_rate'], 2) : '—',
                    !empty($r['fees']) ? collect($r['fees'])->map(fn($f) => $f['name'] . ': $' . number_format($f['price'], 2))->implode(', ') : '—',
                    Str::limit($r['description'],40),
                ])->all()
            );
            $this->info('✓ ' . count($serviceRates) . ' rate(s) via service method.');

            // Step 4: Test auto-rate selection
            $this->info('');
            $this->info('━━━ Step 4: Auto-Rate Selection ━━━');
            $autoRate = $usps->selectAutoRate($serviceRates, 'PRIORITY_MAIL');
            if ($autoRate) {
                $this->info("  For PRIORITY_MAIL → {$autoRate['service']} ({$autoRate['rateIndicator']}) \${$autoRate['retail_rate']}");
            } else {
                $this->warn('  No auto-rate found for PRIORITY_MAIL');
            }
        }

        // Step 5: Test legacy normalization
        $this->info('');
        $this->info('━━━ Step 5: Legacy Mail Class Normalization ━━━');
        foreach (['PRIORITY_MAIL', 'PARCEL_POST', 'PARCEL_SELECT', 'STANDARD_POST', 'priority_mail'] as $legacy) {
            $normalized = $usps->normalizeMailClass($legacy);
            $changed = $legacy !== $normalized ? " → {$normalized}" : '';
            $this->info("  {$legacy}{$changed}");
        }

        $this->info('');
        return $successCount > 0 ? 0 : 1;
    }
}
