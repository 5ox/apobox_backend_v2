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

    const CREATED_AT = 'created';
    const UPDATED_AT = null;

    /**
     * Requests expire after 30 minutes.
     */
    public const EXPIRY_MINUTES = 30;

    protected $fillable = [
        'id',
        'customer_id',
        'admin_id',
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
     * Scope to only valid (non-expired) requests based on created.
     */
    public function scopeValid($query)
    {
        return $query->where('created', '>', now()->subMinutes(self::EXPIRY_MINUTES));
    }

    /**
     * Delete all expired password requests.
     */
    public function scopeDeleteExpired($query)
    {
        return $query->where('created', '<=', now()->subMinutes(self::EXPIRY_MINUTES))->delete();
    }

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    public function isExpired(): bool
    {
        return $this->created->addMinutes(self::EXPIRY_MINUTES)->isPast();
    }
}
