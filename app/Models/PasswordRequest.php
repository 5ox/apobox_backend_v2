<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PasswordRequest extends Model
{
    protected $table = 'password_requests';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = true;

    /**
     * Requests expire after 30 minutes.
     */
    public const EXPIRY_MINUTES = 30;

    protected $fillable = [
        'id',
        'customer_id',
        'token',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    // ---------------------------------------------------------------
    // Boot
    // ---------------------------------------------------------------

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
            if (empty($model->token)) {
                $model->token = Str::random(64);
            }
            if (empty($model->expires_at)) {
                $model->expires_at = now()->addMinutes(self::EXPIRY_MINUTES);
            }
        });
    }

    // ---------------------------------------------------------------
    // Relationships
    // ---------------------------------------------------------------

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customers_id');
    }

    // ---------------------------------------------------------------
    // Scopes
    // ---------------------------------------------------------------

    /**
     * Scope to only valid (non-expired) requests.
     */
    public function scopeValid($query)
    {
        return $query->where('expires_at', '>', now());
    }

    /**
     * Delete all expired password requests.
     */
    public function scopeDeleteExpired($query)
    {
        return $query->where('expires_at', '<=', now())->delete();
    }

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }
}
