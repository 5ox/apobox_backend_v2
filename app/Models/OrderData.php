<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderData extends Model
{
    protected $table = 'orders_data';
    protected $primaryKey = 'orders_data_id';
    public $timestamps = true;

    const CREATED_AT = 'created';
    const UPDATED_AT = 'modified';

    protected $fillable = [
        'orders_id',
        'data_type',
        'data',
    ];

    protected $casts = [
        'orders_id' => 'integer',
        'created' => 'datetime',
        'modified' => 'datetime',
    ];

    // ---------------------------------------------------------------
    // Relationships
    // ---------------------------------------------------------------

    public function order()
    {
        return $this->belongsTo(Order::class, 'orders_id', 'orders_id');
    }

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    /**
     * Get a data value by type for a given order.
     */
    public static function getValue(int $orderId, string $type, $default = null)
    {
        $record = static::where('orders_id', $orderId)
            ->where('data_type', $type)
            ->first();

        return $record ? $record->data : $default;
    }

    /**
     * Set a data value by type for a given order (insert or update).
     */
    public static function setValue(int $orderId, string $type, string $value): static
    {
        return static::updateOrCreate(
            ['orders_id' => $orderId, 'data_type' => $type],
            ['data' => $value]
        );
    }
}
