<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomPackageRequest extends Model
{
    protected $table = 'custom_orders';
    protected $primaryKey = 'custom_orders_id';
    public $timestamps = false;

    const STATUS_NEW = 1;
    const STATUS_PROCESSING = 2;
    const STATUS_COMPLETED = 3;
    const STATUS_CANCELLED = 4;

    protected $fillable = [
        'customers_id',
        'billing_id',
        'tracking_id',
        'orders_id',
        'package_status',
        'package_repack',
        'insurance_fee',
        'insurance_coverage',
        'mail_class',
        'instructions',
    ];

    protected $casts = [
        'order_add_date' => 'datetime',
        'package_status' => 'integer',
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
    // Status Workflow
    // ---------------------------------------------------------------

    public function isPending(): bool
    {
        return $this->package_status === self::STATUS_NEW;
    }

    public function isProcessing(): bool
    {
        return $this->package_status === self::STATUS_PROCESSING;
    }

    public function isCompleted(): bool
    {
        return $this->package_status === self::STATUS_COMPLETED;
    }

    public function isCancelled(): bool
    {
        return $this->package_status === self::STATUS_CANCELLED;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->package_status) {
            self::STATUS_NEW => 'New',
            self::STATUS_PROCESSING => 'Processing',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_CANCELLED => 'Cancelled',
            default => 'Unknown',
        };
    }

    // ---------------------------------------------------------------
    // Scopes
    // ---------------------------------------------------------------

    public function scopePending($query)
    {
        return $query->where('package_status', self::STATUS_NEW);
    }

    public function scopeProcessing($query)
    {
        return $query->where('package_status', self::STATUS_PROCESSING);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('package_status', [self::STATUS_NEW, self::STATUS_PROCESSING]);
    }

    public function scopeCompleted($query)
    {
        return $query->where('package_status', self::STATUS_COMPLETED);
    }
}
