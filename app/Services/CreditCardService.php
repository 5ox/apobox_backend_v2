<?php

namespace App\Services;

class CreditCardService
{
    protected string $key;

    /**
     * Card type patterns (prefix regex => type name).
     */
    private const CARD_PATTERNS = [
        '/^4[0-9]{12}(?:[0-9]{3})?$/'           => 'visa',
        '/^5[1-5][0-9]{14}$/'                    => 'mastercard',
        '/^2(?:2[2-9][1-9]|2[3-9]|[3-6]|7[01]|720)[0-9]{12}$/' => 'mastercard',
        '/^3[47][0-9]{13}$/'                     => 'amex',
        '/^6(?:011|5[0-9]{2})[0-9]{12}$/'        => 'discover',
        '/^3(?:0[0-5]|[68][0-9])[0-9]{11}$/'     => 'dinersclub',
        '/^(?:2131|1800|35\d{3})\d{11}$/'         => 'jcb',
    ];

    public function __construct(string $key)
    {
        $this->key = $key;
    }

    /**
     * Encrypt a credit card number.
     * Uses OpenSSL AES-256-CBC (replaces legacy mcrypt RIJNDAEL-256).
     */
    public function encrypt(string $number): string
    {
        $cipher = 'aes-256-cbc';
        $ivLength = openssl_cipher_iv_length($cipher);
        $iv = openssl_random_pseudo_bytes($ivLength);
        $key = hash('sha256', $this->key, true);

        $encrypted = openssl_encrypt($number, $cipher, $key, OPENSSL_RAW_DATA, $iv);

        return base64_encode($iv . $encrypted);
    }

    /**
     * Decrypt a credit card number.
     * Supports both new OpenSSL format and legacy mcrypt format.
     */
    public function decrypt(string $encrypted): string
    {
        if (empty($encrypted)) {
            return '';
        }

        $data = base64_decode($encrypted);
        if ($data === false) {
            return '';
        }

        // Try OpenSSL AES-256-CBC first (new format)
        $cipher = 'aes-256-cbc';
        $ivLength = openssl_cipher_iv_length($cipher);
        $key = hash('sha256', $this->key, true);

        if (strlen($data) > $ivLength) {
            $iv = substr($data, 0, $ivLength);
            $ciphertext = substr($data, $ivLength);
            $decrypted = openssl_decrypt($ciphertext, $cipher, $key, OPENSSL_RAW_DATA, $iv);

            if ($decrypted !== false && is_numeric($decrypted)) {
                return $decrypted;
            }
        }

        // Fall back to legacy mcrypt RIJNDAEL-256 decryption via OpenSSL
        // Legacy format: random 32-byte IV prepended to ciphertext
        return $this->decryptLegacy($data);
    }

    /**
     * Decrypt legacy mcrypt RIJNDAEL-256/CFB format.
     */
    protected function decryptLegacy(string $data): string
    {
        // mcrypt RIJNDAEL-256 used 32-byte IV
        $ivSize = 32;

        if (strlen($data) <= $ivSize) {
            return '';
        }

        $iv = substr($data, 0, $ivSize);
        $ciphertext = substr($data, $ivSize);

        // RIJNDAEL-256 in CFB mode with mcrypt is non-standard.
        // If the legacy PHP mcrypt extension is available, use it:
        if (function_exists('mcrypt_decrypt')) {
            $decrypted = @mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $this->key, $ciphertext, MCRYPT_MODE_CFB, $iv);
            return rtrim($decrypted, "\0");
        }

        // Without mcrypt, return empty (cards should be re-encrypted on next update)
        return '';
    }

    /**
     * Mask a credit card number, showing only last 4 digits.
     */
    public function maskNumber(string $number): string
    {
        if (strlen($number) <= 4) {
            return $number;
        }

        return str_repeat('X', strlen($number) - 4) . substr($number, -4);
    }

    /**
     * Validate a credit card number using the Luhn algorithm.
     */
    public function isValid(string $number): bool
    {
        $number = preg_replace('/\D/', '', $number);

        if (strlen($number) < 13 || strlen($number) > 19) {
            return false;
        }

        return $this->luhnCheck($number);
    }

    /**
     * Get card type from number (visa, mastercard, amex, discover, etc.).
     */
    public function getCardType(string $number): ?string
    {
        $number = preg_replace('/\D/', '', $number);

        foreach (self::CARD_PATTERNS as $pattern => $type) {
            if (preg_match($pattern, $number)) {
                return $type;
            }
        }

        return null;
    }

    /**
     * Luhn algorithm (mod 10) check.
     */
    private function luhnCheck(string $number): bool
    {
        $sum = 0;
        $length = strlen($number);
        $parity = $length % 2;

        for ($i = $length - 1; $i >= 0; $i--) {
            $digit = (int) $number[$i];

            if (($i % 2) === $parity) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }

            $sum += $digit;
        }

        return ($sum % 10) === 0;
    }
}
