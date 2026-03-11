<?php

namespace App\Services\Shipping;

use Illuminate\Support\Facades\Log;

class ZebraLabelService
{
    protected string $method;
    protected ?string $printerIp;

    public function __construct()
    {
        $this->method = config('shipping.zebra.method', 'raw');
        $this->printerIp = config('shipping.zebra.client');
    }

    /**
     * Generate a ZPL label string.
     */
    public function generateLabel(array $data): string
    {
        $zpl = "^XA\n";
        $zpl .= "^CF0,30\n";

        // Order info
        $zpl .= "^FO50,50^FD" . ($data['billing_id'] ?? '') . "^FS\n";
        $zpl .= "^FO50,90^FD" . ($data['customer_name'] ?? '') . "^FS\n";

        // Tracking
        if (!empty($data['tracking_id'])) {
            $zpl .= "^FO50,140^BY2^BCN,80,Y,N,N^FD" . $data['tracking_id'] . "^FS\n";
        }

        // Date
        $zpl .= "^FO50,260^FD" . date('m/d/Y') . "^FS\n";

        // Weight
        if (!empty($data['weight_oz'])) {
            $zpl .= "^FO50,300^FDWeight: " . $data['weight_oz'] . " oz^FS\n";
        }

        $zpl .= "^XZ";

        return $zpl;
    }

    /**
     * Print a label (either raw ZPL data return or network print).
     */
    public function printLabel(string $zpl): array
    {
        if ($this->method === 'network' && $this->printerIp) {
            return $this->networkPrint($zpl);
        }

        return ['method' => 'raw', 'data' => $zpl];
    }

    protected function networkPrint(string $zpl): array
    {
        try {
            $socket = @fsockopen($this->printerIp, 9100, $errno, $errstr, 5);

            if (!$socket) {
                return ['error' => "Could not connect to printer: {$errstr} ({$errno})"];
            }

            fwrite($socket, $zpl);
            fclose($socket);

            return ['success' => true, 'method' => 'network'];
        } catch (\Exception $e) {
            Log::error('Zebra print error: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }
}
