<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuthorizedName extends Model
{
    protected $table = 'authorized_names';
    protected $primaryKey = 'authorized_names_id';
    public $timestamps = false;

    protected $fillable = [
        'customers_id',
        'authorized_firstname',
        'authorized_lastname',
    ];

    // ---------------------------------------------------------------
    // Relationships
    // ---------------------------------------------------------------

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customers_id', 'customers_id');
    }

    // ---------------------------------------------------------------
    // Accessors
    // ---------------------------------------------------------------

    public function getFullNameAttribute(): string
    {
        return trim($this->authorized_firstname . ' ' . $this->authorized_lastname);
    }
}
