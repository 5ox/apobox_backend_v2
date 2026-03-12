<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Insurance extends Model
{
    protected $table = 'insurance';
    protected $primaryKey = 'insurance_id';
    public $timestamps = false;

    protected $fillable = [
        'amount_from',
        'amount_to',
        'insurance_fee',
    ];

    protected $casts = [
        'amount_from' => 'decimal:2',
        'amount_to' => 'decimal:2',
        'insurance_fee' => 'decimal:2',
    ];

    // ---------------------------------------------------------------
    // Static Helpers
    // ---------------------------------------------------------------

    /**
     * Look up the insurance fee for a given coverage amount.
     *
     * Finds the tier where amount_from <= $amount <= amount_to.
     */
    public static function getFeeForCoverageAmount(float $amount): float
    {
        $tier = static::where('amount_from', '<=', $amount)
            ->where('amount_to', '>=', $amount)
            ->first();

        if ($tier) {
            return (float) $tier->insurance_fee;
        }

        // Fall back to the highest tier
        $highest = static::orderByDesc('amount_to')->first();

        return $highest ? (float) $highest->insurance_fee : 0.00;
    }
}
