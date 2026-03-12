<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\MasksCreditCard;
use App\Models\OrderLineItems\OrderLineItem;
use App\Models\OrderLineItems\OrderShipping;
use App\Models\OrderLineItems\OrderFee;
use App\Models\OrderLineItems\OrderInsurance;
use App\Models\OrderLineItems\OrderBattery;
use App\Models\OrderLineItems\OrderRepack;
use App\Models\OrderLineItems\OrderStorage;
use App\Models\OrderLineItems\OrderReturn;
use App\Models\OrderLineItems\OrderMisaddressed;
use App\Models\OrderLineItems\OrderShipToUS;
use App\Models\OrderLineItems\OrderSubtotal;
use App\Models\OrderLineItems\OrderTotal;
use App\Models\OrderData;

class Order extends Model
{
    use MasksCreditCard;

    protected $table = 'orders';
    protected $primaryKey = 'orders_id';
    public $timestamps = false;

    protected $fillable = [
        'customers_id',
        'customers_name',
        'customers_company',
        'customers_street_address',
        'customers_suburb',
        'customers_city',
        'customers_postcode',
        'customers_state',
        'customers_country',
        'customers_telephone',
        'customers_email_address',
        'customers_address_format_id',
        'delivery_name',
        'delivery_company',
        'delivery_street_address',
        'delivery_suburb',
        'delivery_city',
        'delivery_postcode',
        'delivery_state',
        'delivery_country',
        'delivery_address_format_id',
        'billing_name',
        'billing_company',
        'billing_street_address',
        'billing_suburb',
        'billing_city',
        'billing_postcode',
        'billing_state',
        'billing_country',
        'billing_address_format_id',
        'payment_method',
        'cc_type',
        'cc_owner',
        'cc_number',
        'cc_expires',
        'comments',
        'last_modified',
        'date_purchased',
        'turnaround_sec',
        'orders_status',
        'orders_date_finished',
        'ups_track_num',
        'usps_track_num',
        'usps_track_num_in',
        'fedex_track_num',
        'fedex_freight_track_num',
        'dhl_track_num',
        'currency',
        'currency_value',
        'shipping_tax',
        'billing_status',
        'qbi_imported',
        'width',
        'length',
        'depth',
        'weight_oz',
        'mail_class',
        'package_type',
        'NonMachinable',
        'OversizeRate',
        'BalloonRate',
        'package_flow',
        'shipped_from',
        'insurance_coverage',
        'warehouse',
        'postage_id',
        'trans_id',
        'moved_to_invoice',
        'creator_id',
        'amazon_track_num',
        'customs_description',
    ];

    protected $casts = [
        'last_modified' => 'datetime',
        'date_purchased' => 'datetime',
        'orders_date_finished' => 'datetime',
        'billing_status' => 'boolean',
    ];

    // ---------------------------------------------------------------
    // Relationships
    // ---------------------------------------------------------------

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customers_id', 'customers_id');
    }

    public function status()
    {
        return $this->belongsTo(OrderStatus::class, 'orders_status', 'orders_status_id');
    }

    public function statusHistory()
    {
        return $this->hasMany(OrderStatusHistory::class, 'orders_id', 'orders_id');
    }

    public function lineItems()
    {
        // Use a concrete subclass with withoutGlobalScopes() to query all
        // rows in orders_total without the abstract class instantiation issue
        return $this->hasMany(OrderTotal::class, 'orders_id', 'orders_id')
            ->withoutGlobalScopes();
    }

    public function shipping()
    {
        return $this->hasOne(OrderShipping::class, 'orders_id', 'orders_id');
    }

    public function fee()
    {
        return $this->hasOne(OrderFee::class, 'orders_id', 'orders_id');
    }

    public function insurance()
    {
        return $this->hasOne(OrderInsurance::class, 'orders_id', 'orders_id');
    }

    public function battery()
    {
        return $this->hasOne(OrderBattery::class, 'orders_id', 'orders_id');
    }

    public function repack()
    {
        return $this->hasOne(OrderRepack::class, 'orders_id', 'orders_id');
    }

    public function storage()
    {
        return $this->hasOne(OrderStorage::class, 'orders_id', 'orders_id');
    }

    public function returnItem()
    {
        return $this->hasOne(OrderReturn::class, 'orders_id', 'orders_id');
    }

    public function misaddressed()
    {
        return $this->hasOne(OrderMisaddressed::class, 'orders_id', 'orders_id');
    }

    public function shipToUS()
    {
        return $this->hasOne(OrderShipToUS::class, 'orders_id', 'orders_id');
    }

    public function subtotal()
    {
        return $this->hasOne(OrderSubtotal::class, 'orders_id', 'orders_id');
    }

    public function total()
    {
        return $this->hasOne(OrderTotal::class, 'orders_id', 'orders_id');
    }

    public function data()
    {
        return $this->hasMany(OrderData::class, 'orders_id', 'orders_id');
    }

    public function customPackageRequests()
    {
        return $this->hasMany(CustomPackageRequest::class, 'orders_id', 'orders_id');
    }

    // ---------------------------------------------------------------
    // Accessors
    // ---------------------------------------------------------------

    public function getDeliveryAddressAttribute(): string
    {
        return implode(', ', array_filter([
            $this->delivery_name,
            $this->delivery_street_address,
            $this->delivery_city,
            $this->delivery_state,
            $this->delivery_postcode,
        ]));
    }

    public function getInboundTrackingAttribute(): ?string
    {
        return $this->usps_track_num_in ?: $this->ups_track_num ?: $this->dhl_track_num;
    }

    public function getDimensionsAttribute(): ?string
    {
        if (empty($this->width) && empty($this->length) && empty($this->depth)) {
            return null;
        }

        return "{$this->length}x{$this->width}x{$this->depth}";
    }
}
