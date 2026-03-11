<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tracking extends Model
{
    protected $table = 'tracking';
    protected $primaryKey = 'tracking_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'tracking_id',
        'orders_id',
        'carrier',
        'tracking_number',
        'status',
        'date_added',
        'date_updated',
    ];

    protected $casts = [
        'date_added' => 'datetime',
        'date_updated' => 'datetime',
    ];

    // ---------------------------------------------------------------
    // Relationships
    // ---------------------------------------------------------------

    public function order()
    {
        return $this->belongsTo(Order::class, 'orders_id', 'orders_id');
    }
}
