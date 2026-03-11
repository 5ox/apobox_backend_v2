<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AffiliateLink extends Model
{
    protected $table = 'affiliate_links';
    public $timestamps = true;

    protected $fillable = [
        'code',
        'url',
        'description',
        'clicks',
        'conversions',
        'is_active',
    ];

    protected $casts = [
        'clicks' => 'integer',
        'conversions' => 'integer',
        'is_active' => 'boolean',
    ];

    // ---------------------------------------------------------------
    // Scopes
    // ---------------------------------------------------------------

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    /**
     * Record a click on this affiliate link.
     */
    public function recordClick(): void
    {
        $this->increment('clicks');
    }

    /**
     * Record a conversion for this affiliate link.
     */
    public function recordConversion(): void
    {
        $this->increment('conversions');
    }
}
