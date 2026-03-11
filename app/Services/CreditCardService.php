<?php

namespace App\Services;

use Inacho\CreditCard as CreditCardValidator;

class CreditCardService
{
    protected string $key;

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
        // This requires the openssl extension with the proper cipher string.
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
     * Validate a credit card number using Luhn check.
     */
    public function isValid(string $number): bool
    {
        try {
            $result = CreditCardValidator::validCreditCard($number);
            return $result['valid'];
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get card type from number.
     */
    public function getCardType(string $number): ?string
    {
        try {
            $result = CreditCardValidator::validCreditCard($number);
            return $result['valid'] ? $result['type'] : null;
        } catch (\Exception $e) {
            return null;
        }
    }
}
