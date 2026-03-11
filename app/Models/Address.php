<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $table = 'address_book';
    protected $primaryKey = 'address_book_id';
    public $timestamps = false;

    protected $fillable = [
        'customers_id',
        'entry_gender',
        'entry_company',
        'entry_firstname',
        'entry_lastname',
        'entry_street_address',
        'entry_suburb',
        'entry_postcode',
        'entry_city',
        'entry_state',
        'entry_country_id',
        'entry_zone_id',
    ];

    // ---------------------------------------------------------------
    // Relationships
    // ---------------------------------------------------------------

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customers_id', 'customers_id');
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class, 'entry_zone_id', 'zone_id');
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'entry_country_id', 'countries_id');
    }

    // ---------------------------------------------------------------
    // Accessors
    // ---------------------------------------------------------------

    /**
     * Return a formatted single-line address string.
     */
    public function getFullAttribute(): string
    {
        $parts = array_filter([
            trim($this->entry_firstname . ' ' . $this->entry_lastname),
            $this->entry_company,
            $this->entry_street_address,
            $this->entry_suburb,
            $this->entry_city,
            $this->entry_state,
            $this->entry_postcode,
        ]);

        return implode(', ', $parts);
    }
}
