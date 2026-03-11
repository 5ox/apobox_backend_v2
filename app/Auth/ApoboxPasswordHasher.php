<?php

namespace App\Auth;

use Illuminate\Contracts\Hashing\Hasher as HasherContract;
use Illuminate\Hashing\BcryptHasher;

/**
 * Custom password hasher that supports legacy MD5+salt format
 * from the CakePHP 2.x app and transparently upgrades to bcrypt.
 *
 * Legacy format: "md5hash:salt" (40-char hex hash + colon + salt)
 */
class ApoboxPasswordHasher implements HasherContract
{
    protected BcryptHasher $bcrypt;

    public function __construct()
    {
        $this->bcrypt = new BcryptHasher(['rounds' => 12]);
    }

    public function info($hashedValue): array
    {
        if ($this->isLegacyHash($hashedValue)) {
            return ['algo' => 'md5-salt', 'algoName' => 'md5-salt', 'options' => []];
        }

        return $this->bcrypt->info($hashedValue);
    }

    public function make($value, array $options = []): string
    {
        return $this->bcrypt->make($value, $options);
    }

    public function check($value, $hashedValue, array $options = []): bool
    {
        if (empty($hashedValue)) {
            return false;
        }

        // Try bcrypt first
        if (!$this->isLegacyHash($hashedValue)) {
            return $this->bcrypt->check($value, $hashedValue, $options);
        }

        // Fall back to legacy MD5+salt: "hash:salt"
        [$hash, $salt] = explode(':', $hashedValue, 2);

        return $hash === md5($salt . $value);
    }

    public function needsRehash($hashedValue, array $options = []): bool
    {
        if ($this->isLegacyHash($hashedValue)) {
            return true;
        }

        return $this->bcrypt->needsRehash($hashedValue, $options);
    }

    protected function isLegacyHash(string $hashedValue): bool
    {
        return str_contains($hashedValue, ':') && strlen(explode(':', $hashedValue, 2)[0]) === 32;
    }
}
