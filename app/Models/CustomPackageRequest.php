<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomPackageRequest extends Model
{
    protected $table = 'custom_orders';
    protected $primaryKey = 'custom_orders_id';
    public $timestamps = false;

    protected $fillable = [
        'customers_id',
        'orders_id',
        'status',
        'request_date',
        'completed_date',
        'description',
        'instructions',
        'admin_notes',
    ];

    protected $casts = [
        'request_date' => 'datetime',
        'completed_date' => 'datetime',
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
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isDenied(): bool
    {
        return $this->status === 'denied';
    }

    public function approve(): bool
    {
        return $this->update(['status' => 'approved']);
    }

    public function complete(): bool
    {
        return $this->update([
            'status' => 'completed',
            'completed_date' => now(),
        ]);
    }

    public function deny(): bool
    {
        return $this->update(['status' => 'denied']);
    }

    // ---------------------------------------------------------------
    // Scopes
    // ---------------------------------------------------------------

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}
