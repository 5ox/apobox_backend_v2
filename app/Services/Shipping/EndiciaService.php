<?php

namespace App\Services\Shipping;

use Illuminate\Support\Facades\Log;

class EndiciaService
{
    protected string $accountNumber;
    protected string $customsSigner;

    public function __construct()
    {
        $this->accountNumber = config('shipping.endicia.account_number');
        $this->customsSigner = config('shipping.endicia.customs_signer');
    }

    /**
     * Generate a DAZzle XML label file.
     */
    public function generateLabel(array $order, array $customer): string
    {
        $mailClass = $this->mapMailClass($order['mail_class'] ?? 'priority_mail');
        $weight = (float) ($order['weight_oz'] ?? 0);
        $weightOz = $weight;
        $weightLbs = floor($weightOz / 16);
        $remainOz = $weightOz - ($weightLbs * 16);

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<DAZzle OutputFile="RESPONSEHERE" OutputType="No Postage" Prompt="NO">' . "\n";
        $xml .= '<Package ID="1">' . "\n";
        $xml .= '<MailClass>' . $mailClass . '</MailClass>' . "\n";
        $xml .= '<WeightOz>' . $weightOz . '</WeightOz>' . "\n";
        $xml .= '<PackageType>Package</PackageType>' . "\n";
        $xml .= '<DateAdvance>0</DateAdvance>' . "\n";

        // Recipient
        $xml .= '<ToName>' . htmlspecialchars($order['delivery_name'] ?? '') . '</ToName>' . "\n";
        $xml .= '<ToAddress1>' . htmlspecialchars($order['delivery_street_address'] ?? '') . '</ToAddress1>' . "\n";
        $xml .= '<ToCity>' . htmlspecialchars($order['delivery_city'] ?? '') . '</ToCity>' . "\n";
        $xml .= '<ToState>' . htmlspecialchars($order['delivery_state'] ?? '') . '</ToState>' . "\n";
        $xml .= '<ToPostalCode>' . htmlspecialchars($order['delivery_postcode'] ?? '') . '</ToPostalCode>' . "\n";

        // Sender
        $xml .= '<ReturnAddress1>' . config('shipping.fedex.shipper.address.StreetLines.0') . '</ReturnAddress1>' . "\n";
        $xml .= '<FromCity>' . config('shipping.fedex.shipper.address.City') . '</FromCity>' . "\n";
        $xml .= '<FromState>' . config('shipping.fedex.shipper.address.StateOrProvinceCode') . '</FromState>' . "\n";
        $xml .= '<FromPostalCode>' . config('shipping.fedex.shipper.address.PostalCode') . '</FromPostalCode>' . "\n";

        // Customs
        if (!empty($order['insurance_coverage'])) {
            $xml .= '<InsuredValue>' . $order['insurance_coverage'] . '</InsuredValue>' . "\n";
        }
        $xml .= '<CustomsDescription>' . config('apobox.orders.default_customs_description') . '</CustomsDescription>' . "\n";
        $xml .= '<CustomsSigner>' . $this->customsSigner . '</CustomsSigner>' . "\n";

        // Dimensions
        if (!empty($order['length'])) {
            $xml .= '<Length>' . $order['length'] . '</Length>' . "\n";
            $xml .= '<Width>' . ($order['width'] ?? '') . '</Width>' . "\n";
            $xml .= '<Depth>' . ($order['depth'] ?? '') . '</Depth>' . "\n";
        }

        $xml .= '</Package>' . "\n";
        $xml .= '</DAZzle>';

        return $xml;
    }

    protected function mapMailClass(string $class): string
    {
        return match ($class) {
            'priority_mail' => 'Priority',
            'parcel_post' => 'ParcelSelect',
            default => 'Priority',
        };
    }
}
