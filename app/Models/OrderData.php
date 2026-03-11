<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderData extends Model
{
    protected $table = 'orders_data';
    protected $primaryKey = 'orders_data_id';
    public $timestamps = false;

    protected $fillable = [
        'orders_id',
        'data_key',
        'data_value',
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
     * Get a data value by key for a given order.
     */
    public static function getValue(int $orderId, string $key, $default = null)
    {
        $record = static::where('orders_id', $orderId)
            ->where('data_key', $key)
            ->first();

        return $record ? $record->data_value : $default;
    }

    /**
     * Set a data value by key for a given order (insert or update).
     */
    public static function setValue(int $orderId, string $key, string $value): static
    {
        return static::updateOrCreate(
            ['orders_id' => $orderId, 'data_key' => $key],
            ['data_value' => $value]
        );
    }
}
