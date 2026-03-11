<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class ShippingAddress extends Address
{
    /**
     * Boot the model and add a global scope that filters to only
     * APO/FPO/DPO addresses (military postal addresses).
     *
     * These are identified by:
     * - City in: APO, FPO, DPO
     * - State/zone code in: AA (Armed Forces Americas), AE (Armed Forces Europe), AP (Armed Forces Pacific)
     */
    protected static function booted(): void
    {
        static::addGlobalScope('military_postal', function (Builder $builder) {
            $builder->whereIn('entry_city', ['APO', 'FPO', 'DPO'])
                ->whereIn('entry_state', ['AA', 'AE', 'AP']);
        });
    }
}
