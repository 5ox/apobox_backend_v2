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
     * Generate a ZPL label string from structured label data (header/body/footer sections).
     */
    public function generateLabel(array $data): string
    {
        $zpl = "^XA\n";

        $yPos = 50;

        // Header section
        if (!empty($data['header'])) {
            $size = $data['header']['size'] ?? 30;
            $zpl .= "^CF0,{$size}\n";
            foreach (explode("\n", $data['header']['content'] ?? '') as $line) {
                $line = trim($line);
                if ($line !== '') {
                    $zpl .= "^FO50,{$yPos}^FD{$line}^FS\n";
                    $yPos += $size + 10;
                }
            }
        }

        $yPos += 10;

        // Body section
        if (!empty($data['body'])) {
            $size = $data['body']['size'] ?? 26;
            $zpl .= "^CF0,{$size}\n";
            foreach (explode("\n", $data['body']['content'] ?? '') as $line) {
                $line = trim($line);
                if ($line !== '') {
                    $zpl .= "^FO50,{$yPos}^FD{$line}^FS\n";
                    $yPos += $size + 8;
                }
            }
        }

        $yPos += 10;

        // Footer section
        if (!empty($data['footer'])) {
            $size = $data['footer']['size'] ?? 20;
            $zpl .= "^CF0,{$size}\n";
            foreach (explode("\n", $data['footer']['content'] ?? '') as $line) {
                $line = trim($line);
                if ($line !== '') {
                    $zpl .= "^FO50,{$yPos}^FD{$line}^FS\n";
                    $yPos += $size + 8;
                }
            }
        }

        $zpl .= "^XZ";

        return $zpl;
    }

    /**
     * Print a label from structured data or raw ZPL string.
     */
    public function printLabel(string|array $data): array
    {
        $zpl = is_array($data) ? $this->generateLabel($data) : $data;

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
