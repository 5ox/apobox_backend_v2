<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AffiliateLink extends Model
{
    protected $table = 'affiliate_links';
    public $timestamps = false;

    protected $fillable = [
        'title',
        'url',
        'enabled',
    ];

    protected $casts = [
        'enabled' => 'boolean',
    ];

    // ---------------------------------------------------------------
    // Scopes
    // ---------------------------------------------------------------

    public function scopeActive($query)
    {
        return $query->where('enabled', true);
    }
}
