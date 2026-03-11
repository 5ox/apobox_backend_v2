<?php

namespace App\Models\OrderLineItems;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Order;

abstract class OrderLineItem extends Model
{
    protected $table = 'orders_total';
    protected $primaryKey = 'orders_total_id';
    public $timestamps = false;

    protected $fillable = [
        'orders_id',
        'title',
        'text',
        'value',
        'class',
        'sort_order',
    ];

    protected $casts = [
        'value' => 'decimal:4',
    ];

    /**
     * The discriminator class string stored in the `class` column.
     */
    abstract protected static function lineItemClass(): string;

    /**
     * Default title for this line item type.
     */
    abstract protected static function lineItemTitle(): string;

    /**
     * Default sort order for this line item type.
     */
    abstract protected static function lineItemSortOrder(): int;

    protected static function booted(): void
    {
        // Global scope: only return rows matching this subtype's class
        static::addGlobalScope('class', function (Builder $builder) {
            $builder->where('class', static::lineItemClass());
        });

        // Auto-set class, title, sort_order on creating
        static::creating(function ($item) {
            $item->class = $item->class ?: static::lineItemClass();
            $item->title = $item->title ?: static::lineItemTitle();
            $item->sort_order = $item->sort_order ?: static::lineItemSortOrder();

            // Format text as currency
            if ($item->value !== null) {
                $item->text = '$' . number_format((float) $item->value, 2);
            }
        });

        static::saving(function ($item) {
            if ($item->value !== null && $item->isDirty('value')) {
                $item->text = '$' . number_format((float) $item->value, 2);
            }
        });
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'orders_id', 'orders_id');
    }
}
