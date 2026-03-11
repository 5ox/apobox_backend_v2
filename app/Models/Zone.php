<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Zone extends Model
{
    protected $table = 'zones';
    protected $primaryKey = 'zone_id';
    public $timestamps = false;

    protected $fillable = [
        'zone_country_id',
        'zone_code',
        'zone_name',
    ];

    // ---------------------------------------------------------------
    // Relationships
    // ---------------------------------------------------------------

    public function country()
    {
        return $this->belongsTo(Country::class, 'zone_country_id', 'countries_id');
    }
}
