<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class OrderStatusHistory extends Model
{
    protected $table = 'orders_status_history';
    protected $primaryKey = 'orders_status_history_id';
    public $timestamps = false;

    protected $fillable = [
        'orders_id',
        'orders_status_id',
        'date_added',
        'customer_notified',
        'comments',
    ];

    protected $casts = [
        'date_added' => 'datetime',
        'customer_notified' => 'boolean',
    ];

    // ---------------------------------------------------------------
    // Relationships
    // ---------------------------------------------------------------

    public function order()
    {
        return $this->belongsTo(Order::class, 'orders_id', 'orders_id');
    }

    public function status()
    {
        return $this->belongsTo(OrderStatus::class, 'orders_status_id', 'orders_status_id');
    }

    // ---------------------------------------------------------------
    // Static Helpers
    // ---------------------------------------------------------------

    /**
     * Record a new status history entry for an order.
     */
    public static function record(
        int $orderId,
        int $statusId,
        string $comments = '',
        bool $notified = false
    ): static {
        $entry = static::create([
            'orders_id' => $orderId,
            'orders_status_id' => $statusId,
            'date_added' => now(),
            'customer_notified' => $notified,
            'comments' => $comments,
        ]);

        // Bust report caches when order status changes
        static::clearReportCaches();

        return $entry;
    }

    /**
     * Clear all report-related caches.
     */
    public static function clearReportCaches(): void
    {
        $patterns = ['reports:summary:', 'reports:trends:', 'reports:customers:', 'reports:status_counts', 'reports:sales:', 'reports:signups:'];
        foreach ($patterns as $pattern) {
            // Clear known cache keys for common ranges
            if (str_ends_with($pattern, ':')) {
                foreach (['7d', '30d', '90d', '12m'] as $range) {
                    Cache::forget($pattern . $range);
                }
            } else {
                Cache::forget($pattern);
            }
        }
    }

    // ---------------------------------------------------------------
    // Scopes
    // ---------------------------------------------------------------

    /**
     * Find the current (most recent) status for an order.
     */
    public function scopeFindCurrentStatus($query, int $orderId)
    {
        return $query->where('orders_id', $orderId)
            ->orderByDesc('date_added')
            ->orderByDesc('orders_status_history_id');
    }
}
