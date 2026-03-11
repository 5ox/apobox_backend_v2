<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerReminder extends Model
{
    protected $table = 'customer_reminders';
    public $timestamps = true;

    protected $fillable = [
        'customers_id',
        'type',
        'sent_at',
        'notes',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    /**
     * Valid reminder types.
     */
    public const TYPE_AWAITING_PAYMENT = 'awaiting_payment';
    public const TYPE_PARTIAL_SIGNUP = 'partial_signup';
    public const TYPE_EXPIRED_CARD = 'expired_card';

    public const TYPES = [
        self::TYPE_AWAITING_PAYMENT,
        self::TYPE_PARTIAL_SIGNUP,
        self::TYPE_EXPIRED_CARD,
    ];

    // ---------------------------------------------------------------
    // Relationships
    // ---------------------------------------------------------------

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customers_id', 'customers_id');
    }

    // ---------------------------------------------------------------
    // Scopes
    // ---------------------------------------------------------------

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeAwaitingPayment($query)
    {
        return $query->ofType(self::TYPE_AWAITING_PAYMENT);
    }

    public function scopePartialSignup($query)
    {
        return $query->ofType(self::TYPE_PARTIAL_SIGNUP);
    }

    public function scopeExpiredCard($query)
    {
        return $query->ofType(self::TYPE_EXPIRED_CARD);
    }
}
