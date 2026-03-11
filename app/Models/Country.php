<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $table = 'countries';
    protected $primaryKey = 'countries_id';
    public $timestamps = false;

    protected $fillable = [
        'countries_name',
        'countries_iso_code_2',
        'countries_iso_code_3',
        'address_format_id',
    ];

    // ---------------------------------------------------------------
    // Relationships
    // ---------------------------------------------------------------

    public function zones()
    {
        return $this->hasMany(Zone::class, 'zone_country_id', 'countries_id');
    }
}
