<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerReminder extends Model
{
    protected $table = 'customer_reminders';
    protected $primaryKey = 'customer_reminder_id';
    public $timestamps = true;

    const CREATED_AT = 'created';
    const UPDATED_AT = 'modified';

    protected $fillable = [
        'customers_id',
        'orders_id',
        'reminder_type',
        'reminder_count',
    ];

    protected $casts = [
        'orders_id' => 'integer',
        'reminder_count' => 'integer',
        'created' => 'datetime',
        'modified' => 'datetime',
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

    public function order()
    {
        return $this->belongsTo(Order::class, 'orders_id', 'orders_id');
    }

    // ---------------------------------------------------------------
    // Scopes
    // ---------------------------------------------------------------

    public function scopeOfType($query, string $type)
    {
        return $query->where('reminder_type', $type);
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
