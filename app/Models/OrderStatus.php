<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderStatus extends Model
{
    protected $table = 'orders_status';
    protected $primaryKey = 'orders_status_id';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'orders_status_id',
        'orders_status_name',
        'language_id',
    ];

    // ---------------------------------------------------------------
    // Relationships
    // ---------------------------------------------------------------

    public function orders()
    {
        return $this->hasMany(Order::class, 'orders_status', 'orders_status_id');
    }
}
