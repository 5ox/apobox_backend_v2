<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomersInfo extends Model
{
    protected $table = 'customers_info';
    protected $primaryKey = 'customers_info_id';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'customers_info_id',
        'customers_info_date_of_last_logon',
        'customers_info_number_of_logons',
        'customers_info_date_account_created',
        'customers_info_date_account_last_modified',
        'global_product_notifications',
    ];

    protected $casts = [
        'customers_info_date_of_last_logon' => 'datetime',
        'customers_info_date_account_created' => 'datetime',
        'customers_info_date_account_last_modified' => 'datetime',
        'customers_info_number_of_logons' => 'integer',
        'global_product_notifications' => 'boolean',
    ];

    // ---------------------------------------------------------------
    // Relationships
    // ---------------------------------------------------------------

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customers_info_id', 'customers_id');
    }
}
