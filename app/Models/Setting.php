<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['key', 'value'];

    protected const CACHE_KEY = 'app_settings';
    protected const CACHE_TTL = 3600;

    public static function get(string $key, mixed $default = null): mixed
    {
        $settings = static::allCached();
        return $settings[$key] ?? $default;
    }

    public static function set(string $key, ?string $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        static::clearCache();
    }

    public static function getMany(array $keys): array
    {
        $settings = static::allCached();
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $settings[$key] ?? null;
        }
        return $result;
    }

    public static function setMany(array $data): void
    {
        foreach ($data as $key => $value) {
            static::updateOrCreate(['key' => $key], ['value' => $value]);
        }
        static::clearCache();
    }

    public static function allCached(): array
    {
        return Cache::remember(static::CACHE_KEY, static::CACHE_TTL, function () {
            return static::pluck('value', 'key')->toArray();
        });
    }

    public static function clearCache(): void
    {
        Cache::forget(static::CACHE_KEY);
    }
}
