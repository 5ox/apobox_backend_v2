<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Insurance extends Model
{
    protected $table = 'insurance';
    protected $primaryKey = 'insurance_id';
    public $timestamps = false;

    protected $fillable = [
        'coverage_amount',
        'fee',
        'description',
    ];

    protected $casts = [
        'coverage_amount' => 'decimal:2',
        'fee' => 'decimal:2',
    ];

    // ---------------------------------------------------------------
    // Static Helpers
    // ---------------------------------------------------------------

    /**
     * Look up the insurance fee for a given coverage amount.
     *
     * Returns the fee from the first row whose coverage_amount is >= $amount,
     * or the highest available fee if $amount exceeds all tiers.
     */
    public static function getFeeForCoverageAmount(float $amount): float
    {
        $tier = static::where('coverage_amount', '>=', $amount)
            ->orderBy('coverage_amount')
            ->first();

        if ($tier) {
            return (float) $tier->fee;
        }

        // Fall back to the highest tier
        $highest = static::orderByDesc('coverage_amount')->first();

        return $highest ? (float) $highest->fee : 0.00;
    }
}
